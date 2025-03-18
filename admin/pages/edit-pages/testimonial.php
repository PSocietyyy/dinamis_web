<?php
// Try creating the upload directory structure at initialization
$rootPath = dirname(dirname(dirname(__DIR__)));
if (!file_exists($rootPath . '/assets')) {
    @mkdir($rootPath . '/assets', 0777);
}
if (!file_exists($rootPath . '/assets/images')) {
    @mkdir($rootPath . '/assets/images', 0777);
}
if (!file_exists($rootPath . '/assets/images/uploads')) {
    @mkdir($rootPath . '/assets/images/uploads', 0777);
}
if (!file_exists($rootPath . '/assets/images/uploads/testimonial')) {
    @mkdir($rootPath . '/assets/images/uploads/testimonial', 0777);
}

// Start the session
session_start();

// Check if not logged in
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../../../login.php");
    exit;
}

// Include database connection
require_once('../../../config.php');

// Create database tables if they don't exist
try {
    // Create testimonial_page_settings table if it doesn't exist
    $createSettingsTable = "CREATE TABLE IF NOT EXISTS testimonial_page_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        page_title VARCHAR(255) NOT NULL DEFAULT 'Testimoni',
        meta_description TEXT,
        breadcrumb_parent VARCHAR(100) DEFAULT 'Tentang',
        breadcrumb_current VARCHAR(100) DEFAULT 'Testimoni',
        section_title VARCHAR(255) DEFAULT 'Testimoni Customer',
        section_subtitle VARCHAR(255) DEFAULT 'Apa Kata Mereka?',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->exec($createSettingsTable);
    
    // Create testimonials table if it doesn't exist
    $createTestimonialsTable = "CREATE TABLE IF NOT EXISTS testimonials (
        id INT AUTO_INCREMENT PRIMARY KEY,
        client_name VARCHAR(100) NOT NULL,
        client_position VARCHAR(100),
        testimonial_text TEXT NOT NULL,
        client_image VARCHAR(255),
        is_active BOOLEAN DEFAULT TRUE,
        display_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->exec($createTestimonialsTable);
    
    // Insert default settings if not exist
    $checkSettings = $conn->query("SELECT COUNT(*) FROM testimonial_page_settings");
    if ($checkSettings->fetchColumn() == 0) {
        $defaultSettings = "INSERT INTO testimonial_page_settings (
            id, page_title, meta_description, 
            breadcrumb_parent, breadcrumb_current,
            section_title, section_subtitle
        ) VALUES (
            1, 'Testimoni', 'Testimoni dari pelanggan Akademi Merdeka',
            'Tentang', 'Testimoni',
            'Testimoni Customer', 'Apa Kata Mereka?'
        )";
        $conn->exec($defaultSettings);
    }
    
    // Import data from home_testimonials if testimonials table is empty
    $checkTestimonials = $conn->query("SELECT COUNT(*) FROM testimonials");
    if ($checkTestimonials->fetchColumn() == 0) {
        try {
            $conn->query("SELECT * FROM home_testimonials LIMIT 1");
            $importData = "INSERT INTO testimonials (
                client_name, client_position, testimonial_text, 
                client_image, is_active, display_order
            ) SELECT 
                client_name, client_position, testimonial_text, 
                client_image, is_active, display_order 
            FROM home_testimonials";
            $conn->exec($importData);
        } catch (PDOException $e) {
            // home_testimonials doesn't exist, insert default testimonials
            $defaultTestimonials = "INSERT INTO testimonials (client_name, client_position, testimonial_text, client_image, is_active, display_order) VALUES
            ('Bayu Saputra', 'Mahasiswa', '\"Adanya tim Akademi Merdeka membantu saya dalam penerbitan jurnal dengan metode yang efektif, membuat saya cepat memahami.\"', 'assets/images/clients-img/testi-4.jpg', 1, 1),
            ('Aryo Supratman', 'Dosen', '\"Akademi Merdeka tidak hanya sekedar membantu dalam kenaikan Jabatan Fungsional, namun sebagai penasehat dan pendengar yang baik. Tim sangat responsif dan tanggap jika ada persoalan.\"', 'assets/images/clients-img/testi-3.jpg', 1, 2),
            ('Syadid', 'Mahasiswa', '\"Tim Akademi Merdeka membantu pembuatan media ajar mulai dari penyusunan indikator dan memberikan inovasi yang sangat baik.\"', 'assets/images/clients-img/testi-6.jpg', 1, 3),
            ('Alya Afifah', 'Mahasiswa', '\"Desain yang diberikan oleh tim Akademi Merdeka sangat kekinian, sehingga buku yang diterbitkan semakin menarik perhatian pembaca.\"', 'assets/images/clients-img/testi-1.jpg', 1, 4),
            ('Arini Sulistiawati', 'Mahasiswa', '\"Pelayanan Pembuatan HKI sangat cepat. Tim hanya memerlukan 20 menit saja untuk mengirimkan sertifikat HKI kepada saya.\"', 'assets/images/clients-img/testi-2.jpg', 1, 5)";
            $conn->exec($defaultTestimonials);
        }
    }
} catch (PDOException $e) {
    // Log the error but continue with the page
    error_log("Error creating tables: " . $e->getMessage());
}

// Configuration
$uploadDirectory = $rootPath . '/assets/images/uploads/testimonial/';
// Ensure directory exists with proper permissions
if (!file_exists($uploadDirectory)) {
    // Create directory if it doesn't exist
    if (!@mkdir($uploadDirectory, 0777, true)) {
        $error = error_get_last();
        // Just log the error for now, we'll handle it in the upload function
    }
}

// Initialize variables
$message = '';
$messageType = '';
$currentUsername = $_SESSION['username'];
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'testimonials';

// Handle image uploads
function handleImageUpload($fileInput, $oldPath = null) {
    global $uploadDirectory;
    
    // Check if a file was uploaded
    if (isset($_FILES[$fileInput]) && $_FILES[$fileInput]['error'] === UPLOAD_ERR_OK) {
        $tempFile = $_FILES[$fileInput]['tmp_name'];
        $fileInfo = pathinfo($_FILES[$fileInput]['name']);
        $extension = strtolower($fileInfo['extension']);
        
        // Validate file type
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
        if (!in_array($extension, $allowedExtensions)) {
            return [
                'success' => false,
                'message' => "Invalid file type. Only JPG, PNG, GIF, SVG, and WEBP files are allowed."
            ];
        }
        
        // Make sure upload directory exists and is writable
        if (!file_exists($uploadDirectory)) {
            // Try one more time to create it with explicit full path
            $absolutePath = dirname(dirname(dirname(__DIR__))) . '/assets/images/uploads/testimonial/';
            if (!@mkdir($absolutePath, 0777, true)) {
                // Try creating parent directories one by one
                $rootDir = dirname(dirname(dirname(__DIR__)));
                if (!file_exists($rootDir . '/assets')) {
                    @mkdir($rootDir . '/assets', 0777);
                }
                if (!file_exists($rootDir . '/assets/images')) {
                    @mkdir($rootDir . '/assets/images', 0777);
                }
                if (!file_exists($rootDir . '/assets/images/uploads')) {
                    @mkdir($rootDir . '/assets/images/uploads', 0777);
                }
                if (!file_exists($rootDir . '/assets/images/uploads/testimonial')) {
                    @mkdir($rootDir . '/assets/images/uploads/testimonial', 0777);
                }
                
                if (!file_exists($absolutePath)) {
                    return [
                        'success' => false,
                        'message' => "Failed to create upload directory. Please create this directory manually: assets/images/uploads/testimonial"
                    ];
                }
            }
            $uploadDirectory = $absolutePath;
        }
        
        if (!is_writable($uploadDirectory)) {
            @chmod($uploadDirectory, 0777);
            if (!is_writable($uploadDirectory)) {
                return [
                    'success' => false,
                    'message' => "Upload directory exists but is not writable. Please check permissions for: " . $uploadDirectory
                ];
            }
        }
        
        // Generate a unique filename to prevent overwriting
        $newFilename = 'testimonial_' . uniqid() . '.' . $extension;
        $targetPath = $uploadDirectory . $newFilename;
        
        // Move the uploaded file
        if (@move_uploaded_file($tempFile, $targetPath)) {
            // Get the relative path for the database (from website root)
            $relativePath = 'assets/images/uploads/testimonial/' . $newFilename;
            return [
                'success' => true,
                'path' => $relativePath
            ];
        } else {
            $uploadError = error_get_last();
            return [
                'success' => false,
                'message' => "Failed to move uploaded file. " . 
                             "Error: " . ($uploadError ? $uploadError['message'] : 'Unknown error') . 
                             ". Check if PHP has write permissions to the directory."
            ];
        }
    }
    
    // If no new file was uploaded, return the old path
    return [
        'success' => true,
        'path' => $oldPath
    ];
}

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Update Page Settings
    if (isset($_POST['update_page_settings'])) {
        try {
            $conn->beginTransaction();
            
            // Prepare data
            $pageTitle = $_POST['page_title'] ?? 'Testimoni';
            $metaDescription = $_POST['meta_description'] ?? 'Testimoni dari pelanggan Akademi Merdeka';
            $breadcrumbParent = $_POST['breadcrumb_parent'] ?? 'Tentang';
            $breadcrumbCurrent = $_POST['breadcrumb_current'] ?? 'Testimoni';
            $sectionTitle = $_POST['section_title'] ?? 'Testimoni Customer';
            $sectionSubtitle = $_POST['section_subtitle'] ?? 'Apa Kata Mereka?';
            
            // Update or insert page settings
            $stmt = $conn->prepare("INSERT INTO testimonial_page_settings 
                                 (id, page_title, meta_description, 
                                 breadcrumb_parent, breadcrumb_current,
                                 section_title, section_subtitle) 
                                 VALUES (1, :page_title, :meta_description,
                                 :breadcrumb_parent, :breadcrumb_current,
                                 :section_title, :section_subtitle)
                                 ON DUPLICATE KEY UPDATE 
                                 page_title = VALUES(page_title), 
                                 meta_description = VALUES(meta_description),
                                 breadcrumb_parent = VALUES(breadcrumb_parent),
                                 breadcrumb_current = VALUES(breadcrumb_current),
                                 section_title = VALUES(section_title),
                                 section_subtitle = VALUES(section_subtitle)");
            
            $stmt->bindParam(':page_title', $pageTitle);
            $stmt->bindParam(':meta_description', $metaDescription);
            $stmt->bindParam(':breadcrumb_parent', $breadcrumbParent);
            $stmt->bindParam(':breadcrumb_current', $breadcrumbCurrent);
            $stmt->bindParam(':section_title', $sectionTitle);
            $stmt->bindParam(':section_subtitle', $sectionSubtitle);
            $stmt->execute();
            
            $conn->commit();
            $message = "Page settings updated successfully!";
            $messageType = "success";
        } catch (Exception $e) {
            $conn->rollBack();
            $message = "Error updating page settings: " . $e->getMessage();
            $messageType = "error";
        }
    }
    
    // Update Testimonials Items
    elseif (isset($_POST['update_testimonials'])) {
        try {
            $conn->beginTransaction();
            
            // Handle testimonial items
            if (isset($_POST['testimonial_ids']) && is_array($_POST['testimonial_ids'])) {
                $testimonialIds = $_POST['testimonial_ids'];
                $testimonialNames = $_POST['testimonial_names'];
                $testimonialPositions = $_POST['testimonial_positions'];
                $testimonialTexts = $_POST['testimonial_texts'];
                $testimonialOrders = $_POST['testimonial_orders'];
                $testimonialActives = isset($_POST['testimonial_actives']) ? $_POST['testimonial_actives'] : [];
                $testimonialImagePaths = $_POST['testimonial_image_paths'];
                
                // Process each testimonial
                for ($i = 0; $i < count($testimonialIds); $i++) {
                    $id = (int)$testimonialIds[$i];
                    $name = trim($testimonialNames[$i]);
                    $position = trim($testimonialPositions[$i]);
                    $text = trim($testimonialTexts[$i]);
                    $order = (int)$testimonialOrders[$i];
                    $isActive = in_array($id, $testimonialActives) ? 1 : 0;
                    $imagePath = $testimonialImagePaths[$i];
                    
                    // Handle image upload if provided
                    if (!empty($_FILES['testimonial_images']['name'][$i])) {
                        // Create a temporary superglobal entry for the handleImageUpload function
                        $_FILES['temp_image'] = [
                            'name' => $_FILES['testimonial_images']['name'][$i],
                            'type' => $_FILES['testimonial_images']['type'][$i],
                            'tmp_name' => $_FILES['testimonial_images']['tmp_name'][$i],
                            'error' => $_FILES['testimonial_images']['error'][$i],
                            'size' => $_FILES['testimonial_images']['size'][$i],
                        ];
                        
                        $uploadResult = handleImageUpload('temp_image', $imagePath);
                        if (!$uploadResult['success']) {
                            throw new Exception("Error uploading image for testimonial #" . ($i + 1) . ": " . $uploadResult['message']);
                        }
                        $imagePath = $uploadResult['path'];
                    }
                    
                    // Update the testimonial
                    $stmt = $conn->prepare("UPDATE testimonials
                                       SET client_name = :name, 
                                           client_position = :position, 
                                           testimonial_text = :text, 
                                           display_order = :order, 
                                           is_active = :isActive,
                                           client_image = :imagePath
                                       WHERE id = :id");
                    
                    $stmt->bindParam(':name', $name);
                    $stmt->bindParam(':position', $position);
                    $stmt->bindParam(':text', $text);
                    $stmt->bindParam(':order', $order);
                    $stmt->bindParam(':isActive', $isActive);
                    $stmt->bindParam(':imagePath', $imagePath);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }
            }
            
            // Add new testimonial if provided
            if (!empty($_POST['new_testimonial_name']) && !empty($_POST['new_testimonial_text'])) {
                $newName = trim($_POST['new_testimonial_name']);
                $newPosition = trim($_POST['new_testimonial_position']);
                $newText = trim($_POST['new_testimonial_text']);
                $newOrder = (int)$_POST['new_testimonial_order'];
                $imagePath = '';
                
                // Handle image upload if provided
                if (!empty($_FILES['new_testimonial_image']['name'])) {
                    $uploadResult = handleImageUpload('new_testimonial_image');
                    if (!$uploadResult['success']) {
                        throw new Exception("Error uploading image for new testimonial: " . $uploadResult['message']);
                    }
                    $imagePath = $uploadResult['path'];
                }
                
                // Insert new testimonial
                $stmt = $conn->prepare("INSERT INTO testimonials
                                   (client_name, client_position, testimonial_text, display_order, is_active, client_image)
                                   VALUES (:name, :position, :text, :order, 1, :imagePath)");
                
                $stmt->bindParam(':name', $newName);
                $stmt->bindParam(':position', $newPosition);
                $stmt->bindParam(':text', $newText);
                $stmt->bindParam(':order', $newOrder);
                $stmt->bindParam(':imagePath', $imagePath);
                $stmt->execute();
            }
            
            $conn->commit();
            $message = "Testimonials updated successfully!";
            $messageType = "success";
        } catch (Exception $e) {
            $conn->rollBack();
            $message = "Error updating testimonials: " . $e->getMessage();
            $messageType = "error";
        }
    }
    
    // Delete testimonial
    elseif (isset($_POST['delete_testimonial'])) {
        try {
            $conn->beginTransaction();
            
            $id = (int)$_POST['testimonial_id'];
            
            // Get image path to delete file
            $stmt = $conn->prepare("SELECT client_image FROM testimonials WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $testimonial = $stmt->fetch();
            
            // Delete file if it exists and is in our upload directory
            if ($testimonial && !empty($testimonial['client_image'])) {
                $imagePath = $testimonial['client_image'];
                if (strpos($imagePath, 'assets/images/uploads/testimonial/') === 0) {
                    $fullPath = $rootPath . '/' . $imagePath;
                    if (file_exists($fullPath)) {
                        @unlink($fullPath);
                    }
                }
            }
            
            // Delete the testimonial
            $stmt = $conn->prepare("DELETE FROM testimonials WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $conn->commit();
            $message = "Testimonial deleted successfully!";
            $messageType = "success";
        } catch (Exception $e) {
            $conn->rollBack();
            $message = "Error deleting testimonial: " . $e->getMessage();
            $messageType = "error";
        }
    }
}

// Fetch page settings
$pageSettings = [];
try {
    $stmt = $conn->query("SELECT * FROM testimonial_page_settings WHERE id = 1 LIMIT 1");
    $pageSettings = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
} catch(PDOException $e) {
    // Handle error silently, use default values
    $pageSettings = [
        'page_title' => 'Testimoni',
        'meta_description' => 'Testimoni dari pelanggan Akademi Merdeka',
        'breadcrumb_parent' => 'Tentang',
        'breadcrumb_current' => 'Testimoni',
        'section_title' => 'Testimoni Customer',
        'section_subtitle' => 'Apa Kata Mereka?'
    ];
}

// Fetch testimonials data
$testimonialsData = [];
try {
    $stmt = $conn->query("SELECT * FROM testimonials ORDER BY display_order ASC");
    $testimonialsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Handle error silently
    $testimonialsData = [];
}
?>

<!doctype html>
<html lang="id">
<?php include('../../components/head.php'); ?>
<body class="bg-gray-50">
    <div class="min-h-screen flex flex-col lg:flex-row">
        <?php include('../../components/sidebar.php'); ?>
        
        <div class="flex-1 lg:ml-64">
            <div class="bg-white p-4 shadow-sm flex justify-between items-center">
                <h1 class="text-xl font-semibold text-gray-800">Edit Testimoni Page</h1>
                <div class="flex items-center space-x-4">
                    <a href="/testimonial" target="_blank" class="text-blue-600 hover:text-blue-800 flex items-center">
                        <i class="bx bx-link-external mr-1"></i> View Page
                    </a>
                    <span class="text-gray-600">Welcome, <?php echo htmlspecialchars($currentUsername); ?></span>
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
                <div class="mb-6 border-b border-gray-200">
                    <nav class="flex flex-wrap -mb-px">
                        <a href="?tab=testimonials" class="mr-8 py-4 px-1 border-b-2 font-medium text-sm <?php echo $activeTab == 'testimonials' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>">
                            Manage Testimonials
                        </a>
                        <a href="?tab=settings" class="mr-8 py-4 px-1 border-b-2 font-medium text-sm <?php echo $activeTab == 'settings' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>">
                            Page Settings
                        </a>
                    </nav>
                </div>
                
                <!-- Page Settings Tab -->
                <?php if ($activeTab == 'settings'): ?>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Page Settings</h2>
                        <p class="text-sm text-gray-500 mt-1">Customize all text elements on the testimonial page</p>
                    </div>
                    <div class="p-6">
                        <form method="POST" action="?tab=settings">
                            <!-- Main Page Settings -->
                            <div class="p-4 border border-gray-200 rounded-lg mb-6">
                                <h3 class="text-md font-medium text-gray-800 mb-4">Page Header Settings</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="page_title" class="block text-sm font-medium text-gray-700 mb-1">Page Title</label>
                                        <input type="text" id="page_title" name="page_title" 
                                               value="<?php echo htmlspecialchars($pageSettings['page_title'] ?? 'Testimoni'); ?>" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">This appears in the page header and browser title.</p>
                                    </div>
    
                                </div>
                                
                                <div class="mt-4">
                                    <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
                                    <textarea id="meta_description" name="meta_description" rows="3" 
                                             class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($pageSettings['meta_description'] ?? 'Testimoni dari pelanggan Akademi Merdeka'); ?></textarea>
                                    <p class="mt-1 text-xs text-gray-500">Meta description for SEO purposes. Recommended length: 150-160 characters.</p>
                                </div>
                            </div>
                            
                            <!-- Breadcrumb Settings -->
                            <div class="p-4 border border-gray-200 rounded-lg mb-6">
                                <h3 class="text-md font-medium text-gray-800 mb-4">Breadcrumb Settings</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="breadcrumb_parent" class="block text-sm font-medium text-gray-700 mb-1">Parent Link Text</label>
                                        <input type="text" id="breadcrumb_parent" name="breadcrumb_parent" 
                                               value="<?php echo htmlspecialchars($pageSettings['breadcrumb_parent'] ?? 'Tentang'); ?>" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">The first item in the breadcrumb navigation.</p>
                                    </div>
                                    <div>
                                        <label for="breadcrumb_current" class="block text-sm font-medium text-gray-700 mb-1">Current Page Text</label>
                                        <input type="text" id="breadcrumb_current" name="breadcrumb_current" 
                                               value="<?php echo htmlspecialchars($pageSettings['breadcrumb_current'] ?? 'Testimoni'); ?>" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">The current page in the breadcrumb navigation.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Section Title Settings -->
                            <div class="p-4 border border-gray-200 rounded-lg mb-6">
                                <h3 class="text-md font-medium text-gray-800 mb-4">Section Title Settings</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="section_title" class="block text-sm font-medium text-gray-700 mb-1">Section Header</label>
                                        <input type="text" id="section_title" name="section_title" 
                                               value="<?php echo htmlspecialchars($pageSettings['section_title'] ?? 'Testimoni Customer'); ?>" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">The main heading above the testimonials.</p>
                                    </div>
                                    <div>
                                        <label for="section_subtitle" class="block text-sm font-medium text-gray-700 mb-1">Section Subheader</label>
                                        <input type="text" id="section_subtitle" name="section_subtitle" 
                                               value="<?php echo htmlspecialchars($pageSettings['section_subtitle'] ?? 'Apa Kata Mereka?'); ?>" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">The subheading displayed below the main heading.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Preview -->
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-6">
                                <h3 class="text-md font-medium text-gray-800 mb-2">Page Preview</h3>
                                <div class="bg-white p-4 rounded-lg border border-gray-300 mb-2">
                                    <div class="text-center">
                                        <h3 class="text-xl font-bold"><?php echo htmlspecialchars($pageSettings['page_title'] ?? 'Testimoni'); ?></h3>
                                        <div class="text-sm text-gray-600 mt-1">
                                            <span><?php echo htmlspecialchars($pageSettings['breadcrumb_parent'] ?? 'Tentang'); ?></span>
                                            <span class="mx-2">›</span>
                                            <span><?php echo htmlspecialchars($pageSettings['breadcrumb_current'] ?? 'Testimoni'); ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-white p-4 rounded-lg border border-gray-300">
                                    <div class="text-center">
                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs"><?php echo htmlspecialchars($pageSettings['section_title'] ?? 'Testimoni Customer'); ?></span>
                                        <h2 class="text-lg font-bold mt-1"><?php echo htmlspecialchars($pageSettings['section_subtitle'] ?? 'Apa Kata Mereka?'); ?></h2>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-6 flex justify-end">
                                <button type="submit" name="update_page_settings" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    Save Page Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Testimonials Tab -->
                <?php if ($activeTab == 'testimonials'): ?>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Manage Testimonials</h2>
                        <p class="text-sm text-gray-500 mt-1">Add, edit or remove testimonials from customers</p>
                    </div>
                    
                    <div class="p-6">
                        <form method="POST" action="?tab=testimonials" enctype="multipart/form-data">
                            <!-- Existing Testimonials -->
                            <h3 class="text-md font-medium text-gray-900 mb-2">Current Testimonials</h3>
                            
                            <div class="space-y-6">
                                <?php if(empty($testimonialsData)): ?>
                                <div class="text-center text-sm text-gray-500 p-6 bg-gray-50 rounded-lg">
                                    No testimonials found. Add a new testimonial below.
                                </div>
                                <?php else: ?>
                                    <?php foreach($testimonialsData as $index => $testimonial): ?>
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <div class="flex items-center justify-between mb-4">
                                            <h4 class="font-medium text-gray-800">Testimonial #<?php echo $index + 1; ?></h4>
                                            <div class="flex items-center space-x-3">
                                                <div class="flex items-center">
                                                    <span class="mr-2 text-sm text-gray-600">Active</span>
                                                    <input type="hidden" name="testimonial_ids[]" value="<?php echo $testimonial['id']; ?>">
                                                    <input type="checkbox" name="testimonial_actives[]" value="<?php echo $testimonial['id']; ?>" 
                                                           <?php echo $testimonial['is_active'] ? 'checked' : ''; ?> 
                                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                                </div>
                                                
                                                <form method="POST" action="?tab=testimonials" class="inline">
                                                    <input type="hidden" name="testimonial_id" value="<?php echo $testimonial['id']; ?>">
                                                    <button type="submit" name="delete_testimonial" 
                                                            class="text-red-600 hover:text-red-800"
                                                            onclick="return confirm('Are you sure you want to delete this testimonial? This action cannot be undone.');">
                                                        <i class="bx bx-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                            <div class="md:col-span-1">
                                                <div class="bg-gray-100 p-4 rounded-lg text-center mb-2">
                                                    <?php if (!empty($testimonial['client_image'])): ?>
                                                    <img src="../../../<?php echo htmlspecialchars($testimonial['client_image']); ?>" alt="Client" class="h-20 w-20 object-cover rounded-full inline-block">
                                                    <input type="hidden" name="testimonial_image_paths[]" value="<?php echo htmlspecialchars($testimonial['client_image']); ?>">
                                                    <?php else: ?>
                                                    <div class="h-20 w-20 rounded-full bg-gray-300 inline-flex items-center justify-center">
                                                        <i class="bx bx-user text-gray-400 text-3xl"></i>
                                                    </div>
                                                    <input type="hidden" name="testimonial_image_paths[]" value="">
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <input type="file" name="testimonial_images[<?php echo $index; ?>]" 
                                                       class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                                <p class="mt-1 text-xs text-gray-500 text-center">Upload new image (square, min 200x200px)</p>
                                            </div>
                                            
                                            <div class="md:col-span-2">
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                                        <input type="text" name="testimonial_names[]" value="<?php echo htmlspecialchars($testimonial['client_name']); ?>" 
                                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                                                        <input type="text" name="testimonial_positions[]" value="<?php echo htmlspecialchars($testimonial['client_position']); ?>" 
                                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-4">
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Testimonial Text</label>
                                                    <textarea name="testimonial_texts[]" rows="3" 
                                                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($testimonial['testimonial_text']); ?></textarea>
                                                </div>
                                                
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Display Order</label>
                                                    <input type="number" name="testimonial_orders[]" value="<?php echo (int)$testimonial['display_order']; ?>" min="1" 
                                                           class="w-20 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Add New Testimonial -->
                            <div class="mt-8 p-4 border border-gray-200 rounded-lg bg-gray-50">
                                <h3 class="text-base font-medium text-gray-900 mb-4">Add New Testimonial</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label for="new_testimonial_name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                        <input type="text" id="new_testimonial_name" name="new_testimonial_name" placeholder="e.g. John Doe" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label for="new_testimonial_position" class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                                        <input type="text" id="new_testimonial_position" name="new_testimonial_position" placeholder="e.g. Student" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="new_testimonial_text" class="block text-sm font-medium text-gray-700 mb-1">Testimonial Text</label>
                                    <textarea id="new_testimonial_text" name="new_testimonial_text" rows="3" placeholder="Enter the testimonial text here..." 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="new_testimonial_order" class="block text-sm font-medium text-gray-700 mb-1">Order</label>
                                        <input type="number" id="new_testimonial_order" name="new_testimonial_order" value="<?php echo count($testimonialsData) + 1; ?>" min="1" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label for="new_testimonial_image" class="block text-sm font-medium text-gray-700 mb-1">Client Image</label>
                                        <input type="file" id="new_testimonial_image" name="new_testimonial_image" 
                                               class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-6 flex justify-end">
                                <button type="submit" name="update_testimonials" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    Save All Testimonials
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
</body>
</html>