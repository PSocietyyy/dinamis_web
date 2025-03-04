-- Membuat database
CREATE DATABASE IF NOT EXISTS db_dinamis;
USE db_dinamis;

-- Membuat tabel users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100),
    email VARCHAR(100),
    role ENUM('admin', 'user') DEFAULT 'user',
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Menambahkan user admin default
INSERT INTO users (username, password, nama_lengkap, role) 
VALUES ('admin', 'admin123', 'Administrator', 'admin');

-- Menambahkan user biasa
INSERT INTO users (username, password, nama_lengkap, role) 
VALUES ('user', 'user123', 'Regular User', 'user');

-- Create the page_content table
CREATE TABLE IF NOT EXISTS page_content (
  id INT AUTO_INCREMENT PRIMARY KEY,
  section_name VARCHAR(50) NOT NULL,
  section_key VARCHAR(50) NOT NULL,
  content_type VARCHAR(20) NOT NULL DEFAULT 'text',
  content_value TEXT,
  image_url VARCHAR(255),
  link_url VARCHAR(255),
  sort_order INT DEFAULT 0,
  is_active BOOLEAN DEFAULT TRUE,
  last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY section_key_unique (section_name, section_key)
);

-- NAVBAR CONTENT --

-- Insert navbar branding
INSERT INTO page_content (section_name, section_key, content_type, content_value, image_url, sort_order) VALUES
('navbar', 'navbar_logo', 'image', 'Main Logo', 'assets/images/logos/logo-akademi-merdeka.png', 1),
('navbar', 'navbar_logo_dark', 'image', 'Dark Mode Logo', 'assets/images/logos/logo-2.png', 2);

-- Insert navbar menu items
INSERT INTO page_content (section_name, section_key, content_type, content_value, link_url, sort_order, is_active) VALUES
('navbar_menu', 'Home', 'link', 'Home', 'index.php', 1, 1),
('navbar_menu', 'Tentang', 'dropdown', 'Tentang', '#', 2, 1),
('navbar_menu', 'Produk', 'dropdown', 'Produk', '#', 3, 1),
('navbar_menu', 'Layanan', 'link', 'Layanan', 'service.php', 4, 1),
('navbar_menu', 'Blog', 'link', 'Blog', 'blogs.php', 5, 1),
('navbar_menu', 'Kontak', 'link', 'Kontak', 'contact.php', 6, 1),
('navbar_menu', 'Login', 'link', 'Login', 'login.php', 7, 1);

-- Insert navbar dropdown items for Tentang
INSERT INTO page_content (section_name, section_key, content_type, content_value, link_url, sort_order, is_active) VALUES
('navbar_dropdown_Tentang', 'Tim', 'link', 'Tim', 'team.php', 1, 1),
('navbar_dropdown_Tentang', 'Testimoni', 'link', 'Testimoni', 'testimonial.php', 2, 1),
('navbar_dropdown_Tentang', 'FAQ', 'link', 'FAQ', 'faq.php', 3, 1),
('navbar_dropdown_Tentang', 'Syarat & Ketentuan', 'link', 'Syarat & Ketentuan', 'terms-condition.php', 4, 1),
('navbar_dropdown_Tentang', 'Kebijakan Privasi', 'link', 'Kebijakan Privasi', 'privacy-policy.php', 5, 1);

-- Insert navbar dropdown items for Produk
INSERT INTO page_content (section_name, section_key, content_type, content_value, link_url, sort_order, is_active) VALUES
('navbar_dropdown_Produk', 'E-Learning', 'link', 'E-Learning', 'https://vp-dls.akademimerdeka.com/', 1, 1),
('navbar_dropdown_Produk', 'E-Journal', 'link', 'E-Journal', 'https://journal.akademimerdeka.com/ojs/index.php/index/', 2, 1),
('navbar_dropdown_Produk', 'E-Perpus', 'link', 'E-Perpus', 'eperpus.php', 3, 1),
('navbar_dropdown_Produk', 'E-Catalogue', 'link', 'E-Catalogue', 'ecatalogue.php', 4, 1);

-- Insert navbar action button
INSERT INTO page_content (section_name, section_key, content_type, content_value, link_url, sort_order) VALUES
('navbar', 'action_button', 'button', 'Konsultasi Sekarang', 'https://wa.me/6287735426107', 3);

-- HERO SECTION CONTENT --

-- Insert initial hero section content
INSERT INTO page_content (section_name, section_key, content_type, content_value, sort_order) VALUES
('hero', 'hero_title', 'text', 'Platform Academic Digital With Excellent Quality', 1),
('hero', 'hero_description', 'text', 'Platform Akademi Merdeka membantu setiap insan akademisi dengan pelayanan yang eksklusif', 2),
('hero', 'button1_text', 'text', 'Learn More', 3),
('hero', 'button1_url', 'link', '#', 4),
('hero', 'button2_text', 'text', 'Whatsapp', 5),
('hero', 'button2_url', 'link', 'https://wa.me/6287735426107', 6),
('hero', 'hero_image', 'image', 'assets/images/home-three/home-main-pic.png', 7);

-- ABOUT SECTION CONTENT --

-- Insert initial about section content
INSERT INTO page_content (section_name, section_key, content_type, content_value, sort_order) VALUES
('about', 'about_subtitle', 'text', 'About Us', 1),
('about', 'about_title', 'text', 'Tentang Kita', 2),
('about', 'about_description', 'text', 'Akademi Merdeka mempunyai ruang lingkup dalam bidang akademisi yang tujuannya ialah membantu setiap insan akademisi dengan berbagai problematika yang sedang dihadapi.', 3),
('about', 'about_image', 'image', 'assets/images/about/home-about.png', 4);

-- Insert initial about feature items
INSERT INTO page_content (section_name, section_key, content_type, content_value, link_url, sort_order) VALUES
('about_feature', 'Experience', 'icon', 'flaticon-practice', 'Berbagai macam persoalan sudah kami pecahkan dengan prosedur yang efektif.', 1),
('about_feature', 'Quick Support', 'icon', 'flaticon-help', 'Dukungan setiap persoalan akan didampingi oleh satu supervisi yang expert.', 2);

-- SERVICES SECTION CONTENT --

-- Insert initial service section content
INSERT INTO page_content (section_name, section_key, content_type, content_value, sort_order) VALUES
('service', 'service_subtitle', 'text', 'Layanan', 1),
('service', 'service_title', 'text', 'Layanan Kami', 2);

-- Insert initial service items
INSERT INTO page_content (section_name, section_key, content_type, content_value, image_url, link_url, sort_order) VALUES
('service', 'Penerbitan Jurnal', 'text', 'Pendampingan penerbitan Jurnal Nasional Terakreditasi (Sinta), WOS, Scopus, Emarld, Thomson, dll.', 'assets/images/services/ico-jurnal.png', 'services/penerbitan-jurnal', 3),
('service', 'Penerbitan HKI', 'text', 'Melayani penerbitan Hak Paten, HKI, Merk, dll dengan waktu yang cepat dan hasil yang memuaskan.', 'assets/images/services/ico-haki.png', 'services/penerbitan-hki', 4),
('service', 'Pengolahan Statistik', 'text', 'Pendampingan pengolahan data dengan software SAS, R-Studio, SPSS dari berbagai macam analisis sesuai dengan metodologi.', 'assets/images/services/ico-statistik.png', 'services/pengolahan-statistik', 5),
('service', 'Pendampingan TKDA/TKBI', 'text', 'Pelayanan pendampingan TKDA/TKBI agar lolos passing grade diberbagai tes seperti UNPAD, UNAIR, PLTI, Bappenas.', 'assets/images/services/ico-tkbi.png', 'services/pendampingan-tkda', 6),
('service', 'Pendampingan OJS', 'text', 'Pembuatan OJS akan didampingi oleh supervisi yang expert dalam menyiapkan OJS menarik dan responsive.', 'assets/images/services/ico-ojs.png', 'services/pendampingan-ojs', 7),
('service', 'Pembuatan Media Ajar', 'text', 'Berbagai media ajar yang dibutuhkan diberbagai mata kuliah, atau mata pelajaran baik digital atau alat peraga.', 'assets/images/services/ico-mediaajar.png', 'services/media-ajar', 8),
('service', 'Konversi KTI', 'text', 'Melayani pendampingan dalam mengkonversi karya tulis ilmiah menjadi Book Chapter, Reference Book, Monograf.', 'assets/images/services/ico-kti.png', 'services/konversi-kti', 9),
('service', 'E-Learning', 'text', 'Pembuatan berbagai macam jenis platform pembelajaran jarak jauh seperti Moodle, Joomla, dan lainnya.', 'assets/images/services/ico-elearning.png', 'services/elearning', 10);

-- CTA SECTION CONTENT --

-- Insert initial CTA section content
INSERT INTO page_content (section_name, section_key, content_type, content_value, sort_order) VALUES
('cta', 'cta_subtitle', 'text', 'Hubungi Kami', 1),
('cta', 'cta_title', 'text', 'Kami melayani berbagai persoalan dengan solusi yang tepat', 2),
('cta', 'cta_button_text', 'text', 'Whatsapp', 3),
('cta', 'cta_button_url', 'link', 'https://wa.me/6287735426107', 4);

-- PRODUCTS SECTION CONTENT --

-- Insert initial product section content
INSERT INTO page_content (section_name, section_key, content_type, content_value, sort_order) VALUES
('product', 'product_subtitle', 'text', 'Produk Kami', 1),
('product', 'product_title', 'text', 'Kami memberikan solusi terbaik dengan produk terpercaya dan berkualitas', 2);

-- Insert initial product items
INSERT INTO page_content (section_name, section_key, content_type, content_value, image_url, sort_order) VALUES
('product', 'KTI', 'text', '', 'assets/images/services/ico-kti-p.png', 3),
('product', 'Journal', 'text', '', 'assets/images/services/ico-jurnal-p.png', 4),
('product', 'HKI', 'text', '', 'assets/images/services/ico-haki-p.png', 5),
('product', 'OJS', 'text', '', 'assets/images/services/ico-ojs-p.png', 6),
('product', 'Media', 'text', '', 'assets/images/services/ico-mediaajar-p.png', 7);

-- TESTIMONIALS SECTION CONTENT --

-- Insert initial testimonial section content
INSERT INTO page_content (section_name, section_key, content_type, content_value, sort_order) VALUES
('testimonial', 'testimonial_subtitle', 'text', 'Testimoni', 1),
('testimonial', 'testimonial_title', 'text', 'Apa Kata Mereka?', 2);

-- Insert initial testimonial items
INSERT INTO page_content (section_name, section_key, content_type, content_value, image_url, link_url, sort_order) VALUES
('testimonial', 'Bayu Saputra', 'text', '"Adanya tim Akademi Merdeka membantu saya dalam penerbitan jurnal dengan metode yang efektif, membuat saya cepat memahami."', 'assets/images/clients-img/testi-4.jpg', 'Mahasiswa', 3),
('testimonial', 'Aryo Supratman', 'text', '"Akademi Merdeka tidak hanya sekedar membantu dalam kenaikan Jabatan Fungsional, namun sebagai penasehat dan pendengar yang baik. Tim sangat responsif dan tanggap jika ada persoalan."', 'assets/images/clients-img/testi-3.jpg', 'Dosen', 4),
('testimonial', 'Syadid', 'text', '"Tim Akademi Merdeka membantu pembuatan media ajar mulai dari penyusunan indikator dan memberikan inovasi yang sangat baik."', 'assets/images/clients-img/testi-6.jpg', 'Mahasiswa', 5);

-- BLOG SECTION CONTENT --

-- Insert initial blog section content
INSERT INTO page_content (section_name, section_key, content_type, content_value, sort_order) VALUES
('blog', 'blog_subtitle', 'text', 'Blog', 1),
('blog', 'blog_title', 'text', 'Artikel Kami', 2);

-- Insert initial blog posts
INSERT INTO page_content (section_name, section_key, content_type, content_value, image_url, link_url, sort_order) VALUES
('blog', 'Teknik Pembuatan Jurnal/Artikel', 'text', 'Jurnal merupakan sebuah publikasi periodik dalam bentuk artikel yang diterbitkan secara berkala, dalam hal ini biasanya jurnal diterbitkan pada interval waktu tertentu...', 'assets/images/blog/blog-teknik-pembuatan-jurnal-artikel.jpg', 'blog/teknik-pembuatan-jurnal-artikel', 3),
('blog', 'Langkah Langkah Mendapatkan Hak Cipta', 'text', 'Hak Cipta atau copyright adalah hak eksklusif yang diberikan kepada pencipta atau pemegang hak cipta untuk mengatur penggunaan hasil penuangan gagasan...', 'assets/images/blog/blog-langkah-langkah-mendapatkan-hak-cipta.jpg', 'blog/langkah-langkah-mendapatkan-hak-cipta', 4),
('blog', 'Tips Konversi KTI Menjadi Buku Referensi/Book Chapter', 'text', 'KTI merupakan sebuah rangkaian informasi penting yang dapat digunakan sebagai bahan untuk memecahkan persoalan praktis di lapangan. Tentu sangat disayangkan...', 'assets/images/blog/blog-tips-konversi-kti-menjadi-buku-referensi-book-chapter.jpg', 'blog/tips-konversi-kti-menjadi-buku-referensi-book-chapter', 5);

-- FOOTER CONTENT --

-- Insert footer information
INSERT INTO page_content (section_name, section_key, content_type, content_value, image_url, sort_order) VALUES
('footer', 'footer_logo', 'image', 'Footer Logo', 'assets/images/logos/logo-footer.png', 1),
('footer', 'footer_address', 'text', 'Akademi Merdeka Office:<br>Perumahan Kheandra Kalijaga<br>Harjamukti, Cirebon, Jawa Barat', '', 2),
('footer', 'footer_phone', 'text', '+62 877-3542-6107', 'tel:+62877-3542-6107', 3);

-- Insert footer services links
INSERT INTO page_content (section_name, section_key, content_type, content_value, link_url, sort_order, is_active) VALUES
('footer_services', 'Penerbitan Jurnal', 'link', 'Penerbitan Jurnal', 'services/penerbitan-jurnal', 1, 1),
('footer_services', 'Penerbitan HKI', 'link', 'Penerbitan HKI', 'services/penerbitan-hki', 2, 1),
('footer_services', 'Pengolahan Statistik', 'link', 'Pengolahan Statistik', 'services/pengolahan-statistik', 3, 1),
('footer_services', 'Pendampingan OJS', 'link', 'Pendampingan OJS', 'services/pendampingan-ojs', 4, 1),
('footer_services', 'Pendampingan TKDA/TKBI', 'link', 'Pendampingan TKDA/TKBI', 'services/pendampingan-tkda', 5, 1),
('footer_services', 'Konversi KTI', 'link', 'Konversi KTI', 'services/konversi-kti', 6, 1),
('footer_services', 'Pembuatan Media Ajar', 'link', 'Pembuatan Media Ajar', 'services/media-ajar', 7, 1);

-- Insert footer blog items 
INSERT INTO page_content (section_name, section_key, content_type, content_value, image_url, link_url, sort_order) VALUES
('footer_blog', 'Teknik Pembuatan Jurnal/Artikel', 'blog', '11 Jan 2023', 'assets/images/blog/blog-footer-jurnal.png', 'blog/teknik-pembuatan-jurnal-artikel', 1),
('footer_blog', 'Langkah Langkah Mendapatkan HKI', 'blog', '08 Jan 2023', 'assets/images/blog/blog-footer-hki.png', 'blog/langkah-langkah-mendapatkan-hak-cipta', 2),
('footer_blog', 'Tips Konversi KTI Menjadi Buku', 'blog', '06 Jan 2023', 'assets/images/blog/blog-footer-kti.png', 'blog/tips-konversi-kti-menjadi-buku-referensi-book-chapter', 3);

-- Insert footer copyright
INSERT INTO page_content (section_name, section_key, content_type, content_value, link_url, sort_order) VALUES
('footer', 'copyright', 'text', 'Copyright © 2023 <a href="https://akademimerdeka.com/">Akademi Merdeka</a> as establisment date 2022', '', 4);

-- STATS CONTENT --

-- Insert initial stats sliders
INSERT INTO page_content (section_name, section_key, content_type, content_value, image_url, sort_order) VALUES
('stats', 'Karya Ilmiah', 'text', '500+', 'assets/images/home-three/home-slider-karya-ilmiah.png', 1),
('stats', 'Pendampingan OJS', 'text', '10+', 'assets/images/home-three/home-slider-pendampingan-ojs.png', 2),
('stats', 'HKI/Paten/Merk', 'text', '100+', 'assets/images/home-three/home-slider-haki.png', 3),
('stats', 'Media Ajar', 'text', '100+', 'assets/images/home-three/home-slider-media-ajar.png', 4),
('stats', 'Pengolahan Statistik', 'text', '100+', 'assets/images/home-three/home-slider-pengolahan-statistik.png', 5),
('stats', 'Penerbitan Jurnal', 'text', '100+', 'assets/images/home-three/home-slider-pendampingan-jurnal.png', 6),
('stats', 'Referensi Monograf', 'text', '50+', 'assets/images/home-three/home-slider-referensi.png', 7),
('stats', 'E-Learning', 'text', '3+', 'assets/images/home-three/home-slider-elearning.png', 8),
('stats', 'TKDA & TKBI', 'text', '50+', 'assets/images/home-three/home-slider-pendampingan-tkda.png', 9);