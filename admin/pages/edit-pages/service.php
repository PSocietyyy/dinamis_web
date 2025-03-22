<?php
// service.php

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
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'categories';

// Function to process HTML content from Quill editor for articles
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

// Function for slug generation
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

// Handle image uploads dengan path otomatis ke assets/images/services
function handleImageUpload($fileInput, $oldPath = null) {
    // Tentukan root path
    $rootPath = dirname(dirname(dirname(__DIR__)));
    
    // Buat struktur direktori assets/uploads/services secara otomatis
    if (!file_exists($rootPath . '/assets')) {
        @mkdir($rootPath . '/assets', 0777);
    }
    
    if (!file_exists($rootPath . '/assets/images/uploads')) {
        @mkdir($rootPath . '/assets/images/uploads', 0777);
    }
    
    if (!file_exists($rootPath . '/assets/images/uploads/services')) {
        @mkdir($rootPath . '/assets/images/uploads/services', 0777);
    }
    
    // Set direktori upload (dengan slash sebelum assets)
    $uploadDirectory = $rootPath . '/assets/images/uploads/services/';
    
    // Periksa jika direktori dapat ditulis
    if (!is_writable($uploadDirectory)) {
        @chmod($uploadDirectory, 0777);
        if (!is_writable($uploadDirectory)) {
            return [
                'success' => false,
                'message' => "Direktori upload tidak dapat ditulis. Cek permission untuk: " . $uploadDirectory
            ];
        }
    }
    
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
                'message' => "Format file tidak valid. Hanya JPG, PNG, GIF, SVG, dan WEBP yang diperbolehkan."
            ];
        }
        
        // Generate a unique filename to prevent overwriting
        $newFilename = 'service_' . uniqid() . '.' . $extension;
        $targetPath = $uploadDirectory . $newFilename;
        
        // Move the uploaded file
        if (@move_uploaded_file($tempFile, $targetPath)) {
            // Get the relative path for the database (from website root)
            $relativePath = 'assets/images/uploads/services/' . $newFilename;
            return [
                'success' => true,
                'path' => $relativePath
            ];
        } else {
            $uploadError = error_get_last();
            return [
                'success' => false,
                'message' => "Gagal memindahkan file yang diupload. " . 
                             "Error: " . ($uploadError ? $uploadError['message'] : 'Error tidak diketahui') . 
                             ". Periksa apakah PHP memiliki izin tulis ke direktori."
            ];
        }
    }
    
    // If no new file was uploaded, return the old path
    return [
        'success' => true,
        'path' => $oldPath
    ];
}

// Function to handle article image uploads
function handleArticleImageUpload($file) {
    // Define upload directory
    $rootPath = dirname(dirname(dirname(__DIR__)));
    $uploadDir = $rootPath . '/assets/images/uploads/articles/';
    
    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        if (!@mkdir($uploadDir, 0777, true)) {
            // Try to create parent directories if needed
            if (!file_exists($rootPath . '/assets')) {
                @mkdir($rootPath . '/assets', 0777);
            }
            
            if (!file_exists($rootPath . '/assets/images')) {
                @mkdir($rootPath . '/assets/images', 0777);
            }
            
            if (!file_exists($rootPath . '/assets/images/uploads')) {
                @mkdir($rootPath . '/assets/images/uploads', 0777);
            }
            
            if (!file_exists($rootPath . '/assets/images/uploads/articles')) {
                @mkdir($rootPath . '/assets/images/uploads/articles', 0777);
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

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Add Category
    if (isset($_POST['add_category'])) {
        try {
            $conn->beginTransaction();
            
            $categoryName = trim($_POST['category_name']);
            
            if (empty($categoryName)) {
                throw new Exception("Category name is required");
            }
            
            $stmt = $conn->prepare("INSERT INTO service_categories (categories_name) VALUES (:name)");
            $stmt->bindParam(':name', $categoryName);
            $stmt->execute();
            
            $conn->commit();
            $_SESSION['message'] = "Kategori berhasil ditambahkan!";
            $_SESSION['message_type'] = "success";
            
            // Redirect to prevent form resubmission on refresh
            header("Location: ?tab=categories");
            exit;
        } catch (Exception $e) {
            $conn->rollBack();
            $_SESSION['message'] = "Error menambahkan kategori: " . $e->getMessage();
            $_SESSION['message_type'] = "error";
            
            // Redirect to prevent form resubmission on refresh
            header("Location: ?tab=categories");
            exit;
        }
    }
    
    // Edit Category
    elseif (isset($_POST['edit_category'])) {
        try {
            $conn->beginTransaction();
            
            $categoryId = (int)$_POST['category_id'];
            $categoryName = trim($_POST['category_name']);
            
            if (empty($categoryName)) {
                throw new Exception("Nama kategori harus diisi");
            }
            
            $stmt = $conn->prepare("UPDATE service_categories SET categories_name = :name WHERE id = :id");
            $stmt->bindParam(':name', $categoryName);
            $stmt->bindParam(':id', $categoryId);
            $stmt->execute();
            
            $conn->commit();
            $_SESSION['message'] = "Kategori berhasil diperbarui!";
            $_SESSION['message_type'] = "success";
            
            // Redirect to prevent form resubmission on refresh
            header("Location: ?tab=categories");
            exit;
        } catch (Exception $e) {
            $conn->rollBack();
            $_SESSION['message'] = "Error memperbarui kategori: " . $e->getMessage();
            $_SESSION['message_type'] = "error";
            
            // Redirect to prevent form resubmission on refresh
            header("Location: ?tab=categories");
            exit;
        }
    }
    
    // Delete Category
    elseif (isset($_POST['delete_category'])) {
        try {
            $conn->beginTransaction();
            
            $categoryId = (int)$_POST['category_id'];
            
            // Check if the category is being used by any features
            $checkStmt = $conn->prepare("SELECT COUNT(*) FROM service_features WHERE feature_category_id = :id");
            $checkStmt->bindParam(':id', $categoryId);
            $checkStmt->execute();
            
            if ($checkStmt->fetchColumn() > 0) {
                throw new Exception("Tidak dapat menghapus kategori ini karena sedang digunakan oleh satu atau lebih layanan");
            }
            
            $stmt = $conn->prepare("DELETE FROM service_categories WHERE id = :id");
            $stmt->bindParam(':id', $categoryId);
            $stmt->execute();
            
            $conn->commit();
            $_SESSION['message'] = "Kategori berhasil dihapus!";
            $_SESSION['message_type'] = "success";
            
            // Redirect to prevent form resubmission on refresh
            header("Location: ?tab=categories");
            exit;
        } catch (Exception $e) {
            $conn->rollBack();
            $_SESSION['message'] = "Error menghapus kategori: " . $e->getMessage();
            $_SESSION['message_type'] = "error";
            
            // Redirect to prevent form resubmission on refresh
            header("Location: ?tab=categories");
            exit;
        }
    }
    
    // Add Feature
    elseif (isset($_POST['add_feature'])) {
        try {
            $conn->beginTransaction();
            
            $featureName = trim($_POST['feature_name']);
            $featureCategoryId = (int)$_POST['feature_category_id'];
            $featurePath = trim($_POST['feature_path']);
            
            if (empty($featureName)) {
                throw new Exception("Nama layanan harus diisi");
            }
            
            if (empty($featurePath)) {
                throw new Exception("Path layanan harus diisi");
            }
            
            // Handle image upload
            $imagePath = '';
            if (!empty($_FILES['feature_image']['name'])) {
                $uploadResult = handleImageUpload('feature_image');
                if (!$uploadResult['success']) {
                    throw new Exception($uploadResult['message']);
                }
                $imagePath = $uploadResult['path'];
            } elseif (!empty($_POST['feature_image_path'])) {
                // Jika user memasukkan hanya nama file tanpa path lengkap
                $inputPath = $_POST['feature_image_path'];
                if (!strstr($inputPath, '/')) {
                    $imagePath = 'assets/images/uploads/services/' . $inputPath;
                } else {
                    $imagePath = $inputPath;
                }
            } else {
                throw new Exception("Gambar layanan harus diisi");
            }
            
            $stmt = $conn->prepare("INSERT INTO service_features 
                                 (feature_name, feature_category_id, feature_path, feature_image_path) 
                                 VALUES (:name, :category_id, :path, :image_path)");
            
            $stmt->bindParam(':name', $featureName);
            $stmt->bindParam(':category_id', $featureCategoryId);
            $stmt->bindParam(':path', $featurePath);
            $stmt->bindParam(':image_path', $imagePath);
            $stmt->execute();
            
            $conn->commit();
            $_SESSION['message'] = "Layanan berhasil ditambahkan!";
            $_SESSION['message_type'] = "success";
            
            // Redirect to prevent form resubmission on refresh
            header("Location: ?tab=features");
            exit;
        } catch (Exception $e) {
            $conn->rollBack();
            $_SESSION['message'] = "Error menambahkan layanan: " . $e->getMessage();
            $_SESSION['message_type'] = "error";
            
            // Redirect to prevent form resubmission on refresh
            header("Location: ?tab=features");
            exit;
        }
    }
    
    // Edit Feature
    elseif (isset($_POST['edit_feature'])) {
        try {
            $conn->beginTransaction();
        
            $featureId = (int)$_POST['feature_id'];
            $featureName = trim($_POST['feature_name']);
            $featureCategoryId = (int)$_POST['feature_category_id'];
            $featurePath = trim($_POST['feature_path']);
            $oldImagePath = $_POST['old_image_path'];
            
            if (empty($featureName)) {
                throw new Exception("Nama layanan harus diisi");
            }
            
            if (empty($featurePath)) {
                throw new Exception("Path layanan harus diisi");
            }
            
            // Handle image upload
            $imagePath = $oldImagePath;
            if (!empty($_FILES['feature_image']['name'])) {
                $uploadResult = handleImageUpload('feature_image', $oldImagePath);
                if (!$uploadResult['success']) {
                    throw new Exception($uploadResult['message']);
                }
                $imagePath = $uploadResult['path'];
            } elseif (!empty($_POST['feature_image_path'])) {
                // Jika user memasukkan hanya nama file tanpa path lengkap
                $inputPath = $_POST['feature_image_path'];
                if (!strstr($inputPath, '/')) {
                    $imagePath = 'assets/images/uploads/services/' . $inputPath;
                } else {
                    $imagePath = $inputPath;
                }
            }
            
            $stmt = $conn->prepare("UPDATE service_features 
                                 SET feature_name = :name, 
                                     feature_category_id = :category_id, 
                                     feature_path = :path,
                                     feature_image_path = :image_path
                                 WHERE id = :id");
            
            $stmt->bindParam(':name', $featureName);
            $stmt->bindParam(':category_id', $featureCategoryId);
            $stmt->bindParam(':path', $featurePath);
            $stmt->bindParam(':image_path', $imagePath);
            $stmt->bindParam(':id', $featureId);
            $stmt->execute();
            
            $conn->commit();
            $_SESSION['message'] = "Layanan berhasil diperbarui!";
            $_SESSION['message_type'] = "success";
            
            // Redirect to prevent form resubmission on refresh
            header("Location: ?tab=features");
            exit;
        } catch (Exception $e) {
            $conn->rollBack();
            $_SESSION['message'] = "Error memperbarui layanan: " . $e->getMessage();
            $_SESSION['message_type'] = "error";
            
            // Redirect to prevent form resubmission on refresh
            header("Location: ?tab=features");
            exit;
        }
    }
    
    // Delete Feature
    elseif (isset($_POST['delete_feature'])) {
        try {
            $conn->beginTransaction();
            
            $featureId = (int)$_POST['feature_id'];
            
            $stmt = $conn->prepare("DELETE FROM service_features WHERE id = :id");
            $stmt->bindParam(':id', $featureId);
            $stmt->execute();
            
            $conn->commit();
            $_SESSION['message'] = "Layanan berhasil dihapus!";
            $_SESSION['message_type'] = "success";
            
            // Redirect to prevent form resubmission on refresh
            header("Location: ?tab=features");
            exit;
        } catch (Exception $e) {
            $conn->rollBack();
            $_SESSION['message'] = "Error menghapus layanan: " . $e->getMessage();
            $_SESSION['message_type'] = "error";
            
            // Redirect to prevent form resubmission on refresh
            header("Location: ?tab=features");
            exit;
        }
    }
    
    // Add Article
    elseif (isset($_POST['add_article'])) {
        try {
            $conn->beginTransaction();
            
            $title = trim($_POST['article_title']);
            $content = $_POST['article_content'];
            
            if (empty($title)) {
                throw new Exception("Judul artikel harus diisi");
            }
            
            if (empty($content)) {
                throw new Exception("Konten artikel harus diisi");
            }
            
            // Process the content to add custom styling to lists
            $content = processArticleContent($content);
            
            // Generate unique slug
            $slug = generateUniqueSlug($conn, $title);
            
            // Handle image upload
            $imagePath = null;
            if (!empty($_FILES['article_image']['name'])) {
                $uploadResult = handleArticleImageUpload($_FILES['article_image']);
                if (!$uploadResult['success']) {
                    throw new Exception($uploadResult['message']);
                }
                $imagePath = $uploadResult['path'];
            }
            
            // SQL query with optional image field
            $sql = "INSERT INTO service_articles (title, slug, content";
            if ($imagePath) {
                $sql .= ", image_path";
            }
            $sql .= ") VALUES (:title, :slug, :content";
            if ($imagePath) {
                $sql .= ", :image_path";
            }
            $sql .= ")";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':content', $content);
            
            if ($imagePath) {
                $stmt->bindParam(':image_path', $imagePath);
            }
            
            $stmt->execute();
            
            $conn->commit();
            $_SESSION['message'] = "Artikel berhasil ditambahkan!";
            $_SESSION['message_type'] = "success";
            
            // Redirect to prevent form resubmission on refresh
            header("Location: ?tab=articles");
            exit;
        } catch (Exception $e) {
            $conn->rollBack();
            $_SESSION['message'] = "Error menambahkan artikel: " . $e->getMessage();
            if ($e instanceof PDOException) {
                // Tambahkan informasi error PDO yang lebih detail
                $_SESSION['message'] .= " (PDO Error: " . $e->getCode() . ")";
            }
            $_SESSION['message_type'] = "error";
            
            // Redirect to prevent form resubmission on refresh
            header("Location: ?tab=articles");
            exit;
        }
    }
    
    // Edit Article
    elseif (isset($_POST['edit_article'])) {
        try {
            $conn->beginTransaction();
            
            $id = (int)$_POST['article_id'];
            $title = trim($_POST['article_title']);
            $feature_id = $_POST['feature_id'];
            $content = $_POST['article_content'];
            $oldImagePath = $_POST['old_image_path'];
            
            if (empty($title)) {
                throw new Exception("Judul artikel harus diisi");
            }
            
            if (empty($content)) {
                throw new Exception("Konten artikel harus diisi");
            }
            
            // Process the content to add custom styling to lists
            $content = processArticleContent($content);
            
            // Generate unique slug for updated title (exclude current article ID)
            $slug = generateUniqueSlug($conn, $title, $id);
            
            // Handle image upload
            $imagePath = $oldImagePath;
            if (!empty($_FILES['article_image']['name'])) {
                $uploadResult = handleArticleImageUpload($_FILES['article_image']);
                if (!$uploadResult['success']) {
                    throw new Exception($uploadResult['message']);
                }
                $imagePath = $uploadResult['path'];
            }
            
            $stmt = $conn->prepare("UPDATE service_articles 
                                  SET title = :title, 
                                      slug = :slug,
                                      feature_id = :feature_id,
                                      content = :content, 
                                      image_path = :image_path,
                                      updated_at = CURRENT_TIMESTAMP 
                                  WHERE id = :id");
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':feature_id', $feature_id);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':image_path', $imagePath);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $conn->commit();
            $_SESSION['message'] = "Artikel berhasil diperbarui!";
            $_SESSION['message_type'] = "success";
            
            // Redirect to prevent form resubmission on refresh
            header("Location: ?tab=articles");
            exit;
        } catch (Exception $e) {
            $conn->rollBack();
            $_SESSION['message'] = "Error memperbarui artikel: " . $e->getMessage();
            $_SESSION['message_type'] = "error";
            
            // Redirect to prevent form resubmission on refresh
            header("Location: ?tab=articles");
            exit;
        }
    }
    
    // Delete Article
    elseif (isset($_POST['delete_article'])) {
        try {
            $conn->beginTransaction();
            
            $id = (int)$_POST['article_id'];
            
            $stmt = $conn->prepare("DELETE FROM service_articles WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $conn->commit();
            $_SESSION['message'] = "Artikel berhasil dihapus!";
            $_SESSION['message_type'] = "success";
            
            // Redirect to prevent form resubmission on refresh
            header("Location: ?tab=articles");
            exit;
        } catch (Exception $e) {
            $conn->rollBack();
            $_SESSION['message'] = "Error menghapus artikel: " . $e->getMessage();
            $_SESSION['message_type'] = "error";
            
            // Redirect to prevent form resubmission on refresh
            header("Location: ?tab=articles");
            exit;
        }
    }
}

// Display message from session if exists
if (isset($_SESSION['message']) && isset($_SESSION['message_type'])) {
    $message = $_SESSION['message'];
    $messageType = $_SESSION['message_type'];
    
    // Clear the session variables
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Check if slug field exists in the service_articles table, if not, add it
if ($activeTab == 'articles') {
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
}

// Fetch all categories
$categories = [];
try {
    $stmt = $conn->query("SELECT * FROM service_categories ORDER BY categories_name ASC");
    $categories = $stmt->fetchAll();
} catch(PDOException $e) {
    $message = "Error mengambil data kategori: " . $e->getMessage();
    $messageType = "error";
}

// Fetch all features with category names
$features = [];
try {
    $stmt = $conn->query("SELECT f.*, c.categories_name 
                        FROM service_features f
                        LEFT JOIN service_categories c ON f.feature_category_id = c.id
                        ORDER BY f.id ASC");
    $features = $stmt->fetchAll();
} catch(PDOException $e) {
    $message = "Error mengambil data layanan: " . $e->getMessage();
    $messageType = "error";
}

// Fetch all articles
$articles = [];
if ($activeTab == 'articles') {
    try {
        $stmt = $conn->query("SELECT * FROM service_articles ORDER BY created_at DESC");
        $articles = $stmt->fetchAll();
    } catch(PDOException $e) {
        $message = "Error mengambil data artikel: " . $e->getMessage();
        $messageType = "error";
    }
}

// Fetch specific article for editing if edit_article is provided
$editArticle = null;
if (isset($_GET['edit_article']) && !empty($_GET['edit_article'])) {
    $editId = (int)$_GET['edit_article'];
    
    try {
        $stmt = $conn->prepare("SELECT * FROM service_articles WHERE id = :id");
        $stmt->bindParam(':id', $editId);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $editArticle = $stmt->fetch();
            $activeTab = 'articles';
            
            // Prepare content for Quill to preserve list separation
            if ($editArticle['content']) {
                $editArticle['content'] = prepareContentForQuill($editArticle['content']);
            }
        }
    } catch(PDOException $e) {
        $message = "Error mengambil data artikel: " . $e->getMessage();
        $messageType = "error";
    }
}

?>

<!doctype html>
<html lang="id">
<?php include('../../components/head.php'); ?>
<head>
    <!-- Include Quill stylesheet -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    
    <style>
        /* Custom styles for Quill editor */
        .ql-container {
            min-height: 200px;
            max-height: 400px;
            overflow-y: auto;
        }
        
        .quill-content img {
            max-width: 100%;
            height: auto;
        }
        
        .article-preview img {
            max-width: 100%;
            height: auto;
        }
        
        /* Modal fixes for Quill */
        .modal-with-quill {
            max-width: 800px !important;
        }
        
        /* Custom styles for article lists */
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
        
        /* Image preview */
        .image-preview {
            max-height: 200px;
            width: auto;
            object-fit: contain;
        }
        
        /* Modal content */
        .modal-content {
            max-height: calc(100vh - 200px);
            overflow-y: auto;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex flex-col lg:flex-row">
        <?php include('../../components/sidebar.php'); ?>
        
        <div class="flex-1 lg:ml-64">
            <div class="bg-white p-4 shadow-sm flex justify-between items-center">
                <h1 class="text-xl font-semibold text-gray-800">Manajemen Layanan</h1>
                <div class="flex items-center space-x-4">
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
                        <a href="?tab=categories" class="mr-8 py-4 px-1 border-b-2 font-medium text-sm <?php echo $activeTab == 'categories' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>">
                            Kategori Layanan
                        </a>
                        <a href="?tab=features" class="mr-8 py-4 px-1 border-b-2 font-medium text-sm <?php echo $activeTab == 'features' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>">
                            Detail Layanan
                        </a>
                        <a href="?tab=articles" class="mr-8 py-4 px-1 border-b-2 font-medium text-sm <?php echo $activeTab == 'articles' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>">
                            Artikel Layanan
                        </a>
                    </nav>
                </div>
                
                <!-- Categories Tab -->
                <?php if ($activeTab == 'categories'): ?>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Kategori Layanan</h2>
                        <p class="text-sm text-gray-500 mt-1">Kelola kategori layanan yang tersedia</p>
                    </div>
                    <div class="p-6">
                        <!-- Add New Category Form -->
                        <div class="mb-8">
                            <h3 class="text-md font-medium text-gray-800 mb-4">Tambah Kategori Baru</h3>
                            <form method="POST" action="?tab=categories" class="flex items-end space-x-4">
                                <div class="flex-grow">
                                    <label for="category_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Kategori</label>
                                    <input type="text" id="category_name" name="category_name" required
                                           placeholder="Masukkan nama kategori" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <button type="submit" name="add_category" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        Tambah Kategori
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Categories List -->
                        <div>
                            <h3 class="text-md font-medium text-gray-800 mb-4">Daftar Kategori</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Kategori</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php if(empty($categories)): ?>
                                        <tr>
                                            <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada kategori yang ditemukan</td>
                                        </tr>
                                        <?php else: ?>
                                            <?php foreach($categories as $category): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $category['id']; ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($category['categories_name']); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <button type="button" onclick="openEditCategoryModal(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['categories_name']); ?>')" 
                                                            class="text-blue-600 hover:text-blue-900 mr-3">
                                                        <i class="bx bx-edit"></i> Edit
                                                    </button>
                                                    <button type="button" onclick="openDeleteCategoryModal(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['categories_name']); ?>')" 
                                                            class="text-red-600 hover:text-red-900">
                                                        <i class="bx bx-trash"></i> Hapus
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Edit Category Modal -->
                <div id="editCategoryModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
                    <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Edit Kategori</h3>
                            <button type="button" onclick="closeEditCategoryModal()" class="text-gray-400 hover:text-gray-500">
                                <i class="bx bx-x text-2xl"></i>
                            </button>
                        </div>
                        <form method="POST" action="?tab=categories">
                            <input type="hidden" id="edit_category_id" name="category_id">
                            <div class="mb-4">
                                <label for="edit_category_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Kategori</label>
                                <input type="text" id="edit_category_name" name="category_name" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="flex justify-end space-x-3">
                                <button type="button" onclick="closeEditCategoryModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                    Batal
                                </button>
                                <button type="submit" name="edit_category" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Delete Category Modal -->
                <div id="deleteCategoryModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
                    <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Hapus Kategori</h3>
                            <button type="button" onclick="closeDeleteCategoryModal()" class="text-gray-400 hover:text-gray-500">
                                <i class="bx bx-x text-2xl"></i>
                            </button>
                        </div>
                        <p class="text-gray-700 mb-4">Apakah Anda yakin ingin menghapus kategori <span id="delete_category_name" class="font-semibold"></span>?</p>
                        <p class="text-gray-500 text-sm mb-6">Kategori ini hanya dapat dihapus jika tidak digunakan oleh layanan manapun.</p>
                        <form method="POST" action="?tab=categories">
                            <input type="hidden" id="delete_category_id" name="category_id">
                            <div class="flex justify-end space-x-3">
                                <button type="button" onclick="closeDeleteCategoryModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                    Batal
                                </button>
                                <button type="submit" name="delete_category" class="px-4 py-2 bg-red-600 text-white font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                    Hapus Kategori
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Features Tab -->
                <?php if ($activeTab == 'features'): ?>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Detail Layanan</h2>
                        <p class="text-sm text-gray-500 mt-1">Kelola layanan yang tersedia pada website</p>
                    </div>
                    <div class="p-6">
                        <!-- Add New Feature Button -->
                        <div class="mb-6">
                            <button type="button" onclick="openAddFeatureModal()" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <i class="bx bx-plus mr-1"></i> Tambah Layanan Baru
                            </button>
                        </div>
                        
                        <!-- Features List -->
                        <div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Layanan</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Path</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gambar</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php if(empty($features)): ?>
                                        <tr>
                                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada layanan yang ditemukan</td>
                                        </tr>
                                        <?php else: ?>
                                            <?php foreach($features as $feature): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $feature['id']; ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($feature['categories_name']); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($feature['feature_name']); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($feature['feature_path']); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php if(!empty($feature['feature_image_path'])): ?>
                                                    <img src="../../../<?php echo htmlspecialchars($feature['feature_image_path']); ?>" alt="<?php echo htmlspecialchars($feature['feature_name']); ?>" class="h-10 w-auto object-contain">
                                                    <?php else: ?>
                                                    <span class="text-gray-400">No image</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <button type="button" 
                                                            onclick="openEditFeatureModal(
                                                                <?php echo $feature['id']; ?>, 
                                                                '<?php echo htmlspecialchars($feature['feature_name']); ?>', 
                                                                <?php echo $feature['feature_category_id']; ?>, 
                                                                '<?php echo htmlspecialchars($feature['feature_path']); ?>', 
                                                                '<?php echo htmlspecialchars($feature['feature_image_path']); ?>'
                                                            )" 
                                                            class="text-blue-600 hover:text-blue-900 mr-3">
                                                        <i class="bx bx-edit"></i> Edit
                                                    </button>
                                                    <button type="button" 
                                                            onclick="openDeleteFeatureModal(<?php echo $feature['id']; ?>, '<?php echo htmlspecialchars($feature['feature_name']); ?>')" 
                                                            class="text-red-600 hover:text-red-900">
                                                        <i class="bx bx-trash"></i> Hapus
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Add Feature Modal -->
                <div id="addFeatureModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
                    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Tambah Layanan Baru</h3>
                            <button type="button" onclick="closeAddFeatureModal()" class="text-gray-400 hover:text-gray-500">
                                <i class="bx bx-x text-2xl"></i>
                            </button>
                        </div>
                        <form method="POST" action="?tab=features" enctype="multipart/form-data">
                            <div class="mb-4">
                                <label for="feature_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Layanan</label>
                                <input type="text" id="feature_name" name="feature_name" required
                                       placeholder="Masukkan nama layanan" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div class="mb-4">
                                <label for="feature_category_id" class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                                <select id="feature_category_id" name="feature_category_id" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Pilih Kategori</option>
                                    <?php foreach($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['categories_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-4">
                                <label for="feature_path" class="block text-sm font-medium text-gray-700 mb-1">Path URL</label>
                                <input type="text" id="feature_path" name="feature_path" required
                                       placeholder="Contoh: services/penerbitan-jurnal" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="mt-1 text-xs text-gray-500">Path URL relatif dari root website (tanpa slash di awal)</p>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Gambar Layanan</label>
                                <div class="flex flex-col space-y-2">
                                    <input type="file" id="feature_image" name="feature_image" 
                                           class="w-full block text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                    <p class="text-xs text-gray-500">Format: JPG, PNG, GIF, SVG, WEBP. Gambar akan disimpan di <strong>assets/images/uploads/services/</strong></p>
                                </div>
                            </div>
                            
                            <div class="flex justify-end space-x-3 mt-6">
                                <button type="button" onclick="closeAddFeatureModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                    Batal
                                </button>
                                <button type="submit" name="add_feature" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    Tambah Layanan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Edit Feature Modal -->
                <div id="editFeatureModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden overflow-auto py-8">
                    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-auto my-auto">
                        <!-- Modal Header - Tetap di atas saat scroll -->
                        <div class="sticky top-0 bg-white p-6 border-b border-gray-200">
                            <div class="flex justify-between items-center">
                                <h3 class="text-lg font-medium text-gray-900">Edit Layanan</h3>
                                <button type="button" onclick="closeEditFeatureModal()" class="text-gray-400 hover:text-gray-500">
                                    <i class="bx bx-x text-2xl"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Modal Body dengan Scroll -->
                        <div class="p-6 max-h-[calc(100vh-16rem)] overflow-y-auto">
                            <form method="POST" action="?tab=features" enctype="multipart/form-data">
                                <input type="hidden" id="edit_feature_id" name="feature_id">
                                <input type="hidden" id="old_image_path" name="old_image_path">
                                
                                <div class="mb-4">
                                    <label for="edit_feature_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Layanan</label>
                                    <input type="text" id="edit_feature_name" name="feature_name" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                
                                <div class="mb-4">
                                    <label for="edit_feature_category_id" class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                                    <select id="edit_feature_category_id" name="feature_category_id" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Pilih Kategori</option>
                                        <?php foreach($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['categories_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="edit_feature_path" class="block text-sm font-medium text-gray-700 mb-1">Path URL</label>
                                    <input type="text" id="edit_feature_path" name="feature_path" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <p class="mt-1 text-xs text-gray-500">Path URL relatif dari root website (tanpa slash di awal)</p>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Gambar Layanan</label>
                                    
                                    <div id="current_image_preview" class="mb-3 p-2 border rounded-md bg-gray-50 flex items-center">
                                        <div class="w-16 h-16 flex items-center justify-center overflow-hidden bg-gray-100 rounded">
                                            <img id="current_image" src="" alt="Current image" class="max-h-full max-w-full">
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium">Gambar Saat Ini</p>
                                            <p id="current_image_path" class="text-xs text-gray-500 truncate"></p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex flex-col space-y-2">
                                        <input type="file" id="edit_feature_image" name="feature_image" 
                                            class="w-full block text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                        <p class="text-xs text-gray-500">Format: JPG, PNG, GIF, SVG, WEBP. Gambar akan disimpan di direktori assets/images/uploads/services/</p>
                                    </div>
                                    
                                                                            <div class="mt-3">
                                        <label for="edit_feature_image_path" class="block text-sm font-medium text-gray-700 mb-1">Atau Gunakan Gambar yang Sudah Ada</label>
                                        <div class="flex">
                                            <span class="inline-flex items-center px-3 text-gray-500 bg-gray-100 border border-r-0 border-gray-300 rounded-l-md">
                                                assets/images/uploads/services/
                                            </span>
                                            <input type="text" id="edit_feature_image_path" name="feature_image_path"
                                                placeholder="nama-file.jpg" 
                                                class="flex-1 px-3 py-2 border border-gray-300 rounded-r-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500">Cukup masukkan nama file jika gambar sudah ada di direktori assets/images/uploads/services/</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Modal Footer - Tetap di bawah saat scroll -->
                            <div class="sticky bottom-0 bg-white p-6 border-t border-gray-200">
                                <div class="flex justify-end space-x-3">
                                    <button type="button" onclick="closeEditFeatureModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                        Batal
                                    </button>
                                    <button type="submit" name="edit_feature" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Delete Feature Modal -->
                <div id="deleteFeatureModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
                    <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Hapus Layanan</h3>
                            <button type="button" onclick="closeDeleteFeatureModal()" class="text-gray-400 hover:text-gray-500">
                                <i class="bx bx-x text-2xl"></i>
                            </button>
                        </div>
                        <p class="text-gray-700 mb-6">Apakah Anda yakin ingin menghapus layanan <span id="delete_feature_name" class="font-semibold"></span>?</p>
                        <form method="POST" action="?tab=features">
                            <input type="hidden" id="delete_feature_id" name="feature_id">
                            <div class="flex justify-end space-x-3">
                                <button type="button" onclick="closeDeleteFeatureModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                    Batal
                                </button>
                                <button type="submit" name="delete_feature" class="px-4 py-2 bg-red-600 text-white font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                    Hapus Layanan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Articles Tab -->
                <?php if ($activeTab == 'articles'): ?>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Artikel Layanan</h2>
                        <p class="text-sm text-gray-500 mt-1">Kelola artikel tentang layanan yang tersedia</p>
                    </div>
                    <div class="p-6">
                        <!-- Add New Article Button -->
                        <div class="mb-6">
                            <a href="service_article.php" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <i class="bx bx-plus mr-1"></i> Tambah Artikel Baru
                            <a/>
                        </div>
                        
                        <?php if ($editArticle): ?>
                            <!-- Edit Article Form -->
                            <div class="mb-8 bg-gray-50 p-6 rounded-lg border border-gray-200">
                                <h3 class="text-lg font-medium text-gray-800 mb-4">Edit Artikel</h3>
                                <form method="POST" action="?tab=articles" id="editArticleForm" enctype="multipart/form-data">
                                    <input type="hidden" name="article_id" value="<?php echo $editArticle['id']; ?>">
                                    <input type="hidden" name="old_image_path" value="<?php echo htmlspecialchars($editArticle['image_path']); ?>">
                                    
                                    <div class="mb-4">
                                        <label for="edit_article_title" class="block text-sm font-medium text-gray-700 mb-1">Judul Artikel</label>
                                        <input type="text" id="edit_article_title" name="article_title" required
                                               value="<?php echo htmlspecialchars($editArticle['title']); ?>"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                               oninput="updateSlugPreview(this.value, 'edit_slug_preview')">
                                        <div class="mt-1 flex flex-col">
                                            <span class="text-sm text-gray-500">Generated Slug:</span>
                                            <span id="edit_slug_preview" class="slug-preview"><?php echo htmlspecialchars($editArticle['slug'] ?? generateUniqueSlug($conn, $editArticle['title'], $editArticle['id'])); ?></span>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="block text-gray-700 mb-2">Feature</label>
                                        <select name="feature_id" id="feature_id" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                            <option value="<?php ?>" selected>Select Feature</option>
                                            <?php
                                                $query = "SELECT f.id, f.feature_name, a.feature_id 
                                                FROM service_features f
                                                LEFT JOIN service_articles a ON a.feature_id = f.id
                                                WHERE a.feature_id IS NULL;";
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
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Gambar Utama</label>
                                        
                                        <?php if(!empty($editArticle['image_path'])): ?>
                                        <div class="mb-3 p-2 border rounded-md bg-gray-50">
                                            <div class="flex flex-col items-center mb-2">
                                                <img src="../../../<?php echo htmlspecialchars($editArticle['image_path']); ?>" alt="Current image" class="image-preview mb-2">
                                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($editArticle['image_path']); ?></p>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <input type="file" name="article_image" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                        <p class="mt-1 text-xs text-gray-500">Format: JPG, PNG, GIF, SVG, WEBP. Kosongkan jika ingin tetap menggunakan gambar saat ini.</p>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Konten Artikel</label>
                                        <div id="edit-editor-container" class="h-64 border border-gray-300"><?php echo $editArticle['content']; ?></div>
                                        <input type="hidden" name="article_content" id="edit_article_content_hidden">
                                        <p class="mt-1 text-xs text-gray-500">Gunakan editor untuk memformat artikel sesuai kebutuhan.</p>
                                    </div>
                                    
                                    <div class="flex space-x-4">
                                        <a href="?tab=articles" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition">
                                            Batal
                                        </a>
                                        <button type="submit" name="edit_article" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                                            Update Artikel
                                        </button>
                                    </div>
                                </form>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Articles List -->
                        <div>
                            <h3 class="text-md font-medium text-gray-800 mb-4">Daftar Artikel</h3>
                            <?php if(empty($articles)): ?>
                                <p class="text-gray-500">Tidak ada artikel yang ditemukan</p>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach($articles as $article): ?>
                                        <div class="border border-gray-200 rounded-md p-4 shadow-sm hover:shadow-md transition-shadow">
                                            <div class="flex justify-between">
                                                <div class="flex items-center space-x-4">
                                                    <?php if(!empty($article['image_path'])): ?>
                                                    <div class="flex-shrink-0">
                                                        <img src="../../../<?php echo htmlspecialchars($article['image_path']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="h-16 w-16 object-cover rounded-md">
                                                    </div>
                                                    <?php endif; ?>
                                                    <div class="flex flex-col">
                                                        <h3 class="text-lg font-medium"><?php echo htmlspecialchars($article['title']); ?></h3>
                                                        <div class="flex flex-col space-y-1">
                                                            <p class="text-sm text-gray-500"><?php echo date('d M Y', strtotime($article['created_at'])); ?></p>
                                                            <code class="text-xs bg-gray-100 px-2 py-1 rounded-md"><?php echo htmlspecialchars($article['slug'] ?? ''); ?></code>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex space-x-2 items-center">
                                                    <button 
                                                        type="button"
                                                        onclick="openPreviewModal(<?php echo $article['id']; ?>, '<?php echo htmlspecialchars(addslashes($article['title'])); ?>', <?php echo htmlspecialchars(json_encode($article['content'])); ?>, '<?php echo !empty($article['image_path']) ? '../../../' . htmlspecialchars($article['image_path']) : ''; ?>', '<?php echo htmlspecialchars($article['slug'] ?? ''); ?>')"
                                                        class="px-3 py-1 bg-green-50 text-green-700 rounded-md hover:bg-green-100 transition"
                                                    >
                                                        <i class="bx bx-show"></i> Preview
                                                    </button>
                                                    <a href="?tab=articles&edit_article=<?php echo $article['id']; ?>" class="px-3 py-1 bg-blue-50 text-blue-700 rounded-md hover:bg-blue-100 transition">
                                                        <i class="bx bx-edit"></i> Edit
                                                    </a>
                                                    <button 
                                                        type="button"
                                                        onclick="openDeleteArticleModal(<?php echo $article['id']; ?>, '<?php echo htmlspecialchars(addslashes($article['title'])); ?>')"
                                                        class="px-3 py-1 bg-red-50 text-red-700 rounded-md hover:bg-red-100 transition"
                                                    >
                                                        <i class="bx bx-trash"></i> Hapus
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Add Article Modal - Enhanced Design -->
                <div id="addArticleModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden overflow-auto py-8">
                    <div class="bg-white rounded-lg shadow-xl w-full max-w-5xl mx-auto my-auto">
                        <!-- Modal Header - Fixed at top during scroll -->
                        <div class="sticky top-0 bg-gradient-to-r from-blue-600 to-blue-800 p-6 rounded-t-lg">
                            <div class="flex justify-between items-center">
                                <h3 class="text-xl font-semibold text-white">Tambah Artikel Baru</h3>
                                <button type="button" onclick="closeAddArticleModal()" class="text-white hover:text-gray-200 focus:outline-none transition-colors">
                                    <i class="bx bx-x text-3xl"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Modal Body with Scrollable Content -->
                        <div class="p-6 max-h-[calc(100vh-16rem)] overflow-y-auto">
                            <form method="POST" action="?tab=articles" id="addArticleForm" enctype="multipart/form-data">
                                <!-- Title Section -->
                                <div class="mb-6 bg-gray-50 p-4 rounded-lg border-l-4 border-blue-500">
                                    <label for="article_title" class="block text-sm font-medium text-gray-700 mb-2">Judul Artikel</label>
                                    <input type="text" id="article_title" name="article_title" required
                                           placeholder="Masukkan judul artikel yang menarik" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                           oninput="updateSlugPreview(this.value, 'slug_preview')">
                                    <div class="mt-2 flex flex-col">
                                        <div class="flex items-center">
                                            <span class="text-sm text-gray-500 mr-2">
                                                <i class="bx bx-link text-blue-500"></i> URL Slug:
                                            </span>
                                            <span id="slug_preview" class="slug-preview text-sm bg-white border border-gray-200 px-3 py-1 rounded-md font-mono">article_slug</span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">Slug akan otomatis dibuat dari judul artikel. Digunakan untuk URL artikel.</p>
                                    </div>
                                </div>
                                
                                <!-- Featured Image Section -->
                                <div class="mb-6 bg-gray-50 p-4 rounded-lg border-l-4 border-green-500">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="bx bx-image text-green-500 mr-1"></i> Gambar Utama
                                    </label>
                                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:bg-gray-50 transition-colors">
                                        <input type="file" id="article_image" name="article_image" 
                                               class="hidden" accept="image/jpeg,image/png,image/gif,image/svg+xml,image/webp" onchange="showFileName(this)">
                                        <label for="article_image" class="cursor-pointer flex flex-col items-center justify-center">
                                            <i class="bx bx-upload text-4xl text-gray-400"></i>
                                            <span class="mt-2 text-sm text-gray-600">Klik untuk upload gambar atau drag & drop file di sini</span>
                                            <span id="selected_file_name" class="mt-2 text-xs text-blue-600 hidden"></span>
                                        </label>
                                    </div>
                                    <p class="mt-2 text-xs flex items-center text-gray-500">
                                        <i class="bx bx-info-circle mr-1"></i> Format: JPG, PNG, GIF, SVG, WEBP. Disarankan ukuran 1200x630px
                                    </p>
                                </div>
                                
                                <!-- Content Section -->
                                <div class="mb-6 bg-gray-50 p-4 rounded-lg border-l-4 border-purple-500">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="bx bx-edit text-purple-500 mr-1"></i> Konten Artikel
                                    </label>
                                    <div id="add-editor-container" class="bg-white min-h-[300px] border border-gray-300 rounded-lg"></div>
                                    <input type="hidden" name="article_content" id="add_article_content_hidden">
                                    <p class="mt-2 text-xs flex items-center text-gray-500">
                                        <i class="bx bx-bulb mr-1 text-yellow-500"></i> Tip: Gunakan heading untuk membagi artikel menjadi beberapa bagian dan list untuk menyajikan poin-poin penting.
                                    </p>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Modal Footer - Fixed at bottom during scroll -->
                        <div class="sticky bottom-0 bg-gray-50 p-6 border-t border-gray-200 rounded-b-lg">
                            <div class="flex justify-end space-x-3">
                                <button type="button" onclick="closeAddArticleModal()" 
                                        class="px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors flex items-center">
                                    <i class="bx bx-x mr-2"></i> Batal
                                </button>
                                <button type="button" onclick="submitAddArticleForm()" 
                                        class="px-5 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors flex items-center">
                                    <i class="bx bx-save mr-2"></i> Simpan Artikel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Preview Article Modal -->
                <div id="previewModal" class="modal fixed w-full h-full top-0 left-0 flex items-center justify-center hidden z-50">
                    <div class="modal-overlay absolute w-full h-full bg-gray-900 opacity-50"></div>
                    
                    <div class="modal-container bg-white w-11/12 md:max-w-3xl mx-auto rounded-lg shadow-lg z-50 overflow-y-auto">
                        <!-- Modal Header -->
                        <div class="modal-header flex justify-between items-center p-5 border-b border-gray-200">
                            <div>
                                <h2 id="previewTitle" class="text-xl font-bold"></h2>
                                <code id="previewSlug" class="text-xs bg-gray-100 px-2 py-1 rounded-md"></code>
                            </div>
                            <button class="modal-close text-gray-500 hover:text-gray-700 focus:outline-none">
                                <i class="bx bx-x text-3xl"></i>
                            </button>
                        </div>
                        
                        <!-- Modal Content -->
                        <div class="modal-content p-5">
                            <div id="previewImageContainer" class="mb-4 hidden">
                                <img id="previewImage" src="" alt="Article image" class="max-w-full mx-auto rounded-md">
                            </div>
                            <div id="previewContent" class="prose max-w-none"></div>
                        </div>
                        
                        <!-- Modal Footer -->
                        <div class="modal-footer p-5 border-t border-gray-200">
                            <div class="flex justify-end">
                                <button class="modal-close px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition">
                                    Tutup
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Delete Article Modal -->
                <div id="deleteArticleModal" class="modal fixed w-full h-full top-0 left-0 flex items-center justify-center hidden z-50">
                    <div class="modal-overlay absolute w-full h-full bg-gray-900 opacity-50"></div>
                    
                    <div class="modal-container bg-white w-11/12 md:max-w-md mx-auto rounded-lg shadow-lg z-50">
                        <!-- Modal Header -->
                        <div class="modal-header flex justify-between items-center p-5 border-b border-gray-200">
                            <h2 class="text-xl font-bold text-red-600">Konfirmasi Hapus</h2>
                            <button class="modal-close text-gray-500 hover:text-gray-700 focus:outline-none">
                                <i class="bx bx-x text-3xl"></i>
                            </button>
                        </div>
                        
                        <!-- Modal Content -->
                        <div class="modal-content p-5">
                            <p>Apakah Anda yakin ingin menghapus artikel "<span id="deleteArticleTitle" class="font-semibold"></span>"?</p>
                            <p class="text-red-500 mt-2">Tindakan ini tidak dapat dibatalkan.</p>
                        </div>
                        
                        <!-- Modal Footer -->
                        <div class="modal-footer p-5 border-t border-gray-200">
                            <form id="deleteArticleForm" method="POST" action="?tab=articles">
                                <input type="hidden" id="deleteArticleId" name="article_id">
                                <div class="flex justify-end space-x-4">
                                    <button type="button" class="modal-close px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition">
                                        Batal
                                    </button>
                                    <button type="submit" name="delete_article" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition">
                                        Hapus
                                    </button>
                                </div>
                            </form>
                        </div>
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
    
    <!-- Include Quill JavaScript -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    
    <script>
        // Konstanta untuk path gambar
        const servicesImagePath = 'assets/images/uploads/services/';
        
        // Initialize Quill editor for edit mode
        let quillEditor = null;
        let editQuillEditor = null;
        let addQuillEditor = null;
        
        // Quill editor configuration
        const quillOptions = {
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['link', 'image'],
                    ['clean']
                ],
                clipboard: {
                    matchVisual: false
                }
            },
            placeholder: 'Tulis konten artikel di sini...',
            theme: 'snow'
        };
        
        // Initialize editors when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize edit article editor if it exists
            const editEditorContainer = document.getElementById('edit-editor-container');
            if (editEditorContainer) {
                editQuillEditor = new Quill('#edit-editor-container', quillOptions);
                
                // Update hidden field before form submission
                const editArticleForm = document.getElementById('editArticleForm');
                if (editArticleForm) {
                    editArticleForm.onsubmit = function() {
                        document.getElementById('edit_article_content_hidden').value = editQuillEditor.root.innerHTML;
                        return true;
                    };
                }
            }
            
            // Initialize slug preview
            const titleInput = document.getElementById('article_title');
            if (titleInput) {
                updateSlugPreview(titleInput.value, 'slug_preview');
            }
            
            const editTitleInput = document.getElementById('edit_article_title');
            if (editTitleInput) {
                updateSlugPreview(editTitleInput.value, 'edit_slug_preview');
            }
        });
        
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
        
        // Category Modals
        function openEditCategoryModal(id, name) {
            document.getElementById('edit_category_id').value = id;
            document.getElementById('edit_category_name').value = name;
            document.getElementById('editCategoryModal').classList.remove('hidden');
        }
        
        function closeEditCategoryModal() {
            document.getElementById('editCategoryModal').classList.add('hidden');
        }
        
        function openDeleteCategoryModal(id, name) {
            document.getElementById('delete_category_id').value = id;
            document.getElementById('delete_category_name').textContent = name;
            document.getElementById('deleteCategoryModal').classList.remove('hidden');
        }
        
        function closeDeleteCategoryModal() {
            document.getElementById('deleteCategoryModal').classList.add('hidden');
        }
        
        // Feature Modals
        function openAddFeatureModal() {
            // Reset form
            const form = document.querySelector('#addFeatureModal form');
            if (form) {
                form.reset();
            }
            
            document.getElementById('addFeatureModal').classList.remove('hidden');
        }
        
        function closeAddFeatureModal() {
            document.getElementById('addFeatureModal').classList.add('hidden');
        }
        
        function openEditFeatureModal(id, name, categoryId, path, imagePath) {
            document.getElementById('edit_feature_id').value = id;
            document.getElementById('edit_feature_name').value = name;
            document.getElementById('edit_feature_category_id').value = categoryId;
            document.getElementById('edit_feature_path').value = path;
            document.getElementById('old_image_path').value = imagePath;
            
            // Extract filename from full path jika path dimulai dengan services dir path
            if (imagePath && imagePath.startsWith(servicesImagePath)) {
                document.getElementById('edit_feature_image_path').value = imagePath.substring(servicesImagePath.length);
            } else {
                document.getElementById('edit_feature_image_path').value = '';
            }
            
            // Set current image preview
            const currentImageEl = document.getElementById('current_image');
            const currentImagePathEl = document.getElementById('current_image_path');
            
            if (imagePath && imagePath.trim() !== '') {
                currentImageEl.src = '../../../' + imagePath;
                currentImagePathEl.textContent = imagePath;
                document.getElementById('current_image_preview').classList.remove('hidden');
            } else {
                document.getElementById('current_image_preview').classList.add('hidden');
            }
            
            document.getElementById('editFeatureModal').classList.remove('hidden');
        }
        
        function closeEditFeatureModal() {
            document.getElementById('editFeatureModal').classList.add('hidden');
        }
        
        function openDeleteFeatureModal(id, name) {
            document.getElementById('delete_feature_id').value = id;
            document.getElementById('delete_feature_name').textContent = name;
            document.getElementById('deleteFeatureModal').classList.remove('hidden');
        }
        
        function closeDeleteFeatureModal() {
            document.getElementById('deleteFeatureModal').classList.add('hidden');
        }
        
        // Article Modals
        function openAddArticleModal() {
            // Reset form
            const form = document.querySelector('#addArticleForm');
            if (form) {
                form.reset();
            }
            
            // Initialize Quill editor if not already initialized
            if (!addQuillEditor) {
                addQuillEditor = new Quill('#add-editor-container', quillOptions);
            } else {
                // Clear editor content
                addQuillEditor.root.innerHTML = '';
            }
            
            document.getElementById('addArticleModal').classList.remove('hidden');
        }
        
        function closeAddArticleModal() {
            document.getElementById('addArticleModal').classList.add('hidden');
        }
        
        function submitAddArticleForm() {
            // Update hidden input with Quill content
            if (addQuillEditor) {
                document.getElementById('add_article_content_hidden').value = addQuillEditor.root.innerHTML;
            }
            
            // Submit the form
            document.getElementById('addArticleForm').submit();
        }
        
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
        
        function openDeleteArticleModal(id, title) {
            document.getElementById('deleteArticleId').value = id;
            document.getElementById('deleteArticleTitle').textContent = title;
            
            const modal = document.getElementById('deleteArticleModal');
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
        
        // Function to show filename in the enhanced image upload field
        function showFileName(input) {
            const fileNameElement = document.getElementById('selected_file_name');
            if (input.files && input.files[0]) {
                const fileName = input.files[0].name;
                fileNameElement.textContent = fileName;
                fileNameElement.classList.remove('hidden');
                
                // Change parent container style to show active state
                input.parentElement.parentElement.classList.add('bg-blue-50', 'border-blue-300');
                input.parentElement.parentElement.classList.remove('border-gray-300');
            } else {
                fileNameElement.classList.add('hidden');
                input.parentElement.parentElement.classList.remove('bg-blue-50', 'border-blue-300');
                input.parentElement.parentElement.classList.add('border-gray-300');
            }
        }
        
        // Form handling untuk path otomatis
        document.addEventListener('DOMContentLoaded', function() {
            // Form untuk menambah layanan
            const addForm = document.querySelector('#addFeatureModal form');
            if (addForm) {
                addForm.addEventListener('submit', function(e) {
                    const imagePathInput = document.getElementById('feature_image_path');
                    if (imagePathInput && imagePathInput.value && !imagePathInput.value.includes('/')) {
                        // User memasukkan hanya nama file, tambahkan prefix path
                        imagePathInput.value = servicesImagePath + imagePathInput.value;
                    }
                });
            }
            
            // Form untuk mengedit layanan
            const editForm = document.querySelector('#editFeatureModal form');
            if (editForm) {
                editForm.addEventListener('submit', function(e) {
                    const editImagePathInput = document.getElementById('edit_feature_image_path');
                    if (editImagePathInput && editImagePathInput.value && !editImagePathInput.value.includes('/')) {
                        // User memasukkan hanya nama file, tambahkan prefix path
                        editImagePathInput.value = servicesImagePath + editImagePathInput.value;
                    }
                });
            }
        });
    </script>
</body>
</html>