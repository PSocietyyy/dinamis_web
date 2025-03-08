-- Homepage Sections Table
CREATE TABLE IF NOT EXISTS homepage_sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_name VARCHAR(50) NOT NULL,
    section_key VARCHAR(50) NOT NULL UNIQUE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Homepage Content Table
CREATE TABLE IF NOT EXISTS homepage_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_id INT NOT NULL,
    content_key VARCHAR(50) NOT NULL,
    content_value TEXT,
    content_type ENUM('text', 'textarea', 'image', 'color', 'link') NOT NULL DEFAULT 'text',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (section_id) REFERENCES homepage_sections(id) ON DELETE CASCADE,
    UNIQUE KEY (section_id, content_key)
);

-- Stats Slider Items Table
CREATE TABLE IF NOT EXISTS stats_slider (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    count VARCHAR(20) NOT NULL,
    image VARCHAR(255) NOT NULL,
    position INT NOT NULL DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Testimonials Table
CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    position VARCHAR(100) NOT NULL,
    image VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Services Section Table
CREATE TABLE IF NOT EXISTS services_section (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    icon VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    link VARCHAR(255) NOT NULL,
    position INT NOT NULL DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Products Section Table
CREATE TABLE IF NOT EXISTS products_section (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    icon VARCHAR(255) NOT NULL,
    position INT NOT NULL DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Featured Blog Posts Table
CREATE TABLE IF NOT EXISTS featured_blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    image VARCHAR(255) NOT NULL,
    date DATE NOT NULL,
    author VARCHAR(100) DEFAULT 'Admin',
    category VARCHAR(50) NOT NULL,
    excerpt TEXT NOT NULL,
    link VARCHAR(255) NOT NULL,
    position INT NOT NULL DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default homepage sections
INSERT INTO homepage_sections (section_name, section_key, is_active) VALUES
('Banner/Hero Section', 'banner', 1),
('Stats Slider', 'stats', 1),
('About Us Section', 'about', 1),
('Services Section', 'services', 1),
('Contact Section', 'contact', 1),
('Products Section', 'products', 1),
('Testimonials Section', 'testimonials', 1),
('Blog Section', 'blog', 1);

-- Insert default content for Banner section
SET @banner_id = (SELECT id FROM homepage_sections WHERE section_key = 'banner');
INSERT INTO homepage_content (section_id, content_key, content_value, content_type) VALUES
(@banner_id, 'title', 'Platform Academic Digital With Excellent Quality', 'text'),
(@banner_id, 'subtitle', 'Platform Akademi Merdeka membantu setiap insan akademisi dengan pelayanan yang eksklusif', 'textarea'),
(@banner_id, 'button1_text', 'Learn More', 'text'),
(@banner_id, 'button1_link', '#', 'link'),
(@banner_id, 'button2_text', 'Whatsapp', 'text'),
(@banner_id, 'button2_link', 'https://wa.me/6287735426107', 'link'),
(@banner_id, 'banner_image', 'assets/images/home-three/home-main-pic.png', 'image'),
(@banner_id, 'banner_shape', 'assets/images/home-three/home-three-shape.png', 'image');

-- Insert default content for About section
SET @about_id = (SELECT id FROM homepage_sections WHERE section_key = 'about');
INSERT INTO homepage_content (section_id, content_key, content_value, content_type) VALUES
(@about_id, 'title', 'Tentang Kita', 'text'),
(@about_id, 'subtitle', 'About Us', 'text'),
(@about_id, 'description', 'Akademi Merdeka mempunyai ruang lingkup dalam bidang akademisi yang tujuannya ialah membantu setiap insan akademisi dengan berbagai problematika yang sedang dihadapi.', 'textarea'),
(@about_id, 'image', 'assets/images/about/home-about.png', 'image'),
(@about_id, 'card1_title', 'Experience', 'text'),
(@about_id, 'card1_icon', 'flaticon-practice', 'text'),
(@about_id, 'card1_text', 'Berbagai macam persoalan sudah kami pecahkan dengan prosedur yang efektif.', 'textarea'),
(@about_id, 'card2_title', 'Quick Support', 'text'),
(@about_id, 'card2_icon', 'flaticon-help', 'text'),
(@about_id, 'card2_text', 'Dukungan setiap persoalan akan didampingi oleh satu supervisi yang expert.', 'textarea');

-- Insert default content for Services section
SET @services_id = (SELECT id FROM homepage_sections WHERE section_key = 'services');
INSERT INTO homepage_content (section_id, content_key, content_value, content_type) VALUES
(@services_id, 'title', 'Layanan Kami', 'text'),
(@services_id, 'subtitle', 'Layanan', 'text');

-- Insert default service items
INSERT INTO services_section (title, icon, description, link, position, is_active) VALUES
('Penerbitan Jurnal', 'assets/images/services/ico-jurnal.png', 'Pendampingan penerbitan Jurnal Nasional Terakreditasi (Sinta), WOS, Scopus, Emarld, Thomson, dll.', 'services/penerbitan-jurnal', 1, 1),
('Penerbitan HKI', 'assets/images/services/ico-haki.png', 'Melayani penerbitan Hak Paten, HKI, Merk, dll dengan waktu yang cepat dan hasil yang memuaskan.', 'services/penerbitan-hki', 2, 1),
('Pengolahan Statistik', 'assets/images/services/ico-statistik.png', 'Pendampingan pengolahan data dengan software SAS, R-Studio, SPSS dari berbagai macam analisis sesuai dengan metodologi.', 'services/pengolahan-statistik', 3, 1),
('Pendampingan TKDA/TKBI', 'assets/images/services/ico-tkbi.png', 'Pelayanan pendampingan TKDA/TKBI agar lolos passing grade diberbagai tes seperti UNPAD, UNAIR, PLTI, Bappenas.', 'services/pendampingan-tkda', 4, 1),
('Pendampingan OJS', 'assets/images/services/ico-ojs.png', 'Pembuatan OJS akan didampingi oleh supervisi yang expert dalam menyiapkan OJS menarik dan responsive.', 'services/pendampingan-ojs', 5, 1),
('Pembuatan Media Ajar', 'assets/images/services/ico-mediaajar.png', 'Berbagai media ajar yang dibutuhkan diberbagai mata kuliah, atau mata pelajaran baik digital atau alat peraga.', 'services/media-ajar', 6, 1),
('Konversi KTI', 'assets/images/services/ico-kti.png', 'Melayani pendampingan dalam mengkonversi karya tulis ilmiah menjadi Book Chapter, Reference Book, Monograf.', 'services/konversi-kti', 7, 1),
('E-Learning', 'assets/images/services/ico-elearning.png', 'Pembuatan berbagai macam jenis platform pembelajaran jarak jauh seperti Moodle, Joomla, dan lainnya.', 'services/elearning', 8, 1);

-- Insert default content for Contact section
SET @contact_id = (SELECT id FROM homepage_sections WHERE section_key = 'contact');
INSERT INTO homepage_content (section_id, content_key, content_value, content_type) VALUES
(@contact_id, 'title', 'Kami melayani berbagai persoalan dengan solusi yang tepat', 'text'),
(@contact_id, 'subtitle', 'Hubungi Kami', 'text'),
(@contact_id, 'button_text', 'Whatsapp', 'text'),
(@contact_id, 'button_link', 'https://wa.me/6287735426107', 'link');

-- Insert default content for Products section
SET @products_id = (SELECT id FROM homepage_sections WHERE section_key = 'products');
INSERT INTO homepage_content (section_id, content_key, content_value, content_type) VALUES
(@products_id, 'title', 'Kami memberikan solusi terbaik dengan produk terpercaya dan berkualitas', 'text'),
(@products_id, 'subtitle', 'Produk Kami', 'text');

-- Insert default product items
INSERT INTO products_section (title, icon, position, is_active) VALUES
('KTI', 'assets/images/services/ico-kti-p.png', 1, 1),
('Journal', 'assets/images/services/ico-jurnal-p.png', 2, 1),
('HKI', 'assets/images/services/ico-haki-p.png', 3, 1),
('OJS', 'assets/images/services/ico-ojs-p.png', 4, 1),
('Media', 'assets/images/services/ico-mediaajar-p.png', 5, 1);

-- Insert default content for Testimonials section
SET @testimonials_id = (SELECT id FROM homepage_sections WHERE section_key = 'testimonials');
INSERT INTO homepage_content (section_id, content_key, content_value, content_type) VALUES
(@testimonials_id, 'title', 'Apa Kata Mereka?', 'text'),
(@testimonials_id, 'subtitle', 'Testimoni', 'text');

-- Insert default testimonials
INSERT INTO testimonials (name, position, image, content, is_active) VALUES
('Bayu Saputra', 'Mahasiswa', 'assets/images/clients-img/testi-4.jpg', '"Adanya tim Akademi Merdeka membantu saya dalam penerbitan jurnal dengan metode yang efektif, membuat saya cepat memahami."', 1),
('Aryo Supratman', 'Dosen', 'assets/images/clients-img/testi-3.jpg', '"Akademi Merdeka tidak hanya sekedar membantu dalam kenaikan Jabatan Fungsional, namun sebagai penasehat dan pendengar yang baik. Tim sangat responsif dan tanggap jika ada persoalan."', 1),
('Syadid', 'Mahasiswa', 'assets/images/clients-img/testi-6.jpg', '"Tim Akademi Merdeka membantu pembuatan media ajar mulai dari penyusunan indikator dan memberikan inovasi yang sangat baik."', 1),
('Alya Afifah', 'Mahasiswa', 'assets/images/clients-img/testi-1.jpg', '"Desain yang diberikan oleh tim Akademi Merdeka sangat kekinian, sehingga buku yang diterbitkan semakin menarik perhatian pembaca."', 1),
('Arini Sulistiawati', 'Mahasiswa', 'assets/images/clients-img/testi-2.jpg', '"Pelayanan Pembuatan HKI sangat cepat. Tim hanya memerlukan 20 menit saja untuk mengirimkan sertifikat HKI kepada saya."', 1);

-- Insert default content for Blog section
SET @blog_id = (SELECT id FROM homepage_sections WHERE section_key = 'blog');
INSERT INTO homepage_content (section_id, content_key, content_value, content_type) VALUES
(@blog_id, 'title', 'Artikel Kami', 'text'),
(@blog_id, 'subtitle', 'Blog', 'text');

-- Insert default featured blog posts
INSERT INTO featured_blog_posts (title, image, date, author, category, excerpt, link, position, is_active) VALUES
('Teknik Pembuatan Jurnal/Artikel', 'assets/images/blog/blog-teknik-pembuatan-jurnal-artikel.jpg', '2023-01-11', 'Admin', 'Jurnal', 'Jurnal merupakan sebuah publikasi periodik dalam bentuk artikel yang diterbitkan secara berkala, dalam hal ini biasanya jurnal diterbitkan pada interval waktu tertentu...', 'blog/teknik-pembuatan-jurnal-artikel', 1, 1),
('Langkah Langkah Mendapatkan Hak Cipta', 'assets/images/blog/blog-langkah-langkah-mendapatkan-hak-cipta.jpg', '2023-01-08', 'Admin', 'HKI', 'Hak Cipta atau copyright adalah hak eksklusif yang diberikan kepada pencipta atau pemegang hak cipta untuk mengatur penggunaan hasil penuangan gagasan...', 'blog/langkah-langkah-mendapatkan-hak-cipta', 2, 1),
('Tips Konversi KTI Menjadi Buku Referensi/Book Chapter', 'assets/images/blog/blog-tips-konversi-kti-menjadi-buku-referensi-book-chapter.jpg', '2023-01-06', 'Admin', 'KTI', 'KTI merupakan sebuah rangkaian informasi penting yang dapat digunakan sebagai bahan untuk memecahkan persoalan praktis di lapangan. Tentu sangat disayangkan...', 'blog/tips-konversi-kti-menjadi-buku-referensi-book-chapter', 3, 1);

-- Insert default stats slider items
INSERT INTO stats_slider (title, count, image, position, is_active) VALUES
('Karya Ilmiah', '500+', 'assets/images/home-three/home-slider-karya-ilmiah.png', 1, 1),
('Pendampingan OJS', '10+', 'assets/images/home-three/home-slider-pendampingan-ojs.png', 2, 1),
('HKI/Paten/Merk', '100+', 'assets/images/home-three/home-slider-haki.png', 3, 1),
('Media Ajar', '100+', 'assets/images/home-three/home-slider-media-ajar.png', 4, 1),
('Pengolahan Statistik', '100+', 'assets/images/home-three/home-slider-pengolahan-statistik.png', 5, 1),
('Penerbitan Jurnal', '100+', 'assets/images/home-three/home-slider-pendampingan-jurnal.png', 6, 1),
('Referensi Monograf', '50+', 'assets/images/home-three/home-slider-referensi.png', 7, 1),
('E-Learning', '3+', 'assets/images/home-three/home-slider-elearning.png', 8, 1),
('TKDA & TKBI', '50+', 'assets/images/home-three/home-slider-pendampingan-tkda.png', 9, 1);