buatkan yang dihalaman admin menggunakan tailwind saja dan menggunakan sidebar components dan navbar yang berada di manage-components ke dalam manage-navbar jadi yang di manage-componnets khusus buat footer saja 

<?php
// Start the session
session_start();

// Check if not logged in
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit;
}

// Get current username
$username = $_SESSION['username'];
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Akademi Merdeka</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/boxicons.min.css">
    <style>
        .sidebar {
            background-color: #343a40;
            min-height: 100vh;
            color: white;
        }
        .nav-link {
            color: rgba(255,255,255,.75);
        }
        .nav-link:hover {
            color: rgba(255,255,255,1);
        }
        .active {
            color: white;
            font-weight: bold;
        }
        .content {
            padding: 20px;
        }
        .welcome-card {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php
            include('components/sidebar.php')
            ?>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <span class="text-muted">Welcome, <?php echo htmlspecialchars($username); ?></span>
                        </div>
                    </div>
                </div>

                <div class="content">
                    <div class="welcome-card">
                        <h4>Selamat Datang di Admin Panel</h4>
                        <p>Anda telah berhasil login sebagai administrator. Dari sini Anda dapat mengelola semua aspek website Akademi Merdeka.</p>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Users</h5>
                                    <p class="card-text">Manage users and their permissions</p>
                                    <a href="#" class="btn btn-primary">Manage Users</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Navigation Menu</h5>
                                    <p class="card-text">Edit website navigation structure</p>
                                    <a href="#" class="btn btn-primary">Edit Menu</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Content</h5>
                                    <p class="card-text">Manage pages and website content</p>
                                    <a href="#" class="btn btn-primary">Edit Content</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>