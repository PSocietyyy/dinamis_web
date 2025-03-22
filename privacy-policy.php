<?php
// Include database connection
require_once('./config.php');

// Fetch privacy policy page settings
$pageSettings = [];
try {
    $stmt = $conn->query("SELECT * FROM privacy_policy_settings WHERE id = 1 LIMIT 1");
    $pageSettings = $stmt->fetch();
} catch(PDOException $e) {
    // Handle error silently
}

// Fetch active privacy policy sections
$privacyPolicies = [];
try {
    $stmt = $conn->query("SELECT * FROM privacy_policy WHERE is_active = 1 ORDER BY display_order ASC");
    $privacyPolicies = $stmt->fetchAll();
} catch(PDOException $e) {
    // Handle error silently
}
?>

<!doctype html>
<html lang="id">
  <?php
  // Dynamic SEO metadata
  $pageTitle = $pageSettings['seo_title'] ?? 'Kebijakan Privasi | Akademi Merdeka';
  $pageDescription = $pageSettings['seo_description'] ?? '';
  $pageKeywords = $pageSettings['seo_keywords'] ?? '';
  
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
    <div class="inner-banner">
      <div class="container">
        <div class="inner-title text-center">
          <h3><?php echo htmlspecialchars($pageSettings['inner_title'] ?? 'Kebijakan Privasi'); ?></h3>
          <ul>
            <li><a href="<?php echo htmlspecialchars($pageSettings['breadcrumb_parent_link'] ?? '/'); ?>"><?php echo htmlspecialchars($pageSettings['breadcrumb_parent'] ?? 'Tentang'); ?></a></li>
            <li><i class='bx bx-chevrons-right'></i></li>
            <li><?php echo htmlspecialchars($pageSettings['breadcrumb_current'] ?? 'Kebijakan Privasi'); ?></li>
          </ul>
        </div>
      </div>
      <div class="inner-shape"><img src="<?php echo htmlspecialchars($pageSettings['banner_image'] ?? 'assets/images/shape/inner-shape.png'); ?>" alt="Images"></div>
    </div>
    <div class="privacy-policy-area pt-100 pb-70">
      <div class="container">
        <div class="section-title text-center">
          <span class="sp-color2"><?php echo htmlspecialchars($pageSettings['subtitle'] ?? 'Kebijakan Privasi'); ?></span>
          <h2><?php echo htmlspecialchars($pageSettings['title'] ?? 'Akademi Merdeka'); ?></h2>
        </div>
        <div class="row pt-45">
          <div class="col-lg-12">
            <div class="single-content">
              <p style="text-align: justify;"> Kami berkomitmen untuk menjaga privasi pengunjung maupun pengguna website kami, kebijakan ini memberitahukan bagaimana kami mengumpulkan, menggunakan dan mengungkapkan data yang telah kami peroleh dari pengunjung website kami. </p>              
            </div>
            
            <?php foreach($privacyPolicies as $policy): ?>
            <div class="single-content">
              <h3><?php echo htmlspecialchars($policy['title']); ?></h3>
              <p style="text-align: justify;"><?php echo $policy['content']; ?></p>
            </div>
            <?php endforeach; ?>
            
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