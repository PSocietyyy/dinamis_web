<?php
// Testimonials Section Component
// File path: admin/pages/edit-pages/homepagecomp/testimonials.php

// Get testimonials section ID
$testimonialsSectionId = 0;
try {
    $stmt = $conn->prepare("SELECT id FROM homepage_sections WHERE section_key = 'testimonials'");
    $stmt->execute();
    $testimonialsSectionId = $stmt->fetchColumn();
} catch(PDOException $e) {
    echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error fetching testimonials section: " . $e->getMessage() . "</div>";
}

// Handle section header form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_testimonials_header'])) {
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
            $stmt->bindParam(':section_id', $testimonialsSectionId);
            $stmt->execute();
            
            // Update subtitle
            $stmt = $conn->prepare("UPDATE homepage_content 
                                   SET content_value = :value 
                                   WHERE section_id = :section_id AND content_key = 'subtitle'");
            $stmt->bindParam(':value', $subtitle);
            $stmt->bindParam(':section_id', $testimonialsSectionId);
            $stmt->execute();
        }
        
        $conn->commit();
        
        echo "<div class='bg-green-100 text-green-700 p-4 rounded-lg mb-4'>Testimonials section header updated successfully!</div>";
    } catch(PDOException $e) {
        $conn->rollBack();
        echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error updating testimonials section header: " . $e->getMessage() . "</div>";
    }
}

// Handle adding new testimonial
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_testimonial'])) {
    try {
        // Get form data
        $name = trim($_POST['testimonial_name']);
        $position = trim($_POST['testimonial_position']);
        $content = trim($_POST['testimonial_content']);
        $isActive = isset($_POST['testimonial_is_active']) ? 1 : 0;
        
        // Handle image upload if provided
        $imagePath = trim($_POST['testimonial_image']);
        
        if (!empty($_FILES['testimonial_image_upload']['name'])) {
            $uploadDir = '../../../assets/images/clients-img/';
            $fileName = basename($_FILES['testimonial_image_upload']['name']);
            $uploadFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['testimonial_image_upload']['tmp_name'], $uploadFile)) {
                $imagePath = 'assets/images/clients-img/' . $fileName;
            }
        }
        
        // Insert new testimonial
        $stmt = $conn->prepare("INSERT INTO testimonials (name, position, image, content, is_active) 
                               VALUES (:name, :position, :image, :content, :is_active)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':position', $position);
        $stmt->bindParam(':image', $imagePath);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':is_active', $isActive);
        $stmt->execute();
        
        echo "<div class='bg-green-100 text-green-700 p-4 rounded-lg mb-4'>New testimonial added successfully!</div>";
    } catch(PDOException $e) {
        echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error adding new testimonial: " . $e->getMessage() . "</div>";
    }
}

// Handle updating testimonial
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_testimonial'])) {
    try {
        $testimonialId = (int)$_POST['testimonial_id'];
        $name = trim($_POST['testimonial_name']);
        $position = trim($_POST['testimonial_position']);
        $content = trim($_POST['testimonial_content']);
        $isActive = isset($_POST['testimonial_is_active']) ? 1 : 0;
        
        // Handle image upload if provided
        $imagePath = trim($_POST['testimonial_image']);
        
        if (!empty($_FILES['testimonial_image_upload']['name'])) {
            $uploadDir = '../../../assets/images/clients-img/';
            $fileName = basename($_FILES['testimonial_image_upload']['name']);
            $uploadFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['testimonial_image_upload']['tmp_name'], $uploadFile)) {
                $imagePath = 'assets/images/clients-img/' . $fileName;
            }
        }
        
        // Update testimonial
        $stmt = $conn->prepare("UPDATE testimonials 
                               SET name = :name, position = :position, image = :image, 
                                   content = :content, is_active = :is_active 
                               WHERE id = :id");
        $stmt->bindParam(':id', $testimonialId);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':position', $position);
        $stmt->bindParam(':image', $imagePath);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':is_active', $isActive);
        $stmt->execute();
        
        echo "<div class='bg-green-100 text-green-700 p-4 rounded-lg mb-4'>Testimonial updated successfully!</div>";
    } catch(PDOException $e) {
        echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error updating testimonial: " . $e->getMessage() . "</div>";
    }
}

// Handle deleting testimonial
if (isset($_GET['delete_testimonial']) && !empty($_GET['delete_testimonial'])) {
    try {
        $testimonialId = (int)$_GET['delete_testimonial'];
        
        $stmt = $conn->prepare("DELETE FROM testimonials WHERE id = :id");
        $stmt->bindParam(':id', $testimonialId);
        $stmt->execute();
        
        echo "<div class='bg-green-100 text-green-700 p-4 rounded-lg mb-4'>Testimonial deleted successfully!</div>";
    } catch(PDOException $e) {
        echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error deleting testimonial: " . $e->getMessage() . "</div>";
    }
}

// Get testimonials section header
$testimonialsHeader = [
    'title' => 'Apa Kata Mereka?',
    'subtitle' => 'Testimoni'
];

try {
    $stmt = $conn->prepare("SELECT content_key, content_value 
                          FROM homepage_content 
                          WHERE section_id = :section_id AND content_key IN ('title', 'subtitle')");
    $stmt->bindParam(':section_id', $testimonialsSectionId);
    $stmt->execute();
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($results as $row) {
        $testimonialsHeader[$row['content_key']] = $row['content_value'];
    }
} catch(PDOException $e) {
    echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error fetching testimonials header: " . $e->getMessage() . "</div>";
}

// Get all testimonials
$testimonials = [];
try {
    $stmt = $conn->query("SELECT * FROM testimonials ORDER BY id");
    $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error fetching testimonials: " . $e->getMessage() . "</div>";
}

// Get specific testimonial for editing
$editTestimonial = null;
if (isset($_GET['edit_testimonial']) && !empty($_GET['edit_testimonial'])) {
    try {
        $testimonialId = (int)$_GET['edit_testimonial'];
        
        $stmt = $conn->prepare("SELECT * FROM testimonials WHERE id = :id");
        $stmt->bindParam(':id', $testimonialId);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $editTestimonial = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch(PDOException $e) {
        echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error fetching testimonial: " . $e->getMessage() . "</div>";
    }
}
?>

<div class="space-y-8">
    <!-- Testimonials Section Header -->
    <div class="border rounded-lg overflow-hidden">
        <div class="bg-gray-50 p-4 border-b">
            <h3 class="font-medium text-gray-900">Testimonials Section Header</h3>
            <p class="text-sm text-gray-500 mt-1">Edit the testimonials section title and subtitle</p>
        </div>
        
        <div class="p-4">
            <form method="POST" action="" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Section Title</label>
                        <input type="text" id="title" name="title" 
                               value="<?php echo htmlspecialchars($testimonialsHeader['title']); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="subtitle" class="block text-sm font-medium text-gray-700 mb-1">Section Subtitle</label>
                        <input type="text" id="subtitle" name="subtitle" 
                               value="<?php echo htmlspecialchars($testimonialsHeader['subtitle']); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" name="update_testimonials_header" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition-colors">
                        <i class='bx bx-save mr-1'></i> Save Header
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Testimonials List -->
    <div class="border rounded-lg overflow-hidden">
        <div class="bg-gray-50 p-4 border-b flex justify-between items-center">
            <div>
                <h3 class="font-medium text-gray-900">Testimonials List</h3>
                <p class="text-sm text-gray-500 mt-1">Manage client testimonials displayed on your homepage</p>
            </div>
            
            <a href="?tab=testimonials" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition-colors">
                <i class='bx bx-plus mr-1'></i> Add New Testimonial
            </a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Testimonial</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($testimonials)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                No testimonials found. Add your first testimonial using the form below.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($testimonials as $testimonial): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex-shrink-0 h-12 w-12">
                                        <img class="h-12 w-12 rounded-full object-cover" src="../../../<?php echo htmlspecialchars($testimonial['image']); ?>" alt="<?php echo htmlspecialchars($testimonial['name']); ?>">
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($testimonial['name']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($testimonial['position']); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-500 line-clamp-2"><?php echo htmlspecialchars(substr($testimonial['content'], 0, 100) . (strlen($testimonial['content']) > 100 ? '...' : '')); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($testimonial['is_active']): ?>
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
                                    <a href="?tab=testimonials&edit_testimonial=<?php echo $testimonial['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class='bx bx-edit'></i> Edit
                                    </a>
                                    <a href="?tab=testimonials&delete_testimonial=<?php echo $testimonial['id']; ?>" 
                                       onclick="return confirm('Are you sure you want to delete this testimonial?')"
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
    
    <!-- Add/Edit Testimonial Form -->
    <div class="border rounded-lg overflow-hidden">
        <div class="bg-gray-50 p-4 border-b">
            <h3 class="font-medium text-gray-900">
                <?php echo $editTestimonial ? 'Edit Testimonial' : 'Add New Testimonial'; ?>
            </h3>
            <p class="text-sm text-gray-500 mt-1">
                <?php echo $editTestimonial ? 'Update the selected testimonial' : 'Add a new client testimonial to your homepage'; ?>
            </p>
        </div>
        
        <div class="p-4">
            <form method="POST" action="" enctype="multipart/form-data" class="space-y-4">
                <?php if ($editTestimonial): ?>
                    <input type="hidden" name="testimonial_id" value="<?php echo $editTestimonial['id']; ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="testimonial_name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" id="testimonial_name" name="testimonial_name" required
                               value="<?php echo $editTestimonial ? htmlspecialchars($editTestimonial['name']) : ''; ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="testimonial_position" class="block text-sm font-medium text-gray-700 mb-1">Position/Role</label>
                        <input type="text" id="testimonial_position" name="testimonial_position" required
                               value="<?php echo $editTestimonial ? htmlspecialchars($editTestimonial['position']) : ''; ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Example: Mahasiswa, Dosen, etc.</p>
                    </div>
                </div>
                
                <div>
                    <label for="testimonial_content" class="block text-sm font-medium text-gray-700 mb-1">Testimonial Content</label>
                    <textarea id="testimonial_content" name="testimonial_content" required rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo $editTestimonial ? htmlspecialchars($editTestimonial['content']) : ''; ?></textarea>
                    <p class="mt-1 text-xs text-gray-500">The testimonial text from the client (recommended: 100-150 words)</p>
                </div>
                
                <div>
                    <label for="testimonial_image" class="block text-sm font-medium text-gray-700 mb-1">Profile Image Path</label>
                    <div class="flex">
                        <input type="text" id="testimonial_image" name="testimonial_image" 
                            value="<?php echo $editTestimonial ? htmlspecialchars($editTestimonial['image']) : 'assets/images/clients-img/default-profile.jpg'; ?>"
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <a href="../../../<?php echo $editTestimonial ? htmlspecialchars($editTestimonial['image']) : 'assets/images/clients-img/default-profile.jpg'; ?>" 
                           target="_blank" 
                           class="px-4 py-2 bg-gray-100 text-gray-600 border-t border-r border-b border-gray-300 hover:bg-gray-200 transition-colors">
                            <i class='bx bx-show'></i>
                        </a>
                    </div>
                    <div class="mt-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Upload New Image</label>
                        <div class="flex items-center">
                            <input type="file" id="testimonial_image_upload" name="testimonial_image_upload" class="hidden"
                                accept="image/*" onchange="updateFileName(this)">
                            <label for="testimonial_image_upload" 
                                class="cursor-pointer px-4 py-2 bg-gray-200 text-gray-700 rounded-l-md hover:bg-gray-300 transition-colors">
                                Choose file
                            </label>
                            <span id="testimonial_image_upload_name" 
                                class="px-4 py-2 bg-gray-100 text-gray-600 border border-gray-300 rounded-r-md flex-1">
                                No file chosen
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Recommended size: Square image (e.g., 200x200 pixels)</p>
                    </div>
                </div>
                
                <div class="flex items-center">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="testimonial_is_active" class="h-4 w-4 text-blue-600 rounded"
                               <?php echo (!$editTestimonial || $editTestimonial['is_active']) ? 'checked' : ''; ?>>
                        <span class="ml-2 text-sm text-gray-700">Active</span>
                    </label>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <?php if ($editTestimonial): ?>
                        <a href="?tab=testimonials" class="px-4 py-2 bg-gray-200 text-gray-700 font-medium rounded-md hover:bg-gray-300 transition-colors">
                            Cancel
                        </a>
                    <?php endif; ?>
                    
                    <button type="submit" name="<?php echo $editTestimonial ? 'update_testimonial' : 'add_testimonial'; ?>" 
                            class="px-6 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition-colors">
                        <i class='bx bx-<?php echo $editTestimonial ? 'save' : 'plus'; ?> mr-1'></i>
                        <?php echo $editTestimonial ? 'Update Testimonial' : 'Add Testimonial'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Preview -->
    <div class="border rounded-lg overflow-hidden">
        <div class="bg-gray-50 p-4 border-b">
            <h3 class="font-medium text-gray-900">Testimonials Preview</h3>
            <p class="text-sm text-gray-500 mt-1">A simplified preview of how the testimonials section will appear</p>
        </div>
        
        <div class="p-4">
            <div class="bg-gray-100 p-6 rounded-lg">
                <div class="text-center mb-6">
                    <span class="text-blue-600 text-sm font-medium"><?php echo htmlspecialchars($testimonialsHeader['subtitle']); ?></span>
                    <h2 class="text-xl font-bold text-gray-800 mt-1"><?php echo htmlspecialchars($testimonialsHeader['title']); ?></h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <?php 
                    // Get active testimonials for preview
                    $activeTestimonials = array_filter($testimonials, function($testimonial) {
                        return $testimonial['is_active'] == 1;
                    });
                    
                    // Show only first 3 testimonials in preview
                    $previewTestimonials = array_slice($activeTestimonials, 0, 3);
                    
                    if (empty($previewTestimonials)):
                    ?>
                        <div class="col-span-full text-center py-6 text-gray-500">
                            <p>No active testimonials to display. Add testimonials above to see them in the preview.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($previewTestimonials as $testimonial): ?>
                            <div class="bg-white p-4 rounded-lg shadow-sm flex flex-col items-center">
                                <img src="../../../<?php echo htmlspecialchars($testimonial['image']); ?>" alt="<?php echo htmlspecialchars($testimonial['name']); ?>" class="h-16 w-16 rounded-full object-cover mb-3">
                                <h4 class="font-semibold text-gray-800"><?php echo htmlspecialchars($testimonial['name']); ?></h4>
                                <p class="text-sm text-gray-500 mb-2"><?php echo htmlspecialchars($testimonial['position']); ?></p>
                                <p class="text-sm text-gray-600 text-center"><?php echo htmlspecialchars(substr($testimonial['content'], 0, 150) . (strlen($testimonial['content']) > 150 ? '...' : '')); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-2">This is a simplified preview. The actual appearance may vary. All active testimonials will be displayed in the carousel on the homepage.</p>
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