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
$pageId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pageData = null;

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

// Get specific page data if ID is provided
if ($pageId > 0) {
    try {
        // Get page data
        $stmt = $conn->prepare("SELECT id, title, url, position, is_active FROM navbar_items WHERE id = :id");
        $stmt->bindParam(':id', $pageId);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $pageData = $stmt->fetch();
            
            // If file-based page, redirect to specific edit page
            if (strpos($pageData['url'], './') === 0) {
                $pageName = pathinfo($pageData['url'], PATHINFO_FILENAME);
                $editPage = "./edit-pages/{$pageName}.php";
                
                // Check if edit page exists, if not create it
                if (!file_exists($editPage)) {
                    // Create directory if it doesn't exist
                    if (!file_exists("./edit-pages")) {
                        mkdir("./edit-pages", 0755, true);
                    }
                    
                    // Create empty edit page
                    $template = '<?php
// Start the session
session_start();

// Check if not logged in
if(!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: ../../../login.php");
    exit;
}

// Include database connection
require_once("../../../config.php");

// Initialize variables
$message = "";
$messageType = "";
$username = $_SESSION["username"] ?? "Admin";
$pageId = ' . $pageId . ';
$pageTitle = "' . htmlspecialchars($pageData['title']) . '";
$pageUrl = "' . htmlspecialchars($pageData['url']) . '";
$pageFile = "../../../' . str_replace('./', '', $pageData['url']) . '";

// Get page content if exists
$pageContent = "";
if (file_exists($pageFile)) {
    $pageContent = file_get_contents($pageFile);
}

// Page editing functionality will be implemented here
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit <?php echo htmlspecialchars($pageTitle); ?> - Akademi Merdeka</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Boxicons -->
    <link rel="stylesheet" href="../../../assets/css/boxicons.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: "#f0f9ff",
                            100: "#e0f2fe",
                            200: "#bae6fd",
                            300: "#7dd3fc",
                            400: "#38bdf8",
                            500: "#0ea5e9",
                            600: "#0284c7",
                            700: "#0369a1",
                            800: "#075985",
                            900: "#0c4a6e",
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
        <?php include("../../components/sidebar.php"); ?>
        
        <!-- Main Content -->
        <div class="flex-1 lg:ml-64">
            <!-- Top Bar -->
            <div class="bg-white p-4 shadow flex justify-between items-center">
                <h1 class="text-xl font-semibold text-gray-800">Edit <?php echo htmlspecialchars($pageTitle); ?></h1>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600">Welcome, <?php echo htmlspecialchars($username); ?></span>
                </div>
            </div>
            
            <!-- Page Content -->
            <div class="p-6">
                <?php if(!empty($message)): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $messageType === "success" ? "bg-green-100 text-green-700" : "bg-red-100 text-red-700"; ?>">
                    <?php echo $message; ?>
                </div>
                <?php endif; ?>
                
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h2 class="text-lg font-semibold text-gray-800">Edit <?php echo htmlspecialchars($pageTitle); ?> Content</h2>
                            <a href="../manage-pages.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                                <i class="bx bx-arrow-back mr-1"></i> Back to Pages
                            </a>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="bx bx-info-circle text-blue-600 text-xl"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800">Edit Page Implementation</h3>
                                    <div class="mt-2 text-sm text-blue-700">
                                        <p>This is a placeholder for the <?php echo htmlspecialchars($pageTitle); ?> edit page.</p>
                                        <p>The actual editing functionality will be implemented here.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>';
                    
                    file_put_contents($editPage, $template);
                }
                
                // Redirect to the edit page
                header("Location: $editPage");
                exit;
            } else {
                $message = "This page cannot be edited as it is not a local file.";
                $messageType = "error";
            }
        } else {
            $message = "Page not found.";
            $messageType = "error";
        }
    } catch(PDOException $e) {
        $message = "Error fetching page data: " . $e->getMessage();
        $messageType = "error";
    }
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
                    <div class="flex items-center space-x-2">
                        <span class="text-gray-600">Welcome, <?php echo htmlspecialchars($username); ?></span>
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
                            </div>
                            <p class="text-sm text-gray-500 mt-1">Manage your website pages and content</p>
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
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
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
                                                    <?php 
                                                    if(strpos($page['url'], './') === 0) {
                                                        echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Local Page</span>';
                                                    } elseif(strpos($page['url'], 'http') === 0) {
                                                        echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">External Link</span>';
                                                    } else {
                                                        echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Other</span>';
                                                    }
                                                    ?>
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
                                                    <?php if(strpos($page['url'], './') === 0): ?>
                                                    <a href="?id=<?php echo $page['id']; ?>" class="px-3 py-1 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors">
                                                        <i class='bx bx-edit'></i> Edit Content
                                                    </a>
                                                    <?php elseif($page['url'] !== '#'): ?>
                                                    <span class="text-gray-400">Not Editable</span>
                                                    <?php else: ?>
                                                    <span class="text-gray-400">Dropdown Parent</span>
                                                    <?php endif; ?>
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
                                                        <?php 
                                                        if(strpos($child['url'], './') === 0) {
                                                            echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Local Page</span>';
                                                        } elseif(strpos($child['url'], 'http') === 0) {
                                                            echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">External Link</span>';
                                                        } else {
                                                            echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Other</span>';
                                                        }
                                                        ?>
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
                                                        <?php if(strpos($child['url'], './') === 0): ?>
                                                        <a href="?id=<?php echo $child['id']; ?>" class="px-3 py-1 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors">
                                                            <i class='bx bx-edit'></i> Edit Content
                                                        </a>
                                                        <?php else: ?>
                                                        <span class="text-gray-400">Not Editable</span>
                                                        <?php endif; ?>
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
                                <h3 class="text-base font-medium text-gray-800 mb-2">Edit Content</h3>
                                <ul class="space-y-2 text-sm text-gray-600">
                                    <li class="flex items-start">
                                        <i class="bx bx-info-circle text-blue-500 mt-0.5 mr-2"></i>
                                        <span>Only local pages (.php files) can be edited</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="bx bx-info-circle text-blue-500 mt-0.5 mr-2"></i>
                                        <span>Click "Edit Content" to modify a page's content</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="bx bx-info-circle text-blue-500 mt-0.5 mr-2"></i>
                                        <span>External links and dropdown parents cannot be edited</span>
                                    </li>
                                </ul>
                            </div>
                            
                            <div>
                                <h3 class="text-base font-medium text-gray-800 mb-2">Page Types</h3>
                                <ul class="space-y-2 text-sm text-gray-600">
                                    <li class="flex items-start">
                                        <i class="bx bx-info-circle text-blue-500 mt-0.5 mr-2"></i>
                                        <span><b>Local Page:</b> Internal .php files</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="bx bx-info-circle text-blue-500 mt-0.5 mr-2"></i>
                                        <span><b>External Link:</b> Links to other websites</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="bx bx-info-circle text-blue-500 mt-0.5 mr-2"></i>
                                        <span><b>Dropdown Parent:</b> Contains submenu items</span>
                                    </li>
                                </ul>
                            </div>
                            
                            <div>
                                <h3 class="text-base font-medium text-gray-800 mb-2">Navigation Management</h3>
                                <ul class="space-y-2 text-sm text-gray-600">
                                    <li class="flex items-start">
                                        <i class="bx bx-info-circle text-blue-500 mt-0.5 mr-2"></i>
                                        <span>To edit navigation structure, go to "Navbar" section</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="bx bx-info-circle text-blue-500 mt-0.5 mr-2"></i>
                                        <span>Change page order, titles, or URLs in the Navbar manager</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="bx bx-info-circle text-blue-500 mt-0.5 mr-2"></i>
                                        <span>This section is for content editing only</span>
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