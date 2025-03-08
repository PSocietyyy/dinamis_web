<?php
// Banner Section Component
// File path: admin/pages/edit-pages/homepagecomp/banner.php

// Get banner section ID
$bannerSectionId = 0;
try {
    $stmt = $conn->prepare("SELECT id FROM homepage_sections WHERE section_key = 'banner'");
    $stmt->execute();
    $bannerSectionId = $stmt->fetchColumn();
} catch(PDOException $e) {
    echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error fetching banner section: " . $e->getMessage() . "</div>";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_banner'])) {
    try {
        // Begin transaction
        $conn->beginTransaction();
        
        // Update each content field
        $fieldsToUpdate = [
            'title', 'subtitle', 'button1_text', 'button1_link', 
            'button2_text', 'button2_link', 'banner_image', 'banner_shape'
        ];
        
        foreach ($fieldsToUpdate as $field) {
            if (isset($_POST[$field])) {
                $value = trim($_POST[$field]);
                
                $stmt = $conn->prepare("UPDATE homepage_content 
                                       SET content_value = :value 
                                       WHERE section_id = :section_id AND content_key = :content_key");
                $stmt->bindParam(':value', $value);
                $stmt->bindParam(':section_id', $bannerSectionId);
                $stmt->bindParam(':content_key', $field);
                $stmt->execute();
            }
        }
        
        // Process image uploads if any
        if (!empty($_FILES['banner_image_upload']['name'])) {
            // Handle banner image upload
            $uploadDir = '../../../assets/images/home-three/';
            $fileName = basename($_FILES['banner_image_upload']['name']);
            $uploadFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['banner_image_upload']['tmp_name'], $uploadFile)) {
                // Update image path in database
                $imagePath = 'assets/images/home-three/' . $fileName;
                
                $stmt = $conn->prepare("UPDATE homepage_content 
                                       SET content_value = :value 
                                       WHERE section_id = :section_id AND content_key = 'banner_image'");
                $stmt->bindParam(':value', $imagePath);
                $stmt->bindParam(':section_id', $bannerSectionId);
                $stmt->execute();
            }
        }
        
        // Process banner shape image upload if any
        if (!empty($_FILES['banner_shape_upload']['name'])) {
            // Handle banner shape image upload
            $uploadDir = '../../../assets/images/home-three/';
            $fileName = basename($_FILES['banner_shape_upload']['name']);
            $uploadFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['banner_shape_upload']['tmp_name'], $uploadFile)) {
                // Update image path in database
                $imagePath = 'assets/images/home-three/' . $fileName;
                
                $stmt = $conn->prepare("UPDATE homepage_content 
                                       SET content_value = :value 
                                       WHERE section_id = :section_id AND content_key = 'banner_shape'");
                $stmt->bindParam(':value', $imagePath);
                $stmt->bindParam(':section_id', $bannerSectionId);
                $stmt->execute();
            }
        }
        
        $conn->commit();
        
        echo "<div class='bg-green-100 text-green-700 p-4 rounded-lg mb-4'>Banner section updated successfully!</div>";
    } catch(PDOException $e) {
        $conn->rollBack();
        echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error updating banner section: " . $e->getMessage() . "</div>";
    }
}

// Get banner content
$bannerContent = [];
try {
    $stmt = $conn->prepare("SELECT content_key, content_value, content_type 
                          FROM homepage_content 
                          WHERE section_id = :section_id");
    $stmt->bindParam(':section_id', $bannerSectionId);
    $stmt->execute();
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($results as $row) {
        $bannerContent[$row['content_key']] = $row['content_value'];
    }
} catch(PDOException $e) {
    echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error fetching banner content: " . $e->getMessage() . "</div>";
}

// Default values if content is not found
$defaults = [
    'title' => 'Platform Academic Digital With Excellent Quality',
    'subtitle' => 'Platform Akademi Merdeka membantu setiap insan akademisi dengan pelayanan yang eksklusif',
    'button1_text' => 'Learn More',
    'button1_link' => '#',
    'button2_text' => 'Whatsapp',
    'button2_link' => 'https://wa.me/6287735426107',
    'banner_image' => 'assets/images/home-three/home-main-pic.png',
    'banner_shape' => 'assets/images/home-three/home-three-shape.png'
];

// Merge defaults with actual content
$bannerContent = array_merge($defaults, $bannerContent);
?>

<form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
    <div class="border rounded-lg overflow-hidden">
        <div class="bg-gray-50 p-4 border-b">
            <h3 class="font-medium text-gray-900">Banner and Hero Section</h3>
            <p class="text-sm text-gray-500 mt-1">Edit the main banner content that appears at the top of your homepage</p>
        </div>
        
        <div class="p-4 space-y-4">
            <!-- Title and Subtitle -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Banner Title</label>
                <input type="text" id="title" name="title" 
                       value="<?php echo htmlspecialchars($bannerContent['title']); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
                <label for="subtitle" class="block text-sm font-medium text-gray-700 mb-1">Banner Subtitle</label>
                <textarea id="subtitle" name="subtitle" rows="2"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($bannerContent['subtitle']); ?></textarea>
            </div>
            
            <!-- Buttons -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="button1_text" class="block text-sm font-medium text-gray-700 mb-1">Button 1 Text</label>
                    <input type="text" id="button1_text" name="button1_text" 
                           value="<?php echo htmlspecialchars($bannerContent['button1_text']); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="button1_link" class="block text-sm font-medium text-gray-700 mb-1">Button 1 Link</label>
                    <input type="text" id="button1_link" name="button1_link" 
                           value="<?php echo htmlspecialchars($bannerContent['button1_link']); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="button2_text" class="block text-sm font-medium text-gray-700 mb-1">Button 2 Text</label>
                    <input type="text" id="button2_text" name="button2_text" 
                           value="<?php echo htmlspecialchars($bannerContent['button2_text']); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="button2_link" class="block text-sm font-medium text-gray-700 mb-1">Button 2 Link</label>
                    <input type="text" id="button2_link" name="button2_link" 
                           value="<?php echo htmlspecialchars($bannerContent['button2_link']); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            
            <!-- Images -->
            <div>
                <label for="banner_image" class="block text-sm font-medium text-gray-700 mb-1">Banner Image Path</label>
                <div class="flex">
                    <input type="text" id="banner_image" name="banner_image" 
                        value="<?php echo htmlspecialchars($bannerContent['banner_image']); ?>"
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <a href="../../../<?php echo htmlspecialchars($bannerContent['banner_image']); ?>" target="_blank" 
                        class="px-4 py-2 bg-gray-100 text-gray-600 border-t border-r border-b border-gray-300 hover:bg-gray-200 transition-colors">
                        <i class='bx bx-show'></i>
                    </a>
                </div>
                <div class="mt-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Upload New Banner Image</label>
                    <div class="flex items-center">
                        <input type="file" id="banner_image_upload" name="banner_image_upload" class="hidden"
                            accept="image/*" onchange="updateFileName(this)">
                        <label for="banner_image_upload" 
                            class="cursor-pointer px-4 py-2 bg-gray-200 text-gray-700 rounded-l-md hover:bg-gray-300 transition-colors">
                            Choose file
                        </label>
                        <span id="banner_image_name" 
                            class="px-4 py-2 bg-gray-100 text-gray-600 border border-gray-300 rounded-r-md flex-1">
                            No file chosen
                        </span>
                    </div>
                </div>
            </div>
            
            <div>
                <label for="banner_shape" class="block text-sm font-medium text-gray-700 mb-1">Banner Shape Image Path</label>
                <div class="flex">
                    <input type="text" id="banner_shape" name="banner_shape" 
                        value="<?php echo htmlspecialchars($bannerContent['banner_shape']); ?>"
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <a href="../../../<?php echo htmlspecialchars($bannerContent['banner_shape']); ?>" target="_blank" 
                        class="px-4 py-2 bg-gray-100 text-gray-600 border-t border-r border-b border-gray-300 hover:bg-gray-200 transition-colors">
                        <i class='bx bx-show'></i>
                    </a>
                </div>
                <div class="mt-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Upload New Banner Shape</label>
                    <div class="flex items-center">
                        <input type="file" id="banner_shape_upload" name="banner_shape_upload" class="hidden"
                            accept="image/*" onchange="updateFileName(this)">
                        <label for="banner_shape_upload" 
                            class="cursor-pointer px-4 py-2 bg-gray-200 text-gray-700 rounded-l-md hover:bg-gray-300 transition-colors">
                            Choose file
                        </label>
                        <span id="banner_shape_name" 
                            class="px-4 py-2 bg-gray-100 text-gray-600 border border-gray-300 rounded-r-md flex-1">
                            No file chosen
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Preview -->
    <div class="border rounded-lg overflow-hidden">
        <div class="bg-gray-50 p-4 border-b">
            <h3 class="font-medium text-gray-900">Banner Preview</h3>
            <p class="text-sm text-gray-500 mt-1">A simplified preview of how the banner will appear</p>
        </div>
        
        <div class="p-4">
            <div class="bg-blue-50 p-6 rounded-lg">
                <div class="flex flex-col lg:flex-row items-center">
                    <div class="w-full lg:w-1/2 mb-4 lg:mb-0">
                        <h2 class="text-2xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($bannerContent['title']); ?></h2>
                        <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($bannerContent['subtitle']); ?></p>
                        <div class="flex flex-wrap space-x-2">
                            <span class="px-4 py-2 bg-blue-600 text-white rounded-full inline-block"><?php echo htmlspecialchars($bannerContent['button1_text']); ?></span>
                            <span class="px-4 py-2 bg-blue-500 text-white rounded-full inline-block"><?php echo htmlspecialchars($bannerContent['button2_text']); ?></span>
                        </div>
                    </div>
                    <div class="w-full lg:w-1/2 text-center">
                        <div class="relative inline-block">
                            <img src="../../../<?php echo htmlspecialchars($bannerContent['banner_image']); ?>" alt="Banner Image" class="max-h-48 lg:max-h-40 inline-block">
                        </div>
                    </div>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-2">This is a simplified preview. The actual appearance may vary based on the theme and layout.</p>
        </div>
    </div>
    
    <div class="flex justify-end">
        <button type="submit" name="update_banner" class="px-6 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition-colors">
            <i class='bx bx-save mr-2'></i> Save Banner Changes
        </button>
    </div>
</form>

<script>
    function updateFileName(input) {
        const fileName = input.files[0].name;
        const fileNameElement = document.getElementById(input.id + '_name');
        if (fileNameElement) {
            fileNameElement.textContent = fileName;
        }
    }
</script>