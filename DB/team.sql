-- Team members and page banner database structure for Akademi Merdeka
USE akademi_merdeka;

-- Create team banners table
CREATE TABLE IF NOT EXISTS team_banners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_slug VARCHAR(100) NOT NULL UNIQUE,
    title VARCHAR(100) NOT NULL,
    breadcrumb_text VARCHAR(100) NOT NULL,
    banner_image VARCHAR(255) DEFAULT 'assets/images/shape/inner-shape.png',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default banner for team page
INSERT INTO team_banners (page_slug, title, breadcrumb_text, banner_image) VALUES
('team', 'Tim', 'Tim', 'assets/images/shape/inner-shape.png');

-- Add team_title to the team_banners table if it doesn't exist
ALTER TABLE team_banners ADD COLUMN team_title VARCHAR(100) DEFAULT 'Tim Kami' AFTER banner_image;

-- Update the existing record with the default team title if it exists
UPDATE team_banners SET team_title = 'Tim Kami' WHERE page_slug = 'team' AND team_title IS NULL;

-- Create team members table
CREATE TABLE IF NOT EXISTS team_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    position VARCHAR(100) NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default team members
INSERT INTO team_members (name, position, image_path, display_order, is_active) VALUES
('Moch. Guntur', 'Chief Executive Officer', 'assets/images/team/pp-1.png', 1, 1),
('Azharani Aliyyatunnisa', 'Chief Technical Officer', 'assets/images/team/pp-2.png', 2, 1),
('Nur Indah Septia, N', 'Chief Operation Officer', 'assets/images/team/pp-3.png', 3, 1),
('Siti Musyarrofah', 'Chief Marketing Officer', 'assets/images/team/pp-4.png', 4, 1),
('Lusi Umayah', 'Chief Financial Officer', 'assets/images/team/pp-5.png', 5, 1),
('Ika Aprillia Putri', 'Chief Support Officer', 'assets/images/team/pp-6.png', 6, 1);