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

// Initialize variables
$message = '';
$messageType = '';
$currentUsername = $_SESSION['username'];

// Try creating the upload directory structure at initialization
$uploadDirectory = '../../../assets/uploads/team/';
if (!file_exists($uploadDirectory)) {
    // Create directory if it doesn't exist
    mkdir($uploadDirectory, 0755, true);
}

// Handle file uploads
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
            if (!@mkdir($uploadDirectory, 0755, true)) {
                return [
                    'success' => false,
                    'message' => "Failed to create upload directory. Please create this directory manually: assets/uploads/team"
                ];
            }
        }
        
        if (!is_writable($uploadDirectory)) {
            @chmod($uploadDirectory, 0755);
            if (!is_writable($uploadDirectory)) {
                return [
                    'success' => false,
                    'message' => "Upload directory exists but is not writable. Please check permissions for: " . $uploadDirectory
                ];
            }
        }
        
        // Generate a unique filename to prevent overwriting
        $newFilename = 'team_' . uniqid() . '.' . $extension;
        $targetPath = $uploadDirectory . $newFilename;
        
        // Move the uploaded file
        if (@move_uploaded_file($tempFile, $targetPath)) {
            // Get the relative path for the database (from website root)
            $relativePath = 'assets/uploads/team/' . $newFilename;
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
    
    // Update banner
    if (isset($_POST['update_banner'])) {
        try {
            $title = trim($_POST['banner_title']);
            $breadcrumbText = trim($_POST['banner_breadcrumb']);
            $imagePath = trim($_POST['banner_image']);
            
            // Handle banner image upload
            if (!empty($_FILES['banner_image_file']['name'])) {
                $uploadResult = handleImageUpload('banner_image_file', $imagePath);
                if (!$uploadResult['success']) {
                    throw new Exception($uploadResult['message']);
                }
                $imagePath = $uploadResult['path'];
            }
            
            // Check if banner exists
            $stmt = $conn->prepare("SELECT COUNT(*) FROM team_banners WHERE page_slug = :slug");
            $pageSlug = 'team';
            $stmt->bindParam(':slug', $pageSlug);
            $stmt->execute();
            
            if ($stmt->fetchColumn() > 0) {
                // Update existing banner
                $stmt = $conn->prepare("UPDATE team_banners 
                                     SET title = :title, 
                                         breadcrumb_text = :breadcrumb_text,
                                         banner_image = :banner_image 
                                     WHERE page_slug = :slug");
            } else {
                // Insert new banner
                $stmt = $conn->prepare("INSERT INTO team_banners 
                                     (page_slug, title, breadcrumb_text, banner_image) 
                                     VALUES (:slug, :title, :breadcrumb_text, :banner_image)");
            }
            
            $stmt->bindParam(':slug', $pageSlug);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':breadcrumb_text', $breadcrumbText);
            $stmt->bindParam(':banner_image', $imagePath);
            $stmt->execute();
            
            $message = "Banner updated successfully!";
            $messageType = "success";
        } catch (Exception $e) {
            $message = "Error updating banner: " . $e->getMessage();
            $messageType = "error";
        }
    }
    
    // Update team members
    if (isset($_POST['update_members'])) {
        try {
            $conn->beginTransaction();
            
            // Process each team member
            if (isset($_POST['member_ids']) && is_array($_POST['member_ids'])) {
                $memberIds = $_POST['member_ids'];
                $memberNames = $_POST['member_names'];
                $memberPositions = $_POST['member_positions'];
                $memberOrders = $_POST['member_orders'];
                $memberActives = isset($_POST['member_actives']) ? $_POST['member_actives'] : [];
                $memberImagePaths = $_POST['member_image_paths'];
                
                // Process each member
                for ($i = 0; $i < count($memberIds); $i++) {
                    $id = (int)$memberIds[$i];
                    $name = trim($memberNames[$i]);
                    $position = trim($memberPositions[$i]);
                    $order = (int)$memberOrders[$i];
                    $isActive = in_array($id, $memberActives) ? 1 : 0;
                    $imagePath = $memberImagePaths[$i];
                    
                    // Handle image upload if provided
                    if (!empty($_FILES['member_images']['name'][$i])) {
                        // Create a temporary superglobal entry for the handleImageUpload function
                        $_FILES['temp_image'] = [
                            'name' => $_FILES['member_images']['name'][$i],
                            'type' => $_FILES['member_images']['type'][$i],
                            'tmp_name' => $_FILES['member_images']['tmp_name'][$i],
                            'error' => $_FILES['member_images']['error'][$i],
                            'size' => $_FILES['member_images']['size'][$i],
                        ];
                        
                        $uploadResult = handleImageUpload('temp_image', $imagePath);
                        if (!$uploadResult['success']) {
                            throw new Exception("Error uploading image for member #" . ($i + 1) . ": " . $uploadResult['message']);
                        }
                        $imagePath = $uploadResult['path'];
                    }
                    
                    // Update the member
                    $stmt = $conn->prepare("UPDATE team_members
                                       SET name = :name, 
                                           position = :position, 
                                           display_order = :order, 
                                           is_active = :isActive,
                                           image_path = :imagePath
                                       WHERE id = :id");
                    
                    $stmt->bindParam(':name', $name);
                    $stmt->bindParam(':position', $position);
                    $stmt->bindParam(':order', $order);
                    $stmt->bindParam(':isActive', $isActive);
                    $stmt->bindParam(':imagePath', $imagePath);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }
            }
            
            // Add new member if provided
            if (!empty($_POST['new_member_name']) && !empty($_POST['new_member_position'])) {
                $newName = trim($_POST['new_member_name']);
                $newPosition = trim($_POST['new_member_position']);
                $newOrder = (int)$_POST['new_member_order'];
                $imagePath = '';
                
                // Handle image upload if provided
                if (!empty($_FILES['new_member_image']['name'])) {
                    $uploadResult = handleImageUpload('new_member_image');
                    if (!$uploadResult['success']) {
                        throw new Exception("Error uploading image for new member: " . $uploadResult['message']);
                    }
                    $imagePath = $uploadResult['path'];
                }
                
                // Insert new member
                $stmt = $conn->prepare("INSERT INTO team_members
                                   (name, position, display_order, is_active, image_path)
                                   VALUES (:name, :position, :order, 1, :imagePath)");
                
                $stmt->bindParam(':name', $newName);
                $stmt->bindParam(':position', $newPosition);
                $stmt->bindParam(':order', $newOrder);
                $stmt->bindParam(':imagePath', $imagePath);
                $stmt->execute();
            }
            
            $conn->commit();
            $message = "Team members updated successfully!";
            $messageType = "success";
        } catch (Exception $e) {
            $conn->rollBack();
            $message = "Error updating team members: " . $e->getMessage();
            $messageType = "error";
        }
    }
    
    // Delete team member
    if (isset($_POST['delete_member'])) {
        try {
            $id = (int)$_POST['member_id'];
            
            // Get the current image path
            $stmt = $conn->prepare("SELECT image_path FROM team_members WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $member = $stmt->fetch();
            
            // Delete the member
            $stmt = $conn->prepare("DELETE FROM team_members WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            // Delete the image file if it exists and is in the uploads directory
            if (!empty($member['image_path']) && strpos($member['image_path'], 'assets/uploads/team/') === 0) {
                $filePath = '../../../' . $member['image_path'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            $message = "Team member deleted successfully!";
            $messageType = "success";
        } catch (Exception $e) {
            $message = "Error deleting team member: " . $e->getMessage();
            $messageType = "error";
        }
    }
}

// Fetch team members
$teamMembers = [];
try {
    $stmt = $conn->query("SELECT * FROM team_members ORDER BY display_order ASC");
    $teamMembers = $stmt->fetchAll();
} catch(PDOException $e) {
    $message = "Error fetching team members: " . $e->getMessage();
    $messageType = "error";
}

// Fetch banner data
$bannerData = [];
try {
    $stmt = $conn->prepare("SELECT * FROM team_banners WHERE page_slug = :slug LIMIT 1");
    $pageSlug = 'team';
    $stmt->bindParam(':slug', $pageSlug);
    $stmt->execute();
    $bannerData = $stmt->fetch();
    
    // If no banner found, set default values
    if (!$bannerData) {
        $bannerData = [
            'title' => 'Tim',
            'breadcrumb_text' => 'Tim',
            'banner_image' => 'assets/images/shape/inner-shape.png'
        ];
    }
} catch(PDOException $e) {
    $message = "Error fetching banner data: " . $e->getMessage();
    $messageType = "error";
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
                <h1 class="text-xl font-semibold text-gray-800">Edit Team Page</h1>
                <div class="flex items-center space-x-4">
                    <a href="/team" target="_blank" class="text-blue-600 hover:text-blue-800 flex items-center">
                        <i class="bx bx-link-external mr-1"></i> View Team Page
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
                
                <!-- Banner Section -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Banner Section</h2>
                        <p class="text-sm text-gray-500 mt-1">Edit the banner section at the top of the team page</p>
                    </div>
                    <div class="p-6">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                                <div>
                                    <label for="banner_title" class="block text-sm font-medium text-gray-700 mb-1">Banner Title</label>
                                    <input type="text" id="banner_title" name="banner_title" 
                                        value="<?php echo htmlspecialchars($bannerData['title']); ?>" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label for="banner_breadcrumb" class="block text-sm font-medium text-gray-700 mb-1">Breadcrumb Text</label>
                                    <input type="text" id="banner_breadcrumb" name="banner_breadcrumb" 
                                        value="<?php echo htmlspecialchars($bannerData['breadcrumb_text']); ?>" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Banner Background Image</label>
                                
                                <div class="flex items-start space-x-4">
                                    <div class="w-1/3">
                                        <?php $imagePath = $bannerData['banner_image']; ?>
                                        <div class="mb-2 bg-gray-100 p-4 rounded-lg text-center">
                                            <?php if (!empty($imagePath)): ?>
                                            <img src="../../../<?php echo htmlspecialchars($imagePath); ?>" alt="Banner image" class="max-h-32 inline-block">
                                            <?php else: ?>
                                            <div class="text-gray-400 py-4">No image set</div>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-xs text-gray-500 text-center">Current Banner Image</p>
                                    </div>
                                    
                                    <div class="w-2/3">
                                        <div class="mb-3">
                                            <label for="banner_image_file" class="block text-sm font-medium text-gray-700 mb-1">Upload New Image</label>
                                            <input type="file" id="banner_image_file" name="banner_image_file" 
                                                class="w-full block text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                            <p class="mt-1 text-xs text-gray-500">Recommended size: 1200×300px. Accepted formats: JPG, PNG, GIF, SVG, WEBP.</p>
                                        </div>
                                        
                                        <div>
                                            <label for="banner_image" class="block text-sm font-medium text-gray-700 mb-1">Or Specify Image Path</label>
                                            <input type="text" id="banner_image" name="banner_image" 
                                                value="<?php echo htmlspecialchars($imagePath); ?>"
                                                placeholder="assets/images/example.png"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            <p class="mt-1 text-xs text-gray-500">Path relative to website root. This will be used if no file is uploaded.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-6 flex justify-end">
                                <button type="submit" name="update_banner" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <i class="bx bx-save mr-2"></i> Save Banner Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Team Members Section -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Team Members</h2>
                        <p class="text-sm text-gray-500 mt-1">Manage team members displayed on the team page</p>
                    </div>
                    <div class="p-6">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <!-- Current Team Members -->
                            <div class="space-y-6">
                                <?php if(empty($teamMembers)): ?>
                                <div class="text-center text-sm text-gray-500 p-6 bg-gray-50 rounded-lg">
                                    No team members found. Add team members below.
                                </div>
                                <?php else: ?>
                                    <?php foreach($teamMembers as $index => $member): ?>
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <div class="flex items-center justify-between mb-4">
                                            <h3 class="font-medium text-gray-800">Team Member #<?php echo $index + 1; ?></h3>
                                            <div class="flex items-center">
                                                <span class="mr-2 text-sm text-gray-600">Active</span>
                                                <input type="hidden" name="member_ids[]" value="<?php echo $member['id']; ?>">
                                                <input type="checkbox" name="member_actives[]" value="<?php echo $member['id']; ?>" 
                                                       <?php echo $member['is_active'] ? 'checked' : ''; ?> 
                                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            </div>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                            <div class="md:col-span-1">
                                                <div class="bg-gray-100 p-4 rounded-lg text-center mb-2">
                                                    <?php if (!empty($member['image_path'])): ?>
                                                    <img src="../../../<?php echo htmlspecialchars($member['image_path']); ?>" alt="Team Member" class="h-32 w-32 object-cover rounded-full inline-block">
                                                    <input type="hidden" name="member_image_paths[]" value="<?php echo htmlspecialchars($member['image_path']); ?>">
                                                    <?php else: ?>
                                                    <div class="h-32 w-32 rounded-full bg-gray-300 inline-flex items-center justify-center">
                                                        <i class="bx bx-user text-gray-400 text-3xl"></i>
                                                    </div>
                                                    <input type="hidden" name="member_image_paths[]" value="">
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <input type="file" name="member_images[<?php echo $index; ?>]" 
                                                       class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                                <p class="mt-1 text-xs text-gray-500 text-center">Upload new image (square, min 200x200px)</p>
                                            </div>
                                            
                                            <div class="md:col-span-2">
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                                        <input type="text" name="member_names[]" value="<?php echo htmlspecialchars($member['name']); ?>" 
                                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                                                        <input type="text" name="member_positions[]" value="<?php echo htmlspecialchars($member['position']); ?>" 
                                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    </div>
                                                </div>
                                                
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Display Order</label>
                                                    <input type="number" name="member_orders[]" value="<?php echo (int)$member['display_order']; ?>" min="1" 
                                                           class="w-24 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                </div>
                                                
                                                <div class="mt-4">
                                                    <form method="POST" action="" class="inline">
                                                        <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                                        <button type="submit" name="delete_member" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" onclick="return confirm('Are you sure you want to delete this team member?');">
                                                            <i class="bx bx-trash mr-1.5"></i> Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Add New Team Member -->
                            <div class="mt-8 p-4 border border-gray-200 rounded-lg bg-gray-50">
                                <h3 class="text-base font-medium text-gray-900 mb-4">Add New Team Member</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label for="new_member_name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                        <input type="text" id="new_member_name" name="new_member_name" placeholder="e.g. John Doe" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label for="new_member_position" class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                                        <input type="text" id="new_member_position" name="new_member_position" placeholder="e.g. CEO" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label for="new_member_order" class="block text-sm font-medium text-gray-700 mb-1">Display Order</label>
                                        <input type="number" id="new_member_order" name="new_member_order" value="<?php echo count($teamMembers) + 1; ?>" min="1" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <label for="new_member_image" class="block text-sm font-medium text-gray-700 mb-1">Member Image</label>
                                    <input type="file" id="new_member_image" name="new_member_image" 
                                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                    <p class="mt-1 text-xs text-gray-500">Upload profile image (square, min 200x200px recommended)</p>
                                </div>
                            </div>
                            
                            <div class="mt-6 flex justify-end">
                                <button type="submit" name="update_members" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <i class="bx bx-save mr-2"></i> Save Team Members
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Preview Section -->
                <div class="mt-8 bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-gray-800">Page Preview</h2>
                        <a href="/team" target="_blank" class="text-blue-600 hover:text-blue-800 flex items-center">
                            <i class="bx bx-link-external mr-1"></i> View Full Page
                        </a>
                    </div>
                    <div class="p-6">
                        <div class="bg-gray-100 p-4 rounded-lg mb-4">
                            <h3 class="text-base font-medium text-gray-800 mb-2">Banner Section</h3>
                            <div class="bg-white p-4 rounded border border-gray-200">
                                <div class="text-center">
                                    <h4 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($bannerData['title']); ?></h4>
                                    <div class="text-sm text-gray-600 mt-1">
                                        <span>Home</span> &gt; <span>Tentang</span> &gt; <span><?php echo htmlspecialchars($bannerData['breadcrumb_text']); ?></span>
                                    </div>
                                </div>
                                <div class="mt-2 text-center text-xs text-gray-500">
                                    Banner Background: <?php echo htmlspecialchars($bannerData['banner_image']); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-100 p-4 rounded-lg">
                            <h3 class="text-base font-medium text-gray-800 mb-2">Team Members</h3>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-3">
                                <?php foreach($teamMembers as $member): ?>
                                <?php if($member['is_active']): ?>
                                <div class="bg-white p-3 rounded border border-gray-200 text-center">
                                    <?php if(!empty($member['image_path'])): ?>
                                    <div class="w-16 h-16 mx-auto bg-gray-200 rounded-full overflow-hidden mb-2">
                                        <img src="../../../<?php echo htmlspecialchars($member['image_path']); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>" class="w-full h-full object-cover">
                                    </div>
                                    <?php else: ?>
                                    <div class="w-16 h-16 mx-auto bg-gray-200 rounded-full flex items-center justify-center mb-2">
                                        <i class="bx bx-user text-gray-400 text-xl"></i>
                                    </div>
                                    <?php endif; ?>
                                    <h5 class="text-sm font-medium text-gray-800"><?php echo htmlspecialchars($member['name']); ?></h5>
                                    <p class="text-xs text-gray-600"><?php echo htmlspecialchars($member['position']); ?></p>
                                </div>
                                <?php endif; ?>
                                <?php endforeach; ?>
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
        // Preview uploaded images before submission
        document.addEventListener('DOMContentLoaded', function() {
            // For banner image
            const bannerImageInput = document.getElementById('banner_image_file');
            if (bannerImageInput) {
                bannerImageInput.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const previewContainer = bannerImageInput.closest('.w-2/3').previousElementSibling.querySelector('div');
                            previewContainer.innerHTML = `<img src="${e.target.result}" alt="Banner preview" class="max-h-32 inline-block">`;
                        };
                        reader.readAsDataURL(this.files[0]);
                    }
                });
            }
            
            // For member images
            const memberImageInputs = document.querySelectorAll('input[name^="member_images"]');
            memberImageInputs.forEach(input => {
                input.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const previewContainer = input.closest('.md\\:col-span-1').querySelector('.bg-gray-100');
                            previewContainer.innerHTML = `<img src="${e.target.result}" alt="Member preview" class="h-32 w-32 object-cover rounded-full inline-block">`;
                        };
                        reader.readAsDataURL(this.files[0]);
                    }
                });
            });
            
            // For new member image
            const newMemberImageInput = document.getElementById('new_member_image');
            if (newMemberImageInput) {
                newMemberImageInput.addEventListener('change', function() {
                    // No preview for new member since there's no dedicated preview area
                });
            }
        });
    </script>
</body>
</html>