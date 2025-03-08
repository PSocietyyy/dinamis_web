<?php
// Services Section Component
// File path: admin/pages/edit-pages/homepagecomp/services.php

// Get services section ID
$servicesSectionId = 0;
try {
    $stmt = $conn->prepare("SELECT id FROM homepage_sections WHERE section_key = 'services'");
    $stmt->execute();
    $servicesSectionId = $stmt->fetchColumn();
} catch(PDOException $e) {
    echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error fetching services section: " . $e->getMessage() . "</div>";
}

// Handle section header form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_services_header'])) {
    try {
        // Begin transaction
        $conn->beginTransaction();
        
        // Update title and subtitle
        if (isset($_POST['title']) && isset($_POST['subtitle'])) {
            $title = trim($_POST['title']);
            $subtitle = trim($_POST['subtitle']);
            
            // Update title
            $stmt = $conn->prepare("UPDATE homepage_content 
                                   SET content_value = :value 
                                   WHERE section_id = :section_id AND content_key = 'title'");
            $stmt->bindParam(':value', $title);
            $stmt->bindParam(':section_id', $servicesSectionId);
            $stmt->execute();
            
            // Update subtitle
            $stmt = $conn->prepare("UPDATE homepage_content 
                                   SET content_value = :value 
                                   WHERE section_id = :section_id AND content_key = 'subtitle'");
            $stmt->bindParam(':value', $subtitle);
            $stmt->bindParam(':section_id', $servicesSectionId);
            $stmt->execute();
        }
        
        $conn->commit();
        
        echo "<div class='bg-green-100 text-green-700 p-4 rounded-lg mb-4'>Services section header updated successfully!</div>";
    } catch(PDOException $e) {
        $conn->rollBack();
        echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error updating services section header: " . $e->getMessage() . "</div>";
    }
}

// Handle adding new service
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_service'])) {
    try {
        // Get form data
        $title = trim($_POST['service_title']);
        $description = trim($_POST['service_description']);
        $link = trim($_POST['service_link']);
        $position = isset($_POST['service_position']) ? (int)$_POST['service_position'] : 0;
        $isActive = isset($_POST['service_is_active']) ? 1 : 0;
        
        // Handle icon upload if provided
        $iconPath = trim($_POST['service_icon']);
        
        if (!empty($_FILES['service_icon_upload']['name'])) {
            $uploadDir = '../../../assets/images/services/';
            $fileName = basename($_FILES['service_icon_upload']['name']);
            $uploadFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['service_icon_upload']['tmp_name'], $uploadFile)) {
                $iconPath = 'assets/images/services/' . $fileName;
            }
        }
        
        // Insert new service
        $stmt = $conn->prepare("INSERT INTO services_section (title, icon, description, link, position, is_active) 
                               VALUES (:title, :icon, :description, :link, :position, :is_active)");
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':icon', $iconPath);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':link', $link);
        $stmt->bindParam(':position', $position);
        $stmt->bindParam(':is_active', $isActive);
        $stmt->execute();
        
        echo "<div class='bg-green-100 text-green-700 p-4 rounded-lg mb-4'>New service added successfully!</div>";
    } catch(PDOException $e) {
        echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error adding new service: " . $e->getMessage() . "</div>";
    }
}

// Handle updating service
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_service'])) {
    try {
        $serviceId = (int)$_POST['service_id'];
        $title = trim($_POST['service_title']);
        $description = trim($_POST['service_description']);
        $link = trim($_POST['service_link']);
        $position = isset($_POST['service_position']) ? (int)$_POST['service_position'] : 0;
        $isActive = isset($_POST['service_is_active']) ? 1 : 0;
        
        // Handle icon upload if provided
        $iconPath = trim($_POST['service_icon']);
        
        if (!empty($_FILES['service_icon_upload']['name'])) {
            $uploadDir = '../../../assets/images/services/';
            $fileName = basename($_FILES['service_icon_upload']['name']);
            $uploadFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['service_icon_upload']['tmp_name'], $uploadFile)) {
                $iconPath = 'assets/images/services/' . $fileName;
            }
        }
        
        // Update service
        $stmt = $conn->prepare("UPDATE services_section 
                               SET title = :title, icon = :icon, description = :description, 
                                   link = :link, position = :position, is_active = :is_active 
                               WHERE id = :id");
        $stmt->bindParam(':id', $serviceId);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':icon', $iconPath);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':link', $link);
        $stmt->bindParam(':position', $position);
        $stmt->bindParam(':is_active', $isActive);
        $stmt->execute();
        
        echo "<div class='bg-green-100 text-green-700 p-4 rounded-lg mb-4'>Service updated successfully!</div>";
    } catch(PDOException $e) {
        echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error updating service: " . $e->getMessage() . "</div>";
    }
}

// Handle deleting service
if (isset($_GET['delete_service']) && !empty($_GET['delete_service'])) {
    try {
        $serviceId = (int)$_GET['delete_service'];
        
        $stmt = $conn->prepare("DELETE FROM services_section WHERE id = :id");
        $stmt->bindParam(':id', $serviceId);
        $stmt->execute();
        
        echo "<div class='bg-green-100 text-green-700 p-4 rounded-lg mb-4'>Service deleted successfully!</div>";
    } catch(PDOException $e) {
        echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error deleting service: " . $e->getMessage() . "</div>";
    }
}

// Get services section header
$servicesHeader = [
    'title' => 'Layanan Kami',
    'subtitle' => 'Layanan'
];

try {
    $stmt = $conn->prepare("SELECT content_key, content_value 
                          FROM homepage_content 
                          WHERE section_id = :section_id AND content_key IN ('title', 'subtitle')");
    $stmt->bindParam(':section_id', $servicesSectionId);
    $stmt->execute();
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($results as $row) {
        $servicesHeader[$row['content_key']] = $row['content_value'];
    }
} catch(PDOException $e) {
    echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error fetching services header: " . $e->getMessage() . "</div>";
}

// Get all services
$services = [];
try {
    $stmt = $conn->query("SELECT * FROM services_section ORDER BY position, id");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error fetching services: " . $e->getMessage() . "</div>";
}

// Get specific service for editing
$editService = null;
if (isset($_GET['edit_service']) && !empty($_GET['edit_service'])) {
    try {
        $serviceId = (int)$_GET['edit_service'];
        
        $stmt = $conn->prepare("SELECT * FROM services_section WHERE id = :id");
        $stmt->bindParam(':id', $serviceId);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $editService = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch(PDOException $e) {
        echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error fetching service: " . $e->getMessage() . "</div>";
    }
}
?>

<div class="space-y-8">
    <!-- Services Section Header -->
    <div class="border rounded-lg overflow-hidden">
        <div class="bg-gray-50 p-4 border-b">
            <h3 class="font-medium text-gray-900">Services Section Header</h3>
            <p class="text-sm text-gray-500 mt-1">Edit the services section title and subtitle</p>
        </div>
        
        <div class="p-4">
            <form method="POST" action="" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Section Title</label>
                        <input type="text" id="title" name="title" 
                               value="<?php echo htmlspecialchars($servicesHeader['title']); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="subtitle" class="block text-sm font-medium text-gray-700 mb-1">Section Subtitle</label>
                        <input type="text" id="subtitle" name="subtitle" 
                               value="<?php echo htmlspecialchars($servicesHeader['subtitle']); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" name="update_services_header" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition-colors">
                        <i class='bx bx-save mr-1'></i> Save Header
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Services List -->
    <div class="border rounded-lg overflow-hidden">
        <div class="bg-gray-50 p-4 border-b flex justify-between items-center">
            <div>
                <h3 class="font-medium text-gray-900">Services List</h3>
                <p class="text-sm text-gray-500 mt-1">Manage the services displayed on your homepage</p>
            </div>
            
            <a href="?tab=services" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition-colors">
                <i class='bx bx-plus mr-1'></i> Add New Service
            </a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Icon</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Link</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($services)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                No services found. Add your first service using the form below.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($services as $service): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <img class="h-10 w-10 object-contain" src="../../../<?php echo htmlspecialchars($service['icon']); ?>" alt="<?php echo htmlspecialchars($service['title']); ?>">
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($service['title']); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-500 line-clamp-2"><?php echo htmlspecialchars(substr($service['description'], 0, 100) . (strlen($service['description']) > 100 ? '...' : '')); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($service['link']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $service['position']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($service['is_active']): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Inactive
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="?tab=services&edit_service=<?php echo $service['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class='bx bx-edit'></i> Edit
                                    </a>
                                    <a href="?tab=services&delete_service=<?php echo $service['id']; ?>" 
                                       onclick="return confirm('Are you sure you want to delete this service?')"
                                       class="text-red-600 hover:text-red-900">
                                        <i class='bx bx-trash'></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Add/Edit Service Form -->
    <div class="border rounded-lg overflow-hidden">
        <div class="bg-gray-50 p-4 border-b">
            <h3 class="font-medium text-gray-900">
                <?php echo $editService ? 'Edit Service' : 'Add New Service'; ?>
            </h3>
            <p class="text-sm text-gray-500 mt-1">
                <?php echo $editService ? 'Update the selected service' : 'Add a new service to your homepage'; ?>
            </p>
        </div>
        
        <div class="p-4">
            <form method="POST" action="" enctype="multipart/form-data" class="space-y-4">
                <?php if ($editService): ?>
                    <input type="hidden" name="service_id" value="<?php echo $editService['id']; ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="service_title" class="block text-sm font-medium text-gray-700 mb-1">Service Title</label>
                        <input type="text" id="service_title" name="service_title" required
                               value="<?php echo $editService ? htmlspecialchars($editService['title']) : ''; ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="service_link" class="block text-sm font-medium text-gray-700 mb-1">Service Link</label>
                        <input type="text" id="service_link" name="service_link" required
                               value="<?php echo $editService ? htmlspecialchars($editService['link']) : 'services/'; ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Example: services/penerbitan-jurnal</p>
                    </div>
                </div>
                
                <div>
                    <label for="service_description" class="block text-sm font-medium text-gray-700 mb-1">Service Description</label>
                    <textarea id="service_description" name="service_description" required rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo $editService ? htmlspecialchars($editService['description']) : ''; ?></textarea>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="service_position" class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                        <input type="number" id="service_position" name="service_position" min="0"
                               value="<?php echo $editService ? $editService['position'] : count($services) + 1; ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="flex items-center h-full pt-6">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="service_is_active" class="h-4 w-4 text-blue-600 rounded"
                                   <?php echo (!$editService || $editService['is_active']) ? 'checked' : ''; ?>>
                            <span class="ml-2 text-sm text-gray-700">Active</span>
                        </label>
                    </div>
                </div>
                
                <div>
                    <label for="service_icon" class="block text-sm font-medium text-gray-700 mb-1">Service Icon Path</label>
                    <div class="flex">
                        <input type="text" id="service_icon" name="service_icon" 
                            value="<?php echo $editService ? htmlspecialchars($editService['icon']) : 'assets/images/services/ico-default.png'; ?>"
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <a href="../../../<?php echo $editService ? htmlspecialchars($editService['icon']) : 'assets/images/services/ico-default.png'; ?>" 
                           target="_blank" 
                           class="px-4 py-2 bg-gray-100 text-gray-600 border-t border-r border-b border-gray-300 hover:bg-gray-200 transition-colors">
                            <i class='bx bx-show'></i>
                        </a>
                    </div>
                    <div class="mt-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Upload New Icon</label>
                        <div class="flex items-center">
                            <input type="file" id="service_icon_upload" name="service_icon_upload" class="hidden"
                                accept="image/*" onchange="updateFileName(this)">
                            <label for="service_icon_upload" 
                                class="cursor-pointer px-4 py-2 bg-gray-200 text-gray-700 rounded-l-md hover:bg-gray-300 transition-colors">
                                Choose file
                            </label>
                            <span id="service_icon_upload_name" 
                                class="px-4 py-2 bg-gray-100 text-gray-600 border border-gray-300 rounded-r-md flex-1">
                                No file chosen
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Recommended size: 45x45 pixels</p>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <?php if ($editService): ?>
                        <a href="?tab=services" class="px-4 py-2 bg-gray-200 text-gray-700 font-medium rounded-md hover:bg-gray-300 transition-colors">
                            Cancel
                        </a>
                    <?php endif; ?>
                    
                    <button type="submit" name="<?php echo $editService ? 'update_service' : 'add_service'; ?>" 
                            class="px-6 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition-colors">
                        <i class='bx bx-<?php echo $editService ? 'save' : 'plus'; ?> mr-1'></i>
                        <?php echo $editService ? 'Update Service' : 'Add Service'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function updateFileName(input) {
        const fileName = input.files[0].name;
        const fileNameElement = document.getElementById(input.id + '_name');
        if (fileNameElement) {
            fileNameElement.textContent = fileName;
        }
    }
</script>