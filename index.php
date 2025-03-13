<!doctype html>
<html lang="id">
  <?php
  include('components/head.php');
  require_once('./config.php'); // Include database connection
  
  // Fetch banner data
  $banner = [];
  try {
    $stmt = $conn->query("SELECT * FROM home_banner WHERE id = 1 LIMIT 1");
    $banner = $stmt->fetch();
  } catch(PDOException $e) {
    // Handle error silently
  }

  // Fetch stats data
  $stats = [];
  try {
    $stmt = $conn->query("SELECT * FROM home_stats WHERE is_active = 1 ORDER BY display_order ASC");
    $stats = $stmt->fetchAll();
  } catch(PDOException $e) {
    // Handle error silently
  }
  
  // Fetch about section data
  $about = [];
  try {
    $stmt = $conn->query("SELECT * FROM home_about WHERE id = 1 LIMIT 1");
    $about = $stmt->fetch();
  } catch(PDOException $e) {
    // Handle error silently
  }
  
  // Fetch CTA section data
  $cta = [];
  try {
    $stmt = $conn->query("SELECT * FROM home_cta WHERE id = 1 LIMIT 1");
    $cta = $stmt->fetch();
  } catch(PDOException $e) {
    // Handle error silently
  }
  
  // Fetch products section data
  $productsSection = [];
  try {
    $stmt = $conn->query("SELECT * FROM home_products_section WHERE id = 1 LIMIT 1");
    $productsSection = $stmt->fetch();
  } catch(PDOException $e) {
    // Handle error silently
  }
  
  // Fetch products
  $products = [];
  try {
    $stmt = $conn->query("SELECT * FROM home_products WHERE is_active = 1 ORDER BY display_order ASC");
    $products = $stmt->fetchAll();
  } catch(PDOException $e) {
    // Handle error silently
  }
  
  // Fetch testimonials section data
  $testimonialsSection = [];
  try {
    $stmt = $conn->query("SELECT * FROM home_testimonials_section WHERE id = 1 LIMIT 1");
    $testimonialsSection = $stmt->fetch();
  } catch(PDOException $e) {
    // Handle error silently
  }
  
  // Fetch testimonials
  $testimonials = [];
  try {
    $stmt = $conn->query("SELECT * FROM home_testimonials WHERE is_active = 1 ORDER BY display_order ASC");
    $testimonials = $stmt->fetchAll();
  } catch(PDOException $e) {
    // Handle error silently
  }
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
    <!-- Banner Area - DYNAMIC -->
    <div class="banner-area-two">
      <div class="container-fluid">
        <div class="container-max">
          <div class="row align-items-center">
            <div class="col-lg-5">
              <div class="banner-content">
                <h1><?php echo htmlspecialchars($banner['title'] ?? 'Platform Academic Digital With Excellent Quality'); ?></h1>
                <p><?php echo htmlspecialchars($banner['subtitle'] ?? 'Platform Akademi Merdeka membantu setiap insan akademisi dengan pelayanan yang eksklusif'); ?></p>
                <div class="banner-btn">
                  <a href="<?php echo htmlspecialchars($banner['button1_url'] ?? '#'); ?>" class="default-btn btn-bg-two border-radius-50">
                    <?php echo htmlspecialchars($banner['button1_text'] ?? 'Learn More'); ?> <i class='bx bx-chevron-right'></i>
                  </a>
                  <a href="<?php echo htmlspecialchars($banner['button2_url'] ?? 'https://wa.me/6287735426107'); ?>" target="_blank" class="default-btn btn-bg-one border-radius-50 ml-20">
                    <?php echo htmlspecialchars($banner['button2_text'] ?? 'Whatsapp'); ?> <i class='bx bx-chevron-right'></i>
                  </a>
                </div>
              </div>
            </div>
            <div class="col-lg-7">
              <div class="banner-img">
                <img src="<?php echo htmlspecialchars($banner['banner_image'] ?? 'assets/images/home-three/home-main-pic.png'); ?>" alt="Banner Image">
                <div class="banner-img-shape"><img src="<?php echo htmlspecialchars($banner['shape_image'] ?? 'assets/images/home-three/home-three-shape.png'); ?>" alt="Shape" loading="lazy"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Stats Counters - DYNAMIC -->
      <div class="container" style="padding-top: 70px;">
        <div class="banner-sub-slider owl-carousel owl-theme">
          <?php foreach($stats as $stat): ?>
          <div class="banner-sub-item">
            <img src="<?php echo htmlspecialchars($stat['image_path']); ?>" alt="<?php echo htmlspecialchars($stat['count_label']); ?>" loading="lazy">
            <div class="content">
              <h3><?php echo htmlspecialchars($stat['count_number']); ?></h3>
              <span><?php echo htmlspecialchars($stat['count_label']); ?></span>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <!-- About Area - DYNAMIC -->
    <div class="about-area about-bg pt-100 pb-70">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-6">
            <div class="about-img-2">
              <img src="<?php echo htmlspecialchars($about['image_path'] ?? 'assets/images/about/home-about.png'); ?>" alt="About Images" loading="lazy">
            </div>
          </div>
          <div class="col-lg-6">
            <div class="about-content-2 ml-20">
              <div class="section-title">
                <span class="sp-color1"><?php echo htmlspecialchars($about['subtitle'] ?? 'About Us'); ?></span>
                <h2><?php echo htmlspecialchars($about['title'] ?? 'Tentang Kita'); ?></h2>
                <p style="text-align: justify;"><?php echo htmlspecialchars($about['content'] ?? 'Akademi Merdeka mempunyai ruang lingkup dalam bidang akademisi yang tujuannya ialah membantu setiap insan akademisi dengan berbagai problematika yang sedang dihadapi.'); ?></p>
              </div>
              <div class="row">
                <div class="col-lg-6 col-6">
                  <div class="about-card">
                    <div class="content"><i class="<?php echo htmlspecialchars($about['card1_icon'] ?? 'flaticon-practice'); ?>" style="color: #ffc221;"></i>
                      <h3><?php echo htmlspecialchars($about['card1_title'] ?? 'Experience'); ?></h3>
                    </div>
                    <p style="text-align: justify;"><?php echo htmlspecialchars($about['card1_text'] ?? 'Berbagai macam persoalan sudah kami pecahkan dengan prosedur yang efektif.'); ?></p>
                  </div>
                </div>
                <div class="col-lg-6 col-6">
                  <div class="about-card">
                    <div class="content"><i class="<?php echo htmlspecialchars($about['card2_icon'] ?? 'flaticon-help'); ?>" style="color: #ffc221;"></i>
                      <h3><?php echo htmlspecialchars($about['card2_title'] ?? 'Quick Support'); ?></h3>
                    </div>
                    <p style="text-align: justify;"><?php echo htmlspecialchars($about['card2_text'] ?? 'Dukungan setiap persoalan akan didampingi oleh satu supervisi yang expert.'); ?></p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Services Area - STATIC (as requested) -->
    <div class="security-area pt-100 pb-70">
      <div class="container">
        <div class="section-title text-center"><span class="sp-color2">Layanan</span>
          <h2>Layanan Kami</h2>
        </div>
        
        <div class="row pt-45">
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
            <div class="col-lg-4 col-sm-6">
              <div class="security-card"><i><img src="assets/images/services/ico-statistik.png" width="45" height="45" style="margin-top:-5px;"></i>
                <h3><a href="services/pengolahan-statistik">Pengolahan Statistik</a></h3>
                <p style="text-align: justify;">Pendampingan pengolahan data dengan software SAS, R-Studio, SPSS dari berbagai macam analisis sesuai dengan metodologi.</p>
              </div>
            </div>
            <div class="col-lg-4 col-sm-6">
              <div class="security-card"><i><img src="assets/images/services/ico-tkbi.png" width="45" height="45" style="margin-top:-5px;"></i>
                <h3><a href="services/pendampingan-tkda">Pendampingan TKDA/TKBI</a></h3>
                <p style="text-align: justify;">Pelayanan pendampingan TKDA/TKBI agar lolos passing grade diberbagai tes seperti UNPAD, UNAIR, PLTI, Bappenas.</p>
              </div>
            </div>
            <div class="col-lg-4 col-sm-6">
              <div class="security-card"><i><img src="assets/images/services/ico-ojs.png" width="45" height="45" style="margin-top:-5px;"></i>
                <h3><a href="services/pendampingan-ojs">Pendampingan OJS</a></h3>
                <p style="text-align: justify;">Pembuatan OJS akan didampingi oleh supervisi yang expert dalam menyiapkan OJS menarik dan responsive.</p>
              </div>
            </div>           
            <div class="col-lg-4 col-sm-6">
                <div class="security-card"><i><img src="assets/images/services/ico-mediaajar.png" width="45" height="45" style="margin-top:-5px;"></i>
                <h3><a href="services/media-ajar">Pembuatan Media Ajar</a></h3>
                <p style="text-align: justify;">Berbagai media ajar yang dibutuhkan diberbagai mata kuliah, atau mata pelajaran baik digital atau alat peraga.</p>
                </div>
            </div>
            <div class="col-lg-4 col-sm-6">
                <div class="security-card"><i><img src="assets/images/services/ico-kti.png" width="45" height="45" style="margin-top:-5px;"></i>
                <h3><a href="services/konversi-kti">Konversi KTI</a></h3>
                <p style="text-align: justify;">Melayani pendampingan dalam mengkonversi karya tulis ilmiah menjadi Book Chapter, Reference Book, Monograf.</p>
                </div>
            </div>
            <div class="col-lg-4 col-sm-6">
              <div class="security-card"><i><img src="assets/images/services/ico-elearning.png" width="45" height="45" style="margin-top:-5px;"></i>
              <h3><a href="services/elearning">E-Learning</a></h3>
              <p style="text-align: justify;">Pembuatan berbagai macam jenis platform pembelajaran jarak jauh seperti Moodle, Joomla, dan lainnya.</p>
              </div>
          </div>            
        </div> 
      </div>
    </div>
    <!-- Call to Action - DYNAMIC -->
    <div class="talk-area ptb-100">
        <div class="container">
          <div class="talk-content text-center">
            <div class="section-title text-center">
              <span class="sp-color1"><?php echo htmlspecialchars($cta['subtitle'] ?? 'Hubungi Kami'); ?></span>
              <h2><?php echo htmlspecialchars($cta['title'] ?? 'Kami melayani berbagai persoalan dengan solusi yang tepat'); ?></h2>
            </div>
            <a href="<?php echo htmlspecialchars($cta['button_url'] ?? 'https://wa.me/6287735426107'); ?>" target="_blank" class="default-btn btn-bg-one border-radius-5">
              <?php echo htmlspecialchars($cta['button_text'] ?? 'Whatsapp'); ?>
            </a>
          </div>
        </div>
      </div>    
           
    <!-- Products Section - DYNAMIC -->
    <section class="technology-area-two pt-100 pb-70">
      <div class="container">
        <div class="section-title text-center">
          <span class="sp-color2"><?php echo htmlspecialchars($productsSection['subtitle'] ?? 'Produk Kami'); ?></span>
          <h2><?php echo htmlspecialchars($productsSection['title'] ?? 'Kami memberikan solusi terbaik dengan produk terpercaya dan berkualitas'); ?></h2>
        </div>
        <div class="row pt-45">
          <?php foreach($products as $product): ?>
          <div class="col-lg-2 col-6">
            <div class="technology-card technology-card-color">
              <img src="<?php echo htmlspecialchars($product['image_path']); ?>" width="50" height="50">
              <h3><?php echo htmlspecialchars($product['title']); ?></h3>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    
    <!-- Testimonials - DYNAMIC -->
    <section class="clients-area pt-100 pb-70">
      <div class="container">
        <div class="section-title text-center">
          <span class="sp-color1"><?php echo htmlspecialchars($testimonialsSection['subtitle'] ?? 'Testimoni'); ?></span>
          <h2><?php echo htmlspecialchars($testimonialsSection['title'] ?? 'Apa Kata Mereka?'); ?></h2>
        </div>
        <div class="clients-slider owl-carousel owl-theme pt-45">
          <?php foreach($testimonials as $testimonial): ?>
          <div class="clients-content">
            <div class="content">
              <img src="<?php echo htmlspecialchars($testimonial['client_image']); ?>" alt="<?php echo htmlspecialchars($testimonial['client_name']); ?>" loading="lazy">
              <i class='bx bxs-quote-alt-left'></i>
              <h3><?php echo htmlspecialchars($testimonial['client_name']); ?></h3>
              <span><?php echo htmlspecialchars($testimonial['client_position']); ?></span>
            </div>
            <p><?php echo htmlspecialchars($testimonial['testimonial_text']); ?></p>
          </div>
          <?php endforeach; ?>
        </div>
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
    
    <!-- Blog Section - STATIC (as requested) -->
    <div class="blog-area pt-100 pb-70">
      <div class="container">
        <div class="section-title text-center"><span class="sp-color2">Blog</span>
          <h2>Artikel Kami</h2>
        </div>
        <div class="row pt-45">          
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
                <p>Jurnal merupakan sebuah publikasi periodik dalam bentuk artikel yang diterbitkan secara berkala, dalam hal ini biasanya jurnal diterbitkan pada interval waktu tertentu...</p><a href="blog/teknik-pembuatan-jurnal-artikel" class="read-btn">Selengkapnya <i class='bx bx-chevron-right'></i></a>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 offset-lg-0 offset-md-3">
            <div class="blog-card">
              <div class="blog-img"><a href="blog/langkah-langkah-mendapatkan-hak-cipta"><img src="assets/images/blog/blog-langkah-langkah-mendapatkan-hak-cipta.jpg" alt="Blog Images" loading="lazy"></a>
                <div class="blog-tag">
                  <h3>08</h3><span>Jan</span>
                </div>
              </div>
              <div class="content">
                <ul>
                  <li><a href="/"><i class='bx bxs-user'></i> by Admin</a></li>
                  <li><a href="/"><i class='bx bx-purchase-tag-alt'></i>HKI</a></li>
                </ul>
                <h3><a href="blog/langkah-langkah-mendapatkan-hak-cipta">Langkah Langkah Mendapatkan Hak Cipta</a></h3>
                <p>Hak Cipta atau copyright adalah hak eksklusif yang diberikan kepada pencipta atau pemegang hak cipta untuk mengatur penggunaan hasil penuangan gagasan...</p><a href="blog/langkah-langkah-mendapatkan-hak-cipta" class="read-btn">Selengkapnya <i class='bx bx-chevron-right'></i></a>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6">
            <div class="blog-card">
              <div class="blog-img"><a href="blog/tips-konversi-kti-menjadi-buku-referensi-book-chapter"><img src="assets/images/blog/blog-tips-konversi-kti-menjadi-buku-referensi-book-chapter.jpg" alt="Blog Images" loading="lazy"></a>
                <div class="blog-tag">
                  <h3>06</h3><span>Jan</span>
                </div>
              </div>
              <div class="content">
                <ul>
                  <li><a href="/"><i class='bx bxs-user'></i> by Admin</a></li>
                  <li><a href="/"><i class='bx bx-purchase-tag-alt'></i>KTI</a></li>
                </ul>
                <h3><a href="blog/tips-konversi-kti-menjadi-buku-referensi-book-chapter">Tips Konversi KTI Menjadi Buku Referensi/Book Chapter</a></h3>
                <p>KTI merupakan sebuah rangkaian informasi penting yang dapat digunakan sebagai bahan untuk memecahkan persoalan praktis di lapangan. Tentu sangat disayangkan...</p><a href="blog/tips-konversi-kti-menjadi-buku-referensi-book-chapter" class="read-btn">Selengkapnya <i class='bx bx-chevron-right'></i></a>
              </div>
            </div>
          </div>
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