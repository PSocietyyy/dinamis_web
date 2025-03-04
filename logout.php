<?php
// logout.php - Proses logout user
session_start();

// Hapus semua variabel session
$_SESSION = array();

// Hapus session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hancurkan session
session_destroy();

// Redirect ke halaman login dengan parameter untuk mencegah caching
header("Location: login.php?logout=" . time());
exit;
?>