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

// Initialize variables
$message = '';
$messageType = '';
$currentUsername = $_SESSION['username'];
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'menu-items';

// Upload directory for images
$upload_dir = '../assets/images/uploads/';

// Create upload directory if it doesn't exist
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Function to check if a menu item is a descendant of another
function isDescendantOf($conn, $itemId, $potentialParentId) {
    if ($itemId == $potentialParentId) {
        return true; // Can't be its own parent
    }
    
    $descendants = [];
    $toCheck = [$itemId];
    
    while (!empty($toCheck)) {
        $currentId = array_shift($toCheck);
        
        $stmt = $conn->prepare("SELECT id FROM navbar_items WHERE parent_id = :parent_id");
        $stmt->bindParam(':parent_id', $currentId);
        $stmt->execute();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $childId = $row['id'];
            if ($childId == $potentialParentId) {
                return true; // Found the potential parent as a descendant
            }
            $descendants[] = $childId;
            $toCheck[] = $childId;
        }
    }
    
    return false;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add new menu item
    if (isset($_POST['add_item'])) {
        $title = trim($_POST['title']);
        $link = trim($_POST['link']);
        $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        $has_dropdown = isset($_POST['has_dropdown']) ? 1 : 0;
        $target = !empty($_POST['target']) ? trim($_POST['target']) : '_self';
        
        // Get the highest order_index for the selected parent
        try {
            if ($parent_id) {
                $stmt = $conn->prepare("SELECT MAX(order_index) FROM navbar_items WHERE parent_id = :parent_id");
                $stmt->bindParam(':parent_id', $parent_id);
            } else {
                $stmt = $conn->prepare("SELECT MAX(order_index) FROM navbar_items WHERE parent_id IS NULL");
            }
            $stmt->execute();
            $max_order = $stmt->fetchColumn();
            $order_index = $max_order ? $max_order + 1 : 1;
            
            // Insert new item
            $stmt = $conn->prepare("INSERT INTO navbar_items (title, link, parent_id, has_dropdown, target, order_index) 
                                  VALUES (:title, :link, :parent_id, :has_dropdown, :target, :order_index)");
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':link', $link);
            $stmt->bindParam(':parent_id', $parent_id);
            $stmt->bindParam(':has_dropdown', $has_dropdown);
            $stmt->bindParam(':target', $target);
            $stmt->bindParam(':order_index', $order_index);
            $stmt->execute();
            
            $message = "Menu item added successfully!";
            $messageType = "success";
            $activeTab = 'menu-items';
        } catch(PDOException $e) {
            $message = "Error adding menu item: " . $e->getMessage();
            $messageType = "error";
            $activeTab = 'menu-items';
        }
    }
    
    // Update existing menu item
    if (isset($_POST['update_item'])) {
        $id = (int)$_POST['item_id'];
        $title = trim($_POST['title']);
        $link = trim($_POST['link']);
        $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        $has_dropdown = isset($_POST['has_dropdown']) ? 1 : 0;
        $target = !empty($_POST['target']) ? trim($_POST['target']) : '_self';
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        try {
            // Check if trying to set parent as itself or one of its descendants
            if ($parent_id && isDescendantOf($conn, $id, $parent_id)) {
                throw new PDOException("Cannot set a menu item as a child of itself or its descendants.");
            }
            
            // Update item
            $stmt = $conn->prepare("UPDATE navbar_items 
                                  SET title = :title, link = :link, parent_id = :parent_id, 
                                      has_dropdown = :has_dropdown, target = :target, is_active = :is_active
                                  WHERE id = :id");
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':link', $link);
            $stmt->bindParam(':parent_id', $parent_id);
            $stmt->bindParam(':has_dropdown', $has_dropdown);
            $stmt->bindParam(':target', $target);
            $stmt->bindParam(':is_active', $is_active);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $message = "Menu item updated successfully!";
            $messageType = "success";
            $activeTab = 'menu-items';
        } catch(PDOException $e) {
            $message = "Error updating menu item: " . $e->getMessage();
            $messageType = "error";
            $activeTab = 'menu-items';
        }
    }
    
    // Delete menu item
    if (isset($_POST['delete_item'])) {
        $id = (int)$_POST['item_id'];
        
        try {
            $stmt = $conn->prepare("DELETE FROM navbar_items WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $message = "Menu item deleted successfully!";
            $messageType = "success";
            $activeTab = 'menu-items';
        } catch(PDOException $e) {
            $message = "Error deleting menu item: " . $e->getMessage();
            $messageType = "error";
            $activeTab = 'menu-items';
        }
    }
    
    // Reorder menu items
    if (isset($_POST['reorder_items'])) {
        $items = json_decode($_POST['order_data'], true);
        
        try {
            $conn->beginTransaction();
            
            foreach ($items as $item) {
                $stmt = $conn->prepare("UPDATE navbar_items SET order_index = :order_index, parent_id = :parent_id WHERE id = :id");
                $stmt->bindParam(':order_index', $item['order']);
                $stmt->bindParam(':parent_id', $item['parent_id']);
                $stmt->bindParam(':id', $item['id']);
                $stmt->execute();
            }
            
            $conn->commit();
            $message = "Menu order updated successfully!";
            $messageType = "success";
            $activeTab = 'menu-items';
        } catch(PDOException $e) {
            $conn->rollBack();
            $message = "Error updating menu order: " . $e->getMessage();
            $messageType = "error";
            $activeTab = 'menu-items';
        }
    }
    
    // Update navbar settings with image uploads
    if (isset($_POST['update_settings'])) {
        try {
            $conn->beginTransaction();
            
            // Process each setting
            $settings = [
                // Logo settings
                'logo_alt' => trim($_POST['logo_alt']),
                'logo_height' => (int)$_POST['logo_height'],
                'logo_two_alt' => trim($_POST['logo_two_alt']),
                'logo_two_height' => (int)$_POST['logo_two_height'],
                
                // Mobile logo settings
                'mobile_logo_alt' => trim($_POST['mobile_logo_alt']),
                'mobile_logo_height' => (int)$_POST['mobile_logo_height'],
                'mobile_logo_two_alt' => trim($_POST['mobile_logo_two_alt']),
                'mobile_logo_two_height' => (int)$_POST['mobile_logo_two_height'],
                
                // Action button settings
                'action_button_text' => trim($_POST['action_button_text']),
                'action_button_link' => trim($_POST['action_button_link']),
                'action_button_target' => trim($_POST['action_button_target'])
            ];
            
            // Process logo uploads
            $logoFields = [
                'logo_file' => 'logo_path',
                'logo_two_file' => 'logo_two_path',
                'mobile_logo_file' => 'mobile_logo_path',
                'mobile_logo_two_file' => 'mobile_logo_two_path'
            ];
            
            foreach ($logoFields as $fileField => $pathField) {
                if (!empty($_FILES[$fileField]['name'])) {
                    $file_name = $_FILES[$fileField]['name'];
                    $file_tmp = $_FILES[$fileField]['tmp_name'];
                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    
                    // Verify extension
                    $extensions = ["jpeg", "jpg", "png", "gif", "svg", "webp"];
                    if (in_array($file_ext, $extensions)) {
                        $new_file_name = 'navbar_' . uniqid() . '.' . $file_ext;
                        $destination = $upload_dir . $new_file_name;
                        
                        if (move_uploaded_file($file_tmp, $destination)) {
                            // Path to store in the database (relative to website root)
                            $relative_path = 'assets/images/uploads/' . $new_file_name;
                            $settings[$pathField] = $relative_path;
                        } else {
                            throw new Exception("Failed to upload image: " . $file_name);
                        }
                    } else {
                        throw new Exception("Invalid file type. Only JPG, JPEG, PNG, GIF, SVG and WEBP are allowed: " . $file_name);
                    }
                } elseif (isset($_POST[$pathField])) {
                    // Keep existing path if no new file uploaded
                    $settings[$pathField] = trim($_POST[$pathField]);
                }
            }
            
            // Save all settings to database
            foreach ($settings as $key => $value) {
                $stmt = $conn->prepare("INSERT INTO navbar_settings (setting_key, setting_value) 
                                      VALUES (:key, :value) 
                                      ON DUPLICATE KEY UPDATE setting_value = :value");
                $stmt->bindParam(':key', $key);
                $stmt->bindParam(':value', $value);
                $stmt->execute();
            }
            
            $conn->commit();
            $message = "Navbar settings updated successfully!";
            $messageType = "success";
            $activeTab = 'settings';
        } catch(Exception $e) {
            $conn->rollBack();
            $message = "Error updating navbar settings: " . $e->getMessage();
            $messageType = "error";
            $activeTab = 'settings';
        }
    }
}

// Get all menu items
$menuItems = [];
try {
    $stmt = $conn->query("SELECT * FROM navbar_items ORDER BY parent_id IS NULL DESC, parent_id, order_index");
    $menuItems = $stmt->fetchAll();
} catch(PDOException $e) {
    $message = "Error fetching menu items: " . $e->getMessage();
    $messageType = "error";
}

// Get parent menu items (for dropdown selection)
$parentItems = [];
try {
    $stmt = $conn->query("SELECT id, title FROM navbar_items WHERE parent_id IS NULL ORDER BY order_index");
    $parentItems = $stmt->fetchAll();
} catch(PDOException $e) {
    $message = "Error fetching parent menu items: " . $e->getMessage();
    $messageType = "error";
}

// Get navbar settings
$navbarSettings = [];
try {
    $stmt = $conn->query("SELECT setting_key, setting_value FROM navbar_settings");
    while ($row = $stmt->fetch()) {
        $navbarSettings[$row['setting_key']] = $row['setting_value'];
    }
} catch(PDOException $e) {
    $message = "Error fetching navbar settings: " . $e->getMessage();
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

// Get single menu item for editing
$editItem = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    
    try {
        $stmt = $conn->prepare("SELECT * FROM navbar_items WHERE id = :id");
        $stmt->bindParam(':id', $editId);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $editItem = $stmt->fetch();
            $activeTab = 'menu-items';
        }
    } catch(PDOException $e) {
        $message = "Error fetching menu item: " . $e->getMessage();
        $messageType = "error";
    }
}

// Convert menu item tree to JSON for JavaScript
$menuTreeJson = json_encode($menuTree);

// Function to display menu tree with expandable sections
function displayMenuTree($menuTree, $level = 0) {
    $html = '';
    
    foreach ($menuTree as $index => $item) {
        $isFirst = $index === 0;
        $isLast = $index === count($menuTree) - 1;
        $hasChildren = isset($item['children']) && !empty($item['children']);
        $statusIcon = $item['is_active'] ? '<span class="text-green-500"><i class="bx bxs-check-circle"></i></span>' : '<span class="text-red-500"><i class="bx bxs-x-circle"></i></span>';
        $dropdownIcon = $item['has_dropdown'] ? '<span class="inline-flex items-center px-2 py-0.5 ml-2 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Dropdown</span>' : '';
        $targetIcon = $item['target'] === '_blank' ? '<span class="ml-1 text-gray-500" title="Opens in new tab"><i class="bx bx-link-external"></i></span>' : '';
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
        $html .= '<div class="menu-item relative pl-' . ($level * 4 + 4) . ' py-2" data-id="' . $item['id'] . '">';
        
        // Tree lines
        if ($level > 0) {
            $html .= '<div class="absolute left-0 top-0 h-full w-4">' . $lineGraphic . '</div>';
        }
        
        // Item container with hover effect
        $html .= '<div class="flex items-center p-2 rounded-lg bg-white hover:bg-gray-50 transition-colors shadow-sm border border-gray-200">';
        
        // Expand/collapse for items with children
        if ($hasChildren) {
            $html .= '<button type="button" class="toggle-children mr-2 text-gray-500 hover:text-gray-700 focus:outline-none" aria-expanded="' . $expanded . '">
                        <i class="bx bx-chevron-down transition-transform" style="transform: rotate(' . ($expanded === 'true' ? '0deg' : '-90deg') . ');"></i>
                    </button>';
        } else {
            $html .= '<span class="w-6 mr-2"></span>';
        }
        
        // Item status
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
        $html .= '<div class="ml-4 flex items-center space-x-2">
                    <a href="?edit=' . $item['id'] . '" class="px-3 py-1 bg-blue-50 text-blue-700 rounded-md hover:bg-blue-100 transition-colors flex items-center">
                        <i class="bx bx-edit mr-1"></i> Edit
                    </a>
                    <button type="button" class="px-3 py-1 bg-gray-50 text-gray-700 rounded-md hover:bg-gray-100 transition-colors flex items-center move-item">
                        <i class="bx bx-move mr-1"></i> Move
                    </button>
                  </div>';
        
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
<?php include('components/head.php'); ?>
<body class="bg-gray-100">
    <div class="min-h-screen flex flex-col lg:flex-row">
        <?php include('components/sidebar.php'); ?>
        
        <div class="flex-1 lg:ml-64">
            <div class="bg-white p-4 shadow-sm flex justify-between items-center">
                <h1 class="text-xl font-semibold text-gray-800">Manage Navbar</h1>
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
                
                <!-- Tab Navigation -->
                <div class="mb-6">
                    <div class="border-b border-gray-200">
                        <nav class="flex -mb-px">
                            <a href="?tab=menu-items" class="<?php echo $activeTab === 'menu-items' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm flex items-center">
                                <i class="bx bx-menu-alt-left text-xl mr-2"></i>
                                Menu Items
                            </a>
                            <a href="?tab=settings" class="<?php echo $activeTab === 'settings' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm flex items-center">
                                <i class="bx bx-cog text-xl mr-2"></i>
                                Navbar Settings
                            </a>
                        </nav>
                    </div>
                </div>
                
                <!-- Menu Items Tab -->
                <?php if ($activeTab === 'menu-items'): ?>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Menu Items List -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                            <div class="px-6 py-5 border-b border-gray-200 flex justify-between items-center">
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-800">Menu Structure</h2>
                                    <p class="text-sm text-gray-500 mt-1">Manage your navbar menu items</p>
                                </div>
                                
                                <div class="flex-shrink-0">
                                    <a href="?tab=menu-items" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <i class="bx bx-refresh mr-2"></i>
                                        Refresh
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
                                
                                <!-- Dropdown Legend -->
                                <div class="bg-gray-50 mt-4 p-4 rounded-md border border-gray-200">
                                    <h3 class="text-sm font-medium text-gray-700 mb-2">Menu Legend:</h3>
                                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                        <div class="flex items-center">
                                            <span class="text-green-500 mr-2"><i class="bx bxs-check-circle"></i></span>
                                            <span class="text-sm text-gray-600">Active item</span>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="text-red-500 mr-2"><i class="bx bxs-x-circle"></i></span>
                                            <span class="text-sm text-gray-600">Inactive item</span>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="inline-flex items-center px-2 py-0.5 mr-2 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Dropdown</span>
                                            <span class="text-sm text-gray-600">Has dropdown menu</span>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="mr-2"><i class="bx bx-link-external text-gray-500"></i></span>
                                            <span class="text-sm text-gray-600">Opens in new tab</span>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="mr-2"><i class="bx bx-chevron-down text-gray-500"></i></span>
                                            <span class="text-sm text-gray-600">Expand/collapse submenu</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Dropdown Preview -->
                            <?php if (count($menuTree) > 0): ?>
                            <div class="px-6 py-5 border-t border-gray-200">
                                <h3 class="text-sm font-medium text-gray-700 mb-3">Navbar Preview:</h3>
                                <div class="bg-white border border-gray-200 rounded-md shadow-sm p-4 overflow-x-auto">
                                    <div class="flex space-x-6">
                                        <?php foreach ($menuTree as $item): ?>
                                        <div class="relative group">
                                            <div class="px-3 py-2 font-medium text-gray-700 rounded-md <?php echo $item['has_dropdown'] ? 'cursor-pointer' : ''; ?> hover:bg-gray-100">
                                                <?php echo htmlspecialchars($item['title']); ?>
                                                <?php if ($item['has_dropdown']): ?>
                                                <i class='bx bx-caret-down ml-1'></i>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <?php if ($item['has_dropdown'] && isset($item['children'])): ?>
                                            <div class="absolute left-0 mt-2 w-48 bg-white border border-gray-200 rounded-md shadow-lg z-10 hidden group-hover:block">
                                                <?php foreach ($item['children'] as $child): ?>
                                                <div class="px-4 py-2 hover:bg-gray-100 text-sm cursor-pointer">
                                                    <?php echo htmlspecialchars($child['title']); ?>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Add/Edit Menu Item Form -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                            <div class="px-6 py-5 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-800">
                                    <?php echo $editItem ? 'Edit Menu Item' : 'Add New Menu Item'; ?>
                                </h2>
                                <p class="text-sm text-gray-500 mt-1">
                                    <?php echo $editItem ? 'Modify the selected menu item' : 'Create a new navbar menu item'; ?>
                                </p>
                            </div>
                            
                            <div class="p-6">
                                <form method="POST" action="">
                                    <?php if($editItem): ?>
                                    <input type="hidden" name="item_id" value="<?php echo $editItem['id']; ?>">
                                    <?php endif; ?>
                                    
                                    <div class="space-y-5">
                                        <div>
                                            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                                            <input type="text" id="title" name="title" required
                                                placeholder="e.g. Home, About Us, Contact"
                                                value="<?php echo $editItem ? htmlspecialchars($editItem['title']) : ''; ?>"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        
                                        <div>
                                            <label for="link" class="block text-sm font-medium text-gray-700 mb-1">Link/URL <span class="text-red-500">*</span></label>
                                            <input type="text" id="link" name="link" required
                                                placeholder="e.g. /, about-us, contact"
                                                value="<?php echo $editItem ? htmlspecialchars($editItem['link']) : ''; ?>"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <p class="mt-1 text-xs text-gray-500">Use # for dropdown parents</p>
                                        </div>
                                        
                                        <div>
                                            <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-1">Parent Menu</label>
                                            <select id="parent_id" name="parent_id" 
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                <option value="">None (Top Level)</option>
                                                <?php foreach($parentItems as $parent): ?>
                                                    <?php if(!$editItem || $parent['id'] != $editItem['id']): ?>
                                                    <option value="<?php echo $parent['id']; ?>" <?php echo ($editItem && $editItem['parent_id'] == $parent['id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($parent['title']); ?>
                                                    </option>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </select>
                                            <p class="mt-1 text-xs text-gray-500">Items will appear in dropdown menus</p>
                                        </div>
                                        
                                        <div>
                                            <label for="target" class="block text-sm font-medium text-gray-700 mb-1">Open In</label>
                                            <select id="target" name="target" 
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                <option value="_self" <?php echo ($editItem && $editItem['target'] == '_self') ? 'selected' : ''; ?>>Same Window (_self)</option>
                                                <option value="_blank" <?php echo ($editItem && $editItem['target'] == '_blank') ? 'selected' : ''; ?>>New Tab (_blank)</option>
                                            </select>
                                        </div>
                                        
                                        <div class="pt-2">
                                            <div class="relative flex items-start">
                                                <div class="flex items-center h-5">
                                                    <input type="checkbox" name="has_dropdown" id="has_dropdown" 
                                                        <?php echo ($editItem && $editItem['has_dropdown'] == 1) ? 'checked' : ''; ?>
                                                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                                </div>
                                                <div class="ml-3 text-sm">
                                                    <label for="has_dropdown" class="font-medium text-gray-700">Has Dropdown</label>
                                                    <p class="text-gray-500">Enable dropdown menu for this item</p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <?php if($editItem): ?>
                                        <div class="pt-2">
                                            <div class="relative flex items-start">
                                                <div class="flex items-center h-5">
                                                    <input type="checkbox" name="is_active" id="is_active"
                                                        <?php echo ($editItem && $editItem['is_active'] == 1) ? 'checked' : ''; ?>
                                                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                                </div>
                                                <div class="ml-3 text-sm">
                                                    <label for="is_active" class="font-medium text-gray-700">Active</label>
                                                    <p class="text-gray-500">Display this item in the navbar</p>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="flex justify-between mt-8">
                                        <?php if($editItem): ?>
                                        <button type="submit" name="update_item" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <i class="bx bx-save mr-2"></i> Update Item
                                        </button>
                                        
                                        <button type="submit" name="delete_item" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" onclick="return confirm('Are you sure you want to delete this menu item? All child items will also be deleted.');">
                                            <i class="bx bx-trash mr-2"></i> Delete
                                        </button>
                                        <?php else: ?>
                                        <button type="submit" name="add_item" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <i class="bx bx-plus mr-2"></i> Add Item
                                        </button>
                                        
                                        <a href="manage-navbar.php" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            Cancel
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Dropdown Structure Guidelines -->
                        <div class="mt-6 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b border-blue-100">
                                <h3 class="text-base font-medium text-blue-800 flex items-center">
                                    <i class="bx bx-info-circle text-lg mr-2"></i> Dropdown Structure
                                </h3>
                            </div>
                            <div class="px-6 py-4">
                                <div class="text-sm text-blue-800 mb-4">
                                    To create a dropdown menu, follow these steps:
                                </div>
                                <ol class="list-decimal pl-5 space-y-3 text-sm text-blue-800">
                                    <li class="pb-2 border-b border-blue-100">
                                        <strong>Create a parent item</strong>: 
                                        <p class="mt-1 text-blue-600">Set the link to "#" and check "Has Dropdown"</p>
                                    </li>
                                    <li class="pb-2 border-b border-blue-100">
                                        <strong>Add child items</strong>: 
                                        <p class="mt-1 text-blue-600">Create new items and select the parent item from the dropdown</p>
                                    </li>
                                    <li>
                                        <strong>Structure example</strong>:
                                        <div class="mt-2 bg-white bg-opacity-50 p-3 rounded-md">
                                            <div class="font-medium">Products (parent, has_dropdown=true)</div>
                                            <div class="pl-4 mt-1">
                                                <div>└─ Product A (child)</div>
                                                <div>└─ Product B (child)</div>
                                                <div>└─ Product C (child)</div>
                                            </div>
                                        </div>
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Navbar Settings Tab -->
                <?php if ($activeTab === 'settings'): ?>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Navbar Settings</h2>
                        <p class="text-sm text-gray-500 mt-1">Customize logos and action button</p>
                    </div>
                    
                    <div class="p-6">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <!-- Logo Settings -->
                            <div class="mb-8">
                                <h3 class="text-md font-medium text-gray-800 px-4 py-2 bg-gray-50 rounded-md mb-4 flex items-center">
                                    <i class="bx bx-image-alt mr-2 text-lg text-gray-600"></i>
                                    Desktop Logo Settings
                                </h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-white p-4 rounded-md border border-gray-200">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Primary Logo</label>
                                        
                                        <!-- Logo preview -->
                                        <div class="mb-3 p-4 bg-gray-50 border border-gray-200 rounded-md flex items-center justify-center">
                                            <?php if(!empty($navbarSettings['logo_path'])): ?>
                                            <img src="../<?php echo htmlspecialchars($navbarSettings['logo_path']); ?>" 
                                                 alt="Logo" class="max-h-16 max-w-full object-contain">
                                            <?php else: ?>
                                            <div class="text-gray-400 text-sm">No logo uploaded</div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- File upload -->
                                        <div class="mt-2">
                                            <label class="block text-sm text-gray-600 mb-1">Upload New Logo:</label>
                                            <input type="file" name="logo_file" accept="image/*" 
                                                   class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                            <input type="hidden" name="logo_path" value="<?php echo htmlspecialchars($navbarSettings['logo_path'] ?? ''); ?>">
                                        </div>
                                        
                                        <div class="grid grid-cols-2 gap-4 mt-3">
                                            <div>
                                                <label class="block text-sm text-gray-600 mb-1">Height (px):</label>
                                                <input type="number" name="logo_height" value="<?php echo (int)($navbarSettings['logo_height'] ?? 64); ?>" 
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm text-gray-600 mb-1">Alt Text:</label>
                                                <input type="text" name="logo_alt" value="<?php echo htmlspecialchars($navbarSettings['logo_alt'] ?? 'Logo'); ?>" 
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Secondary Logo</label>
                                        
                                        <!-- Logo preview -->
                                        <div class="mb-3 p-4 bg-gray-50 border border-gray-200 rounded-md flex items-center justify-center">
                                            <?php if(!empty($navbarSettings['logo_two_path'])): ?>
                                            <img src="../<?php echo htmlspecialchars($navbarSettings['logo_two_path']); ?>" 
                                                 alt="Secondary Logo" class="max-h-16 max-w-full object-contain">
                                            <?php else: ?>
                                            <div class="text-gray-400 text-sm">No logo uploaded</div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- File upload -->
                                        <div class="mt-2">
                                            <label class="block text-sm text-gray-600 mb-1">Upload New Logo:</label>
                                            <input type="file" name="logo_two_file" accept="image/*" 
                                                   class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                            <input type="hidden" name="logo_two_path" value="<?php echo htmlspecialchars($navbarSettings['logo_two_path'] ?? ''); ?>">
                                        </div>
                                        
                                        <div class="grid grid-cols-2 gap-4 mt-3">
                                            <div>
                                                <label class="block text-sm text-gray-600 mb-1">Height (px):</label>
                                                <input type="number" name="logo_two_height" value="<?php echo (int)($navbarSettings['logo_two_height'] ?? 64); ?>" 
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm text-gray-600 mb-1">Alt Text:</label>
                                                <input type="text" name="logo_two_alt" value="<?php echo htmlspecialchars($navbarSettings['logo_two_alt'] ?? 'Logo'); ?>" 
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Mobile Logo Settings -->
                            <div class="mb-8">
                                <h3 class="text-md font-medium text-gray-800 px-4 py-2 bg-gray-50 rounded-md mb-4 flex items-center">
                                    <i class="bx bx-mobile-alt mr-2 text-lg text-gray-600"></i>
                                    Mobile Logo Settings
                                </h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-white p-4 rounded-md border border-gray-200">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Mobile Primary Logo</label>
                                        
                                        <!-- Logo preview -->
                                        <div class="mb-3 p-4 bg-gray-50 border border-gray-200 rounded-md flex items-center justify-center">
                                            <?php if(!empty($navbarSettings['mobile_logo_path'])): ?>
                                            <img src="../<?php echo htmlspecialchars($navbarSettings['mobile_logo_path']); ?>" 
                                                 alt="Mobile Logo" class="max-h-16 max-w-full object-contain">
                                            <?php else: ?>
                                            <div class="text-gray-400 text-sm">No logo uploaded</div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- File upload -->
                                        <div class="mt-2">
                                            <label class="block text-sm text-gray-600 mb-1">Upload New Logo:</label>
                                            <input type="file" name="mobile_logo_file" accept="image/*" 
                                                   class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                            <input type="hidden" name="mobile_logo_path" value="<?php echo htmlspecialchars($navbarSettings['mobile_logo_path'] ?? ''); ?>">
                                        </div>
                                        
                                        <div class="grid grid-cols-2 gap-4 mt-3">
                                            <div>
                                                <label class="block text-sm text-gray-600 mb-1">Height (px):</label>
                                                <input type="number" name="mobile_logo_height" value="<?php echo (int)($navbarSettings['mobile_logo_height'] ?? 64); ?>" 
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm text-gray-600 mb-1">Alt Text:</label>
                                                <input type="text" name="mobile_logo_alt" value="<?php echo htmlspecialchars($navbarSettings['mobile_logo_alt'] ?? 'Logo'); ?>" 
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Mobile Secondary Logo</label>
                                        
                                        <!-- Logo preview -->
                                        <div class="mb-3 p-4 bg-gray-50 border border-gray-200 rounded-md flex items-center justify-center">
                                            <?php if(!empty($navbarSettings['mobile_logo_two_path'])): ?>
                                            <img src="../<?php echo htmlspecialchars($navbarSettings['mobile_logo_two_path']); ?>" 
                                                 alt="Mobile Secondary Logo" class="max-h-16 max-w-full object-contain">
                                            <?php else: ?>
                                            <div class="text-gray-400 text-sm">No logo uploaded</div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- File upload -->
                                        <div class="mt-2">
                                            <label class="block text-sm text-gray-600 mb-1">Upload New Logo:</label>
                                            <input type="file" name="mobile_logo_two_file" accept="image/*" 
                                                   class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                            <input type="hidden" name="mobile_logo_two_path" value="<?php echo htmlspecialchars($navbarSettings['mobile_logo_two_path'] ?? ''); ?>">
                                        </div>
                                        
                                        <div class="grid grid-cols-2 gap-4 mt-3">
                                            <div>
                                                <label class="block text-sm text-gray-600 mb-1">Height (px):</label>
                                                <input type="number" name="mobile_logo_two_height" value="<?php echo (int)($navbarSettings['mobile_logo_two_height'] ?? 64); ?>" 
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm text-gray-600 mb-1">Alt Text:</label>
                                                <input type="text" name="mobile_logo_two_alt" value="<?php echo htmlspecialchars($navbarSettings['mobile_logo_two_alt'] ?? 'Logo'); ?>" 
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Upload sizes info -->
                                <div class="mt-3 bg-blue-50 p-3 rounded-md text-sm text-blue-700 border border-blue-100">
                                    <div class="flex items-start">
                                        <i class="bx bx-info-circle mt-0.5 mr-2 text-blue-500"></i>
                                        <div>
                                            <strong>Recommended logo sizes:</strong>
                                            <ul class="mt-1 ml-4 list-disc space-y-1">
                                                <li>Logo dimensions: 180-240px width, 60-80px height</li>
                                                <li>File formats: PNG, SVG, WEBP recommended (with transparency)</li>
                                                <li>Maximum file size: 500KB</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Action Button Settings -->
                            <div class="mb-8">
                                <h3 class="text-md font-medium text-gray-800 px-4 py-2 bg-gray-50 rounded-md mb-4 flex items-center">
                                    <i class="bx bx-pointer mr-2 text-lg text-gray-600"></i>
                                    Action Button Settings
                                </h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Button Text</label>
                                        <div class="mt-1 flex rounded-md shadow-sm">
                                            <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                                                <i class="bx bx-text"></i>
                                            </span>
                                            <input type="text" name="action_button_text" value="<?php echo htmlspecialchars($navbarSettings['action_button_text'] ?? 'Konsultasi Sekarang'); ?>" 
                                                class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Button Link</label>
                                        <div class="mt-1 flex rounded-md shadow-sm">
                                            <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                                                <i class="bx bx-link"></i>
                                            </span>
                                            <input type="text" name="action_button_link" value="<?php echo htmlspecialchars($navbarSettings['action_button_link'] ?? 'https://wa.me/6287735426107'); ?>" 
                                                class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Button Target</label>
                                    <select name="action_button_target" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                        <option value="_self" <?php echo ($navbarSettings['action_button_target'] ?? '') === '_self' ? 'selected' : ''; ?>>Same Window (_self)</option>
                                        <option value="_blank" <?php echo ($navbarSettings['action_button_target'] ?? '') === '_blank' ? 'selected' : ''; ?>>New Tab (_blank)</option>
                                    </select>
                                </div>
                                
                                <!-- Preview -->
                                <div class="mt-6 bg-gray-50 border border-gray-200 rounded-md p-4">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Button Preview:</h4>
                                    <div class="inline-block rounded-md overflow-hidden">
                                        <a href="#" class="flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md">
                                            <?php echo htmlspecialchars($navbarSettings['action_button_text'] ?? 'Konsultasi Sekarang'); ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                                <div class="text-sm text-gray-500">
                                    Last updated: <?php echo date('M d, Y H:i'); ?>
                                </div>
                                <button type="submit" name="update_settings" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <i class="bx bx-save mr-2"></i>
                                    Save Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Footer -->
                <div class="text-center text-gray-500 text-sm mt-8 pb-6">
                    <p>&copy; 2023 Akademi Merdeka Admin Dashboard. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Toggle parent_id field based on "Has Dropdown" checkbox
        const hasDropdownCheckbox = document.getElementById('has_dropdown');
        if (hasDropdownCheckbox) {
            hasDropdownCheckbox.addEventListener('change', function() {
                const linkField = document.getElementById('link');
                if (this.checked && linkField.value === '') {
                    linkField.value = '#';
                }
            });
        }
        
        // Toggle children visibility
        document.addEventListener('DOMContentLoaded', function() {
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