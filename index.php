<?php
// Include database connection
require_once('config.php');

// Get homepage content from database
function getHomepageContent() {
    global $conn;
    
    // Initialize data object to store all homepage content
    $homepageData = [
        'sections' => [],
        'banner' => [],
        'stats' => [],
        'about' => [],
        'services' => [],
        'contact' => [],
        'products' => [],
        'testimonials' => [],
        'blog' => []
    ];
    
    try {
        // Get all active sections
        $stmt = $conn->query("SELECT * FROM homepage_sections WHERE is_active = 1 ORDER BY id");
        $homepageData['sections'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get content for each section
        foreach ($homepageData['sections'] as $section) {
            $sectionKey = $section['section_key'];
            
            // Get general content for this section
            $stmt = $conn->prepare("SELECT content_key, content_value 
                                  FROM homepage_content 
                                  WHERE section_id = :section_id");
            $stmt->bindParam(':section_id', $section['id']);
            $stmt->execute();
            $sectionContent = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Create associative array of content
            foreach ($sectionContent as $content) {
                $homepageData[$sectionKey][$content['content_key']] = $content['content_value'];
            }
            
            // Get specific data for certain sections
            switch ($sectionKey) {
                case 'stats':
                    // Get stats slider items
                    $stmt = $conn->query("SELECT * FROM stats_slider WHERE is_active = 1 ORDER BY position");
                    $homepageData[$sectionKey]['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;
                    
                case 'services':
                    // Get service items
                    $stmt = $conn->query("SELECT * FROM services_section WHERE is_active = 1 ORDER BY position");
                    $homepageData[$sectionKey]['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;
                    
                case 'products':
                    // Get product items
                    $stmt = $conn->query("SELECT * FROM products_section WHERE is_active = 1 ORDER BY position");
                    $homepageData[$sectionKey]['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;
                    
                case 'testimonials':
                    // Get testimonial items
                    $stmt = $conn->query("SELECT * FROM testimonials WHERE is_active = 1");
                    $homepageData[$sectionKey]['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;
                    
                case 'blog':
                    // Get blog posts
                    $stmt = $conn->query("SELECT * FROM featured_blog_posts WHERE is_active = 1 ORDER BY position LIMIT 3");
                    $homepageData[$sectionKey]['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;
            }
        }
        
        return $homepageData;
    } catch (PDOException $e) {
        // In case of error, return empty data structure
        error_log("Error fetching homepage content: " . $e->getMessage());
        return $homepageData;
    }
}

// Get the homepage content
$homepageData = getHomepageContent();

// Check if we have content
$hasHomepageData = !empty($homepageData['sections']);

// Function to check if a section is active
function isSectionActive($sectionKey, $homepageData) {
    foreach ($homepageData['sections'] as $section) {
        if ($section['section_key'] === $sectionKey && $section['is_active']) {
            return true;
        }
    }
    return false;
}
?>

<!doctype html>
<html lang="id">
  <?php
  include('components/head.php');
  ?>
  <body>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WC2H98R" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

    <div class="preloader">
      <div class="d-table">
        <div class="d-table-cell">
          <div class="spinner"></div>
        </div>
      </div>
    </div>    
    <?php
    include('components/navbar.php')
    ?>
    <div class="search-overlay">
      <div class="d-table">
        <div class="d-table-cell">
          <div class="search-layer"></div>
          <div class="search-layer"></div>
          <div class="search-layer"></div>
          <div class="search-close"><span class="search-close-line"></span><span class="search-close-line"></span></div>
          <div class="search-form">
            <form><input type="text" class="input-search" placeholder="Search here..."><button type="submit"><i class='bx bx-search'></i></button></form>
          </div>
        </div>
      </div>
    </div>
    
    <?php if ($hasHomepageData && isSectionActive('banner', $homepageData)): ?>
    <!-- Banner Section -->
    <div class="banner-area-two">
      <div class="container-fluid">
        <div class="container-max">
          <div class="row align-items-center">
            <div class="col-lg-5">
              <div class="banner-content">
                <h1><?php echo htmlspecialchars($homepageData['banner']['title'] ?? 'Platform Academic Digital With Excellent Quality'); ?></h1>
                <p><?php echo htmlspecialchars($homepageData['banner']['subtitle'] ?? 'Platform Akademi Merdeka membantu setiap insan akademisi dengan pelayanan yang eksklusif'); ?></p>
                <div class="banner-btn">
                  <a href="<?php echo htmlspecialchars($homepageData['banner']['button1_link'] ?? '#'); ?>" class="default-btn btn-bg-two border-radius-50">
                    <?php echo htmlspecialchars($homepageData['banner']['button1_text'] ?? 'Learn More'); ?> <i class='bx bx-chevron-right'></i>
                  </a>
                  <a href="<?php echo htmlspecialchars($homepageData['banner']['button2_link'] ?? 'https://wa.me/6287735426107'); ?>" target="_blank" class="default-btn btn-bg-one border-radius-50 ml-20">
                    <?php echo htmlspecialchars($homepageData['banner']['button2_text'] ?? 'Whatsapp'); ?> <i class='bx bx-chevron-right'></i>
                  </a>
                </div>
              </div>
            </div>
            <div class="col-lg-7">
              <div class="banner-img">
                <img src="<?php echo htmlspecialchars($homepageData['banner']['banner_image'] ?? 'assets/images/home-three/home-main-pic.png'); ?>" alt="Banner Image">
                <div class="banner-img-shape">
                  <img src="<?php echo htmlspecialchars($homepageData['banner']['banner_shape'] ?? 'assets/images/home-three/home-three-shape.png'); ?>" alt="Shape" loading="lazy">
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <?php if (isSectionActive('stats', $homepageData) && !empty($homepageData['stats']['items'])): ?>
      <!-- Stats Slider -->
      <div class="container" style="padding-top: 70px;">
        <div class="banner-sub-slider owl-carousel owl-theme">
          <?php foreach ($homepageData['stats']['items'] as $stat): ?>
          <div class="banner-sub-item">
            <img src="<?php echo htmlspecialchars($stat['image']); ?>" alt="<?php echo htmlspecialchars($stat['title']); ?>" loading="lazy">
            <div class="content">
              <h3><?php echo htmlspecialchars($stat['count']); ?></h3>
              <span><?php echo htmlspecialchars($stat['title']); ?></span>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <?php if ($hasHomepageData && isSectionActive('about', $homepageData)): ?>
    <!-- About Section -->
    <div class="about-area about-bg pt-100 pb-70">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-6">
            <div class="about-img-2">
              <img src="<?php echo htmlspecialchars($homepageData['about']['image'] ?? 'assets/images/about/home-about.png'); ?>" alt="About Images" loading="lazy">
            </div>
          </div>
          <div class="col-lg-6">
            <div class="about-content-2 ml-20">
              <div class="section-title">
                <span class="sp-color1"><?php echo htmlspecialchars($homepageData['about']['subtitle'] ?? 'About Us'); ?></span>
                <h2><?php echo htmlspecialchars($homepageData['about']['title'] ?? 'Tentang Kita'); ?></h2>
                <p style="text-align: justify;">
                  <?php echo htmlspecialchars($homepageData['about']['description'] ?? 'Akademi Merdeka mempunyai ruang lingkup dalam bidang akademisi yang tujuannya ialah membantu setiap insan akademisi dengan berbagai problematika yang sedang dihadapi.'); ?>
                </p>
              </div>
              <div class="row">
                <div class="col-lg-6 col-6">
                  <div class="about-card">
                    <div class="content">
                      <i class="<?php echo htmlspecialchars($homepageData['about']['card1_icon'] ?? 'flaticon-practice'); ?>" style="color: #ffc221;"></i>
                      <h3><?php echo htmlspecialchars($homepageData['about']['card1_title'] ?? 'Experience'); ?></h3>
                    </div>
                    <p style="text-align: justify;">
                      <?php echo htmlspecialchars($homepageData['about']['card1_text'] ?? 'Berbagai macam persoalan sudah kami pecahkan dengan prosedur yang efektif.'); ?>
                    </p>
                  </div>
                </div>
                <div class="col-lg-6 col-6">
                  <div class="about-card">
                    <div class="content">
                      <i class="<?php echo htmlspecialchars($homepageData['about']['card2_icon'] ?? 'flaticon-help'); ?>" style="color: #ffc221;"></i>
                      <h3><?php echo htmlspecialchars($homepageData['about']['card2_title'] ?? 'Quick Support'); ?></h3>
                    </div>
                    <p style="text-align: justify;">
                      <?php echo htmlspecialchars($homepageData['about']['card2_text'] ?? 'Dukungan setiap persoalan akan didampingi oleh satu supervisi yang expert.'); ?>
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>
    
    <?php if ($hasHomepageData && isSectionActive('services', $homepageData)): ?>
    <!-- Services Section -->
    <div class="security-area pt-100 pb-70">
      <div class="container">
        <div class="section-title text-center">
          <span class="sp-color2"><?php echo htmlspecialchars($homepageData['services']['subtitle'] ?? 'Layanan'); ?></span>
          <h2><?php echo htmlspecialchars($homepageData['services']['title'] ?? 'Layanan Kami'); ?></h2>
        </div>
        
        <?php if (!empty($homepageData['services']['items'])): ?>
        <div class="row pt-45">
          <?php foreach ($homepageData['services']['items'] as $service): ?>
          <div class="col-lg-4 col-sm-6">
            <div class="security-card">
              <i><img src="<?php echo htmlspecialchars($service['icon']); ?>" width="45" height="45" style="margin-top:-5px;"></i>
              <h3><a href="<?php echo htmlspecialchars($service['link']); ?>"><?php echo htmlspecialchars($service['title']); ?></a></h3>
              <p style="text-align: justify;"><?php echo htmlspecialchars($service['description']); ?></p>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
    
    <?php if ($hasHomepageData && isSectionActive('contact', $homepageData)): ?>
    <!-- Contact Section -->
    <div class="talk-area ptb-100">
        <div class="container">
          <div class="talk-content text-center">
            <div class="section-title text-center">
              <span class="sp-color1"><?php echo htmlspecialchars($homepageData['contact']['subtitle'] ?? 'Hubungi Kami'); ?></span>
              <h2><?php echo htmlspecialchars($homepageData['contact']['title'] ?? 'Kami melayani berbagai persoalan dengan solusi yang tepat'); ?></h2>
            </div>
            <a href="<?php echo htmlspecialchars($homepageData['contact']['button_link'] ?? 'https://wa.me/6287735426107'); ?>" target="_blank" class="default-btn btn-bg-one border-radius-5">
              <?php echo htmlspecialchars($homepageData['contact']['button_text'] ?? 'Whatsapp'); ?>
            </a>
          </div>
        </div>
      </div>    
    <?php endif; ?>
           
    <?php if ($hasHomepageData && isSectionActive('products', $homepageData)): ?>
    <!-- Products Section -->
    <section class="technology-area-two pt-100 pb-70">
      <div class="container">
        <div class="section-title text-center">
          <span class="sp-color2"><?php echo htmlspecialchars($homepageData['products']['subtitle'] ?? 'Produk Kami'); ?></span>
          <h2><?php echo htmlspecialchars($homepageData['products']['title'] ?? 'Kami memberikan solusi terbaik dengan produk terpercaya dan berkualitas'); ?></h2>
        </div>
        
        <?php if (!empty($homepageData['products']['items'])): ?>
        <div class="row pt-45">
          <?php foreach ($homepageData['products']['items'] as $product): ?>
          <div class="col-lg-2 col-6">
            <div class="technology-card technology-card-color">
              <img src="<?php echo htmlspecialchars($product['icon']); ?>" width="50" height="50">
              <h3><?php echo htmlspecialchars($product['title']); ?></h3>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </section>
    <?php endif; ?>
    
    <?php if ($hasHomepageData && isSectionActive('testimonials', $homepageData)): ?>
    <!-- Testimonials Section -->
    <section class="clients-area pt-100 pb-70">
      <div class="container">
        <div class="section-title text-center">
          <span class="sp-color1"><?php echo htmlspecialchars($homepageData['testimonials']['subtitle'] ?? 'Testimoni'); ?></span>
          <h2><?php echo htmlspecialchars($homepageData['testimonials']['title'] ?? 'Apa Kata Mereka?'); ?></h2>
        </div>
        
        <?php if (!empty($homepageData['testimonials']['items'])): ?>
        <div class="clients-slider owl-carousel owl-theme pt-45">
          <?php foreach ($homepageData['testimonials']['items'] as $testimonial): ?>
          <div class="clients-content">
            <div class="content">
              <img src="<?php echo htmlspecialchars($testimonial['image']); ?>" alt="<?php echo htmlspecialchars($testimonial['name']); ?>" loading="lazy">
              <i class='bx bxs-quote-alt-left'></i>
              <h3><?php echo htmlspecialchars($testimonial['name']); ?></h3>
              <span><?php echo htmlspecialchars($testimonial['position']); ?></span>
            </div>
            <p><?php echo htmlspecialchars($testimonial['content']); ?></p>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
      <div class="client-circle">
        <div class="client-circle-1"><div class="circle"></div></div>
        <div class="client-circle-2"><div class="circle"></div></div>
        <div class="client-circle-3"><div class="circle"></div></div>
        <div class="client-circle-4"><div class="circle"></div></div>
        <div class="client-circle-5"><div class="circle"></div></div>
        <div class="client-circle-6"><div class="circle"></div></div>
        <div class="client-circle-7"><div class="circle"></div></div>
      </div>
    </section>
    <?php endif; ?>
    
    <?php if ($hasHomepageData && isSectionActive('blog', $homepageData)): ?>
    <!-- Blog Section -->
    <div class="blog-area pt-100 pb-70">
      <div class="container">
        <div class="section-title text-center">
          <span class="sp-color2"><?php echo htmlspecialchars($homepageData['blog']['subtitle'] ?? 'Blog'); ?></span>
          <h2><?php echo htmlspecialchars($homepageData['blog']['title'] ?? 'Artikel Kami'); ?></h2>
        </div>
        
        <?php if (!empty($homepageData['blog']['items'])): ?>
        <div class="row pt-45">          
          <?php foreach ($homepageData['blog']['items'] as $post): ?>
          <div class="col-lg-4 col-md-6">
            <div class="blog-card">
              <div class="blog-img">
                <a href="<?php echo htmlspecialchars($post['link']); ?>">
                  <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" loading="lazy">
                </a>
                <div class="blog-tag">
                  <h3><?php echo date('d', strtotime($post['date'])); ?></h3>
                  <span><?php echo date('M', strtotime($post['date'])); ?></span>
                </div>
              </div>
              <div class="content">
                <ul>
                  <li><a href="/"><i class='bx bxs-user'></i> by <?php echo htmlspecialchars($post['author']); ?></a></li>
                  <li><a href="/"><i class='bx bx-purchase-tag-alt'></i><?php echo htmlspecialchars($post['category']); ?></a></li>
                </ul>
                <h3><a href="<?php echo htmlspecialchars($post['link']); ?>"><?php echo htmlspecialchars($post['title']); ?></a></h3>
                <p><?php echo htmlspecialchars($post['excerpt']); ?></p>
                <a href="<?php echo htmlspecialchars($post['link']); ?>" class="read-btn">Selengkapnya <i class='bx bx-chevron-right'></i></a>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
    
    <?php
    include('components/footer.php')
    ?>
      
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/owl.carousel.min.js"></script>
    <script src="assets/js/jquery.magnific-popup.min.js"></script>
    <script src="assets/js/jquery.nice-select.min.js"></script>
    <script src="assets/js/wow.min.js"></script>
    <script src="assets/js/meanmenu.js"></script>
    <script src="assets/js/jquery.ajaxchimp.min.js"></script>
    <script src="assets/js/form-validator.min.js"></script>
    <script src="assets/js/contact-form-script.js"></script>
    <script src="assets/js/custom.js"></script>
  </body>
</html>