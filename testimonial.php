<?php
// Include database connection
require_once('./config.php');

// Fetch testimonials data
$testimonials = [];
try {
  $stmt = $conn->query("SELECT * FROM testimonials WHERE is_active = 1 ORDER BY display_order ASC");
  $testimonials = $stmt->fetchAll();
} catch(PDOException $e) {
  // Handle error silently
}

// Fetch page settings
$pageSettings = [];
try {
  $stmt = $conn->query("SELECT * FROM testimonial_page_settings WHERE id = 1 LIMIT 1");
  $pageSettings = $stmt->fetch();
} catch(PDOException $e) {
  // Handle error silently
}

// Default values if no settings found
$pageTitle = $pageSettings['page_title'] ?? 'Testimoni';
$metaDescription = $pageSettings['meta_description'] ?? 'Testimoni dari pelanggan Akademi Merdeka';
$breadcrumbParent = $pageSettings['breadcrumb_parent'] ?? 'Tentang';
$breadcrumbCurrent = $pageSettings['breadcrumb_current'] ?? 'Testimoni';
$sectionTitle = $pageSettings['section_title'] ?? 'Testimoni Customer';
$sectionSubtitle = $pageSettings['section_subtitle'] ?? 'Apa Kata Mereka?';
?>
<!doctype html>
<html lang="id">
  <?php
  include('components/head.php');
  // Add custom meta description for this page
  echo '<meta name="description" content="' . htmlspecialchars($metaDescription) . '">';
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
    <div class="inner-banner">
      <div class="container">
        <div class="inner-title text-center">
          <h3><?php echo htmlspecialchars($pageTitle); ?></h3>
          <ul>
            <li><a href="/"><?php echo htmlspecialchars($breadcrumbParent); ?></a></li>
            <li><i class='bx bx-chevrons-right'></i></li>
            <li><?php echo htmlspecialchars($breadcrumbCurrent); ?></li>
          </ul>
        </div>
      </div>
      <div class="inner-shape"><img src="assets/images/shape/inner-shape.png" alt="Images"></div>
    </div>
    <section class="clients-area clients-area-two pt-100 pb-70">
      <div class="container">
        <div class="section-title text-center">
          <span class="sp-color2"><?php echo htmlspecialchars($sectionTitle); ?></span>
          <h2><?php echo htmlspecialchars($sectionSubtitle); ?></h2>
        </div>
        <div class="clients-slider owl-carousel owl-theme pt-45">
          <?php if (empty($testimonials)): ?>
            <div class="clients-content">
              <p>Belum ada testimoni yang tersedia.</p>
            </div>
          <?php else: ?>
            <?php foreach ($testimonials as $testimonial): ?>
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
          <?php endif; ?>
        </div>
      </div>
    </section>
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