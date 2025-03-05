<?php
// Current page for highlighting active link
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="fixed inset-y-0 left-0 z-30 w-64 bg-gradient-to-b from-blue-600 to-blue-800 text-white transform transition-transform duration-300 lg:translate-x-0" id="sidebar">
    <div class="flex items-center justify-center h-16 border-b border-blue-500">
        <div class="flex items-center">
            <img src="../assets/images/logos/logo-2.png" alt="Logo" class="w-8 h-8">
            <span class="ml-2 text-lg font-bold">Admin Panel</span>
        </div>
    </div>
    <nav class="mt-5">
        <a href="index.php" class="flex items-center px-6 py-3 text-white opacity-75 hover:opacity-100 <?php echo ($current_page == 'index.php') ? 'bg-blue-700 opacity-100 border-l-4 border-white' : ''; ?>">
            <i class='bx bxs-dashboard text-xl mr-3'></i>
            <span>Dashboard</span>
        </a>
        <a href="manage-navbar.php" class="flex items-center px-6 py-3 text-white opacity-75 hover:opacity-100 <?php echo ($current_page == 'manage-navbar.php') ? 'bg-blue-700 opacity-100 border-l-4 border-white' : ''; ?>">
            <i class='bx bxs-navigation text-xl mr-3'></i>
            <span>Navbar</span>
        </a>
        <a href="manage-components.php" class="flex items-center px-6 py-3 text-white opacity-75 hover:opacity-100 <?php echo ($current_page == 'manage-components.php') ? 'bg-blue-700 opacity-100 border-l-4 border-white' : ''; ?>">
            <i class='bx bxs-layout text-xl mr-3'></i>
            <span>Footer</span>
        </a>
        <a href="#" class="flex items-center px-6 py-3 text-white opacity-75 hover:opacity-100">
            <i class='bx bxs-file text-xl mr-3'></i>
            <span>Pages</span>
        </a>
        <div class="border-t border-blue-500 mt-6 pt-4">
            <a href="logout.php" class="flex items-center px-6 py-3 text-white opacity-75 hover:opacity-100 text-red-300 hover:text-red-200">
                <i class='bx bx-log-out text-xl mr-3'></i>
                <span>Logout</span>
            </a>
        </div>
    </nav>
</div>

<!-- Mobile sidebar button -->
<div class="fixed bottom-4 right-4 z-40 lg:hidden">
    <button id="sidebarToggle" class="p-2 bg-blue-600 rounded-full text-white shadow-lg focus:outline-none">
        <i class='bx bx-menu text-2xl'></i>
    </button>
</div>

<script>
    // Sidebar toggle for mobile
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        const sidebar = document.getElementById('sidebar');
        if (sidebar.classList.contains('-translate-x-full')) {
            sidebar.classList.remove('-translate-x-full');
        } else {
            sidebar.classList.add('-translate-x-full');
        }
    });

    // Hide sidebar by default on mobile
    if (window.innerWidth < 1024) {
        document.getElementById('sidebar').classList.add('-translate-x-full');
    }
</script>