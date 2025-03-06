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

// Process form submissions
$message = '';
$messageType = '';

// Handle footer settings update
if(isset($_POST['update_footer'])) {
    // Get form data
    $settings = [
        'footer_logo' => $_POST['footer_logo'],
        'footer_company_name' => $_POST['footer_company_name'],
        'footer_company_address' => $_POST['footer_company_address'],
        'footer_company_phone' => $_POST['footer_company_phone'],
        'footer_company_email' => $_POST['footer_company_email'],
        'footer_copyright_text' => $_POST['footer_copyright_text'],
        'footer_text_color' => $_POST['footer_text_color'],
        'footer_whatsapp_link' => $_POST['footer_whatsapp_link'],
        // New gradient settings
        'footer_gradient_direction' => $_POST['footer_gradient_direction'],
        'footer_gradient_start_color' => $_POST['footer_gradient_start_color'],
        'footer_gradient_end_color' => $_POST['footer_gradient_end_color'],
        // New bulletin settings
        'footer_bulletin_title' => $_POST['footer_bulletin_title'],
        'footer_bulletin_description' => $_POST['footer_bulletin_description'],
        'footer_newsletter_action' => $_POST['footer_newsletter_action']
    ];
    
    try {
        // Begin transaction
        $conn->beginTransaction();
        
        foreach($settings as $key => $value) {
            // Check if setting exists
            $stmt = $conn->prepare("SELECT COUNT(*) FROM site_settings WHERE setting_key = :key");
            $stmt->bindParam(':key', $key);
            $stmt->execute();
            
            if($stmt->fetchColumn() > 0) {
                // Update existing setting
                $stmt = $conn->prepare("UPDATE site_settings SET setting_value = :value WHERE setting_key = :key");
            } else {
                // Insert new setting
                $stmt = $conn->prepare("INSERT INTO site_settings (setting_key, setting_value, setting_group) VALUES (:key, :value, 'footer')");
            }
            
            $stmt->bindParam(':key', $key);
            $stmt->bindParam(':value', $value);
            $stmt->execute();
        }
        
        // Commit transaction
        $conn->commit();
        
        $message = "Footer settings updated successfully!";
        $messageType = "success";
    } catch(PDOException $e) {
        // Rollback transaction on error
        $conn->rollBack();
        
        $message = "Error updating footer settings: " . $e->getMessage();
        $messageType = "error";
    }
}

// Add new footer link
if(isset($_POST['add_link'])) {
    $section = $_POST['section'];
    $title = $_POST['title'];
    $url = $_POST['url'];
    $position = (int)$_POST['position'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    try {
        $stmt = $conn->prepare("INSERT INTO footer_links (section, title, url, position, is_active) VALUES (:section, :title, :url, :position, :is_active)");
        $stmt->bindParam(':section', $section);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':url', $url);
        $stmt->bindParam(':position', $position);
        $stmt->bindParam(':is_active', $is_active);
        $stmt->execute();
        
        $message = "Footer link added successfully!";
        $messageType = "success";
    } catch(PDOException $e) {
        $message = "Error adding footer link: " . $e->getMessage();
        $messageType = "error";
    }
}

// Update footer link
if(isset($_POST['update_link'])) {
    $id = (int)$_POST['id'];
    $section = $_POST['section'];
    $title = $_POST['title'];
    $url = $_POST['url'];
    $position = (int)$_POST['position'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    try {
        $stmt = $conn->prepare("UPDATE footer_links SET section = :section, title = :title, url = :url, position = :position, is_active = :is_active WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':section', $section);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':url', $url);
        $stmt->bindParam(':position', $position);
        $stmt->bindParam(':is_active', $is_active);
        $stmt->execute();
        
        $message = "Footer link updated successfully!";
        $messageType = "success";
    } catch(PDOException $e) {
        $message = "Error updating footer link: " . $e->getMessage();
        $messageType = "error";
    }
}

// Delete footer link
if(isset($_GET['delete_link']) && !empty($_GET['delete_link'])) {
    $id = (int)$_GET['delete_link'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM footer_links WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $message = "Footer link deleted successfully!";
        $messageType = "success";
    } catch(PDOException $e) {
        $message = "Error deleting footer link: " . $e->getMessage();
        $messageType = "error";
    }
}

// Add newsletter form field
if(isset($_POST['add_field'])) {
    $field_name = $_POST['field_name'];
    $field_label = $_POST['field_label'];
    $field_type = $_POST['field_type'];
    $placeholder = $_POST['placeholder'];
    $position = (int)$_POST['field_position'];
    $is_required = isset($_POST['is_required']) ? 1 : 0;
    $is_active = isset($_POST['field_is_active']) ? 1 : 0;
    
    try {
        $stmt = $conn->prepare("INSERT INTO bulletin_fields (field_name, field_label, field_type, is_required, placeholder, position, is_active) 
                               VALUES (:field_name, :field_label, :field_type, :is_required, :placeholder, :position, :is_active)");
        $stmt->bindParam(':field_name', $field_name);
        $stmt->bindParam(':field_label', $field_label);
        $stmt->bindParam(':field_type', $field_type);
        $stmt->bindParam(':is_required', $is_required);
        $stmt->bindParam(':placeholder', $placeholder);
        $stmt->bindParam(':position', $position);
        $stmt->bindParam(':is_active', $is_active);
        $stmt->execute();
        
        $message = "Newsletter form field added successfully!";
        $messageType = "success";
    } catch(PDOException $e) {
        $message = "Error adding newsletter field: " . $e->getMessage();
        $messageType = "error";
    }
}

// Update newsletter form field
if(isset($_POST['update_field'])) {
    $id = (int)$_POST['field_id'];
    $field_name = $_POST['field_name'];
    $field_label = $_POST['field_label'];
    $field_type = $_POST['field_type'];
    $placeholder = $_POST['placeholder'];
    $position = (int)$_POST['field_position'];
    $is_required = isset($_POST['is_required']) ? 1 : 0;
    $is_active = isset($_POST['field_is_active']) ? 1 : 0;
    
    try {
        $stmt = $conn->prepare("UPDATE bulletin_fields SET field_name = :field_name, field_label = :field_label, 
                               field_type = :field_type, is_required = :is_required, placeholder = :placeholder, 
                               position = :position, is_active = :is_active WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':field_name', $field_name);
        $stmt->bindParam(':field_label', $field_label);
        $stmt->bindParam(':field_type', $field_type);
        $stmt->bindParam(':is_required', $is_required);
        $stmt->bindParam(':placeholder', $placeholder);
        $stmt->bindParam(':position', $position);
        $stmt->bindParam(':is_active', $is_active);
        $stmt->execute();
        
        $message = "Newsletter form field updated successfully!";
        $messageType = "success";
    } catch(PDOException $e) {
        $message = "Error updating newsletter field: " . $e->getMessage();
        $messageType = "error";
    }
}

// Delete newsletter form field
if(isset($_GET['delete_field']) && !empty($_GET['delete_field'])) {
    $id = (int)$_GET['delete_field'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM bulletin_fields WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $message = "Newsletter form field deleted successfully!";
        $messageType = "success";
    } catch(PDOException $e) {
        $message = "Error deleting newsletter field: " . $e->getMessage();
        $messageType = "error";
    }
}

// Get footer settings
$footer_settings = [
    'footer_logo' => 'assets/images/logos/logo-footer.png',
    'footer_company_name' => 'Akademi Merdeka',
    'footer_company_address' => 'Perumahan Kheandra Kalijaga<br>Harjamukti, Cirebon, Jawa Barat',
    'footer_company_phone' => '+62 877-3542-6107',
    'footer_company_email' => 'info@akademimerdeka.com',
    'footer_copyright_text' => 'Copyright © 2023 <a href="https://akademimerdeka.com/">Akademi Merdeka</a> as establisment date 2022',
    'footer_text_color' => '#ffffff',
    'footer_whatsapp_link' => 'https://wa.me/6287735426107',
    // Default gradient settings
    'footer_gradient_direction' => 'to bottom',
    'footer_gradient_start_color' => '#343a40',
    'footer_gradient_end_color' => '#1a1e21',
    // Default bulletin settings
    'footer_bulletin_title' => 'Bulletin',
    'footer_bulletin_description' => 'Informasi lain dapat diajukan kepada tim kami untuk ditindaklanjuti.',
    'footer_newsletter_action' => ''
];

try {
    // Get all settings from database
    $stmt = $conn->query("SELECT * FROM site_settings WHERE setting_group = 'footer'");
    $settings = $stmt->fetchAll();
    
    // Assign settings to array
    foreach($settings as $setting) {
        $footer_settings[$setting['setting_key']] = $setting['setting_value'];
    }
} catch(PDOException $e) {
    // If error, use default settings
}

// Get footer links
$footer_links = [];
try {
    $stmt = $conn->query("SELECT * FROM footer_links ORDER BY section, position");
    $footer_links = $stmt->fetchAll();
} catch(PDOException $e) {
    // If error, use empty array
}

// Get newsletter fields
$bulletin_fields = [];
try {
    $stmt = $conn->query("SELECT * FROM bulletin_fields ORDER BY position");
    $bulletin_fields = $stmt->fetchAll();
} catch(PDOException $e) {
    // If error, use empty array
    // Check if table exists
    try {
        $conn->query("SELECT 1 FROM bulletin_fields LIMIT 1");
    } catch(PDOException $e) {
        // Table doesn't exist, create it
        $conn->exec("
            CREATE TABLE IF NOT EXISTS bulletin_fields (
                id INT AUTO_INCREMENT PRIMARY KEY,
                field_name VARCHAR(50) NOT NULL,
                field_label VARCHAR(100) NOT NULL,
                field_type ENUM('text', 'email', 'textarea', 'select', 'checkbox') NOT NULL,
                is_required BOOLEAN DEFAULT FALSE,
                placeholder VARCHAR(255),
                position INT NOT NULL DEFAULT 0,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            );
            
            -- Insert default email field
            INSERT INTO bulletin_fields (field_name, field_label, field_type, is_required, placeholder, position, is_active)
            VALUES ('email', 'Email', 'email', TRUE, 'Enter Your Email', 1, TRUE);
        ");
        
        // Try again to get fields
        $stmt = $conn->query("SELECT * FROM bulletin_fields ORDER BY position");
        $bulletin_fields = $stmt->fetchAll();
    }
}

// Get link for editing if in edit mode
$edit_link = null;
if(isset($_GET['edit_link']) && !empty($_GET['edit_link'])) {
    $id = (int)$_GET['edit_link'];
    
    try {
        $stmt = $conn->prepare("SELECT * FROM footer_links WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $edit_link = $stmt->fetch();
        }
    } catch(PDOException $e) {
        $message = "Error fetching footer link: " . $e->getMessage();
        $messageType = "error";
    }
}

// Get field for editing if in edit mode
$edit_field = null;
if(isset($_GET['edit_field']) && !empty($_GET['edit_field'])) {
    $id = (int)$_GET['edit_field'];
    
    try {
        $stmt = $conn->prepare("SELECT * FROM bulletin_fields WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $edit_field = $stmt->fetch();
        }
    } catch(PDOException $e) {
        $message = "Error fetching newsletter field: " . $e->getMessage();
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
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Footer - Akademi Merdeka</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Boxicons -->
    <link rel="stylesheet" href="../assets/css/boxicons.min.css">
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
<body class="bg-gray-100">
    <div class="min-h-screen flex flex-col lg:flex-row">
        <!-- Sidebar Component -->
        <?php include('components/sidebar.php'); ?>
        
        <!-- Main Content -->
        <div class="flex-1 lg:ml-64">
            <!-- Top Bar -->
            <div class="bg-white p-4 shadow flex justify-between items-center">
                <h1 class="text-xl font-semibold text-gray-800">Manage Footer</h1>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600">Welcome, <?php echo htmlspecialchars($username); ?></span>
                    <img class="w-8 h-8 rounded-full" src="../assets/images/team/pp-1.png" alt="Profile">
                </div>
            </div>
            
            <!-- Page Content -->
            <div class="p-6">
                <?php if(!empty($message)): ?>
                <div class="mb-4 p-4 rounded <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>" role="alert">
                    <?php echo $message; ?>
                </div>
                <?php endif; ?>
                
                <!-- Tab Navigation -->
                <div class="mb-6 border-b border-gray-200">
                    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                        <li class="mr-2">
                            <a href="?tab=settings" class="inline-block p-4 rounded-t-lg <?php echo $activeTab === 'settings' ? 'border-b-2 border-blue-600 text-blue-600' : 'border-b-2 border-transparent hover:text-gray-600 hover:border-gray-300'; ?>">
                                Footer Settings
                            </a>
                        </li>
                        <li class="mr-2">
                            <a href="?tab=links" class="inline-block p-4 rounded-t-lg <?php echo $activeTab === 'links' ? 'border-b-2 border-blue-600 text-blue-600' : 'border-b-2 border-transparent hover:text-gray-600 hover:border-gray-300'; ?>">
                                Footer Links
                            </a>
                        </li>
                        <li class="mr-2">
                            <a href="?tab=bulletin" class="inline-block p-4 rounded-t-lg <?php echo $activeTab === 'bulletin' ? 'border-b-2 border-blue-600 text-blue-600' : 'border-b-2 border-transparent hover:text-gray-600 hover:border-gray-300'; ?>">
                                Bulletin Settings
                            </a>
                        </li>
                    </ul>
                </div>
                
                <?php if($activeTab === 'settings'): ?>
                <!-- Footer Settings Tab -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-lg shadow overflow-hidden">
                            <div class="p-6">
                                <h2 class="text-lg font-semibold text-gray-800 mb-4">Footer Settings</h2>
                                
                                <form method="POST" action="">
                                    <input type="hidden" name="active_tab" value="settings">
                                    
                                    <!-- Logo Settings -->
                                    <div class="mb-4">
                                        <label for="footer_logo" class="block text-sm font-medium text-gray-700 mb-1">Footer Logo Path</label>
                                        <input type="text" id="footer_logo" name="footer_logo" 
                                               value="<?php echo htmlspecialchars($footer_settings['footer_logo']); ?>"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">Path to the footer logo image</p>
                                    </div>
                                    
                                    <!-- Company Info -->
                                    <div class="mb-4">
                                        <label for="footer_company_name" class="block text-sm font-medium text-gray-700 mb-1">Company Name</label>
                                        <input type="text" id="footer_company_name" name="footer_company_name" 
                                               value="<?php echo htmlspecialchars($footer_settings['footer_company_name']); ?>"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="footer_company_address" class="block text-sm font-medium text-gray-700 mb-1">Company Address</label>
                                        <textarea id="footer_company_address" name="footer_company_address" rows="2"
                                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($footer_settings['footer_company_address']); ?></textarea>
                                        <p class="mt-1 text-xs text-gray-500">You can use HTML tags like &lt;br&gt; for line breaks</p>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <label for="footer_company_phone" class="block text-sm font-medium text-gray-700 mb-1">Company Phone</label>
                                            <input type="text" id="footer_company_phone" name="footer_company_phone" 
                                                   value="<?php echo htmlspecialchars($footer_settings['footer_company_phone']); ?>"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label for="footer_company_email" class="block text-sm font-medium text-gray-700 mb-1">Company Email</label>
                                            <input type="email" id="footer_company_email" name="footer_company_email" 
                                                   value="<?php echo htmlspecialchars($footer_settings['footer_company_email']); ?>"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="footer_copyright_text" class="block text-sm font-medium text-gray-700 mb-1">Copyright Text</label>
                                        <input type="text" id="footer_copyright_text" name="footer_copyright_text" 
                                               value="<?php echo htmlspecialchars($footer_settings['footer_copyright_text']); ?>"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">You can use HTML tags like &lt;a&gt; for links</p>
                                    </div>
                                    
                                    <!-- Gradient Settings -->
                                    <div class="mt-6 mb-4">
                                        <h3 class="text-base font-medium text-gray-800 mb-2">Background Gradient</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div>
                                                <label for="footer_gradient_direction" class="block text-sm font-medium text-gray-700 mb-1">Direction</label>
                                                <select id="footer_gradient_direction" name="footer_gradient_direction"
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    <option value="to right" <?php echo $footer_settings['footer_gradient_direction'] === 'to right' ? 'selected' : ''; ?>>Left to Right</option>
                                                    <option value="to left" <?php echo $footer_settings['footer_gradient_direction'] === 'to left' ? 'selected' : ''; ?>>Right to Left</option>
                                                    <option value="to bottom" <?php echo $footer_settings['footer_gradient_direction'] === 'to bottom' ? 'selected' : ''; ?>>Top to Bottom</option>
                                                    <option value="to top" <?php echo $footer_settings['footer_gradient_direction'] === 'to top' ? 'selected' : ''; ?>>Bottom to Top</option>
                                                    <option value="to bottom right" <?php echo $footer_settings['footer_gradient_direction'] === 'to bottom right' ? 'selected' : ''; ?>>Top Left to Bottom Right</option>
                                                    <option value="to bottom left" <?php echo $footer_settings['footer_gradient_direction'] === 'to bottom left' ? 'selected' : ''; ?>>Top Right to Bottom Left</option>
                                                    <option value="to top right" <?php echo $footer_settings['footer_gradient_direction'] === 'to top right' ? 'selected' : ''; ?>>Bottom Left to Top Right</option>
                                                    <option value="to top left" <?php echo $footer_settings['footer_gradient_direction'] === 'to top left' ? 'selected' : ''; ?>>Bottom Right to Top Left</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label for="footer_gradient_start_color" class="block text-sm font-medium text-gray-700 mb-1">Start Color</label>
                                                <div class="flex">
                                                    <input type="text" id="footer_gradient_start_color" name="footer_gradient_start_color" 
                                                           value="<?php echo htmlspecialchars($footer_settings['footer_gradient_start_color']); ?>"
                                                           class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    <input type="color" 
                                                           value="<?php echo htmlspecialchars($footer_settings['footer_gradient_start_color']); ?>"
                                                           onInput="document.getElementById('footer_gradient_start_color').value = this.value"
                                                           class="h-10 w-10 rounded-r-md border-t border-r border-b border-gray-300 p-0">
                                                </div>
                                            </div>
                                            <div>
                                                <label for="footer_gradient_end_color" class="block text-sm font-medium text-gray-700 mb-1">End Color</label>
                                                <div class="flex">
                                                    <input type="text" id="footer_gradient_end_color" name="footer_gradient_end_color" 
                                                           value="<?php echo htmlspecialchars($footer_settings['footer_gradient_end_color']); ?>"
                                                           class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    <input type="color" 
                                                           value="<?php echo htmlspecialchars($footer_settings['footer_gradient_end_color']); ?>"
                                                           onInput="document.getElementById('footer_gradient_end_color').value = this.value"
                                                           class="h-10 w-10 rounded-r-md border-t border-r border-b border-gray-300 p-0">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-2 h-10 w-full rounded-md"
                                             style="background-image: linear-gradient(<?php echo htmlspecialchars($footer_settings['footer_gradient_direction']); ?>, <?php echo htmlspecialchars($footer_settings['footer_gradient_start_color']); ?>, <?php echo htmlspecialchars($footer_settings['footer_gradient_end_color']); ?>);">
                                        </div>
                                    </div>
                                    
                                    <!-- Text Color -->
                                    <div class="mb-4">
                                        <label for="footer_text_color" class="block text-sm font-medium text-gray-700 mb-1">Text Color</label>
                                        <div class="flex">
                                            <input type="text" id="footer_text_color" name="footer_text_color" 
                                                   value="<?php echo htmlspecialchars($footer_settings['footer_text_color']); ?>"
                                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <input type="color" 
                                                   value="<?php echo htmlspecialchars($footer_settings['footer_text_color']); ?>"
                                                   onInput="document.getElementById('footer_text_color').value = this.value"
                                                   class="h-10 w-10 rounded-r-md border-t border-r border-b border-gray-300 p-0">
                                        </div>
                                    </div>
                                    
                                    <!-- Bulletin Settings -->
                                    <div class="mt-6 mb-4">
                                        <h3 class="text-base font-medium text-gray-800 mb-2">Bulletin Section</h3>
                                        <div class="grid grid-cols-1 gap-4">
                                            <div>
                                                <label for="footer_bulletin_title" class="block text-sm font-medium text-gray-700 mb-1">Bulletin Title</label>
                                                <input type="text" id="footer_bulletin_title" name="footer_bulletin_title" 
                                                       value="<?php echo htmlspecialchars($footer_settings['footer_bulletin_title']); ?>"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                            <div>
                                                <label for="footer_bulletin_description" class="block text-sm font-medium text-gray-700 mb-1">Bulletin Description</label>
                                                <textarea id="footer_bulletin_description" name="footer_bulletin_description" rows="2"
                                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($footer_settings['footer_bulletin_description']); ?></textarea>
                                            </div>
                                            <div>
                                                <label for="footer_newsletter_action" class="block text-sm font-medium text-gray-700 mb-1">Newsletter Form Action URL</label>
                                                <input type="text" id="footer_newsletter_action" name="footer_newsletter_action" 
                                                       value="<?php echo htmlspecialchars($footer_settings['footer_newsletter_action']); ?>"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <p class="mt-1 text-xs text-gray-500">Leave blank to use default form handling</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Submit Button -->
                                    <div class="mt-6">
                                        <button type="submit" name="update_footer" class="px-5 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <i class='bx bx-save mr-2'></i> Save Footer Settings
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Preview Section -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-lg shadow overflow-hidden">
                            <div class="p-6">
                                <h2 class="text-lg font-semibold text-gray-800 mb-4">Footer Preview</h2>
                                
                                <div class="p-4 rounded-lg" 
                                     style="background-image: linear-gradient(<?php echo htmlspecialchars($footer_settings['footer_gradient_direction']); ?>, <?php echo htmlspecialchars($footer_settings['footer_gradient_start_color']); ?>, <?php echo htmlspecialchars($footer_settings['footer_gradient_end_color']); ?>); color: <?php echo htmlspecialchars($footer_settings['footer_text_color']); ?>;">
                                    <div class="mb-4">
                                        <img src="../<?php echo htmlspecialchars($footer_settings['footer_logo']); ?>" alt="Footer Logo" class="h-12 mb-2">
                                        <p class="text-sm"><?php echo $footer_settings['footer_company_address']; ?></p>
                                        <p class="text-sm"><strong>Phone:</strong> <?php echo htmlspecialchars($footer_settings['footer_company_phone']); ?></p>
                                        <p class="text-sm"><strong>Email:</strong> <?php echo htmlspecialchars($footer_settings['footer_company_email']); ?></p>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <h3 class="font-medium mb-2">Layanan Kami</h3>
                                        <ul class="text-sm space-y-1">
                                            <li><a href="#" class="hover:opacity-75">Penerbitan Jurnal</a></li>
                                            <li><a href="#" class="hover:opacity-75">Pengolahan Statistik</a></li>
                                            <li><a href="#" class="hover:opacity-75">Pendampingan OJS</a></li>
                                        </ul>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <h3 class="font-medium mb-2"><?php echo htmlspecialchars($footer_settings['footer_bulletin_title']); ?></h3>
                                        <p class="text-sm"><?php echo htmlspecialchars($footer_settings['footer_bulletin_description']); ?></p>
                                        <div class="mt-2 flex">
                                            <input type="text" placeholder="Enter Your Email" class="text-xs px-2 py-1 w-full rounded-l-md border-0">
                                            <button class="bg-blue-600 px-2 rounded-r-md">
                                                <i class='bx bx-paper-plane'></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="border-t border-opacity-20 mt-4 pt-4 text-center text-sm">
                                        <?php echo $footer_settings['footer_copyright_text']; ?>
                                    </div>
                                </div>
                                
                                <p class="text-sm text-gray-500 mt-2">This is a simplified preview. Actual appearance may vary.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if($activeTab === 'links'): ?>
                <!-- Footer Links Tab -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-lg shadow overflow-hidden">
                            <div class="p-6">
                                <h2 class="text-lg font-semibold text-gray-800 mb-4">
                                    <?php echo $edit_link ? 'Edit Footer Link' : 'Add Footer Link'; ?>
                                </h2>
                                
                                <form method="POST" action="">
                                    <input type="hidden" name="active_tab" value="links">
                                    <?php if($edit_link): ?>
                                    <input type="hidden" name="id" value="<?php echo $edit_link['id']; ?>">
                                    <?php endif; ?>
                                    
                                    <div class="mb-4">
                                        <label for="section" class="block text-sm font-medium text-gray-700 mb-1">Section</label>
                                        <select id="section" name="section" required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="layanan" <?php echo ($edit_link && $edit_link['section'] == 'layanan') ? 'selected' : ''; ?>>Layanan Kami</option>
                                            <option value="informasi" <?php echo ($edit_link && $edit_link['section'] == 'informasi') ? 'selected' : ''; ?>>Informasi</option>
                                            <option value="support" <?php echo ($edit_link && $edit_link['section'] == 'support') ? 'selected' : ''; ?>>Support</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                        <input type="text" id="title" name="title" required 
                                               value="<?php echo $edit_link ? htmlspecialchars($edit_link['title']) : ''; ?>"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="url" class="block text-sm font-medium text-gray-700 mb-1">URL</label>
                                        <input type="text" id="url" name="url" required 
                                               value="<?php echo $edit_link ? htmlspecialchars($edit_link['url']) : ''; ?>"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="position" class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                                        <input type="number" id="position" name="position" 
                                               value="<?php echo $edit_link ? htmlspecialchars($edit_link['position']) : '0'; ?>"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="is_active" 
                                                   <?php echo (!$edit_link || $edit_link['is_active']) ? 'checked' : ''; ?>
                                                   class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-gray-700">Active</span>
                                        </label>
                                    </div>
                                    
                                    <div class="mt-6 flex items-center space-x-2">
                                        <button type="submit" name="<?php echo $edit_link ? 'update_link' : 'add_link'; ?>" 
                                                class="px-5 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <i class='bx bx-save mr-2'></i> <?php echo $edit_link ? 'Update Link' : 'Add Link'; ?>
                                        </button>
                                        
                                        <?php if($edit_link): ?>
                                        <a href="?tab=links" class="px-5 py-2 bg-gray-300 text-gray-700 font-medium rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                            Cancel
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-lg shadow overflow-hidden">
                            <div class="p-6">
                                <h2 class="text-lg font-semibold text-gray-800 mb-4">Footer Links</h2>
                                
                                <?php
                                // Group links by section
                                $links_by_section = [];
                                foreach($footer_links as $link) {
                                    if(!isset($links_by_section[$link['section']])) {
                                        $links_by_section[$link['section']] = [];
                                    }
                                    $links_by_section[$link['section']][] = $link;
                                }
                                
                                if(empty($footer_links)):
                                ?>
                                <div class="bg-blue-50 text-blue-700 p-4 rounded">
                                    <div class="flex">
                                        <i class='bx bx-info-circle text-xl mr-2'></i>
                                        <p>No footer links found. Add your first link using the form.</p>
                                    </div>
                                </div>
                                <?php else: ?>
                                
                                <div class="space-y-4">
                                    <?php foreach($links_by_section as $section => $links): ?>
                                    <div class="border rounded-lg overflow-hidden">
                                        <div class="bg-gray-50 px-4 py-3 border-b">
                                            <h3 class="font-medium text-gray-700">
                                                <?php 
                                                $section_title = ucfirst($section);
                                                if($section == 'layanan') {
                                                    $section_title = 'Layanan Kami';
                                                } elseif($section == 'informasi') {
                                                    $section_title = 'Informasi';
                                                } elseif($section == 'support') {
                                                    $section_title = 'Support';
                                                }
                                                echo $section_title;
                                                ?>
                                                <span class="ml-2 bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                                                    <?php echo count($links); ?>
                                                </span>
                                            </h3>
                                        </div>
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
                                                    <?php foreach($links as $link): ?>
                                                    <tr>
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            <?php echo htmlspecialchars($link['title']); ?>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                            <?php echo htmlspecialchars($link['url']); ?>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                            <?php echo $link['position']; ?>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            <?php if($link['is_active']): ?>
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
                                                            <a href="?tab=links&edit_link=<?php echo $link['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                                                <i class='bx bx-edit'></i> Edit
                                                            </a>
                                                            <a href="?tab=links&delete_link=<?php echo $link['id']; ?>" 
                                                               onclick="return confirm('Are you sure you want to delete this link?')" 
                                                               class="text-red-600 hover:text-red-900">
                                                                <i class='bx bx-trash'></i> Delete
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if($activeTab === 'bulletin'): ?>
                <!-- Bulletin Settings Tab -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-lg shadow overflow-hidden">
                            <div class="p-6">
                                <h2 class="text-lg font-semibold text-gray-800 mb-4">
                                    <?php echo $edit_field ? 'Edit Form Field' : 'Add Form Field'; ?>
                                </h2>
                                
                                <form method="POST" action="">
                                    <input type="hidden" name="active_tab" value="bulletin">
                                    <?php if($edit_field): ?>
                                    <input type="hidden" name="field_id" value="<?php echo $edit_field['id']; ?>">
                                    <?php endif; ?>
                                    
                                    <div class="mb-4">
                                        <label for="field_name" class="block text-sm font-medium text-gray-700 mb-1">Field Name</label>
                                        <input type="text" id="field_name" name="field_name" required 
                                               value="<?php echo $edit_field ? htmlspecialchars($edit_field['field_name']) : ''; ?>"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">Used as the input's name attribute (e.g., email, name)</p>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="field_label" class="block text-sm font-medium text-gray-700 mb-1">Field Label</label>
                                        <input type="text" id="field_label" name="field_label" required 
                                               value="<?php echo $edit_field ? htmlspecialchars($edit_field['field_label']) : ''; ?>"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">Displayed to the user as the field label</p>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="field_type" class="block text-sm font-medium text-gray-700 mb-1">Field Type</label>
                                        <select id="field_type" name="field_type" required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="text" <?php echo ($edit_field && $edit_field['field_type'] == 'text') ? 'selected' : ''; ?>>Text</option>
                                            <option value="email" <?php echo ($edit_field && $edit_field['field_type'] == 'email') ? 'selected' : ''; ?>>Email</option>
                                            <option value="textarea" <?php echo ($edit_field && $edit_field['field_type'] == 'textarea') ? 'selected' : ''; ?>>Textarea</option>
                                            <option value="checkbox" <?php echo ($edit_field && $edit_field['field_type'] == 'checkbox') ? 'selected' : ''; ?>>Checkbox</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="placeholder" class="block text-sm font-medium text-gray-700 mb-1">Placeholder</label>
                                        <input type="text" id="placeholder" name="placeholder" 
                                               value="<?php echo $edit_field ? htmlspecialchars($edit_field['placeholder']) : ''; ?>"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">Displayed inside the input when empty</p>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="field_position" class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                                        <input type="number" id="field_position" name="field_position" min="1" 
                                               value="<?php echo $edit_field ? htmlspecialchars($edit_field['position']) : '1'; ?>"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">Order of the field in the form</p>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="is_required" 
                                                   <?php echo ($edit_field && $edit_field['is_required']) ? 'checked' : ''; ?>
                                                   class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-gray-700">Required</span>
                                        </label>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="field_is_active" 
                                                   <?php echo (!$edit_field || $edit_field['is_active']) ? 'checked' : ''; ?>
                                                   class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-gray-700">Active</span>
                                        </label>
                                    </div>
                                    
                                    <div class="mt-6 flex items-center space-x-2">
                                        <button type="submit" name="<?php echo $edit_field ? 'update_field' : 'add_field'; ?>" 
                                                class="px-5 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <i class='bx bx-save mr-2'></i> <?php echo $edit_field ? 'Update Field' : 'Add Field'; ?>
                                        </button>
                                        
                                        <?php if($edit_field): ?>
                                        <a href="?tab=bulletin" class="px-5 py-2 bg-gray-300 text-gray-700 font-medium rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                            Cancel
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Bulletin settings help -->
                        <div class="mt-4 bg-white rounded-lg shadow overflow-hidden">
                            <div class="p-6">
                                <h3 class="text-base font-medium text-gray-800 mb-2">Newsletter Form Setup</h3>
                                <div class="text-sm text-gray-600 space-y-2">
                                    <p>1. Go to the <strong>Footer Settings</strong> tab to set up the bulletin title, description, and form action.</p>
                                    <p>2. Use this page to add form fields that users will fill out when subscribing.</p>
                                    <p>3. At minimum, you should have an <strong>email</strong> field for collecting subscriber information.</p>
                                    <p>4. The form action URL in Footer Settings should point to your newsletter subscription handler script.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-lg shadow overflow-hidden">
                            <div class="p-6">
                                <h2 class="text-lg font-semibold text-gray-800 mb-4">Newsletter Form Fields</h2>
                                
                                <?php if(empty($bulletin_fields)): ?>
                                <div class="bg-blue-50 text-blue-700 p-4 rounded">
                                    <div class="flex">
                                        <i class='bx bx-info-circle text-xl mr-2'></i>
                                        <p>No form fields found. Add your first field using the form.</p>
                                    </div>
                                </div>
                                <?php else: ?>
                                
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Field Name</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Label</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Required</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <?php foreach($bulletin_fields as $field): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <?php echo htmlspecialchars($field['field_name']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <?php echo htmlspecialchars($field['field_label']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo htmlspecialchars($field['field_type']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo $field['is_required'] ? 'Yes' : 'No'; ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo $field['position']; ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <?php if($field['is_active']): ?>
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
                                                    <a href="?tab=bulletin&edit_field=<?php echo $field['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                                        <i class='bx bx-edit'></i> Edit
                                                    </a>
                                                    <a href="?tab=bulletin&delete_field=<?php echo $field['id']; ?>" 
                                                       onclick="return confirm('Are you sure you want to delete this field?')" 
                                                       class="text-red-600 hover:text-red-900">
                                                        <i class='bx bx-trash'></i> Delete
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Form Preview -->
                        <div class="mt-6 bg-white rounded-lg shadow overflow-hidden">
                            <div class="p-6">
                                <h2 class="text-lg font-semibold text-gray-800 mb-4">Newsletter Form Preview</h2>
                                
                                <div class="p-4 rounded-lg" 
                                     style="background-image: linear-gradient(<?php echo htmlspecialchars($footer_settings['footer_gradient_direction']); ?>, <?php echo htmlspecialchars($footer_settings['footer_gradient_start_color']); ?>, <?php echo htmlspecialchars($footer_settings['footer_gradient_end_color']); ?>); color: <?php echo htmlspecialchars($footer_settings['footer_text_color']); ?>;">
                                    <h3 class="font-medium mb-2"><?php echo htmlspecialchars($footer_settings['footer_bulletin_title']); ?></h3>
                                    <p class="text-sm mb-4"><?php echo htmlspecialchars($footer_settings['footer_bulletin_description']); ?></p>
                                    
                                    <form class="space-y-2">
                                        <?php 
                                        usort($bulletin_fields, function($a, $b) {
                                            return $a['position'] <=> $b['position'];
                                        });
                                        
                                        foreach($bulletin_fields as $field): 
                                            if(!$field['is_active']) continue;
                                        ?>
                                            <?php if($field['field_type'] == 'checkbox'): ?>
                                                <div class="flex items-center">
                                                    <input type="checkbox" id="preview_<?php echo $field['field_name']; ?>" 
                                                           class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" 
                                                           <?php echo $field['is_required'] ? 'required' : ''; ?>>
                                                    <label for="preview_<?php echo $field['field_name']; ?>" 
                                                           class="ml-2 text-sm"><?php echo htmlspecialchars($field['field_label']); ?></label>
                                                </div>
                                            <?php elseif($field['field_type'] == 'textarea'): ?>
                                                <div>
                                                    <label for="preview_<?php echo $field['field_name']; ?>" 
                                                          class="block text-sm font-medium mb-1"><?php echo htmlspecialchars($field['field_label']); ?></label>
                                                    <textarea id="preview_<?php echo $field['field_name']; ?>" 
                                                              placeholder="<?php echo htmlspecialchars($field['placeholder']); ?>"
                                                              class="w-full px-3 py-2 text-black border border-gray-300 rounded-md text-sm" 
                                                              rows="2"
                                                              <?php echo $field['is_required'] ? 'required' : ''; ?>></textarea>
                                                </div>
                                            <?php else: ?>
                                                <div>
                                                    <label for="preview_<?php echo $field['field_name']; ?>" 
                                                          class="block text-sm font-medium mb-1"><?php echo htmlspecialchars($field['field_label']); ?></label>
                                                    <input type="<?php echo $field['field_type']; ?>" 
                                                           id="preview_<?php echo $field['field_name']; ?>" 
                                                           placeholder="<?php echo htmlspecialchars($field['placeholder']); ?>"
                                                           class="w-full px-3 py-2 text-black border border-gray-300 rounded-md text-sm" 
                                                           <?php echo $field['is_required'] ? 'required' : ''; ?>>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                        
                                        <div class="pt-2">
                                            <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md text-sm">
                                                Subscribe
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                
                                <p class="text-sm text-gray-500 mt-2">This is a preview of how the newsletter form will appear in the footer.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // Update gradient preview on input
        function updateGradientPreview() {
            const direction = document.getElementById('footer_gradient_direction').value;
            const startColor = document.getElementById('footer_gradient_start_color').value;
            const endColor = document.getElementById('footer_gradient_end_color').value;
            
            const preview = document.querySelector('[style*="background-image: linear-gradient"]');
            if (preview) {
                preview.style.backgroundImage = `linear-gradient(${direction}, ${startColor}, ${endColor})`;
            }
        }
        
        // Add event listeners to update preview on changes
        document.getElementById('footer_gradient_direction')?.addEventListener('change', updateGradientPreview);
        document.getElementById('footer_gradient_start_color')?.addEventListener('input', updateGradientPreview);
        document.getElementById('footer_gradient_end_color')?.addEventListener('input', updateGradientPreview);
        
        // Update text color preview
        document.getElementById('footer_text_color')?.addEventListener('input', function() {
            const preview = document.querySelector('[style*="background-image: linear-gradient"]');
            if (preview) {
                preview.style.color = this.value;
            }
        });
    </script>
</body>
</html>