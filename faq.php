<?php
require_once('./config.php'); // Include database connection

// Fetch FAQ banner data
$banner = [];
try {
    $stmt = $conn->prepare("SELECT * FROM faq_banners WHERE page_slug = :slug LIMIT 1");
    $pageSlug = 'faq';
    $stmt->bindParam(':slug', $pageSlug);
    $stmt->execute();
    $banner = $stmt->fetch();
    
    // Set defaults if no banner found
    if (!$banner) {
        $banner = [
            'title' => 'FAQ',
            'breadcrumb_text' => 'FAQ',
            'banner_image' => 'assets/images/shape/inner-shape.png',
            'faq_title' => 'Frequently Asked Questions',
            'faq_subtitle' => 'Beberapa pertanyaan yang sering disampaikan'
        ];
    }
} catch(PDOException $e) {
    // Handle error silently
    $banner = [
        'title' => 'FAQ',
        'breadcrumb_text' => 'FAQ',
        'banner_image' => 'assets/images/shape/inner-shape.png',
        'faq_title' => 'Frequently Asked Questions',
        'faq_subtitle' => 'Beberapa pertanyaan yang sering disampaikan'
    ];
}

// Fetch FAQ items for left column
$leftFaqs = [];
try {
    $stmt = $conn->query("SELECT * FROM faq_items WHERE is_active = 1 AND column_position = 1 ORDER BY display_order ASC");
    $leftFaqs = $stmt->fetchAll();
} catch(PDOException $e) {
    // Handle error silently
}

// Fetch FAQ items for right column
$rightFaqs = [];
try {
    $stmt = $conn->query("SELECT * FROM faq_items WHERE is_active = 1 AND column_position = 2 ORDER BY display_order ASC");
    $rightFaqs = $stmt->fetchAll();
} catch(PDOException $e) {
    // Handle error silently
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
    <div class="inner-banner">
      <div class="container">
        <div class="inner-title text-center">
          <h3><?php echo htmlspecialchars($banner['title']); ?></h3>
          <ul>
            <li><a href="/">Tentang</a></li>
            <li><i class='bx bx-chevrons-right'></i></li>
            <li><?php echo htmlspecialchars($banner['breadcrumb_text']); ?></li>
          </ul>
        </div>
      </div>
      <div class="inner-shape"><img src="<?php echo htmlspecialchars($banner['banner_image']); ?>" alt="Banner Background"></div>
    </div>
    <div class="faq-area pt-100 pb-70">
      <div class="container">
        <div class="section-title text-center">
          <h2><?php echo htmlspecialchars($banner['faq_title']); ?></h2>
          <p class="margin-auto"><?php echo htmlspecialchars($banner['faq_subtitle']); ?></p>
        </div>
        <div class="row pt-45">
          <div class="col-lg-6">
            <div class="faq-content">
              <div class="faq-accordion">
                <ul class="accordion">
                  <?php foreach($leftFaqs as $index => $faq): ?>
                  <li class="accordion-item">
                    <a class="accordion-title" href="javascript:void(0)">
                      <i class='bx <?php echo $index === 0 ? 'bx-minus' : 'bx-plus'; ?>'></i> 
                      <?php echo htmlspecialchars($faq['question']); ?>
                    </a>
                    <div class="accordion-content <?php echo $index === 0 ? 'show' : ''; ?>">
                      <p><?php echo htmlspecialchars($faq['answer']); ?></p>
                    </div>
                  </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="faq-content">
              <div class="faq-accordion">
                <ul class="accordion">
                  <?php foreach($rightFaqs as $index => $faq): ?>
                  <li class="accordion-item">
                    <a class="accordion-title" href="javascript:void(0)">
                      <i class='bx <?php echo $index === 0 ? 'bx-minus' : 'bx-plus'; ?>'></i> 
                      <?php echo htmlspecialchars($faq['question']); ?>
                    </a>
                    <div class="accordion-content <?php echo $index === 0 ? 'show' : ''; ?>">
                      <p><?php echo htmlspecialchars($faq['answer']); ?></p>
                    </div>
                  </li>
                  <?php endforeach; ?>
                </ul>
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