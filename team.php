<!doctype html>
<html lang="id">
  <?php
  include('components/head.php');
  // Include database connection
  require_once('config.php');
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
          <h3>Tim</h3>
          <ul>
            <li><a href="/">Tentang</a></li>
            <li><i class='bx bx-chevrons-right'></i></li>
            <li>Tim</li>
          </ul>
        </div>
      </div>
      <div class="inner-shape"><img src="assets/images/shape/inner-shape.png" alt="Images"></div>
    </div>
    <div class="team-area pt-100 pb-70">
      <div class="container">
        <div class="section-title text-center"><span class="sp-color2">Tim</span>
          <h2>Tim Kami</h2>
        </div>
        <div class="row pt-45">
          <?php
          // Fetch team members from database
          try {
              $stmt = $conn->query("SELECT * FROM team_members WHERE is_active = 1 ORDER BY display_order, name");
              $team_members = $stmt->fetchAll();
              
              if (count($team_members) > 0) {
                  foreach ($team_members as $member) {
                      ?>
                      <div class="col-lg-4 col-md-6">
                        <div class="team-card">
                          <img src="<?php echo htmlspecialchars($member['image_path']); ?>" 
                               alt="<?php echo htmlspecialchars($member['name']); ?> - <?php echo htmlspecialchars($member['position']); ?>" 
                               loading="lazy">              
                          <div class="content">
                            <h3><?php echo htmlspecialchars($member['name']); ?></h3>
                            <span><?php echo htmlspecialchars($member['position']); ?></span>
                          </div>
                        </div>
                      </div>
                      <?php
                  }
              } else {
                  // If no team members found
                  echo '<div class="col-12 text-center">';
                  echo '<p>Data tim belum tersedia. Silakan kembali lagi nanti.</p>';
                  echo '</div>';
              }
          } catch(PDOException $e) {
              // If there's an error, show a message
              echo '<div class="col-12 text-center">';
              echo '<p>Maaf, data tim tidak tersedia saat ini.</p>';
              echo '</div>';
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