<?php
// admin/index.php - Dashboard admin with content management
session_start();

// Import database configuration
require_once '../config.php';
require_once '../include/functions.php';

// Check if user is logged in
if (!isset($_SESSION['login_status']) || $_SESSION['login_status'] !== true) {
    // Not logged in, redirect to login page
    $_SESSION['error'] = "Anda harus login terlebih dahulu!";
    header("Location: ../login.php");
    exit;
}

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");

// Get additional user information if needed
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $query);
$user_data = mysqli_fetch_assoc($result);

// Set active tab
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';

// Handle form submissions for content updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'update_content' && isset($_POST['content_id'], $_POST['content_value'])) {
        $content_id = (int)$_POST['content_id'];
        $content_value = $_POST['content_value'];
        
        if (updateContent($conn, $content_id, $content_value)) {
            $_SESSION['success'] = "Content updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update content: " . mysqli_error($conn);
        }
    } 
    elseif ($action === 'update_image' && isset($_POST['content_id']) && isset($_FILES['image'])) {
        $content_id = (int)$_POST['content_id'];
        
        // Handle image upload
        $upload_dir = '../assets/images/uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = basename($_FILES['image']['name']);
        $target_file = $upload_dir . time() . '_' . $file_name;
        $image_url = 'assets/images/uploads/' . time() . '_' . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            if (updateImageUrl($conn, $content_id, $image_url)) {
                $_SESSION['success'] = "Image updated successfully!";
            } else {
                $_SESSION['error'] = "Failed to update image in database: " . mysqli_error($conn);
            }
        } else {
            $_SESSION['error'] = "Failed to upload image.";
        }
    }
    elseif ($action === 'create_content' && isset($_POST['section_name'], $_POST['section_key'], $_POST['content_value'])) {
        $data = [
            'section_name' => $_POST['section_name'],
            'section_key' => $_POST['section_key'],
            'content_type' => $_POST['content_type'] ?? 'text',
            'content_value' => $_POST['content_value'],
            'link_url' => $_POST['link_url'] ?? '',
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'is_active' => isset($_POST['is_active']) && $_POST['is_active'] === '1'
        ];
        
        // Handle image upload if present
        if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
            $upload_dir = '../assets/images/uploads/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_name = basename($_FILES['image']['name']);
            $target_file = $upload_dir . time() . '_' . $file_name;
            $data['image_url'] = 'assets/images/uploads/' . time() . '_' . $file_name;
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $_SESSION['error'] = "Failed to upload image.";
                // Continue without image
                $data['image_url'] = '';
            }
        }
        
        if (createContent($conn, $data)) {
            $_SESSION['success'] = "New content created successfully!";
        } else {
            $_SESSION['error'] = "Failed to create content: " . mysqli_error($conn);
        }
    }
    elseif ($action === 'toggle_status' && isset($_POST['content_id'], $_POST['status'])) {
        $content_id = (int)$_POST['content_id'];
        $status = $_POST['status'] === '1';
        
        if (toggleContentStatus($conn, $content_id, $status)) {
            $_SESSION['success'] = "Status updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update status: " . mysqli_error($conn);
        }
    }
    elseif ($action === 'delete_content' && isset($_POST['content_id'])) {
        $content_id = (int)$_POST['content_id'];
        
        if (deleteContent($conn, $content_id)) {
            $_SESSION['success'] = "Content deleted successfully!";
        } else {
            $_SESSION['error'] = "Failed to delete content: " . mysqli_error($conn);
        }
    }
    elseif ($action === 'update_link' && isset($_POST['content_id'], $_POST['link_url'])) {
        $content_id = (int)$_POST['content_id'];
        $link_url = $_POST['link_url'];
        
        if (updateLinkUrl($conn, $content_id, $link_url)) {
            $_SESSION['success'] = "Link updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update link: " . mysqli_error($conn);
        }
    }
    elseif ($action === 'update_all' && isset($_POST['content_id'])) {
        $content_id = (int)$_POST['content_id'];
        $data = [
            'content_value' => $_POST['content_value'] ?? '',
            'link_url' => $_POST['link_url'] ?? '',
            'section_key' => $_POST['section_key'] ?? '',
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'is_active' => isset($_POST['is_active']) && $_POST['is_active'] === '1' ? 1 : 0
        ];
        
        if (updateContentItem($conn, $content_id, $data)) {
            $_SESSION['success'] = "Content updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update content: " . mysqli_error($conn);
        }
    }
    
    // Redirect to prevent form resubmission
    header("Location: index.php?tab=" . $active_tab);
    exit;
}

// Function to format time
function formatTime($timestamp) {
    return date('d M Y, H:i:s', $timestamp);
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Admin Dashboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --primary-dark: #2980b9;
            --secondary-color: #2ecc71;
            --secondary-dark: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #34495e;
            --sidebar-width: 250px;
            --header-height: 60px;
            --shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            --border-radius: 6px;
            --transition: all 0.3s ease;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        a {
            text-decoration: none;
            color: inherit;
        }
        
        .container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--dark-color);
            color: white;
            position: fixed;
            height: 100%;
            overflow-y: auto;
            transition: var(--transition);
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 20px;
            text-align: center;
            background-color: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .sidebar-brand i {
            margin-right: 10px;
            color: var(--primary-color);
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .menu-header {
            padding: 10px 20px;
            font-size: 0.8rem;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.6);
            letter-spacing: 1px;
        }
        
        .menu-item {
            display: block;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.8);
            border-left: 3px solid transparent;
            transition: var(--transition);
            display: flex;
            align-items: center;
        }
        
        .menu-item i {
            margin-right: 10px;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }
        
        .menu-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-left-color: var(--primary-color);
            color: white;
        }
        
        .menu-item.active {
            background-color: rgba(255, 255, 255, 0.1);
            border-left-color: var(--primary-color);
            color: white;
        }
        
        .user-profile {
            padding: 20px;
            background-color: rgba(0, 0, 0, 0.2);
            text-align: center;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            bottom: 0;
            width: 100%;
        }
        
        .profile-img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            color: white;
            font-size: 1.5rem;
        }
        
        .user-info {
            margin-bottom: 10px;
        }
        
        .user-name {
            font-weight: bold;
            font-size: 1rem;
        }
        
        .user-role {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.8rem;
            margin-top: 5px;
        }
        
        .logout-btn {
            display: inline-block;
            background-color: rgba(231, 76, 60, 0.2);
            color: var(--danger-color);
            padding: 8px 15px;
            border-radius: var(--border-radius);
            transition: var(--transition);
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
            width: 100%;
        }
        
        .logout-btn:hover {
            background-color: var(--danger-color);
            color: white;
        }
        
        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: var(--transition);
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            margin-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .page-title {
            font-size: 1.5rem;
            color: var(--dark-color);
        }
        
        .page-title i {
            margin-right: 10px;
            color: var(--primary-color);
        }
        
        .content-wrapper {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: var(--transition);
            margin-bottom: 20px;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            padding: 15px 20px;
            background-color: white;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-title {
            font-size: 1.1rem;
            color: var(--dark-color);
            font-weight: 600;
            margin: 0;
        }
        
        .card-icon {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: rgba(52, 152, 219, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
        }
        
        .card-body {
            padding: 20px;
        }
        
        .info-box {
            margin-bottom: 15px;
        }
        
        .info-title {
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--dark-color);
        }
        
        .info-value {
            color: #666;
        }

        /* Content management specific styles */
        .section-title {
            font-size: 1.2rem;
            margin-bottom: 15px;
            color: var(--dark-color);
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .content-item {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .content-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .content-key {
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .content-actions {
            display: flex;
            gap: 5px;
        }
        
        .content-item-form {
            margin-top: 10px;
        }
        
        .alert {
            border-radius: var(--border-radius);
            margin-bottom: 20px;
        }
        
        .thumbnail {
            max-width: 150px;
            max-height: 100px;
            object-fit: contain;
            margin-bottom: 10px;
            border: 1px solid #eee;
            border-radius: var(--border-radius);
        }
        
        .nav-tabs {
            margin-bottom: 20px;
        }
        
        .nav-tabs .nav-link {
            border-radius: var(--border-radius) var(--border-radius) 0 0;
        }
        
        .nav-tabs .nav-link.active {
            border-color: #dee2e6 #dee2e6 #fff;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        
        .sticky-top {
            top: 20px;
            z-index: 900;
        }
        
        /* Welcome message */
        .welcome-message {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid var(--primary-color);
        }
        
        .welcome-title {
            font-size: 1.3rem;
            margin-bottom: 10px;
            color: var(--dark-color);
        }
        
        /* Footer Styles */
        .footer {
            text-align: center;
            padding: 20px 0;
            margin-top: 20px;
            color: #777;
            font-size: 0.9rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .sidebar.active {
                width: var(--sidebar-width);
                transform: translateX(0);
            }
            
            .toggle-btn {
                display: block;
            }
            
            .content-wrapper {
                grid-template-columns: 1fr;
            }
        }
        
        /* Toggle button for mobile */
        .toggle-btn {
            position: fixed;
            left: 10px;
            top: 10px;
            z-index: 1001;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: var(--shadow);
        }
        
        @media (max-width: 768px) {
            .toggle-btn {
                display: flex;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Toggle Button -->
    <button class="toggle-btn" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-brand">
                    <i class="fas fa-shield-alt"></i>
                    <span>AdminPanel</span>
                </div>
            </div>
            
            <div class="sidebar-menu">
                <div class="menu-header">Konten Website</div>
                <a href="?tab=dashboard" class="menu-item <?php echo $active_tab === 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                
                <div class="menu-header">Header & Footer</div>
                <a href="?tab=navbar" class="menu-item <?php echo $active_tab === 'navbar' ? 'active' : ''; ?>">
                    <i class="fas fa-bars"></i>
                    <span>Navbar</span>
                </a>
                <a href="?tab=navbar_menu" class="menu-item <?php echo $active_tab === 'navbar_menu' ? 'active' : ''; ?>">
                    <i class="fas fa-list"></i>
                    <span>Navbar Menu</span>
                </a>
                <a href="?tab=footer" class="menu-item <?php echo $active_tab === 'footer' ? 'active' : ''; ?>">
                    <i class="fas fa-shoe-prints"></i>
                    <span>Footer</span>
                </a>
                <a href="?tab=footer_services" class="menu-item <?php echo $active_tab === 'footer_services' ? 'active' : ''; ?>">
                    <i class="fas fa-concierge-bell"></i>
                    <span>Footer Services</span>
                </a>
                <a href="?tab=footer_blog" class="menu-item <?php echo $active_tab === 'footer_blog' ? 'active' : ''; ?>">
                    <i class="fas fa-newspaper"></i>
                    <span>Footer Blog</span>
                </a>
                
                <div class="menu-header">Main Content</div>
                <a href="?tab=hero" class="menu-item <?php echo $active_tab === 'hero' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i>
                    <span>Hero Section</span>
                </a>
                <a href="?tab=about" class="menu-item <?php echo $active_tab === 'about' ? 'active' : ''; ?>">
                    <i class="fas fa-info-circle"></i>
                    <span>About Section</span>
                </a>
                <a href="?tab=service" class="menu-item <?php echo $active_tab === 'service' ? 'active' : ''; ?>">
                    <i class="fas fa-cogs"></i>
                    <span>Services</span>
                </a>
                <a href="?tab=product" class="menu-item <?php echo $active_tab === 'product' ? 'active' : ''; ?>">
                    <i class="fas fa-box"></i>
                    <span>Products</span>
                </a>
                <a href="?tab=testimonial" class="menu-item <?php echo $active_tab === 'testimonial' ? 'active' : ''; ?>">
                    <i class="fas fa-quote-right"></i>
                    <span>Testimonials</span>
                </a>
                <a href="?tab=blog" class="menu-item <?php echo $active_tab === 'blog' ? 'active' : ''; ?>">
                    <i class="fas fa-blog"></i>
                    <span>Blog</span>
                </a>
                <a href="?tab=stats" class="menu-item <?php echo $active_tab === 'stats' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line"></i>
                    <span>Statistics</span>
                </a>
                <a href="?tab=cta" class="menu-item <?php echo $active_tab === 'cta' ? 'active' : ''; ?>">
                    <i class="fas fa-bullhorn"></i>
                    <span>Call to Action</span>
                </a>
                
                <div class="menu-header">Manajemen</div>
                <a href="?tab=settings" class="menu-item <?php echo $active_tab === 'settings' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
                <a href="../index.php" class="menu-item" target="_blank">
                    <i class="fas fa-eye"></i>
                    <span>View Site</span>
                </a>
            </div>
            
            <div class="user-profile">
                <div class="profile-img">
                    <i class="fas fa-user"></i>
                </div>
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($_SESSION['nama']); ?></div>
                    <div class="user-role"><?php echo htmlspecialchars($_SESSION['role']); ?></div>
                </div>
                <a href="../logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <div class="page-title">
                    <i class="fas fa-tachometer-alt"></i> <?php echo ucfirst($active_tab); ?>
                </div>
                <div class="header-actions">
                    <span><?php echo date('l, d F Y'); ?></span>
                </div>
            </div>
            
            <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php endif; ?>
            
            <?php if ($active_tab === 'dashboard'): ?>
                <div class="welcome-message">
                    <h3 class="welcome-title">Selamat datang, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</h3>
                    <p>Gunakan panel admin ini untuk mengelola konten di halaman website Anda. Pilih bagian yang ingin diubah dari menu sebelah kiri.</p>
                </div>
                
                <!-- Quick Stats Overview -->
                <div class="row">
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <div class="display-4 text-primary">
                                    <i class="fas fa-home"></i>
                                </div>
                                <h5 class="card-title">Hero Section</h5>
                                <a href="?tab=hero" class="btn btn-sm btn-primary">Edit</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <div class="display-4 text-success">
                                    <i class="fas fa-cogs"></i>
                                </div>
                                <h5 class="card-title">Services</h5>
                                <a href="?tab=service" class="btn btn-sm btn-success">Edit</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <div class="display-4 text-warning">
                                    <i class="fas fa-quote-right"></i>
                                </div>
                                <h5 class="card-title">Testimonials</h5>
                                <a href="?tab=testimonial" class="btn btn-sm btn-warning">Edit</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <div class="display-4 text-danger">
                                    <i class="fas fa-blog"></i>
                                </div>
                                <h5 class="card-title">Blog</h5>
                                <a href="?tab=blog" class="btn btn-sm btn-danger">Edit</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Navigation Management -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Header & Footer</h5>
                                <div class="card-icon">
                                    <i class="fas fa-bars"></i>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6">
                                        <a href="?tab=navbar" class="btn btn-block btn-outline-primary mb-3">
                                            <i class="fas fa-bars mr-2"></i> Navbar
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="?tab=navbar_menu" class="btn btn-block btn-outline-info mb-3">
                                            <i class="fas fa-list mr-2"></i> Menu Items
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="?tab=footer" class="btn btn-block btn-outline-secondary mb-3">
                                            <i class="fas fa-shoe-prints mr-2"></i> Footer
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="?tab=footer_services" class="btn btn-block btn-outline-dark">
                                            <i class="fas fa-concierge-bell mr-2"></i> Footer Links
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- User Information Card -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Informasi Akun</h5>
                                <div class="card-icon">
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="info-box">
                                    <div class="info-title">Username</div>
                                    <div class="info-value"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                                </div>
                                
                                <div class="info-box">
                                    <div class="info-title">Nama Lengkap</div>
                                    <div class="info-value"><?php echo htmlspecialchars($_SESSION['nama']); ?></div>
                                </div>
                                
                                <div class="info-box">
                                    <div class="info-title">Role</div>
                                    <div class="info-value"><?php echo htmlspecialchars($_SESSION['role']); ?></div>
                                </div>
                                
                                <div class="info-box mb-0">
                                    <div class="info-title">Login Terakhir</div>
                                    <div class="info-value"><?php echo formatTime($_SESSION['login_time']); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php elseif ($active_tab === 'navbar_menu'): ?>
                <!-- Navbar Menu Management Interface -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Manage Navbar Menu</h5>
                        <a href="#addMenuModal" data-toggle="modal" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Add Menu Item
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Text</th>
                                        <th>URL/Type</th>
                                        <th>Order</th>
                                        <th>Status</th>
                                        <th width="200">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $menu_items = getPageSectionContent($conn, 'navbar_menu', false);
                                    usort($menu_items, function($a, $b) {
                                        return $a['sort_order'] - $b['sort_order'];
                                    });
                                    
                                    foreach ($menu_items as $item):
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['content_value']); ?></td>
                                        <td>
                                            <?php if ($item['content_type'] === 'dropdown'): ?>
                                                <span class="badge badge-info">Dropdown Menu</span>
                                            <?php else: ?>
                                                <?php echo htmlspecialchars($item['link_url']); ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $item['sort_order']; ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $item['is_active'] ? 'success' : 'secondary'; ?>">
                                                <?php echo $item['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editMenuModal<?php echo $item['id']; ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            
                                            <?php if ($item['content_type'] === 'dropdown'): ?>
                                                <a href="edit_dropdown.php?parent=<?php echo urlencode($item['section_key']); ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-list"></i> Items
                                                </a>
                                            <?php endif; ?>
                                            
                                            <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteMenuModal<?php echo $item['id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    
                                    <!-- Edit Menu Modal -->
                                    <div class="modal fade" id="editMenuModal<?php echo $item['id']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Menu Item</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <form action="" method="post">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="action" value="update_all">
                                                        <input type="hidden" name="content_id" value="<?php echo $item['id']; ?>">
                                                        
                                                        <div class="form-group">
                                                            <label>Menu Text:</label>
                                                            <input type="text" name="content_value" class="form-control" value="<?php echo htmlspecialchars($item['content_value']); ?>" required>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <label>Menu Type:</label>
                                                            <select class="form-control" disabled>
                                                                <option value="link" <?php echo $item['content_type'] === 'link' ? 'selected' : ''; ?>>Link</option>
                                                                <option value="dropdown" <?php echo $item['content_type'] === 'dropdown' ? 'selected' : ''; ?>>Dropdown</option>
                                                            </select>
                                                            <small class="form-text text-muted">Menu type cannot be changed. To change type, delete and create a new menu item.</small>
                                                        </div>
                                                        
                                                        <?php if ($item['content_type'] === 'link'): ?>
                                                        <div class="form-group">
                                                            <label>URL:</label>
                                                            <input type="text" name="link_url" class="form-control" value="<?php echo htmlspecialchars($item['link_url']); ?>" required>
                                                        </div>
                                                        <?php endif; ?>
                                                        
                                                        <div class="form-group">
                                                            <label>Sort Order:</label>
                                                            <input type="number" name="sort_order" class="form-control" value="<?php echo $item['sort_order']; ?>">
                                                        </div>
                                                        
                                                        <div class="form-group form-check">
                                                            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="isActiveEdit<?php echo $item['id']; ?>" <?php echo $item['is_active'] ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="isActiveEdit<?php echo $item['id']; ?>">Active</label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Delete Menu Modal -->
                                    <div class="modal fade" id="deleteMenuModal<?php echo $item['id']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Confirm Delete</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Are you sure you want to delete the menu item <strong><?php echo htmlspecialchars($item['content_value']); ?></strong>?</p>
                                                    <?php if ($item['content_type'] === 'dropdown'): ?>
                                                        <div class="alert alert-warning">
                                                            <i class="fas fa-exclamation-triangle"></i> Warning: This will also delete all dropdown menu items associated with this menu!
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                    <form action="" method="post">
                                                        <input type="hidden" name="action" value="delete_content">
                                                        <input type="hidden" name="content_id" value="<?php echo $item['id']; ?>">
                                                        <button type="submit" class="btn btn-danger">Delete</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Add Menu Modal -->
                <div class="modal fade" id="addMenuModal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Add New Menu Item</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form action="" method="post">
                                <div class="modal-body">
                                    <input type="hidden" name="action" value="create_content">
                                    <input type="hidden" name="section_name" value="navbar_menu">
                                    
                                    <div class="form-group">
                                        <label for="menuText">Menu Text:</label>
                                        <input type="text" id="menuText" name="content_value" class="form-control" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="menuType">Menu Type:</label>
                                        <select id="menuType" name="content_type" class="form-control">
                                            <option value="link">Link</option>
                                            <option value="dropdown">Dropdown</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group" id="urlField">
                                        <label for="menuUrl">URL:</label>
                                        <input type="text" id="menuUrl" name="link_url" class="form-control">
                                        <small class="form-text text-muted">For dropdowns, leave blank or use "#"</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="menuKey">Menu Key:</label>
                                        <input type="text" id="menuKey" name="section_key" class="form-control" required>
                                        <small class="form-text text-muted">A unique identifier (e.g., "Home", "About"). For dropdowns, this is important as it's used to link dropdown items.</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="menuOrder">Sort Order:</label>
                                        <input type="number" id="menuOrder" name="sort_order" class="form-control" value="<?php echo count($menu_items) + 1; ?>">
                                    </div>
                                    
                                    <div class="form-group form-check">
                                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="isActive" checked>
                                        <label class="form-check-label" for="isActive">Active</label>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Add Menu Item</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php elseif (in_array($active_tab, ['navbar', 'hero', 'about', 'service', 'product', 'testimonial', 'blog', 'stats', 'cta', 'footer', 'footer_services', 'footer_blog', 'settings'])): ?>
                <!-- Content Management Interface -->
                <div class="row">
                    <!-- Add New Content Form -->
                    <div class="col-lg-4">
                        <div class="card sticky-top">
                            <div class="card-header">
                                <h5 class="card-title">Add New Content</h5>
                            </div>
                            <div class="card-body">
                                <form action="" method="post" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="create_content">
                                    <input type="hidden" name="section_name" value="<?php echo htmlspecialchars($active_tab); ?>">
                                    
                                    <div class="form-group">
                                        <label for="section_key">Content Key:</label>
                                        <input type="text" id="section_key" name="section_key" class="form-control" required>
                                        <small class="form-text text-muted">A unique identifier for this content (e.g., "hero_title")</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="content_type">Content Type:</label>
                                        <select id="content_type" name="content_type" class="form-control">
                                            <option value="text">Text</option>
                                            <option value="html">HTML</option>
                                            <option value="image">Image</option>
                                            <option value="link">Link</option>
                                            <option value="button">Button</option>
                                            <option value="icon">Icon</option>
                                            <option value="blog">Blog Post</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="content_value">Content Value:</label>
                                        <textarea id="content_value" name="content_value" class="form-control" rows="4"></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="image">Image (if applicable):</label>
                                        <input type="file" id="image" name="image" class="form-control-file">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="link_url">Link URL (if applicable):</label>
                                        <input type="text" id="link_url" name="link_url" class="form-control">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="sort_order">Sort Order:</label>
                                        <input type="number" id="sort_order" name="sort_order" class="form-control" value="0">
                                    </div>
                                    
                                    <div class="form-group form-check">
                                        <input type="checkbox" id="is_active" name="is_active" value="1" class="form-check-input" checked>
                                        <label for="is_active" class="form-check-label">Active</label>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">Add Content</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Existing Content List -->
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Manage <?php echo ucfirst($active_tab); ?> Content</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $content_items = getEditableSectionContent($conn, $active_tab);
                                if (empty($content_items)):
                                ?>
                                <div class="alert alert-info">
                                    No content items found for this section. Add your first one using the form.
                                </div>
                                <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Key</th>
                                                <th>Value</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($content_items as $item): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['section_key']); ?></td>
                                                <td>
                                                    <?php if ($item['content_type'] === 'image' && !empty($item['image_url'])): ?>
                                                        <img src="../<?php echo htmlspecialchars($item['image_url']); ?>" alt="Preview" class="thumbnail"><br>
                                                    <?php endif; ?>
                                                    <?php 
                                                    $preview = strlen($item['content_value']) > 100 
                                                        ? htmlspecialchars(substr($item['content_value'], 0, 100)) . '...' 
                                                        : htmlspecialchars($item['content_value']);
                                                    echo $preview; 
                                                    ?>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?php echo $item['is_active'] ? 'success' : 'secondary'; ?>">
                                                        <?php echo $item['is_active'] ? 'Active' : 'Inactive'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editModal<?php echo $item['id']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    
                                                    <form action="" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to change the status?');">
                                                        <input type="hidden" name="action" value="toggle_status">
                                                        <input type="hidden" name="content_id" value="<?php echo $item['id']; ?>">
                                                        <input type="hidden" name="status" value="<?php echo $item['is_active'] ? '0' : '1'; ?>">
                                                        <button type="submit" class="btn btn-sm btn-<?php echo $item['is_active'] ? 'warning' : 'success'; ?>">
                                                            <i class="fas fa-<?php echo $item['is_active'] ? 'times' : 'check'; ?>"></i>
                                                        </button>
                                                    </form>
                                                    
                                                    <form action="" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this content? This cannot be undone.');">
                                                        <input type="hidden" name="action" value="delete_content">
                                                        <input type="hidden" name="content_id" value="<?php echo $item['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            
                                            <!-- Edit Modal -->
                                            <div class="modal fade" id="editModal<?php echo $item['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?php echo $item['id']; ?>" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="editModalLabel<?php echo $item['id']; ?>">Edit Content</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <form action="" method="post" enctype="multipart/form-data">
                                                                <input type="hidden" name="action" value="update_all">
                                                                <input type="hidden" name="content_id" value="<?php echo $item['id']; ?>">
                                                                
                                                                <div class="form-group">
                                                                    <label>Content Type:</label>
                                                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($item['content_type']); ?>" readonly>
                                                                </div>
                                                                
                                                                <div class="form-group">
                                                                    <label>Content Key:</label>
                                                                    <input type="text" name="section_key" class="form-control" value="<?php echo htmlspecialchars($item['section_key']); ?>">
                                                                </div>
                                                                
                                                                <div class="form-group">
                                                                    <label for="content_value<?php echo $item['id']; ?>">Content Value:</label>
                                                                    <textarea id="content_value<?php echo $item['id']; ?>" name="content_value" class="form-control" rows="5"><?php echo htmlspecialchars($item['content_value']); ?></textarea>
                                                                </div>
                                                                
                                                                <?php if (in_array($item['content_type'], ['link', 'button'])): ?>
                                                                <div class="form-group">
                                                                    <label for="link_url<?php echo $item['id']; ?>">URL:</label>
                                                                    <input type="text" id="link_url<?php echo $item['id']; ?>" name="link_url" class="form-control" value="<?php echo htmlspecialchars($item['link_url']); ?>">
                                                                </div>
                                                                <?php endif; ?>
                                                                
                                                                <div class="form-group">
                                                                    <label for="sort_order<?php echo $item['id']; ?>">Sort Order:</label>
                                                                    <input type="number" id="sort_order<?php echo $item['id']; ?>" name="sort_order" class="form-control" value="<?php echo $item['sort_order']; ?>">
                                                                </div>
                                                                
                                                                <div class="form-group form-check">
                                                                    <input type="checkbox" id="is_active<?php echo $item['id']; ?>" name="is_active" value="1" class="form-check-input" <?php echo $item['is_active'] ? 'checked' : ''; ?>>
                                                                    <label for="is_active<?php echo $item['id']; ?>" class="form-check-label">Active</label>
                                                                </div>
                                                                
                                                                <button type="submit" class="btn btn-primary">Update Content</button>
                                                            </form>
                                                            
                                                            <?php if (in_array($item['content_type'], ['image'])): ?>
                                                            <hr>
                                                            <form action="" method="post" enctype="multipart/form-data">
                                                                <input type="hidden" name="action" value="update_image">
                                                                <input type="hidden" name="content_id" value="<?php echo $item['id']; ?>">
                                                                
                                                                <div class="form-group">
                                                                    <label for="image<?php echo $item['id']; ?>">Update Image:</label>
                                                                    <?php if (!empty($item['image_url'])): ?>
                                                                        <div class="mb-2">
                                                                            <img src="../<?php echo htmlspecialchars($item['image_url']); ?>" alt="Current Image" class="thumbnail">
                                                                        </div>
                                                                    <?php endif; ?>
                                                                    <input type="file" id="image<?php echo $item['id']; ?>" name="image" class="form-control-file" required>
                                                                </div>
                                                                
                                                                <button type="submit" class="btn btn-info">Update Image</button>
                                                            </form>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="footer">
                &copy; <?php echo date('Y'); ?> Admin Dashboard. All rights reserved.
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
    <script>
    // JavaScript for sidebar toggle and preventing back navigation after logout
    (function() {
        // Sidebar toggle for mobile
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });
        }
        
        // Menu type toggle
        const menuType = document.getElementById('menuType');
        const urlField = document.getElementById('urlField');
        
        if (menuType && urlField) {
            menuType.addEventListener('change', function() {
                if (this.value === 'dropdown') {
                    urlField.style.opacity = '0.5';
                    document.getElementById('menuUrl').value = '#';
                } else {
                    urlField.style.opacity = '1';
                    document.getElementById('menuUrl').value = '';
                }
            });
        }
        
        // Prevent back navigation after logout
        window.history.pushState(null, '', window.location.href);
        window.addEventListener('popstate', function() {
            window.history.pushState(null, '', window.location.href);
        });
    })();
    </script>
</body>
</html>
<?php
// Close database connection
mysqli_close($conn);
?>