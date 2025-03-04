<?php
// Get navbar data
$navbar_branding = getNavbarBranding($conn);
$navbar_menu = getNavbarMenu($conn);

// Helper function to find item by key
function findItemByKey($items, $key) {
    foreach ($items as $item) {
        if ($item['section_key'] === $key) {
            return $item;
        }
    }
    return null;
}

// Get logo items
$logo = findItemByKey($navbar_branding, 'navbar_logo');
$logo_dark = findItemByKey($navbar_branding, 'navbar_logo_dark');
$action_button = findItemByKey($navbar_branding, 'action_button');

// Start or resume session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<div class="navbar-area">
    <div class="mobile-nav">
        <a href="/" class="logo">
            <img src="<?php echo htmlspecialchars($logo['image_url'] ?? 'assets/images/logos/logo-akademi-merdeka.png'); ?>" height="64" class="logo-one" alt="Logo">
            <img src="<?php echo htmlspecialchars($logo_dark['image_url'] ?? 'assets/images/logos/logo-2.png'); ?>" height="64" class="logo-two" alt="Logo">
        </a>
    </div>
    <div class="main-nav">
        <div class="container-fluid">
            <div class="container-max">
                <nav class="navbar navbar-expand-md navbar-light">
                    <a class="navbar-brand" href="/">
                        <img src="<?php echo htmlspecialchars($logo['image_url'] ?? 'assets/images/logos/logo-akademi-merdeka.png'); ?>" height="64" class="logo-one" alt="Logo">
                        <img src="<?php echo htmlspecialchars($logo_dark['image_url'] ?? 'assets/images/logos/logo-2.png'); ?>" height="64" class="logo-two" alt="Logo">
                    </a>
                    <div class="collapse navbar-collapse mean-menu" id="navbarSupportedContent">
                        <ul class="navbar-nav m-auto">
                            <?php 
                            foreach ($navbar_menu as $menu_item): 
                                // Skip the Login menu item as we'll handle it separately
                                if ($menu_item['section_key'] === 'Login') continue;
                                
                                if ($menu_item['is_active']):
                            ?>
                                <li class="nav-item">
                                    <a href="<?php echo htmlspecialchars($menu_item['link_url']); ?>" class="nav-link <?php echo ($_SERVER['SCRIPT_NAME'] == '/' . $menu_item['link_url'] || $_SERVER['SCRIPT_NAME'] == '/index.php' && $menu_item['link_url'] == 'index.html') ? 'active' : ''; ?>">
                                        <?php echo htmlspecialchars($menu_item['content_value']); ?>
                                        <?php if ($menu_item['content_type'] === 'dropdown'): ?>
                                            <i class='bx bx-caret-down'></i>
                                        <?php endif; ?>
                                    </a>
                                    <?php if ($menu_item['content_type'] === 'dropdown'): 
                                        $dropdown_items = getNavbarDropdown($conn, $menu_item['section_key']);
                                        if (!empty($dropdown_items)):
                                    ?>
                                        <ul class="dropdown-menu">
                                            <?php foreach ($dropdown_items as $dropdown_item): ?>
                                                <?php if ($dropdown_item['is_active']): ?>
                                                    <li class="nav-item">
                                                        <a href="<?php echo htmlspecialchars($dropdown_item['link_url']); ?>" 
                                                           class="nav-link"
                                                           <?php echo (strpos($dropdown_item['link_url'], 'http') === 0) ? 'target="_blank"' : ''; ?>>
                                                            <?php echo htmlspecialchars($dropdown_item['content_value']); ?>
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; endif; ?>
                                </li>
                            <?php endif; ?>
                            <?php endforeach; ?>
                            
                            <?php 
                            // Include our specialized login component
                            include_once 'navbar-login.php';
                            ?>
                        </ul>
                        
                        <div class="nav-side d-display nav-side-mt">
                            <div class="nav-side-item">
                                <div class="get-btn">
                                    <a href="<?php echo htmlspecialchars($action_button['link_url'] ?? 'https://wa.me/6287735426107'); ?>" 
                                       class="default-btn btn-bg-two border-radius-50" 
                                       target="_blank">
                                        <?php echo htmlspecialchars($action_button['content_value'] ?? 'Konsultasi Sekarang'); ?> 
                                        <i class='bx bx-chevron-right'></i>
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
                                <a href="<?php echo htmlspecialchars($action_button['link_url'] ?? 'https://wa.me/6287735426107'); ?>" 
                                   class="default-btn btn-bg-two border-radius-50" 
                                   target="_blank">
                                    <?php echo htmlspecialchars($action_button['content_value'] ?? 'Konsultasi Sekarang'); ?> 
                                    <i class='bx bx-chevron-right'></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>