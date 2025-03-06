<?php
// Current page for highlighting active link
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="fixed inset-y-0 left-0 z-30 w-64 bg-purple-950 text-white transform transition-transform duration-300 lg:translate-x-0 shadow-lg" id="sidebar">
    <div class="flex items-center justify-center h-16 border-b border-purple-500/30">
        <div class="flex items-center px-4">
            <img src="../assets/images/logos/logo-2.png" alt="Logo" class="w-8 h-8">
            <span class="ml-3 text-lg font-semibold">Akademi Merdeka</span>
        </div>
    </div>
    
    <div class="flex flex-col h-[calc(100%-4rem)] justify-between">
        <nav class="mt-5 px-2">
            <a href="index.php" class="group flex items-center px-4 py-3 mb-1 text-white hover:bg-white/10 rounded-lg transition-all <?php echo ($current_page == 'index.php') ? 'bg-white/20 shadow-sm' : ''; ?>">
                <i class='bx bxs-dashboard text-xl mr-3'></i>
                <span>Dashboard</span>
            </a>
            
            <a href="manage-navbar.php" class="group flex items-center px-4 py-3 mb-1 text-white hover:bg-white/10 rounded-lg transition-all <?php echo ($current_page == 'manage-navbar.php') ? 'bg-white/20 shadow-sm' : ''; ?>">
                <i class='bx bxs-navigation text-xl mr-3'></i>
                <span>Navbar</span>
            </a>
            
            <a href="manage-footer.php" class="group flex items-center px-4 py-3 mb-1 text-white hover:bg-white/10 rounded-lg transition-all <?php echo ($current_page == 'manage-components.php') ? 'bg-white/20 shadow-sm' : ''; ?>">
                <i class='bx bxs-layout text-xl mr-3'></i>
                <span>Footer</span>
            </a>
            
            <div class="px-3 py-2 mt-4 text-xs uppercase text-blue-200 font-semibold">Content</div>
            
            <a href="./pages/index.php" class="group flex items-center px-4 py-3 mb-1 text-white hover:bg-white/10 rounded-lg transition-all">
                <i class='bx bxs-file text-xl mr-3'></i>
                <span>Pages</span>
            </a>
            
            <a href="#" class="group flex items-center px-4 py-3 mb-1 text-white hover:bg-white/10 rounded-lg transition-all">
                <i class='bx bxs-news text-xl mr-3'></i>
                <span>Blog</span>
            </a>
            
            <a href="#" class="group flex items-center px-4 py-3 mb-1 text-white hover:bg-white/10 rounded-lg transition-all">
                <i class='bx bxs-server text-xl mr-3'></i>
                <span>Services</span>
            </a>
        </nav>
        
        <div class="mb-8 px-4">
            <a href="logout.php" class="flex items-center px-4 py-3 text-white bg-red-500/20 hover:bg-red-500/40 rounded-lg transition-all">
                <i class='bx bx-log-out text-xl mr-3'></i>
                <span>Logout</span>
            </a>
            
            <div class="mt-4 px-4 py-3 bg-purple-700/40 text-xs rounded-lg">
                <div class="font-medium">Server Status</div>
                <div class="mt-1 flex items-center">
                    <span class="h-2 w-2 rounded-full bg-green-400 mr-2"></span>
                    <span>Online</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile sidebar button -->
<div class="fixed bottom-4 right-4 z-40 lg:hidden">
    <button id="sidebarToggle" class="p-3 bg-blue-600 rounded-full text-white shadow-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all">
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