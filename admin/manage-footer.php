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
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
$validTabs = ['general', 'services', 'bulletin'];
if (!in_array($activeTab, $validTabs)) {
    $activeTab = 'general';
}

<<<<<<< HEAD
// Configuration
$uploadDirectory = '../assets/uploads/footer/';
=======
// Configuration for file uploads
$uploadDirectory = '../assets/images/uploads/footer/';
>>>>>>> 8e4becbaf5403e7d97d03949a8cd50225d60a7aa
if (!file_exists($uploadDirectory)) {
    // Create directory if it doesn't exist
    mkdir($uploadDirectory, 0755, true);
}

// Helper function for handling file uploads
function handleImageUpload($fileInput, $oldPath = null) {
    global $uploadDirectory, $message, $messageType;
    
    // Check if a file was uploaded
    if (isset($_FILES[$fileInput]) && $_FILES[$fileInput]['error'] === UPLOAD_ERR_OK) {
        $tempFile = $_FILES[$fileInput]['tmp_name'];
        $fileInfo = pathinfo($_FILES[$fileInput]['name']);
        $extension = strtolower($fileInfo['extension']);
        
        // Validate file type
<<<<<<< HEAD
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
        if (!in_array($extension, $allowedExtensions)) {
            return [
                'success' => false,
                'message' => "Invalid file type. Only JPG, PNG, GIF, and SVG files are allowed."
=======
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
        if (!in_array($extension, $allowedExtensions)) {
            return [
                'success' => false,
                'message' => "Invalid file type. Only JPG, JPEG, PNG, GIF, SVG, and WEBP files are allowed."
>>>>>>> 8e4becbaf5403e7d97d03949a8cd50225d60a7aa
            ];
        }
        
        // Generate a unique filename to prevent overwriting
        $newFilename = uniqid('footer_') . '.' . $extension;
        $targetPath = $uploadDirectory . $newFilename;
        
        // Move the uploaded file
        if (move_uploaded_file($tempFile, $targetPath)) {
            // Delete old file if it exists and is in the uploads directory
<<<<<<< HEAD
            if ($oldPath && strpos($oldPath, 'assets/uploads/footer/') !== false && file_exists('../' . $oldPath)) {
=======
            if ($oldPath && strpos($oldPath, 'assets/images/uploads/footer/') !== false && file_exists('../' . $oldPath)) {
>>>>>>> 8e4becbaf5403e7d97d03949a8cd50225d60a7aa
                unlink('../' . $oldPath);
            }
            
            // Return the relative path for database storage
<<<<<<< HEAD
            $relativePath = 'assets/uploads/footer/' . $newFilename;
=======
            $relativePath = 'assets/images/uploads/footer/' . $newFilename;
>>>>>>> 8e4becbaf5403e7d97d03949a8cd50225d60a7aa
            return [
                'success' => true,
                'path' => $relativePath
            ];
        } else {
            return [
                'success' => false,
<<<<<<< HEAD
                'message' => "Failed to move uploaded file."
=======
                'message' => "Failed to move uploaded file. Check folder permissions."
>>>>>>> 8e4becbaf5403e7d97d03949a8cd50225d60a7aa
            ];
        }
    }
    
    // If no new file was uploaded, return the old path
    return [
        'success' => true,
        'path' => $oldPath
    ];
}

// Helper function to check if a setting exists
function settingExists($conn, $key) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM footer_settings WHERE setting_key = :key");
    $stmt->bindParam(':key', $key);
    $stmt->execute();
    return $stmt->fetchColumn() > 0;
}

// Handle form submissions for different tabs
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process form based on which tab is active
    if (isset($_POST['update_general']) && $activeTab === 'general') {
        // Update general footer settings
        try {
            $conn->beginTransaction();
            
            // Handle logo upload if present
            $logoPath = $settings['logo_footer'] ?? 'assets/images/logos/logo-footer.png';
            $uploadResult = handleImageUpload('logo_file', $logoPath);
            
            if (!$uploadResult['success']) {
                throw new Exception($uploadResult['message']);
            }
            
<<<<<<< HEAD
            // Use uploaded path or keep the input path
            $logoPath = $uploadResult['path'];
            if (!isset($_FILES['logo_file']) || $_FILES['logo_file']['error'] !== UPLOAD_ERR_OK) {
                // If no file upload, check if a path was provided in the text field
                if (!empty($_POST['logo_path'])) {
                    $logoPath = $_POST['logo_path'];
                }
            }
=======
            // Use uploaded path or keep the current path
            $logoPath = $uploadResult['path'];
>>>>>>> 8e4becbaf5403e7d97d03949a8cd50225d60a7aa
            
            // Combine address into a single field
            $companyAddress = trim($_POST['company_address'] ?? '');
            
            // Process each setting
            $settings = [
                'company_name' => $_POST['company_name'] ?? '',
                'company_address' => $companyAddress,
                'company_phone' => $_POST['phone'] ?? '',
                'footer_copyright' => $_POST['copyright'] ?? '',
                'logo_footer' => $logoPath
            ];
            
            foreach ($settings as $key => $value) {
                // Check if setting exists first
                if (settingExists($conn, $key)) {
                    // Update
                    $stmt = $conn->prepare("UPDATE footer_settings SET setting_value = :value WHERE setting_key = :key");
                } else {
                    // Insert
                    $stmt = $conn->prepare("INSERT INTO footer_settings (setting_key, setting_value) VALUES (:key, :value)");
                }
                
                $stmt->bindParam(':key', $key);
                $stmt->bindParam(':value', $value);
                $stmt->execute();
            }
            
            $conn->commit();
            $message = "General footer settings updated successfully!";
            $messageType = "success";
        } catch(Exception $e) {
            $conn->rollBack();
            $message = "Error updating settings: " . $e->getMessage();
            $messageType = "error";
        }
    }
    elseif (isset($_POST['update_service_links']) && $activeTab === 'services') {
        // Handle service links update
        try {
            $conn->beginTransaction();
            
            // Delete all existing service links and re-insert them
            $stmt = $conn->prepare("DELETE FROM footer_links WHERE section = 'services'");
            $stmt->execute();
            
            // Process each service link
            if (isset($_POST['service_titles']) && is_array($_POST['service_titles'])) {
                $titles = $_POST['service_titles'];
                $urls = $_POST['service_urls'];
<<<<<<< HEAD
=======
                $icons = $_POST['service_icons'];
>>>>>>> 8e4becbaf5403e7d97d03949a8cd50225d60a7aa
                $orders = $_POST['service_orders'];
                $active = $_POST['service_active'] ?? [];
                
                for ($i = 0; $i < count($titles); $i++) {
                    $title = trim($titles[$i]);
                    $url = trim($urls[$i]);
<<<<<<< HEAD
=======
                    $icon = trim($icons[$i] ?: 'bx bx-chevron-right'); // Default icon if empty
>>>>>>> 8e4becbaf5403e7d97d03949a8cd50225d60a7aa
                    $order = (int)$orders[$i];
                    
                    if (!empty($title) && !empty($url)) {
                        $isActive = in_array($i, $active) ? 1 : 0;
                        
                        $stmt = $conn->prepare("INSERT INTO footer_links 
                                              (section, title, url, icon, display_order, is_active) 
<<<<<<< HEAD
                                              VALUES ('services', :title, :url, 'bx bx-chevron-right', :order, :active)");
                        $stmt->bindParam(':title', $title);
                        $stmt->bindParam(':url', $url);
=======
                                              VALUES ('services', :title, :url, :icon, :order, :active)");
                        $stmt->bindParam(':title', $title);
                        $stmt->bindParam(':url', $url);
                        $stmt->bindParam(':icon', $icon);
>>>>>>> 8e4becbaf5403e7d97d03949a8cd50225d60a7aa
                        $stmt->bindParam(':order', $order);
                        $stmt->bindParam(':active', $isActive, PDO::PARAM_BOOL);
                        $stmt->execute();
                    }
                }
            }
            
            // Add new service if provided
            if (!empty($_POST['new_service_title']) && !empty($_POST['new_service_url'])) {
                $newTitle = trim($_POST['new_service_title']);
                $newUrl = trim($_POST['new_service_url']);
<<<<<<< HEAD
=======
                $newIcon = trim($_POST['new_service_icon'] ?: 'bx bx-chevron-right'); // Default icon if empty
>>>>>>> 8e4becbaf5403e7d97d03949a8cd50225d60a7aa
                $newOrder = (int)$_POST['new_service_order'];
                
                $stmt = $conn->prepare("INSERT INTO footer_links 
                                      (section, title, url, icon, display_order, is_active) 
<<<<<<< HEAD
                                      VALUES ('services', :title, :url, 'bx bx-chevron-right', :order, TRUE)");
                $stmt->bindParam(':title', $newTitle);
                $stmt->bindParam(':url', $newUrl);
=======
                                      VALUES ('services', :title, :url, :icon, :order, TRUE)");
                $stmt->bindParam(':title', $newTitle);
                $stmt->bindParam(':url', $newUrl);
                $stmt->bindParam(':icon', $newIcon);
>>>>>>> 8e4becbaf5403e7d97d03949a8cd50225d60a7aa
                $stmt->bindParam(':order', $newOrder);
                $stmt->execute();
            }
            
            $conn->commit();
            $message = "Service links updated successfully!";
            $messageType = "success";
        } catch(PDOException $e) {
            $conn->rollBack();
            $message = "Error updating service links: " . $e->getMessage();
            $messageType = "error";
        }
    }
    elseif (isset($_POST['update_bulletin']) && $activeTab === 'bulletin') {
        // Update bulletin settings
        try {
            $conn->beginTransaction();
            
            $settings = [
                'bulletin_title' => $_POST['bulletin_title'] ?? 'Bulletin',
                'bulletin_text' => $_POST['bulletin_text'] ?? ''
            ];
            
            foreach ($settings as $key => $value) {
                // Check if setting exists first
                if (settingExists($conn, $key)) {
                    // Update
                    $stmt = $conn->prepare("UPDATE footer_settings SET setting_value = :value WHERE setting_key = :key");
                } else {
                    // Insert
                    $stmt = $conn->prepare("INSERT INTO footer_settings (setting_key, setting_value) VALUES (:key, :value)");
                }
                
                $stmt->bindParam(':key', $key);
                $stmt->bindParam(':value', $value);
                $stmt->execute();
            }
            
            $conn->commit();
            $message = "Bulletin settings updated successfully!";
            $messageType = "success";
        } catch(PDOException $e) {
            $conn->rollBack();
            $message = "Error updating bulletin settings: " . $e->getMessage();
            $messageType = "error";
        }
    }
}

// Fetch current settings for each tab
$settings = [];
try {
    $stmt = $conn->query("SELECT setting_key, setting_value FROM footer_settings");
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch(PDOException $e) {
    $message = "Error fetching settings: " . $e->getMessage();
    $messageType = "error";
}

// Fetch service links
$serviceLinks = [];
try {
<<<<<<< HEAD
    $stmt = $conn->prepare("SELECT id, title, url, display_order, is_active FROM footer_links 
=======
    $stmt = $conn->prepare("SELECT id, title, url, icon, display_order, is_active FROM footer_links 
>>>>>>> 8e4becbaf5403e7d97d03949a8cd50225d60a7aa
                           WHERE section = 'services' ORDER BY display_order ASC");
    $stmt->execute();
    $serviceLinks = $stmt->fetchAll();
} catch(PDOException $e) {
    $message = "Error fetching service links: " . $e->getMessage();
    $messageType = "error";
}

// Prepare company address
$companyAddress = $settings['company_address'] ?? '';
if (empty($companyAddress)) {
    // If using old format with separate address lines, combine them
    $addressLines = [];
    if (!empty($settings['company_address_line1'])) $addressLines[] = $settings['company_address_line1'];
    if (!empty($settings['company_address_line2'])) $addressLines[] = $settings['company_address_line2'];
    if (!empty($settings['company_address_line3'])) $addressLines[] = $settings['company_address_line3'];
    $companyAddress = implode("\n", $addressLines);
}
?>

<!doctype html>
<html lang="id">
<?php
include('components/head.php')
?>
<body class="bg-gray-50">
    <div class="min-h-screen flex flex-col lg:flex-row">
        <?php include('components/sidebar.php'); ?>
        
        <div class="flex-1 lg:ml-64">
            <div class="bg-white p-4 shadow flex justify-between items-center">
                <h1 class="text-xl font-semibold text-gray-800">Manage Footer</h1>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <span class="text-gray-600">Welcome, <?php echo htmlspecialchars($currentUsername); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Footer Management Content -->
            <div class="p-6">
                <?php if(!empty($message)): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo $message; ?>
                </div>
                <?php endif; ?>
                
                <!-- Tabs Navigation -->
                <div class="mb-6 border-b border-gray-200">
                    <nav class="flex space-x-8">
                        <a href="?tab=general" class="py-4 px-1 border-b-2 font-medium text-sm leading-5 <?php echo $activeTab === 'general' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>">
                            General Settings
                        </a>
                        <a href="?tab=services" class="py-4 px-1 border-b-2 font-medium text-sm leading-5 <?php echo $activeTab === 'services' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>">
                            Service Links
                        </a>
                        <a href="?tab=bulletin" class="py-4 px-1 border-b-2 font-medium text-sm leading-5 <?php echo $activeTab === 'bulletin' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>">
                            Bulletin Section
                        </a>
                    </nav>
                </div>
                
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">
                            <?php 
                            if ($activeTab === 'general') echo 'General Footer Settings';
                            elseif ($activeTab === 'services') echo 'Manage Service Links';
                            elseif ($activeTab === 'bulletin') echo 'Bulletin Settings';
                            ?>
                        </h2>
                    </div>
                    
                    <div class="p-6">
                        <?php if ($activeTab === 'general'): ?>
                            <!-- General Settings Form with File Upload -->
                            <form method="POST" action="?tab=general" enctype="multipart/form-data">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="company_name" class="block text-sm font-medium text-gray-700 mb-1">Company Name</label>
                                        <input type="text" id="company_name" name="company_name" 
                                            value="<?php echo htmlspecialchars($settings['company_name'] ?? 'Akademi Merdeka'); ?>"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    
                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                        <input type="text" id="phone" name="phone" 
                                            value="<?php echo htmlspecialchars($settings['company_phone'] ?? '+62 877-3542-6107'); ?>"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    
                                    <div class="col-span-1 md:col-span-2">
                                        <label for="company_address" class="block text-sm font-medium text-gray-700 mb-1">Company Address</label>
                                        <textarea id="company_address" name="company_address" rows="3" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($companyAddress); ?></textarea>
                                        <p class="mt-1 text-xs text-gray-500">Each line will be displayed as a separate line in the footer</p>
                                    </div>
                                    
                                    <div class="col-span-1 md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Footer Logo</label>
                                        
                                        <div class="flex items-start space-x-4">
                                            <div class="w-1/3">
                                                <?php $logoPath = $settings['logo_footer'] ?? 'assets/images/logos/logo-footer.png'; ?>
                                                <div class="mb-2 bg-gray-100 p-4 rounded-lg text-center">
                                                    <img src="../<?php echo htmlspecialchars($logoPath); ?>" alt="Current logo" class="max-h-24 inline-block">
                                                </div>
                                                <p class="text-xs text-gray-500 text-center">Current Logo</p>
                                            </div>
                                            
                                            <div class="w-2/3">
                                                <div class="mb-3">
                                                    <label for="logo_file" class="block text-sm font-medium text-gray-700 mb-1">Upload New Logo</label>
                                                    <input type="file" id="logo_file" name="logo_file" 
<<<<<<< HEAD
                                                        class="w-full block text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                                    <p class="mt-1 text-xs text-gray-500">Recommended size: 270px × 60px. Accepted formats: JPG, PNG, GIF, SVG.</p>
                                                </div>
                                                
                                                <div>
                                                    <label for="logo_path" class="block text-sm font-medium text-gray-700 mb-1">Or Specify Logo Path</label>
                                                    <input type="text" id="logo_path" name="logo_path" 
                                                        value="<?php echo htmlspecialchars($logoPath); ?>"
                                                        placeholder="assets/images/logo.png"
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    <p class="mt-1 text-xs text-gray-500">Path relative to website root. This will be used if no file is uploaded.</p>
=======
                                                           class="w-full block text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                                    <p class="mt-1 text-xs text-gray-500">Recommended size: 270px × 60px. Accepted formats: JPG, JPEG, PNG, GIF, SVG, WEBP.</p>
>>>>>>> 8e4becbaf5403e7d97d03949a8cd50225d60a7aa
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-6">
                                    <label for="copyright" class="block text-sm font-medium text-gray-700 mb-1">Copyright Text</label>
                                    <textarea id="copyright" name="copyright" rows="2" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($settings['footer_copyright'] ?? 'Copyright © ' . date('Y') . ' <a href="https://akademimerdeka.com/">Akademi Merdeka</a> as establisment date 2022'); ?></textarea>
                                    <p class="mt-1 text-xs text-gray-500">You can use HTML tags like &lt;a&gt; for links</p>
                                </div>
                                
                                <div class="mt-6 flex justify-end">
                                    <button type="submit" name="update_general" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                            
                        <?php elseif ($activeTab === 'services'): ?>
                            <!-- Service Links Management Form -->
                            <form method="POST" action="?tab=services">
                                <div class="mb-6">
                                    <h3 class="text-base font-medium text-gray-900 mb-2">Current Service Links</h3>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Active</th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL</th>
<<<<<<< HEAD
=======
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Icon</th>
>>>>>>> 8e4becbaf5403e7d97d03949a8cd50225d60a7aa
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                <?php if(empty($serviceLinks)): ?>
                                                <tr>
<<<<<<< HEAD
                                                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No service links found</td>
=======
                                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No service links found</td>
>>>>>>> 8e4becbaf5403e7d97d03949a8cd50225d60a7aa
                                                </tr>
                                                <?php else: ?>
                                                    <?php foreach($serviceLinks as $index => $link): ?>
                                                    <tr>
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            <input type="checkbox" name="service_active[]" value="<?php echo $index; ?>" 
                                                                <?php echo $link['is_active'] ? 'checked' : ''; ?> 
                                                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            <input type="text" name="service_titles[]" value="<?php echo htmlspecialchars($link['title']); ?>" 
                                                                class="w-full px-3 py-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            <input type="text" name="service_urls[]" value="<?php echo htmlspecialchars($link['url']); ?>" 
                                                                class="w-full px-3 py-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap">
<<<<<<< HEAD
=======
                                                            <input type="text" name="service_icons[]" value="<?php echo htmlspecialchars($link['icon']); ?>" 
                                                                placeholder="bx bx-chevron-right"
                                                                class="w-full px-3 py-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap">
>>>>>>> 8e4becbaf5403e7d97d03949a8cd50225d60a7aa
                                                            <input type="number" name="service_orders[]" value="<?php echo $link['display_order']; ?>" min="1" 
                                                                class="w-20 px-3 py-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                <div class="mt-8 p-4 border border-gray-200 rounded-lg bg-gray-50">
                                    <h3 class="text-base font-medium text-gray-900 mb-4">Add New Service Link</h3>
<<<<<<< HEAD
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
=======
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
>>>>>>> 8e4becbaf5403e7d97d03949a8cd50225d60a7aa
                                        <div>
                                            <label for="new_service_title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                            <input type="text" id="new_service_title" name="new_service_title" 
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label for="new_service_url" class="block text-sm font-medium text-gray-700 mb-1">URL</label>
                                            <input type="text" id="new_service_url" name="new_service_url" 
                                                placeholder="services/example-service" 
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        <div>
<<<<<<< HEAD
=======
                                            <label for="new_service_icon" class="block text-sm font-medium text-gray-700 mb-1">Icon Class</label>
                                            <input type="text" id="new_service_icon" name="new_service_icon" 
                                                placeholder="bx bx-chevron-right" 
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <p class="mt-1 text-xs text-gray-500">Boxicons class name (default: bx bx-chevron-right)</p>
                                        </div>
                                        <div>
>>>>>>> 8e4becbaf5403e7d97d03949a8cd50225d60a7aa
                                            <label for="new_service_order" class="block text-sm font-medium text-gray-700 mb-1">Order</label>
                                            <input type="number" id="new_service_order" name="new_service_order" 
                                                value="<?php echo count($serviceLinks) + 1; ?>" min="1" 
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-6 flex justify-end">
                                    <button type="submit" name="update_service_links" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                            
                        <?php elseif ($activeTab === 'bulletin'): ?>
                            <!-- Bulletin Settings Form -->
                            <form method="POST" action="?tab=bulletin">
                                <div class="space-y-6">
                                    <div>
                                        <label for="bulletin_title" class="block text-sm font-medium text-gray-700 mb-1">Bulletin Title</label>
                                        <input type="text" id="bulletin_title" name="bulletin_title" 
                                            value="<?php echo htmlspecialchars($settings['bulletin_title'] ?? 'Bulletin'); ?>"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    
                                    <div>
                                        <label for="bulletin_text" class="block text-sm font-medium text-gray-700 mb-1">Bulletin Text</label>
                                        <textarea id="bulletin_text" name="bulletin_text" rows="4" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($settings['bulletin_text'] ?? 'Informasi lain dapat diajukan kepada tim kami untuk ditindaklanjuti.'); ?></textarea>
                                    </div>
                                    
                                    <div class="bg-blue-50 p-4 rounded-lg">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <i class="bx bx-info-circle text-blue-600 text-xl"></i>
                                            </div>
                                            <div class="ml-3">
                                                <h3 class="text-sm font-medium text-blue-800">Email Form Information</h3>
                                                <div class="mt-2 text-sm text-blue-700">
                                                    <p>The newsletter form functionality needs to be implemented separately. This management interface only controls the title and descriptive text.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-6 flex justify-end">
                                    <button type="submit" name="update_bulletin" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Preview Section -->
                <div class="mt-8 bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-gray-800">Footer Preview</h2>
                        <a href="../" target="_blank" class="text-blue-600 hover:text-blue-800 flex items-center">
                            <i class="bx bx-link-external mr-1"></i> View on Site
                        </a>
                    </div>
                    <div class="p-2 bg-gray-100">
                        <div class="bg-gray-800 text-white p-4 rounded-lg" style="max-height: 400px; overflow-y: auto;">
                            <!-- Simplified Preview -->
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <div class="mb-3">
                                        <img src="../<?php echo htmlspecialchars($settings['logo_footer'] ?? 'assets/images/logos/logo-footer.png'); ?>" 
                                             alt="Logo" class="max-h-12 bg-gray-700 p-1 rounded">
                                    </div>
                                    <p class="text-sm text-gray-300">
                                        <?php echo nl2br(htmlspecialchars($companyAddress)); ?>
                                    </p>
                                    <div class="mt-2">
                                        <h4 class="font-medium">Hubungi Kami</h4>
                                        <span class="text-blue-300"><?php echo htmlspecialchars($settings['company_phone'] ?? '+62 877-3542-6107'); ?></span>
                                    </div>
                                </div>
                                
                                <div>
                                    <h3 class="text-lg font-semibold mb-2">Layanan Kami</h3>
                                    <ul class="space-y-1 text-sm text-gray-300">
                                        <?php if(empty($serviceLinks)): ?>
                                            <li>No services defined</li>
                                        <?php else: ?>
                                            <?php foreach($serviceLinks as $link): ?>
                                                <?php if($link['is_active']): ?>
                                                <li>
<<<<<<< HEAD
                                                    <i class='bx bx-chevron-right'></i> 
=======
                                                    <i class='<?php echo htmlspecialchars($link['icon']); ?>'></i> 
>>>>>>> 8e4becbaf5403e7d97d03949a8cd50225d60a7aa
                                                    <?php echo htmlspecialchars($link['title']); ?>
                                                </li>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                                
                                <div>
                                    <h3 class="text-lg font-semibold mb-2">Blog</h3>
                                    <p class="text-sm text-gray-400">Blog section is static and managed separately</p>
                                </div>
                                
                                <div>
                                    <h3 class="text-lg font-semibold mb-2"><?php echo htmlspecialchars($settings['bulletin_title'] ?? 'Bulletin'); ?></h3>
                                    <p class="text-sm text-gray-300">
                                        <?php echo htmlspecialchars($settings['bulletin_text'] ?? 'Informasi lain dapat diajukan kepada tim kami untuk ditindaklanjuti.'); ?>
                                    </p>
                                    <div class="mt-2 bg-gray-700 p-2 rounded text-xs">
                                        [Newsletter Form Placeholder]
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4 pt-4 border-t border-gray-700 text-center text-sm text-gray-400">
                                <?php echo $settings['footer_copyright'] ?? 'Copyright © ' . date('Y') . ' <a href="https://akademimerdeka.com/">Akademi Merdeka</a> as establisment date 2022'; ?>
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