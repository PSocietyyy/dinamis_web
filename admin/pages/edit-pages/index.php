<?php
session_start();

if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../../../login.php");
    exit;
}

require_once('../../../config.php');

$message = '';
$messageType = '';
$username = $_SESSION['username'];

$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'banner';

$sections = [];
try {
    $stmt = $conn->query("SELECT * FROM homepage_sections WHERE is_active = 1 ORDER BY id");
    $sections = $stmt->fetchAll();
} catch(PDOException $e) {
    $message = "Error fetching sections: " . $e->getMessage();
    $messageType = "error";
    
    if(strpos($e->getMessage(), "Table 'akademi_merdeka.homepage_sections' doesn't exist") !== false) {
        $message = "Homepage tables not found. Please run the database setup script first.";
    }
}

$sectionNames = [
    'banner' => 'Banner & Hero',
    'stats' => 'Statistics Slider',
    'about' => 'About Us',
    'services' => 'Services',
    'contact' => 'Contact',
    'products' => 'Products',
    'testimonials' => 'Testimonials',
    'blog' => 'Blog Posts'
];

$setupNeeded = false;
try {
    $stmt = $conn->query("SELECT 1 FROM homepage_sections LIMIT 1");
    $stmt->fetchAll();
} catch(PDOException $e) {
    $setupNeeded = true;
}

if(isset($_POST['setup_homepage_db']) && $setupNeeded) {
    try {
        $setupSql = file_get_contents('homepage-db-setup.sql');
        $conn->exec($setupSql);
        
        $message = "Homepage database tables created successfully! Please refresh the page.";
        $messageType = "success";
        $setupNeeded = false;
    } catch(PDOException $e) {
        $message = "Error setting up homepage database: " . $e->getMessage();
        $messageType = "error";
    }
}
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Homepage - Akademi Merdeka</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../../assets/css/boxicons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;300;400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
    </style>
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
        <!-- Include sidebar with correct path -->
        <?php include('../../components/sidebar.php'); ?>
        
        <!-- Main Content -->
        <div class="flex-1 lg:ml-64">
            <!-- Top Bar -->
            <div class="bg-white p-4 shadow flex justify-between items-center">
                <h1 class="text-xl font-semibold text-gray-800">Homepage Editor</h1>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <span class="text-gray-600">Welcome, <?php echo htmlspecialchars($username); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Page Content -->
            <div class="p-6">
                <?php if(!empty($message)): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo $message; ?>
                </div>
                <?php endif; ?>
                
                <?php if($setupNeeded): ?>
                <!-- DB Setup Required Message -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Database Setup Required</h2>
                        <p class="mb-4">The homepage editor requires additional database tables to be set up before it can be used.</p>
                        
                        <form method="POST" action="">
                            <button type="submit" name="setup_homepage_db" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                                <i class='bx bx-data mr-2'></i> Set Up Homepage Database
                            </button>
                        </form>
                    </div>
                </div>
                <?php else: ?>
                
                <!-- Homepage Editor -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h2 class="text-lg font-semibold text-gray-800">Edit Homepage Content</h2>
                            <a href="../../../" target="_blank" class="px-4 py-2 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors">
                                <i class='bx bx-show mr-1'></i> View Homepage
                            </a>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">Customize your website's homepage content and appearance</p>
                    </div>
                    
                    <!-- Tabs Navigation -->
                    <div class="bg-gray-50 border-b border-gray-200">
                        <div class="flex overflow-x-auto">
                            <?php foreach($sections as $section): ?>
                                <button 
                                    class="tab-button px-6 py-3 text-sm font-medium whitespace-nowrap <?php echo $activeTab === $section['section_key'] ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700'; ?>"
                                    data-tab="<?php echo $section['section_key']; ?>"
                                    onclick="location.href='?tab=<?php echo $section['section_key']; ?>'">
                                    <?php echo $sectionNames[$section['section_key']] ?? $section['section_name']; ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Tab Content -->
                    <div class="p-6">
                        <?php 
                        // Include the appropriate component file based on active tab
                        $componentFile = "homepagecomp/{$activeTab}.php";
                        if(file_exists($componentFile)) {
                            include($componentFile);
                        } else {
                            echo "<div class='bg-yellow-50 p-4 rounded-lg text-yellow-700'>";
                            echo "<i class='bx bx-error-circle mr-2'></i> Component file for '{$activeTab}' section not found.";
                            echo "</div>";
                        }
                        ?>
                    </div>
                </div>
                
                <!-- Tips and Help Section -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Homepage Editing Tips</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-base font-medium text-gray-800 mb-2">General Tips</h3>
                                <ul class="space-y-2 text-sm text-gray-600">
                                    <li class="flex items-start">
                                        <i class="bx bx-check-circle text-green-500 mt-0.5 mr-2"></i>
                                        <span>Keep your content concise and engaging</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="bx bx-check-circle text-green-500 mt-0.5 mr-2"></i>
                                        <span>Use high-quality images (recommended size: 1200×800 pixels)</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="bx bx-check-circle text-green-500 mt-0.5 mr-2"></i>
                                        <span>Preview your changes to ensure they look good on the live site</span>
                                    </li>
                                </ul>
                            </div>
                            
                            <div>
                                <h3 class="text-base font-medium text-gray-800 mb-2">Image Guidelines</h3>
                                <ul class="space-y-2 text-sm text-gray-600">
                                    <li class="flex items-start">
                                        <i class="bx bx-check-circle text-green-500 mt-0.5 mr-2"></i>
                                        <span>Use relative paths (e.g., assets/images/...)</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="bx bx-check-circle text-green-500 mt-0.5 mr-2"></i>
                                        <span>Optimize images for web to improve page load speed</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="bx bx-check-circle text-green-500 mt-0.5 mr-2"></i>
                                        <span>Keep SVG icons small and simple</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Footer -->
                <div class="text-center text-gray-500 text-sm mt-6">
                    <p>&copy; <?php echo date('Y'); ?> Akademi Merdeka Admin Dashboard. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Basic tab functionality (although we're using server-side tabs)
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const tabId = this.dataset.tab;
                    
                    // Remove active class from all tabs
                    tabButtons.forEach(btn => btn.classList.remove('text-blue-600', 'border-b-2', 'border-blue-600'));
                    tabButtons.forEach(btn => btn.classList.add('text-gray-500'));
                    
                    // Add active class to current tab
                    this.classList.add('text-blue-600', 'border-b-2', 'border-blue-600');
                    this.classList.remove('text-gray-500');
                });
            });
        });
        
        // Show file name when selecting an image
        function updateFileName(input) {
            const fileName = input.files[0].name;
            const label = input.nextElementSibling;
            label.innerText = fileName;
        }
    </script>
</body>
</html>