-- Team members table
USE akademi_merdeka;

-- Create team members table
CREATE TABLE IF NOT EXISTS team_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    position VARCHAR(100) NOT NULL,
    image_path VARCHAR(255),
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert initial team members
INSERT INTO team_members (name, position, image_path, display_order, is_active) VALUES
('Moch. Guntur', 'Chief Executive Officer', 'assets/images/team/pp-1.png', 1, 1),
('Azharani Aliyyatunnisa', 'Chief Technical Officer', 'assets/images/team/pp-2.png', 2, 1),
('Nur Indah Septia, N', 'Chief Operation Officer', 'assets/images/team/pp-3.png', 3, 1),
('Siti Musyarrofah', 'Chief Marketing Officer', 'assets/images/team/pp-4.png', 4, 1),
('Lusi Umayah', 'Chief Financial Officer', 'assets/images/team/pp-5.png', 5, 1),
('Ika Aprillia Putri', 'Chief Support Officer', 'assets/images/team/pp-6.png', 6, 1);

-- Team page settings
CREATE TABLE IF NOT EXISTS team_page_settings (
    id INT PRIMARY KEY DEFAULT 1,
    title VARCHAR(255) NOT NULL DEFAULT 'Tim Kami',
    subtitle VARCHAR(255) DEFAULT 'Tim',
    description TEXT,
    inner_title VARCHAR(255) DEFAULT 'Tim',
    breadcrumb_parent VARCHAR(255) DEFAULT 'Tentang',
    breadcrumb_parent_link VARCHAR(255) DEFAULT '/',
    breadcrumb_current VARCHAR(255) DEFAULT 'Tim',
    banner_image VARCHAR(255) DEFAULT 'assets/images/shape/inner-shape.png',
    seo_title VARCHAR(255) DEFAULT 'Tim | Akademi Merdeka',
    seo_description TEXT,
    seo_keywords TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default settings
INSERT INTO team_page_settings (
    title, subtitle, inner_title, 
    breadcrumb_parent, breadcrumb_parent_link, breadcrumb_current, 
    banner_image, seo_title
) VALUES (
    'Tim Kami', 'Tim', 'Tim', 
    'Tentang', '/', 'Tim', 
    'assets/images/shape/inner-shape.png', 'Tim | Akademi Merdeka'
);