<?php
// Determine which directory level we're at
$current_dir = dirname($_SERVER['PHP_SELF']);
$in_edit_pages = strpos($current_dir, '/edit-pages') !== false;
$in_pages = strpos($current_dir, '/pages') !== false && !$in_edit_pages;
$in_admin_root = !$in_pages && !$in_edit_pages;

// Set correct path for asset loading
if ($in_edit_pages) {
    $assets_path = "../../../assets/"; // From edit-pages to assets
} elseif ($in_pages) {
    $assets_path = "../../assets/"; // From pages to assets
} else {
    $assets_path = "../assets/"; // From admin root to assets
}
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Akademi Merdeka</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Boxicons - with correct path based on directory level -->
    <link rel="stylesheet" href="<?php echo $assets_path; ?>css/boxicons.min.css">
    <!-- Custom Tailwind Config -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;300;400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
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