<?php
// Try creating the upload directory structure at initialization
$rootPath = dirname(dirname(dirname(__DIR__)));
if (!file_exists($rootPath . '/assets')) {
    @mkdir($rootPath . '/assets', 0777);
}
if (!file_exists($rootPath . '/assets/uploads')) {
    @mkdir($rootPath . '/assets/uploads', 0777);
}
if (!file_exists($rootPath . '/assets/uploads/home')) {
    @mkdir($rootPath . '/assets/uploads/home', 0777);
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

// Configuration
$uploadDirectory = dirname(dirname(dirname(__DIR__))) . '/assets/uploads/home/';
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
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'banner';

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
            $absolutePath = dirname(dirname(dirname(__DIR__))) . '/assets/uploads/home/';
            if (!@mkdir($absolutePath, 0777, true)) {
                // Try creating parent directories one by one
                $rootDir = dirname(dirname(dirname(__DIR__)));
                if (!file_exists($rootDir . '/assets')) {
                    @mkdir($rootDir . '/assets', 0777);
                }
                if (!file_exists($rootDir . '/assets/uploads')) {
                    @mkdir($rootDir . '/assets/uploads', 0777);
                }
                if (!file_exists($rootDir . '/assets/uploads/home')) {
                    @mkdir($rootDir . '/assets/uploads/home', 0777);
                }
                
                if (!file_exists($absolutePath)) {
                    return [
                        'success' => false,
                        'message' => "Failed to create upload directory. Please create this directory manually: assets/uploads/home"
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
        $newFilename = 'home_' . uniqid() . '.' . $extension;
        $targetPath = $uploadDirectory . $newFilename;
        
        // Move the uploaded file
        if (@move_uploaded_file($tempFile, $targetPath)) {
            // Get the relative path for the database (from website root)
            $relativePath = 'assets/uploads/home/' . $newFilename;
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
    
    // Update Banner Section
    if (isset($_POST['update_banner'])) {
        try {
            $conn->beginTransaction();
            
            // Handle banner image upload
            $bannerImage = $_POST['banner_image'] ?? '';
            if (!empty($_FILES['banner_image_file']['name'])) {
                $uploadResult = handleImageUpload('banner_image_file', $bannerImage);
                if (!$uploadResult['success']) {
                    throw new Exception($uploadResult['message']);
                }
                $bannerImage = $uploadResult['path'];
            }
            
            // Handle shape image upload
            $shapeImage = $_POST['shape_image'] ?? '';
            if (!empty($_FILES['shape_image_file']['name'])) {
                $uploadResult = handleImageUpload('shape_image_file', $shapeImage);
                if (!$uploadResult['success']) {
                    throw new Exception($uploadResult['message']);
                }
                $shapeImage = $uploadResult['path'];
            }
            
            // Prepare data
            $title = $_POST['title'] ?? '';
            $subtitle = $_POST['subtitle'] ?? '';
            $button1Text = $_POST['button1_text'] ?? '';
            $button1Url = $_POST['button1_url'] ?? '';
            $button2Text = $_POST['button2_text'] ?? '';
            $button2Url = $_POST['button2_url'] ?? '';
            
            // Update the database structure if needed
            try {
                // Check if shape_image column exists, if not add it
                $checkColumnStmt = $conn->prepare("SHOW COLUMNS FROM home_banner LIKE 'shape_image'");
                $checkColumnStmt->execute();
                
                if ($checkColumnStmt->rowCount() == 0) {
                    // Add the shape_image column
                    $alterTableStmt = $conn->prepare("ALTER TABLE home_banner 
                        ADD COLUMN shape_image VARCHAR(255) AFTER banner_image");
                    $alterTableStmt->execute();
                }
            } catch(PDOException $e) {
                // If we can't alter the table, continue anyway - we'll use the fields that exist
            }
            
            // Update or insert banner data with only the necessary fields
            $stmt = $conn->prepare("INSERT INTO home_banner 
                             (id, title, subtitle, button1_text, button1_url, button2_text, button2_url, 
                              banner_image, shape_image) 
                             VALUES (1, :title, :subtitle, :button1_text, :button1_url, :button2_text, :button2_url,
                                    :banner_image, :shape_image)
                             ON DUPLICATE KEY UPDATE 
                             title = VALUES(title), 
                             subtitle = VALUES(subtitle), 
                             button1_text = VALUES(button1_text), 
                             button1_url = VALUES(button1_url), 
                             button2_text = VALUES(button2_text), 
                             button2_url = VALUES(button2_url), 
                             banner_image = VALUES(banner_image),
                             shape_image = VALUES(shape_image)");
            
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':subtitle', $subtitle);
            $stmt->bindParam(':button1_text', $button1Text);
            $stmt->bindParam(':button1_url', $button1Url);
            $stmt->bindParam(':button2_text', $button2Text);
            $stmt->bindParam(':button2_url', $button2Url);
            $stmt->bindParam(':banner_image', $bannerImage);
            $stmt->bindParam(':shape_image', $shapeImage);
            $stmt->execute();
            
            $conn->commit();
            $message = "Banner section updated successfully!";
            $messageType = "success";
            $activeTab = 'banner';
        } catch (Exception $e) {
            $conn->rollBack();
            $message = "Error updating banner section: " . $e->getMessage();
            $messageType = "error";
        }
    }
    
    // Update Stats Section
    elseif (isset($_POST['update_stats'])) {
        try {
            $conn->beginTransaction();
            
            // Handle stats items
            if (isset($_POST['stat_ids']) && is_array($_POST['stat_ids'])) {
                $statIds = $_POST['stat_ids'];
                $statLabels = $_POST['stat_labels'];
                $statNumbers = $_POST['stat_numbers'];
                $statOrders = $_POST['stat_orders'];
                $statActives = isset($_POST['stat_actives']) ? $_POST['stat_actives'] : [];
                $statImagePaths = $_POST['stat_image_paths'];
                
                // Process each stat
                for ($i = 0; $i < count($statIds); $i++) {
                    $id = (int)$statIds[$i];
                    $label = trim($statLabels[$i]);
                    $number = trim($statNumbers[$i]);
                    $order = (int)$statOrders[$i];
                    $isActive = in_array($id, $statActives) ? 1 : 0;
                    $imagePath = $statImagePaths[$i];
                    
                    // Handle image upload if provided
                    if (!empty($_FILES['stat_images']['name'][$i])) {
                        // Create a temporary superglobal entry for the handleImageUpload function
                        $_FILES['temp_image'] = [
                            'name' => $_FILES['stat_images']['name'][$i],
                            'type' => $_FILES['stat_images']['type'][$i],
                            'tmp_name' => $_FILES['stat_images']['tmp_name'][$i],
                            'error' => $_FILES['stat_images']['error'][$i],
                            'size' => $_FILES['stat_images']['size'][$i],
                        ];
                        
                        $uploadResult = handleImageUpload('temp_image', $imagePath);
                        if (!$uploadResult['success']) {
                            throw new Exception("Error uploading image for stat #" . ($i + 1) . ": " . $uploadResult['message']);
                        }
                        $imagePath = $uploadResult['path'];
                    }
                    
                    // Update the stat
                    $stmt = $conn->prepare("UPDATE home_stats
                                       SET count_label = :label, 
                                           count_number = :number, 
                                           display_order = :order, 
                                           is_active = :isActive,
                                           image_path = :imagePath
                                       WHERE id = :id");
                    
                    $stmt->bindParam(':label', $label);
                    $stmt->bindParam(':number', $number);
                    $stmt->bindParam(':order', $order);
                    $stmt->bindParam(':isActive', $isActive);
                    $stmt->bindParam(':imagePath', $imagePath);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }
            }
            
            // Add new stat if provided
            if (!empty($_POST['new_stat_label']) && !empty($_POST['new_stat_number'])) {
                $newLabel = trim($_POST['new_stat_label']);
                $newNumber = trim($_POST['new_stat_number']);
                $newOrder = (int)$_POST['new_stat_order'];
                $imagePath = '';
                
                // Handle image upload if provided
                if (!empty($_FILES['new_stat_image']['name'])) {
                    $uploadResult = handleImageUpload('new_stat_image');
                    if (!$uploadResult['success']) {
                        throw new Exception("Error uploading image for new stat: " . $uploadResult['message']);
                    }
                    $imagePath = $uploadResult['path'];
                }
                
                // Insert new stat
                $stmt = $conn->prepare("INSERT INTO home_stats
                                   (count_label, count_number, display_order, is_active, image_path)
                                   VALUES (:label, :number, :order, 1, :imagePath)");
                
                $stmt->bindParam(':label', $newLabel);
                $stmt->bindParam(':number', $newNumber);
                $stmt->bindParam(':order', $newOrder);
                $stmt->bindParam(':imagePath', $imagePath);
                $stmt->execute();
            }
            
            $conn->commit();
            $message = "Statistics section updated successfully!";
            $messageType = "success";
            $activeTab = 'stats';
        } catch (Exception $e) {
            $conn->rollBack();
            $message = "Error updating statistics section: " . $e->getMessage();
            $messageType = "error";
        }
    }
    
    // Update About Section
    elseif (isset($_POST['update_about'])) {
        try {
            $conn->beginTransaction();
            
            // Handle about image upload
            $aboutImage = $_POST['about_image'] ?? '';
            if (!empty($_FILES['about_image_file']['name'])) {
                $uploadResult = handleImageUpload('about_image_file', $aboutImage);
                if (!$uploadResult['success']) {
                    throw new Exception($uploadResult['message']);
                }
                $aboutImage = $uploadResult['path'];
            }
            
            // Prepare data
            $title = $_POST['title'] ?? '';
            $subtitle = $_POST['subtitle'] ?? '';
            $content = $_POST['content'] ?? '';
            $card1Icon = $_POST['card1_icon'] ?? '';
            $card1Title = $_POST['card1_title'] ?? '';
            $card1Text = $_POST['card1_text'] ?? '';
            $card2Icon = $_POST['card2_icon'] ?? '';
            $card2Title = $_POST['card2_title'] ?? '';
            $card2Text = $_POST['card2_text'] ?? '';
            
            // Update or insert about data
            $stmt = $conn->prepare("INSERT INTO home_about 
                                 (id, title, subtitle, content, image_path, card1_icon, card1_title, card1_text, card2_icon, card2_title, card2_text) 
                                 VALUES (1, :title, :subtitle, :content, :image_path, :card1_icon, :card1_title, :card1_text, :card2_icon, :card2_title, :card2_text)
                                 ON DUPLICATE KEY UPDATE 
                                 title = VALUES(title), 
                                 subtitle = VALUES(subtitle), 
                                 content = VALUES(content), 
                                 image_path = VALUES(image_path), 
                                 card1_icon = VALUES(card1_icon), 
                                 card1_title = VALUES(card1_title), 
                                 card1_text = VALUES(card1_text), 
                                 card2_icon = VALUES(card2_icon), 
                                 card2_title = VALUES(card2_title), 
                                 card2_text = VALUES(card2_text)");
            
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':subtitle', $subtitle);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':image_path', $aboutImage);
            $stmt->bindParam(':card1_icon', $card1Icon);
            $stmt->bindParam(':card1_title', $card1Title);
            $stmt->bindParam(':card1_text', $card1Text);
            $stmt->bindParam(':card2_icon', $card2Icon);
            $stmt->bindParam(':card2_title', $card2Title);
            $stmt->bindParam(':card2_text', $card2Text);
            $stmt->execute();
            
            $conn->commit();
            $message = "About section updated successfully!";
            $messageType = "success";
            $activeTab = 'about';
        } catch (Exception $e) {
            $conn->rollBack();
            $message = "Error updating about section: " . $e->getMessage();
            $messageType = "error";
        }
    }
    
    // Update CTA Section
    elseif (isset($_POST['update_cta'])) {
        try {
            $conn->beginTransaction();
            
            // Prepare data
            $title = $_POST['title'] ?? '';
            $subtitle = $_POST['subtitle'] ?? '';
            $buttonText = $_POST['button_text'] ?? '';
            $buttonUrl = $_POST['button_url'] ?? '';
            
            // Update or insert CTA data
            $stmt = $conn->prepare("INSERT INTO home_cta 
                                 (id, title, subtitle, button_text, button_url) 
                                 VALUES (1, :title, :subtitle, :button_text, :button_url)
                                 ON DUPLICATE KEY UPDATE 
                                 title = VALUES(title), 
                                 subtitle = VALUES(subtitle), 
                                 button_text = VALUES(button_text), 
                                 button_url = VALUES(button_url)");
            
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':subtitle', $subtitle);
            $stmt->bindParam(':button_text', $buttonText);
            $stmt->bindParam(':button_url', $buttonUrl);
            $stmt->execute();
            
            $conn->commit();
            $message = "Call-to-Action section updated successfully!";
            $messageType = "success";
            $activeTab = 'cta';
        } catch (Exception $e) {
            $conn->rollBack();
            $message = "Error updating CTA section: " . $e->getMessage();
            $messageType = "error";
        }
    }
    
    // Update Products Section
    elseif (isset($_POST['update_products_section'])) {
        try {
            $conn->beginTransaction();
            
            // Prepare data
            $title = $_POST['title'] ?? '';
            $subtitle = $_POST['subtitle'] ?? '';
            
            // Update or insert products section data
            $stmt = $conn->prepare("INSERT INTO home_products_section 
                                 (id, title, subtitle) 
                                 VALUES (1, :title, :subtitle)
                                 ON DUPLICATE KEY UPDATE 
                                 title = VALUES(title), 
                                 subtitle = VALUES(subtitle)");
            
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':subtitle', $subtitle);
            $stmt->execute();
            
            $conn->commit();
            $message = "Products section updated successfully!";
            $messageType = "success";
            $activeTab = 'products';
        } catch (Exception $e) {
            $conn->rollBack();
            $message = "Error updating products section: " . $e->getMessage();
            $messageType = "error";
        }
    }
    
    // Update Products Items
    elseif (isset($_POST['update_products'])) {
        try {
            $conn->beginTransaction();
            
            // Handle product items
            if (isset($_POST['product_ids']) && is_array($_POST['product_ids'])) {
                $productIds = $_POST['product_ids'];
                $productTitles = $_POST['product_titles'];
                $productOrders = $_POST['product_orders'];
                $productActives = isset($_POST['product_actives']) ? $_POST['product_actives'] : [];
                $productImagePaths = $_POST['product_image_paths'];
                
                // Process each product
                for ($i = 0; $i < count($productIds); $i++) {
                    $id = (int)$productIds[$i];
                    $title = trim($productTitles[$i]);
                    $order = (int)$productOrders[$i];
                    $isActive = in_array($id, $productActives) ? 1 : 0;
                    $imagePath = $productImagePaths[$i];
                    
                    // Handle image upload if provided
                    if (!empty($_FILES['product_images']['name'][$i])) {
                        // Create a temporary superglobal entry for the handleImageUpload function
                        $_FILES['temp_image'] = [
                            'name' => $_FILES['product_images']['name'][$i],
                            'type' => $_FILES['product_images']['type'][$i],
                            'tmp_name' => $_FILES['product_images']['tmp_name'][$i],
                            'error' => $_FILES['product_images']['error'][$i],
                            'size' => $_FILES['product_images']['size'][$i],
                        ];
                        
                        $uploadResult = handleImageUpload('temp_image', $imagePath);
                        if (!$uploadResult['success']) {
                            throw new Exception("Error uploading image for product #" . ($i + 1) . ": " . $uploadResult['message']);
                        }
                        $imagePath = $uploadResult['path'];
                    }
                    
                    // Update the product
                    $stmt = $conn->prepare("UPDATE home_products
                                       SET title = :title, 
                                           display_order = :order, 
                                           is_active = :isActive,
                                           image_path = :imagePath
                                       WHERE id = :id");
                    
                    $stmt->bindParam(':title', $title);
                    $stmt->bindParam(':order', $order);
                    $stmt->bindParam(':isActive', $isActive);
                    $stmt->bindParam(':imagePath', $imagePath);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }
            }
            
            // Add new product if provided
            if (!empty($_POST['new_product_title'])) {
                $newTitle = trim($_POST['new_product_title']);
                $newOrder = (int)$_POST['new_product_order'];
                $imagePath = '';
                
                // Handle image upload if provided
                if (!empty($_FILES['new_product_image']['name'])) {
                    $uploadResult = handleImageUpload('new_product_image');
                    if (!$uploadResult['success']) {
                        throw new Exception("Error uploading image for new product: " . $uploadResult['message']);
                    }
                    $imagePath = $uploadResult['path'];
                }
                
                // Insert new product
                $stmt = $conn->prepare("INSERT INTO home_products
                                   (title, display_order, is_active, image_path)
                                   VALUES (:title, :order, 1, :imagePath)");
                
                $stmt->bindParam(':title', $newTitle);
                $stmt->bindParam(':order', $newOrder);
                $stmt->bindParam(':imagePath', $imagePath);
                $stmt->execute();
            }
            
            $conn->commit();
            $message = "Products updated successfully!";
            $messageType = "success";
            $activeTab = 'products';
        } catch (Exception $e) {
            $conn->rollBack();
            $message = "Error updating products: " . $e->getMessage();
            $messageType = "error";
        }
    }
    
    // Update Testimonials Section
    elseif (isset($_POST['update_testimonials_section'])) {
        try {
            $conn->beginTransaction();
            
            // Prepare data
            $title = $_POST['title'] ?? '';
            $subtitle = $_POST['subtitle'] ?? '';
            
            // Update or insert testimonials section data
            $stmt = $conn->prepare("INSERT INTO home_testimonials_section 
                                 (id, title, subtitle) 
                                 VALUES (1, :title, :subtitle)
                                 ON DUPLICATE KEY UPDATE 
                                 title = VALUES(title), 
                                 subtitle = VALUES(subtitle)");
            
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':subtitle', $subtitle);
            $stmt->execute();
            
            $conn->commit();
            $message = "Testimonials section updated successfully!";
            $messageType = "success";
            $activeTab = 'testimonials';
        } catch (Exception $e) {
            $conn->rollBack();
            $message = "Error updating testimonials section: " . $e->getMessage();
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
                    $stmt = $conn->prepare("UPDATE home_testimonials
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
                $stmt = $conn->prepare("INSERT INTO home_testimonials
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
            $activeTab = 'testimonials';
        } catch (Exception $e) {
            $conn->rollBack();
            $message = "Error updating testimonials: " . $e->getMessage();
            $messageType = "error";
        }
    }
}

// Fetch data for each section
// Banner Data
$bannerData = null;
try {
    $stmt = $conn->query("SELECT * FROM home_banner WHERE id = 1 LIMIT 1");
    $bannerData = $stmt->fetch();
} catch(PDOException $e) {
    // Handle error silently
}

// Stats Data
$statsData = [];
try {
    $stmt = $conn->query("SELECT * FROM home_stats ORDER BY display_order ASC");
    $statsData = $stmt->fetchAll();
} catch(PDOException $e) {
    // Handle error silently
}

// About Data
$aboutData = null;
try {
    $stmt = $conn->query("SELECT * FROM home_about WHERE id = 1 LIMIT 1");
    $aboutData = $stmt->fetch();
} catch(PDOException $e) {
    // Handle error silently
}

// CTA Data
$ctaData = null;
try {
    $stmt = $conn->query("SELECT * FROM home_cta WHERE id = 1 LIMIT 1");
    $ctaData = $stmt->fetch();
} catch(PDOException $e) {
    // Handle error silently
}

// Products Section Data
$productsSectionData = null;
try {
    $stmt = $conn->query("SELECT * FROM home_products_section WHERE id = 1 LIMIT 1");
    $productsSectionData = $stmt->fetch();
} catch(PDOException $e) {
    // Handle error silently
}

// Products Data
$productsData = [];
try {
    $stmt = $conn->query("SELECT * FROM home_products ORDER BY display_order ASC");
    $productsData = $stmt->fetchAll();
} catch(PDOException $e) {
    // Handle error silently
}

// Testimonials Section Data
$testimonialsSectionData = null;
try {
    $stmt = $conn->query("SELECT * FROM home_testimonials_section WHERE id = 1 LIMIT 1");
    $testimonialsSectionData = $stmt->fetch();
} catch(PDOException $e) {
    // Handle error silently
}

// Testimonials Data
$testimonialsData = [];
try {
    $stmt = $conn->query("SELECT * FROM home_testimonials ORDER BY display_order ASC");
    $testimonialsData = $stmt->fetchAll();
} catch(PDOException $e) {
    // Handle error silently
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
                <h1 class="text-xl font-semibold text-gray-800">Edit Homepage</h1>
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
                        <a href="?tab=banner" class="mr-8 py-4 px-1 border-b-2 font-medium text-sm <?php echo $activeTab == 'banner' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>">
                            Banner Section
                        </a>
                        <a href="?tab=stats" class="mr-8 py-4 px-1 border-b-2 font-medium text-sm <?php echo $activeTab == 'stats' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>">
                            Stats Counters
                        </a>
                        <a href="?tab=about" class="mr-8 py-4 px-1 border-b-2 font-medium text-sm <?php echo $activeTab == 'about' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>">
                            About Section
                        </a>
                        <a href="?tab=cta" class="mr-8 py-4 px-1 border-b-2 font-medium text-sm <?php echo $activeTab == 'cta' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>">
                            Call to Action
                        </a>
                        <a href="?tab=products" class="mr-8 py-4 px-1 border-b-2 font-medium text-sm <?php echo $activeTab == 'products' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>">
                            Products Section
                        </a>
                        <a href="?tab=testimonials" class="mr-8 py-4 px-1 border-b-2 font-medium text-sm <?php echo $activeTab == 'testimonials' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>">
                            Testimonials
                        </a>
                    </nav>
                </div>
                
                <!-- Banner Section Tab -->
                <?php if ($activeTab == 'banner'): ?>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Banner Section</h2>
                        <p class="text-sm text-gray-500 mt-1">Edit the hero banner section that appears at the top of the homepage</p>
                    </div>
                    <div class="p-6">
                        <form method="POST" action="?tab=banner" enctype="multipart/form-data">
                            <!-- Text Content Settings -->
                            <div class="p-4 border border-gray-200 rounded-lg mb-6">
                                <h3 class="text-md font-medium text-gray-800 mb-4">Text Content</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Main Title</label>
                                        <input type="text" id="title" name="title" 
                                               value="<?php echo htmlspecialchars($bannerData['title'] ?? ''); ?>" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label for="subtitle" class="block text-sm font-medium text-gray-700 mb-1">Subtitle</label>
                                        <input type="text" id="subtitle" name="subtitle" 
                                               value="<?php echo htmlspecialchars($bannerData['subtitle'] ?? ''); ?>" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Button Settings -->
                            <div class="p-4 border border-gray-200 rounded-lg mb-6">
                                <h3 class="text-md font-medium text-gray-800 mb-4">Button Settings</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="button1_text" class="block text-sm font-medium text-gray-700 mb-1">Button 1 Text</label>
                                        <input type="text" id="button1_text" name="button1_text" 
                                               value="<?php echo htmlspecialchars($bannerData['button1_text'] ?? ''); ?>" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label for="button1_url" class="block text-sm font-medium text-gray-700 mb-1">Button 1 URL</label>
                                        <input type="text" id="button1_url" name="button1_url" 
                                               value="<?php echo htmlspecialchars($bannerData['button1_url'] ?? ''); ?>" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                                    <div>
                                        <label for="button2_text" class="block text-sm font-medium text-gray-700 mb-1">Button 2 Text</label>
                                        <input type="text" id="button2_text" name="button2_text" 
                                               value="<?php echo htmlspecialchars($bannerData['button2_text'] ?? ''); ?>" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label for="button2_url" class="block text-sm font-medium text-gray-700 mb-1">Button 2 URL</label>
                                        <input type="text" id="button2_url" name="button2_url" 
                                               value="<?php echo htmlspecialchars($bannerData['button2_url'] ?? ''); ?>" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Main Banner Image -->
                            <div class="p-4 border border-gray-200 rounded-lg mb-6">
                                <h3 class="text-md font-medium text-gray-800 mb-4">Main Banner Image</h3>
                                
                                <div class="flex items-start space-x-4">
                                    <div class="w-1/3">
                                        <?php $imagePath = $bannerData['banner_image'] ?? ''; ?>
                                        <div class="mb-2 bg-gray-100 p-4 rounded-lg text-center">
                                            <?php if (!empty($imagePath)): ?>
                                            <img src="../../../<?php echo htmlspecialchars($imagePath); ?>" alt="Current banner image" class="max-h-32 inline-block">
                                            <?php else: ?>
                                            <div class="text-gray-400 py-4">No image set</div>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-xs text-gray-500 text-center">Current Main Image</p>
                                    </div>
                                    
                                    <div class="w-2/3">
                                        <div class="mb-3">
                                            <label for="banner_image_file" class="block text-sm font-medium text-gray-700 mb-1">Upload New Image</label>
                                            <input type="file" id="banner_image_file" name="banner_image_file" 
                                                   class="w-full block text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                            <p class="mt-1 text-xs text-gray-500">Recommended size: 800×600px. Accepted formats: JPG, PNG, GIF, SVG, WEBP.</p>
                                        </div>
                                        
                                        <div>
                                            <label for="banner_image" class="block text-sm font-medium text-gray-700 mb-1">Or Specify Image Path</label>
                                            <input type="text" id="banner_image" name="banner_image" 
                                                   value="<?php echo htmlspecialchars($imagePath); ?>"
                                                   placeholder="assets/images/example.png"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <p class="mt-1 text-xs text-gray-500">Path relative to website root. This will be used if no file is uploaded.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Banner Shape Image -->
                            <div class="p-4 border border-gray-200 rounded-lg mb-6">
                                <h3 class="text-md font-medium text-gray-800 mb-4">Banner Shape Image</h3>
                                
                                <div class="flex items-start space-x-4">
                                    <div class="w-1/3">
                                        <?php $shapePath = $bannerData['shape_image'] ?? ''; ?>
                                        <div class="mb-2 bg-gray-100 p-4 rounded-lg text-center">
                                            <?php if (!empty($shapePath)): ?>
                                            <img src="../../../<?php echo htmlspecialchars($shapePath); ?>" alt="Current shape image" class="max-h-32 inline-block">
                                            <?php else: ?>
                                            <div class="text-gray-400 py-4">No shape image set</div>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-xs text-gray-500 text-center">Current Shape Image</p>
                                    </div>
                                    
                                    <div class="w-2/3">
                                        <div class="mb-3">
                                            <label for="shape_image_file" class="block text-sm font-medium text-gray-700 mb-1">Upload New Shape</label>
                                            <input type="file" id="shape_image_file" name="shape_image_file" 
                                                   class="w-full block text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                            <p class="mt-1 text-xs text-gray-500">Recommended size: Match your main banner dimensions. Accepted formats: PNG, SVG with transparency.</p>
                                        </div>
                                        
                                        <div>
                                            <label for="shape_image" class="block text-sm font-medium text-gray-700 mb-1">Or Specify Shape Path</label>
                                            <input type="text" id="shape_image" name="shape_image" 
                                                   value="<?php echo htmlspecialchars($shapePath); ?>"
                                                   placeholder="assets/images/shape.png"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <p class="mt-1 text-xs text-gray-500">Path relative to website root. This will be used if no file is uploaded.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-6 flex justify-end">
                                <button type="submit" name="update_banner" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Stats Section Tab -->
                <?php if ($activeTab == 'stats'): ?>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Stats Counter Section</h2>
                        <p class="text-sm text-gray-500 mt-1">Manage the statistics counters shown below the main banner</p>
                    </div>
                    <div class="p-6">
                        <form method="POST" action="?tab=stats" enctype="multipart/form-data">
                            <div class="mb-6">
                                <h3 class="text-md font-medium text-gray-900 mb-2">Current Stats</h3>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Active</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Count Number</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Label</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">New Image</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <?php if(empty($statsData)): ?>
                                            <tr>
                                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No stats counters found</td>
                                            </tr>
                                            <?php else: ?>
                                                <?php foreach($statsData as $index => $stat): ?>
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <input type="hidden" name="stat_ids[]" value="<?php echo $stat['id']; ?>">
                                                        <input type="checkbox" name="stat_actives[]" value="<?php echo $stat['id']; ?>" 
                                                               <?php echo $stat['is_active'] ? 'checked' : ''; ?> 
                                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <?php if (!empty($stat['image_path'])): ?>
                                                        <img src="../../../<?php echo htmlspecialchars($stat['image_path']); ?>" alt="Stat icon" class="h-10 w-10 object-contain">
                                                        <input type="hidden" name="stat_image_paths[]" value="<?php echo htmlspecialchars($stat['image_path']); ?>">
                                                        <?php else: ?>
                                                        <span class="text-gray-400">No image</span>
                                                        <input type="hidden" name="stat_image_paths[]" value="">
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <input type="text" name="stat_numbers[]" value="<?php echo htmlspecialchars($stat['count_number']); ?>" 
                                                               class="w-full px-3 py-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <input type="text" name="stat_labels[]" value="<?php echo htmlspecialchars($stat['count_label']); ?>" 
                                                               class="w-full px-3 py-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <input type="number" name="stat_orders[]" value="<?php echo (int)$stat['display_order']; ?>" min="1" 
                                                               class="w-20 px-3 py-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <input type="file" name="stat_images[<?php echo $index; ?>]" 
                                                               class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="mt-8 p-4 border border-gray-200 rounded-lg bg-gray-50">
                                <h3 class="text-base font-medium text-gray-900 mb-4">Add New Stat Counter</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                    <div>
                                        <label for="new_stat_number" class="block text-sm font-medium text-gray-700 mb-1">Count Number</label>
                                        <input type="text" id="new_stat_number" name="new_stat_number" placeholder="e.g. 500+" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label for="new_stat_label" class="block text-sm font-medium text-gray-700 mb-1">Label</label>
                                        <input type="text" id="new_stat_label" name="new_stat_label" placeholder="e.g. Clients" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label for="new_stat_order" class="block text-sm font-medium text-gray-700 mb-1">Order</label>
                                        <input type="number" id="new_stat_order" name="new_stat_order" value="<?php echo count($statsData) + 1; ?>" min="1" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label for="new_stat_image" class="block text-sm font-medium text-gray-700 mb-1">Icon Image</label>
                                        <input type="file" id="new_stat_image" name="new_stat_image" 
                                               class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-6 flex justify-end">
                                <button type="submit" name="update_stats" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- About Section Tab -->
                <?php if ($activeTab == 'about'): ?>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">About Section</h2>
                        <p class="text-sm text-gray-500 mt-1">Edit the "About Us" section content</p>
                    </div>
                    <div class="p-6">
                        <form method="POST" action="?tab=about" enctype="multipart/form-data">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Main Title</label>
                                    <input type="text" id="title" name="title" 
                                           value="<?php echo htmlspecialchars($aboutData['title'] ?? ''); ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="subtitle" class="block text-sm font-medium text-gray-700 mb-1">Subtitle</label>
                                    <input type="text" id="subtitle" name="subtitle" 
                                           value="<?php echo htmlspecialchars($aboutData['subtitle'] ?? ''); ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            
                            <div class="mt-6">
                                <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Main Content</label>
                                <textarea id="content" name="content" rows="4" 
                                         class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($aboutData['content'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="mt-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">About Image</label>
                                
                                <div class="flex items-start space-x-4">
                                    <div class="w-1/3">
                                        <?php $imagePath = $aboutData['image_path'] ?? ''; ?>
                                        <div class="mb-2 bg-gray-100 p-4 rounded-lg text-center">
                                            <?php if (!empty($imagePath)): ?>
                                            <img src="../../../<?php echo htmlspecialchars($imagePath); ?>" alt="About image" class="max-h-32 inline-block">
                                            <?php else: ?>
                                            <div class="text-gray-400 py-4">No image set</div>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-xs text-gray-500 text-center">Current Image</p>
                                    </div>
                                    
                                    <div class="w-2/3">
                                        <div class="mb-3">
                                            <label for="about_image_file" class="block text-sm font-medium text-gray-700 mb-1">Upload New Image</label>
                                            <input type="file" id="about_image_file" name="about_image_file" 
                                                   class="w-full block text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                            <p class="mt-1 text-xs text-gray-500">Recommended size: 600×800px. Accepted formats: JPG, PNG, GIF, SVG, WEBP.</p>
                                        </div>
                                        
                                        <div>
                                            <label for="about_image" class="block text-sm font-medium text-gray-700 mb-1">Or Specify Image Path</label>
                                            <input type="text" id="about_image" name="about_image" 
                                                   value="<?php echo htmlspecialchars($imagePath); ?>"
                                                   placeholder="assets/images/example.png"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <p class="mt-1 text-xs text-gray-500">Path relative to website root. This will be used if no file is uploaded.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Card 1 -->
                                <div class="border border-gray-200 rounded-md p-4">
                                    <h4 class="font-medium text-gray-800 mb-2">Card 1</h4>
                                    
                                    <div class="mb-3">
                                        <label for="card1_icon" class="block text-sm font-medium text-gray-700 mb-1">Icon Class</label>
                                        <input type="text" id="card1_icon" name="card1_icon" 
                                               value="<?php echo htmlspecialchars($aboutData['card1_icon'] ?? ''); ?>" 
                                               placeholder="e.g. flaticon-practice"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">Enter a Flaticon or other icon class</p>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="card1_title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                        <input type="text" id="card1_title" name="card1_title" 
                                               value="<?php echo htmlspecialchars($aboutData['card1_title'] ?? ''); ?>" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    
                                    <div>
                                        <label for="card1_text" class="block text-sm font-medium text-gray-700 mb-1">Text</label>
                                        <textarea id="card1_text" name="card1_text" rows="3" 
                                                 class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($aboutData['card1_text'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                                
                                <!-- Card 2 -->
                                <div class="border border-gray-200 rounded-md p-4">
                                    <h4 class="font-medium text-gray-800 mb-2">Card 2</h4>
                                    
                                    <div class="mb-3">
                                        <label for="card2_icon" class="block text-sm font-medium text-gray-700 mb-1">Icon Class</label>
                                        <input type="text" id="card2_icon" name="card2_icon" 
                                               value="<?php echo htmlspecialchars($aboutData['card2_icon'] ?? ''); ?>" 
                                               placeholder="e.g. flaticon-help"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">Enter a Flaticon or other icon class</p>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="card2_title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                        <input type="text" id="card2_title" name="card2_title" 
                                               value="<?php echo htmlspecialchars($aboutData['card2_title'] ?? ''); ?>" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    
                                    <div>
                                        <label for="card2_text" class="block text-sm font-medium text-gray-700 mb-1">Text</label>
                                        <textarea id="card2_text" name="card2_text" rows="3" 
                                                 class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($aboutData['card2_text'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-6 flex justify-end">
                                <button type="submit" name="update_about" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- CTA Section Tab -->
                <?php if ($activeTab == 'cta'): ?>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Call to Action Section</h2>
                        <p class="text-sm text-gray-500 mt-1">Edit the call-to-action section that appears below services</p>
                    </div>
                    <div class="p-6">
                        <form method="POST" action="?tab=cta">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Main Title</label>
                                    <input type="text" id="title" name="title" 
                                           value="<?php echo htmlspecialchars($ctaData['title'] ?? ''); ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div class="md:col-span-2">
                                    <label for="subtitle" class="block text-sm font-medium text-gray-700 mb-1">Subtitle</label>
                                    <input type="text" id="subtitle" name="subtitle" 
                                           value="<?php echo htmlspecialchars($ctaData['subtitle'] ?? ''); ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="button_text" class="block text-sm font-medium text-gray-700 mb-1">Button Text</label>
                                    <input type="text" id="button_text" name="button_text" 
                                           value="<?php echo htmlspecialchars($ctaData['button_text'] ?? ''); ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="button_url" class="block text-sm font-medium text-gray-700 mb-1">Button URL</label>
                                    <input type="text" id="button_url" name="button_url" 
                                           value="<?php echo htmlspecialchars($ctaData['button_url'] ?? ''); ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            
                            <div class="mt-6 flex justify-end">
                                <button type="submit" name="update_cta" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Products Section Tab -->
                <?php if ($activeTab == 'products'): ?>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Products Section</h2>
                        <p class="text-sm text-gray-500 mt-1">Edit the products showcase section</p>
                    </div>
                    <div class="p-6">
                        <!-- Products Section Header -->
                        <form method="POST" action="?tab=products">
                            <h3 class="text-md font-medium text-gray-900 mb-2">Section Header</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Main Title</label>
                                    <input type="text" id="title" name="title" 
                                           value="<?php echo htmlspecialchars($productsSectionData['title'] ?? ''); ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="subtitle" class="block text-sm font-medium text-gray-700 mb-1">Subtitle</label>
                                    <input type="text" id="subtitle" name="subtitle" 
                                           value="<?php echo htmlspecialchars($productsSectionData['subtitle'] ?? ''); ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            
                            <div class="flex justify-end mb-8">
                                <button type="submit" name="update_products_section" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    Save Section Header
                                </button>
                            </div>
                        </form>
                        
                        <!-- Products Items -->
                        <form method="POST" action="?tab=products" enctype="multipart/form-data">
                            <h3 class="text-md font-medium text-gray-900 mb-2">Product Items</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Active</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">New Image</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php if(empty($productsData)): ?>
                                        <tr>
                                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No products found</td>
                                        </tr>
                                        <?php else: ?>
                                            <?php foreach($productsData as $index => $product): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <input type="hidden" name="product_ids[]" value="<?php echo $product['id']; ?>">
                                                    <input type="checkbox" name="product_actives[]" value="<?php echo $product['id']; ?>" 
                                                           <?php echo $product['is_active'] ? 'checked' : ''; ?> 
                                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <?php if (!empty($product['image_path'])): ?>
                                                    <img src="../../../<?php echo htmlspecialchars($product['image_path']); ?>" alt="Product icon" class="h-10 w-10 object-contain">
                                                    <input type="hidden" name="product_image_paths[]" value="<?php echo htmlspecialchars($product['image_path']); ?>">
                                                    <?php else: ?>
                                                    <span class="text-gray-400">No image</span>
                                                    <input type="hidden" name="product_image_paths[]" value="">
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <input type="text" name="product_titles[]" value="<?php echo htmlspecialchars($product['title']); ?>" 
                                                           class="w-full px-3 py-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <input type="number" name="product_orders[]" value="<?php echo (int)$product['display_order']; ?>" min="1" 
                                                           class="w-20 px-3 py-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <input type="file" name="product_images[<?php echo $index; ?>]" 
                                                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="mt-8 p-4 border border-gray-200 rounded-lg bg-gray-50">
                                <h3 class="text-base font-medium text-gray-900 mb-4">Add New Product</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label for="new_product_title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                        <input type="text" id="new_product_title" name="new_product_title" placeholder="e.g. Journal" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label for="new_product_order" class="block text-sm font-medium text-gray-700 mb-1">Order</label>
                                        <input type="number" id="new_product_order" name="new_product_order" value="<?php echo count($productsData) + 1; ?>" min="1" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label for="new_product_image" class="block text-sm font-medium text-gray-700 mb-1">Image</label>
                                        <input type="file" id="new_product_image" name="new_product_image" 
                                               class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-6 flex justify-end">
                                <button type="submit" name="update_products" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    Save Products
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Testimonials Section Tab -->
                <?php if ($activeTab == 'testimonials'): ?>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Testimonials Section</h2>
                        <p class="text-sm text-gray-500 mt-1">Edit the testimonials section</p>
                    </div>
                    <div class="p-6">
                        <!-- Testimonials Section Header -->
                        <form method="POST" action="?tab=testimonials">
                            <h3 class="text-md font-medium text-gray-900 mb-2">Section Header</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Main Title</label>
                                    <input type="text" id="title" name="title" 
                                           value="<?php echo htmlspecialchars($testimonialsSectionData['title'] ?? ''); ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="subtitle" class="block text-sm font-medium text-gray-700 mb-1">Subtitle</label>
                                    <input type="text" id="subtitle" name="subtitle" 
                                           value="<?php echo htmlspecialchars($testimonialsSectionData['subtitle'] ?? ''); ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            
                            <div class="flex justify-end mb-8">
                                <button type="submit" name="update_testimonials_section" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    Save Section Header
                                </button>
                            </div>
                        </form>
                        
                        <!-- Testimonials Items -->
                        <form method="POST" action="?tab=testimonials" enctype="multipart/form-data">
                            <h3 class="text-md font-medium text-gray-900 mb-2">Testimonial Items</h3>
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
                                            <div class="flex items-center">
                                                <span class="mr-2 text-sm text-gray-600">Active</span>
                                                <input type="hidden" name="testimonial_ids[]" value="<?php echo $testimonial['id']; ?>">
                                                <input type="checkbox" name="testimonial_actives[]" value="<?php echo $testimonial['id']; ?>" 
                                                       <?php echo $testimonial['is_active'] ? 'checked' : ''; ?> 
                                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
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
                                    Save Testimonials
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
    
    <script>
        // Preview uploaded images
        document.addEventListener('DOMContentLoaded', function() {
            // Add any JavaScript for image previews or other interactive features here
        });
    </script>
</body>
</html>yyy