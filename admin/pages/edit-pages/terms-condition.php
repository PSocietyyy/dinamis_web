<?php
// Start the session
session_start();

// Check if not logged in
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../../../login.php");
    exit;
}

// Include database connection
require_once('../../../config.php');

// Configuration
$uploadDirectory = '../../../assets/images/uploads/termsc/';

// Ensure directories exist with proper permissions
if (!file_exists($uploadDirectory)) {
    // Create directory if it doesn't exist
    if (!@mkdir($uploadDirectory, 0777, true)) {
        $error = error_get_last();
    }
}

// Initialize variables
$message = '';
$messageType = '';
$currentUsername = $_SESSION['username'];
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'terms';
$editTermId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;

// Handle image uploads
function handleImageUpload($fileInput, $uploadDir, $oldPath = null) {
    // Check if a file was uploaded
    if (isset($_FILES[$fileInput]) && $_FILES[$fileInput]['error'] === UPLOAD_ERR_OK) {
        $tempFile = $_FILES[$fileInput]['tmp_name'];
        $fileInfo = pathinfo($_FILES[$fileInput]['name']);
        $extension = strtolower($fileInfo['extension']);
        
        // Validate file type
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($extension, $allowedExtensions)) {
            return [
                'success' => false,
                'message' => "Invalid file type. Only JPG, PNG, GIF, and WEBP files are allowed."
            ];
        }
        
        // Make sure upload directory exists and is writable
        if (!file_exists($uploadDir)) {
            if (!@mkdir($uploadDir, 0777, true)) {
                return [
                    'success' => false,
                    'message' => "Failed to create upload directory: " . $uploadDir
                ];
            }
        }
        
        if (!is_writable($uploadDir)) {
            @chmod($uploadDir, 0777);
            if (!is_writable($uploadDir)) {
                return [
                    'success' => false,
                    'message' => "Upload directory exists but is not writable: " . $uploadDir
                ];
            }
        }
        
        // Generate a unique filename to prevent overwriting
        $prefix = 'banner_';
        $newFilename = $prefix . uniqid() . '.' . $extension;
        $targetPath = $uploadDir . $newFilename;
        
        // Move the uploaded file
        if (@move_uploaded_file($tempFile, $targetPath)) {
            // Delete old file if it exists and is in the uploads directory
            if ($oldPath && strpos($oldPath, 'uploads/termsc') !== false && file_exists('../../../' . $oldPath)) {
                @unlink('../../../' . $oldPath);
            }
            
            // Get the relative path for the database (from website root)
            $relativePath = 'assets/images/uploads/termsc/' . $newFilename;
            
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
    
    // Update page settings - General Tab
    if (isset($_POST['update_general_settings'])) {
        try {
            $conn->beginTransaction();
            
            $title = $_POST['title'] ?? '';
            $subtitle = $_POST['subtitle'] ?? '';
            $seoTitle = $_POST['seo_title'] ?? '';
            $seoDescription = $_POST['seo_description'] ?? '';
            $seoKeywords = $_POST['seo_keywords'] ?? '';
            
            $stmt = $conn->prepare("INSERT INTO terms_conditions_settings 
                                 (id, title, subtitle, seo_title, seo_description, seo_keywords) 
                                 VALUES (1, :title, :subtitle, :seo_title, :seo_description, :seo_keywords)
                                 ON DUPLICATE KEY UPDATE 
                                 title = VALUES(title), 
                                 subtitle = VALUES(subtitle), 
                                 seo_title = VALUES(seo_title),
                                 seo_description = VALUES(seo_description),
                                 seo_keywords = VALUES(seo_keywords)");
            
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':subtitle', $subtitle);
            $stmt->bindParam(':seo_title', $seoTitle);
            $stmt->bindParam(':seo_description', $seoDescription);
            $stmt->bindParam(':seo_keywords', $seoKeywords);
            $stmt->execute();
            
            $conn->commit();
            $message = "General page settings updated successfully!";
            $messageType = "success";
            $activeTab = 'general';
        } catch (Exception $e) {
            $conn->rollBack();
            $message = "Error updating page settings: " . $e->getMessage();
            $messageType = "error";
        }
    }
    
    // Update page banner settings
    if (isset($_POST['update_banner_settings'])) {
        try {
            $conn->beginTransaction();
            
            $innerTitle = $_POST['inner_title'] ?? '';
            $breadcrumbParent = $_POST['breadcrumb_parent'] ?? '';
            $breadcrumbParentLink = $_POST['breadcrumb_parent_link'] ?? '';
            $breadcrumbCurrent = $_POST['breadcrumb_current'] ?? '';
            $currentBannerImage = $_POST['current_banner_image'] ?? '';
            
            // Handle banner image upload
            $uploadResult = handleImageUpload('banner_image', $uploadDirectory, $currentBannerImage);
            if (!$uploadResult['success']) {
                throw new Exception($uploadResult['message']);
            }
            $bannerImage = $uploadResult['path'];
            
            $stmt = $conn->prepare("INSERT INTO terms_conditions_settings 
                                 (id, inner_title, breadcrumb_parent, breadcrumb_parent_link, breadcrumb_current, banner_image) 
                                 VALUES (1, :inner_title, :breadcrumb_parent, :breadcrumb_parent_link, :breadcrumb_current, :banner_image)
                                 ON DUPLICATE KEY UPDATE 
                                 inner_title = VALUES(inner_title), 
                                 breadcrumb_parent = VALUES(breadcrumb_parent), 
                                 breadcrumb_parent_link = VALUES(breadcrumb_parent_link),
                                 breadcrumb_current = VALUES(breadcrumb_current),
                                 banner_image = VALUES(banner_image)");
            
            $stmt->bindParam(':inner_title', $innerTitle);
            $stmt->bindParam(':breadcrumb_parent', $breadcrumbParent);
            $stmt->bindParam(':breadcrumb_parent_link', $breadcrumbParentLink);
            $stmt->bindParam(':breadcrumb_current', $breadcrumbCurrent);
            $stmt->bindParam(':banner_image', $bannerImage);
            $stmt->execute();
            
            $conn->commit();
            $message = "Banner settings updated successfully!";
            $messageType = "success";
            $activeTab = 'banner';
        } catch (Exception $e) {
            $conn->rollBack();
            $message = "Error updating banner settings: " . $e->getMessage();
            $messageType = "error";
        }
    }
    
    // Add new terms and conditions section
    elseif (isset($_POST['add_term'])) {
        try {
            $conn->beginTransaction();
            
            $title = $_POST['title'] ?? '';
            $content = $_POST['content'] ?? '';
            $order = (int)$_POST['display_order'] ?? 0;
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            
            $stmt = $conn->prepare("INSERT INTO terms_conditions (title, content, display_order, is_active) 
                                 VALUES (:title, :content, :display_order, :is_active)");
            
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':display_order', $order);
            $stmt->bindParam(':is_active', $isActive);
            $stmt->execute();
            
            $conn->commit();
            $message = "Terms and conditions section added successfully!";
            $messageType = "success";
            $activeTab = 'terms';
        } catch (Exception $e) {
            $conn->rollBack();
            $message = "Error adding terms and conditions section: " . $e->getMessage();
            $messageType = "error";
        }
    }
    
    // Update existing terms and conditions section
    elseif (isset($_POST['update_term'])) {
        try {
            $conn->beginTransaction();
            
            $id = (int)$_POST['term_id'];
            $title = $_POST['title'] ?? '';
            $content = $_POST['content'] ?? '';
            $order = (int)$_POST['display_order'] ?? 0;
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            
            $stmt = $conn->prepare("UPDATE terms_conditions 
                                 SET title = :title, 
                                     content = :content, 
                                     display_order = :display_order, 
                                     is_active = :is_active 
                                 WHERE id = :id");
            
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':display_order', $order);
            $stmt->bindParam(':is_active', $isActive);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $conn->commit();
            $message = "Terms and conditions section updated successfully!";
            $messageType = "success";
            $activeTab = 'terms';
            $editTermId = $id; // Keep editing the same section
        } catch (Exception $e) {
            $conn->rollBack();
            $message = "Error updating terms and conditions section: " . $e->getMessage();
            $messageType = "error";
        }
    }
    
    // Delete terms and conditions section
    elseif (isset($_POST['delete_term'])) {
        try {
            $conn->beginTransaction();
            
            $id = (int)$_POST['term_id'];
            
            // Delete the section
            $stmt = $conn->prepare("DELETE FROM terms_conditions WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $conn->commit();
            $message = "Terms and conditions section deleted successfully!";
            $messageType = "success";
            $activeTab = 'terms';
            $editTermId = 0; // Clear edit mode
        } catch (Exception $e) {
            $conn->rollBack();
            $message = "Error deleting terms and conditions section: " . $e->getMessage();
            $messageType = "error";
        }
    }
    
    // Reorder terms and conditions sections
    elseif (isset($_POST['reorder_terms'])) {
        try {
            $conn->beginTransaction();
            
            $termIds = $_POST['term_ids'] ?? [];
            $termOrders = $_POST['term_orders'] ?? [];
            
            if (count($termIds) === count($termOrders)) {
                for ($i = 0; $i < count($termIds); $i++) {
                    $id = (int)$termIds[$i];
                    $order = (int)$termOrders[$i];
                    
                    $stmt = $conn->prepare("UPDATE terms_conditions SET display_order = :order WHERE id = :id");
                    $stmt->bindParam(':order', $order);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }
                
                $conn->commit();
                $message = "Terms and conditions order updated successfully!";
                $messageType = "success";
                $activeTab = 'terms';
            } else {
                throw new Exception("Invalid reorder data");
            }
        } catch (Exception $e) {
            $conn->rollBack();
            $message = "Error reordering terms and conditions: " . $e->getMessage();
            $messageType = "error";
        }
    }
}

// Fetch data
// Page settings
$pageSettings = [];
try {
    $stmt = $conn->query("SELECT * FROM terms_conditions_settings WHERE id = 1 LIMIT 1");
    if ($stmt->rowCount() > 0) {
        $pageSettings = $stmt->fetch();
    }
} catch(PDOException $e) {
    // Handle error silently
}

// Terms and conditions sections
$termsConditions = [];
try {
    $stmt = $conn->query("SELECT * FROM terms_conditions ORDER BY display_order ASC");
    $termsConditions = $stmt->fetchAll();
} catch(PDOException $e) {
    // Handle error silently
}

// Fetch specific section for editing
$editTerm = null;
if ($editTermId > 0) {
    try {
        $stmt = $conn->prepare("SELECT * FROM terms_conditions WHERE id = :id");
        $stmt->bindParam(':id', $editTermId);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $editTerm = $stmt->fetch();
            $activeTab = 'terms';
        }
    } catch(PDOException $e) {
        // Handle error silently
    }
}

// Get next display order
$nextOrder = 1;
if (!empty($termsConditions)) {
    $maxOrder = 0;
    foreach ($termsConditions as $term) {
        if ((int)$term['display_order'] > $maxOrder) {
            $maxOrder = (int)$term['display_order'];
        }
    }
    $nextOrder = $maxOrder + 1;
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
                <h1 class="text-xl font-semibold text-gray-800">Edit Terms & Conditions Page</h1>
                <div class="flex items-center space-x-4">
                    <a href="/terms-condition" target="_blank" class="text-blue-600 hover:text-blue-800 flex items-center">
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
                    <nav class="flex flex-wrap space-x-8">
                        <a href="?tab=terms" class="py-4 px-1 border-b-2 font-medium text-sm leading-5 <?php echo $activeTab === 'terms' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>">
                            Terms & Conditions Sections
                        </a>
                        <a href="?tab=general" class="py-4 px-1 border-b-2 font-medium text-sm leading-5 <?php echo $activeTab === 'general' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>">
                            General Settings
                        </a>
                        <a href="?tab=banner" class="py-4 px-1 border-b-2 font-medium text-sm leading-5 <?php echo $activeTab === 'banner' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>">
                            Banner Settings
                        </a>
                    </nav>
                </div>
                
                <!-- Terms & Conditions Sections Tab -->
                <?php if ($activeTab === 'terms'): ?>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Terms & Conditions List -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <div class="p-6 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-800">Terms & Conditions Sections</h2>
                                <p class="text-sm text-gray-500 mt-1">Manage your terms and conditions content</p>
                            </div>
                            
                            <div class="p-6">
                                <!-- Terms & Conditions Table -->
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <?php if (empty($termsConditions)): ?>
                                            <tr>
                                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No terms and conditions sections found. Add your first section.</td>
                                            </tr>
                                            <?php else: ?>
                                                <?php foreach ($termsConditions as $term): ?>
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($term['title']); ?></div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $term['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                                            <?php echo $term['is_active'] ? 'Active' : 'Inactive'; ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        <?php echo (int)$term['display_order']; ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                        <a href="?edit=<?php echo $term['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                                        <form method="POST" action="" class="inline-block">
                                                            <input type="hidden" name="term_id" value="<?php echo $term['id']; ?>">
                                                            <button type="submit" name="delete_term" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this section?');">Delete</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Reorder Form -->
                                <?php if (count($termsConditions) > 1): ?>
                                <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <h3 class="text-sm font-medium text-gray-700 mb-3">Reorder Sections</h3>
                                    <form method="POST" action="">
                                        <div class="space-y-3">
                                            <?php foreach ($termsConditions as $index => $term): ?>
                                            <div class="flex items-center space-x-3">
                                                <input type="hidden" name="term_ids[]" value="<?php echo $term['id']; ?>">
                                                <div class="flex-shrink-0">
                                                    <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-gray-200">
                                                        <?php echo $index + 1; ?>
                                                    </span>
                                                </div>
                                                <div class="flex-1">
                                                    <span class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($term['title']); ?></span>
                                                </div>
                                                <div class="w-20">
                                                    <input type="number" name="term_orders[]" value="<?php echo (int)$term['display_order']; ?>" min="1" 
                                                           class="block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-300 focus:outline-none">
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="mt-4">
                                            <button type="submit" name="reorder_terms" 
                                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                Update Order
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Add/Edit Terms & Conditions Section Form -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <div class="p-6 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-800">
                                    <?php echo $editTerm ? 'Edit Section' : 'Add New Section'; ?>
                                </h2>
                            </div>
                            
                            <div class="p-6">
                                <form method="POST" action="">
                                    <?php if ($editTerm): ?>
                                    <input type="hidden" name="term_id" value="<?php echo $editTerm['id']; ?>">
                                    <?php endif; ?>
                                    
                                    <div class="space-y-6">
                                        <!-- Title Field -->
                                        <div class="mb-5">
                                            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Section Title <span class="text-red-500">*</span></label>
                                            <input type="text" name="title" id="title" required
                                                value="<?php echo htmlspecialchars($editTerm['title'] ?? ''); ?>"
                                                class="block w-full px-4 py-2.5 text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-300 focus:outline-none transition-colors duration-200 hover:border-gray-400">
                                        </div>
                                        
                                        <!-- Content Field -->
                                        <div class="mb-5">
                                            <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Content <span class="text-red-500">*</span></label>
                                            <textarea name="content" id="content" rows="10" required
                                                class="block w-full px-4 py-2.5 text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-300 focus:outline-none transition-colors duration-200 hover:border-gray-400"><?php echo htmlspecialchars($editTerm['content'] ?? ''); ?></textarea>
                                            <p class="text-xs text-gray-500 mt-1">You can use HTML tags for formatting.</p>
                                        </div>
                                        
                                        <!-- Display Order Field -->
                                        <div class="mb-5">
                                            <label for="display_order" class="block text-sm font-medium text-gray-700 mb-1">Display Order</label>
                                            <input type="number" name="display_order" id="display_order" min="1"
                                                value="<?php echo (int)($editTerm['display_order'] ?? $nextOrder); ?>"
                                                class="block w-full px-4 py-2.5 text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-300 focus:outline-none transition-colors duration-200 hover:border-gray-400">
                                        </div>
                                        
                                        <!-- Is Active Field -->
                                        <div class="mb-5">
                                            <div class="flex items-start">
                                                <div class="flex items-center h-5">
                                                    <input type="checkbox" name="is_active" id="is_active"
                                                        <?php echo (!$editTerm || $editTerm['is_active']) ? 'checked' : ''; ?>
                                                        class="h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-300 focus:ring-opacity-50 focus:outline-none">
                                                </div>
                                                <div class="ml-3 text-sm">
                                                    <label for="is_active" class="font-medium text-gray-700">Active</label>
                                                    <p class="text-gray-500">Show this section on the website</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-6 flex justify-end">
                                        <?php if ($editTerm): ?>
                                        <a href="?tab=terms" class="mr-3 inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                            Cancel
                                        </a>
                                        <button type="submit" name="update_term" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                            Update Section
                                        </button>
                                        <?php else: ?>
                                        <button type="submit" name="add_term" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                            Add Section
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- General Settings Tab -->
                <?php if ($activeTab === 'general'): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">General Page Settings</h2>
                        <p class="text-sm text-gray-500 mt-1">Customize the terms and conditions page content and SEO</p>
                    </div>
                    
                    <div class="p-6">
                        <form method="POST" action="">
                            <div class="grid grid-cols-1 gap-6">
                                <!-- Main Content Section -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h3 class="text-md font-medium text-gray-800 mb-4">Main Content</h3>
                                    
                                    <!-- Page Title -->
                                    <div class="mb-4">
                                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Page Title <span class="text-red-500">*</span></label>
                                        <input type="text" name="title" id="title" required
                                            value="<?php echo htmlspecialchars($pageSettings['title'] ?? 'Akademi Merdeka'); ?>"
                                            class="block w-full px-4 py-2.5 text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-300 focus:outline-none transition-colors duration-200 hover:border-gray-400">
                                        <p class="text-xs text-gray-500 mt-1">Main heading displayed on the terms and conditions page</p>
                                    </div>
                                    
                                    <!-- Page Subtitle -->
                                    <div class="mb-4">
                                        <label for="subtitle" class="block text-sm font-medium text-gray-700 mb-1">Page Subtitle</label>
                                        <input type="text" name="subtitle" id="subtitle"
                                            value="<?php echo htmlspecialchars($pageSettings['subtitle'] ?? 'Syarat & Ketentuan'); ?>"
                                            class="block w-full px-4 py-2.5 text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-300 focus:outline-none transition-colors duration-200 hover:border-gray-400">
                                        <p class="text-xs text-gray-500 mt-1">Smaller text displayed above the main title</p>
                                    </div>
                                </div>
                                
                                <!-- SEO Section -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h3 class="text-md font-medium text-gray-800 mb-4">SEO Settings</h3>
                                    
                                    <!-- SEO Title -->
                                    <div class="mb-4">
                                        <label for="seo_title" class="block text-sm font-medium text-gray-700 mb-1">SEO Title</label>
                                        <input type="text" name="seo_title" id="seo_title"
                                            value="<?php echo htmlspecialchars($pageSettings['seo_title'] ?? 'Syarat & Ketentuan | Akademi Merdeka'); ?>"
                                            class="block w-full px-4 py-2.5 text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-300 focus:outline-none transition-colors duration-200 hover:border-gray-400">
                                        <p class="text-xs text-gray-500 mt-1">Title shown in browser tab and search results</p>
                                    </div>
                                    
                                    <!-- SEO Description -->
                                    <div class="mb-4">
                                        <label for="seo_description" class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
                                        <textarea name="seo_description" id="seo_description" rows="3"
                                            class="block w-full px-4 py-2.5 text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-300 focus:outline-none transition-colors duration-200 hover:border-gray-400 resize-none"><?php echo htmlspecialchars($pageSettings['seo_description'] ?? ''); ?></textarea>
                                        <p class="text-xs text-gray-500 mt-1">Short description for search engines (150-160 characters ideal)</p>
                                    </div>
                                    
                                    <!-- SEO Keywords -->
                                    <div>
                                        <label for="seo_keywords" class="block text-sm font-medium text-gray-700 mb-1">Meta Keywords</label>
                                        <input type="text" name="seo_keywords" id="seo_keywords"
                                            value="<?php echo htmlspecialchars($pageSettings['seo_keywords'] ?? ''); ?>"
                                            class="block w-full px-4 py-2.5 text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-300 focus:outline-none transition-colors duration-200 hover:border-gray-400">
                                        <p class="text-xs text-gray-500 mt-1">Comma-separated keywords for search engines</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-6 flex justify-end">
                                <button type="submit" name="update_general_settings" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                    Save Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Banner Settings Tab -->
                <?php if ($activeTab === 'banner'): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Banner Settings</h2>
                        <p class="text-sm text-gray-500 mt-1">Customize the page banner and breadcrumb navigation</p>
                    </div>
                    
                    <div class="p-6">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <input type="hidden" name="current_banner_image" value="<?php echo htmlspecialchars($pageSettings['banner_image'] ?? 'assets/images/shape/inner-shape.png'); ?>">
                            
                            <div class="grid grid-cols-1 gap-6">
                                <!-- Banner Title -->
                                <div class="mb-4">
                                    <label for="inner_title" class="block text-sm font-medium text-gray-700 mb-1">Banner Title <span class="text-red-500">*</span></label>
                                    <input type="text" name="inner_title" id="inner_title" required
                                        value="<?php echo htmlspecialchars($pageSettings['inner_title'] ?? 'Syarat & Ketentuan'); ?>"
                                        class="block w-full px-4 py-2.5 text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-300 focus:outline-none transition-colors duration-200 hover:border-gray-400">
                                    <p class="text-xs text-gray-500 mt-1">Main heading displayed on the banner</p>
                                </div>
                                
                                <!-- Breadcrumbs Section -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h3 class="text-md font-medium text-gray-800 mb-4">Breadcrumb Navigation</h3>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="breadcrumb_parent" class="block text-sm font-medium text-gray-700 mb-1">Parent Link Text</label>
                                            <input type="text" name="breadcrumb_parent" id="breadcrumb_parent"
                                                value="<?php echo htmlspecialchars($pageSettings['breadcrumb_parent'] ?? 'Tentang'); ?>"
                                                class="block w-full px-4 py-2.5 text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-300 focus:outline-none transition-colors duration-200 hover:border-gray-400">
                                        </div>
                                        
                                        <div>
                                            <label for="breadcrumb_parent_link" class="block text-sm font-medium text-gray-700 mb-1">Parent Link URL</label>
                                            <input type="text" name="breadcrumb_parent_link" id="breadcrumb_parent_link"
                                                value="<?php echo htmlspecialchars($pageSettings['breadcrumb_parent_link'] ?? '/'); ?>"
                                                class="block w-full px-4 py-2.5 text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-300 focus:outline-none transition-colors duration-200 hover:border-gray-400">
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4">
                                        <label for="breadcrumb_current" class="block text-sm font-medium text-gray-700 mb-1">Current Page Text</label>
                                        <input type="text" name="breadcrumb_current" id="breadcrumb_current"
                                            value="<?php echo htmlspecialchars($pageSettings['breadcrumb_current'] ?? 'Syarat & Ketentuan'); ?>"
                                            class="block w-full px-4 py-2.5 text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-300 focus:outline-none transition-colors duration-200 hover:border-gray-400">
                                    </div>
                                    
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">Preview: <span class="text-gray-700">Home > <?php echo htmlspecialchars($pageSettings['breadcrumb_parent'] ?? 'Tentang'); ?> > <?php echo htmlspecialchars($pageSettings['breadcrumb_current'] ?? 'Syarat & Ketentuan'); ?></span></p>
                                    </div>
                                </div>
                                
                                <!-- Banner Image -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Banner Background Image</label>
                                    
                                    <?php if (!empty($pageSettings['banner_image'])): ?>
                                    <div class="mt-2 mb-4">
                                        <div class="relative rounded-lg overflow-hidden h-32 bg-gray-100">
                                            <img src="../../../<?php echo htmlspecialchars($pageSettings['banner_image']); ?>" alt="Banner Background" class="w-full h-full object-contain">
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">Current banner image</p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="mt-2">
                                        <input type="file" name="banner_image" id="banner_image"
                                            class="block w-full text-sm text-gray-700 file:mr-4 file:py-2.5 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-colors duration-200">
                                        <p class="text-xs text-gray-500 mt-1">Recommended: PNG or SVG with transparency.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-6 flex justify-end">
                                <button type="submit" name="update_banner_settings" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                    Save Banner Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Footer -->
                <div class="text-center text-gray-500 text-sm mt-8">
                    <p>&copy; 2023 Akademi Merdeka Admin Dashboard. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>