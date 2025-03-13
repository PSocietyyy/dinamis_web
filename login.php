<?php
// Start the session di awal file
session_start();

// Include database connection
require_once('./config.php');

// Login processing
$error = "";
if($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $_POST['username'];
  $password = $_POST['password'];
  
  try {
    // Query to find the user
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    // Check if user exists
    if($stmt->rowCount() > 0) {
      $user = $stmt->fetch();
      
      // Check password - handles both hashed and non-hashed passwords
      if(password_verify($password, $user['password'])) {
        // Password is hashed and verified
        $passwordIsValid = true;
      } else if($password === $user['password']) {
        // Legacy password (not hashed)
        $passwordIsValid = true;
        
        // Upgrade to a hashed password for better security
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $updateStmt = $conn->prepare("UPDATE users SET password = :password WHERE id = :id");
        $updateStmt->bindParam(':password', $hashedPassword);
        $updateStmt->bindParam(':id', $user['id']);
        $updateStmt->execute();
      } else {
        $passwordIsValid = false;
      }
      
      if($passwordIsValid) {
        // Set session variables
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        
        // Redirect to admin page
        header("Location: admin/index.php");
        exit;
      } else {
        $error = "Password yang Anda masukkan salah.";
      }
    } else {
      $error = "Username tidak ditemukan.";
    }
  } catch(PDOException $e) {
    $error = "Terjadi kesalahan sistem: " . $e->getMessage();
  }
}
?>
<!doctype html>
<html lang="id">
  <?php include('components/head.php'); ?>
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