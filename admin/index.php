<?php
// Start the session
session_start();

// Check if not logged in
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit;
}

// Include database connection
require_once('../config.php');

// Initialize variables
$message = '';
$messageType = '';
$currentUsername = $_SESSION['username'];
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$userData = null;

// Handle form submission for password update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $newUsername = trim($_POST['username']);
    $newPassword = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);
    $userId = (int)$_POST['user_id'];
    
    // Basic validation
    if (empty($newUsername)) {
        $message = "Username cannot be empty.";
        $messageType = "error";
    } elseif (!empty($newPassword) && $newPassword !== $confirmPassword) {
        $message = "Passwords do not match.";
        $messageType = "error";
    } else {
        try {
            // Begin transaction
            $conn->beginTransaction();
            
            // Check if username exists for another user
            if (!empty($newUsername)) {
                $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = :username AND id != :id");
                $stmt->bindParam(':username', $newUsername);
                $stmt->bindParam(':id', $userId);
                $stmt->execute();
                
                if ($stmt->fetchColumn() > 0) {
                    $message = "Username already exists. Please choose another username.";
                    $messageType = "error";
                    $conn->rollBack();
                } else {
                    // Update username
                    $stmt = $conn->prepare("UPDATE users SET username = :username WHERE id = :id");
                    $stmt->bindParam(':username', $newUsername);
                    $stmt->bindParam(':id', $userId);
                    $stmt->execute();
                    
                    // Update password if provided
                    if (!empty($newPassword)) {
                        // Hash password
                        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                        
                        // Update password
                        $stmt = $conn->prepare("UPDATE users SET password = :password WHERE id = :id");
                        $stmt->bindParam(':password', $hashedPassword);
                        $stmt->bindParam(':id', $userId);
                        $stmt->execute();
                    }
                    
                    $conn->commit();
                    $message = "User updated successfully!";
                    $messageType = "success";
                    
                    // Update session if current user is being edited
                    if ($_SESSION['user_id'] == $userId) {
                        $_SESSION['username'] = $newUsername;
                    }
                }
            }
        } catch(PDOException $e) {
            $conn->rollBack();
            $message = "Error updating user: " . $e->getMessage();
            $messageType = "error";
        }
    }
}

// Get all users
$users = [];
try {
    $stmt = $conn->query("SELECT id, username, role, created_at FROM users ORDER BY id");
    $users = $stmt->fetchAll();
} catch(PDOException $e) {
    $message = "Error fetching users: " . $e->getMessage();
    $messageType = "error";
}

// Get specific user data if ID is provided
if ($userId > 0) {
    try {
        $stmt = $conn->prepare("SELECT id, username, role, created_at FROM users WHERE id = :id");
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $userData = $stmt->fetch();
        } else {
            $message = "User not found.";
            $messageType = "error";
        }
    } catch(PDOException $e) {
        $message = "Error fetching user data: " . $e->getMessage();
        $messageType = "error";
    }
}
?>

<!doctype html>
<html lang="id">
<?php
include('components/head.php')
?>
<body class="bg-gray-50">
    <div class="min-h-screen flex flex-col lg:flex-row">
        <?php include('components/sidebar.php'); ?>
        
        <div class="flex-1 lg:ml-64">
            <div class="bg-white p-4 shadow flex justify-between items-center">
                <h1 class="text-xl font-semibold text-gray-800">Dashboard</h1>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <span class="text-gray-600">Welcome, <?php echo htmlspecialchars($currentUsername); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- User Management Content -->
            <div class="p-6">
                <?php if(!empty($message)): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo $message; ?>
                </div>
                <?php endif; ?>
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- User List -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <div class="p-6 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-800">User Accounts</h2>
                                <p class="text-sm text-gray-500 mt-1">Select a user to edit their username and password</p>
                            </div>
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php if(empty($users)): ?>
                                        <tr>
                                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No users found</td>
                                        </tr>
                                        <?php else: ?>
                                            <?php foreach($users as $user): ?>
                                            <tr class="<?php echo $userId == $user['id'] ? 'bg-blue-50' : ''; ?>">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $user['id']; ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                            <span class="text-blue-600"><?php echo strtoupper(substr($user['username'], 0, 1)); ?></span>
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['username']); ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $user['role'] === 'admin' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'; ?>">
                                                        <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <a href="?id=<?php echo $user['id']; ?>" class="text-blue-600 hover:text-blue-900">
                                                        Edit
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Edit User Form -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <div class="p-6 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-800">
                                    <?php echo $userData ? 'Edit User' : 'Select User'; ?>
                                </h2>
                            </div>
                            
                            <?php if($userData): ?>
                            <div class="p-6">
                                <form method="POST" action="">
                                    <input type="hidden" name="user_id" value="<?php echo $userData['id']; ?>">
                                    
                                    <div class="mb-4">
                                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                                        <input type="text" id="username" name="username" 
                                               value="<?php echo htmlspecialchars($userData['username']); ?>"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                        <input type="password" id="password" name="password" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">Leave blank to keep current password</p>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                                        <input type="password" id="confirm_password" name="confirm_password" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                                        <div class="bg-gray-100 px-3 py-2 rounded-md">
                                            <span class="text-gray-800">
                                                <?php echo ucfirst(htmlspecialchars($userData['role'])); ?>
                                            </span>
                                            <p class="mt-1 text-xs text-gray-500">Role cannot be changed here</p>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-6">
                                        <button type="submit" name="update_user" class="w-full px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            Update User
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <?php else: ?>
                            <div class="p-6">
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <i class="bx bx-info-circle text-blue-600 text-xl"></i>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-blue-800">No user selected</h3>
                                            <div class="mt-2 text-sm text-blue-700">
                                                <p>Please select a user from the list to edit their details.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Password Guidelines -->
                        <div class="mt-6 bg-white rounded-lg shadow-md overflow-hidden">
                            <div class="p-6">
                                <h3 class="text-base font-semibold text-gray-800 mb-2">Password Guidelines</h3>
                                <ul class="space-y-2 text-sm text-gray-600">
                                    <li class="flex items-start">
                                        <i class="bx bx-check-circle text-green-500 mt-0.5 mr-2"></i>
                                        <span>Use at least 8 characters</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="bx bx-check-circle text-green-500 mt-0.5 mr-2"></i>
                                        <span>Include uppercase and lowercase letters</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="bx bx-check-circle text-green-500 mt-0.5 mr-2"></i>
                                        <span>Include at least one number</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="bx bx-check-circle text-green-500 mt-0.5 mr-2"></i>
                                        <span>Include at least one special character</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="text-center text-gray-500 text-sm mt-6">
                    <p>&copy; 2023 Akademi Merdeka Admin Dashboard. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Password validation
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        
        // Add event listener to confirm password field to check if passwords match
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', function() {
                if (passwordInput.value !== this.value) {
                    this.setCustomValidity('Passwords do not match');
                } else {
                    this.setCustomValidity('');
                }
            });
        }
        
        // Check password match on password field change too
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                if (confirmPasswordInput.value && confirmPasswordInput.value !== this.value) {
                    confirmPasswordInput.setCustomValidity('Passwords do not match');
                } else {
                    confirmPasswordInput.setCustomValidity('');
                }
            });
        }
    </script>
</body>
</html>