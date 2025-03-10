<?php
// Start the session
session_start();

// Check if not logged in
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../../../login.php");
    exit;
}

// Include database connection
require_once('../../../config.php');

// Initialize variables
$message = '';
$messageType = '';
$username = $_SESSION['username'] ?? 'Admin';

// Create tables if they don't exist
try {
    // Check if contact_page_settings table exists
    $stmt = $conn->prepare("SHOW TABLES LIKE 'contact_page_settings'");
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        // Create contact_page_settings table
        $sql = "CREATE TABLE contact_page_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $conn->exec($sql);
        
        // Insert default settings
        $defaultSettings = [
            ['page_title', 'Kontak'],
            ['form_title', 'Ada Pertanyaan? Silahkan lengkapi form dibawah ini'],
            ['contact_section_title', 'Hubungi Kami'],
            ['contact_section_subtitle', 'Mari bergabung bersama kami'],
            ['company_name', 'Akademi Merdeka Office:'],
            ['address', 'Perumahan Kheandra Kalijaga<br>Harjamukti, Cirebon, Jawa Barat'],
            ['phone', '+62 877-3542-6107'],
            ['email', 'info@akademimerdeka.com'],
            ['map_embed_url', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3962.019486213893!2d108.54767291372046!3d-6.767477868057374!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6e7d988b9404fb0f%3A0x5acb2b6afbaeac6f!2sAkademi%20Merdeka!5e0!3m2!1sid!2sid!4v1678334730904!5m2!1sid!2sid']
        ];
        
        $stmt = $conn->prepare("INSERT INTO contact_page_settings (setting_key, setting_value) VALUES (:key, :value)");
        foreach ($defaultSettings as $setting) {
            $stmt->bindValue(':key', $setting[0]);
            $stmt->bindValue(':value', $setting[1]);
            $stmt->execute();
        }
    }
    
    // Check if contact_form_fields table exists
    $stmt = $conn->prepare("SHOW TABLES LIKE 'contact_form_fields'");
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        // Create contact_form_fields table
        $sql = "CREATE TABLE contact_form_fields (
            id INT AUTO_INCREMENT PRIMARY KEY,
            field_name VARCHAR(50) NOT NULL,
            field_label VARCHAR(100) NOT NULL,
            field_type ENUM('text', 'email', 'tel', 'textarea', 'checkbox') NOT NULL,
            placeholder TEXT,
            is_required BOOLEAN NOT NULL DEFAULT TRUE,
            position INT NOT NULL DEFAULT 0,
            is_active BOOLEAN NOT NULL DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $conn->exec($sql);
        
        // Insert default form fields
        $defaultFields = [
            ['name', 'Nama', 'text', 'Nama', 1, 1, 1],
            ['email', 'Email', 'email', 'Email', 1, 2, 1],
            ['phone_number', 'Telepon/Whatsapp', 'tel', 'Telepon/Whatsapp', 1, 3, 1],
            ['msg_subject', 'Judul Pesan', 'text', 'Judul Pesan', 1, 4, 1],
            ['message', 'Detail Pesan', 'textarea', 'Detail Pesan', 1, 5, 1],
            ['agreement', 'Saya menyetujui <a href="terms-condition.html">Syarat & Ketentuan</a> dan <a href="privacy-policy.html">Kebijakan Privasi.</a>', 'checkbox', '', 0, 6, 1]
        ];
        
        $stmt = $conn->prepare("INSERT INTO contact_form_fields (field_name, field_label, field_type, placeholder, is_required, position, is_active) VALUES (:name, :label, :type, :placeholder, :required, :position, :active)");
        foreach ($defaultFields as $field) {
            $stmt->bindValue(':name', $field[0]);
            $stmt->bindValue(':label', $field[1]);
            $stmt->bindValue(':type', $field[2]);
            $stmt->bindValue(':placeholder', $field[3]);
            $stmt->bindValue(':required', $field[4]);
            $stmt->bindValue(':position', $field[5]);
            $stmt->bindValue(':active', $field[6]);
            $stmt->execute();
        }
    }
    
    // Create contact_submissions table if it doesn't exist
    $stmt = $conn->prepare("SHOW TABLES LIKE 'contact_submissions'");
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        $sql = "CREATE TABLE contact_submissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            phone_number VARCHAR(50),
            subject VARCHAR(255),
            message TEXT,
            ip_address VARCHAR(45),
            user_agent TEXT,
            status ENUM('new', 'read', 'replied', 'archived') DEFAULT 'new',
            submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $conn->exec($sql);
    }
} catch(PDOException $e) {
    $message = "Database setup error: " . $e->getMessage();
    $messageType = "error";
}

// Get current settings
$settings = [];
try {
    $stmt = $conn->query("SELECT setting_key, setting_value FROM contact_page_settings");
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch(PDOException $e) {
    $message = "Error fetching settings: " . $e->getMessage();
    $messageType = "error";
}

// Get form fields
$formFields = [];
try {
    $stmt = $conn->query("SELECT * FROM contact_form_fields ORDER BY position");
    $formFields = $stmt->fetchAll();
} catch(PDOException $e) {
    $message = "Error fetching form fields: " . $e->getMessage();
    $messageType = "error";
}

// Handle general settings update
if (isset($_POST['update_settings'])) {
    try {
        $conn->beginTransaction();
        
        // Update each setting
        $updateStmt = $conn->prepare("UPDATE contact_page_settings SET setting_value = :value WHERE setting_key = :key");
        
        foreach ($_POST['settings'] as $key => $value) {
            $updateStmt->bindParam(':key', $key);
            $updateStmt->bindParam(':value', $value);
            $updateStmt->execute();
        }
        
        $conn->commit();
        $message = "Contact page settings updated successfully!";
        $messageType = "success";
        
        // Refresh settings
        $stmt = $conn->query("SELECT setting_key, setting_value FROM contact_page_settings");
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    } catch(PDOException $e) {
        $conn->rollBack();
        $message = "Error updating settings: " . $e->getMessage();
        $messageType = "error";
    }
}

// Handle form field addition
if (isset($_POST['add_field'])) {
    $field_name = $_POST['field_name'];
    $field_label = $_POST['field_label'];
    $field_type = $_POST['field_type'];
    $placeholder = $_POST['placeholder'];
    $is_required = isset($_POST['is_required']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Get max position
    $stmt = $conn->query("SELECT MAX(position) as max_pos FROM contact_form_fields");
    $maxPos = $stmt->fetch();
    $position = ($maxPos['max_pos'] ?? 0) + 1;
    
    try {
        $stmt = $conn->prepare("INSERT INTO contact_form_fields (field_name, field_label, field_type, placeholder, is_required, position, is_active) VALUES (:name, :label, :type, :placeholder, :required, :position, :active)");
        
        $stmt->bindParam(':name', $field_name);
        $stmt->bindParam(':label', $field_label);
        $stmt->bindParam(':type', $field_type);
        $stmt->bindParam(':placeholder', $placeholder);
        $stmt->bindParam(':required', $is_required);
        $stmt->bindParam(':position', $position);
        $stmt->bindParam(':active', $is_active);
        
        $stmt->execute();
        
        $message = "Form field added successfully!";
        $messageType = "success";
        
        // Refresh form fields
        $stmt = $conn->query("SELECT * FROM contact_form_fields ORDER BY position");
        $formFields = $stmt->fetchAll();
    } catch(PDOException $e) {
        $message = "Error adding form field: " . $e->getMessage();
        $messageType = "error";
    }
}

// Handle form field update
if (isset($_POST['update_field'])) {
    $field_id = (int)$_POST['field_id'];
    $field_name = $_POST['field_name'];
    $field_label = $_POST['field_label'];
    $field_type = $_POST['field_type'];
    $placeholder = $_POST['placeholder'];
    $position = (int)$_POST['position'];
    $is_required = isset($_POST['is_required']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    try {
        $stmt = $conn->prepare("UPDATE contact_form_fields SET 
                               field_name = :name,
                               field_label = :label,
                               field_type = :type,
                               placeholder = :placeholder,
                               is_required = :required,
                               position = :position,
                               is_active = :active
                               WHERE id = :id");
        
        $stmt->bindParam(':id', $field_id);
        $stmt->bindParam(':name', $field_name);
        $stmt->bindParam(':label', $field_label);
        $stmt->bindParam(':type', $field_type);
        $stmt->bindParam(':placeholder', $placeholder);
        $stmt->bindParam(':required', $is_required);
        $stmt->bindParam(':position', $position);
        $stmt->bindParam(':active', $is_active);
        
        $stmt->execute();
        
        $message = "Form field updated successfully!";
        $messageType = "success";
        
        // Refresh form fields
        $stmt = $conn->query("SELECT * FROM contact_form_fields ORDER BY position");
        $formFields = $stmt->fetchAll();
    } catch(PDOException $e) {
        $message = "Error updating form field: " . $e->getMessage();
        $messageType = "error";
    }
}

// Handle form field deletion
if (isset($_GET['delete_field']) && !empty($_GET['delete_field'])) {
    $field_id = (int)$_GET['delete_field'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM contact_form_fields WHERE id = :id");
        $stmt->bindParam(':id', $field_id);
        $stmt->execute();
        
        $message = "Form field deleted successfully!";
        $messageType = "success";
        
        // Refresh form fields
        $stmt = $conn->query("SELECT * FROM contact_form_fields ORDER BY position");
        $formFields = $stmt->fetchAll();
    } catch(PDOException $e) {
        $message = "Error deleting form field: " . $e->getMessage();
        $messageType = "error";
    }
}

// Get field for editing if in edit mode
$edit_field = null;
if (isset($_GET['edit_field']) && !empty($_GET['edit_field'])) {
    $field_id = (int)$_GET['edit_field'];
    
    try {
        $stmt = $conn->prepare("SELECT * FROM contact_form_fields WHERE id = :id");
        $stmt->bindParam(':id', $field_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $edit_field = $stmt->fetch();
        }
    } catch(PDOException $e) {
        $message = "Error fetching field data: " . $e->getMessage();
        $messageType = "error";
    }
}

// Get active tab from URL or post
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : (isset($_POST['active_tab']) ? $_POST['active_tab'] : 'general');
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Page Management - Akademi Merdeka</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Boxicons -->
    <link rel="stylesheet" href="../../../assets/css/boxicons.min.css">
    <!-- CodeMirror for HTML editing -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/monokai.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/htmlmixed/htmlmixed.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/javascript/javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/css/css.min.js"></script>
    <!-- Custom Tailwind Config -->
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
                <h1 class="text-xl font-semibold text-gray-800">Contact Page Management</h1>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <span class="text-gray-600">Welcome, <?php echo htmlspecialchars($username); ?></span>
                        <img class="w-8 h-8 rounded-full" src="../../../assets/images/team/pp-1.png" alt="Profile">
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
                
                <!-- Tab Navigation -->
                <div class="mb-6 border-b border-gray-200">
                    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                        <li class="mr-2">
                            <a href="?tab=general" class="inline-block p-4 rounded-t-lg <?php echo $activeTab === 'general' ? 'border-b-2 border-blue-600 text-blue-600' : 'border-b-2 border-transparent hover:text-gray-600 hover:border-gray-300'; ?>">
                                General Settings
                            </a>
                        </li>
                        <li class="mr-2">
                            <a href="?tab=form" class="inline-block p-4 rounded-t-lg <?php echo $activeTab === 'form' ? 'border-b-2 border-blue-600 text-blue-600' : 'border-b-2 border-transparent hover:text-gray-600 hover:border-gray-300'; ?>">
                                Contact Form
                            </a>
                        </li>
                        <li class="mr-2">
                            <a href="?tab=map" class="inline-block p-4 rounded-t-lg <?php echo $activeTab === 'map' ? 'border-b-2 border-blue-600 text-blue-600' : 'border-b-2 border-transparent hover:text-gray-600 hover:border-gray-300'; ?>">
                                Map Settings
                            </a>
                        </li>
                    </ul>
                </div>
                
                <?php if ($activeTab === 'general'): ?>
                <!-- General Settings Tab -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Page & Contact Info Settings</h2>
                        <p class="text-sm text-gray-500 mt-1">Configure general settings for the contact page</p>
                    </div>
                    
                    <div class="p-6">
                        <form method="POST" action="">
                            <input type="hidden" name="active_tab" value="general">
                            
                            <!-- Page Settings -->
                            <div class="mb-6">
                                <h3 class="text-base font-medium text-gray-800 mb-3">Page Settings</h3>
                                
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <label for="page_title" class="block text-sm font-medium text-gray-700 mb-1">Page Title</label>
                                        <input type="text" id="page_title" name="settings[page_title]" 
                                            value="<?php echo htmlspecialchars($settings['page_title'] ?? ''); ?>" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">This appears in the banner and breadcrumbs</p>
                                    </div>
                                    
                                    <div>
                                        <label for="form_title" class="block text-sm font-medium text-gray-700 mb-1">Form Section Title</label>
                                        <input type="text" id="form_title" name="settings[form_title]" 
                                            value="<?php echo htmlspecialchars($settings['form_title'] ?? ''); ?>" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">The title above the contact form</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Contact Info Settings -->
                            <div class="mb-6">
                                <h3 class="text-base font-medium text-gray-800 mb-3">Contact Information</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label for="contact_section_title" class="block text-sm font-medium text-gray-700 mb-1">Section Title</label>
                                        <input type="text" id="contact_section_title" name="settings[contact_section_title]" 
                                            value="<?php echo htmlspecialchars($settings['contact_section_title'] ?? ''); ?>" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    
                                    <div>
                                        <label for="contact_section_subtitle" class="block text-sm font-medium text-gray-700 mb-1">Section Subtitle</label>
                                        <input type="text" id="contact_section_subtitle" name="settings[contact_section_subtitle]" 
                                            value="<?php echo htmlspecialchars($settings['contact_section_subtitle'] ?? ''); ?>" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="company_name" class="block text-sm font-medium text-gray-700 mb-1">Company Name</label>
                                    <input type="text" id="company_name" name="settings[company_name]" 
                                        value="<?php echo htmlspecialchars($settings['company_name'] ?? ''); ?>" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                
                                <div class="mb-4">
                                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                                    <textarea id="address" name="settings[address]" rows="2" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($settings['address'] ?? ''); ?></textarea>
                                    <p class="mt-1 text-xs text-gray-500">You can use HTML tags like &lt;br&gt; for line breaks</p>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                        <input type="text" id="phone" name="settings[phone]" 
                                            value="<?php echo htmlspecialchars($settings['phone'] ?? ''); ?>" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    
                                    <div>
                                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                        <input type="email" id="email" name="settings[email]" 
                                            value="<?php echo htmlspecialchars($settings['email'] ?? ''); ?>" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-6">
                                <button type="submit" name="update_settings" class="px-5 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <i class='bx bx-save mr-2'></i> Save Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Preview Section -->
                <div class="mt-6 bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Contact Info Preview</h2>
                    </div>
                    
                    <div class="p-6">
                        <div class="p-4 border rounded-lg bg-gray-50">
                            <span class="text-blue-600 font-medium"><?php echo htmlspecialchars($settings['contact_section_title'] ?? 'Hubungi Kami'); ?></span>
                            <h2 class="text-xl font-bold my-2"><?php echo htmlspecialchars($settings['contact_section_subtitle'] ?? 'Mari bergabung bersama kami'); ?></h2>
                            <p class="mb-4"><?php echo htmlspecialchars($settings['company_name'] ?? 'Akademi Merdeka Office:'); ?></p>
                            
                            <ul class="space-y-4">
                                <li class="flex">
                                    <div class="mr-3 text-blue-600"><i class='bx bxs-map text-xl'></i></div>
                                    <div>
                                        <h3 class="font-medium">Address</h3>
                                        <span><?php echo $settings['address'] ?? ''; ?></span>
                                    </div>
                                </li>
                                <li class="flex">
                                    <div class="mr-3 text-blue-600"><i class='bx bx-phone-call text-xl'></i></div>
                                    <div>
                                        <h3 class="font-medium">Phone Number</h3>
                                        <span><?php echo htmlspecialchars($settings['phone'] ?? ''); ?></span>
                                    </div>
                                </li>
                                <li class="flex">
                                    <div class="mr-3 text-blue-600"><i class='bx bx-message text-xl'></i></div>
                                    <div>
                                        <h3 class="font-medium">Email</h3>
                                        <span><?php echo htmlspecialchars($settings['email'] ?? ''); ?></span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <p class="text-sm text-gray-500 mt-2">This is how the contact information will appear on the contact page.</p>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($activeTab === 'form'): ?>
                <!-- Contact Form Tab -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <div class="p-6 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-800"><?php echo $edit_field ? 'Edit Form Field' : 'Add Form Field'; ?></h2>
                            </div>
                            
                            <div class="p-6">
                                <form method="POST" action="">
                                    <input type="hidden" name="active_tab" value="form">
                                    
                                    <?php if($edit_field): ?>
                                    <input type="hidden" name="field_id" value="<?php echo $edit_field['id']; ?>">
                                    <?php endif; ?>
                                    
                                    <div class="mb-4">
                                        <label for="field_name" class="block text-sm font-medium text-gray-700 mb-1">Field Name</label>
                                        <input type="text" id="field_name" name="field_name" required
                                               value="<?php echo $edit_field ? htmlspecialchars($edit_field['field_name']) : ''; ?>"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">Used as the form field identifier (e.g., name, email)</p>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="field_label" class="block text-sm font-medium text-gray-700 mb-1">Field Label</label>
                                        <input type="text" id="field_label" name="field_label" required
                                               value="<?php echo $edit_field ? htmlspecialchars($edit_field['field_label']) : ''; ?>"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">Text displayed as the field label</p>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="field_type" class="block text-sm font-medium text-gray-700 mb-1">Field Type</label>
                                        <select id="field_type" name="field_type" required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="text" <?php echo ($edit_field && $edit_field['field_type'] == 'text') ? 'selected' : ''; ?>>Text</option>
                                            <option value="email" <?php echo ($edit_field && $edit_field['field_type'] == 'email') ? 'selected' : ''; ?>>Email</option>
                                            <option value="tel" <?php echo ($edit_field && $edit_field['field_type'] == 'tel') ? 'selected' : ''; ?>>Telephone</option>
                                            <option value="textarea" <?php echo ($edit_field && $edit_field['field_type'] == 'textarea') ? 'selected' : ''; ?>>Textarea</option>
                                            <option value="checkbox" <?php echo ($edit_field && $edit_field['field_type'] == 'checkbox') ? 'selected' : ''; ?>>Checkbox</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="placeholder" class="block text-sm font-medium text-gray-700 mb-1">Placeholder</label>
                                        <input type="text" id="placeholder" name="placeholder"
                                               value="<?php echo $edit_field ? htmlspecialchars($edit_field['placeholder']) : ''; ?>"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">Text shown inside the empty field</p>
                                    </div>
                                    
                                    <?php if($edit_field): ?>
                                    <div class="mb-4">
                                        <label for="position" class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                                        <input type="number" id="position" name="position" min="1"
                                               value="<?php echo $edit_field ? htmlspecialchars($edit_field['position']) : '1'; ?>"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">Order in which fields appear on the form</p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="mb-4">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="is_required"
                                                   <?php echo (!$edit_field || $edit_field['is_required']) ? 'checked' : ''; ?>
                                                   class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-gray-700">Required Field</span>
                                        </label>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="is_active"
                                                   <?php echo (!$edit_field || $edit_field['is_active']) ? 'checked' : ''; ?>
                                                   class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-gray-700">Active</span>
                                        </label>
                                        <p class="mt-1 text-xs text-gray-500 ml-6">Inactive fields won't appear on the contact form</p>
                                    </div>
                                    
                                    <div class="mt-6 flex items-center space-x-2">
                                        <button type="submit" name="<?php echo $edit_field ? 'update_field' : 'add_field'; ?>"
                                                class="px-5 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <i class='bx bx-save mr-2'></i> <?php echo $edit_field ? 'Update Field' : 'Add Field'; ?>
                                        </button>
                                        
                                        <?php if($edit_field): ?>
                                        <a href="?tab=form" class="px-5 py-2 bg-gray-300 text-gray-700 font-medium rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                            Cancel
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <div class="p-6 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-800">Form Fields</h2>
                                <p class="text-sm text-gray-500 mt-1">Manage the fields that appear on the contact form</p>
                            </div>
                            
                            <div class="p-6">
                                <?php if(empty($formFields)): ?>
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <i class="bx bx-info-circle text-blue-600 text-xl"></i>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-blue-800">No form fields found</h3>
                                            <div class="mt-2 text-sm text-blue-700">
                                                <p>Add your first form field using the form on the left.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php else: ?>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Field Name</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Label</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Required</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <?php foreach($formFields as $field): ?>
                                            <tr class="<?php echo (isset($_GET['edit_field']) && $_GET['edit_field'] == $field['id']) ? 'bg-blue-50' : ''; ?>">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <?php echo htmlspecialchars($field['field_name']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <?php echo htmlspecialchars($field['field_label']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <?php echo htmlspecialchars($field['field_type']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <?php echo $field['is_required'] ? 'Yes' : 'No'; ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <?php echo htmlspecialchars($field['position']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <?php if($field['is_active']): ?>
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Active
                                                    </span>
                                                    <?php else: ?>
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        Inactive
                                                    </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="?tab=form&edit_field=<?php echo $field['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                                        <i class='bx bx-edit'></i> Edit
                                                    </a>
                                                    <a href="?tab=form&delete_field=<?php echo $field['id']; ?>" 
                                                       onclick="return confirm('Are you sure you want to delete this field?')"
                                                       class="text-red-600 hover:text-red-900">
                                                        <i class='bx bx-trash'></i> Delete
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Form Preview -->
                        <div class="mt-6 bg-white rounded-lg shadow-md overflow-hidden">
                            <div class="p-6 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-800">Form Preview</h2>
                            </div>
                            
                            <div class="p-6">
                                <div class="mb-4 text-center">
                                    <h2 class="text-xl font-semibold"><?php echo htmlspecialchars($settings['form_title'] ?? 'Ada Pertanyaan? Silahkan lengkapi form dibawah ini'); ?></h2>
                                </div>
                                
                                <div class="p-4 border rounded-lg">
                                    <form class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <?php
                                        // Sort fields by position
                                        usort($formFields, function($a, $b) {
                                            return $a['position'] <=> $b['position'];
                                        });
                                        
                                        // Display only active fields
                                        foreach($formFields as $field):
                                            if(!$field['is_active']) continue;
                                            
                                            $fieldClasses = "form-control px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 w-full";
                                            
                                            // For textarea and checkbox fields, use full width
                                            $colSpan = ($field['field_type'] === 'textarea' || $field['field_type'] === 'checkbox') ? 'md:col-span-2' : '';
                                        ?>
                                        <div class="<?php echo $colSpan; ?>">
                                            <?php if($field['field_type'] === 'checkbox'): ?>
                                            <div class="flex items-center">
                                                <input type="checkbox" id="preview_<?php echo $field['field_name']; ?>" 
                                                       class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                                       <?php echo $field['is_required'] ? 'required' : ''; ?>>
                                                <label for="preview_<?php echo $field['field_name']; ?>" class="ml-2 text-sm">
                                                    <?php echo $field['field_label']; ?>
                                                    <?php if($field['is_required']): ?><span class="text-red-500 ml-1">*</span><?php endif; ?>
                                                </label>
                                            </div>
                                            <?php else: ?>
                                            <label for="preview_<?php echo $field['field_name']; ?>" class="block text-sm font-medium text-gray-700 mb-1">
                                                <?php echo htmlspecialchars($field['field_label']); ?>
                                                <?php if($field['is_required']): ?><span class="text-red-500 ml-1">*</span><?php endif; ?>
                                            </label>
                                            
                                            <?php if($field['field_type'] === 'textarea'): ?>
                                            <textarea id="preview_<?php echo $field['field_name']; ?>" 
                                                      placeholder="<?php echo htmlspecialchars($field['placeholder']); ?>"
                                                      class="<?php echo $fieldClasses; ?>" rows="4"
                                                      <?php echo $field['is_required'] ? 'required' : ''; ?>></textarea>
                                            <?php else: ?>
                                            <input type="<?php echo $field['field_type']; ?>" 
                                                   id="preview_<?php echo $field['field_name']; ?>" 
                                                   placeholder="<?php echo htmlspecialchars($field['placeholder']); ?>"
                                                   class="<?php echo $fieldClasses; ?>" 
                                                   <?php echo $field['is_required'] ? 'required' : ''; ?>>
                                            <?php endif; ?>
                                            <?php endif; ?>
                                            
                                            <div class="help-block with-errors"></div>
                                        </div>
                                        <?php endforeach; ?>
                                        
                                        <div class="md:col-span-2 mt-4 text-center">
                                            <button type="button" class="px-5 py-2 bg-blue-600 text-white font-medium rounded-full hover:bg-blue-700">
                                                Submit <i class='bx bx-chevron-right'></i>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                
                                <p class="text-sm text-gray-500 mt-2">This is a preview of how the contact form will appear to users.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($activeTab === 'map'): ?>
                <!-- Map Settings Tab -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Map Settings</h2>
                        <p class="text-sm text-gray-500 mt-1">Configure the Google Map that appears on the contact page</p>
                    </div>
                    
                    <div class="p-6">
                        <form method="POST" action="">
                            <input type="hidden" name="active_tab" value="map">
                            
                            <div class="mb-6">
                                <label for="map_embed_url" class="block text-sm font-medium text-gray-700 mb-1">Google Maps Embed URL</label>
                                <textarea id="map_embed_url" name="settings[map_embed_url]" rows="3" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($settings['map_embed_url'] ?? ''); ?></textarea>
                                <p class="mt-1 text-xs text-gray-500">Enter the full Google Maps embed URL <br>(e.g., https://www.google.com/maps/embed?pb=...)</p>
                            </div>
                            
                            <div class="mt-6">
                                <button type="submit" name="update_settings" class="px-5 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <i class='bx bx-save mr-2'></i> Save Map Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Map Preview -->
                <div class="mt-6 bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Map Preview</h2>
                    </div>
                    
                    <div class="p-6">
                        <div class="border rounded-lg overflow-hidden aspect-video">
                            <iframe src="<?php echo htmlspecialchars($settings['map_embed_url'] ?? ''); ?>" 
                                    width="100%" height="100%" style="border:0;" 
                                    allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                        
                        <p class="text-sm text-gray-500 mt-2">This is how the map will appear on the contact page.</p>
                    </div>
                </div>
                
                <!-- Instructions for Getting Embed URL -->
                <div class="mt-6 bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">How to Get Google Maps Embed URL</h2>
                    </div>
                    
                    <div class="p-6">
                        <ol class="list-decimal pl-5 space-y-3 text-sm text-gray-700">
                            <li>Go to <a href="https://www.google.com/maps" target="_blank" class="text-blue-600 hover:underline">Google Maps</a></li>
                            <li>Search for your business location</li>
                            <li>Click on "Share" button</li>
                            <li>Select the "Embed a map" tab</li>
                            <li>Choose your preferred size</li>
                            <li>Copy the entire iframe code</li>
                            <li>From the iframe code, extract just the URL part (the part inside the <code class="bg-gray-100 px-1 py-0.5 rounded">src="..."</code> attribute)</li>
                            <li>Paste this URL into the Google Maps Embed URL field above</li>
                        </ol>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // For HTML editing capabilities
        const htmlTextareas = document.querySelectorAll('textarea[data-editor="html"]');
        htmlTextareas.forEach(textarea => {
            const editor = CodeMirror.fromTextArea(textarea, {
                lineNumbers: true,
                mode: "htmlmixed",
                theme: "monokai",
                lineWrapping: true
            });
            
            editor.setSize(null, 200);
        });
    </script>
</body>
</html>