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
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'banner';

// Try creating the upload directory structure at initialization
$uploadDirectory = '../../../assets/images/uploads/faq/';
if (!file_exists($uploadDirectory)) {
    // Create directory if it doesn't exist
    mkdir($uploadDirectory, 0755, true);
}

// Handle image uploads
function handleImageUpload($fileInput, $oldPath = null) {
    global $uploadDirectory;
    
    if (isset($_FILES[$fileInput]) && $_FILES[$fileInput]['error'] === UPLOAD_ERR_OK) {
        $tempFile = $_FILES[$fileInput]['tmp_name'];
        $fileInfo = pathinfo($_FILES[$fileInput]['name']);
        $extension = strtolower($fileInfo['extension']);
        
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
                    'message' => "Failed to create upload directory. Please create this directory manually: assets/images/uploads/faq"
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
        $newFilename = 'faq_' . uniqid() . '.' . $extension;
        $targetPath = $uploadDirectory . $newFilename;
        
        // Move the uploaded file
        if (@move_uploaded_file($tempFile, $targetPath)) {
            // Get the relative path for the database (from website root)
            $relativePath = 'assets/images/uploads/faq/' . $newFilename;
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
    
    // Update Banner
    if (isset($_POST['update_banner'])) {
        try {
            $title = trim($_POST['banner_title']);
            $breadcrumbText = trim($_POST['banner_breadcrumb']);
            $faqTitle = trim($_POST['faq_title']);
            $faqSubtitle = trim($_POST['faq_subtitle']);
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
            $stmt = $conn->prepare("SELECT COUNT(*) FROM faq_banners WHERE page_slug = :slug");
            $pageSlug = 'faq';
            $stmt->bindParam(':slug', $pageSlug);
            $stmt->execute();
            
            if ($stmt->fetchColumn() > 0) {
                // Update existing banner
                $stmt = $conn->prepare("UPDATE faq_banners 
                                     SET title = :title, 
                                         breadcrumb_text = :breadcrumb_text,
                                         banner_image = :banner_image,
                                         faq_title = :faq_title,
                                         faq_subtitle = :faq_subtitle
                                     WHERE page_slug = :slug");
            } else {
                // Insert new banner
                $stmt = $conn->prepare("INSERT INTO faq_banners 
                                     (page_slug, title, breadcrumb_text, banner_image, faq_title, faq_subtitle) 
                                     VALUES (:slug, :title, :breadcrumb_text, :banner_image, :faq_title, :faq_subtitle)");
            }
            
            $stmt->bindParam(':slug', $pageSlug);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':breadcrumb_text', $breadcrumbText);
            $stmt->bindParam(':banner_image', $imagePath);
            $stmt->bindParam(':faq_title', $faqTitle);
            $stmt->bindParam(':faq_subtitle', $faqSubtitle);
            $stmt->execute();
            
            $message = "Banner updated successfully!";
            $messageType = "success";
        } catch (Exception $e) {
            $message = "Error updating banner: " . $e->getMessage();
            $messageType = "error";
        }
    }
    
    // Update FAQ Items - Left Column
    if (isset($_POST['update_left_faqs'])) {
        try {
            $conn->beginTransaction();
            
            // Process each FAQ item
            if (isset($_POST['left_faq_ids']) && is_array($_POST['left_faq_ids'])) {
                $faqIds = $_POST['left_faq_ids'];
                $faqQuestions = $_POST['left_faq_questions'];
                $faqAnswers = $_POST['left_faq_answers'];
                $faqOrders = $_POST['left_faq_orders'];
                $faqActives = isset($_POST['left_faq_actives']) ? $_POST['left_faq_actives'] : [];
                
                // Process each FAQ
                for ($i = 0; $i < count($faqIds); $i++) {
                    $id = (int)$faqIds[$i];
                    $question = trim($faqQuestions[$i]);
                    $answer = trim($faqAnswers[$i]);
                    $order = (int)$faqOrders[$i];
                    $isActive = in_array($id, $faqActives) ? 1 : 0;
                    
                    // Update the FAQ item
                    $stmt = $conn->prepare("UPDATE faq_items
                                       SET question = :question, 
                                           answer = :answer, 
                                           display_order = :order, 
                                           is_active = :isActive
                                       WHERE id = :id");
                    
                    $stmt->bindParam(':question', $question);
                    $stmt->bindParam(':answer', $answer);
                    $stmt->bindParam(':order', $order);
                    $stmt->bindParam(':isActive', $isActive);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }
            }
            
            // Add new FAQ if provided
            if (!empty($_POST['new_left_question']) && !empty($_POST['new_left_answer'])) {
                $newQuestion = trim($_POST['new_left_question']);
                $newAnswer = trim($_POST['new_left_answer']);
                $newOrder = (int)$_POST['new_left_order'];
                
                // Insert new FAQ item
                $stmt = $conn->prepare("INSERT INTO faq_items
                                   (question, answer, display_order, is_active, column_position)
                                   VALUES (:question, :answer, :order, 1, 1)");
                
                $stmt->bindParam(':question', $newQuestion);
                $stmt->bindParam(':answer', $newAnswer);
                $stmt->bindParam(':order', $newOrder);
                $stmt->execute();
            }
            
            $conn->commit();
            $message = "Left column FAQ items updated successfully!";
            $messageType = "success";
            $activeTab = 'left_faqs';
        } catch (Exception $e) {
            $conn->rollBack();
            $message = "Error updating FAQ items: " . $e->getMessage();
            $messageType = "error";
        }
    }
    
    // Update FAQ Items - Right Column
    if (isset($_POST['update_right_faqs'])) {
        try {
            $conn->beginTransaction();
            
            // Process each FAQ item
            if (isset($_POST['right_faq_ids']) && is_array($_POST['right_faq_ids'])) {
                $faqIds = $_POST['right_faq_ids'];
                $faqQuestions = $_POST['right_faq_questions'];
                $faqAnswers = $_POST['right_faq_answers'];
                $faqOrders = $_POST['right_faq_orders'];
                $faqActives = isset($_POST['right_faq_actives']) ? $_POST['right_faq_actives'] : [];
                
                // Process each FAQ
                for ($i = 0; $i < count($faqIds); $i++) {
                    $id = (int)$faqIds[$i];
                    $question = trim($faqQuestions[$i]);
                    $answer = trim($faqAnswers[$i]);
                    $order = (int)$faqOrders[$i];
                    $isActive = in_array($id, $faqActives) ? 1 : 0;
                    
                    // Update the FAQ item
                    $stmt = $conn->prepare("UPDATE faq_items
                                       SET question = :question, 
                                           answer = :answer, 
                                           display_order = :order, 
                                           is_active = :isActive
                                       WHERE id = :id");
                    
                    $stmt->bindParam(':question', $question);
                    $stmt->bindParam(':answer', $answer);
                    $stmt->bindParam(':order', $order);
                    $stmt->bindParam(':isActive', $isActive);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }
            }
            
            // Add new FAQ if provided
            if (!empty($_POST['new_right_question']) && !empty($_POST['new_right_answer'])) {
                $newQuestion = trim($_POST['new_right_question']);
                $newAnswer = trim($_POST['new_right_answer']);
                $newOrder = (int)$_POST['new_right_order'];
                
                // Insert new FAQ item
                $stmt = $conn->prepare("INSERT INTO faq_items
                                   (question, answer, display_order, is_active, column_position)
                                   VALUES (:question, :answer, :order, 1, 2)");
                
                $stmt->bindParam(':question', $newQuestion);
                $stmt->bindParam(':answer', $newAnswer);
                $stmt->bindParam(':order', $newOrder);
                $stmt->execute();
            }
            
            $conn->commit();
            $message = "Right column FAQ items updated successfully!";
            $messageType = "success";
            $activeTab = 'right_faqs';
        } catch (Exception $e) {
            $conn->rollBack();
            $message = "Error updating FAQ items: " . $e->getMessage();
            $messageType = "error";
        }
    }
    
    // Delete FAQ Item
    if (isset($_POST['delete_faq'])) {
        try {
            $id = (int)$_POST['faq_id'];
            
            // Delete the FAQ item
            $stmt = $conn->prepare("DELETE FROM faq_items WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $message = "FAQ item deleted successfully!";
            $messageType = "success";
        } catch (Exception $e) {
            $message = "Error deleting FAQ item: " . $e->getMessage();
            $messageType = "error";
        }
    }
}

// Fetch banner data
$bannerData = [];
try {
    $stmt = $conn->prepare("SELECT * FROM faq_banners WHERE page_slug = :slug LIMIT 1");
    $pageSlug = 'faq';
    $stmt->bindParam(':slug', $pageSlug);
    $stmt->execute();
    $bannerData = $stmt->fetch();
    
    // If no banner found, set default values
    if (!$bannerData) {
        $bannerData = [
            'title' => 'FAQ',
            'breadcrumb_text' => 'FAQ',
            'banner_image' => 'assets/images/shape/inner-shape.png',
            'faq_title' => 'Frequently Asked Questions',
            'faq_subtitle' => 'Beberapa pertanyaan yang sering disampaikan'
        ];
    }
} catch(PDOException $e) {
    $message = "Error fetching banner data: " . $e->getMessage();
    $messageType = "error";
}

// Fetch left column FAQ items
$leftFaqs = [];
try {
    $stmt = $conn->query("SELECT * FROM faq_items WHERE column_position = 1 ORDER BY display_order ASC");
    $leftFaqs = $stmt->fetchAll();
} catch(PDOException $e) {
    $message = "Error fetching left column FAQ items: " . $e->getMessage();
    $messageType = "error";
}

// Fetch right column FAQ items
$rightFaqs = [];
try {
    $stmt = $conn->query("SELECT * FROM faq_items WHERE column_position = 2 ORDER BY display_order ASC");
    $rightFaqs = $stmt->fetchAll();
} catch(PDOException $e) {
    $message = "Error fetching right column FAQ items: " . $e->getMessage();
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
                <h1 class="text-xl font-semibold text-gray-800">Edit FAQ Page</h1>
                <div class="flex items-center space-x-4">
                    <a href="/faq" target="_blank" class="text-blue-600 hover:text-blue-800 flex items-center">
                        <i class="bx bx-link-external mr-1"></i> View FAQ Page
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
                        <a href="?tab=banner" class="mr-8 py-4 px-1 border-b-2 font-medium text-sm <?php echo $activeTab == 'banner' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>">
                            Banner Section
                        </a>
                        <a href="?tab=left_faqs" class="mr-8 py-4 px-1 border-b-2 font-medium text-sm <?php echo $activeTab == 'left_faqs' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>">
                            Left Column FAQs
                        </a>
                        <a href="?tab=right_faqs" class="mr-8 py-4 px-1 border-b-2 font-medium text-sm <?php echo $activeTab == 'right_faqs' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>">
                            Right Column FAQs
                        </a>
                    </nav>
                </div>
                
                <!-- Banner Section Tab -->
                <?php if ($activeTab == 'banner'): ?>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Banner Section</h2>
                        <p class="text-sm text-gray-500 mt-1">Edit the banner section at the top of the FAQ page</p>
                    </div>
                    <div class="p-6">
                        <form method="POST" action="?tab=banner" enctype="multipart/form-data">
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
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                                <div>
                                    <label for="faq_title" class="block text-sm font-medium text-gray-700 mb-1">FAQ Section Title</label>
                                    <input type="text" id="faq_title" name="faq_title" 
                                        value="<?php echo htmlspecialchars($bannerData['faq_title']); ?>" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label for="faq_subtitle" class="block text-sm font-medium text-gray-700 mb-1">FAQ Section Subtitle</label>
                                    <input type="text" id="faq_subtitle" name="faq_subtitle" 
                                        value="<?php echo htmlspecialchars($bannerData['faq_subtitle']); ?>" 
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
                <?php endif; ?>
                
                <!-- Left Column FAQs Tab -->
                <?php if ($activeTab == 'left_faqs'): ?>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Left Column FAQs</h2>
                        <p class="text-sm text-gray-500 mt-1">Manage FAQ items displayed in the left column</p>
                    </div>
                    <div class="p-6">
                        <form method="POST" action="?tab=left_faqs">
                            <div class="space-y-6">
                                <?php if(empty($leftFaqs)): ?>
                                <div class="text-center text-sm text-gray-500 p-6 bg-gray-50 rounded-lg">
                                    No FAQ items found in the left column. Add FAQs below.
                                </div>
                                <?php else: ?>
                                    <?php foreach($leftFaqs as $index => $faq): ?>
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <div class="flex items-center justify-between mb-4">
                                            <h3 class="font-medium text-gray-800">FAQ Item #<?php echo $index + 1; ?></h3>
                                            <div class="flex items-center">
                                                <span class="mr-2 text-sm text-gray-600">Active</span>
                                                <input type="hidden" name="left_faq_ids[]" value="<?php echo $faq['id']; ?>">
                                                <input type="checkbox" name="left_faq_actives[]" value="<?php echo $faq['id']; ?>" 
                                                       <?php echo $faq['is_active'] ? 'checked' : ''; ?> 
                                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            </div>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Question</label>
                                            <input type="text" name="left_faq_questions[]" value="<?php echo htmlspecialchars($faq['question']); ?>" 
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        
                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Answer</label>
                                            <textarea name="left_faq_answers[]" rows="3" 
                                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($faq['answer']); ?></textarea>
                                        </div>
                                        
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Display Order</label>
                                                <input type="number" name="left_faq_orders[]" value="<?php echo (int)$faq['display_order']; ?>" min="1" 
                                                       class="w-24 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                            
                                            <div>
                                                <a href="#" onclick="deleteFaqItem(<?php echo $faq['id']; ?>); return false;" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                    <i class="bx bx-trash mr-1.5"></i> Delete
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Add New FAQ Item -->
                            <div class="mt-8 p-4 border border-gray-200 rounded-lg bg-gray-50">
                                <h3 class="text-base font-medium text-gray-900 mb-4">Add New FAQ Item</h3>
                                
                                <div class="mb-4">
                                    <label for="new_left_question" class="block text-sm font-medium text-gray-700 mb-1">Question</label>
                                    <input type="text" id="new_left_question" name="new_left_question" placeholder="Enter the question here" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                
                                <div class="mb-4">
                                    <label for="new_left_answer" class="block text-sm font-medium text-gray-700 mb-1">Answer</label>
                                    <textarea id="new_left_answer" name="new_left_answer" rows="3" placeholder="Enter the answer here" 
                                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                                </div>
                                
                                <div>
                                    <label for="new_left_order" class="block text-sm font-medium text-gray-700 mb-1">Display Order</label>
                                    <input type="number" id="new_left_order" name="new_left_order" value="<?php echo count($leftFaqs) + 1; ?>" min="1" 
                                           class="w-24 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            
                            <div class="mt-6 flex justify-end">
                                <button type="submit" name="update_left_faqs" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <i class="bx bx-save mr-2"></i> Save Left Column FAQs
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Right Column FAQs Tab -->
                <?php if ($activeTab == 'right_faqs'): ?>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Right Column FAQs</h2>
                        <p class="text-sm text-gray-500 mt-1">Manage FAQ items displayed in the right column</p>
                    </div>
                    <div class="p-6">
                        <form method="POST" action="?tab=right_faqs">
                            <div class="space-y-6">
                                <?php if(empty($rightFaqs)): ?>
                                <div class="text-center text-sm text-gray-500 p-6 bg-gray-50 rounded-lg">
                                    No FAQ items found in the right column. Add FAQs below.
                                </div>
                                <?php else: ?>
                                    <?php foreach($rightFaqs as $index => $faq): ?>
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <div class="flex items-center justify-between mb-4">
                                            <h3 class="font-medium text-gray-800">FAQ Item #<?php echo $index + 1; ?></h3>
                                            <div class="flex items-center">
                                                <span class="mr-2 text-sm text-gray-600">Active</span>
                                                <input type="hidden" name="right_faq_ids[]" value="<?php echo $faq['id']; ?>">
                                                <input type="checkbox" name="right_faq_actives[]" value="<?php echo $faq['id']; ?>" 
                                                       <?php echo $faq['is_active'] ? 'checked' : ''; ?> 
                                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            </div>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Question</label>
                                            <input type="text" name="right_faq_questions[]" value="<?php echo htmlspecialchars($faq['question']); ?>" 
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        
                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Answer</label>
                                            <textarea name="right_faq_answers[]" rows="3" 
                                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($faq['answer']); ?></textarea>
                                        </div>
                                        
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Display Order</label>
                                                <input type="number" name="right_faq_orders[]" value="<?php echo (int)$faq['display_order']; ?>" min="1" 
                                                       class="w-24 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                            
                                            <div>
                                                <a href="#" onclick="deleteFaqItem(<?php echo $faq['id']; ?>); return false;" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                    <i class="bx bx-trash mr-1.5"></i> Delete
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Add New FAQ Item -->
                            <div class="mt-8 p-4 border border-gray-200 rounded-lg bg-gray-50">
                                <h3 class="text-base font-medium text-gray-900 mb-4">Add New FAQ Item</h3>
                                
                                <div class="mb-4">
                                    <label for="new_right_question" class="block text-sm font-medium text-gray-700 mb-1">Question</label>
                                    <input type="text" id="new_right_question" name="new_right_question" placeholder="Enter the question here" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                
                                <div class="mb-4">
                                    <label for="new_right_answer" class="block text-sm font-medium text-gray-700 mb-1">Answer</label>
                                    <textarea id="new_right_answer" name="new_right_answer" rows="3" placeholder="Enter the answer here" 
                                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                                </div>
                                
                                <div>
                                    <label for="new_right_order" class="block text-sm font-medium text-gray-700 mb-1">Display Order</label>
                                    <input type="number" id="new_right_order" name="new_right_order" value="<?php echo count($rightFaqs) + 1; ?>" min="1" 
                                           class="w-24 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            
                            <div class="mt-6 flex justify-end">
                                <button type="submit" name="update_right_faqs" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <i class="bx bx-save mr-2"></i> Save Right Column FAQs
                                </button>
                            </div>
                        </form>
                        
                        <!-- Separate form for deleting FAQ items -->
                        <form id="delete-form" method="POST" action="" style="display: none;">
                            <input type="hidden" id="faq_id" name="faq_id" value="">
                            <input type="hidden" name="delete_faq" value="1">
                        </form>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Preview Section -->
                <div class="mt-8 bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-gray-800">Page Preview</h2>
                        <a href="/faq" target="_blank" class="text-blue-600 hover:text-blue-800 flex items-center">
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
                        
                        <div class="bg-gray-100 p-4 rounded-lg mb-4">
                            <h3 class="text-base font-medium text-gray-800 mb-2">FAQ Section Title</h3>
                            <div class="bg-white p-4 rounded border border-gray-200 text-center">
                                <h4 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($bannerData['faq_title']); ?></h4>
                                <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($bannerData['faq_subtitle']); ?></p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-gray-100 p-4 rounded-lg">
                                <h3 class="text-base font-medium text-gray-800 mb-2">Left Column FAQs</h3>
                                <div class="bg-white rounded border border-gray-200">
                                    <?php if (empty($leftFaqs)): ?>
                                    <div class="p-4 text-center text-sm text-gray-500">
                                        No FAQ items in left column
                                    </div>
                                    <?php else: ?>
                                        <?php foreach ($leftFaqs as $index => $faq): ?>
                                            <?php if ($faq['is_active']): ?>
                                            <div class="border-b border-gray-200 last:border-b-0">
                                                <div class="p-3 flex justify-between items-center cursor-pointer bg-gray-50">
                                                    <span class="font-medium text-sm"><?php echo htmlspecialchars($faq['question']); ?></span>
                                                    <i class='bx bx-plus text-gray-500'></i>
                                                </div>
                                                <div class="p-3 text-sm text-gray-600 hidden">
                                                    <?php echo htmlspecialchars($faq['answer']); ?>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="bg-gray-100 p-4 rounded-lg">
                                <h3 class="text-base font-medium text-gray-800 mb-2">Right Column FAQs</h3>
                                <div class="bg-white rounded border border-gray-200">
                                    <?php if (empty($rightFaqs)): ?>
                                    <div class="p-4 text-center text-sm text-gray-500">
                                        No FAQ items in right column
                                    </div>
                                    <?php else: ?>
                                        <?php foreach ($rightFaqs as $index => $faq): ?>
                                            <?php if ($faq['is_active']): ?>
                                            <div class="border-b border-gray-200 last:border-b-0">
                                                <div class="p-3 flex justify-between items-center cursor-pointer bg-gray-50">
                                                    <span class="font-medium text-sm"><?php echo htmlspecialchars($faq['question']); ?></span>
                                                    <i class='bx bx-plus text-gray-500'></i>
                                                </div>
                                                <div class="p-3 text-sm text-gray-600 hidden">
                                                    <?php echo htmlspecialchars($faq['answer']); ?>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
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
        // Function to handle FAQ item deletion
        function deleteFaqItem(faqId) {
            if (confirm('Are you sure you want to delete this FAQ item?')) {
                document.getElementById('faq_id').value = faqId;
                document.getElementById('delete-form').submit();
            }
        }
        
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
            
            // Simple preview functionality for FAQ accordion
            const faqHeaders = document.querySelectorAll('.cursor-pointer');
            faqHeaders.forEach(header => {
                header.addEventListener('click', function() {
                    const content = this.nextElementSibling;
                    content.classList.toggle('hidden');
                    
                    const icon = this.querySelector('i');
                    if (icon.classList.contains('bx-plus')) {
                        icon.classList.replace('bx-plus', 'bx-minus');
                    } else {
                        icon.classList.replace('bx-minus', 'bx-plus');
                    }
                });
            });
        });
    </script>
</body>
</html>