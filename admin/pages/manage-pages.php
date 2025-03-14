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
$currentUsername = $_SESSION['username'];

// Default directory for pages content
$pagesDirectory = '../../pages/';

// Function to get file path from navbar item URL
function getPageFilePath($url) {
    global $pagesDirectory;
    
    // Clean up URL (remove leading slashes, query params, etc.)
    $url = trim($url, '/');
    $url = strtok($url, '?');
    
    // Handle special cases
    if ($url == '' || $url == 'index') {
        return '../../index.php';
    }
    
    // Check if .php extension is needed
    if (!str_ends_with($url, '.php')) {
        $url .= '.php';
    }
    
    return $pagesDirectory . $url;
}

// Check if file exists
function pageFileExists($url) {
    $filePath = getPageFilePath($url);
    return file_exists($filePath);
}

// Get all pages and menu items (for the tree view)
$menuItems = [];
try {
    $stmt = $conn->query("SELECT id, title, link, is_active, parent_id, has_dropdown, order_index 
                        FROM navbar_items 
                        ORDER BY parent_id IS NULL DESC, parent_id, order_index");
    $menuItems = $stmt->fetchAll();
    
    // Mark items that are actual pages (not dropdown parents and not external links)
    foreach ($menuItems as $key => $item) {
        $menuItems[$key]['is_page'] = ($item['has_dropdown'] == 0 && strpos($item['link'], 'http') !== 0);
        $menuItems[$key]['file_exists'] = $menuItems[$key]['is_page'] ? pageFileExists($item['link']) : false;
    }
} catch(PDOException $e) {
    $message = "Error fetching menu items: " . $e->getMessage();
    $messageType = "error";
}

// Function to build a tree structure of menu items
function buildMenuTree($items, $parentId = null) {
    $tree = [];
    
    foreach ($items as $item) {
        if ($item['parent_id'] == $parentId) {
            $children = buildMenuTree($items, $item['id']);
            if ($children) {
                $item['children'] = $children;
            }
            $tree[] = $item;
        }
    }
    
    return $tree;
}

// Build menu tree
$menuTree = buildMenuTree($menuItems);

// Function to display menu tree with expandable sections
function displayMenuTree($menuTree, $level = 0) {
    $html = '';
    
    foreach ($menuTree as $index => $item) {
        $isFirst = $index === 0;
        $isLast = $index === count($menuTree) - 1;
        $hasChildren = isset($item['children']) && !empty($item['children']);
        $statusIcon = $item['is_active'] ? '<span class="text-green-500"><i class="bx bxs-check-circle"></i></span>' : '<span class="text-red-500"><i class="bx bxs-x-circle"></i></span>';
        $pageIcon = $item['is_page'] ? ($item['file_exists'] ? '<span class="text-blue-600"><i class="bx bxs-file"></i></span>' : '<span class="text-yellow-500"><i class="bx bxs-file-blank"></i></span>') : '<span class="text-gray-400"><i class="bx bxs-folder"></i></span>';
        $dropdownIcon = $item['has_dropdown'] ? '<span class="inline-flex items-center px-2 py-0.5 ml-2 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Dropdown</span>' : '';
        $targetIcon = strpos($item['link'], 'http') === 0 ? '<span class="ml-1 text-gray-500" title="External Link"><i class="bx bx-link-external"></i></span>' : '';
        $expanded = 'true'; // Default expanded
        
        // Create tree line graphics based on position
        $treeLineClass = $level > 0 ? 'border-l' : '';
        $lineGraphic = '';
        
        if ($level > 0) {
            if ($isLast) {
                $lineGraphic = '<span class="absolute top-0 left-0 h-1/2 border-l border-gray-300"></span>
                               <span class="absolute top-1/2 left-0 h-0 w-4 border-t border-gray-300"></span>';
            } else {
                $lineGraphic = '<span class="absolute top-0 left-0 h-full border-l border-gray-300"></span>
                               <span class="absolute top-1/2 left-0 h-0 w-4 border-t border-gray-300"></span>';
            }
        }
        
        // Start menu item
        $html .= '<div class="menu-item relative pl-' . ($level * 4 + 4) . ' py-2" data-id="' . $item['id'] . '" data-parent="' . ($item['parent_id'] ?? 'null') . '" data-order="' . $item['order_index'] . '">';
        
        // Tree lines
        if ($level > 0) {
            $html .= '<div class="absolute left-0 top-0 h-full w-4">' . $lineGraphic . '</div>';
        }
        
        // Item container with hover effect
        $html .= '<div class="flex items-center p-2 rounded-lg ' . ($item['is_page'] ? 'bg-white' : 'bg-gray-50') . ' hover:bg-gray-50 transition-colors shadow-sm border ' . ($item['is_page'] ? 'border-gray-200' : 'border-gray-200 border-dashed') . '">';
        
        // Expand/collapse for items with children
        if ($hasChildren) {
            $html .= '<button type="button" class="toggle-children mr-2 text-gray-500 hover:text-gray-700 focus:outline-none" aria-expanded="' . $expanded . '">
                        <i class="bx bx-chevron-down transition-transform" style="transform: rotate(' . ($expanded === 'true' ? '0deg' : '-90deg') . ');"></i>
                    </button>';
        } else {
            $html .= '<span class="w-6 mr-2"></span>';
        }
        
        // Item icon & status
        $html .= '<span class="mr-2">' . $pageIcon . '</span>';
        $html .= '<span class="mr-2">' . $statusIcon . '</span>';
        
        // Item title and badges
        $html .= '<div class="flex-1">
                    <span class="font-medium text-gray-800">' . htmlspecialchars($item['title']) . '</span>
                    ' . $dropdownIcon . $targetIcon . '
                    <div class="text-xs text-gray-500 mt-1">
                        <span class="inline-block mr-4">' . htmlspecialchars($item['link']) . '</span>
                        <span class="inline-block">ID: ' . $item['id'] . '</span>
                    </div>
                  </div>';
        
        // Actions
        if ($item['is_page']) {
            // Get filename from link
            $filename = trim($item['link'], '/');
            // Remove .php extension if it exists
            $filename = preg_replace('/\.php$/', '', $filename);
            // Replace slashes with dashes for subfolder paths
            $filename = str_replace('/', '-', $filename);
            // Handle empty or index case
            if (empty($filename) || $filename === 'index') {
                $filename = 'home';
            }
            
            $html .= '<div class="ml-4 flex items-center space-x-2">
                        <a href="edit-pages/' . $filename . '.php" class="px-3 py-1 bg-green-500 text-white rounded-md hover:bg-green-600 transition-colors flex items-center">
                            <i class="bx bx-edit mr-1"></i> Edit Page
                        </a>
                      </div>';
        } else {
            $html .= '<div class="ml-4 flex items-center space-x-2">
                        <span class="px-3 py-1 bg-gray-100 text-gray-400 rounded-md flex items-center">
                            <i class="bx bx-folder mr-1"></i> ' . ($item['has_dropdown'] ? 'Menu Group' : 'External Link') . '
                        </span>
                      </div>';
        }
        
        $html .= '</div>'; // End item container
        
        // Children container
        if ($hasChildren) {
            $html .= '<div class="children mt-2' . ($expanded === 'false' ? ' hidden' : '') . '">';
            $html .= displayMenuTree($item['children'], $level + 1);
            $html .= '</div>';
        }
        
        $html .= '</div>'; // End menu item
    }
    
    return $html;
}
?>

<!doctype html>
<html lang="id">
<?php include('../components/head.php'); ?>
<body class="bg-gray-50">
    <div class="min-h-screen flex flex-col lg:flex-row">
        <?php include('../components/sidebar.php'); ?>
        
        <div class="flex-1 lg:ml-64">
            <div class="bg-white p-4 shadow flex justify-between items-center">
                <h1 class="text-xl font-semibold text-gray-800">Manage Pages</h1>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <span class="text-gray-600">Welcome, <?php echo htmlspecialchars($currentUsername); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="p-6">
                <?php if(!empty($message)): ?>
                <div class="mb-6 p-4 rounded-lg shadow-sm border-l-4 <?php echo $messageType === 'success' ? 'bg-green-50 border-green-500 text-green-700' : 'bg-red-50 border-red-500 text-red-700'; ?> flex items-center">
                    <i class="bx <?php echo $messageType === 'success' ? 'bx-check-circle' : 'bx-error-circle'; ?> text-2xl mr-3"></i>
                    <span><?php echo $message; ?></span>
                </div>
                <?php endif; ?>
                
                <!-- Main container -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-200 flex justify-between items-center">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800">Pages Structure</h2>
                            <p class="text-sm text-gray-500 mt-1">Select a page to edit its content</p>
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <a href="?" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="bx bx-refresh mr-1"></i> Refresh
                            </a>
                            <a href="../manage-navbar.php" class="inline-flex items-center px-3 py-1.5 border border-blue-500 shadow-sm text-sm font-medium rounded-md text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="bx bx-navigation mr-1"></i> Manage Navbar
                            </a>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <div class="flex space-x-4 mb-4">
                            <button type="button" id="expand-all" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="bx bx-expand-alt mr-1"></i> Expand All
                            </button>
                            <button type="button" id="collapse-all" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="bx bx-collapse-alt mr-1"></i> Collapse All
                            </button>
                        </div>
                        
                        <!-- Menu Tree View -->
                        <div id="menu-tree" class="menu-tree">
                            <?php echo displayMenuTree($menuTree); ?>
                        </div>
                        
                        <!-- Legend -->
                        <div class="bg-gray-50 mt-4 p-4 rounded-md border border-gray-200">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Legend:</h3>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <div class="flex items-center">
                                    <span class="text-blue-600 mr-2"><i class="bx bxs-file"></i></span>
                                    <span class="text-sm text-gray-600">Page with file</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-yellow-500 mr-2"><i class="bx bxs-file-blank"></i></span>
                                    <span class="text-sm text-gray-600">Page without file</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-gray-400 mr-2"><i class="bx bxs-folder"></i></span>
                                    <span class="text-sm text-gray-600">Menu item (not a page)</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-green-500 mr-2"><i class="bx bxs-check-circle"></i></span>
                                    <span class="text-sm text-gray-600">Active item</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-red-500 mr-2"><i class="bx bxs-x-circle"></i></span>
                                    <span class="text-sm text-gray-600">Inactive item</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="inline-flex items-center px-2 py-0.5 mr-2 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Dropdown</span>
                                    <span class="text-sm text-gray-600">Dropdown menu</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Page Management Help Box -->
                <div class="mt-6 bg-blue-50 rounded-lg shadow-sm overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="bx bx-info-circle text-blue-600 text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-blue-800 mb-2">How to Edit Pages</h3>
                                <p class="text-sm text-blue-700 mb-4">Click the "Edit Page" button next to any page in the list to modify its content. This will take you directly to the edit page for that specific content file in the <code>admin/pages/edit-pages/</code> directory. Only actual pages (with the file icon) can be edited.</p>
                                <p class="text-sm text-blue-700">The edit page filenames are derived from the page URL in the navigation structure. For example, a page with URL "about-us" will have an edit file named "about-us.php" in the edit-pages directory.</p>
                                <p class="text-sm text-blue-700 mt-2">To manage the navigation structure or create new pages, use the <a href="../manage-navbar.php" class="text-blue-800 underline font-medium">Navbar Manager</a>.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="text-center text-gray-500 text-sm mt-8 pb-6">
                    <p>&copy; 2023 Akademi Merdeka Admin Dashboard. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle children visibility
            const toggleButtons = document.querySelectorAll('.toggle-children');
            toggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const menuItem = this.closest('.menu-item');
                    const childrenContainer = menuItem.querySelector('.children');
                    const icon = this.querySelector('i');
                    
                    if (childrenContainer) {
                        const isExpanded = this.getAttribute('aria-expanded') === 'true';
                        this.setAttribute('aria-expanded', isExpanded ? 'false' : 'true');
                        childrenContainer.classList.toggle('hidden');
                        
                        // Rotate icon
                        icon.style.transform = isExpanded ? 'rotate(-90deg)' : 'rotate(0deg)';
                    }
                });
            });
            
            // Expand all button
            document.getElementById('expand-all')?.addEventListener('click', function() {
                document.querySelectorAll('.children').forEach(container => {
                    container.classList.remove('hidden');
                });
                
                document.querySelectorAll('.toggle-children').forEach(button => {
                    button.setAttribute('aria-expanded', 'true');
                    button.querySelector('i').style.transform = 'rotate(0deg)';
                });
            });
            
            // Collapse all button
            document.getElementById('collapse-all')?.addEventListener('click', function() {
                document.querySelectorAll('.children').forEach(container => {
                    container.classList.add('hidden');
                });
                
                document.querySelectorAll('.toggle-children').forEach(button => {
                    button.setAttribute('aria-expanded', 'false');
                    button.querySelector('i').style.transform = 'rotate(-90deg)';
                });
            });
        });
    </script>
</body>
</html>