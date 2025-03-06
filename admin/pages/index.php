<?php
// Start the session
session_start();

// Check if not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
  header("Location: ../login.php");
  exit;
}

// Include database connection
require_once('../config.php');
?>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Akademi Merdeka</title>
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Boxicons -->
  <link rel="stylesheet" href="../assets/css/boxicons.min.css">
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <!-- Custom Tailwind Config -->
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: {
              50: '#f0f9ff',
              100: '#e0f2fe',
              200: '#bae6fd',
              300: '#7dd3fc',
              400: '#38bdf8',
              500: '#0ea5e9',
              600: '#0284c7',
              700: '#0369a1',
              800: '#075985',
              900: '#0c4a6e',
            }
          }
        }
      }
    }
  </script>
</head>

<body class="bg-gray-50">
  <div class="min-h-screen flex flex-col lg:flex-row">
    <!-- Sidebar Component -->
    <?php include('../components/sidebar.php'); ?>

    <!-- Main Content -->
    <div class="flex-1 lg:ml-64">
      <!-- Top Bar -->
      <div class="bg-white p-4 shadow flex justify-between items-center">
        <h1 class="text-xl font-semibold text-gray-800">Dashboard</h1>
        <div class="flex items-center space-x-4">
          <div class="relative">
            <button class="text-gray-500 hover:text-gray-700">
              <i class='bx bx-bell text-xl'></i>
              <span class="absolute top-0 right-0 h-2 w-2 rounded-full bg-red-500"></span>
            </button>
          </div>
          <div class="flex items-center space-x-2">
            <span class="text-gray-600">Welcome, <?php echo htmlspecialchars($username); ?></span>
            <img class="w-8 h-8 rounded-full" src="../assets/images/team/pp-1.png" alt="Profile">
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>