-- Testimonial tables
USE akademi_merdeka;

-- Create testimonial items table
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

-- Insert initial testimonial items
INSERT INTO testimonial_items (client_name, client_position, testimonial_text, image_path, display_order, is_active) VALUES
('Bayu Saputra', 'Mahasiswa', '"Adanya tim Akademi Merdeka membantu saya dalam penerbitan jurnal dengan metode yang efektif, membuat saya cepat memahami."', 'assets/images/clients-img/testi-4.jpg', 1, 1),
('Aryo Supratman', 'Dosen', '"Akademi Merdeka tidak hanya sekedar membantu dalam kenaikan Jabatan Fungsional, namun sebagai penasehat dan pendengar yang baik. Tim sangat responsif dan tanggap jika ada persoalan."', 'assets/images/clients-img/testi-3.jpg', 2, 1),
('Syadid', 'Mahasiswa', '"Tim Akademi Merdeka membantu pembuatan media ajar mulai dari penyusunan indikator dan memberikan inovasi yang sangat baik."', 'assets/images/clients-img/testi-6.jpg', 3, 1),
('Alya Afifah', 'Mahasiswa', '"Desain yang diberikan oleh tim Akademi Merdeka sangat kekinian, sehingga buku yang diterbitkan semakin menarik perhatian pembaca."', 'assets/images/clients-img/testi-1.jpg', 4, 1),
('Arini Sulistiawati', 'Mahasiswa', '"Pelayanan Pembuatan HKI sangat cepat. Tim hanya memerlukan 20 menit saja untuk mengirimkan sertifikat HKI kepada saya."', 'assets/images/clients-img/testi-2.jpg', 5, 1);

-- Testimonial page settings
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

-- Insert default settings
INSERT INTO testimonial_page_settings (
    title, subtitle, inner_title, 
    breadcrumb_parent, breadcrumb_parent_link, breadcrumb_current, 
    banner_image, seo_title
) VALUES (
    'Apa Kata Mereka?', 'Testimoni Customer', 'Testimoni', 
    'Tentang', '/', 'Testimoni', 
    'assets/images/shape/inner-shape.png', 'Testimoni | Akademi Merdeka'
);
