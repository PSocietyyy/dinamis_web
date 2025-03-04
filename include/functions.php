<?php
/**
 * Functions for retrieving and managing dynamic page content
 */

/**
 * Get all content for a specific section
 *
 * @param string $section_name The name of the section
 * @param bool $active_only Whether to return only active content
 * @return array Array of content items
 */
function getPageSectionContent($conn, $section_name, $active_only = true) {
    $active_condition = $active_only ? " AND is_active = 1" : "";
    
    $sql = "SELECT * FROM page_content 
            WHERE section_name = ?
            $active_condition
            ORDER BY sort_order ASC";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $section_name);
    mysqli_stmt_execute($stmt);
    
    $result = mysqli_stmt_get_result($stmt);
    $items = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }
    
    return $items;
}

/**
 * Get a specific content item by section and key
 *
 * @param string $section_name The name of the section
 * @param string $section_key The specific key in the section
 * @return string|null The content value or null if not found
 */
function getContentByKey($conn, $section_name, $section_key) {
    $sql = "SELECT content_value FROM page_content 
            WHERE section_name = ? AND section_key = ? AND is_active = 1";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $section_name, $section_key);
    mysqli_stmt_execute($stmt);
    
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['content_value'];
    }
    
    return null;
}

/**
 * Get all service items for display
 *
 * @return array Array of service items
 */
function getServices($conn) {
    return getPageSectionContent($conn, 'service');
}

/**
 * Get all testimonial items for display
 *
 * @return array Array of testimonial items
 */
function getTestimonials($conn) {
    return getPageSectionContent($conn, 'testimonial');
}

/**
 * Get all blog posts for the homepage display
 *
 * @param int $limit Number of posts to retrieve
 * @return array Array of blog posts
 */
function getHomepagePosts($conn, $limit = 3) {
    $sql = "SELECT * FROM page_content 
            WHERE section_name = 'blog' AND is_active = 1
            ORDER BY sort_order, last_updated DESC
            LIMIT ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $limit);
    mysqli_stmt_execute($stmt);
    
    $result = mysqli_stmt_get_result($stmt);
    $posts = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
    }
    
    return $posts;
}

/**
 * Update a content item
 *
 * @param int $id The content ID
 * @param string $content_value The new content value
 * @return bool True if successful, false otherwise
 */
function updateContent($conn, $id, $content_value) {
    $sql = "UPDATE page_content SET content_value = ? WHERE id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $content_value, $id);
    
    return mysqli_stmt_execute($stmt);
}

/**
 * Update image URL for a content item
 *
 * @param int $id The content ID
 * @param string $image_url The new image URL
 * @return bool True if successful, false otherwise
 */
function updateImageUrl($conn, $id, $image_url) {
    $sql = "UPDATE page_content SET image_url = ? WHERE id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $image_url, $id);
    
    return mysqli_stmt_execute($stmt);
}

/**
 * Toggle active status for a content item
 *
 * @param int $id The content ID
 * @param bool $status The new active status
 * @return bool True if successful, false otherwise
 */
function toggleContentStatus($conn, $id, $status) {
    $sql = "UPDATE page_content SET is_active = ? WHERE id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    $status_int = $status ? 1 : 0;
    mysqli_stmt_bind_param($stmt, "ii", $status_int, $id);
    
    return mysqli_stmt_execute($stmt);
}

/**
 * Create a new content item
 *
 * @param array $data The content data
 * @return int|false The new content ID or false on failure
 */
function createContent($conn, $data) {
    $sql = "INSERT INTO page_content (
                section_name, section_key, content_type, content_value, 
                image_url, link_url, sort_order, is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $sql);
    
    $section_name = $data['section_name'];
    $section_key = $data['section_key'] ?? $section_name . '_' . time();
    $content_type = $data['content_type'] ?? 'text';
    $content_value = $data['content_value'] ?? '';
    $image_url = $data['image_url'] ?? '';
    $link_url = $data['link_url'] ?? '';
    $sort_order = $data['sort_order'] ?? 0;
    $is_active = isset($data['is_active']) ? ($data['is_active'] ? 1 : 0) : 1;
    
    mysqli_stmt_bind_param(
        $stmt, 
        "ssssssii", 
        $section_name, $section_key, $content_type, $content_value,
        $image_url, $link_url, $sort_order, $is_active
    );
    
    if (mysqli_stmt_execute($stmt)) {
        return mysqli_insert_id($conn);
    }
    
    return false;
}

/**
 * Delete a content item
 *
 * @param int $id The content ID
 * @return bool True if successful, false otherwise
 */
function deleteContent($conn, $id) {
    $sql = "DELETE FROM page_content WHERE id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    return mysqli_stmt_execute($stmt);
}

/**
 * Get navbar logo and action button
 * 
 * @return array Navbar branding and action button
 */
function getNavbarBranding($conn) {
    return getPageSectionContent($conn, 'navbar');
}

/**
 * Get navbar menu items
 * 
 * @return array Menu items for main navigation
 */
function getNavbarMenu($conn) {
    return getPageSectionContent($conn, 'navbar_menu');
}

/**
 * Get dropdown items for a specific menu item
 * 
 * @param string $parent_key The parent menu key
 * @return array Dropdown menu items
 */
function getNavbarDropdown($conn, $parent_key) {
    return getPageSectionContent($conn, 'navbar_dropdown_' . $parent_key);
}

/**
 * Get footer information
 * 
 * @return array Footer content items
 */
function getFooterInfo($conn) {
    return getPageSectionContent($conn, 'footer');
}

/**
 * Get footer services links
 * 
 * @return array Footer service links
 */
function getFooterServices($conn) {
    return getPageSectionContent($conn, 'footer_services');
}

/**
 * Get footer blog items
 * 
 * @return array Footer blog items
 */
function getFooterBlog($conn) {
    return getPageSectionContent($conn, 'footer_blog');
}

/**
 * Get content section for editing in admin panel
 *
 * @param string $section The section to get
 * @return array Section content items
 */
function getEditableSectionContent($conn, $section) {
    return getPageSectionContent($conn, $section, false);
}

/**
 * Update sort order for a content item
 *
 * @param int $id The content ID
 * @param int $sort_order The new sort order
 * @return bool True if successful, false otherwise
 */
function updateSortOrder($conn, $id, $sort_order) {
    $sql = "UPDATE page_content SET sort_order = ? WHERE id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $sort_order, $id);
    
    return mysqli_stmt_execute($stmt);
}

/**
 * Update link URL for a content item
 *
 * @param int $id The content ID
 * @param string $link_url The new link URL
 * @return bool True if successful, false otherwise
 */
function updateLinkUrl($conn, $id, $link_url) {
    $sql = "UPDATE page_content SET link_url = ? WHERE id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $link_url, $id);
    
    return mysqli_stmt_execute($stmt);
}

/**
 * Update multiple fields of a content item
 *
 * @param int $id The content ID
 * @param array $data The data to update
 * @return bool True if successful, false otherwise
 */
function updateContentItem($conn, $id, $data) {
    $updates = [];
    $params = [];
    $types = '';
    
    // Build the SET part of the query
    foreach ($data as $key => $value) {
        if (in_array($key, ['content_value', 'image_url', 'link_url', 'section_key', 'sort_order', 'is_active'])) {
            $updates[] = "$key = ?";
            $params[] = $value;
            
            if ($key == 'sort_order' || $key == 'is_active') {
                $types .= 'i'; // integer
            } else {
                $types .= 's'; // string
            }
        }
    }
    
    if (empty($updates)) {
        return false;
    }
    
    $sql = "UPDATE page_content SET " . implode(', ', $updates) . " WHERE id = ?";
    $types .= 'i'; // For the ID
    $params[] = $id;
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    
    return mysqli_stmt_execute($stmt);
}
?>