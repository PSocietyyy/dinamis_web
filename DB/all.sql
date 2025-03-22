-- Membuat database jika belum ada
CREATE DATABASE IF NOT EXISTS akademi_merdeka;
USE akademi_merdeka;

-- ===============================
-- Tabel Manajemen Pengguna
-- ===============================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'editor') NOT NULL DEFAULT 'editor',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Data default untuk user admin
INSERT INTO users (username, password, role) 
VALUES ('admin', 'admin123', 'admin');

-- ===============================
-- Tabel untuk konten homepage dinamis
-- ===============================

-- Tabel untuk banner utama
CREATE TABLE IF NOT EXISTS home_banner (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title TEXT NOT NULL,
    subtitle TEXT,
    button1_text VARCHAR(100),
    button1_url VARCHAR(255),
    button2_text VARCHAR(100),
    button2_url VARCHAR(255),
    banner_image VARCHAR(255),
    shape_image VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel untuk statistik counter
CREATE TABLE IF NOT EXISTS home_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image_path VARCHAR(255),
    count_number VARCHAR(20) NOT NULL,
    count_label VARCHAR(100) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel untuk bagian tentang kami
CREATE TABLE IF NOT EXISTS home_about (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    subtitle VARCHAR(255),
    content TEXT NOT NULL,
    image_path VARCHAR(255),
    card1_icon VARCHAR(100),
    card1_title VARCHAR(100),
    card1_text TEXT,
    card2_icon VARCHAR(100),
    card2_title VARCHAR(100),
    card2_text TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel untuk call to action
CREATE TABLE IF NOT EXISTS home_cta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title TEXT NOT NULL,
    subtitle TEXT,
    button_text VARCHAR(100),
    button_url VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel untuk judul bagian produk
CREATE TABLE IF NOT EXISTS home_products_section (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    subtitle TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel untuk item produk
CREATE TABLE IF NOT EXISTS home_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    image_path VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel untuk judul bagian testimonial
CREATE TABLE IF NOT EXISTS home_testimonials_section (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    subtitle VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel untuk item testimonial
CREATE TABLE IF NOT EXISTS home_testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(100) NOT NULL,
    client_position VARCHAR(100),
    testimonial_text TEXT NOT NULL,
    client_image VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ===============================
-- Data Default Homepage
-- ===============================

-- Data default untuk banner
INSERT INTO home_banner (id, title, subtitle, button1_text, button1_url, button2_text, button2_url, banner_image, shape_image) 
VALUES (1, 'Platform Academic Digital With Excellent Quality', 'Platform Akademi Merdeka membantu setiap insan akademisi dengan pelayanan yang eksklusif', 
'Learn More', '#', 'Whatsapp', 'https://wa.me/6287735426107', 'assets/images/home-three/home-main-pic.png', 'assets/images/home-three/home-three-shape.png')
ON DUPLICATE KEY UPDATE id = 1;

-- Data default untuk bagian tentang kami
INSERT INTO home_about (id, title, subtitle, content, image_path, card1_icon, card1_title, card1_text, card2_icon, card2_title, card2_text)
VALUES (1, 'Tentang Kita', 'About Us', 'Akademi Merdeka mempunyai ruang lingkup dalam bidang akademisi yang tujuannya ialah membantu setiap insan akademisi dengan berbagai problematika yang sedang dihadapi.', 
'assets/images/about/home-about.png', 'flaticon-practice', 'Experience', 'Berbagai macam persoalan sudah kami pecahkan dengan prosedur yang efektif.',
'flaticon-help', 'Quick Support', 'Dukungan setiap persoalan akan didampingi oleh satu supervisi yang expert.')
ON DUPLICATE KEY UPDATE id = 1;

-- Data default untuk call to action
INSERT INTO home_cta (id, title, subtitle, button_text, button_url)
VALUES (1, 'Kami melayani berbagai persoalan dengan solusi yang tepat', 'Hubungi Kami', 'Whatsapp', 'https://wa.me/6287735426107')
ON DUPLICATE KEY UPDATE id = 1;

-- Data default untuk judul bagian produk
INSERT INTO home_products_section (id, title, subtitle)
VALUES (1, 'Kami memberikan solusi terbaik dengan produk terpercaya dan berkualitas', 'Produk Kami')
ON DUPLICATE KEY UPDATE id = 1;

-- Data default untuk produk
INSERT INTO home_products (title, image_path, is_active, display_order) VALUES
('KTI', 'assets/images/services/ico-kti-p.png', 1, 1),
('Journal', 'assets/images/services/ico-jurnal-p.png', 1, 2),
('HKI', 'assets/images/services/ico-haki-p.png', 1, 3),
('OJS', 'assets/images/services/ico-ojs-p.png', 1, 4),
('Media', 'assets/images/services/ico-mediaajar-p.png', 1, 5);

-- Data default untuk judul bagian testimonial
INSERT INTO home_testimonials_section (id, title, subtitle)
VALUES (1, 'Apa Kata Mereka?', 'Testimoni')
ON DUPLICATE KEY UPDATE id = 1;

-- Data default untuk testimonial
INSERT INTO home_testimonials (client_name, client_position, testimonial_text, client_image, is_active, display_order) VALUES
('Bayu Saputra', 'Mahasiswa', '"Adanya tim Akademi Merdeka membantu saya dalam penerbitan jurnal dengan metode yang efektif, membuat saya cepat memahami."', 'assets/images/clients-img/testi-4.jpg', 1, 1),
('Aryo Supratman', 'Dosen', '"Akademi Merdeka tidak hanya sekedar membantu dalam kenaikan Jabatan Fungsional, namun sebagai penasehat dan pendengar yang baik. Tim sangat responsif dan tanggap jika ada persoalan."', 'assets/images/clients-img/testi-3.jpg', 1, 2),
('Syadid', 'Mahasiswa', '"Tim Akademi Merdeka membantu pembuatan media ajar mulai dari penyusunan indikator dan memberikan inovasi yang sangat baik."', 'assets/images/clients-img/testi-6.jpg', 1, 3),
('Alya Afifah', 'Mahasiswa', '"Desain yang diberikan oleh tim Akademi Merdeka sangat kekinian, sehingga buku yang diterbitkan semakin menarik perhatian pembaca."', 'assets/images/clients-img/testi-1.jpg', 1, 4),
('Arini Sulistiawati', 'Mahasiswa', '"Pelayanan Pembuatan HKI sangat cepat. Tim hanya memerlukan 20 menit saja untuk mengirimkan sertifikat HKI kepada saya."', 'assets/images/clients-img/testi-2.jpg', 1, 5);

-- Data default untuk statistik counter
INSERT INTO home_stats (image_path, count_number, count_label, is_active, display_order) VALUES
('assets/images/home-three/home-slider-karya-ilmiah.png', '500+', 'Karya Ilmiah', 1, 1),
('assets/images/home-three/home-slider-pendampingan-ojs.png', '10+', 'Pendampingan OJS', 1, 2),
('assets/images/home-three/home-slider-haki.png', '100+', 'HKI/Paten/Merk', 1, 3),
('assets/images/home-three/home-slider-media-ajar.png', '100+', 'Media Ajar', 1, 4),
('assets/images/home-three/home-slider-pengolahan-statistik.png', '100+', 'Pengolahan Statistik', 1, 5),
('assets/images/home-three/home-slider-pendampingan-jurnal.png', '100+', 'Penerbitan Jurnal', 1, 6),
('assets/images/home-three/home-slider-referensi.png', '50+', 'Referensi Monograf', 1, 7),
('assets/images/home-three/home-slider-elearning.png', '3+', 'E-Learning', 1, 8),
('assets/images/home-three/home-slider-pendampingan-tkda.png', '50+', 'TKDA & TKBI', 1, 9);

-- ===============================
-- Tabel untuk Navigasi
-- ===============================

-- Tabel untuk item navigasi utama
CREATE TABLE navbar_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    link VARCHAR(255) NOT NULL,
    parent_id INT DEFAULT NULL,
    has_dropdown TINYINT(1) DEFAULT 0,
    target VARCHAR(20) DEFAULT '_self', -- Untuk membuka di tab baru (_blank) atau di tab yang sama (_self)
    order_index INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES navbar_items(id) ON DELETE CASCADE
);

-- Tabel untuk pengaturan navigasi (logo dan tombol aksi)
CREATE TABLE navbar_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Memasukkan item menu default
INSERT INTO navbar_items (title, link, parent_id, has_dropdown, order_index, is_active) VALUES
-- Item menu utama
('Home', '/', NULL, 0, 1, 1),
('Tentang', '#', NULL, 1, 2, 1),
('Produk', '#', NULL, 1, 3, 1),
('Layanan', 'service', NULL, 0, 4, 1),
('Blog', 'blogs', NULL, 0, 5, 1),
('Kontak', 'contact', NULL, 0, 6, 1);

-- Item dropdown untuk "Tentang"
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

-- Item dropdown untuk "Produk"
INSERT INTO navbar_items (title, link, parent_id, has_dropdown, order_index, is_active, target)
SELECT 'E-Learning', 'https://vp-dls.akademimerdeka.com/', id, 0, 1, 1, '_blank' FROM navbar_items WHERE title = 'Produk';

INSERT INTO navbar_items (title, link, parent_id, has_dropdown, order_index, is_active, target)
SELECT 'E-Journal', 'https://journal.akademimerdeka.com/ojs/index.php/index/', id, 0, 2, 1, '_blank' FROM navbar_items WHERE title = 'Produk';

INSERT INTO navbar_items (title, link, parent_id, has_dropdown, order_index, is_active)
SELECT 'E-Perpus', 'eperpus', id, 0, 3, 1 FROM navbar_items WHERE title = 'Produk';

INSERT INTO navbar_items (title, link, parent_id, has_dropdown, order_index, is_active)
SELECT 'E-Catalogue', 'ecatalogue', id, 0, 4, 1 FROM navbar_items WHERE title = 'Produk';

-- Data default untuk logo dan tombol aksi
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
('action_button_target', '_blank');

-- ===============================
-- Tabel untuk Footer
-- ===============================

-- Tabel untuk pengaturan footer umum
CREATE TABLE IF NOT EXISTS footer_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel untuk link/layanan footer
CREATE TABLE IF NOT EXISTS footer_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section VARCHAR(50) NOT NULL, -- 'services', 'social', dll.
    title VARCHAR(100) NOT NULL,
    url VARCHAR(255) NOT NULL,
    icon VARCHAR(50), -- untuk kelas icon seperti 'bx bx-chevron-right'
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Data default untuk pengaturan footer
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

-- Data default untuk link layanan
INSERT INTO footer_links (section, title, url, icon, display_order, is_active) VALUES
('services', 'Penerbitan Jurnal', 'services/penerbitan-jurnal', 'bx bx-chevron-right', 1, TRUE),
('services', 'Penerbitan HKI', 'services/penerbitan-hki', 'bx bx-chevron-right', 2, TRUE),
('services', 'Pengolahan Statistik', 'services/pengolahan-statistik', 'bx bx-chevron-right', 3, TRUE),
('services', 'Pendampingan OJS', 'services/pendampingan-ojs', 'bx bx-chevron-right', 4, TRUE),
('services', 'Pendampingan TKDA/TKBI', 'services/pendampingan-tkda', 'bx bx-chevron-right', 5, TRUE),
('services', 'Konversi KTI', 'services/konversi-kti', 'bx bx-chevron-right', 6, TRUE),
('services', 'Pembuatan Media Ajar', 'services/media-ajar', 'bx bx-chevron-right', 7, TRUE);

-- Pengaturan tambahan untuk footer
INSERT INTO footer_settings (setting_key, setting_value) VALUES
('contact_title', 'Hubungi Kami')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

INSERT INTO footer_settings (setting_key, setting_value) VALUES
('services_title', 'Layanan Kami')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- Pengaturan untuk form newsletter
INSERT INTO footer_settings (setting_key, setting_value) VALUES
('bulletin_email_placeholder', 'Enter Your Email')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

INSERT INTO footer_settings (setting_key, setting_value) VALUES
('bulletin_success_message', 'Thank you for subscribing!')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

INSERT INTO footer_settings (setting_key, setting_value) VALUES
('bulletin_error_message', 'Something went wrong. Please try again.')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- ===============================
-- Tabel untuk Tim (Team)
-- ===============================

-- Tabel untuk anggota tim
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

-- Data awal untuk anggota tim
INSERT INTO team_members (name, position, image_path, display_order, is_active) VALUES
('Moch. Guntur', 'Chief Executive Officer', 'assets/images/team/pp-1.png', 1, 1),
('Azharani Aliyyatunnisa', 'Chief Technical Officer', 'assets/images/team/pp-2.png', 2, 1),
('Nur Indah Septia, N', 'Chief Operation Officer', 'assets/images/team/pp-3.png', 3, 1),
('Siti Musyarrofah', 'Chief Marketing Officer', 'assets/images/team/pp-4.png', 4, 1),
('Lusi Umayah', 'Chief Financial Officer', 'assets/images/team/pp-5.png', 5, 1),
('Ika Aprillia Putri', 'Chief Support Officer', 'assets/images/team/pp-6.png', 6, 1);

-- Pengaturan halaman tim
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

-- Data default untuk pengaturan halaman tim
INSERT INTO team_page_settings (
    title, subtitle, inner_title, 
    breadcrumb_parent, breadcrumb_parent_link, breadcrumb_current, 
    banner_image, seo_title
) VALUES (
    'Tim Kami', 'Tim', 'Tim', 
    'Tentang', '/', 'Tim', 
    'assets/images/shape/inner-shape.png', 'Tim | Akademi Merdeka'
);

-- ===============================
-- Tabel untuk Testimonial
-- ===============================

-- Tabel untuk item testimonial
CREATE TABLE IF NOT EXISTS testimonial_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(100) NOT NULL,
    client_position VARCHAR(100) NOT NULL,
    testimonial_text TEXT NOT NULL,
    image_path VARCHAR(255),
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Data awal untuk item testimonial
INSERT INTO testimonial_items (client_name, client_position, testimonial_text, image_path, display_order, is_active) VALUES
('Bayu Saputra', 'Mahasiswa', '"Adanya tim Akademi Merdeka membantu saya dalam penerbitan jurnal dengan metode yang efektif, membuat saya cepat memahami."', 'assets/images/clients-img/testi-4.jpg', 1, 1),
('Aryo Supratman', 'Dosen', '"Akademi Merdeka tidak hanya sekedar membantu dalam kenaikan Jabatan Fungsional, namun sebagai penasehat dan pendengar yang baik. Tim sangat responsif dan tanggap jika ada persoalan."', 'assets/images/clients-img/testi-3.jpg', 2, 1),
('Syadid', 'Mahasiswa', '"Tim Akademi Merdeka membantu pembuatan media ajar mulai dari penyusunan indikator dan memberikan inovasi yang sangat baik."', 'assets/images/clients-img/testi-6.jpg', 3, 1),
('Alya Afifah', 'Mahasiswa', '"Desain yang diberikan oleh tim Akademi Merdeka sangat kekinian, sehingga buku yang diterbitkan semakin menarik perhatian pembaca."', 'assets/images/clients-img/testi-1.jpg', 4, 1),
('Arini Sulistiawati', 'Mahasiswa', '"Pelayanan Pembuatan HKI sangat cepat. Tim hanya memerlukan 20 menit saja untuk mengirimkan sertifikat HKI kepada saya."', 'assets/images/clients-img/testi-2.jpg', 5, 1);

-- Pengaturan halaman testimonial
CREATE TABLE IF NOT EXISTS testimonial_page_settings (
    id INT PRIMARY KEY DEFAULT 1,
    title VARCHAR(255) NOT NULL DEFAULT 'Apa Kata Mereka?',
    subtitle VARCHAR(255) DEFAULT 'Testimoni Customer',
    description TEXT,
    inner_title VARCHAR(255) DEFAULT 'Testimoni',
    breadcrumb_parent VARCHAR(255) DEFAULT 'Tentang',
    breadcrumb_parent_link VARCHAR(255) DEFAULT '/',
    breadcrumb_current VARCHAR(255) DEFAULT 'Testimoni',
    banner_image VARCHAR(255) DEFAULT 'assets/images/shape/inner-shape.png',
    seo_title VARCHAR(255) DEFAULT 'Testimoni | Akademi Merdeka',
    seo_description TEXT,
    seo_keywords TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Data default untuk pengaturan halaman testimonial
INSERT INTO testimonial_page_settings (
    title, subtitle, inner_title, 
    breadcrumb_parent, breadcrumb_parent_link, breadcrumb_current, 
    banner_image, seo_title
) VALUES (
    'Apa Kata Mereka?', 'Testimoni Customer', 'Testimoni', 
    'Tentang', '/', 'Testimoni', 
    'assets/images/shape/inner-shape.png', 'Testimoni | Akademi Merdeka'
);

-- ===============================
-- Tabel untuk Layanan (Service)
-- ===============================

-- Tabel untuk kategori layanan
CREATE TABLE service_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    categories_name VARCHAR(50) NOT NULL
);

-- Tabel untuk fitur layanan
CREATE TABLE service_features (
    id INT PRIMARY KEY AUTO_INCREMENT,
    feature_name VARCHAR(100) NOT NULL,
    feature_category_id INT,
    feature_path VARCHAR(255) DEFAULT NULL,
    feature_image_path TEXT NOT NULL,
    FOREIGN KEY (feature_category_id) REFERENCES service_categories(id) ON DELETE SET NULL
);

-- Tabel untuk artikel layanan
CREATE TABLE service_articles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    slug VARCHAR(255) NOT NULL,
    feature_id INT DEFAULT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,  -- Menyimpan HTML hasil konversi Quill
    image_path VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (feature_id) REFERENCES service_features(id) ON DELETE SET NULL
);

-- Data untuk kategori layanan
INSERT INTO service_categories (categories_name) 
VALUES  
    ('Pendampingan'),
    ('Penerbitan'),
    ('Pengolahan'),
    ('Pembuatan'),
    ('Konversi');

-- Data untuk fitur layanan
INSERT INTO service_features (feature_name, feature_category_id, feature_image_path) 
VALUES 
    -- Pendampingan (kategori_id = 1)
    ('Penerbitan Jurnal', 1, 'assets/images/uploads/services/services-jurnal.jpg'),
    ('TKDA/TKBI', 1, 'assets/images/uploads/services/services-tkda.jpg'),
    ('OJS', 1, 'assets/images/uploads/services/services-ojs.jpg'),

    -- Penerbitan (kategori_id = 2)
    ('HKI, Paten, Merk', 2, 'assets/images/uploads/services/services-hki.jpg'),

    -- Pengolahan (kategori_id = 3)
    ('Statistik', 3, 'assets/images/uploads/services/services-statistik.jpg'),

    -- Pembuatan (kategori_id = 4)
    ('Media Ajar', 4, 'assets/images/uploads/services/services-mediaajar.jpg'),
    ('E-Learning', 4, 'assets/images/uploads/services/services-elearning.jpg'),

    -- Konversi (kategori_id = 5)
    ('KTI', 5, 'assets/images/uploads/services/services-kti.jpg');

