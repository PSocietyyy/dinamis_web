<!doctype html>
<html lang="id">
  <?php
  // Import database configuration and functions
  require_once 'config.php';
  require_once 'include/functions.php';
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

    <!-- HERO SECTION -->
    <div class="banner-area-two">
      <div class="container-fluid">
        <div class="container-max">
          <div class="row align-items-center">
            <div class="col-lg-5">
              <div class="banner-content">
                <h1><?php echo htmlspecialchars(getContentByKey($conn, 'hero', 'hero_title') ?: 'Platform Academic Digital With Excellent Quality'); ?></h1>
                <p><?php echo htmlspecialchars(getContentByKey($conn, 'hero', 'hero_description') ?: 'Platform Akademi Merdeka membantu setiap insan akademisi dengan pelayanan yang eksklusif'); ?></p>
                <div class="banner-btn">
                  <a href="<?php echo htmlspecialchars(getContentByKey($conn, 'hero', 'button1_url') ?: '#'); ?>" class="default-btn btn-bg-two border-radius-50">
                    <?php echo htmlspecialchars(getContentByKey($conn, 'hero', 'button1_text') ?: 'Learn More'); ?> 
                    <i class='bx bx-chevron-right'></i>
                  </a>
                  <a href="<?php echo htmlspecialchars(getContentByKey($conn, 'hero', 'button2_url') ?: 'https://wa.me/6287735426107'); ?>" target="_blank" class="default-btn btn-bg-one border-radius-50 ml-20">
                    <?php echo htmlspecialchars(getContentByKey($conn, 'hero', 'button2_text') ?: 'Whatsapp'); ?> 
                    <i class='bx bx-chevron-right'></i>
                  </a>
                </div>
              </div>
            </div>
            <div class="col-lg-7">
              <div class="banner-img">
                <img src="<?php echo htmlspecialchars(getContentByKey($conn, 'hero', 'hero_image') ?: 'assets/images/home-three/home-main-pic.png'); ?>" alt="Hero Image">
                <div class="banner-img-shape">
                  <img src="assets/images/home-three/home-three-shape.png" alt="Shape" loading="lazy">
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- STATS SLIDER -->
      <div class="container" style="padding-top: 70px;">
        <div class="banner-sub-slider owl-carousel owl-theme">
          <?php
          $stats = getPageSectionContent($conn, 'stats');
          if (!empty($stats)) {
            foreach ($stats as $stat) {
              ?>
              <div class="banner-sub-item">
                <img src="<?php echo htmlspecialchars($stat['image_url']); ?>" alt="<?php echo htmlspecialchars($stat['section_key']); ?>" loading="lazy">
                <div class="content">
                  <h3><?php echo htmlspecialchars($stat['content_value']); ?></h3>
                  <span><?php echo htmlspecialchars($stat['section_key']); ?></span>
                </div>
              </div>
              <?php
            }
          } else {
            // Fallback to static content if no dynamic content is available
            ?>
            <div class="banner-sub-item">
              <img src="assets/images/home-three/home-slider-karya-ilmiah.png" alt="Karya Ilmiah" loading="lazy">
              <div class="content">
                <h3>500+</h3><span>Karya Ilmiah</span>
              </div>
            </div>
            <div class="banner-sub-item">
              <img src="assets/images/home-three/home-slider-pendampingan-ojs.png" alt="Pendampingan OJS" loading="lazy">
              <div class="content">
                <h3>10+</h3><span>Pendampingan OJS</span>
              </div>
            </div>
            <?php
          }
          ?>
        </div>
      </div>
    </div>
    
    <!-- ABOUT SECTION -->
    <div class="about-area about-bg pt-100 pb-70">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-6">
            <div class="about-img-2">
              <img src="<?php echo htmlspecialchars(getContentByKey($conn, 'about', 'about_image') ?: 'assets/images/about/home-about.png'); ?>" alt="About Images" loading="lazy">
            </div>
          </div>
          <div class="col-lg-6">
            <div class="about-content-2 ml-20">
              <div class="section-title">
                <span class="sp-color1"><?php echo htmlspecialchars(getContentByKey($conn, 'about', 'about_subtitle') ?: 'About Us'); ?></span>
                <h2><?php echo htmlspecialchars(getContentByKey($conn, 'about', 'about_title') ?: 'Tentang Kita'); ?></h2>
                <p style="text-align: justify;"><?php echo htmlspecialchars(getContentByKey($conn, 'about', 'about_description') ?: 'Akademi Merdeka mempunyai ruang lingkup dalam bidang akademisi yang tujuannya ialah membantu setiap insan akademisi dengan berbagai problematika yang sedang dihadapi.'); ?></p>
              </div>
              <div class="row">
                <?php
                $aboutFeatures = getPageSectionContent($conn, 'about_feature');
                if (!empty($aboutFeatures)) {
                  foreach ($aboutFeatures as $feature) {
                    ?>
                    <div class="col-lg-6 col-6">
                      <div class="about-card">
                        <div class="content">
                          <i class="<?php echo htmlspecialchars($feature['content_value']); ?>" style="color: #ffc221;"></i>
                          <h3><?php echo htmlspecialchars($feature['section_key']); ?></h3>
                        </div>
                        <p style="text-align: justify;"><?php echo htmlspecialchars($feature['link_url']); ?></p>
                      </div>
                    </div>
                    <?php
                  }
                } else {
                  // Fallback to static content
                  ?>
                  <div class="col-lg-6 col-6">
                    <div class="about-card">
                      <div class="content"><i class="flaticon-practice" style="color: #ffc221;"></i>
                        <h3>Experience</h3>
                      </div>
                      <p style="text-align: justify;">Berbagai macam persoalan sudah kami pecahkan dengan prosedur yang efektif.</p>
                    </div>
                  </div>
                  <div class="col-lg-6 col-6">
                    <div class="about-card">
                      <div class="content"><i class="flaticon-help" style="color: #ffc221;"></i>
                        <h3>Quick Support</h3>
                      </div>
                      <p style="text-align: justify;">Dukungan setiap persoalan akan didampingi oleh satu supervisi yang expert.</p>
                    </div>
                  </div>
                  <?php
                }
                ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- SERVICES SECTION -->
    <div class="security-area pt-100 pb-70">
      <div class="container">
        <div class="section-title text-center">
          <span class="sp-color2"><?php echo htmlspecialchars(getContentByKey($conn, 'service', 'service_subtitle') ?: 'Layanan'); ?></span>
          <h2><?php echo htmlspecialchars(getContentByKey($conn, 'service', 'service_title') ?: 'Layanan Kami'); ?></h2>
        </div>
        
        <div class="row pt-45">
          <?php
          $services = getServices($conn);
          if (!empty($services)) {
            foreach ($services as $service) {
              if ($service['section_key'] === 'service_title' || $service['section_key'] === 'service_subtitle') {
                continue; // Skip the title and subtitle entries
              }
              ?>
              <div class="col-lg-4 col-sm-6">
                <div class="security-card">
                  <i><img src="<?php echo htmlspecialchars($service['image_url']); ?>" width="45" height="45" style="margin-top:-5px;"></i>
                  <h3><a href="<?php echo htmlspecialchars($service['link_url']); ?>"><?php echo htmlspecialchars($service['section_key']); ?></a></h3>
                  <p style="text-align: justify;"><?php echo htmlspecialchars($service['content_value']); ?></p>
                </div>
              </div>
              <?php
            }
          } else {
            // Fallback to a few static services if no dynamic content
            ?>
            <div class="col-lg-4 col-sm-6">
              <div class="security-card"><i><img src="assets/images/services/ico-jurnal.png" width="45" height="45" style="margin-top:-5px;"></i>
                <h3><a href="services/penerbitan-jurnal">Penerbitan Jurnal</a></h3>
                <p style="text-align: justify;">Pendampingan penerbitan Jurnal Nasional Terakreditasi (Sinta), WOS, Scopus, Emarld, Thomson, dll.</p>
              </div>
            </div>
            <div class="col-lg-4 col-sm-6">
              <div class="security-card"><i><img src="assets/images/services/ico-haki.png" width="45" height="45" style="margin-top:-5px;"></i>
                <h3><a href="services/penerbitan-hki">Penerbitan HKI</a></h3>
                <p style="text-align: justify;">Melayani penerbitan Hak Paten, HKI, Merk, dll dengan waktu yang cepat dan hasil yang memuaskan.</p>
              </div>
            </div>
            <?php
          }
          ?>
        </div> 
      </div>
    </div>
    
    <!-- CTA SECTION -->
    <div class="talk-area ptb-100">
      <div class="container">
        <div class="talk-content text-center">
          <div class="section-title text-center">
            <span class="sp-color1"><?php echo htmlspecialchars(getContentByKey($conn, 'cta', 'cta_subtitle') ?: 'Hubungi Kami'); ?></span>
            <h2><?php echo htmlspecialchars(getContentByKey($conn, 'cta', 'cta_title') ?: 'Kami melayani berbagai persoalan dengan solusi yang tepat'); ?></h2>
          </div>
          <a href="<?php echo htmlspecialchars(getContentByKey($conn, 'cta', 'cta_button_url') ?: 'https://wa.me/6287735426107'); ?>" target="_blank" class="default-btn btn-bg-one border-radius-5">
            <?php echo htmlspecialchars(getContentByKey($conn, 'cta', 'cta_button_text') ?: 'Whatsapp'); ?>
          </a>
        </div>
      </div>
    </div>    
    
    <!-- PRODUCTS SECTION -->
    <section class="technology-area-two pt-100 pb-70">
      <div class="container">
        <div class="section-title text-center">
          <span class="sp-color2"><?php echo htmlspecialchars(getContentByKey($conn, 'product', 'product_subtitle') ?: 'Produk Kami'); ?></span>
          <h2><?php echo htmlspecialchars(getContentByKey($conn, 'product', 'product_title') ?: 'Kami memberikan solusi terbaik dengan produk terpercaya dan berkualitas'); ?></h2>
        </div>
        <div class="row pt-45">
          <?php
          $products = getPageSectionContent($conn, 'product');
          if (!empty($products)) {
            foreach ($products as $product) {
              if ($product['section_key'] === 'product_title' || $product['section_key'] === 'product_subtitle') {
                continue; // Skip the title and subtitle entries
              }
              ?>
              <div class="col-lg-2 col-6">
                <div class="technology-card technology-card-color">
                  <img src="<?php echo htmlspecialchars($product['image_url']); ?>" width="50" height="50">
                  <h3><?php echo htmlspecialchars($product['section_key']); ?></h3>
                </div>
              </div>
              <?php
            }
          } else {
            // Fallback static products
            ?>
            <div class="col-lg-2 col-6">
              <div class="technology-card technology-card-color"><img src="assets/images/services/ico-kti-p.png" width="50" height="50">
                <h3>KTI</h3>
              </div>
            </div>
            <div class="col-lg-2 col-6">
              <div class="technology-card technology-card-color"><img src="assets/images/services/ico-jurnal-p.png" width="50" height="50">
                <h3>Journal</h3>
              </div>
            </div>
            <?php
          }
          ?>
        </div>
      </div>
    </section>
    
    <!-- TESTIMONIALS SECTION -->
    <section class="clients-area pt-100 pb-70">
      <div class="container">
        <div class="section-title text-center">
          <span class="sp-color1"><?php echo htmlspecialchars(getContentByKey($conn, 'testimonial', 'testimonial_subtitle') ?: 'Testimoni'); ?></span>
          <h2><?php echo htmlspecialchars(getContentByKey($conn, 'testimonial', 'testimonial_title') ?: 'Apa Kata Mereka?'); ?></h2>
        </div>
        <div class="clients-slider owl-carousel owl-theme pt-45">
          <?php
          $testimonials = getTestimonials($conn);
          if (!empty($testimonials)) {
            foreach ($testimonials as $testimonial) {
              if ($testimonial['section_key'] === 'testimonial_title' || $testimonial['section_key'] === 'testimonial_subtitle') {
                continue; // Skip the title and subtitle entries
              }
              ?>
              <div class="clients-content">
                <div class="content">
                  <img src="<?php echo htmlspecialchars($testimonial['image_url']); ?>" alt="<?php echo htmlspecialchars($testimonial['section_key']); ?>" loading="lazy">
                  <i class='bx bxs-quote-alt-left'></i>
                  <h3><?php echo htmlspecialchars($testimonial['section_key']); ?></h3>
                  <span><?php echo htmlspecialchars($testimonial['link_url']); ?></span>
                </div>
                <p><?php echo htmlspecialchars($testimonial['content_value']); ?></p>
              </div>
              <?php
            }
          } else {
            // Fallback testimonials
            ?>
            <div class="clients-content">
              <div class="content"><img src="assets/images/clients-img/testi-4.jpg" alt="Images" loading="lazy"><i class='bx bxs-quote-alt-left'></i>
                <h3>Bayu Saputra</h3><span>Mahasiswa</span>
              </div>
              <p>"Adanya tim Akademi Merdeka membantu saya dalam penerbitan jurnal dengan metode yang efektif, membuat saya cepat memahami."<br><br></p>
            </div>
            <?php
          }
          ?>
        </div>
      </div>
      <div class="client-circle">
        <div class="client-circle-1">
          <div class="circle"></div>
        </div>
        <!-- Other circles remain unchanged -->
        <div class="client-circle-7">
          <div class="circle"></div>
        </div>
      </div>
    </section>
    
    <!-- BLOG SECTION -->
    <div class="blog-area pt-100 pb-70">
      <div class="container">
        <div class="section-title text-center">
          <span class="sp-color2"><?php echo htmlspecialchars(getContentByKey($conn, 'blog', 'blog_subtitle') ?: 'Blog'); ?></span>
          <h2><?php echo htmlspecialchars(getContentByKey($conn, 'blog', 'blog_title') ?: 'Artikel Kami'); ?></h2>
        </div>
        <div class="row pt-45">
          <?php
          $blog_posts = getHomepagePosts($conn);
          if (!empty($blog_posts)) {
            foreach ($blog_posts as $post) {
              ?>
              <div class="col-lg-4 col-md-6">
                <div class="blog-card">
                  <div class="blog-img">
                    <a href="<?php echo htmlspecialchars($post['link_url']); ?>">
                      <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="<?php echo htmlspecialchars($post['section_key']); ?>" loading="lazy">
                    </a>
                    <div class="blog-tag">
                      <?php 
                      // For blog posts in blog section, content_value contains the blog content
                      // We can extract a date from it or use current date
                      $date = new DateTime();
                      if (preg_match('/(\d{1,2}) (\w{3}) (\d{4})/', $post['content_value'], $matches)) {
                        $date = DateTime::createFromFormat('d M Y', $matches[0]);
                      }
                      ?>
                      <h3><?php echo $date->format('d'); ?></h3>
                      <span><?php echo $date->format('M'); ?></span>
                    </div>
                  </div>
                  <div class="content">
                    <ul>
                      <li><a href="/"><i class='bx bxs-user'></i> by Admin</a></li>
                      <li><a href="/"><i class='bx bx-purchase-tag-alt'></i><?php echo htmlspecialchars($post['section_key']); ?></a></li>
                    </ul>
                    <h3><a href="<?php echo htmlspecialchars($post['link_url']); ?>"><?php echo htmlspecialchars($post['section_key']); ?></a></h3>
                    <p><?php echo htmlspecialchars(substr($post['content_value'], 0, 150)) . '...'; ?></p>
                    <a href="<?php echo htmlspecialchars($post['link_url']); ?>" class="read-btn">Selengkapnya <i class='bx bx-chevron-right'></i></a>
                  </div>
                </div>
              </div>
              <?php
            }
          } else {
            // Fallback blog posts
            ?>
            <div class="col-lg-4 col-md-6">
              <div class="blog-card">
                <div class="blog-img"><a href="blog/teknik-pembuatan-jurnal-artikel"><img src="assets/images/blog/blog-teknik-pembuatan-jurnal-artikel.jpg" alt="Blog Images" loading="lazy"></a>
                  <div class="blog-tag">
                    <h3>11</h3><span>Jan</span>
                  </div>
                </div>
                <div class="content">
                  <ul>
                    <li><a href="/"><i class='bx bxs-user'></i> by Admin</a></li>
                    <li><a href="/"><i class='bx bx-purchase-tag-alt'></i>Jurnal</a></li>
                  </ul>
                  <h3><a href="blog/teknik-pembuatan-jurnal-artikel">Teknik Pembuatan Jurnal/Artikel</a></h3>
                  <p>Jurnal merupakan sebuah publikasi periodik dalam bentuk artikel yang diterbitkan secara berkala, dalam hal ini biasanya jurnal diterbitkan pada interval waktu tertentu...</p>
                  <a href="blog/teknik-pembuatan-jurnal-artikel" class="read-btn">Selengkapnya <i class='bx bx-chevron-right'></i></a>
                </div>
              </div>
            </div>
            <?php
          }
          ?>
        </div>
      </div>
    </div>
    
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