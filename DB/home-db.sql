-- Home page settings and content tables
USE akademi_merdeka;

-- Create tables for dynamic homepage content
CREATE TABLE IF NOT EXISTS home_banner (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title TEXT NOT NULL,
    subtitle TEXT,
    button1_text VARCHAR(100),
    button1_url VARCHAR(255),
    button2_text VARCHAR(100),
    button2_url VARCHAR(255),
    banner_image VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Statistics counter items
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

-- About section data
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

-- Call to action section
CREATE TABLE IF NOT EXISTS home_cta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title TEXT NOT NULL,
    subtitle TEXT,
    button_text VARCHAR(100),
    button_url VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Products section titles
CREATE TABLE IF NOT EXISTS home_products_section (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    subtitle TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Products section items
CREATE TABLE IF NOT EXISTS home_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    image_path VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Testimonials section title
CREATE TABLE IF NOT EXISTS home_testimonials_section (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    subtitle VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Testimonials section items
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

-- Update the home_banner table to add shape_image column
ALTER TABLE home_banner ADD COLUMN shape_image VARCHAR(255) AFTER banner_image;

-- Insert default data
-- Banner
INSERT INTO home_banner (id, title, subtitle, button1_text, button1_url, button2_text, button2_url, banner_image, shape_image) 
VALUES (1, 'Platform Academic Digital With Excellent Quality', 'Platform Akademi Merdeka membantu setiap insan akademisi dengan pelayanan yang eksklusif', 
'Learn More', '#', 'Whatsapp', 'https://wa.me/6287735426107', 'assets/images/home-three/home-main-pic.png', 'assets/images/home-three/home-three-shape.png')
ON DUPLICATE KEY UPDATE id = 1;

-- About section
INSERT INTO home_about (id, title, subtitle, content, image_path, card1_icon, card1_title, card1_text, card2_icon, card2_title, card2_text)
VALUES (1, 'Tentang Kita', 'About Us', 'Akademi Merdeka mempunyai ruang lingkup dalam bidang akademisi yang tujuannya ialah membantu setiap insan akademisi dengan berbagai problematika yang sedang dihadapi.', 
'assets/images/about/home-about.png', 'flaticon-practice', 'Experience', 'Berbagai macam persoalan sudah kami pecahkan dengan prosedur yang efektif.',
'flaticon-help', 'Quick Support', 'Dukungan setiap persoalan akan didampingi oleh satu supervisi yang expert.')
ON DUPLICATE KEY UPDATE id = 1;

-- Call to action
INSERT INTO home_cta (id, title, subtitle, button_text, button_url)
VALUES (1, 'Kami melayani berbagai persoalan dengan solusi yang tepat', 'Hubungi Kami', 'Whatsapp', 'https://wa.me/6287735426107')
ON DUPLICATE KEY UPDATE id = 1;

-- Products section title
INSERT INTO home_products_section (id, title, subtitle)
VALUES (1, 'Kami memberikan solusi terbaik dengan produk terpercaya dan berkualitas', 'Produk Kami')
ON DUPLICATE KEY UPDATE id = 1;

-- Products
INSERT INTO home_products (title, image_path, is_active, display_order) VALUES
('KTI', 'assets/images/services/ico-kti-p.png', 1, 1),
('Journal', 'assets/images/services/ico-jurnal-p.png', 1, 2),
('HKI', 'assets/images/services/ico-haki-p.png', 1, 3),
('OJS', 'assets/images/services/ico-ojs-p.png', 1, 4),
('Media', 'assets/images/services/ico-mediaajar-p.png', 1, 5);

-- Testimonials section title
INSERT INTO home_testimonials_section (id, title, subtitle)
VALUES (1, 'Apa Kata Mereka?', 'Testimoni')
ON DUPLICATE KEY UPDATE id = 1;

-- Testimonials
INSERT INTO home_testimonials (client_name, client_position, testimonial_text, client_image, is_active, display_order) VALUES
('Bayu Saputra', 'Mahasiswa', '"Adanya tim Akademi Merdeka membantu saya dalam penerbitan jurnal dengan metode yang efektif, membuat saya cepat memahami."', 'assets/images/clients-img/testi-4.jpg', 1, 1),
('Aryo Supratman', 'Dosen', '"Akademi Merdeka tidak hanya sekedar membantu dalam kenaikan Jabatan Fungsional, namun sebagai penasehat dan pendengar yang baik. Tim sangat responsif dan tanggap jika ada persoalan."', 'assets/images/clients-img/testi-3.jpg', 1, 2),
('Syadid', 'Mahasiswa', '"Tim Akademi Merdeka membantu pembuatan media ajar mulai dari penyusunan indikator dan memberikan inovasi yang sangat baik."', 'assets/images/clients-img/testi-6.jpg', 1, 3),
('Alya Afifah', 'Mahasiswa', '"Desain yang diberikan oleh tim Akademi Merdeka sangat kekinian, sehingga buku yang diterbitkan semakin menarik perhatian pembaca."', 'assets/images/clients-img/testi-1.jpg', 1, 4),
('Arini Sulistiawati', 'Mahasiswa', '"Pelayanan Pembuatan HKI sangat cepat. Tim hanya memerlukan 20 menit saja untuk mengirimkan sertifikat HKI kepada saya."', 'assets/images/clients-img/testi-2.jpg', 1, 5);

-- Stats counter items
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