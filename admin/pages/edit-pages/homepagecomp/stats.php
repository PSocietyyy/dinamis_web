<?php
// Stats Slider Component
// File path: admin/pages/edit-pages/homepagecomp/stats.php

// Handle adding new stats item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_stats_item'])) {
    try {
        // Get form data
        $title = trim($_POST['stats_title']);
        $count = trim($_POST['stats_count']);
        $position = isset($_POST['stats_position']) ? (int)$_POST['stats_position'] : 0;
        $isActive = isset($_POST['stats_is_active']) ? 1 : 0;
        
        // Handle image upload if provided
        $imagePath = trim($_POST['stats_image']);
        
        if (!empty($_FILES['stats_image_upload']['name'])) {
            $uploadDir = '../../../assets/images/home-three/';
            $fileName = basename($_FILES['stats_image_upload']['name']);
            $uploadFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['stats_image_upload']['tmp_name'], $uploadFile)) {
                $imagePath = 'assets/images/home-three/' . $fileName;
            }
        }
        
        // Insert new stats item
        $stmt = $conn->prepare("INSERT INTO stats_slider (title, count, image, position, is_active) 
                               VALUES (:title, :count, :image, :position, :is_active)");
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':count', $count);
        $stmt->bindParam(':image', $imagePath);
        $stmt->bindParam(':position', $position);
        $stmt->bindParam(':is_active', $isActive);
        $stmt->execute();
        
        echo "<div class='bg-green-100 text-green-700 p-4 rounded-lg mb-4'>New stats item added successfully!</div>";
    } catch(PDOException $e) {
        echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error adding new stats item: " . $e->getMessage() . "</div>";
    }
}

// Handle updating stats item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stats_item'])) {
    try {
        $statsId = (int)$_POST['stats_id'];
        $title = trim($_POST['stats_title']);
        $count = trim($_POST['stats_count']);
        $position = isset($_POST['stats_position']) ? (int)$_POST['stats_position'] : 0;
        $isActive = isset($_POST['stats_is_active']) ? 1 : 0;
        
        // Handle image upload if provided
        $imagePath = trim($_POST['stats_image']);
        
        if (!empty($_FILES['stats_image_upload']['name'])) {
            $uploadDir = '../../../assets/images/home-three/';
            $fileName = basename($_FILES['stats_image_upload']['name']);
            $uploadFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['stats_image_upload']['tmp_name'], $uploadFile)) {
                $imagePath = 'assets/images/home-three/' . $fileName;
            }
        }
        
        // Update stats item
        $stmt = $conn->prepare("UPDATE stats_slider 
                               SET title = :title, count = :count, image = :image, 
                                   position = :position, is_active = :is_active 
                               WHERE id = :id");
        $stmt->bindParam(':id', $statsId);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':count', $count);
        $stmt->bindParam(':image', $imagePath);
        $stmt->bindParam(':position', $position);
        $stmt->bindParam(':is_active', $isActive);
        $stmt->execute();
        
        echo "<div class='bg-green-100 text-green-700 p-4 rounded-lg mb-4'>Stats item updated successfully!</div>";
    } catch(PDOException $e) {
        echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error updating stats item: " . $e->getMessage() . "</div>";
    }
}

// Handle deleting stats item
if (isset($_GET['delete_stats']) && !empty($_GET['delete_stats'])) {
    try {
        $statsId = (int)$_GET['delete_stats'];
        
        $stmt = $conn->prepare("DELETE FROM stats_slider WHERE id = :id");
        $stmt->bindParam(':id', $statsId);
        $stmt->execute();
        
        echo "<div class='bg-green-100 text-green-700 p-4 rounded-lg mb-4'>Stats item deleted successfully!</div>";
    } catch(PDOException $e) {
        echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error deleting stats item: " . $e->getMessage() . "</div>";
    }
}

// Get all stats items
$statsItems = [];
try {
    $stmt = $conn->query("SELECT * FROM stats_slider ORDER BY position, id");
    $statsItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error fetching stats items: " . $e->getMessage() . "</div>";
}

// Get specific stats item for editing
$editStats = null;
if (isset($_GET['edit_stats']) && !empty($_GET['edit_stats'])) {
    try {
        $statsId = (int)$_GET['edit_stats'];
        
        $stmt = $conn->prepare("SELECT * FROM stats_slider WHERE id = :id");
        $stmt->bindParam(':id', $statsId);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $editStats = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch(PDOException $e) {
        echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error fetching stats item: " . $e->getMessage() . "</div>";
    }
}
?>

<div class="space-y-8">
    <!-- Stats Slider Items List -->
    <div class="border rounded-lg overflow-hidden">
        <div class="bg-gray-50 p-4 border-b flex justify-between items-center">
            <div>
                <h3 class="font-medium text-gray-900">Statistics Slider Items</h3>
                <p class="text-sm text-gray-500 mt-1">Manage the statistics slider items on your homepage</p>
            </div>
            
            <a href="?tab=stats" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition-colors">
                <i class='bx bx-plus mr-1'></i> Add New Statistic
            </a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Count</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($statsItems)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                No statistics items found. Add your first item using the form below.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($statsItems as $item): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex-shrink-0 h-16 w-16">
                                        <img class="h-16 w-16 object-contain" src="../../../<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['title']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['count']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $item['position']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($item['is_active']): ?>
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
                                    <a href="?tab=stats&edit_stats=<?php echo $item['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class='bx bx-edit'></i> Edit
                                    </a>
                                    <a href="?tab=stats&delete_stats=<?php echo $item['id']; ?>" 
                                       onclick="return confirm('Are you sure you want to delete this statistics item?')"
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
    
    <!-- Add/Edit Stats Item Form -->
    <div class="border rounded-lg overflow-hidden">
        <div class="bg-gray-50 p-4 border-b">
            <h3 class="font-medium text-gray-900">
                <?php echo $editStats ? 'Edit Statistics Item' : 'Add New Statistics Item'; ?>
            </h3>
            <p class="text-sm text-gray-500 mt-1">
                <?php echo $editStats ? 'Update the selected statistics item' : 'Add a new statistics item to your homepage slider'; ?>
            </p>
        </div>
        
        <div class="p-4">
            <form method="POST" action="" enctype="multipart/form-data" class="space-y-4">
                <?php if ($editStats): ?>
                    <input type="hidden" name="stats_id" value="<?php echo $editStats['id']; ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="stats_title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <input type="text" id="stats_title" name="stats_title" required
                               value="<?php echo $editStats ? htmlspecialchars($editStats['title']) : ''; ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Example: Karya Ilmiah, Penerbitan Jurnal, etc.</p>
                    </div>
                    
                    <div>
                        <label for="stats_count" class="block text-sm font-medium text-gray-700 mb-1">Count/Number</label>
                        <input type="text" id="stats_count" name="stats_count" required
                               value="<?php echo $editStats ? htmlspecialchars($editStats['count']) : ''; ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Example: 500+, 10+, 100+, etc.</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="stats_position" class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                        <input type="number" id="stats_position" name="stats_position" min="0"
                               value="<?php echo $editStats ? $editStats['position'] : count($statsItems) + 1; ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="flex items-center h-full pt-6">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="stats_is_active" class="h-4 w-4 text-blue-600 rounded"
                                   <?php echo (!$editStats || $editStats['is_active']) ? 'checked' : ''; ?>>
                            <span class="ml-2 text-sm text-gray-700">Active</span>
                        </label>
                    </div>
                </div>
                
                <div>
                    <label for="stats_image" class="block text-sm font-medium text-gray-700 mb-1">Image Path</label>
                    <div class="flex">
                        <input type="text" id="stats_image" name="stats_image" 
                            value="<?php echo $editStats ? htmlspecialchars($editStats['image']) : 'assets/images/home-three/home-slider-default.png'; ?>"
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <a href="../../../<?php echo $editStats ? htmlspecialchars($editStats['image']) : 'assets/images/home-three/home-slider-default.png'; ?>" 
                           target="_blank" 
                           class="px-4 py-2 bg-gray-100 text-gray-600 border-t border-r border-b border-gray-300 hover:bg-gray-200 transition-colors">
                            <i class='bx bx-show'></i>
                        </a>
                    </div>
                    <div class="mt-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Upload New Image</label>
                        <div class="flex items-center">
                            <input type="file" id="stats_image_upload" name="stats_image_upload" class="hidden"
                                accept="image/*" onchange="updateFileName(this)">
                            <label for="stats_image_upload" 
                                class="cursor-pointer px-4 py-2 bg-gray-200 text-gray-700 rounded-l-md hover:bg-gray-300 transition-colors">
                                Choose file
                            </label>
                            <span id="stats_image_upload_name" 
                                class="px-4 py-2 bg-gray-100 text-gray-600 border border-gray-300 rounded-r-md flex-1">
                                No file chosen
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Recommended size: 150x150 pixels</p>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <?php if ($editStats): ?>
                        <a href="?tab=stats" class="px-4 py-2 bg-gray-200 text-gray-700 font-medium rounded-md hover:bg-gray-300 transition-colors">
                            Cancel
                        </a>
                    <?php endif; ?>
                    
                    <button type="submit" name="<?php echo $editStats ? 'update_stats_item' : 'add_stats_item'; ?>" 
                            class="px-6 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition-colors">
                        <i class='bx bx-<?php echo $editStats ? 'save' : 'plus'; ?> mr-1'></i>
                        <?php echo $editStats ? 'Update Statistics Item' : 'Add Statistics Item'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Preview -->
    <div class="border rounded-lg overflow-hidden">
        <div class="bg-gray-50 p-4 border-b">
            <h3 class="font-medium text-gray-900">Stats Slider Preview</h3>
            <p class="text-sm text-gray-500 mt-1">A simplified preview of how the statistics slider will appear</p>
        </div>
        
        <div class="p-4">
            <div class="bg-gray-100 p-6 rounded-lg">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    <?php 
                    // Get active stats items for preview
                    $activeStats = array_filter($statsItems, function($item) {
                        return $item['is_active'] == 1;
                    });
                    
                    // Sort by position
                    usort($activeStats, function($a, $b) {
                        return $a['position'] - $b['position'];
                    });
                    
                    // Show only first 5 items in preview
                    $previewStats = array_slice($activeStats, 0, 5);
                    
                    if (empty($previewStats)):
                    ?>
                        <div class="col-span-full text-center py-6 text-gray-500">
                            <p>No active statistics items to display. Add items above to see them in the preview.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($previewStats as $item): ?>
                            <div class="flex flex-col items-center p-3 bg-white rounded-lg shadow-sm">
                                <img src="../../../<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="h-12 w-12 object-contain mb-2">
                                <h4 class="text-lg font-semibold text-center"><?php echo htmlspecialchars($item['count']); ?></h4>
                                <p class="text-sm text-gray-600 text-center"><?php echo htmlspecialchars($item['title']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-2">This is a simplified preview. The actual appearance may vary. The slider will display all active items in the order specified by their position.</p>
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