-- FAQ page database structure for Akademi Merdeka
USE akademi_merdeka;

-- Create FAQ banners table
CREATE TABLE IF NOT EXISTS faq_banners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_slug VARCHAR(100) NOT NULL UNIQUE,
    title VARCHAR(100) NOT NULL,
    breadcrumb_text VARCHAR(100) NOT NULL,
    banner_image VARCHAR(255) DEFAULT 'assets/images/shape/inner-shape.png',
    faq_title VARCHAR(100) DEFAULT 'Frequently Asked Questions',
    faq_subtitle TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default banner for FAQ page
INSERT INTO faq_banners (page_slug, title, breadcrumb_text, banner_image, faq_title, faq_subtitle) VALUES
('faq', 'FAQ', 'FAQ', 'assets/images/shape/inner-shape.png', 'Frequently Asked Questions', 'Beberapa pertanyaan yang sering disampaikan');

-- Create FAQ items table
CREATE TABLE IF NOT EXISTS faq_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    column_position INT DEFAULT 1, -- 1 for left column, 2 for right column
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default FAQ items
INSERT INTO faq_items (question, answer, display_order, is_active, column_position) VALUES
('Mengapa harus Akademi Merdeka?', 'Karena Akademi Merdeka merupakan plaform digital yang lengkap dan mendetail dalam menyelesaikan persoalan Insan Akademi.', 1, 1, 1),
('Apa saja layanan Akademi Merdeka?', 'Berbagai macam layanan kami sediakan mulai dari pendampingan Jurnal, JAD, SERDOS, TKDA/TKBI, Pengolahan Statistika, dll.', 2, 1, 1),
('Apa keunggulan dari Akademi Merdeka?', 'Setiap Insan Akademisi akan didampingi satu supervisi yang expert dalam bidangnya, sehingga dapat fokus untuk membantu.', 3, 1, 1),
('Bagaimana cara menghubungi Tim Kami?', 'Jika ada kendala dalam penyelesaian dapat menghubungi Whatsaap (087 735 426 107) atau email (info@akademimerdeka.com).', 1, 1, 2),
('Bagaimana proses publikasi yang dilakukan oleh Kami?', 'Naskah yang masuk langsung kami proses, kemudian dilakukan screening, layouting, cek plagiasi.', 2, 1, 2),
('Apa boleh pembayaran dilakukan secara bertahap?', 'Proses pembayaran dapat dilakukan bertahap, alur pembayarannya akan dilakukan setelah MoU diberikan dan disepakati.', 3, 1, 2);