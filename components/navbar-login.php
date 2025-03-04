<?php
/**
 * navbar-login.php - Special handling for navbar login link
 * 
 * This file replaces the dynamic login menu item in the navbar
 * with a direct link to the login page, ensuring the login system
 * remains independent from the dynamic content system.
 */

// Determine if user is logged in
$is_logged_in = isset($_SESSION['login_status']) && $_SESSION['login_status'] === true;

// Determine active class
$is_active = (basename($_SERVER['SCRIPT_NAME']) == 'login.php') ? 'active' : '';

// Show appropriate menu item based on login status
if ($is_logged_in) {
    // If logged in, show a link to the admin dashboard
    echo '<li class="nav-item">';
    echo '<a href="admin/index.php" class="nav-link ' . $is_active . '">';
    echo 'Dashboard <i class="bx bx-user-circle"></i>';
    echo '</a>';
    echo '</li>';
} else {
    // If not logged in, show login link
    echo '<li class="nav-item">';
    echo '<a href="login.php" class="nav-link ' . $is_active . '">';
    echo 'Login <i class="bx bx-log-in"></i>';
    echo '</a>';
    echo '</li>';
}
?>