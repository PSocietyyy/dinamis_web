<?php
// login.php - Halaman login dengan tampilan yang diperbaiki

// Memulai session
session_start();

// Impor konfigurasi database
require_once 'config.php';

/**
 * Fungsi untuk melakukan login
 */
function doLogin($username, $password, $conn) {
    // Membersihkan input untuk mencegah SQL Injection
    $username = mysqli_real_escape_string($conn, $username);
    
    // Query untuk mencari user
    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Verifikasi password (untuk produksi, gunakan password_hash dan password_verify)
        if ($password == $user['password']) {
            // Update waktu login terakhir
            $update_sql = "UPDATE users SET last_login = NOW() WHERE id = {$user['id']}";
            mysqli_query($conn, $update_sql);
            
            // Set session
            $_SESSION['login_status'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'] ?? 'user';
            $_SESSION['nama'] = $user['nama_lengkap'] ?? $user['username'];
            $_SESSION['login_time'] = time();
            
            return array(
                'status' => true,
                'message' => 'Login berhasil'
            );
        } else {
            return array(
                'status' => false,
                'message' => 'Password salah!'
            );
        }
    } else {
        return array(
            'status' => false,
            'message' => 'Username tidak ditemukan!'
        );
    }
}

// Jika sudah login, redirect ke halaman admin
if (isset($_SESSION['login_status']) && $_SESSION['login_status'] === true) {
    header("Location: admin/index.php");
    exit;
}

// Proses Login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['username']) && !empty($_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        $result = doLogin($username, $password, $conn);
        
        if ($result['status']) {
            // Redirect ke admin/index.php
            header("Location: admin/index.php");
            exit;
        } else {
            $_SESSION['error'] = $result['message'];
        }
    } else {
        $_SESSION['error'] = "Username dan password harus diisi!";
    }
}

// Define a variable to indicate this is not a dynamic content page
$is_static_page = true;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Login - Akademi Merdeka</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Include minimal necessary CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
    <style>
        :root {
            --primary-color: #3498db;
            --primary-dark: #2980b9;
            --secondary-color: #2ecc71;
            --danger-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #34495e;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --border-radius: 6px;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #3498db, #8e44ad);
            background-attachment: fixed;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        .navbar {
            background-color: rgba(255, 255, 255, 0.95);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 10px 0;
            text-align: center;
        }
        
        .navbar a {
            text-decoration: none;
        }
        
        .navbar img {
            height: 64px;
            vertical-align: middle;
        }
        
        .content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .login-container {
            background-color: white;
            padding: 2.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            width: 380px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .login-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-header h2 {
            color: var(--dark-color);
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .login-logo {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .form-group {
            margin-bottom: 1.2rem;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark-color);
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
        }
        
        input[type=text], input[type=password] {
            width: 100%;
            padding: 12px 12px 12px 40px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        
        input[type=text]:focus, input[type=password]:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            outline: none;
        }
        
        button {
            background-color: var(--primary-color);
            color: white;
            padding: 14px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            width: 100%;
            font-size: 1rem;
            font-weight: bold;
            transition: background-color 0.3s, transform 0.2s;
        }
        
        button:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        button:active {
            transform: translateY(0);
        }
        
        .error {
            color: var(--danger-color);
            background-color: rgba(231, 76, 60, 0.1);
            border-left: 3px solid var(--danger-color);
            padding: 12px;
            margin: 15px 0;
            border-radius: 0 var(--border-radius) var(--border-radius) 0;
            font-size: 0.9rem;
        }
        
        .form-footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eee;
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .form-footer p {
            margin-bottom: 0.5rem;
        }
        
        .credentials {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: var(--border-radius);
            font-family: monospace;
            color: #666;
            font-size: 0.85rem;
        }
        
        .back-to-site {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
        }
        
        .back-to-site a {
            color: var(--light-color);
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .back-to-site a:hover {
            color: white;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Simple static navbar with logo -->
    <div class="navbar">
        <a href="/">
            <img src="assets/images/logos/logo-akademi-merdeka.png" alt="Akademi Merdeka Logo">
        </a>
    </div>

    <div class="content">
        <div class="login-container">
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h2>Login System</h2>
                <p>Masukkan kredensial untuk akses dashboard</p>
            </div>
            
            <?php if (isset($_SESSION['error'])) { ?>
                <div class="error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php } ?>
            
            <form action="" method="post">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-group">
                        <div class="input-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <input type="text" id="username" name="username" placeholder="Masukkan username" required autofocus>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-group">
                        <div class="input-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                        <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                    </div>
                </div>
                
                <button type="submit">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
            
            <div class="form-footer">
                <p>Default Login Credentials</p>
                <div class="credentials">
                    Username: admin<br>
                    Password: admin123
                </div>
            </div>
        </div>
    </div>
    
    <div class="back-to-site">
        <a href="index.php"><i class="fas fa-arrow-left"></i> Kembali ke Beranda</a>
    </div>
</body>
</html>
<?php
// Tutup koneksi database
mysqli_close($conn);
?>