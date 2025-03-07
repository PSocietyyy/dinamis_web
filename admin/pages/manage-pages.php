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
$pageContent = '';

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

// Handle page update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_page'])) {
    $pageId = (int)$_POST['page_id'];
    $title = trim($_POST['title']);
    $url = trim($_POST['url']);
    $position = (int)$_POST['position'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_description = trim($_POST['meta_description'] ?? '');
    
    // Validate
    if (empty($title)) {
        $message = "Title cannot be empty.";
        $messageType = "error";
    } elseif (empty($url)) {
        $message = "URL cannot be empty.";
        $messageType = "error";
    } else {
        try {
            // Begin transaction
            $conn->beginTransaction();
            
            // Update navbar item
            $stmt = $conn->prepare("UPDATE navbar_items 
                                   SET title = :title, url = :url, position = :position, is_active = :is_active 
                                   WHERE id = :id");
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':url', $url);
            $stmt->bindParam(':position', $position);
            $stmt->bindParam(':is_active', $is_active);
            $stmt->bindParam(':id', $pageId);
            $stmt->execute();
            
            // Check if page_meta table exists, if not create it
            $stmt = $conn->prepare("SHOW TABLES LIKE 'page_meta'");
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                // Create page_meta table
                $conn->exec("CREATE TABLE page_meta (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    page_id INT NOT NULL,
                    meta_title VARCHAR(255),
                    meta_description TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (page_id) REFERENCES navbar_items(id) ON DELETE CASCADE
                )");
            }
            
            // Check if meta for this page already exists
            $stmt = $conn->prepare("SELECT id FROM page_meta WHERE page_id = :page_id");
            $stmt->bindParam(':page_id', $pageId);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                // Update existing meta
                $stmt = $conn->prepare("UPDATE page_meta 
                                       SET meta_title = :meta_title, meta_description = :meta_description 
                                       WHERE page_id = :page_id");
            } else {
                // Insert new meta
                $stmt = $conn->prepare("INSERT INTO page_meta (page_id, meta_title, meta_description) 
                                       VALUES (:page_id, :meta_title, :meta_description)");
            }
            
            $stmt->bindParam(':meta_title', $meta_title);
            $stmt->bindParam(':meta_description', $meta_description);
            $stmt->bindParam(':page_id', $pageId);
            $stmt->execute();
            
            // If file-based page, try to get content
            if (strpos($url, './') === 0 && strpos($url, '.php') !== false) {
                $pageFile = str_replace('./', '../../', $url);
                if (file_exists($pageFile)) {
                    // Read file content
                    $pageContent = file_get_contents($pageFile);
                    
                    // Save content if submitted
                    if (isset($_POST['page_content']) && !empty($_POST['page_content'])) {
                        file_put_contents($pageFile, $_POST['page_content']);
                    }
                }
            }
            
            $conn->commit();
            $message = "Page updated successfully!";
            $messageType = "success";
        } catch(PDOException $e) {
            $conn->rollBack();
            $message = "Error updating page: " . $e->getMessage();
            $messageType = "error";
        }
    }
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
            
            // Get page meta
            try {
                $stmt = $conn->prepare("SELECT meta_title, meta_description FROM page_meta WHERE page_id = :page_id");
                $stmt->bindParam(':page_id', $pageId);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    $metaData = $stmt->fetch();
                    $pageData['meta_title'] = $metaData['meta_title'];
                    $pageData['meta_description'] = $metaData['meta_description'];
                } else {
                    $pageData['meta_title'] = $pageData['title'];
                    $pageData['meta_description'] = '';
                }
            } catch(PDOException $e) {
                // If page_meta table doesn't exist yet
                $pageData['meta_title'] = $pageData['title'];
                $pageData['meta_description'] = '';
            }
            
            // If file-based page, try to get content
            if (strpos($pageData['url'], './') === 0 && strpos($pageData['url'], '.php') !== false) {
                $pageFile = str_replace('./', '../../', $pageData['url']);
                if (file_exists($pageFile)) {
                    $pageContent = file_get_contents($pageFile);
                }
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
    <!-- CodeMirror for code editing -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/monokai.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/htmlmixed/htmlmixed.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/javascript/javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/css/css.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/php/php.min.js"></script>
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
                                <?php if($pageData): ?>
                                <a href="?id=0" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                                    <i class='bx bx-x mr-1'></i> Cancel Editing
                                </a>
                                <?php endif; ?>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">Manage your website pages and navigation structure</p>
                        </div>
                        
                        <div class="p-6">
                            <?php if($pageData): ?>
                            <!-- Edit Page Form -->
                            <form method="POST" action="">
                                <input type="hidden" name="page_id" value="<?php echo $pageData['id']; ?>">
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div>
                                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Page Title</label>
                                        <input type="text" id="title" name="title" 
                                               value="<?php echo htmlspecialchars($pageData['title']); ?>"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">This will appear in the navigation menu</p>
                                    </div>
                                    
                                    <div>
                                        <label for="url" class="block text-sm font-medium text-gray-700 mb-1">Page URL</label>
                                        <input type="text" id="url" name="url" 
                                               value="<?php echo htmlspecialchars($pageData['url']); ?>"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">Use "./page.php" for local pages or full URLs for external links</p>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div>
                                        <label for="position" class="block text-sm font-medium text-gray-700 mb-1">Menu Position</label>
                                        <input type="number" id="position" name="position" 
                                               value="<?php echo htmlspecialchars($pageData['position']); ?>"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">Lower numbers appear first in the menu</p>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-3">Status</label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="is_active" 
                                                   <?php echo $pageData['is_active'] ? 'checked' : ''; ?>
                                                   class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                                            <span class="ml-2">Active</span>
                                        </label>
                                        <p class="mt-1 text-xs text-gray-500">Inactive pages won't appear in the navigation menu</p>
                                    </div>
                                </div>
                                
                                <div class="mb-6">
                                    <label for="meta_title" class="block text-sm font-medium text-gray-700 mb-1">Meta Title</label>
                                    <input type="text" id="meta_title" name="meta_title" 
                                           value="<?php echo htmlspecialchars($pageData['meta_title'] ?? ''); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <p class="mt-1 text-xs text-gray-500">SEO title that appears in search engine results</p>
                                </div>
                                
                                <div class="mb-6">
                                    <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
                                    <textarea id="meta_description" name="meta_description" rows="3"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($pageData['meta_description'] ?? ''); ?></textarea>
                                    <p class="mt-1 text-xs text-gray-500">SEO description that appears in search engine results</p>
                                </div>
                                
                                <?php if(!empty($pageContent)): ?>
                                <div class="mb-6">
                                    <label for="page_content" class="block text-sm font-medium text-gray-700 mb-1">Page Content</label>
                                    <textarea id="page_content" name="page_content" rows="15"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($pageContent); ?></textarea>
                                    <p class="mt-1 text-xs text-gray-500">Edit the page content directly. Be careful with PHP code!</p>
                                </div>
                                <?php endif; ?>
                                
                                <div class="flex justify-between mt-8">
                                    <a href="?id=0" class="px-5 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                                        Cancel
                                    </a>
                                    <button type="submit" name="update_page" class="px-5 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                                        Update Page
                                    </button>
                                </div>
                            </form>
                            <?php else: ?>
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
                                                    <a href="?id=<?php echo $page['id']; ?>" class="text-blue-600 hover:text-blue-900">
                                                        Edit
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
                                                        <a href="?id=<?php echo $child['id']; ?>" class="text-blue-600 hover:text-blue-900">
                                                            Edit
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
                            <?php endif; ?>
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
    
    <script>
        // Initialize CodeMirror for PHP editor if available
        const pageContentTextarea = document.getElementById('page_content');
        if (pageContentTextarea) {
            const editor = CodeMirror.fromTextArea(pageContentTextarea, {
                lineNumbers: true,
                mode: "application/x-httpd-php",
                theme: "monokai",
                indentUnit: 4,
                indentWithTabs: false,
                lineWrapping: true,
                autoCloseBrackets: true,
                matchBrackets: true
            });
            
            // Set editor height
            editor.setSize(null, 500);
        }
    </script>
</body>
</html>