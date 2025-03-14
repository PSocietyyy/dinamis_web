-- Create the navbar tables for Akademi Merdeka
USE akademi_merdeka;

-- Main navbar items table
CREATE TABLE navbar_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    link VARCHAR(255) NOT NULL,
    parent_id INT DEFAULT NULL,
    has_dropdown TINYINT(1) DEFAULT 0,
    target VARCHAR(20) DEFAULT '_self', -- For opening in new tab (_blank) or same tab (_self)
    order_index INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES navbar_items(id) ON DELETE CASCADE
);

-- Navbar settings table for logos and action button
CREATE TABLE navbar_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default menu items
INSERT INTO navbar_items (title, link, parent_id, has_dropdown, order_index, is_active) VALUES
-- Main menu items
('Home', '/', NULL, 0, 1, 1),
('Tentang', '#', NULL, 1, 2, 1),
('Produk', '#', NULL, 1, 3, 1),
('Layanan', 'service', NULL, 0, 4, 1),
('Blog', 'blogs', NULL, 0, 5, 1),
('Kontak', 'contact', NULL, 0, 6, 1);

-- Dropdown items for "Tentang"
INSERT INTO navbar_items (title, link, parent_id, has_dropdown, order_index, is_active)
SELECT 'Tim', 'team', id, 0, 1, 1 FROM navbar_items WHERE title = 'Tentang';

INSERT INTO navbar_items (title, link, parent_id, has_dropdown, order_index, is_active)
SELECT 'Testimoni', 'testimonial', id, 0, 2, 1 FROM navbar_items WHERE title = 'Tentang';

INSERT INTO navbar_items (title, link, parent_id, has_dropdown, order_index, is_active)
SELECT 'FAQ', 'faq', id, 0, 3, 1 FROM navbar_items WHERE title = 'Tentang';

INSERT INTO navbar_items (title, link, parent_id, has_dropdown, order_index, is_active)
SELECT 'Syarat & Ketentuan', 'terms-condition', id, 0, 4, 1 FROM navbar_items WHERE title = 'Tentang';

INSERT INTO navbar_items (title, link, parent_id, has_dropdown, order_index, is_active)
SELECT 'Kebijakan Privasi', 'privacy-policy', id, 0, 5, 1 FROM navbar_items WHERE title = 'Tentang';

-- Dropdown items for "Produk"
INSERT INTO navbar_items (title, link, parent_id, has_dropdown, order_index, is_active, target)
SELECT 'E-Learning', 'https://vp-dls.akademimerdeka.com/', id, 0, 1, 1, '_blank' FROM navbar_items WHERE title = 'Produk';

INSERT INTO navbar_items (title, link, parent_id, has_dropdown, order_index, is_active, target)
SELECT 'E-Journal', 'https://journal.akademimerdeka.com/ojs/index.php/index/', id, 0, 2, 1, '_blank' FROM navbar_items WHERE title = 'Produk';

INSERT INTO navbar_items (title, link, parent_id, has_dropdown, order_index, is_active)
SELECT 'E-Perpus', 'eperpus', id, 0, 3, 1 FROM navbar_items WHERE title = 'Produk';

INSERT INTO navbar_items (title, link, parent_id, has_dropdown, order_index, is_active)
SELECT 'E-Catalogue', 'ecatalogue', id, 0, 4, 1 FROM navbar_items WHERE title = 'Produk';

-- Insert default settings for logos and action button
INSERT INTO navbar_settings (setting_key, setting_value) VALUES
('logo_path', 'assets/images/logos/logo-akademi-merdeka.png'),
('logo_height', '64'),
('logo_alt', 'Logo'),
('logo_two_path', 'assets/images/logos/logo-2.png'),
('logo_two_height', '64'),
('logo_two_alt', 'Logo'),
('mobile_logo_path', 'assets/images/logos/logo-akademi-merdeka.png'),
('mobile_logo_height', '64'),
('mobile_logo_alt', 'Logo'),
('mobile_logo_two_path', 'assets/images/logos/logo-2.png'),
('mobile_logo_two_height', '64'),
('mobile_logo_two_alt', 'Logo'),
('action_button_text', 'Konsultasi Sekarang'),
('action_button_link', 'https://wa.me/6287735426107'),
('action_button_target', '_blank'),
