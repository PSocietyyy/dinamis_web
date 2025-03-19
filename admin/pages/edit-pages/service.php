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

// Handle image uploads dengan path otomatis ke assets/images/services
// Handle image uploads dengan path otomatis ke assets/uploads/services
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
            
            // Check if the feature is being used by any articles
            $checkStmt = $conn->prepare("SELECT COUNT(*) FROM service_articles WHERE feature_id = :id");
            $checkStmt->bindParam(':id', $featureId);
            $checkStmt->execute();
            
            if ($checkStmt->fetchColumn() > 0) {
                // Jika memiliki artikel terkait, hapus artikel terlebih dahulu
                $delArticleStmt = $conn->prepare("DELETE FROM service_articles WHERE feature_id = :id");
                $delArticleStmt->bindParam(':id', $featureId);
                $delArticleStmt->execute();
            }
            
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
            
            $featureId = !empty($_POST['feature_id']) ? (int)$_POST['feature_id'] : null;
            $title = trim($_POST['article_title']);
            $content = $_POST['article_content']; // HTML content from Quill
            
            if (empty($title)) {
                throw new Exception("Judul artikel harus diisi");
            }
            
            if (empty($content)) {
                throw new Exception("Konten artikel harus diisi");
            }
            
            // Handle image upload for article thumbnail if available
            $imagePath = null;
            if (!empty($_FILES['article_image']['name'])) {
                $uploadResult = handleImageUpload('article_image');
                if (!$uploadResult['success']) {
                    throw new Exception($uploadResult['message']);
                }
                $imagePath = $uploadResult['path'];
            }
            
            $stmt = $conn->prepare("INSERT INTO service_articles 
                                  (feature_id, title, content, image_path) 
                                  VALUES (:feature_id, :title, :content, :image_path)");
            
            $stmt->bindParam(':feature_id', $featureId);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':image_path', $imagePath);
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
            
            $articleId = (int)$_POST['article_id'];
            $featureId = !empty($_POST['feature_id']) ? (int)$_POST['feature_id'] : null;
            $title = trim($_POST['article_title']);
            $content = $_POST['article_content']; // HTML content from Quill
            $oldImagePath = $_POST['old_article_image'];
            
            if (empty($title)) {
                throw new Exception("Judul artikel harus diisi");
            }
            
            if (empty($content)) {
                throw new Exception("Konten artikel harus diisi");
            }
            
            // Handle image upload for article thumbnail if available
            $imagePath = $oldImagePath;
            if (!empty($_FILES['article_image']['name'])) {
                $uploadResult = handleImageUpload('article_image', $oldImagePath);
                if (!$uploadResult['success']) {
                    throw new Exception($uploadResult['message']);
                }
                $imagePath = $uploadResult['path'];
            }
            
            $stmt = $conn->prepare("UPDATE service_articles 
                                  SET feature_id = :feature_id, 
                                      title = :title, 
                                      content = :content, 
                                      image_path = :image_path,
                                      updated_at = CURRENT_TIMESTAMP
                                  WHERE id = :id");
            
            $stmt->bindParam(':feature_id', $featureId);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':image_path', $imagePath);
            $stmt->bindParam(':id', $articleId);
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
            
            $articleId = (int)$_POST['article_id'];
            
            $stmt = $conn->prepare("DELETE FROM service_articles WHERE id = :id");
            $stmt->bindParam(':id', $articleId);
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

// Fetch all articles with related feature names
$articles = [];
try {
    $stmt = $conn->query("SELECT a.*, f.feature_name 
                       FROM service_articles a
                       LEFT JOIN service_features f ON a.feature_id = f.id
                       ORDER BY a.created_at DESC");
    $articles = $stmt->fetchAll();
} catch(PDOException $e) {
    $message = "Error mengambil data artikel: " . $e->getMessage();
    $messageType = "error";
}

// Fetch specific article for editing if id is provided
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
                                    <p class="text-xs text-gray-500">Format: JPG, PNG, GIF, SVG, WEBP. Gambar akan disimpan di <strong>assets/images/images/services/</strong></p>
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
                <!-- Edit Feature Modal dengan Scroll -->
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
                        <p class="text-sm text-gray-500 mt-1">Kelola artikel untuk setiap layanan yang tersedia</p>
                    </div>
                    <div class="p-6">
                        <!-- Add New Article Button -->
                        <div class="mb-6">
                            <button type="button" onclick="openAddArticleModal()" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <i class="bx bx-plus mr-1"></i> Tambah Artikel Baru
                            </button>
                        </div>
                        
                        <!-- Articles List -->
                        <div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terkait Layanan</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul Artikel</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gambar</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php if(empty($articles)): ?>
                                        <tr>
                                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada artikel yang ditemukan</td>
                                        </tr>
                                        <?php else: ?>
                                            <?php foreach($articles as $article): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $article['id']; ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo $article['feature_id'] ? htmlspecialchars($article['feature_name']) : '<span class="text-gray-400">Tidak terkait</span>'; ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($article['title']); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php if(!empty($article['image_path'])): ?>
                                                    <img src="../../../<?php echo htmlspecialchars($article['image_path']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="h-10 w-auto object-contain">
                                                    <?php else: ?>
                                                    <span class="text-gray-400">No image</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo date('d M Y', strtotime($article['created_at'])); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <a href="?tab=articles&edit_article=<?php echo $article['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                                        <i class="bx bx-edit"></i> Edit
                                                    </a>
                                                    <button type="button" 
                                                            onclick="openDeleteArticleModal(<?php echo $article['id']; ?>, '<?php echo htmlspecialchars($article['title']); ?>')" 
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
                
                <!-- Add/Edit Article Form (appears when editing) -->
                <?php if(isset($editArticle) && $editArticle): ?>
                <div class="mt-8 bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Edit Artikel</h2>
                        <p class="text-sm text-gray-500 mt-1">Perbarui konten artikel</p>
                    </div>
                    <div class="p-6">
                        <form method="POST" action="?tab=articles" enctype="multipart/form-data">
                            <input type="hidden" name="article_id" value="<?php echo $editArticle['id']; ?>">
                            <input type="hidden" name="old_article_image" value="<?php echo htmlspecialchars($editArticle['image_path']); ?>">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label for="article_title" class="block text-sm font-medium text-gray-700 mb-1">Judul Artikel</label>
                                    <input type="text" id="article_title" name="article_title" required
                                           value="<?php echo htmlspecialchars($editArticle['title']); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                
                                <div>
                                    <label for="feature_id" class="block text-sm font-medium text-gray-700 mb-1">Terkait Layanan</label>
                                    <select id="feature_id" name="feature_id" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Tidak terkait dengan layanan spesifik</option>
                                        <?php foreach($features as $feature): ?>
                                        <option value="<?php echo $feature['id']; ?>" <?php echo ($editArticle['feature_id'] == $feature['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($feature['feature_name']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Gambar Artikel</label>
                                
                                <?php if(!empty($editArticle['image_path'])): ?>
                                <div class="mb-3 p-2 border rounded-md bg-gray-50 flex items-center">
                                    <div class="w-16 h-16 flex items-center justify-center overflow-hidden bg-gray-100 rounded">
                                        <img src="../../../<?php echo htmlspecialchars($editArticle['image_path']); ?>" alt="Current image" class="max-h-full max-w-full">
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium">Gambar Saat Ini</p>
                                        <p class="text-xs text-gray-500 truncate"><?php echo htmlspecialchars($editArticle['image_path']); ?></p>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <div class="flex flex-col space-y-2">
                                    <input type="file" id="article_image" name="article_image" 
                                           class="w-full block text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                    <p class="text-xs text-gray-500">Format: JPG, PNG, GIF, SVG, WEBP. Biarkan kosong jika tidak ingin mengubah gambar.</p>
                                </div>
                            </div>
                            
                            <div class="mb-6">
                                <label for="article_content" class="block text-sm font-medium text-gray-700 mb-1">Konten Artikel</label>
                                <!-- Quill editor container -->
                                <div id="editor-container" class="border border-gray-300 rounded-md"><?php echo $editArticle['content']; ?></div>
                                <!-- Hidden input to store HTML content -->
                                <input type="hidden" name="article_content" id="article_content_hidden">
                            </div>
                            
                            <div class="flex justify-end space-x-3">
                                <a href="?tab=articles" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                    Batal
                                </a>
                                <button type="submit" name="edit_article" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Add Article Modal -->
                <div id="addArticleModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden overflow-auto py-8">
                    <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl mx-auto my-auto modal-with-quill">
                        <!-- Modal Header -->
                        <div class="sticky top-0 bg-white p-6 border-b border-gray-200">
                            <div class="flex justify-between items-center">
                                <h3 class="text-lg font-medium text-gray-900">Tambah Artikel Baru</h3>
                                <button type="button" onclick="closeAddArticleModal()" class="text-gray-400 hover:text-gray-500">
                                    <i class="bx bx-x text-2xl"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Modal Body -->
                        <div class="p-6 max-h-[calc(100vh-16rem)] overflow-y-auto">
                            <form method="POST" action="?tab=articles" enctype="multipart/form-data" id="addArticleForm">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div>
                                        <label for="add_article_title" class="block text-sm font-medium text-gray-700 mb-1">Judul Artikel</label>
                                        <input type="text" id="add_article_title" name="article_title" required
                                               placeholder="Masukkan judul artikel" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    
                                    <div>
                                        <label for="add_feature_id" class="block text-sm font-medium text-gray-700 mb-1">Terkait Layanan</label>
                                        <select id="add_feature_id" name="feature_id" 
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="">Tidak terkait dengan layanan spesifik</option>
                                            <?php foreach($features as $feature): ?>
                                            <option value="<?php echo $feature['id']; ?>"><?php echo htmlspecialchars($feature['feature_name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Gambar Artikel (Opsional)</label>
                                    <div class="flex flex-col space-y-2">
                                        <input type="file" id="add_article_image" name="article_image" 
                                               class="w-full block text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                        <p class="text-xs text-gray-500">Format: JPG, PNG, GIF, SVG, WEBP.</p>
                                    </div>
                                </div>
                                
                                <div class="mb-6">
                                    <label for="add_article_content" class="block text-sm font-medium text-gray-700 mb-1">Konten Artikel</label>
                                    <!-- Quill editor container -->
                                    <div id="add-editor-container" class="border border-gray-300 rounded-md"></div>
                                    <!-- Hidden input to store HTML content -->
                                    <input type="hidden" name="article_content" id="add_article_content_hidden">
                                </div>
                            </form>
                        </div>
                        
                        <!-- Modal Footer -->
                        <div class="sticky bottom-0 bg-white p-6 border-t border-gray-200">
                            <div class="flex justify-end space-x-3">
                                <button type="button" onclick="closeAddArticleModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                    Batal
                                </button>
                                <button type="button" onclick="submitAddArticleForm()" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    Tambah Artikel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Delete Article Modal -->
                <div id="deleteArticleModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
                    <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Hapus Artikel</h3>
                            <button type="button" onclick="closeDeleteArticleModal()" class="text-gray-400 hover:text-gray-500">
                                <i class="bx bx-x text-2xl"></i>
                            </button>
                        </div>
                        <p class="text-gray-700 mb-6">Apakah Anda yakin ingin menghapus artikel <span id="delete_article_title" class="font-semibold"></span>?</p>
                        <form method="POST" action="?tab=articles">
                            <input type="hidden" id="delete_article_id" name="article_id">
                            <div class="flex justify-end space-x-3">
                                <button type="button" onclick="closeDeleteArticleModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                    Batal
                                </button>
                                <button type="submit" name="delete_article" class="px-4 py-2 bg-red-600 text-white font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                    Hapus Artikel
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
    
    <!-- Include Quill JavaScript -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    
    <script>
        // Konstanta untuk path gambar
        const servicesImagePath = 'assets/images/uploads/services/';
        
        // Initialize Quill editor for edit mode
        let quillEditor = null;
        const editorContainer = document.getElementById('editor-container');
        if (editorContainer) {
            quillEditor = new Quill('#editor-container', {
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'script': 'sub'}, { 'script': 'super' }],
                        [{ 'indent': '-1'}, { 'indent': '+1' }],
                        [{ 'color': [] }, { 'background': [] }],
                        ['blockquote', 'code-block'],
                        ['link', 'image'],
                        ['clean']
                    ]
                },
                placeholder: 'Tulis konten artikel di sini...',
                theme: 'snow'
            });
            
            // Handle form submission to update hidden input with HTML content
            const form = editorContainer.closest('form');
            if (form) {
                form.onsubmit = function() {
                    document.getElementById('article_content_hidden').value = quillEditor.root.innerHTML;
                    return true;
                };
            }
        }
        
        // Initialize Quill editor for add mode (will be created when modal opens)
        let addQuillEditor = null;
        
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
            const form = document.getElementById('addArticleForm');
            if (form) {
                form.reset();
            }
            
            // Initialize Quill editor if not already initialized
            if (!addQuillEditor) {
                addQuillEditor = new Quill('#add-editor-container', {
                    modules: {
                        toolbar: [
                            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                            [{ 'script': 'sub'}, { 'script': 'super' }],
                            [{ 'indent': '-1'}, { 'indent': '+1' }],
                            [{ 'color': [] }, { 'background': [] }],
                            ['blockquote', 'code-block'],
                            ['link', 'image'],
                            ['clean']
                        ]
                    },
                    placeholder: 'Tulis konten artikel di sini...',
                    theme: 'snow'
                });
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
        
        function openDeleteArticleModal(id, title) {
            document.getElementById('delete_article_id').value = id;
            document.getElementById('delete_article_title').textContent = title;
            document.getElementById('deleteArticleModal').classList.remove('hidden');
        }
        
        function closeDeleteArticleModal() {
            document.getElementById('deleteArticleModal').classList.add('hidden');
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
        
        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            const modals = [
                { element: document.getElementById('editCategoryModal'), close: closeEditCategoryModal },
                { element: document.getElementById('deleteCategoryModal'), close: closeDeleteCategoryModal },
                { element: document.getElementById('addFeatureModal'), close: closeAddFeatureModal },
                { element: document.getElementById('editFeatureModal'), close: closeEditFeatureModal },
                { element: document.getElementById('deleteFeatureModal'), close: closeDeleteFeatureModal },
                { element: document.getElementById('addArticleModal'), close: closeAddArticleModal },
                { element: document.getElementById('deleteArticleModal'), close: closeDeleteArticleModal }
            ];
            
            modals.forEach(modal => {
                if (event.target === modal.element) {
                    modal.close();
                }
            });
        });
        
        // Add escape key handler for modals
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const modals = [
                    { element: document.getElementById('editCategoryModal'), close: closeEditCategoryModal },
                    { element: document.getElementById('deleteCategoryModal'), close: closeDeleteCategoryModal },
                    { element: document.getElementById('addFeatureModal'), close: closeAddFeatureModal },
                    { element: document.getElementById('editFeatureModal'), close: closeEditFeatureModal },
                    { element: document.getElementById('deleteFeatureModal'), close: closeDeleteFeatureModal },
                    { element: document.getElementById('addArticleModal'), close: closeAddArticleModal },
                    { element: document.getElementById('deleteArticleModal'), close: closeDeleteArticleModal }
                ];
                
                modals.forEach(modal => {
                    if (modal.element && !modal.element.classList.contains('hidden')) {
                        modal.close();
                    }
                });
            }
        });
    </script>
</body>
</html>