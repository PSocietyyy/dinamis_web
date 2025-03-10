-- Create the database
CREATE DATABASE IF NOT EXISTS akademi_merdeka;
USE akademi_merdeka;

-- ===============================
-- User Management Tables
-- ===============================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'editor') NOT NULL DEFAULT 'editor',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default admin user
INSERT INTO users (username, password, role) 
VALUES ('admin', 'admin123', 'admin');

-- ===============================
-- Navigation Management Tables
-- ===============================
CREATE TABLE IF NOT EXISTS navbar_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT NULL,
    title VARCHAR(50) NOT NULL,
    url VARCHAR(255) NOT NULL,
    position INT NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES navbar_items(id) ON DELETE SET NULL
);

-- ===============================
-- Content Management Tables
-- ===============================
CREATE TABLE IF NOT EXISTS page_meta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_id INT NOT NULL,
    meta_title VARCHAR(255),
    meta_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (page_id) REFERENCES navbar_items(id) ON DELETE CASCADE
);

-- ===============================
-- Footer Management Tables
-- ===============================
CREATE TABLE IF NOT EXISTS footer_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section VARCHAR(50) NOT NULL,
    title VARCHAR(100) NOT NULL,
    url VARCHAR(255) NOT NULL,
    position INT NOT NULL DEFAULT 0,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ===============================
-- Site Configuration Tables
-- ===============================
CREATE TABLE IF NOT EXISTS site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_group VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ===============================
-- Newsletter Management Tables
-- ===============================
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(100),
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    ip_address VARCHAR(45),
    user_agent TEXT
);

CREATE TABLE IF NOT EXISTS bulletin_fields (
    id INT AUTO_INCREMENT PRIMARY KEY,
    field_name VARCHAR(50) NOT NULL,
    field_label VARCHAR(100) NOT NULL,
    field_type ENUM('text', 'email', 'textarea', 'select', 'checkbox') NOT NULL,
    is_required BOOLEAN DEFAULT FALSE,
    placeholder VARCHAR(255),
    position INT NOT NULL DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ===============================
-- Default Data - Navbar Items
-- ===============================
INSERT INTO navbar_items (parent_id, title, url, position, is_active) VALUES
(NULL, 'Home', './index.php', 1, 1),
(NULL, 'Tentang', '#', 2, 1),
(NULL, 'Produk', '#', 3, 1),
(NULL, 'Layanan', './service.php', 4, 1),
(NULL, 'Blog', './blogs.php', 5, 1),
(NULL, 'Kontak', './contact.php', 6, 1);

-- Insert dropdown menu items for 'Tentang'
SET @tentang_id = (SELECT id FROM navbar_items WHERE title = 'Tentang' AND parent_id IS NULL);
INSERT INTO navbar_items (parent_id, title, url, position, is_active) VALUES
(@tentang_id, 'Tim', './team.php', 1, 1),
(@tentang_id, 'Testimoni', './testimonial.php', 2, 1),
(@tentang_id, 'FAQ', './faq.php', 3, 1),
(@tentang_id, 'Syarat & Ketentuan', './terms-condition.php', 4, 1),
(@tentang_id, 'Kebijakan Privasi', './privacy-policy.php', 5, 1);

-- Insert dropdown menu items for 'Produk'
SET @produk_id = (SELECT id FROM navbar_items WHERE title = 'Produk' AND parent_id IS NULL);
INSERT INTO navbar_items (parent_id, title, url, position, is_active) VALUES
(@produk_id, 'E-Learning', 'https://vp-dls.akademimerdeka.com/', 1, 1),
(@produk_id, 'E-Journal', 'https://journal.akademimerdeka.com/ojs/index.php/index/', 2, 1),
(@produk_id, 'E-Perpus', './eperpus.php', 3, 1),
(@produk_id, 'E-Catalogue', './ecatalogue.php', 4, 1);

-- ===============================
-- Default Data - Footer Links
-- ===============================
INSERT INTO footer_links (section, title, url, position, is_active) VALUES
-- Layanan Kami
('layanan', 'Karya Ilmiah', 'services/karya-ilmiah', 1, 1),
('layanan', 'Penerbitan Jurnal', 'services/penerbitan-jurnal', 2, 1),
('layanan', 'Penerbitan Buku', 'services/penerbitan-buku', 3, 1),
('layanan', 'Penerbitan HKI', 'services/penerbitan-hki', 4, 1),
('layanan', 'Pengolahan Statistik', 'services/pengolahan-statistik', 5, 1),
('layanan', 'Pendampingan OJS', 'services/pendampingan-ojs', 6, 1),
('layanan', 'Pendampingan TKDA/TKBI', 'services/pendampingan-tkda', 7, 1),
('layanan', 'Konversi KTI', 'services/konversi-kti', 8, 1),
('layanan', 'Pembuatan Media Ajar', 'services/media-ajar', 9, 1);

-- ===============================
-- Default Data - Site Settings
-- ===============================
INSERT INTO site_settings (setting_key, setting_value, setting_group) VALUES
-- Navbar settings
('navbar_logo', 'assets/images/logos/logo-akademi-merdeka.png', 'navbar'),
('navbar_logo_alt', 'assets/images/logos/logo-2.png', 'navbar'),
('navbar_button_text', 'Konsultasi Sekarang', 'navbar'),
('navbar_button_url', 'https://wa.me/6287735426107', 'navbar'),
('navbar_bg_color', '#ffffff', 'navbar'),
('navbar_text_color', '#5a5c69', 'navbar'),

-- Footer settings
('footer_logo', 'assets/images/logos/logo-footer.png', 'footer'),
('footer_company_name', 'Akademi Merdeka', 'footer'),
('footer_company_address', 'Perumahan Kheandra Kalijaga<br>Harjamukti, Cirebon, Jawa Barat', 'footer'),
('footer_company_phone', '+62 877-3542-6107', 'footer'),
('footer_company_email', 'info@akademimerdeka.com', 'footer'),
('footer_copyright_text', 'Copyright © 2023 <a href="https://akademimerdeka.com/">Akademi Merdeka</a> as establisment date 2022', 'footer'),
('footer_text_color', '#ffffff', 'footer'),
('footer_whatsapp_link', 'https://wa.me/6287735426107', 'footer'),
('footer_bulletin_title', 'Bulletin', 'footer'),
('footer_bulletin_description', 'Informasi lain dapat diajukan kepada tim kami untuk ditindaklanjuti.', 'footer'),
('footer_newsletter_action', '', 'footer'),
('footer_gradient_direction', 'to bottom', 'footer'),
('footer_gradient_start_color', '#343a40', 'footer'),
('footer_gradient_end_color', '#1a1e21', 'footer');

-- ===============================
-- Default Data - Newsletter Fields
-- ===============================
INSERT INTO bulletin_fields (field_name, field_label, field_type, is_required, placeholder, position, is_active)
VALUES ('email', 'Email', 'email', TRUE, 'Enter Your Email', 1, TRUE);