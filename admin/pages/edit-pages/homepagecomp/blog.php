<?php
// Blog Section Component
// File path: admin/pages/edit-pages/homepagecomp/blog.php

// Get blog section ID
$blogSectionId = 0;
try {
    $stmt = $conn->prepare("SELECT id FROM homepage_sections WHERE section_key = 'blog'");
    $stmt->execute();
    $blogSectionId = $stmt->fetchColumn();
} catch(PDOException $e) {
    echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error fetching blog section: " . $e->getMessage() . "</div>";
}

// Handle section header form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_blog_header'])) {
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
            $stmt->bindParam(':section_id', $blogSectionId);
            $stmt->execute();
            
            // Update subtitle
            $stmt = $conn->prepare("UPDATE homepage_content 
                                  SET content_value = :value 
                                  WHERE section_id = :section_id AND content_key = 'subtitle'");
            $stmt->bindParam(':value', $subtitle);
            $stmt->bindParam(':section_id', $blogSectionId);
            $stmt->execute();
        }
        
        $conn->commit();
        
        echo "<div class='bg-green-100 text-green-700 p-4 rounded-lg mb-4'>Blog section header updated successfully!</div>";
    } catch(PDOException $e) {
        $conn->rollBack();
        echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error updating blog section header: " . $e->getMessage() . "</div>";
    }
}

// Handle adding new blog post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_blog_post'])) {
    try {
        // Get form data
        $title = trim($_POST['blog_title']);
        $excerpt = trim($_POST['blog_excerpt']);
        $link = trim($_POST['blog_link']);
        $author = trim($_POST['blog_author']);
        $category = trim($_POST['blog_category']);
        $date = $_POST['blog_date'];
        $position = isset($_POST['blog_position']) ? (int)$_POST['blog_position'] : 0;
        $isActive = isset($_POST['blog_is_active']) ? 1 : 0;
        
        // Handle image upload if provided
        $imagePath = trim($_POST['blog_image']);
        
        if (!empty($_FILES['blog_image_upload']['name'])) {
            $uploadDir = '../../../assets/images/blog/';
            $fileName = basename($_FILES['blog_image_upload']['name']);
            $uploadFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['blog_image_upload']['tmp_name'], $uploadFile)) {
                $imagePath = 'assets/images/blog/' . $fileName;
            }
        }
        
        // Insert new blog post
        $stmt = $conn->prepare("INSERT INTO featured_blog_posts (title, image, date, author, category, excerpt, link, position, is_active) 
                              VALUES (:title, :image, :date, :author, :category, :excerpt, :link, :position, :is_active)");
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':image', $imagePath);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':author', $author);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':excerpt', $excerpt);
        $stmt->bindParam(':link', $link);
        $stmt->bindParam(':position', $position);
        $stmt->bindParam(':is_active', $isActive);
        $stmt->execute();
        
        echo "<div class='bg-green-100 text-green-700 p-4 rounded-lg mb-4'>New blog post added successfully!</div>";
    } catch(PDOException $e) {
        echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error adding new blog post: " . $e->getMessage() . "</div>";
    }
}

// Handle updating blog post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_blog_post'])) {
    try {
        $blogId = (int)$_POST['blog_id'];
        $title = trim($_POST['blog_title']);
        $excerpt = trim($_POST['blog_excerpt']);
        $link = trim($_POST['blog_link']);
        $author = trim($_POST['blog_author']);
        $category = trim($_POST['blog_category']);
        $date = $_POST['blog_date'];
        $position = isset($_POST['blog_position']) ? (int)$_POST['blog_position'] : 0;
        $isActive = isset($_POST['blog_is_active']) ? 1 : 0;
        
        // Handle image upload if provided
        $imagePath = trim($_POST['blog_image']);
        
        if (!empty($_FILES['blog_image_upload']['name'])) {
            $uploadDir = '../../../assets/images/blog/';
            $fileName = basename($_FILES['blog_image_upload']['name']);
            $uploadFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['blog_image_upload']['tmp_name'], $uploadFile)) {
                $imagePath = 'assets/images/blog/' . $fileName;
            }
        }
        
        // Update blog post
        $stmt = $conn->prepare("UPDATE featured_blog_posts 
                              SET title = :title, image = :image, date = :date, 
                                  author = :author, category = :category, excerpt = :excerpt, 
                                  link = :link, position = :position, is_active = :is_active 
                              WHERE id = :id");
        $stmt->bindParam(':id', $blogId);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':image', $imagePath);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':author', $author);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':excerpt', $excerpt);
        $stmt->bindParam(':link', $link);
        $stmt->bindParam(':position', $position);
        $stmt->bindParam(':is_active', $isActive);
        $stmt->execute();
        
        echo "<div class='bg-green-100 text-green-700 p-4 rounded-lg mb-4'>Blog post updated successfully!</div>";
    } catch(PDOException $e) {
        echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error updating blog post: " . $e->getMessage() . "</div>";
    }
}

// Handle deleting blog post
if (isset($_GET['delete_blog_post']) && !empty($_GET['delete_blog_post'])) {
    try {
        $blogId = (int)$_GET['delete_blog_post'];
        
        $stmt = $conn->prepare("DELETE FROM featured_blog_posts WHERE id = :id");
        $stmt->bindParam(':id', $blogId);
        $stmt->execute();
        
        echo "<div class='bg-green-100 text-green-700 p-4 rounded-lg mb-4'>Blog post deleted successfully!</div>";
    } catch(PDOException $e) {
        echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error deleting blog post: " . $e->getMessage() . "</div>";
    }
}

// Get blog section header
$blogHeader = [
    'title' => 'Artikel Kami',
    'subtitle' => 'Blog'
];

try {
    $stmt = $conn->prepare("SELECT content_key, content_value 
                          FROM homepage_content 
                          WHERE section_id = :section_id AND content_key IN ('title', 'subtitle')");
    $stmt->bindParam(':section_id', $blogSectionId);
    $stmt->execute();
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($results as $row) {
        $blogHeader[$row['content_key']] = $row['content_value'];
    }
} catch(PDOException $e) {
    echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error fetching blog header: " . $e->getMessage() . "</div>";
}

// Get all blog posts
$blogPosts = [];
try {
    $stmt = $conn->query("SELECT * FROM featured_blog_posts ORDER BY position, id");
    $blogPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error fetching blog posts: " . $e->getMessage() . "</div>";
}

// Get specific blog post for editing
$editBlogPost = null;
if (isset($_GET['edit_blog_post']) && !empty($_GET['edit_blog_post'])) {
    try {
        $blogId = (int)$_GET['edit_blog_post'];
        
        $stmt = $conn->prepare("SELECT * FROM featured_blog_posts WHERE id = :id");
        $stmt->bindParam(':id', $blogId);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $editBlogPost = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch(PDOException $e) {
        echo "<div class='bg-red-100 text-red-700 p-4 rounded-lg mb-4'>Error fetching blog post: " . $e->getMessage() . "</div>";
    }
}
?>

<div class="space-y-8">
    <!-- Blog Section Header -->
    <div class="border rounded-lg overflow-hidden">
        <div class="bg-gray-50 p-4 border-b">
            <h3 class="font-medium text-gray-900">Blog Section Header</h3>
            <p class="text-sm text-gray-500 mt-1">Edit the blog section title and subtitle</p>
        </div>
        
        <div class="p-4">
            <form method="POST" action="" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Section Title</label>
                        <input type="text" id="title" name="title" 
                               value="<?php echo htmlspecialchars($blogHeader['title']); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="subtitle" class="block text-sm font-medium text-gray-700 mb-1">Section Subtitle</label>
                        <input type="text" id="subtitle" name="subtitle" 
                               value="<?php echo htmlspecialchars($blogHeader['subtitle']); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" name="update_blog_header" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition-colors">
                        <i class='bx bx-save mr-1'></i> Save Header
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Blog Posts List -->
    <div class="border rounded-lg overflow-hidden">
        <div class="bg-gray-50 p-4 border-b flex justify-between items-center">
            <div>
                <h3 class="font-medium text-gray-900">Featured Blog Posts</h3>
                <p class="text-sm text-gray-500 mt-1">Manage the featured blog posts displayed on your homepage</p>
            </div>
            
            <a href="?tab=blog" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition-colors">
                <i class='bx bx-plus mr-1'></i> Add New Post
            </a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($blogPosts)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                No blog posts found. Add your first post using the form below.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($blogPosts as $post): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex-shrink-0 h-14 w-20">
                                        <img class="h-14 w-20 object-cover rounded" src="../../../<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900 line-clamp-2"><?php echo htmlspecialchars($post['title']); ?></div>
                                    <div class="text-xs text-gray-500">by <?php echo htmlspecialchars($post['author']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('M d, Y', strtotime($post['date'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        <?php echo htmlspecialchars($post['category']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $post['position']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($post['is_active']): ?>
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
                                    <a href="?tab=blog&edit_blog_post=<?php echo $post['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class='bx bx-edit'></i> Edit
                                    </a>
                                    <a href="?tab=blog&delete_blog_post=<?php echo $post['id']; ?>" 
                                       onclick="return confirm('Are you sure you want to delete this blog post?')"
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
    
    <!-- Add/Edit Blog Post Form -->
    <div class="border rounded-lg overflow-hidden">
        <div class="bg-gray-50 p-4 border-b">
            <h3 class="font-medium text-gray-900">
                <?php echo $editBlogPost ? 'Edit Blog Post' : 'Add New Blog Post'; ?>
            </h3>
            <p class="text-sm text-gray-500 mt-1">
                <?php echo $editBlogPost ? 'Update the selected blog post' : 'Add a new blog post to your homepage'; ?>
            </p>
        </div>
        
        <div class="p-4">
            <form method="POST" action="" enctype="multipart/form-data" class="space-y-4">
                <?php if ($editBlogPost): ?>
                    <input type="hidden" name="blog_id" value="<?php echo $editBlogPost['id']; ?>">
                <?php endif; ?>
                
                <div>
                    <label for="blog_title" class="block text-sm font-medium text-gray-700 mb-1">Post Title</label>
                    <input type="text" id="blog_title" name="blog_title" required
                           value="<?php echo $editBlogPost ? htmlspecialchars($editBlogPost['title']) : ''; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="blog_author" class="block text-sm font-medium text-gray-700 mb-1">Author</label>
                        <input type="text" id="blog_author" name="blog_author"
                               value="<?php echo $editBlogPost ? htmlspecialchars($editBlogPost['author']) : 'Admin'; ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="blog_category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <input type="text" id="blog_category" name="blog_category" required
                               value="<?php echo $editBlogPost ? htmlspecialchars($editBlogPost['category']) : ''; ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Example: Jurnal, HKI, KTI, etc.</p>
                    </div>
                    
                    <div>
                        <label for="blog_date" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                        <input type="date" id="blog_date" name="blog_date" required
                               value="<?php echo $editBlogPost ? date('Y-m-d', strtotime($editBlogPost['date'])) : date('Y-m-d'); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <div>
                    <label for="blog_excerpt" class="block text-sm font-medium text-gray-700 mb-1">Excerpt</label>
                    <textarea id="blog_excerpt" name="blog_excerpt" required rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo $editBlogPost ? htmlspecialchars($editBlogPost['excerpt']) : ''; ?></textarea>
                    <p class="mt-1 text-xs text-gray-500">Short description of the blog post (recommended: 150-200 characters)</p>
                </div>
                
                <div>
                    <label for="blog_link" class="block text-sm font-medium text-gray-700 mb-1">Post Link</label>
                    <input type="text" id="blog_link" name="blog_link" required
                           value="<?php echo $editBlogPost ? htmlspecialchars($editBlogPost['link']) : 'blog/'; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-gray-500">Example: blog/post-slug or full URL</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="blog_position" class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                        <input type="number" id="blog_position" name="blog_position" min="0"
                               value="<?php echo $editBlogPost ? $editBlogPost['position'] : count($blogPosts) + 1; ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Lower numbers appear first</p>
                    </div>
                    
                    <div class="flex items-center h-full pt-6">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="blog_is_active" class="h-4 w-4 text-blue-600 rounded"
                                   <?php echo (!$editBlogPost || $editBlogPost['is_active']) ? 'checked' : ''; ?>>
                            <span class="ml-2 text-sm text-gray-700">Active</span>
                        </label>
                    </div>
                </div>
                
                <div>
                    <label for="blog_image" class="block text-sm font-medium text-gray-700 mb-1">Featured Image Path</label>
                    <div class="flex">
                        <input type="text" id="blog_image" name="blog_image" 
                            value="<?php echo $editBlogPost ? htmlspecialchars($editBlogPost['image']) : 'assets/images/blog/default-blog.jpg'; ?>"
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <a href="../../../<?php echo $editBlogPost ? htmlspecialchars($editBlogPost['image']) : 'assets/images/blog/default-blog.jpg'; ?>" 
                           target="_blank" 
                           class="px-4 py-2 bg-gray-100 text-gray-600 border-t border-r border-b border-gray-300 hover:bg-gray-200 transition-colors">
                            <i class='bx bx-show'></i>
                        </a>
                    </div>
                    <div class="mt-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Upload New Image</label>
                        <div class="flex items-center">
                            <input type="file" id="blog_image_upload" name="blog_image_upload" class="hidden"
                                accept="image/*" onchange="updateFileName(this)">
                            <label for="blog_image_upload" 
                                class="cursor-pointer px-4 py-2 bg-gray-200 text-gray-700 rounded-l-md hover:bg-gray-300 transition-colors">
                                Choose file
                            </label>
                            <span id="blog_image_upload_name" 
                                class="px-4 py-2 bg-gray-100 text-gray-600 border border-gray-300 rounded-r-md flex-1">
                                No file chosen
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Recommended size: 800x500 pixels</p>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <?php if ($editBlogPost): ?>
                        <a href="?tab=blog" class="px-4 py-2 bg-gray-200 text-gray-700 font-medium rounded-md hover:bg-gray-300 transition-colors">
                            Cancel
                        </a>
                    <?php endif; ?>
                    
                    <button type="submit" name="<?php echo $editBlogPost ? 'update_blog_post' : 'add_blog_post'; ?>" 
                            class="px-6 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition-colors">
                        <i class='bx bx-<?php echo $editBlogPost ? 'save' : 'plus'; ?> mr-1'></i>
                        <?php echo $editBlogPost ? 'Update Blog Post' : 'Add Blog Post'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Preview -->
    <div class="border rounded-lg overflow-hidden">
        <div class="bg-gray-50 p-4 border-b">
            <h3 class="font-medium text-gray-900">Blog Section Preview</h3>
            <p class="text-sm text-gray-500 mt-1">A simplified preview of how the blog section will appear</p>
        </div>
        
        <div class="p-4">
            <div class="bg-gray-100 p-6 rounded-lg">
                <div class="text-center mb-6">
                    <span class="text-blue-600 text-sm font-medium"><?php echo htmlspecialchars($blogHeader['subtitle']); ?></span>
                    <h2 class="text-xl font-bold text-gray-800 mt-1"><?php echo htmlspecialchars($blogHeader['title']); ?></h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php 
                    // Get active blog posts for preview
                    $activePosts = array_filter($blogPosts, function($post) {
                        return $post['is_active'] == 1;
                    });
                    
                    // Sort by position
                    usort($activePosts, function($a, $b) {
                        return $a['position'] - $b['position'];
                    });
                    
                    // Show only first 3 posts in preview
                    $previewPosts = array_slice($activePosts, 0, 3);
                    
                    if (empty($previewPosts)):
                    ?>
                        <div class="col-span-full text-center py-6 text-gray-500">
                            <p>No active blog posts to display. Add posts above to see them in the preview.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($previewPosts as $post): ?>
                            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                                <div class="h-48 w-full overflow-hidden">
                                    <img src="../../../<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="w-full h-full object-cover">
                                </div>
                                <div class="p-4">
                                    <div class="flex justify-between items-center mb-2 text-sm text-gray-500">
                                        <div><i class='bx bx-calendar mr-1'></i> <?php echo date('M d, Y', strtotime($post['date'])); ?></div>
                                        <div><i class='bx bx-purchase-tag-alt mr-1'></i> <?php echo htmlspecialchars($post['category']); ?></div>
                                    </div>
                                    <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2"><?php echo htmlspecialchars($post['title']); ?></h3>
                                    <p class="text-sm text-gray-600 mb-3 line-clamp-3"><?php echo htmlspecialchars($post['excerpt']); ?></p>
                                    <a href="#" class="text-sm text-blue-600 font-medium">Read More <i class='bx bx-chevron-right inline-block align-middle'></i></a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-2">This is a simplified preview. The actual appearance may vary. The section will display up to 3 active blog posts in the order specified by their position.</p>
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