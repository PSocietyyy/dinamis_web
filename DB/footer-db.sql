-- Footer Database Structure
USE akademi_merdeka;

-- Table for storing general footer settings
CREATE TABLE IF NOT EXISTS footer_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table for storing footer links/services
CREATE TABLE IF NOT EXISTS footer_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section VARCHAR(50) NOT NULL, -- 'services', 'social', etc.
    title VARCHAR(100) NOT NULL,
    url VARCHAR(255) NOT NULL,
    icon VARCHAR(50), -- for icon class like 'bx bx-chevron-right'
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default footer settings with consolidated address
INSERT INTO footer_settings (setting_key, setting_value) VALUES
('company_name', 'Akademi Merdeka'),
('company_address', 'Akademi Merdeka Office:
Perumahan Kheandra Kalijaga
Harjamukti, Cirebon, Jawa Barat'),
('company_phone', '+62 877-3542-6107'),
('footer_copyright', 'Copyright © 2023 <a href="https://akademimerdeka.com/">Akademi Merdeka</a> as establisment date 2022'),
('bulletin_title', 'Bulletin'),
('bulletin_text', 'Informasi lain dapat diajukan kepada tim kami untuk ditindaklanjuti.'),
('logo_footer', 'assets/images/logos/logo-footer.png');

-- Insert default services links
INSERT INTO footer_links (section, title, url, icon, display_order, is_active) VALUES
('services', 'Penerbitan Jurnal', 'services/penerbitan-jurnal', 'bx bx-chevron-right', 1, TRUE),
('services', 'Penerbitan HKI', 'services/penerbitan-hki', 'bx bx-chevron-right', 2, TRUE),
('services', 'Pengolahan Statistik', 'services/pengolahan-statistik', 'bx bx-chevron-right', 3, TRUE),
('services', 'Pendampingan OJS', 'services/pendampingan-ojs', 'bx bx-chevron-right', 4, TRUE),
('services', 'Pendampingan TKDA/TKBI', 'services/pendampingan-tkda', 'bx bx-chevron-right', 5, TRUE),
('services', 'Konversi KTI', 'services/konversi-kti', 'bx bx-chevron-right', 6, TRUE),
('services', 'Pembuatan Media Ajar', 'services/media-ajar', 'bx bx-chevron-right', 7, TRUE);