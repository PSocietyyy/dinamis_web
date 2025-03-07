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
$username = $_SESSION['username'] ?? 'Admin';
$pageId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pageData = null;
$pageContent = '';

// If no ID provided, redirect back to manage pages
if($pageId === 0) {
    header("Location: ./manage-pages.php");
    exit;
}

// Handle page update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_page'])) {
    $pageContent = $_POST['page_content'] ?? '';
    
    // Get the file path from page URL
    if ($pageData && strpos($pageData['url'], './') === 0 && strpos($pageData['url'], '.php') !== false) {
        $pageFile = str_replace('./', '../../', $pageData['url']);
        
        // Try to save content to file
        try {
            file_put_contents($pageFile, $pageContent);
            $message = "Page content updated successfully!";
            $messageType = "success";
        } catch (Exception $e) {
            $message = "Error saving page content: " . $e->getMessage();
            $messageType = "error";
        }
    } else {
        $message = "Cannot save content: Invalid page URL format.";
        $messageType = "error";
    }
}

// Get specific page data
try {
    // Get page data
    $stmt = $conn->prepare("SELECT id, title, url, position, is_active FROM navbar_items WHERE id = :id");
    $stmt->bindParam(':id', $pageId);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $pageData = $stmt->fetch();
        
        // Get page meta if exists
        try {
            $stmt = $conn->prepare("SELECT meta_title, meta_description FROM page_meta WHERE page_id = :page_id");
            $stmt->bindParam(':page_id', $pageId);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $metaData = $stmt->fetch();
                $pageData['meta_title'] = $metaData['meta_title'];
                $pageData['meta_description'] = $metaData['meta_description'];
            } else {
                $pageData['meta_title'] = $pageData['title'];
                $pageData['meta_description'] = '';
            }
        } catch(PDOException $e) {
            // If page_meta table doesn't exist yet
            $pageData['meta_title'] = $pageData['title'];
            $pageData['meta_description'] = '';
        }
        
        // If file-based page, try to get content
        if (strpos($pageData['url'], './') === 0 && strpos($pageData['url'], '.php') !== false) {
            $pageFile = str_replace('./', '../../', $pageData['url']);
            if (file_exists($pageFile)) {
                $pageContent = file_get_contents($pageFile);
            } else {
                $message = "Warning: Page file not found at {$pageFile}";
                $messageType = "error";
            }
        } else {
            $message = "This page doesn't have editable content. Only local .php pages can be edited.";
            $messageType = "error";
        }
    } else {
        $message = "Page not found.";
        $messageType = "error";
    }
} catch(PDOException $e) {
    $message = "Error fetching page data: " . $e->getMessage();
    $messageType = "error";
}
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Page: <?php echo $pageData ? htmlspecialchars($pageData['title']) : 'Not Found'; ?> - Akademi Merdeka</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Boxicons -->
    <link rel="stylesheet" href="../../assets/css/boxicons.min.css">
    <!-- CodeMirror for code editing -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/monokai.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/htmlmixed/htmlmixed.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/php/php.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/javascript/javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/css/css.min.js"></script>
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
<body class="bg-gray-50">
    <div class="min-h-screen flex flex-col lg:flex-row">
        <!-- Include sidebar -->
        <?php include('../components/sidebar.php'); ?>
        
        <!-- Main Content -->
        <div class="flex-1 lg:ml-64">
            <!-- Top Bar -->
            <div class="bg-white p-4 shadow flex justify-between items-center">
                <h1 class="text-xl font-semibold text-gray-800">
                    Edit Page: <?php echo $pageData ? htmlspecialchars($pageData['title']) : 'Not Found'; ?>
                </h1>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600">Welcome, <?php echo htmlspecialchars($username); ?></span>
                </div>
            </div>
            
            <!-- Page Content -->
            <div class="p-6">
                <?php if(!empty($message)): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo $message; ?>
                </div>
                <?php endif; ?>
                
                <?php if($pageData): ?>
                <!-- Edit Page Layout -->
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <!-- Main Edit Area (3 columns wide) -->
                    <div class="lg:col-span-3">
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                                <h2 class="text-lg font-semibold text-gray-800">Edit Page Content</h2>
                                <div class="flex space-x-2">
                                    <button id="previewButton" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                                        <i class='bx bx-show mr-1'></i> Preview
                                    </button>
                                    <button id="saveButton" form="editForm" type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                                        <i class='bx bx-save mr-1'></i> Save Changes
                                    </button>
                                </div>
                            </div>
                            
                            <div class="p-6">
                                <form id="editForm" method="POST" action="">
                                    <input type="hidden" name="update_page" value="1">
                                    <textarea id="pageEditor" name="page_content" class="w-full h-[600px]"><?php echo htmlspecialchars($pageContent); ?></textarea>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sidebar (1 column wide) -->
                    <div class="lg:col-span-1">
                        <!-- Page Properties -->
                        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                            <div class="p-4 border-b border-gray-200">
                                <h3 class="font-semibold text-gray-800">Page Properties</h3>
                            </div>
                            
                            <div class="p-4 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                    <p class="px-3 py-2 border border-gray-300 bg-gray-50 rounded-md text-gray-700">
                                        <?php echo htmlspecialchars($pageData['title']); ?>
                                    </p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">URL</label>
                                    <p class="px-3 py-2 border border-gray-300 bg-gray-50 rounded-md text-gray-700">
                                        <?php echo htmlspecialchars($pageData['url']); ?>
                                    </p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                    <p class="px-3 py-2 rounded-md <?php echo $pageData['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                        <?php echo $pageData['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </p>
                                </div>
                                
                                <div class="pt-4">
                                    <a href="manage-pages.php" class="block w-full px-4 py-2 bg-gray-200 text-center text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                                        <i class='bx bx-arrow-back mr-1'></i> Back to Pages
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- SEO Settings -->
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <div class="p-4 border-b border-gray-200">
                                <h3 class="font-semibold text-gray-800">SEO Settings</h3>
                            </div>
                            
                            <div class="p-4 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Meta Title</label>
                                    <p class="px-3 py-2 border border-gray-300 bg-gray-50 rounded-md text-gray-700">
                                        <?php echo htmlspecialchars($pageData['meta_title'] ?? ''); ?>
                                    </p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
                                    <p class="px-3 py-2 border border-gray-300 bg-gray-50 rounded-md text-gray-700 h-20 overflow-y-auto">
                                        <?php echo htmlspecialchars($pageData['meta_description'] ?? ''); ?>
                                    </p>
                                </div>
                                
                                <div class="pt-2">
                                    <a href="../manage-pages.php?id=<?php echo $pageId; ?>" class="block w-full px-4 py-2 bg-blue-100 text-center text-blue-700 rounded-md hover:bg-blue-200 transition-colors">
                                        <i class='bx bx-edit mr-1'></i> Edit Page Settings
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Help Section -->
                        <div class="bg-white rounded-lg shadow-md overflow-hidden mt-6">
                            <div class="p-4 border-b border-gray-200">
                                <h3 class="font-semibold text-gray-800">Help</h3>
                            </div>
                            
                            <div class="p-4 space-y-3 text-sm text-gray-600">
                                <p>
                                    <i class='bx bx-info-circle text-blue-500 mr-1'></i>
                                    This editor allows you to directly edit the PHP/HTML code of your page.
                                </p>
                                <p>
                                    <i class='bx bx-info-circle text-blue-500 mr-1'></i>
                                    Use caution when editing PHP code to avoid errors.
                                </p>
                                <p>
                                    <i class='bx bx-info-circle text-blue-500 mr-1'></i>
                                    Click "Save Changes" to update the page file.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Preview Modal (Hidden by default) -->
                <div id="previewModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center">
                    <div class="bg-white rounded-lg shadow-xl w-11/12 max-w-6xl max-h-[90vh] flex flex-col">
                        <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                            <h3 class="font-semibold text-gray-800">Page Preview</h3>
                            <button id="closePreview" class="text-gray-500 hover:text-gray-700">
                                <i class='bx bx-x text-2xl'></i>
                            </button>
                        </div>
                        <div class="flex-1 overflow-auto p-4">
                            <iframe id="previewFrame" class="w-full h-full min-h-[500px] border border-gray-300 rounded-md"></iframe>
                        </div>
                        <div class="p-4 border-t border-gray-200 text-right">
                            <button id="closePreviewBtn" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                                Close Preview
                            </button>
                        </div>
                    </div>
                </div>
                
                <?php else: ?>
                <!-- Page Not Found -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="text-center">
                        <i class='bx bx-error-circle text-red-500 text-5xl mb-4'></i>
                        <h2 class="text-xl font-semibold text-gray-800 mb-2">Page Not Found</h2>
                        <p class="text-gray-600 mb-6">The page you are trying to edit does not exist or has been removed.</p>
                        <a href="manage-pages.php" class="px-5 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                            Back to Pages
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Footer -->
            <div class="text-center text-gray-500 text-sm mt-6 pb-6">
                <p>&copy; 2023 Akademi Merdeka Admin Dashboard. All rights reserved.</p>
            </div>
        </div>
    </div>
    
    <?php if($pageData): ?>
    <script>
        // Initialize CodeMirror
        document.addEventListener('DOMContentLoaded', function() {
            // Create editor
            var editor = CodeMirror.fromTextArea(document.getElementById('pageEditor'), {
                mode: 'application/x-httpd-php',
                theme: 'monokai',
                lineNumbers: true,
                indentUnit: 4,
                tabSize: 4,
                indentWithTabs: false,
                lineWrapping: true,
                extraKeys: {"Tab": "indentMore", "Shift-Tab": "indentLess"}
            });
            
            // Set editor size
            editor.setSize(null, 600);
            
            // Preview button functionality
            const previewModal = document.getElementById('previewModal');
            const previewFrame = document.getElementById('previewFrame');
            
            document.getElementById('previewButton').addEventListener('click', function() {
                // Get current content
                const content = editor.getValue();
                
                // Create a blob with the content
                const blob = new Blob([content], { type: 'text/html' });
                const url = URL.createObjectURL(blob);
                
                // Set iframe source
                previewFrame.src = url;
                
                // Show modal
                previewModal.classList.remove('hidden');
            });
            
            // Close preview modal
            document.getElementById('closePreview').addEventListener('click', function() {
                previewModal.classList.add('hidden');
            });
            
            document.getElementById('closePreviewBtn').addEventListener('click', function() {
                previewModal.classList.add('hidden');
            });
            
            // Close on click outside
            previewModal.addEventListener('click', function(event) {
                if (event.target === previewModal) {
                    previewModal.classList.add('hidden');
                }
            });
            
            // Ensure form submission includes CodeMirror content
            document.getElementById('editForm').addEventListener('submit', function() {
                editor.save(); // Update the textarea with editor content
            });
        });
    </script>
    <?php endif; ?>
</body>
</html>