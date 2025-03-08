<?php
// About Section Component
// File path: admin/pages/edit-pages/homepagecomp/about.php

// Get about section ID
$aboutSectionId = 0;
try {
    $stmt = $conn->prepare("SELECT id FROM homepage_sections WHERE section_key = 'about'");
    $stmt->execute();
    $aboutSectionId = $stmt->fetchColumn();
} catch(PDOException $e) {
    echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error fetching about section: " . $e->getMessage() . "</div>";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_about'])) {
    try {
        // Begin transaction
        $conn->beginTransaction();
        
        // Update each content field
        $fieldsToUpdate = [
            'title', 'subtitle', 'description', 'image', 
            'card1_title', 'card1_icon', 'card1_text', 
            'card2_title', 'card2_icon', 'card2_text'
        ];
        
        foreach ($fieldsToUpdate as $field) {
            if (isset($_POST[$field])) {
                $value = trim($_POST[$field]);
                
                $stmt = $conn->prepare("UPDATE homepage_content 
                                      SET content_value = :value 
                                      WHERE section_id = :section_id AND content_key = :content_key");
                $stmt->bindParam(':value', $value);
                $stmt->bindParam(':section_id', $aboutSectionId);
                $stmt->bindParam(':content_key', $field);
                $stmt->execute();
            }
        }
        
        // Process image upload if any
        if (!empty($_FILES['about_image_upload']['name'])) {
            // Handle about image upload
            $uploadDir = '../../../assets/images/about/';
            $fileName = basename($_FILES['about_image_upload']['name']);
            $uploadFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['about_image_upload']['tmp_name'], $uploadFile)) {
                // Update image path in database
                $imagePath = 'assets/images/about/' . $fileName;
                
                $stmt = $conn->prepare("UPDATE homepage_content 
                                      SET content_value = :value 
                                      WHERE section_id = :section_id AND content_key = 'image'");
                $stmt->bindParam(':value', $imagePath);
                $stmt->bindParam(':section_id', $aboutSectionId);
                $stmt->execute();
            }
        }
        
        $conn->commit();
        
        echo "<div class='bg-green-100 text-green-700 p-4 rounded-lg mb-4'>About section updated successfully!</div>";
    } catch(PDOException $e) {
        $conn->rollBack();
        echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error updating about section: " . $e->getMessage() . "</div>";
    }
}

// Get about content
$aboutContent = [];
try {
    $stmt = $conn->prepare("SELECT content_key, content_value, content_type 
                          FROM homepage_content 
                          WHERE section_id = :section_id");
    $stmt->bindParam(':section_id', $aboutSectionId);
    $stmt->execute();
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($results as $row) {
        $aboutContent[$row['content_key']] = $row['content_value'];
    }
} catch(PDOException $e) {
    echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error fetching about content: " . $e->getMessage() . "</div>";
}

// Default values if content is not found
$defaults = [
    'title' => 'Tentang Kita',
    'subtitle' => 'About Us',
    'description' => 'Akademi Merdeka mempunyai ruang lingkup dalam bidang akademisi yang tujuannya ialah membantu setiap insan akademisi dengan berbagai problematika yang sedang dihadapi.',
    'image' => 'assets/images/about/home-about.png',
    'card1_title' => 'Experience',
    'card1_icon' => 'flaticon-practice',
    'card1_text' => 'Berbagai macam persoalan sudah kami pecahkan dengan prosedur yang efektif.',
    'card2_title' => 'Quick Support',
    'card2_icon' => 'flaticon-help',
    'card2_text' => 'Dukungan setiap persoalan akan didampingi oleh satu supervisi yang expert.'
];

// Merge defaults with actual content
$aboutContent = array_merge($defaults, $aboutContent);
?>

<form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
    <div class="border rounded-lg overflow-hidden">
        <div class="bg-gray-50 p-4 border-b">
            <h3 class="font-medium text-gray-900">About Section</h3>
            <p class="text-sm text-gray-500 mt-1">Edit the About Us section content that appears on your homepage</p>
        </div>
        
        <div class="p-4 space-y-4">
            <!-- Title and Subtitle -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Section Title</label>
                    <input type="text" id="title" name="title" 
                           value="<?php echo htmlspecialchars($aboutContent['title']); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label for="subtitle" class="block text-sm font-medium text-gray-700 mb-1">Section Subtitle</label>
                    <input type="text" id="subtitle" name="subtitle" 
                           value="<?php echo htmlspecialchars($aboutContent['subtitle']); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            
            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">About Description</label>
                <textarea id="description" name="description" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($aboutContent['description']); ?></textarea>
            </div>
            
            <!-- About Image -->
            <div>
                <label for="image" class="block text-sm font-medium text-gray-700 mb-1">About Image Path</label>
                <div class="flex">
                    <input type="text" id="image" name="image" 
                        value="<?php echo htmlspecialchars($aboutContent['image']); ?>"
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <a href="../../../<?php echo htmlspecialchars($aboutContent['image']); ?>" target="_blank" 
                        class="px-4 py-2 bg-gray-100 text-gray-600 border-t border-r border-b border-gray-300 hover:bg-gray-200 transition-colors">
                        <i class='bx bx-show'></i>
                    </a>
                </div>
                <div class="mt-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Upload New Image</label>
                    <div class="flex items-center">
                        <input type="file" id="about_image_upload" name="about_image_upload" class="hidden"
                            accept="image/*" onchange="updateFileName(this)">
                        <label for="about_image_upload" 
                            class="cursor-pointer px-4 py-2 bg-gray-200 text-gray-700 rounded-l-md hover:bg-gray-300 transition-colors">
                            Choose file
                        </label>
                        <span id="about_image_upload_name" 
                            class="px-4 py-2 bg-gray-100 text-gray-600 border border-gray-300 rounded-r-md flex-1">
                            No file chosen
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Card 1 -->
            <div class="border rounded-lg p-4 bg-gray-50">
                <h4 class="font-medium text-gray-800 mb-3">Card 1</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="card1_title" class="block text-sm font-medium text-gray-700 mb-1">Card Title</label>
                        <input type="text" id="card1_title" name="card1_title" 
                               value="<?php echo htmlspecialchars($aboutContent['card1_title']); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="card1_icon" class="block text-sm font-medium text-gray-700 mb-1">Card Icon</label>
                        <input type="text" id="card1_icon" name="card1_icon" 
                               value="<?php echo htmlspecialchars($aboutContent['card1_icon']); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Flaticon class name (e.g., flaticon-practice)</p>
                    </div>
                </div>
                
                <div>
                    <label for="card1_text" class="block text-sm font-medium text-gray-700 mb-1">Card Text</label>
                    <textarea id="card1_text" name="card1_text" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($aboutContent['card1_text']); ?></textarea>
                </div>
            </div>
            
            <!-- Card 2 -->
            <div class="border rounded-lg p-4 bg-gray-50">
                <h4 class="font-medium text-gray-800 mb-3">Card 2</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="card2_title" class="block text-sm font-medium text-gray-700 mb-1">Card Title</label>
                        <input type="text" id="card2_title" name="card2_title" 
                               value="<?php echo htmlspecialchars($aboutContent['card2_title']); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="card2_icon" class="block text-sm font-medium text-gray-700 mb-1">Card Icon</label>
                        <input type="text" id="card2_icon" name="card2_icon" 
                               value="<?php echo htmlspecialchars($aboutContent['card2_icon']); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Flaticon class name (e.g., flaticon-help)</p>
                    </div>
                </div>
                
                <div>
                    <label for="card2_text" class="block text-sm font-medium text-gray-700 mb-1">Card Text</label>
                    <textarea id="card2_text" name="card2_text" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($aboutContent['card2_text']); ?></textarea>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Preview -->
    <div class="border rounded-lg overflow-hidden">
        <div class="bg-gray-50 p-4 border-b">
            <h3 class="font-medium text-gray-900">About Section Preview</h3>
            <p class="text-sm text-gray-500 mt-1">A simplified preview of how the About section will appear</p>
        </div>
        
        <div class="p-4">
            <div class="bg-gray-100 p-6 rounded-lg">
                <div class="flex flex-col md:flex-row items-center">
                    <div class="w-full md:w-1/2 mb-4 md:mb-0 md:pr-6">
                        <img src="../../../<?php echo htmlspecialchars($aboutContent['image']); ?>" alt="About Image" class="w-full max-h-60 object-contain">
                    </div>
                    <div class="w-full md:w-1/2">
                        <div class="mb-4">
                            <span class="text-blue-600 text-sm font-medium"><?php echo htmlspecialchars($aboutContent['subtitle']); ?></span>
                            <h2 class="text-xl font-bold text-gray-800 mt-1"><?php echo htmlspecialchars($aboutContent['title']); ?></h2>
                            <p class="text-gray-600 text-sm mt-2"><?php echo htmlspecialchars($aboutContent['description']); ?></p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-white p-3 rounded-lg shadow-sm">
                                <h3 class="font-medium text-gray-800"><?php echo htmlspecialchars($aboutContent['card1_title']); ?></h3>
                                <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($aboutContent['card1_text']); ?></p>
                            </div>
                            <div class="bg-white p-3 rounded-lg shadow-sm">
                                <h3 class="font-medium text-gray-800"><?php echo htmlspecialchars($aboutContent['card2_title']); ?></h3>
                                <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($aboutContent['card2_text']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-2">This is a simplified preview. The actual appearance may vary based on the theme and layout.</p>
        </div>
    </div>
    
    <div class="flex justify-end">
        <button type="submit" name="update_about" class="px-6 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition-colors">
            <i class='bx bx-save mr-2'></i> Save About Changes
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