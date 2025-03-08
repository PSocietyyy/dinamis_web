<?php
// Products Section Component
// File path: admin/pages/edit-pages/homepagecomp/products.php

// Get products section ID
$productsSectionId = 0;
try {
    $stmt = $conn->prepare("SELECT id FROM homepage_sections WHERE section_key = 'products'");
    $stmt->execute();
    $productsSectionId = $stmt->fetchColumn();
} catch(PDOException $e) {
    echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error fetching products section: " . $e->getMessage() . "</div>";
}

// Handle section header form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_products_header'])) {
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
            $stmt->bindParam(':section_id', $productsSectionId);
            $stmt->execute();
            
            // Update subtitle
            $stmt = $conn->prepare("UPDATE homepage_content 
                                  SET content_value = :value 
                                  WHERE section_id = :section_id AND content_key = 'subtitle'");
            $stmt->bindParam(':value', $subtitle);
            $stmt->bindParam(':section_id', $productsSectionId);
            $stmt->execute();
        }
        
        $conn->commit();
        
        echo "<div class='bg-green-100 text-green-700 p-4 rounded-lg mb-4'>Products section header updated successfully!</div>";
    } catch(PDOException $e) {
        $conn->rollBack();
        echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error updating products section header: " . $e->getMessage() . "</div>";
    }
}

// Handle adding new product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    try {
        // Get form data
        $title = trim($_POST['product_title']);
        $position = isset($_POST['product_position']) ? (int)$_POST['product_position'] : 0;
        $isActive = isset($_POST['product_is_active']) ? 1 : 0;
        
        // Handle icon upload if provided
        $iconPath = trim($_POST['product_icon']);
        
        if (!empty($_FILES['product_icon_upload']['name'])) {
            $uploadDir = '../../../assets/images/services/';
            $fileName = basename($_FILES['product_icon_upload']['name']);
            $uploadFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['product_icon_upload']['tmp_name'], $uploadFile)) {
                $iconPath = 'assets/images/services/' . $fileName;
            }
        }
        
        // Insert new product
        $stmt = $conn->prepare("INSERT INTO products_section (title, icon, position, is_active) 
                              VALUES (:title, :icon, :position, :is_active)");
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':icon', $iconPath);
        $stmt->bindParam(':position', $position);
        $stmt->bindParam(':is_active', $isActive);
        $stmt->execute();
        
        echo "<div class='bg-green-100 text-green-700 p-4 rounded-lg mb-4'>New product added successfully!</div>";
    } catch(PDOException $e) {
        echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error adding new product: " . $e->getMessage() . "</div>";
    }
}

// Handle updating product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    try {
        $productId = (int)$_POST['product_id'];
        $title = trim($_POST['product_title']);
        $position = isset($_POST['product_position']) ? (int)$_POST['product_position'] : 0;
        $isActive = isset($_POST['product_is_active']) ? 1 : 0;
        
        // Handle icon upload if provided
        $iconPath = trim($_POST['product_icon']);
        
        if (!empty($_FILES['product_icon_upload']['name'])) {
            $uploadDir = '../../../assets/images/services/';
            $fileName = basename($_FILES['product_icon_upload']['name']);
            $uploadFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['product_icon_upload']['tmp_name'], $uploadFile)) {
                $iconPath = 'assets/images/services/' . $fileName;
            }
        }
        
        // Update product
        $stmt = $conn->prepare("UPDATE products_section 
                              SET title = :title, icon = :icon, 
                                  position = :position, is_active = :is_active 
                              WHERE id = :id");
        $stmt->bindParam(':id', $productId);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':icon', $iconPath);
        $stmt->bindParam(':position', $position);
        $stmt->bindParam(':is_active', $isActive);
        $stmt->execute();
        
        echo "<div class='bg-green-100 text-green-700 p-4 rounded-lg mb-4'>Product updated successfully!</div>";
    } catch(PDOException $e) {
        echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error updating product: " . $e->getMessage() . "</div>";
    }
}

// Handle deleting product
if (isset($_GET['delete_product']) && !empty($_GET['delete_product'])) {
    try {
        $productId = (int)$_GET['delete_product'];
        
        $stmt = $conn->prepare("DELETE FROM products_section WHERE id = :id");
        $stmt->bindParam(':id', $productId);
        $stmt->execute();
        
        echo "<div class='bg-green-100 text-green-700 p-4 rounded-lg mb-4'>Product deleted successfully!</div>";
    } catch(PDOException $e) {
        echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error deleting product: " . $e->getMessage() . "</div>";
    }
}

// Get products section header
$productsHeader = [
    'title' => 'Kami memberikan solusi terbaik dengan produk terpercaya dan berkualitas',
    'subtitle' => 'Produk Kami'
];

try {
    $stmt = $conn->prepare("SELECT content_key, content_value 
                          FROM homepage_content 
                          WHERE section_id = :section_id AND content_key IN ('title', 'subtitle')");
    $stmt->bindParam(':section_id', $productsSectionId);
    $stmt->execute();
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($results as $row) {
        $productsHeader[$row['content_key']] = $row['content_value'];
    }
} catch(PDOException $e) {
    echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error fetching products header: " . $e->getMessage() . "</div>";
}

// Get all products
$products = [];
try {
    $stmt = $conn->query("SELECT * FROM products_section ORDER BY position, id");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error fetching products: " . $e->getMessage() . "</div>";
}

// Get specific product for editing
$editProduct = null;
if (isset($_GET['edit_product']) && !empty($_GET['edit_product'])) {
    try {
        $productId = (int)$_GET['edit_product'];
        
        $stmt = $conn->prepare("SELECT * FROM products_section WHERE id = :id");
        $stmt->bindParam(':id', $productId);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $editProduct = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch(PDOException $e) {
        echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error fetching product: " . $e->getMessage() . "</div>";
    }
}
?>

<div class="space-y-8">
    <!-- Products Section Header -->
    <div class="border rounded-lg overflow-hidden">
        <div class="bg-gray-50 p-4 border-b">
            <h3 class="font-medium text-gray-900">Products Section Header</h3>
            <p class="text-sm text-gray-500 mt-1">Edit the products section title and subtitle</p>
        </div>
        
        <div class="p-4">
            <form method="POST" action="" class="space-y-4">
                <div>
                    <label for="subtitle" class="block text-sm font-medium text-gray-700 mb-1">Section Subtitle</label>
                    <input type="text" id="subtitle" name="subtitle" 
                           value="<?php echo htmlspecialchars($productsHeader['subtitle']); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Section Title</label>
                    <input type="text" id="title" name="title" 
                           value="<?php echo htmlspecialchars($productsHeader['title']); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" name="update_products_header" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition-colors">
                        <i class='bx bx-save mr-1'></i> Save Header
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Products List -->
    <div class="border rounded-lg overflow-hidden">
        <div class="bg-gray-50 p-4 border-b flex justify-between items-center">
            <div>
                <h3 class="font-medium text-gray-900">Products List</h3>
                <p class="text-sm text-gray-500 mt-1">Manage the products displayed on your homepage</p>
            </div>
            
            <a href="?tab=products" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition-colors">
                <i class='bx bx-plus mr-1'></i> Add New Product
            </a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Icon</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                No products found. Add your first product using the form below.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <img class="h-10 w-10 object-contain" src="../../../<?php echo htmlspecialchars($product['icon']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($product['title']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $product['position']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($product['is_active']): ?>
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
                                    <a href="?tab=products&edit_product=<?php echo $product['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class='bx bx-edit'></i> Edit
                                    </a>
                                    <a href="?tab=products&delete_product=<?php echo $product['id']; ?>" 
                                       onclick="return confirm('Are you sure you want to delete this product?')"
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
    
    <!-- Add/Edit Product Form -->
    <div class="border rounded-lg overflow-hidden">
        <div class="bg-gray-50 p-4 border-b">
            <h3 class="font-medium text-gray-900">
                <?php echo $editProduct ? 'Edit Product' : 'Add New Product'; ?>
            </h3>
            <p class="text-sm text-gray-500 mt-1">
                <?php echo $editProduct ? 'Update the selected product' : 'Add a new product to your homepage'; ?>
            </p>
        </div>
        
        <div class="p-4">
            <form method="POST" action="" enctype="multipart/form-data" class="space-y-4">
                <?php if ($editProduct): ?>
                    <input type="hidden" name="product_id" value="<?php echo $editProduct['id']; ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="product_title" class="block text-sm font-medium text-gray-700 mb-1">Product Title</label>
                        <input type="text" id="product_title" name="product_title" required
                               value="<?php echo $editProduct ? htmlspecialchars($editProduct['title']) : ''; ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="product_position" class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                        <input type="number" id="product_position" name="product_position" min="0"
                               value="<?php echo $editProduct ? $editProduct['position'] : count($products) + 1; ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <div>
                    <label for="product_icon" class="block text-sm font-medium text-gray-700 mb-1">Product Icon Path</label>
                    <div class="flex">
                        <input type="text" id="product_icon" name="product_icon" 
                            value="<?php echo $editProduct ? htmlspecialchars($editProduct['icon']) : 'assets/images/services/ico-default-p.png'; ?>"
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <a href="../../../<?php echo $editProduct ? htmlspecialchars($editProduct['icon']) : 'assets/images/services/ico-default-p.png'; ?>" 
                           target="_blank" 
                           class="px-4 py-2 bg-gray-100 text-gray-600 border-t border-r border-b border-gray-300 hover:bg-gray-200 transition-colors">
                            <i class='bx bx-show'></i>
                        </a>
                    </div>
                    <div class="mt-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Upload New Icon</label>
                        <div class="flex items-center">
                            <input type="file" id="product_icon_upload" name="product_icon_upload" class="hidden"
                                accept="image/*" onchange="updateFileName(this)">
                            <label for="product_icon_upload" 
                                class="cursor-pointer px-4 py-2 bg-gray-200 text-gray-700 rounded-l-md hover:bg-gray-300 transition-colors">
                                Choose file
                            </label>
                            <span id="product_icon_upload_name" 
                                class="px-4 py-2 bg-gray-100 text-gray-600 border border-gray-300 rounded-r-md flex-1">
                                No file chosen
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Recommended size: 50x50 pixels</p>
                    </div>
                </div>
                
                <div class="flex items-center">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="product_is_active" class="h-4 w-4 text-blue-600 rounded"
                               <?php echo (!$editProduct || $editProduct['is_active']) ? 'checked' : ''; ?>>
                        <span class="ml-2 text-sm text-gray-700">Active</span>
                    </label>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <?php if ($editProduct): ?>
                        <a href="?tab=products" class="px-4 py-2 bg-gray-200 text-gray-700 font-medium rounded-md hover:bg-gray-300 transition-colors">
                            Cancel
                        </a>
                    <?php endif; ?>
                    
                    <button type="submit" name="<?php echo $editProduct ? 'update_product' : 'add_product'; ?>" 
                            class="px-6 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition-colors">
                        <i class='bx bx-<?php echo $editProduct ? 'save' : 'plus'; ?> mr-1'></i>
                        <?php echo $editProduct ? 'Update Product' : 'Add Product'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Preview -->
    <div class="border rounded-lg overflow-hidden">
        <div class="bg-gray-50 p-4 border-b">
            <h3 class="font-medium text-gray-900">Products Section Preview</h3>
            <p class="text-sm text-gray-500 mt-1">A simplified preview of how the products section will appear</p>
        </div>
        
        <div class="p-4">
            <div class="bg-gray-100 p-6 rounded-lg">
                <div class="text-center mb-6">
                    <span class="text-blue-600 text-sm font-medium"><?php echo htmlspecialchars($productsHeader['subtitle']); ?></span>
                    <h2 class="text-xl font-bold text-gray-800 mt-1"><?php echo htmlspecialchars($productsHeader['title']); ?></h2>
                </div>
                
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <?php 
                    // Get active products for preview
                    $activeProducts = array_filter($products, function($product) {
                        return $product['is_active'] == 1;
                    });
                    
                    // Sort by position
                    usort($activeProducts, function($a, $b) {
                        return $a['position'] - $b['position'];
                    });
                    
                    // Show only first 6 products in preview
                    $previewProducts = array_slice($activeProducts, 0, 6);
                    
                    if (empty($previewProducts)):
                    ?>
                        <div class="col-span-full text-center py-6 text-gray-500">
                            <p>No active products to display. Add products above to see them in the preview.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($previewProducts as $product): ?>
                            <div class="bg-white p-4 rounded-lg shadow-sm flex flex-col items-center">
                                <img src="../../../<?php echo htmlspecialchars($product['icon']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>" class="h-12 w-12 object-contain mb-2">
                                <h4 class="text-sm font-semibold text-gray-800 text-center"><?php echo htmlspecialchars($product['title']); ?></h4>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-2">This is a simplified preview. The actual appearance may vary.</p>
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