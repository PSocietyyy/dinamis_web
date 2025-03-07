<?php
// Start the session
session_start();

// Check if not logged in
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../../login.php");
    exit;
}

// Include database connection
require_once('../../config.php');

// Initialize variables
$message = '';
$messageType = '';
$username = $_SESSION['username'] ?? 'Admin';

// Get all pages from navbar items
$pages = [];

try {
    // Get parent menu items
    $stmt = $conn->query("SELECT n1.id, n1.title, n1.url, n1.position, n1.is_active, 
                           COUNT(n2.id) as children_count 
                           FROM navbar_items n1 
                           LEFT JOIN navbar_items n2 ON n1.id = n2.parent_id 
                           WHERE n1.parent_id IS NULL 
                           GROUP BY n1.id 
                           ORDER BY n1.position");
    $parent_items = $stmt->fetchAll();

    foreach($parent_items as &$parent) {
        // Get child items if any
        $parent['children'] = [];
        if($parent['children_count'] > 0) {
            $stmt = $conn->prepare("SELECT id, title, url, position, is_active 
                                    FROM navbar_items 
                                    WHERE parent_id = :parent_id 
                                    ORDER BY position");
            $stmt->bindParam(':parent_id', $parent['id']);
            $stmt->execute();
            $parent['children'] = $stmt->fetchAll();
        }
    }
    
    $pages = $parent_items;
} catch(PDOException $e) {
    $message = "Error fetching pages: " . $e->getMessage();
    $messageType = "error";
}
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Management - Akademi Merdeka</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Boxicons -->
    <link rel="stylesheet" href="../../assets/css/boxicons.min.css">
    <!-- Custom Tailwind Config -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;300;400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
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
        <!-- Include sidebar with correct path -->
        <?php include('../components/sidebar.php'); ?>
        
        <!-- Main Content -->
        <div class="flex-1 lg:ml-64">
            <!-- Top Bar -->
            <div class="bg-white p-4 shadow flex justify-between items-center">
                <h1 class="text-xl font-semibold text-gray-800">Page Management</h1>
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <button class="text-gray-500 hover:text-gray-700">
                            <i class='bx bx-bell text-xl'></i>
                            <span class="absolute top-0 right-0 h-2 w-2 rounded-full bg-red-500"></span>
                        </button>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-gray-600">Welcome, <?php echo htmlspecialchars($username); ?></span>
                        <img class="w-8 h-8 rounded-full" src="../../assets/images/team/pp-1.png" alt="Profile">
                    </div>
                </div>
            </div>
            
            <!-- Page Management Content -->
            <div class="p-6">
                <?php if(!empty($message)): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo $message; ?>
                </div>
                <?php endif; ?>
                
                <!-- Page Section -->
                <div class="mb-6">
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex justify-between items-center">
                                <h2 class="text-lg font-semibold text-gray-800">Website Pages</h2>
                                <div>
                                    <a href="../manage-navbar.php" class="px-4 py-2 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors">
                                        <i class='bx bx-menu mr-1'></i> Manage Navigation
                                    </a>
                                </div>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">Manage your website pages and navigation structure</p>
                        </div>
                        
                        <div class="p-6">
                            <!-- Pages List -->
                            <div class="space-y-6">
                                <?php if(empty($pages)): ?>
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <i class="bx bx-info-circle text-blue-600 text-xl"></i>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-blue-800">No pages found</h3>
                                            <div class="mt-2 text-sm text-blue-700">
                                                <p>There are no pages in the navigation menu. Please check your database setup.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php else: ?>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <?php foreach($pages as $page): ?>
                                            <tr class="bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap font-medium">
                                                    <?php echo htmlspecialchars($page['title']); ?>
                                                    <?php if($page['children_count'] > 0): ?>
                                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                        Dropdown
                                                    </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo htmlspecialchars($page['url']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo $page['position']; ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <?php if($page['is_active']): ?>
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Active
                                                    </span>
                                                    <?php else: ?>
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        Inactive
                                                    </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <a href="edit-page.php?id=<?php echo $page['id']; ?>" class="text-blue-600 hover:text-blue-900">
                                                        <i class='bx bx-edit mr-1'></i> Edit
                                                    </a>
                                                </td>
                                            </tr>
                                            
                                            <?php if(!empty($page['children'])): ?>
                                                <?php foreach($page['children'] as $child): ?>
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap pl-12">
                                                        <i class='bx bx-subdirectory-right mr-2 text-gray-400'></i>
                                                        <?php echo htmlspecialchars($child['title']); ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        <?php echo htmlspecialchars($child['url']); ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        <?php echo $child['position']; ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <?php if($child['is_active']): ?>
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                            Active
                                                        </span>
                                                        <?php else: ?>
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                            Inactive
                                                        </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                        <a href="edit-page.php?id=<?php echo $child['id']; ?>" class="text-blue-600 hover:text-blue-900">
                                                            <i class='bx bx-edit mr-1'></i> Edit
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Help Section -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Page Management Help</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <h3 class="text-base font-medium text-gray-800 mb-2">Navigation Structure</h3>
                                <ul class="space-y-2 text-sm text-gray-600">
                                    <li class="flex items-start">
                                        <i class="bx bx-info-circle text-blue-500 mt-0.5 mr-2"></i>
                                        <span>Main menu items are top-level navigation</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="bx bx-info-circle text-blue-500 mt-0.5 mr-2"></i>
                                        <span>Dropdown menus display child pages</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="bx bx-info-circle text-blue-500 mt-0.5 mr-2"></i>
                                        <span>Use position numbers to control the order</span>
                                    </li>
                                </ul>
                            </div>
                            
                            <div>
                                <h3 class="text-base font-medium text-gray-800 mb-2">URL Format</h3>
                                <ul class="space-y-2 text-sm text-gray-600">
                                    <li class="flex items-start">
                                        <i class="bx bx-info-circle text-blue-500 mt-0.5 mr-2"></i>
                                        <span>Use "./page.php" for local pages</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="bx bx-info-circle text-blue-500 mt-0.5 mr-2"></i>
                                        <span>Full URLs (http://) for external links</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="bx bx-info-circle text-blue-500 mt-0.5 mr-2"></i>
                                        <span>Use "#" for dropdown parent items</span>
                                    </li>
                                </ul>
                            </div>
                            
                            <div>
                                <h3 class="text-base font-medium text-gray-800 mb-2">SEO Tips</h3>
                                <ul class="space-y-2 text-sm text-gray-600">
                                    <li class="flex items-start">
                                        <i class="bx bx-info-circle text-blue-500 mt-0.5 mr-2"></i>
                                        <span>Keep meta titles under 60 characters</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="bx bx-info-circle text-blue-500 mt-0.5 mr-2"></i>
                                        <span>Meta descriptions should be 150-160 characters</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="bx bx-info-circle text-blue-500 mt-0.5 mr-2"></i>
                                        <span>Include relevant keywords in both fields</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="text-center text-gray-500 text-sm mt-6">
                    <p>&copy; 2023 Akademi Merdeka Admin Dashboard. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>