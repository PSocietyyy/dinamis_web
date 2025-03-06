<?php
// Start the session
session_start();

// Check if not logged in
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit;
}

// Include database connection
require_once('../config.php');

// Get database stats
$stats = [
    'total_users' => 0,
    'total_pages' => 0,
    'total_posts' => 0,
    'total_services' => 0
];

try {
    // Count users
    $stmt = $conn->query("SELECT COUNT(*) FROM users");
    $stats['total_users'] = $stmt->fetchColumn();
    
    // Count navbar items (as pages)
    $stmt = $conn->query("SELECT COUNT(*) FROM navbar_items");
    $stats['total_pages'] = $stmt->fetchColumn();
    
    // Get posts count if table exists
    try {
        $stmt = $conn->query("SELECT COUNT(*) FROM posts");
        $stats['total_posts'] = $stmt->fetchColumn();
    } catch(PDOException $e) {
        // Posts table doesn't exist
    }
    
    // Count footer links (as services)
    $stmt = $conn->query("SELECT COUNT(*) FROM footer_links WHERE section = 'layanan'");
    $stats['total_services'] = $stmt->fetchColumn();
} catch(PDOException $e) {
    // Error getting stats
}

// Get recent activity (example data)
$recent_activity = [
    ['type' => 'update', 'item' => 'Navbar Settings', 'user' => 'Admin', 'time' => '2 hours ago'],
    ['type' => 'create', 'item' => 'New Footer Link', 'user' => 'Admin', 'time' => '1 day ago'],
    ['type' => 'delete', 'item' => 'Old Post', 'user' => 'Editor', 'time' => '3 days ago'],
    ['type' => 'update', 'item' => 'Service Page', 'user' => 'Admin', 'time' => '5 days ago'],
];

// Get username
$username = $_SESSION['username'] ?? 'Admin';
?>

<!doctype html>
<html lang="id">
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
        <?php include('components/sidebar.php'); ?>
        
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
            
            <!-- Dashboard Content -->
            <div class="p-6">
                <!-- Welcome Card -->
                <div class="bg-gradient-to-r from-blue-500 to-blue-700 rounded-lg shadow-md mb-6 text-white">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-2xl font-bold mb-1">Welcome back, <?php echo htmlspecialchars($username); ?>!</h2>
                                <p class="text-blue-100">Here's what's happening with your website today.</p>
                            </div>
                            <div class="hidden md:block">
                                <img src="../assets/images/logos/logo-2.png" alt="Logo" class="h-16">
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <a href="#" class="inline-flex items-center px-4 py-2 bg-white text-blue-600 rounded-lg shadow-sm hover:bg-blue-50 transition-colors">
                                <i class='bx bx-refresh mr-2'></i> Refresh Data
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Users</p>
                                <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['total_users']; ?></h3>
                            </div>
                            <div class="bg-blue-100 p-3 rounded-full">
                                <i class='bx bx-user text-xl text-blue-600'></i>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="flex items-center text-green-500 text-sm">
                                <i class='bx bx-up-arrow-alt'></i>
                                <span class="ml-1">Active</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Pages</p>
                                <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['total_pages']; ?></h3>
                            </div>
                            <div class="bg-purple-100 p-3 rounded-full">
                                <i class='bx bx-file text-xl text-purple-600'></i>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="flex items-center text-gray-500 text-sm">
                                <i class='bx bx-right-arrow-alt'></i>
                                <span class="ml-1">Stable</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Blog Posts</p>
                                <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['total_posts']; ?></h3>
                            </div>
                            <div class="bg-yellow-100 p-3 rounded-full">
                                <i class='bx bx-news text-xl text-yellow-600'></i>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="flex items-center text-green-500 text-sm">
                                <i class='bx bx-up-arrow-alt'></i>
                                <span class="ml-1">Growing</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Services</p>
                                <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['total_services']; ?></h3>
                            </div>
                            <div class="bg-green-100 p-3 rounded-full">
                                <i class='bx bx-server text-xl text-green-600'></i>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="flex items-center text-red-500 text-sm">
                                <i class='bx bx-down-arrow-alt'></i>
                                <span class="ml-1">Needs update</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Charts and Activity -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <!-- Traffic Chart -->
                    <div class="bg-white rounded-lg shadow-md lg:col-span-2">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-800">Website Traffic</h3>
                                <div class="flex items-center space-x-2">
                                    <button class="px-3 py-1 bg-blue-50 text-blue-600 rounded-md text-sm">Monthly</button>
                                    <button class="px-3 py-1 text-gray-600 rounded-md text-sm">Weekly</button>
                                </div>
                            </div>
                            <div>
                                <canvas id="trafficChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Activity -->
                    <div class="bg-white rounded-lg shadow-md">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Activity</h3>
                            <div class="space-y-4">
                                <?php foreach($recent_activity as $activity): ?>
                                <div class="flex items-start">
                                    <?php if($activity['type'] == 'update'): ?>
                                        <div class="bg-blue-100 p-2 rounded-full mr-4">
                                            <i class='bx bx-edit text-blue-600'></i>
                                        </div>
                                    <?php elseif($activity['type'] == 'create'): ?>
                                        <div class="bg-green-100 p-2 rounded-full mr-4">
                                            <i class='bx bx-plus text-green-600'></i>
                                        </div>
                                    <?php elseif($activity['type'] == 'delete'): ?>
                                        <div class="bg-red-100 p-2 rounded-full mr-4">
                                            <i class='bx bx-trash text-red-600'></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <p class="text-gray-800 font-medium"><?php echo htmlspecialchars($activity['item']); ?></p>
                                        <p class="text-gray-500 text-sm">
                                            <?php echo htmlspecialchars($activity['user']); ?> • 
                                            <span><?php echo htmlspecialchars($activity['time']); ?></span>
                                        </p>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="mt-4 text-center">
                                <a href="#" class="text-blue-600 hover:underline text-sm">View All Activity</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                        <a href="manage-navbar.php" class="flex flex-col items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="bg-blue-100 p-3 rounded-full mb-2">
                                <i class='bx bx-navigation text-xl text-blue-600'></i>
                            </div>
                            <span class="text-sm text-gray-700">Edit Navbar</span>
                        </a>
                        <a href="manage-components.php" class="flex flex-col items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="bg-purple-100 p-3 rounded-full mb-2">
                                <i class='bx bx-layout text-xl text-purple-600'></i>
                            </div>
                            <span class="text-sm text-gray-700">Edit Footer</span>
                        </a>
                        <a href="#" class="flex flex-col items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="bg-green-100 p-3 rounded-full mb-2">
                                <i class='bx bx-edit text-xl text-green-600'></i>
                            </div>
                            <span class="text-sm text-gray-700">New Post</span>
                        </a>
                        <a href="#" class="flex flex-col items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="bg-yellow-100 p-3 rounded-full mb-2">
                                <i class='bx bx-user-plus text-xl text-yellow-600'></i>
                            </div>
                            <span class="text-sm text-gray-700">Add User</span>
                        </a>
                        <a href="#" class="flex flex-col items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="bg-red-100 p-3 rounded-full mb-2">
                                <i class='bx bx-cog text-xl text-red-600'></i>
                            </div>
                            <span class="text-sm text-gray-700">Settings</span>
                        </a>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="text-center text-gray-500 text-sm mt-6">
                    <p>&copy; 2023 Akademi Merdeka Admin Dashboard. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Traffic chart
        const trafficCtx = document.getElementById('trafficChart').getContext('2d');
        new Chart(trafficCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Visitors',
                    data: [1500, 1800, 2100, 1900, 2400, 2800, 3200, 3500, 3800, 4100, 4300, 4600],
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: true,
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>