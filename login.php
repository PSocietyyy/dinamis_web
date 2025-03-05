<!doctype html>
<html lang="id">
  <?php
  include('components/head.php');
  // Start the session
  session_start();
  
  // Check if already logged in
  if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: admin/index.php");
    exit;
  }
  
  // Login processing
  $error = "";
  if($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Simple validation for demonstration
    if($username === 'admin' && $password === 'admin123') {
      $_SESSION['logged_in'] = true;
      $_SESSION['username'] = $username;
      header("Location: admin/index.php");
      exit;
    } else {
      $error = "Username atau password salah!";
    }
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
    <div class="inner-banner">
      <div class="container">
        <div class="inner-title text-center">
          <h3>Login Admin</h3>
          <ul>
            <li><a href="/">Home</a></li>
            <li><i class='bx bx-chevrons-right'></i></li>
            <li>Login</li>
          </ul>
        </div>
      </div>
      <div class="inner-shape"><img src="assets/images/shape/inner-shape.png" alt="Images"></div>
    </div>
    <div class="contact-form-area pt-100 pb-70">
      <div class="container">
        <div class="section-title text-center">
          <h2>Login Admin</h2>
          <p>Silahkan masukkan username dan password Anda</p>
        </div>
        <div class="row pt-45 justify-content-center">
          <div class="col-lg-6">
            <div class="contact-form">
              <?php if(!empty($error)): ?>
              <div class="alert alert-danger" role="alert">
                <?php echo $error; ?>
              </div>
              <?php endif; ?>
              <form method="POST" action="">
                <div class="row">
                  <div class="col-lg-12">
                    <div class="form-group">
                      <label>Username <span>*</span></label>
                      <input type="text" name="username" class="form-control" required placeholder="Username">
                    </div>
                  </div>
                  <div class="col-lg-12">
                    <div class="form-group">
                      <label>Password <span>*</span></label>
                      <input type="password" name="password" class="form-control" required placeholder="Password">
                    </div>
                  </div>
                  <div class="col-lg-12 text-center">
                    <button type="submit" class="default-btn btn-bg-two border-radius-50">
                      Login <i class='bx bx-chevron-right'></i>
                    </button>
                  </div>
                </div>
              </form>
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