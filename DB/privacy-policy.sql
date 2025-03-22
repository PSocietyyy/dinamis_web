-- Privacy Policy tables
USE akademi_merdeka;

-- Create privacy policy content table
CREATE TABLE IF NOT EXISTS privacy_policy (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create privacy policy page settings table
CREATE TABLE IF NOT EXISTS privacy_policy_settings (
    id INT PRIMARY KEY DEFAULT 1,
    title VARCHAR(255) NOT NULL DEFAULT 'Akademi Merdeka',
    subtitle VARCHAR(255) DEFAULT 'Kebijakan Privasi',
    inner_title VARCHAR(255) DEFAULT 'Kebijakan Privasi',
    breadcrumb_parent VARCHAR(255) DEFAULT 'Tentang',
    breadcrumb_parent_link VARCHAR(255) DEFAULT '/',
    breadcrumb_current VARCHAR(255) DEFAULT 'Kebijakan Privasi',
    banner_image VARCHAR(255) DEFAULT 'assets/images/shape/inner-shape.png',
    seo_title VARCHAR(255) DEFAULT 'Kebijakan Privasi | Akademi Merdeka',
    seo_description TEXT,
    seo_keywords TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default settings
INSERT INTO privacy_policy_settings (
    title, subtitle, inner_title, 
    breadcrumb_parent, breadcrumb_parent_link, breadcrumb_current, 
    banner_image, seo_title
) VALUES (
    'Akademi Merdeka', 'Kebijakan Privasi', 'Kebijakan Privasi', 
    'Tentang', '/', 'Kebijakan Privasi', 
    'assets/images/shape/inner-shape.png', 'Kebijakan Privasi | Akademi Merdeka'
);

-- Insert initial privacy policy sections
INSERT INTO privacy_policy (title, content, display_order, is_active) VALUES
('PENGUMPULAN INFORMASI', 'Pengumpulan data pengguna kami peroleh dari pendaftaran online saat pemesanan layanan. Kami juga dapat mengumpulkan data pengguna dari penggunaan "cookies" pada website pressrelease.co.id untuk melacak, siapa, dari mana dan keyword apa yang digunakan pengguna hingga sampai pada halaman situs web kami.', 1, 1),

('COOKIES', 'Cookies adalah file yang merupakan serangkaian informasi berbentuk teks yang dikirim oleh server web dan disimpan oleh web browser komputer anda ataupun perangkat lainnya ketika Anda mengakses sebuah situs web. Cookies tersebut dikirim kembali ke situs asal setiap kali browser meminta halaman dari server. Cookies ini memungkinkan situs web kami untuk mengenali perangkat pengguna. Kami mungkin menggunakan informasi yang diambil dari cookies untuk meningkatkan pengalaman pengguna situs dan keperluan pemasaran. Kami juga mungkin menggunakan informasi tersebut untuk melakukan personalisasi situs kami untuk anda.', 2, 1),

('PENGGUNAAN DATA', 'Data yang terkumpul digunakan untuk komunikasi antar pengguna yang menggunakan layanan dari Keyword. Data pelanggan tidak akan kami publikasikan/ perjual belikan kepada siapapun.', 3, 1);