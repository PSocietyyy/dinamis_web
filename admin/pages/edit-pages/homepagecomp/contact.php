<?php
// Contact Section Component
// File path: admin/pages/edit-pages/homepagecomp/contact.php

// Get contact section ID
$contactSectionId = 0;
try {
    $stmt = $conn->prepare("SELECT id FROM homepage_sections WHERE section_key = 'contact'");
    $stmt->execute();
    $contactSectionId = $stmt->fetchColumn();
} catch(PDOException $e) {
    echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error fetching contact section: " . $e->getMessage() . "</div>";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_contact'])) {
    try {
        // Begin transaction
        $conn->beginTransaction();
        
        // Update each content field
        $fieldsToUpdate = [
            'title', 'subtitle', 'button_text', 'button_link'
        ];
        
        foreach ($fieldsToUpdate as $field) {
            if (isset($_POST[$field])) {
                $value = trim($_POST[$field]);
                
                $stmt = $conn->prepare("UPDATE homepage_content 
                                       SET content_value = :value 
                                       WHERE section_id = :section_id AND content_key = :content_key");
                $stmt->bindParam(':value', $value);
                $stmt->bindParam(':section_id', $contactSectionId);
                $stmt->bindParam(':content_key', $field);
                $stmt->execute();
            }
        }
        
        $conn->commit();
        
        echo "<div class='bg-green-100 text-green-700 p-4 rounded-lg mb-4'>Contact section updated successfully!</div>";
    } catch(PDOException $e) {
        $conn->rollBack();
        echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error updating contact section: " . $e->getMessage() . "</div>";
    }
}

// Get contact content
$contactContent = [];
try {
    $stmt = $conn->prepare("SELECT content_key, content_value, content_type 
                          FROM homepage_content 
                          WHERE section_id = :section_id");
    $stmt->bindParam(':section_id', $contactSectionId);
    $stmt->execute();
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($results as $row) {
        $contactContent[$row['content_key']] = $row['content_value'];
    }
} catch(PDOException $e) {
    echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error fetching contact content: " . $e->getMessage() . "</div>";
}

// Default values if content is not found
$defaults = [
    'title' => 'Kami melayani berbagai persoalan dengan solusi yang tepat',
    'subtitle' => 'Hubungi Kami',
    'button_text' => 'Whatsapp',
    'button_link' => 'https://wa.me/6287735426107'
];

// Merge defaults with actual content
$contactContent = array_merge($defaults, $contactContent);
?>

<form method="POST" action="" class="space-y-6">
    <div class="border rounded-lg overflow-hidden">
        <div class="bg-gray-50 p-4 border-b">
            <h3 class="font-medium text-gray-900">Contact/CTA Section</h3>
            <p class="text-sm text-gray-500 mt-1">Edit the call-to-action section that appears on your homepage</p>
        </div>
        
        <div class="p-4 space-y-4">
            <!-- Title and Subtitle -->
            <div>
                <label for="subtitle" class="block text-sm font-medium text-gray-700 mb-1">Section Subtitle</label>
                <input type="text" id="subtitle" name="subtitle" 
                       value="<?php echo htmlspecialchars($contactContent['subtitle']); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Section Title/Headline</label>
                <input type="text" id="title" name="title" 
                       value="<?php echo htmlspecialchars($contactContent['title']); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <!-- Button -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="button_text" class="block text-sm font-medium text-gray-700 mb-1">Button Text</label>
                    <input type="text" id="button_text" name="button_text" 
                           value="<?php echo htmlspecialchars($contactContent['button_text']); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label for="button_link" class="block text-sm font-medium text-gray-700 mb-1">Button Link</label>
                    <input type="text" id="button_link" name="button_link" 
                           value="<?php echo htmlspecialchars($contactContent['button_link']); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-gray-500">Example: https://wa.me/6287735426107</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Preview -->
    <div class="border rounded-lg overflow-hidden">
        <div class="bg-gray-50 p-4 border-b">
            <h3 class="font-medium text-gray-900">Contact Section Preview</h3>
            <p class="text-sm text-gray-500 mt-1">A simplified preview of how the contact section will appear</p>
        </div>
        
        <div class="p-4">
            <div class="bg-blue-100 text-blue-800 p-6 rounded-lg text-center">
                <span class="text-blue-600 text-sm font-medium"><?php echo htmlspecialchars($contactContent['subtitle']); ?></span>
                <h2 class="text-xl font-bold text-gray-800 mt-1 mb-4"><?php echo htmlspecialchars($contactContent['title']); ?></h2>
                <div>
                    <a href="<?php echo $contactContent['button_link']; ?>" target="_blank" class="inline-block px-5 py-2.5 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition-colors">
                        <?php echo htmlspecialchars($contactContent['button_text']); ?>
                    </a>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-2">This is a simplified preview. The actual appearance may vary based on the theme and layout.</p>
        </div>
    </div>
    
    <div class="flex justify-end">
        <button type="submit" name="update_contact" class="px-6 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition-colors">
            <i class='bx bx-save mr-2'></i> Save Contact Changes
        </button>
    </div>
</form>