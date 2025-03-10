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

// Process form submissions
$message = '';
$messageType = '';

// Handle batch actions
if (isset($_POST['batch_action']) && isset($_POST['selected_items']) && !empty($_POST['selected_items'])) {
    $action = $_POST['batch_action'];
    $items = explode(',', $_POST['selected_items']);

    if (!empty($items)) {
        try {
            $conn->beginTransaction();

            switch ($action) {
                case 'activate':
                    $stmt = $conn->prepare("UPDATE navbar_items SET is_active = 1 WHERE id IN (" . implode(',', array_fill(0, count($items), '?')) . ")");
                    foreach ($items as $key => $id) {
                        $stmt->bindValue($key + 1, $id);
                    }
                    $stmt->execute();
                    $message = "Selected items activated successfully!";
                    $messageType = "success";
                    break;

                case 'deactivate':
                    $stmt = $conn->prepare("UPDATE navbar_items SET is_active = 0 WHERE id IN (" . implode(',', array_fill(0, count($items), '?')) . ")");
                    foreach ($items as $key => $id) {
                        $stmt->bindValue($key + 1, $id);
                    }
                    $stmt->execute();
                    $message = "Selected items deactivated successfully!";
                    $messageType = "success";
                    break;

                case 'delete':
                    // Check if any of the items have children
                    $hasChildrenIds = [];

                    foreach ($items as $id) {
                        $stmt = $conn->prepare("SELECT COUNT(*) FROM navbar_items WHERE parent_id = ?");
                        $stmt->bindValue(1, $id);
                        $stmt->execute();

                        if ($stmt->fetchColumn() > 0) {
                            $hasChildrenIds[] = $id;
                        }
                    }

                    if (!empty($hasChildrenIds)) {
                        // First delete child items for parents that have children
                        $stmt = $conn->prepare("DELETE FROM navbar_items WHERE parent_id IN (" . implode(',', array_fill(0, count($hasChildrenIds), '?')) . ")");
                        foreach ($hasChildrenIds as $key => $id) {
                            $stmt->bindValue($key + 1, $id);
                        }
                        $stmt->execute();
                    }

                    // Then delete the selected items
                    $stmt = $conn->prepare("DELETE FROM navbar_items WHERE id IN (" . implode(',', array_fill(0, count($items), '?')) . ")");
                    foreach ($items as $key => $id) {
                        $stmt->bindValue($key + 1, $id);
                    }
                    $stmt->execute();

                    $message = "Selected items deleted successfully!";
                    $messageType = "success";
                    break;
            }

            $conn->commit();
        } catch (PDOException $e) {
            $conn->rollBack();
            $message = "Error processing batch action: " . $e->getMessage();
            $messageType = "error";
        }
    }
}

// Handle navbar settings update
if (isset($_POST['update_navbar'])) {
    // Get form data
    $settings = [
        'navbar_logo' => $_POST['navbar_logo'],
        'navbar_logo_alt' => $_POST['navbar_logo_alt'],
        'navbar_button_text' => $_POST['navbar_button_text'],
        'navbar_button_url' => $_POST['navbar_button_url'],
        'navbar_bg_color' => $_POST['navbar_bg_color'],
        'navbar_text_color' => $_POST['navbar_text_color']
    ];

    try {
        // Begin transaction
        $conn->beginTransaction();

        foreach ($settings as $key => $value) {
            // Check if setting exists
            $stmt = $conn->prepare("SELECT COUNT(*) FROM site_settings WHERE setting_key = :key");
            $stmt->bindParam(':key', $key);
            $stmt->execute();

            if ($stmt->fetchColumn() > 0) {
                // Update existing setting
                $stmt = $conn->prepare("UPDATE site_settings SET setting_value = :value WHERE setting_key = :key");
            } else {
                // Insert new setting
                $stmt = $conn->prepare("INSERT INTO site_settings (setting_key, setting_value, setting_group) VALUES (:key, :value, 'navbar')");
            }

            $stmt->bindParam(':key', $key);
            $stmt->bindParam(':value', $value);
            $stmt->execute();
        }

        // Commit transaction
        $conn->commit();

        $message = "Navbar settings updated successfully!";
        $messageType = "success";
    } catch (PDOException $e) {
        // Rollback transaction on error
        $conn->rollBack();

        $message = "Error updating navbar settings: " . $e->getMessage();
        $messageType = "error";
    }
}

// Handle menu item add
if (isset($_POST['add_menu_item'])) {
    $title = $_POST['title'];
    $url = $_POST['url'];
    $parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;
    $position = (int)$_POST['position'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    try {
        $stmt = $conn->prepare("INSERT INTO navbar_items (parent_id, title, url, position, is_active) VALUES (:parent_id, :title, :url, :position, :is_active)");
        $stmt->bindParam(':parent_id', $parent_id);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':url', $url);
        $stmt->bindParam(':position', $position);
        $stmt->bindParam(':is_active', $is_active);
        $stmt->execute();

        $message = "Menu item added successfully!";
        $messageType = "success";
    } catch (PDOException $e) {
        $message = "Error adding menu item: " . $e->getMessage();
        $messageType = "error";
    }
}

// Handle menu item update
if (isset($_POST['update_menu_item'])) {
    $id = (int)$_POST['id'];
    $title = $_POST['title'];
    $url = $_POST['url'];
    $parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;
    $position = (int)$_POST['position'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    try {
        $stmt = $conn->prepare("UPDATE navbar_items SET parent_id = :parent_id, title = :title, url = :url, position = :position, is_active = :is_active WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':parent_id', $parent_id);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':url', $url);
        $stmt->bindParam(':position', $position);
        $stmt->bindParam(':is_active', $is_active);
        $stmt->execute();

        $message = "Menu item updated successfully!";
        $messageType = "success";
    } catch (PDOException $e) {
        $message = "Error updating menu item: " . $e->getMessage();
        $messageType = "error";
    }
}

// Handle menu item delete
if (isset($_GET['delete_item']) && !empty($_GET['delete_item'])) {
    $id = (int)$_GET['delete_item'];

    try {
        // First check if this item has children
        $stmt = $conn->prepare("SELECT COUNT(*) FROM navbar_items WHERE parent_id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        if ($stmt->fetchColumn() > 0) {
            $message = "Cannot delete menu item with children. Please remove child items first.";
            $messageType = "error";
        } else {
            $stmt = $conn->prepare("DELETE FROM navbar_items WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $message = "Menu item deleted successfully!";
            $messageType = "success";
        }
    } catch (PDOException $e) {
        $message = "Error deleting menu item: " . $e->getMessage();
        $messageType = "error";
    }
}

// Get navbar settings
$navbar_settings = [
    'navbar_logo' => 'assets/images/logos/logo-akademi-merdeka.png',
    'navbar_logo_alt' => 'assets/images/logos/logo-2.png',
    'navbar_button_text' => 'Konsultasi Sekarang',
    'navbar_button_url' => 'https://wa.me/6287735426107',
    'navbar_bg_color' => '#ffffff',
    'navbar_text_color' => '#5a5c69'
];

try {
    // Get all settings from database
    $stmt = $conn->query("SELECT * FROM site_settings WHERE setting_group = 'navbar'");
    $settings = $stmt->fetchAll();

    // Assign settings to array
    foreach ($settings as $setting) {
        $navbar_settings[$setting['setting_key']] = $setting['setting_value'];
    }
} catch (PDOException $e) {
    // If error, use default settings
}

// Get menu items
$menu_items = [];
try {
    $stmt = $conn->query("SELECT * FROM navbar_items ORDER BY parent_id IS NULL DESC, position ASC");
    $menu_items = $stmt->fetchAll();
} catch (PDOException $e) {
    // If error, use empty array
}

// Organize menu items by hierarchy
$menu_hierarchy = [];
$menu_parents = [];

foreach ($menu_items as $item) {
    if ($item['parent_id'] === null) {
        $menu_hierarchy[$item['id']] = $item;
        $menu_hierarchy[$item['id']]['children'] = [];
    }
    $menu_parents[$item['id']] = $item;
}

foreach ($menu_items as $item) {
    if ($item['parent_id'] !== null && isset($menu_hierarchy[$item['parent_id']])) {
        $menu_hierarchy[$item['parent_id']]['children'][] = $item;
    }
}

// Get item for editing if in edit mode
$edit_item = null;
if (isset($_GET['edit_item']) && !empty($_GET['edit_item'])) {
    $id = (int)$_GET['edit_item'];

    try {
        $stmt = $conn->prepare("SELECT * FROM navbar_items WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $edit_item = $stmt->fetch();
        }
    } catch (PDOException $e) {
        $message = "Error fetching menu item: " . $e->getMessage();
        $messageType = "error";
    }
}

// Get username
$username = $_SESSION['username'] ?? 'Admin';

// Get active tab from URL or post
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : (isset($_POST['active_tab']) ? $_POST['active_tab'] : 'settings');
?>

<!doctype html>
<html lang="id">
<?php
include('components/head.php')
?>

<body class="bg-gray-100">
    <div class="min-h-screen flex flex-col lg:flex-row">
        <!-- Sidebar Component -->
        <?php include('components/sidebar.php'); ?>

        <!-- Main Content -->
        <div class="flex-1 lg:ml-64">
            <!-- Top Bar -->
            <div class="bg-white p-4 shadow flex justify-between items-center">
                <h1 class="text-xl font-semibold text-gray-800">Manage Navbar</h1>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600">Welcome, <?php echo htmlspecialchars($username); ?></span>
                </div>
            </div>

            <!-- Page Content -->
            <div class="p-6">
                <?php if (!empty($message)): ?>
                    <div class="mb-4 p-4 rounded <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>" role="alert">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <!-- Tab Navigation -->
                <div class="mb-6 border-b border-gray-200">
                    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                        <li class="mr-2">
                            <a href="?tab=settings" class="inline-block p-4 rounded-t-lg <?php echo $activeTab === 'settings' ? 'border-b-2 border-blue-600 text-blue-600' : 'border-b-2 border-transparent hover:text-gray-600 hover:border-gray-300'; ?>">
                                Navbar Settings
                            </a>
                        </li>
                        <li class="mr-2">
                            <a href="?tab=menu_items" class="inline-block p-4 rounded-t-lg <?php echo $activeTab === 'menu_items' ? 'border-b-2 border-blue-600 text-blue-600' : 'border-b-2 border-transparent hover:text-gray-600 hover:border-gray-300'; ?>">
                                Menu Items
                            </a>
                        </li>
                    </ul>
                </div>

                <?php if ($activeTab === 'settings'): ?>
                    <!-- Navbar Settings Tab -->
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4">Navbar Settings</h2>

                            <form method="POST" action="">
                                <input type="hidden" name="active_tab" value="settings">
                                <!-- Logo Settings -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div>
                                        <label for="navbar_logo" class="block text-sm font-medium text-gray-700 mb-1">Main Logo Path</label>
                                        <input type="text" id="navbar_logo" name="navbar_logo"
                                            value="<?php echo htmlspecialchars($navbar_settings['navbar_logo']); ?>"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">Path to the main logo image</p>
                                    </div>
                                    <div>
                                        <label for="navbar_logo_alt" class="block text-sm font-medium text-gray-700 mb-1">Alternative Logo Path</label>
                                        <input type="text" id="navbar_logo_alt" name="navbar_logo_alt"
                                            value="<?php echo htmlspecialchars($navbar_settings['navbar_logo_alt']); ?>"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">Path to the alternative logo image</p>
                                    </div>
                                </div>

                                <!-- Button Settings -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div>
                                        <label for="navbar_button_text" class="block text-sm font-medium text-gray-700 mb-1">Button Text</label>
                                        <input type="text" id="navbar_button_text" name="navbar_button_text"
                                            value="<?php echo htmlspecialchars($navbar_settings['navbar_button_text']); ?>"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label for="navbar_button_url" class="block text-sm font-medium text-gray-700 mb-1">Button URL</label>
                                        <input type="text" id="navbar_button_url" name="navbar_button_url"
                                            value="<?php echo htmlspecialchars($navbar_settings['navbar_button_url']); ?>"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                </div>

                                <!-- Color Settings -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div>
                                        <label for="navbar_bg_color" class="block text-sm font-medium text-gray-700 mb-1">Background Color</label>
                                        <div class="flex">
                                            <input type="text" id="navbar_bg_color" name="navbar_bg_color"
                                                value="<?php echo htmlspecialchars($navbar_settings['navbar_bg_color']); ?>"
                                                class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <div class="w-10 h-10 rounded-r-md border-t border-r border-b border-gray-300"
                                                style="background-color: <?php echo htmlspecialchars($navbar_settings['navbar_bg_color']); ?>"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="navbar_text_color" class="block text-sm font-medium text-gray-700 mb-1">Text Color</label>
                                        <div class="flex">
                                            <input type="text" id="navbar_text_color" name="navbar_text_color"
                                                value="<?php echo htmlspecialchars($navbar_settings['navbar_text_color']); ?>"
                                                class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <div class="w-10 h-10 rounded-r-md border-t border-r border-b border-gray-300"
                                                style="background-color: <?php echo htmlspecialchars($navbar_settings['navbar_text_color']); ?>"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="mt-6">
                                    <button type="submit" name="update_navbar" class="px-5 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <i class='bx bx-save mr-2'></i> Save Navbar Settings
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($activeTab === 'menu_items'): ?>
                    <!-- Menu Items Tab -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div class="lg:col-span-1">
                            <div class="bg-white rounded-lg shadow overflow-hidden">
                                <div class="p-6">
                                    <h2 class="text-lg font-semibold text-gray-800 mb-4">
                                        <?php echo $edit_item ? 'Edit Menu Item' : 'Add Menu Item'; ?>
                                    </h2>

                                    <form method="POST" action="">
                                        <input type="hidden" name="active_tab" value="menu_items">
                                        <?php if ($edit_item): ?>
                                            <input type="hidden" name="id" value="<?php echo $edit_item['id']; ?>">
                                        <?php endif; ?>

                                        <div class="mb-4">
                                            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                            <input type="text" id="title" name="title" required
                                                value="<?php echo $edit_item ? htmlspecialchars($edit_item['title']) : ''; ?>"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>

                                        <div class="mb-4">
                                            <label for="url" class="block text-sm font-medium text-gray-700 mb-1">URL</label>
                                            <input type="text" id="url" name="url" required
                                                value="<?php echo $edit_item ? htmlspecialchars($edit_item['url']) : ''; ?>"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <p class="mt-1 text-xs text-gray-500">
                                                Use './page.php' for local pages or full URLs for external links. Use '#' for dropdown parent items.
                                            </p>
                                        </div>

                                        <div class="mb-4">
                                            <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-1">Parent Menu</label>
                                            <select id="parent_id" name="parent_id"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <option value="">None (Top Level)</option>
                                                <?php foreach ($menu_parents as $parent): ?>
                                                    <?php if ($parent['parent_id'] === null && (!$edit_item || $parent['id'] != $edit_item['id'])): ?>
                                                        <option value="<?php echo $parent['id']; ?>" <?php echo ($edit_item && $edit_item['parent_id'] == $parent['id']) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($parent['title']); ?>
                                                        </option>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </select>
                                            <p class="mt-1 text-xs text-gray-500">
                                                Select parent menu item to create a dropdown menu item. Leave blank for top-level items.
                                            </p>
                                        </div>

                                        <div class="mb-4">
                                            <label for="position" class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                                            <input type="number" id="position" name="position" min="1"
                                                value="<?php echo $edit_item ? htmlspecialchars($edit_item['position']) : '1'; ?>"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <p class="mt-1 text-xs text-gray-500">
                                                Menu items are displayed in ascending order by position.
                                            </p>
                                        </div>

                                        <div class="mb-4">
                                            <label class="flex items-center">
                                                <input type="checkbox" name="is_active"
                                                    <?php echo (!$edit_item || $edit_item['is_active']) ? 'checked' : ''; ?>
                                                    class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                                                <span class="ml-2 text-sm text-gray-700">Active</span>
                                            </label>
                                            <p class="mt-1 text-xs text-gray-500 ml-6">
                                                Inactive menu items will not be displayed on the site.
                                            </p>
                                        </div>

                                        <div class="mt-6 flex items-center space-x-2">
                                            <button type="submit" name="<?php echo $edit_item ? 'update_menu_item' : 'add_menu_item'; ?>"
                                                class="px-5 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <i class='bx bx-save mr-2'></i> <?php echo $edit_item ? 'Update Menu Item' : 'Add Menu Item'; ?>
                                            </button>

                                            <?php if ($edit_item): ?>
                                                <a href="?tab=menu_items" class="px-5 py-2 bg-gray-300 text-gray-700 font-medium rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                                    Cancel
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="lg:col-span-2">
                            <!-- Batch Actions -->
                            <div class="bg-white rounded-lg shadow overflow-hidden mb-4">
                                <div class="p-4 border-b border-gray-200">
                                    <h3 class="text-base font-semibold text-gray-800">Batch Actions</h3>
                                </div>
                                <div class="p-4">
                                    <form method="POST" action="" id="batchForm">
                                        <input type="hidden" name="active_tab" value="menu_items">
                                        <input type="hidden" name="batch_action" id="batchActionType" value="">
                                        <input type="hidden" name="selected_items" id="selectedItems" value="">

                                        <div class="flex items-center space-x-4">
                                            <label class="text-sm text-gray-700">With selected:</label>
                                            <button type="button" onclick="submitBatchAction('activate')" class="px-3 py-1.5 bg-green-100 text-green-700 rounded-md hover:bg-green-200 transition-colors">
                                                <i class='bx bx-check-circle'></i> Activate
                                            </button>
                                            <button type="button" onclick="submitBatchAction('deactivate')" class="px-3 py-1.5 bg-yellow-100 text-yellow-700 rounded-md hover:bg-yellow-200 transition-colors">
                                                <i class='bx bx-x-circle'></i> Deactivate
                                            </button>
                                            <button type="button" onclick="confirmBatchDelete()" class="px-3 py-1.5 bg-red-100 text-red-700 rounded-md hover:bg-red-200 transition-colors">
                                                <i class='bx bx-trash'></i> Delete
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Menu Items Table -->
                            <div class="bg-white rounded-lg shadow mb-6">
                                <div class="p-6 border-b border-gray-200">
                                    <h2 class="text-lg font-semibold text-gray-800 mb-2">Menu Structure</h2>
                                    <p class="text-sm text-gray-500">Manage your navigation menu items and structure</p>
                                </div>

                                <!-- Table Container -->
                                <div class="relative">
                                    <div class="overflow-x-auto max-h-[70vh] overflow-y-auto" id="tableScroll">
                                        <table class="min-w-full divide-y divide-gray-200 table-fixed">
                                            <thead class="bg-gray-50 sticky top-0 z-10">
                                                <tr>
                                                    <th scope="col" class="w-16 px-4 py-3 text-center">
                                                        <input type="checkbox" id="selectAll" class="w-4 h-4 text-blue-600 bg-gray-100 rounded border-gray-300 focus:ring-blue-500">
                                                    </th>
                                                    <th scope="col" class="w-64 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                                    <th scope="col" class="w-64 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL</th>
                                                    <th scope="col" class="w-28 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                                                    <th scope="col" class="w-28 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                    <th scope="col" class="w-48 px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                <?php foreach ($menu_hierarchy as $parent): ?>
                                                    <tr class="bg-gray-50 hover:bg-gray-100 transition-colors <?php echo (isset($_GET['edit_item']) && $_GET['edit_item'] == $parent['id']) ? 'bg-blue-50' : ''; ?>">
                                                        <td class="px-4 py-4 text-center">
                                                            <input type="checkbox" name="menu_item[]" value="<?php echo $parent['id']; ?>" class="w-4 h-4 text-blue-600 bg-gray-100 rounded border-gray-300 focus:ring-blue-500 menu-item-checkbox">
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                                            <?php echo htmlspecialchars($parent['title']); ?>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 truncate max-w-xs" title="<?php echo htmlspecialchars($parent['url']); ?>">
                                                            <?php echo htmlspecialchars($parent['url']); ?>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                            <?php echo $parent['position']; ?>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            <?php if ($parent['is_active']): ?>
                                                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                                    Active
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                                    Inactive
                                                                </span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                                            <div class="flex justify-center space-x-2">
                                                                <a href="?tab=menu_items&edit_item=<?php echo $parent['id']; ?>"
                                                                    class="px-3 py-1.5 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors">
                                                                    <i class='bx bx-edit'></i> Edit
                                                                </a>
                                                                <button type="button"
                                                                    onclick="confirmDelete(<?php echo $parent['id']; ?>, '<?php echo htmlspecialchars(addslashes($parent['title'])); ?>', <?php echo !empty($parent['children']) ? 'true' : 'false'; ?>)"
                                                                    class="px-3 py-1.5 bg-red-100 text-red-700 rounded-md hover:bg-red-200 transition-colors">
                                                                    <i class='bx bx-trash'></i> Delete
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    <?php if (!empty($parent['children'])): ?>
                                                        <?php foreach ($parent['children'] as $child): ?>
                                                            <tr class="hover:bg-gray-100 transition-colors <?php echo (isset($_GET['edit_item']) && $_GET['edit_item'] == $child['id']) ? 'bg-blue-50' : ''; ?>">
                                                                <td class="px-4 py-4 text-center">
                                                                    <input type="checkbox" name="menu_item[]" value="<?php echo $child['id']; ?>" class="w-4 h-4 text-blue-600 bg-gray-100 rounded border-gray-300 focus:ring-blue-500 menu-item-checkbox">
                                                                </td>
                                                                <td class="px-6 py-4 whitespace-nowrap pl-12">
                                                                    <div class="flex items-center">
                                                                        <i class='bx bx-subdirectory-right mr-2 text-gray-400'></i>
                                                                        <span class="text-gray-900"><?php echo htmlspecialchars($child['title']); ?></span>
                                                                    </div>
                                                                </td>
                                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 truncate max-w-xs" title="<?php echo htmlspecialchars($child['url']); ?>">
                                                                    <?php echo htmlspecialchars($child['url']); ?>
                                                                </td>
                                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                                    <?php echo $child['position']; ?>
                                                                </td>
                                                                <td class="px-6 py-4 whitespace-nowrap">
                                                                    <?php if ($child['is_active']): ?>
                                                                        <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                                            Active
                                                                        </span>
                                                                    <?php else: ?>
                                                                        <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                                            Inactive
                                                                        </span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                                    <div class="flex justify-center space-x-2">
                                                                        <a href="?tab=menu_items&edit_item=<?php echo $child['id']; ?>"
                                                                            class="px-3 py-1.5 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors">
                                                                            <i class='bx bx-edit'></i> Edit
                                                                        </a>
                                                                        <button type="button"
                                                                            onclick="confirmDelete(<?php echo $child['id']; ?>, '<?php echo htmlspecialchars(addslashes($child['title'])); ?>', false)"
                                                                            class="px-3 py-1.5 bg-red-100 text-red-700 rounded-md hover:bg-red-200 transition-colors">
                                                                            <i class='bx bx-trash'></i> Delete
                                                                        </button>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Empty state message -->
                                <?php if (empty($menu_items)): ?>
                                    <div class="p-6">
                                        <div class="bg-blue-50 text-blue-700 p-4 rounded flex items-start">
                                            <i class='bx bx-info-circle text-xl mr-2 mt-0.5'></i>
                                            <p>No menu items found. Add your first menu item using the form.</p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="hidden fixed inset-0 bg-gray-500 bg-opacity-75 items-center justify-center z-50">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <div class="text-center">
                <i class='bx bx-error-circle text-red-500 text-5xl mb-4'></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Confirm Deletion</h3>
                <p class="text-sm text-gray-500 mb-4" id="deleteMessage">
                    Are you sure you want to delete this menu item?
                </p>
                <div class="flex justify-center space-x-4">
                    <button type="button" id="cancelDelete" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors">
                        Cancel
                    </button>
                    <a href="#" id="confirmDeleteBtn" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                        Yes, Delete
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Color picker functionality
        document.getElementById('navbar_bg_color')?.addEventListener('input', function() {
            this.nextElementSibling.style.backgroundColor = this.value;
        });

        document.getElementById('navbar_text_color')?.addEventListener('input', function() {
            this.nextElementSibling.style.backgroundColor = this.value;
        });

        // Delete confirmation modal
        function confirmDelete(id, title, hasChildren) {
            const modal = document.getElementById('deleteModal');
            const message = document.getElementById('deleteMessage');
            const confirmBtn = document.getElementById('confirmDeleteBtn');

            // Set the message based on whether the item has children
            if (hasChildren) {
                message.innerHTML = `Are you sure you want to delete <strong>${title}</strong>?<br><br>This will also remove all child menu items.`;
            } else {
                message.innerHTML = `Are you sure you want to delete <strong>${title}</strong>?`;
            }

            // Set the confirmation button link
            confirmBtn.href = `?tab=menu_items&delete_item=${id}`;

            // Show the modal
            modal.classList.remove('hidden');

            // Set up event listener for the cancel button
            document.getElementById('cancelDelete').addEventListener('click', function() {
                modal.classList.add('hidden');
            });

            // Close modal when clicking outside
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.add('hidden');
                }
            });
        }

        // Batch actions and select all functionality
        document.getElementById('selectAll')?.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.menu-item-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        function submitBatchAction(action) {
            const selectedCheckboxes = document.querySelectorAll('.menu-item-checkbox:checked');
            if (selectedCheckboxes.length === 0) {
                alert('Please select at least one menu item.');
                return;
            }

            const selectedIds = Array.from(selectedCheckboxes).map(checkbox => checkbox.value);
            document.getElementById('batchActionType').value = action;
            document.getElementById('selectedItems').value = selectedIds.join(',');
            document.getElementById('batchForm').submit();
        }

        function confirmBatchDelete() {
            const selectedCheckboxes = document.querySelectorAll('.menu-item-checkbox:checked');
            if (selectedCheckboxes.length === 0) {
                alert('Please select at least one menu item.');
                return;
            }

            if (confirm(`Are you sure you want to delete ${selectedCheckboxes.length} menu item(s)?`)) {
                submitBatchAction('delete');
            }
        }
    </script>
</body>
</html>