<?php
/**
 * Database Configuration File
 * 
 * This file contains database connection settings
 * and error handling configuration.
 */

// Database credentials
$db_host     = 'localhost';     // Database host (usually localhost)
$db_name     = 'db_dinamis';   // Database name
$db_user     = 'root';          // Database username
$db_password = 'ususgysjjsu7';              // Database password

// Create database connection
$conn = mysqli_connect($db_host, $db_user, $db_password, $db_name);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set character set
mysqli_set_charset($conn, "utf8");

// Error reporting
// Comment out in production for security reasons
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Optional: Set timezone
date_default_timezone_set('Asia/Jakarta');

/**
 * Custom function to sanitize user input
 * 
 * @param string $data Input data to sanitize
 * @return string Sanitized data
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Create uploads directory if it doesn't exist
$upload_dir = __DIR__ . '/assets/images/uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}
?>