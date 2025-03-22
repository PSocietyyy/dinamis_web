<?php
// service_article.php
    // Start the session
    session_start();
// Function to process HTML content from Quill editor
function processArticleContent($content) {
    // Load the HTML into a DOMDocument for proper processing
    $dom = new DOMDocument();
    
    // Suppress warnings from malformed HTML
    libxml_use_internal_errors(true);
    
    // Add a proper HTML structure with charset to handle special characters correctly
    $dom->loadHTML('<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>' . $content . '</body></html>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    
    // Clear the errors
    libxml_clear_errors();
    
    // Find all ul elements
    $ulElements = $dom->getElementsByTagName('ul');
    $uls = [];
    
    // We need to collect them first because the NodeList is live and changes during modification
    foreach ($ulElements as $ul) {
        $uls[] = $ul;
    }
    
    // Process each ul element
    foreach ($uls as $ul) {
        // Add the custom classes
        $ul->setAttribute('class', 'service-article-list service-article-rs');
        
        // Process each li element inside this ul
        $liElements = $ul->getElementsByTagName('li');
        $lis = [];
        
        // Collect li elements first
        foreach ($liElements as $li) {
            $lis[] = $li;
        }
        
        // Process each li element
        foreach ($lis as $li) {
            // Create the icon element
            $icon = $dom->createElement('i');
            $icon->setAttribute('class', 'bx bxs-check-circle');
            
            // Insert the icon at the beginning of the li
            if ($li->hasChildNodes()) {
                $li->insertBefore($icon, $li->firstChild);
            } else {
                $li->appendChild($icon);
            }
        }
    }
    
    // Get the body content only
    $body = $dom->getElementsByTagName('body')->item(0);
    $html = '';
    foreach ($body->childNodes as $node) {
        $html .= $dom->saveHTML($node);
    }
    
    return $html;
}

// Function to preserve list structure when loading into Quill
function prepareContentForQuill($content) {
    // Add a div between adjacent lists to prevent Quill from merging them
    $content = preg_replace('/<\/ul>\s*<ul/is', '</ul><div class="list-separator">&nbsp;</div><ul', $content);
    return $content;
}

function generateUniqueSlug($conn, $title, $id = null) {
    // Convert title to lowercase
    $slug = strtolower($title);

    // Replace spaces and slashes with underscores
    $slug = str_replace([' ', '/'], '_', $slug);

    // Remove special characters, allow only a-z, 0-9, _, and -
    $slug = preg_replace('/[^a-z0-9_-]+/', '_', $slug);
    
    // Trim underscores di awal & akhir
    $slug = trim($slug, '_');

    // Make sure slug is not empty
    if (empty($slug)) {
        $slug = 'article_' . time();
    }
    
    // Check if slug already exists in database
    $originalSlug = $slug;
    $count = 1;
    $uniqueSlug = $slug;
    
    do {
        $exists = false;
        
        // SQL to check if slug exists (excluding current article if it's an update)
        $sql = "SELECT COUNT(*) FROM service_articles WHERE slug = :slug";
        $params = [':slug' => $uniqueSlug];
        
        if ($id) {
            $sql .= " AND id != :id";
            $params[':id'] = $id;
        }
        
        $stmt = $conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        if ($stmt->fetchColumn() > 0) {
            $exists = true;
            $uniqueSlug = $originalSlug . '_' . $count;
            $count++;
        }
    } while ($exists);
    
    return $uniqueSlug;
}


// Function to handle image uploads
function handleImageUpload($file) {
    // Define upload directory
    $uploadDir = '../../../assets/images/uploads/articles/';
    
    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        if (!@mkdir($uploadDir, 0777, true)) {
            // Try to create parent directories if needed
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
            
            if (!file_exists($rootDir . '/assets/images/uploads/articles')) {
                @mkdir($rootDir . '/assets/images/uploads/articles', 0777);
            }
        }
    }
    
    // Check if directory is writable
    if (!is_writable($uploadDir)) {
        @chmod($uploadDir, 0777);
        if (!is_writable($uploadDir)) {
            return [
                'success' => false,
                'message' => "Upload directory is not writable. Please check permissions for: " . $uploadDir
            ];
        }
    }
    
    // Check if a file was uploaded
    if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
        $tempFile = $file['tmp_name'];
        $fileInfo = pathinfo($file['name']);
        $extension = strtolower($fileInfo['extension']);
        
        // Validate file type
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
        if (!in_array($extension, $allowedExtensions)) {
            return [
                'success' => false,
                'message' => "Invalid file type. Only JPG, PNG, GIF, SVG, and WEBP are allowed."
            ];
        }
        
        // Generate a unique filename
        $newFilename = 'article_' . uniqid() . '.' . $extension;
        $targetPath = $uploadDir . $newFilename;
        
        // Move the uploaded file
        if (@move_uploaded_file($tempFile, $targetPath)) {
            // Return the relative path for the database
            $relativePath = 'assets/images/uploads/articles/' . $newFilename;
            return [
                'success' => true,
                'path' => $relativePath
            ];
        } else {
            $error = error_get_last();
            return [
                'success' => false,
                'message' => "Failed to move uploaded file. Error: " . ($error ? $error['message'] : 'Unknown error')
            ];
        }
    }
    
    return [
        'success' => false,
        'message' => "No file uploaded or file upload error."
    ];
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Article Management</title>
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom styles for our article lists */
        .service-article-list {
            padding-left: 0;
            margin-bottom: 1.5rem;
        }
        
        .service-article-list li {
            display: flex;
            align-items: flex-start;
            margin-bottom: 0.75rem;
            line-height: 1.5;
        }
        
        .service-article-list li i {
            color: #4CAF50; /* green color for check icons */
            margin-right: 0.75rem;
            flex-shrink: 0;
            font-size: 1.25rem;
        }
        
        .service-article-rs li {
            padding-left: 0.5rem;
        }
        
        /* For Quill editor toolbar */
        .ql-editor {
            min-height: 200px;
        }
        
        /* Style for list separators */
        .list-separator {
            height: 12px;
            opacity: 0;
        }
        
        /* Modal styles */
        .modal {
            transition: opacity 0.25s ease;
        }
        
        body.modal-active {
            overflow-x: hidden;
            overflow-y: visible !important;
        }
        
        /* Preview modal scrollable content */
        .modal-content {
            max-height: calc(100vh - 200px);
            overflow-y: auto;
        }
        
        /* Image preview */
        .image-preview {
            max-height: 200px;
            width: auto;
            object-fit: contain;
        }

        /* Slug display */
        .slug-preview {
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.5rem;
            font-family: monospace;
            margin-top: 0.5rem;
            word-break: break-all;
        }
    </style>
</head>
<body class="bg-gray-50 p-6">
    <?php

    // Include database connection
    require_once('../../../config.php');

    // Check if slug field exists in the database, if not, add it
    try {
        // Check if slug column exists
        $stmt = $conn->prepare("SHOW COLUMNS FROM service_articles LIKE 'slug'");
        $stmt->execute();
        
        if ($stmt->rowCount() == 0) {
            // Add slug column if it doesn't exist
            $conn->exec("ALTER TABLE service_articles ADD COLUMN slug VARCHAR(255) AFTER title");
            $conn->exec("CREATE UNIQUE INDEX idx_service_articles_slug ON service_articles(slug)");
            
            // Generate slugs for existing articles
            $stmt = $conn->query("SELECT id, title FROM service_articles WHERE slug IS NULL OR slug = ''");
            $articles = $stmt->fetchAll();
            
            foreach ($articles as $article) {
                $slug = generateUniqueSlug($conn, $article['title'], $article['id']);
                $updateStmt = $conn->prepare("UPDATE service_articles SET slug = :slug WHERE id = :id");
                $updateStmt->bindParam(':slug', $slug);
                $updateStmt->bindParam(':id', $article['id']);
                $updateStmt->execute();
            }
        }
    } catch(PDOException $e) {
        // Just log the error, don't stop execution
        error_log("Error updating database schema: " . $e->getMessage());
    }

    // Process form submission for adding an article
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_article'])) {
        try {
            $title = trim($_POST['article_title']);
            $feature_id = $_POST['feature_id'] ? $_POST['feature_id'] : null;
            $content = $_POST['article_content'];
            
            // Process the content to add custom styling to lists
            $content = processArticleContent($content);
            
            // Generate unique slug
            $slug = generateUniqueSlug($conn, $title);
            
            // Handle image upload
            $imagePath = null;
            if (!empty($_FILES['article_image']['name'])) {
                $uploadResult = handleImageUpload($_FILES['article_image']);
                if (!$uploadResult['success']) {
                    throw new Exception($uploadResult['message']);
                }
                $imagePath = $uploadResult['path'];
            }
            
            // SQL query with optional image field
            $sql = "INSERT INTO service_articles (title, feature_id, slug, content";
            if ($imagePath) {
                $sql .= ", image_path";
            }
            $sql .= ") VALUES (:title, :feature_id, :slug, :content";
            if ($imagePath) {
                $sql .= ", :image_path";
            }
            $sql .= ")";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':feature_id', $feature_id);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':content', $content);
            
            if ($imagePath) {
                $stmt->bindParam(':image_path', $imagePath);
            }
            
            $stmt->execute();
            
            $message = "Article added successfully!";
            $messageType = "success";
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = "error";
        }
    }

    // Process form submission for editing an article
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_article'])) {
        try {
            $id = (int)$_POST['article_id'];
            $title = trim($_POST['article_title']);
            $content = $_POST['article_content'];
            $oldImagePath = $_POST['old_image_path'];
            
            // Process the content to add custom styling to lists
            $content = processArticleContent($content);
            
            // Generate unique slug for updated title (exclude current article ID)
            $slug = generateUniqueSlug($conn, $title, $id);
            
            // Handle image upload
            $imagePath = $oldImagePath;
            if (!empty($_FILES['article_image']['name'])) {
                $uploadResult = handleImageUpload($_FILES['article_image']);
                if (!$uploadResult['success']) {
                    throw new Exception($uploadResult['message']);
                }
                $imagePath = $uploadResult['path'];
            }
            
            $stmt = $conn->prepare("UPDATE service_articles 
                                  SET title = :title, 
                                      slug = :slug,
                                      content = :content, 
                                      image_path = :image_path,
                                      updated_at = CURRENT_TIMESTAMP 
                                  WHERE id = :id");
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':image_path', $imagePath);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $message = "Article updated successfully!";
            $messageType = "success";
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = "error";
        }
    }
    
    // Process article deletion
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_article'])) {
        try {
            $id = (int)$_POST['article_id'];
            
            $stmt = $conn->prepare("DELETE FROM service_articles WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $message = "Article deleted successfully!";
            $messageType = "success";
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = "error";
        }
    }

    // Fetch specific article for editing if id is provided
    $editArticle = null;
    if (isset($_GET['edit']) && !empty($_GET['edit'])) {
        $editId = (int)$_GET['edit'];
        
        try {
            $stmt = $conn->prepare("SELECT * FROM service_articles WHERE id = :id");
            $stmt->bindParam(':id', $editId);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $editArticle = $stmt->fetch();
                // Prepare content for Quill to preserve list separation
                if ($editArticle['content']) {
                    $editArticle['content'] = prepareContentForQuill($editArticle['content']);
                }
            }
        } catch(PDOException $e) {
            $message = "Error fetching article: " . $e->getMessage();
            $messageType = "error";
        }
    }
    ?>

    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-center text-center">
                <a href="service.php?tab=articles" class="text-xl font-semibold text-blue-600 hover:text-blue-800 hover:underline">Back</a>
                <h1 class="text-2xl font-bold w-full">Article Editor</h1>
            </div>
            <?php if(isset($message)): ?>
                <div class="mt-4 p-4 <?php echo $messageType === 'success' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'; ?> rounded">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
        </div>
        
        
            <!-- Add New Article Form -->
            <div class="p-6">
                <h2 class="text-xl font-semibold mb-4">Add New Article</h2>
                <form method="POST" action="" id="articleForm" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Title</label>
                        <input type="text" name="article_title" id="article_title" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md"
                               oninput="updateSlugPreview(this.value, 'slug_preview')">
                        <div class="mt-1 flex flex-col">
                            <span class="text-sm text-gray-500">Generated Slug:</span>
                            <span id="slug_preview" class="slug-preview">article_slug</span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Feature</label>
                        <select name="feature_id" id="feature_id" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            <option value="" selected>Select Feature</option>
                            <?php
                                $query = "SELECT id, feature_name FROM service_features";
                                $stmt = $conn->prepare($query);
                                $stmt->execute();

                                // Fetch data dan tampilkan sebagai option
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='{$row['id']}'>{$row['feature_name']}</option>";
                                }
                            ?>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Featured Image</label>
                        <input type="file" name="article_image" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <p class="mt-1 text-xs text-gray-500">Accepted formats: JPG, PNG, GIF, SVG, WEBP</p>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Content</label>
                        <div id="editor" class="h-64 border border-gray-300"></div>
                        <input type="hidden" name="article_content" id="hiddenContent">
                    </div>
                    
                    <button type="submit" name="add_article" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                        Add Article
                    </button>
                </form>
            </div>
        
        
    </div>
    
    </div>
    
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        // Initialize Quill editor with enhanced list handling configuration
        var quill = new Quill('#editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['link', 'image'],
                    ['clean']
                ]
            },
            // This setting helps prevent automatic merging of adjacent lists
            clipboard: {
                matchVisual: false
            }
        });

        // Add custom separator detection for Quill
        quill.clipboard.addMatcher('DIV.list-separator', function(node, delta) {
            // When encountering a list separator, add a blank line to the Delta
            return {
                ops: [{ insert: '\n\n' }]
            };
        });
        
        // Update hidden field before form submission
        <?php if ($editArticle): ?>
            document.getElementById('editArticleForm').onsubmit = function() {
                document.getElementById('hiddenContent').value = quill.root.innerHTML;
                return true;
            };
        <?php else: ?>
            document.getElementById('articleForm').onsubmit = function() {
                document.getElementById('hiddenContent').value = quill.root.innerHTML;
                return true;
            };
        <?php endif; ?>
        
        // Function to create a slug from title
        function createSlug(text) {
            return text.toLowerCase()
                .replace(/\s+/g, '_')      // Replace spaces with underscores
                .replace(/\//g, '_')       // Replace slashes with underscores
                .replace(/[^\w\-_]+/g, '_') // Replace special chars with underscores
                .replace(/\_\_+/g, '_')    // Replace multiple underscores with single ones
                .replace(/^_+/, '')        // Trim underscores from start
                .replace(/_+$/, '');       // Trim underscores from end
        }
        
        // Update slug preview when title changes
        function updateSlugPreview(title, previewElementId) {
            const slug = createSlug(title) || 'article_slug';
            document.getElementById(previewElementId).textContent = slug;
        }
        
        // Modal functionality
        
        // Preview Modal
        function openPreviewModal(id, title, content, imagePath, slug) {
            document.getElementById('previewTitle').textContent = title;
            document.getElementById('previewSlug').textContent = slug;
            document.getElementById('previewContent').innerHTML = content;
            
            // Handle image preview
            const imageContainer = document.getElementById('previewImageContainer');
            const imageElement = document.getElementById('previewImage');
            
            if (imagePath && imagePath !== '') {
                imageElement.src = imagePath;
                imageContainer.classList.remove('hidden');
            } else {
                imageContainer.classList.add('hidden');
            }
            
            const modal = document.getElementById('previewModal');
            modal.classList.remove('hidden');
            document.body.classList.add('modal-active');
        }
        
        // Delete Modal
        function openDeleteModal(id, title) {
            document.getElementById('deleteArticleId').value = id;
            document.getElementById('deleteArticleTitle').textContent = title;
            
            const modal = document.getElementById('deleteModal');
            modal.classList.remove('hidden');
            document.body.classList.add('modal-active');
        }
        
        // Close Modals
        const closeButtons = document.querySelectorAll('.modal-close');
        closeButtons.forEach(button => {
            button.addEventListener('click', function() {
                const modal = this.closest('.modal');
                modal.classList.add('hidden');
                document.body.classList.remove('modal-active');
            });
        });
        
        // Close modals when clicking outside
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.add('hidden');
                    document.body.classList.remove('modal-active');
                }
            });
        });
        
        // Close modals with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal').forEach(modal => {
                    modal.classList.add('hidden');
                });
                document.body.classList.remove('modal-active');
            }
        });
        
        // Show file name when selecting file
        const fileInputs = document.querySelectorAll('input[type="file"]');
        fileInputs.forEach(input => {
            input.addEventListener('change', function() {
                const fileName = this.files[0]?.name || 'No file selected';
                const fileInfo = this.nextElementSibling;
                if (fileInfo && fileInfo.tagName === 'P') {
                    fileInfo.innerHTML = this.files[0] ? 
                        `Selected file: <span class="font-semibold">${fileName}</span>` : 
                        'Accepted formats: JPG, PNG, GIF, SVG, WEBP';
                }
            });
        });

        // Initialize slug preview for new article form
        window.addEventListener('DOMContentLoaded', function() {
            const titleInput = document.getElementById('article_title');
            if (titleInput) {
                updateSlugPreview(titleInput.value, 'slug_preview');
            }
            
            const editTitleInput = document.getElementById('edit_article_title');
            if (editTitleInput) {
                updateSlugPreview(editTitleInput.value, 'edit_slug_preview');
            }
        });
    </script>
</body>
</html>