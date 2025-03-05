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
        'footer_bg_color' => $_POST['footer_bg_color'],
        'footer_text_color' => $_POST['footer_text_color'],
        'footer_whatsapp_link' => $_POST['footer_whatsapp_link']
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

// Get footer settings
$footer_settings = [
    'footer_logo' => 'assets/images/logos/logo-footer.png',
    'footer_company_name' => 'Akademi Merdeka',
    'footer_company_address' => 'Perumahan Kheandra Kalijaga<br>Harjamukti, Cirebon, Jawa Barat',
    'footer_company_phone' => '+62 877-3542-6107',
    'footer_company_email' => 'info@akademimerdeka.com',
    'footer_copyright_text' => 'Copyright © 2023 <a href="https://akademimerdeka.com/">Akademi Merdeka</a> as establisment date 2022',
    'footer_bg_color' => '#343a40',
    'footer_text_color' => '#ffffff',
    'footer_whatsapp_link' => 'https://wa.me/6287735426107'
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
                                    
                                    <!-- Color Settings -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <label for="footer_bg_color" class="block text-sm font-medium text-gray-700 mb-1">Background Color</label>
                                            <div class="flex">
                                                <input type="text" id="footer_bg_color" name="footer_bg_color" 
                                                       value="<?php echo htmlspecialchars($footer_settings['footer_bg_color']); ?>"
                                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <div class="w-10 h-10 rounded-r-md border-t border-r border-b border-gray-300" 
                                                     style="background-color: <?php echo htmlspecialchars($footer_settings['footer_bg_color']); ?>"></div>
                                            </div>
                                        </div>
                                        <div>
                                            <label for="footer_text_color" class="block text-sm font-medium text-gray-700 mb-1">Text Color</label>
                                            <div class="flex">
                                                <input type="text" id="footer_text_color" name="footer_text_color" 
                                                       value="<?php echo htmlspecialchars($footer_settings['footer_text_color']); ?>"
                                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <div class="w-10 h-10 rounded-r-md border-t border-r border-b border-gray-300" 
                                                     style="background-color: <?php echo htmlspecialchars($footer_settings['footer_text_color']); ?>"></div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="footer_whatsapp_link" class="block text-sm font-medium text-gray-700 mb-1">WhatsApp Link</label>
                                        <input type="text" id="footer_whatsapp_link" name="footer_whatsapp_link" 
                                               value="<?php echo htmlspecialchars($footer_settings['footer_whatsapp_link']); ?>"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                                
                                <div class="p-4 rounded-lg" style="background-color: <?php echo htmlspecialchars($footer_settings['footer_bg_color']); ?>; color: <?php echo htmlspecialchars($footer_settings['footer_text_color']); ?>;">
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
            </div>
        </div>
    </div>
    
    <script>
        // Update color preview on input
        document.getElementById('footer_bg_color')?.addEventListener('input', function() {
            this.nextElementSibling.style.backgroundColor = this.value;
        });
        
        document.getElementById('footer_text_color')?.addEventListener('input', function() {
            this.nextElementSibling.style.backgroundColor = this.value;
        });
    </script>
</body>
</html>