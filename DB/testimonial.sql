-- Testimonials page database structure
USE akademi_merdeka;

-- Table for testimonials page settings
CREATE TABLE IF NOT EXISTS testimonial_page_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_title VARCHAR(255) NOT NULL DEFAULT 'Testimoni',
    meta_description TEXT,
    breadcrumb_parent VARCHAR(100) DEFAULT 'Tentang',
    breadcrumb_current VARCHAR(100) DEFAULT 'Testimoni',
    section_title VARCHAR(255) DEFAULT 'Testimoni Customer',
    section_subtitle VARCHAR(255) DEFAULT 'Apa Kata Mereka?',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table for testimonials
CREATE TABLE IF NOT EXISTS testimonials (
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

-- Insert default page settings
INSERT INTO testimonial_page_settings (
    id, page_title, meta_description, 
    breadcrumb_parent, breadcrumb_current, 
    section_title, section_subtitle
) x
VALUES (
    1, 'Testimoni', 'Testimoni dari pelanggan Akademi Merdeka',
    'Tentang', 'Testimoni',
    'Testimoni Customer', 'Apa Kata Mereka?'
)
ON DUPLICATE KEY UPDATE id = 1;

-- Import testimonials from home_testimonials table if exists
-- This will be executed by the admin page when needed, but here's the syntax for reference:
/*
INSERT INTO testimonials (client_name, client_position, testimonial_text, client_image, is_active, display_order)
SELECT client_name, client_position, testimonial_text, client_image, is_active, display_order
FROM home_testimonials
*/

-- Default testimonials in case home_testimonials doesn't exist
INSERT INTO testimonials (client_name, client_position, testimonial_text, client_image, is_active, display_order) VALUES
('Bayu Saputra', 'Mahasiswa', '"Adanya tim Akademi Merdeka membantu saya dalam penerbitan jurnal dengan metode yang efektif, membuat saya cepat memahami."', 'assets/images/clients-img/testi-4.jpg', 1, 1),
('Aryo Supratman', 'Dosen', '"Akademi Merdeka tidak hanya sekedar membantu dalam kenaikan Jabatan Fungsional, namun sebagai penasehat dan pendengar yang baik. Tim sangat responsif dan tanggap jika ada persoalan."', 'assets/images/clients-img/testi-3.jpg', 1, 2),
('Syadid', 'Mahasiswa', '"Tim Akademi Merdeka membantu pembuatan media ajar mulai dari penyusunan indikator dan memberikan inovasi yang sangat baik."', 'assets/images/clients-img/testi-6.jpg', 1, 3),
('Alya Afifah', 'Mahasiswa', '"Desain yang diberikan oleh tim Akademi Merdeka sangat kekinian, sehingga buku yang diterbitkan semakin menarik perhatian pembaca."', 'assets/images/clients-img/testi-1.jpg', 1, 4),
('Arini Sulistiawati', 'Mahasiswa', '"Pelayanan Pembuatan HKI sangat cepat. Tim hanya memerlukan 20 menit saja untuk mengirimkan sertifikat HKI kepada saya."', 'assets/images/clients-img/testi-2.jpg', 1, 5);

-- Create the directory structure if not exists
-- Note: This is handled by the PHP code, but mentioned here for documentation
-- assets/images/uploads/testimonial/