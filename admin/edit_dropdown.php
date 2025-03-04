<?php
// edit_dropdown.php - For managing dropdown items from the navbar menu

session_start();

// Import database configuration and functions
require_once '../config.php';
require_once '../include/functions.php';

// Check if user is logged in
if (!isset($_SESSION['login_status']) || $_SESSION['login_status'] !== true) {
    $_SESSION['error'] = "Anda harus login terlebih dahulu!";
    header("Location: ../login.php");
    exit;
}

// Get the parent menu key
if (!isset($_GET['parent']) || empty($_GET['parent'])) {
    $_SESSION['error'] = "Menu parent tidak valid.";
    header("Location: index.php?tab=navbar_menu");
    exit;
}

$parent_key = $_GET['parent'];
$section_name = 'navbar_dropdown_' . $parent_key;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'create_dropdown_item') {
        $data = [
            'section_name' => $section_name,
            'section_key' => $_POST['menu_text'],
            'content_type' => 'link',
            'content_value' => $_POST['menu_text'],
            'link_url' => $_POST['menu_url'],
            'sort_order' => (int)$_POST['sort_order'],
            'is_active' => isset($_POST['is_active']) && $_POST['is_active'] === '1'
        ];
        
        if (createContent($conn, $data)) {
            $_SESSION['success'] = "Menu item added successfully!";
        } else {
            $_SESSION['error'] = "Failed to add menu item: " . mysqli_error($conn);
        }
    } 
    elseif ($action === 'update_dropdown_item' && isset($_POST['item_id'])) {
        $item_id = (int)$_POST['item_id'];
        $menu_text = $_POST['menu_text'];
        $menu_url = $_POST['menu_url'];
        $sort_order = (int)$_POST['sort_order'];
        $is_active = isset($_POST['is_active']) && $_POST['is_active'] === '1' ? 1 : 0;
        
        // Update multiple fields in one query
        $sql = "UPDATE page_content 
                SET section_key = ?, content_value = ?, link_url = ?, sort_order = ?, is_active = ? 
                WHERE id = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssiis", $menu_text, $menu_text, $menu_url, $sort_order, $is_active, $item_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Menu item updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update menu item: " . mysqli_error($conn);
        }
    }
    elseif ($action === 'delete_dropdown_item' && isset($_POST['item_id'])) {
        $item_id = (int)$_POST['item_id'];
        
        if (deleteContent($conn, $item_id)) {
            $_SESSION['success'] = "Menu item deleted successfully!";
        } else {
            $_SESSION['error'] = "Failed to delete menu item: " . mysqli_error($conn);
        }
    }
    
    // Redirect to prevent form resubmission
    header("Location: edit_dropdown.php?parent=" . urlencode($parent_key));
    exit;
}

// Get parent menu item info
$sql = "SELECT * FROM page_content WHERE section_name = 'navbar_menu' AND section_key = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $parent_key);
mysqli_stmt_execute($stmt);
$parent_result = mysqli_stmt_get_result($stmt);
$parent_menu = mysqli_fetch_assoc($parent_result);

if (!$parent_menu) {
    $_SESSION['error'] = "Menu parent tidak ditemukan.";
    header("Location: index.php?tab=navbar_menu");
    exit;
}

// Get dropdown items
$dropdown_items = getPageSectionContent($conn, $section_name, false);

// Sort items by sort_order
usort($dropdown_items, function($a, $b) {
    return $a['sort_order'] - $b['sort_order'];
});
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Edit Dropdown Menu - Admin Dashboard</title>
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
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
        }
        
        .card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 20px;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background-color: white;
            border-bottom: 1px solid #eee;
        }
        
        .card-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .btn-group-sm > .btn, .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        
        .alert {
            margin-bottom: 20px;
        }
        
        .table th {
            font-weight: 600;
            vertical-align: middle;
        }
        
        .table td {
            vertical-align: middle;
        }
        
        .form-group:last-child {
            margin-bottom: 0;
        }
        
        .badge {
            font-size: 85%;
            padding: 0.4em 0.6em;
        }
        
        .sortable-handle {
            cursor: move;
            color: #aaa;
        }
        
        .sortable-ghost {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Edit Dropdown Menu: <?php echo htmlspecialchars($parent_menu['content_value']); ?></h1>
            <a href="index.php?tab=navbar_menu" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Back</a>
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
        
        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Add New Dropdown Item</h5>
                    </div>
                    <div class="card-body">
                        <form action="" method="post">
                            <input type="hidden" name="action" value="create_dropdown_item">
                            
                            <div class="form-group">
                                <label for="menu_text">Menu Text:</label>
                                <input type="text" id="menu_text" name="menu_text" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="menu_url">Menu URL:</label>
                                <input type="text" id="menu_url" name="menu_url" class="form-control" required>
                                <small class="form-text text-muted">Use relative paths (e.g., about.html) or full URLs for external links.</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="sort_order">Sort Order:</label>
                                <input type="number" id="sort_order" name="sort_order" class="form-control" value="<?php echo count($dropdown_items) + 1; ?>">
                                <small class="form-text text-muted">Lower numbers appear first in the menu.</small>
                            </div>
                            
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" checked>
                                    <label class="custom-control-label" for="is_active">Active</label>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Add Item</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Dropdown Items</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($dropdown_items)): ?>
                            <div class="alert alert-info mb-0">No dropdown items found. Add your first one using the form.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="30%">Text</th>
                                            <th width="30%">URL</th>
                                            <th width="10%">Order</th>
                                            <th width="10%">Status</th>
                                            <th width="15%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="menu-items">
                                        <?php foreach ($dropdown_items as $index => $item): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td><?php echo htmlspecialchars($item['section_key']); ?></td>
                                                <td><code><?php echo htmlspecialchars($item['link_url']); ?></code></td>
                                                <td><?php echo $item['sort_order']; ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo $item['is_active'] ? 'success' : 'secondary'; ?>">
                                                        <?php echo $item['is_active'] ? 'Active' : 'Inactive'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editModal<?php echo $item['id']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    
                                                    <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal<?php echo $item['id']; ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            
                                            <!-- Edit Modal -->
                                            <div class="modal fade" id="editModal<?php echo $item['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?php echo $item['id']; ?>" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="editModalLabel<?php echo $item['id']; ?>">Edit Menu Item</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <form action="" method="post">
                                                            <div class="modal-body">
                                                                <input type="hidden" name="action" value="update_dropdown_item">
                                                                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                                                
                                                                <div class="form-group">
                                                                    <label for="menu_text<?php echo $item['id']; ?>">Menu Text:</label>
                                                                    <input type="text" id="menu_text<?php echo $item['id']; ?>" name="menu_text" class="form-control" value="<?php echo htmlspecialchars($item['section_key']); ?>" required>
                                                                </div>
                                                                
                                                                <div class="form-group">
                                                                    <label for="menu_url<?php echo $item['id']; ?>">Menu URL:</label>
                                                                    <input type="text" id="menu_url<?php echo $item['id']; ?>" name="menu_url" class="form-control" value="<?php echo htmlspecialchars($item['link_url']); ?>" required>
                                                                </div>
                                                                
                                                                <div class="form-group">
                                                                    <label for="sort_order<?php echo $item['id']; ?>">Sort Order:</label>
                                                                    <input type="number" id="sort_order<?php echo $item['id']; ?>" name="sort_order" class="form-control" value="<?php echo $item['sort_order']; ?>">
                                                                </div>
                                                                
                                                                <div class="form-group">
                                                                    <div class="custom-control custom-switch">
                                                                        <input type="checkbox" class="custom-control-input" id="is_active<?php echo $item['id']; ?>" name="is_active" value="1" <?php echo $item['is_active'] ? 'checked' : ''; ?>>
                                                                        <label class="custom-control-label" for="is_active<?php echo $item['id']; ?>">Active</label>
                                                                    </div>
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
                                            
                                            <!-- Delete Modal -->
                                            <div class="modal fade" id="deleteModal<?php echo $item['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel<?php echo $item['id']; ?>" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="deleteModalLabel<?php echo $item['id']; ?>">Confirm Delete</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Are you sure you want to delete the menu item <strong><?php echo htmlspecialchars($item['section_key']); ?></strong>?</p>
                                                            <p class="text-danger"><small>This action cannot be undone.</small></p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                            <form action="" method="post" class="d-inline">
                                                                <input type="hidden" name="action" value="delete_dropdown_item">
                                                                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
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
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
<?php
// Close database connection
mysqli_close($conn);
?>