<?php
// Include database connection if not already included
if (!isset($conn)) {
    require_once './config.php';
}

// Get navbar settings from database
$navbar_settings = [
    'navbar_logo' => 'assets/images/logos/logo-akademi-merdeka.png',
    'navbar_logo_alt' => 'assets/images/logos/logo-2.png',
    'navbar_button_text' => 'Konsultasi Sekarang',
    'navbar_button_url' => 'https://wa.me/6287735426107',
    'navbar_bg_color' => '#ffffff',
    'navbar_text_color' => '#5a5c69'
];

try {
    $stmt = $conn->query("SELECT setting_key, setting_value FROM site_settings WHERE setting_group = 'navbar'");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Update settings array with database values
    foreach($settings as $key => $value) {
        $navbar_settings[$key] = $value;
    }
} catch(PDOException $e) {
    // If error, use default settings
}

// Get menu items from database
try {
    // Get parent menu items
    $stmt = $conn->query("SELECT id, title, url FROM navbar_items WHERE parent_id IS NULL AND is_active = 1 ORDER BY position");
    $parent_items = $stmt->fetchAll();
} catch(PDOException $e) {
    // If error, use default menu structure
    $parent_items = [
        ['id' => 1, 'title' => 'Home', 'url' => './index.php'],
        ['id' => 2, 'title' => 'Tentang', 'url' => '#'],
        ['id' => 3, 'title' => 'Produk', 'url' => '#'],
        ['id' => 4, 'title' => 'Layanan', 'url' => './service.php'],
        ['id' => 5, 'title' => 'Blog', 'url' => './blogs.php'],
        ['id' => 6, 'title' => 'Kontak', 'url' => './contact.php']
    ];
}

// Current page
$current_page = basename($_SERVER['PHP_SELF']);

// Custom CSS for navbar colors
$navbar_css = "
<style>
    .navbar-area {
        background-color: " . $navbar_settings['navbar_bg_color'] . ";
    }
    .navbar-area .nav-link {
        color: " . $navbar_settings['navbar_text_color'] . ";
    }
    .navbar-light .navbar-nav .nav-link:hover {
        color: #4e73df;
    }
    .navbar-light .navbar-nav .nav-link.active {
        color: #4e73df;
        font-weight: bold;
    }
</style>
";

// Output custom CSS
echo $navbar_css;
?>

<div class="navbar-area">
    <div class="mobile-nav">
        <a href="/" class="logo">
            <img src="<?php echo $navbar_settings['navbar_logo']; ?>" height="64" class="logo-one" alt="Logo">
            <img src="<?php echo $navbar_settings['navbar_logo_alt']; ?>" height="64" class="logo-two" alt="Logo">
        </a>
    </div>
    <div class="main-nav">
        <div class="container-fluid">
            <div class="container-max">
                <nav class="navbar navbar-expand-md navbar-light ">
                    <a class="navbar-brand" href="/">
                        <img src="<?php echo $navbar_settings['navbar_logo']; ?>" height="64" class="logo-one" alt="Logo">
                        <img src="<?php echo $navbar_settings['navbar_logo_alt']; ?>" height="64" class="logo-two" alt="Logo">
                    </a>
                    <div class="collapse navbar-collapse mean-menu" id="navbarSupportedContent">
                        <ul class="navbar-nav m-auto">
                            <?php foreach($parent_items as $parent): 
                                // Check if this is the active item
                                $is_active = ($current_page === basename($parent['url'])) ? 'active' : '';
                                
                                // Get child items if any
                                $has_children = false;
                                $children = [];
                                
                                try {
                                    $stmt = $conn->prepare("SELECT title, url FROM navbar_items WHERE parent_id = :parent_id AND is_active = 1 ORDER BY position");
                                    $stmt->bindParam(':parent_id', $parent['id']);
                                    $stmt->execute();
                                    $children = $stmt->fetchAll();
                                    $has_children = (count($children) > 0);
                                } catch(PDOException $e) {
                                    // If error, assume no children
                                    $has_children = false;
                                }
                            ?>
                            <li class="nav-item">
                                <a href="<?php echo $parent['url']; ?>" class="nav-link <?php echo $is_active; ?>">
                                    <?php echo htmlspecialchars($parent['title']); ?> 
                                    <?php if($has_children): ?>
                                    <i class='bx bx-caret-down'></i>
                                    <?php endif; ?>
                                </a>
                                <?php if($has_children): ?>
                                <ul class="dropdown-menu">
                                    <?php foreach($children as $child): ?>
                                    <li class="nav-item">
                                        <a href="<?php echo $child['url']; ?>" class="nav-link">
                                            <?php echo htmlspecialchars($child['title']); ?>
                                        </a>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php endif; ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>

                        <div class="nav-side d-display nav-side-mt">
                            <div class="nav-side-item">
                                <div class="get-btn">
                                    <a href="<?php echo htmlspecialchars($navbar_settings['navbar_button_url']); ?>" class="default-btn btn-bg-two border-radius-50" target="_blank">
                                        <?php echo htmlspecialchars($navbar_settings['navbar_button_text']); ?> <i class='bx bx-chevron-right'></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </div>

    <div class="side-nav-responsive">
        <div class="container-max">
            <div class="container">
                <div class="side-nav-inner">
                    <div class="side-nav justify-content-center align-items-center">
                        <div class="side-nav-item nav-side">
                            <div class="search-box"><i class='bx bx-search'></i></div>
                            <div class="get-btn">
                                <a href="<?php echo htmlspecialchars($navbar_settings['navbar_button_url']); ?>" class="default-btn btn-bg-two border-radius-50" target="_blank">
                                    <?php echo htmlspecialchars($navbar_settings['navbar_button_text']); ?> <i class='bx bx-chevron-right'></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>