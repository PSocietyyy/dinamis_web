<?php
// Include database connection if not already included
if (!isset($conn)) {
    require_once('config.php');
}

// Get all active menu items
$menuItems = [];
try {
    $stmt = $conn->query("SELECT * FROM navbar_items WHERE is_active = 1 ORDER BY parent_id IS NULL DESC, parent_id, order_index");
    $menuItems = $stmt->fetchAll();
} catch(PDOException $e) {
    // Handle error silently, menu will be empty
}

// Get navbar settings
$navbarSettings = [];
try {
    $stmt = $conn->query("SELECT setting_key, setting_value FROM navbar_settings");
    while ($row = $stmt->fetch()) {
        $navbarSettings[$row['setting_key']] = $row['setting_value'];
    }
} catch(PDOException $e) {
    // Handle error silently, use defaults
    $navbarSettings = [
        'logo_path' => 'assets/images/logos/logo-akademi-merdeka.png',
        'logo_height' => '64',
        'logo_alt' => 'Logo',
        'logo_two_path' => 'assets/images/logos/logo-2.png',
        'logo_two_height' => '64',
        'logo_two_alt' => 'Logo',
        'mobile_logo_path' => 'assets/images/logos/logo-akademi-merdeka.png',
        'mobile_logo_height' => '64',
        'mobile_logo_alt' => 'Logo',
        'mobile_logo_two_path' => 'assets/images/logos/logo-2.png',
        'mobile_logo_two_height' => '64',
        'mobile_logo_two_alt' => 'Logo',
        'action_button_text' => 'Konsultasi Sekarang',
        'action_button_link' => 'https://wa.me/6287735426107',
        'action_button_target' => '_blank'
    ];
}

// Function to build a tree structure of menu items
function buildMenuTree($items, $parentId = null) {
    $tree = [];
    
    foreach ($items as $item) {
        if ($item['parent_id'] == $parentId) {
            $children = buildMenuTree($items, $item['id']);
            if ($children) {
                $item['children'] = $children;
            }
            $tree[] = $item;
        }
    }
    
    return $tree;
}

// Build the menu tree
$menuTree = buildMenuTree($menuItems);

// Get the current page URL
$currentURL = $_SERVER['REQUEST_URI'];
// Remove query parameters if any
if (strpos($currentURL, '?') !== false) {
    $currentURL = substr($currentURL, 0, strpos($currentURL, '?'));
}
// Get the page name without extension
$currentPage = basename($currentURL);
// Special case for homepage
if ($currentPage === '' || $currentPage === 'index.php') {
    $currentPage = '/';
}

// Add custom CSS for active states with hover effects
echo '<style>
    /* Active state for main menu items */
    .navbar-nav .nav-item .nav-link.active {
        color: #0ea5e9 !important; /* Blue color */
        font-weight: 600;
    }
    
    /* Active state for dropdown items */
    .navbar-nav .dropdown-menu .nav-item .nav-link.active {
        background-color: rgba(14, 165, 233, 0.1); /* Light blue background */
        color: #0ea5e9 !important;
        font-weight: 600;
    }
    
    /* Hover effect for all menu items */
    .navbar-nav .nav-item .nav-link:hover {
        color: #0ea5e9 !important;
    }
    
    /* Hover effect for dropdown items */
    .navbar-nav .dropdown-menu .nav-item .nav-link:hover {
        background-color: rgba(14, 165, 233, 0.05);
    }
    
    /* Keep active state visible during hover */
    .navbar-nav .nav-item .nav-link.active:hover {
        color: #0ea5e9 !important;
    }
</style>';

// Function to check if a menu item should be marked as active
function isActiveMenuItem($item, $currentPage, $currentURL) {
    // Special case for homepage
    if ($currentPage === '/' && $item['link'] === '/') {
        return true;
    }
    
    // Clean up the item link for comparison
    $itemLink = $item['link'];
    if ($itemLink === '#') {
        // Skip dropdown parent items
        return false;
    }
    
    // Handle absolute URLs (external links)
    if (filter_var($itemLink, FILTER_VALIDATE_URL)) {
        return false; // External links are never active
    }
    
    // Remove trailing slashes for consistency
    $itemLink = rtrim($itemLink, '/');
    $currentURL = rtrim($currentURL, '/');
    
    // Check if current URL ends with the item link
    if ($itemLink !== '' && $itemLink !== '/' && (
        $currentURL === $itemLink || 
        substr($currentURL, -strlen($itemLink)) === $itemLink ||
        strpos($currentURL, $itemLink . '/') !== false
    )) {
        return true;
    }
    
    // Check if the current page matches the link name
    if ($currentPage !== '/' && $itemLink !== '' && $itemLink !== '/') {
        if ($currentPage === $itemLink || basename($itemLink) === $currentPage) {
            return true;
        }
    }
    
    return false;
}

// Function to check if any child is active (for dropdown highlighting)
function hasActiveChild($item, $currentPage, $currentURL) {
    if (!isset($item['children'])) {
        return false;
    }
    
    foreach ($item['children'] as $child) {
        if (isActiveMenuItem($child, $currentPage, $currentURL)) {
            return true;
        }
        
        // Check grandchildren if needed
        if (isset($child['children']) && hasActiveChild($child, $currentPage, $currentURL)) {
            return true;
        }
    }
    
    return false;
}
?>

<div class="navbar-area">
    <div class="mobile-nav">
        <a href="/" class="logo">
            <img src="<?php echo htmlspecialchars($navbarSettings['mobile_logo_path'] ?? 'assets/images/logos/logo-akademi-merdeka.png'); ?>" 
                 height="<?php echo (int)($navbarSettings['mobile_logo_height'] ?? 64); ?>" 
                 class="logo-one" 
                 alt="<?php echo htmlspecialchars($navbarSettings['mobile_logo_alt'] ?? 'Logo'); ?>">
            <img src="<?php echo htmlspecialchars($navbarSettings['mobile_logo_two_path'] ?? 'assets/images/logos/logo-2.png'); ?>" 
                 height="<?php echo (int)($navbarSettings['mobile_logo_two_height'] ?? 64); ?>" 
                 class="logo-two" 
                 alt="<?php echo htmlspecialchars($navbarSettings['mobile_logo_two_alt'] ?? 'Logo'); ?>">
        </a>
    </div>
    <div class="main-nav">
        <div class="container-fluid">
            <div class="container-max">
                <nav class="navbar navbar-expand-md navbar-light ">
                    <a class="navbar-brand" href="/">
                        <img src="<?php echo htmlspecialchars($navbarSettings['logo_path'] ?? 'assets/images/logos/logo-akademi-merdeka.png'); ?>" 
                             height="<?php echo (int)($navbarSettings['logo_height'] ?? 64); ?>" 
                             class="logo-one" 
                             alt="<?php echo htmlspecialchars($navbarSettings['logo_alt'] ?? 'Logo'); ?>">
                        <img src="<?php echo htmlspecialchars($navbarSettings['logo_two_path'] ?? 'assets/images/logos/logo-2.png'); ?>" 
                             height="<?php echo (int)($navbarSettings['logo_two_height'] ?? 64); ?>" 
                             class="logo-two" 
                             alt="<?php echo htmlspecialchars($navbarSettings['logo_two_alt'] ?? 'Logo'); ?>">
                    </a>
                    <div class="collapse navbar-collapse mean-menu" id="navbarSupportedContent">
                        <ul class="navbar-nav m-auto">
                            <?php foreach ($menuTree as $item): 
                                $isActive = isActiveMenuItem($item, $currentPage, $currentURL);
                                $hasActiveChild = hasActiveChild($item, $currentPage, $currentURL);
                                $activeClass = ($isActive || $hasActiveChild) ? ' active' : '';
                            ?>
                            <li class="nav-item">
                                <a href="<?php echo htmlspecialchars($item['link']); ?>" 
                                   class="nav-link<?php echo $activeClass; ?>"
                                   <?php echo $item['target'] === '_blank' ? ' target="_blank"' : ''; ?>> 
                                   <?php echo htmlspecialchars($item['title']); ?> 
                                   <?php if ($item['has_dropdown'] && isset($item['children'])): ?>
                                   <i class='bx bx-caret-down'></i>
                                   <?php endif; ?>
                                </a>
                                <?php if ($item['has_dropdown'] && isset($item['children'])): ?>
                                <ul class="dropdown-menu">
                                    <?php foreach ($item['children'] as $child): 
                                        $isChildActive = isActiveMenuItem($child, $currentPage, $currentURL);
                                    ?>
                                    <li class="nav-item">
                                        <a href="<?php echo htmlspecialchars($child['link']); ?>" 
                                           class="nav-link<?php echo $isChildActive ? ' active' : ''; ?>"
                                           <?php echo $child['target'] === '_blank' ? ' target="_blank"' : ''; ?>> 
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
                                    <a href="<?php echo htmlspecialchars($navbarSettings['action_button_link'] ?? 'https://wa.me/6287735426107'); ?>" 
                                       class="default-btn btn-bg-two border-radius-50" 
                                       target="<?php echo htmlspecialchars($navbarSettings['action_button_target'] ?? '_blank'); ?>">
                                        <?php echo htmlspecialchars($navbarSettings['action_button_text'] ?? 'Konsultasi Sekarang'); ?>
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
                            <div class="get-btn">
                                <a href="<?php echo htmlspecialchars($navbarSettings['action_button_link'] ?? 'https://wa.me/6287735426107'); ?>" 
                                   class="default-btn btn-bg-two border-radius-50" 
                                   target="<?php echo htmlspecialchars($navbarSettings['action_button_target'] ?? '_blank'); ?>">
                                    <?php echo htmlspecialchars($navbarSettings['action_button_text'] ?? 'Konsultasi Sekarang'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Add this script to ensure the parent dropdown stays highlighted when a child is active
document.addEventListener('DOMContentLoaded', function() {
    // Check if any dropdown item is active
    const activeDropdownItems = document.querySelectorAll('.dropdown-menu .nav-link.active');
    
    // If there's an active dropdown item, highlight its parent
    activeDropdownItems.forEach(function(item) {
        const parentNavItem = item.closest('.nav-item').closest('.nav-item');
        if (parentNavItem) {
            const parentLink = parentNavItem.querySelector('.nav-link');
            if (parentLink) {
                parentLink.classList.add('active');
            }
        }
    });
});
</script>