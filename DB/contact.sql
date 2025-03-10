-- Create contact page settings table
CREATE TABLE IF NOT EXISTS contact_page_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default contact page settings
INSERT INTO contact_page_settings (setting_key, setting_value) VALUES
    ('page_title', 'Kontak'),
    ('form_title', 'Ada Pertanyaan? Silahkan lengkapi form dibawah ini'),
    ('contact_section_title', 'Hubungi Kami'),
    ('contact_section_subtitle', 'Mari bergabung bersama kami'),
    ('company_name', 'Akademi Merdeka Office:'),
    ('address', 'Perumahan Kheandra Kalijaga<br>Harjamukti, Cirebon, Jawa Barat'),
    ('phone', '+62 877-3542-6107'),
    ('email', 'info@akademimerdeka.com'),
    ('map_embed_url', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3962.019486213893!2d108.54767291372046!3d-6.767477868057374!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6e7d988b9404fb0f%3A0x5acb2b6afbaeac6f!2sAkademi%20Merdeka!5e0!3m2!1sid!2sid!4v1678334730904!5m2!1sid!2sid');

-- Create contact form fields table
CREATE TABLE IF NOT EXISTS contact_form_fields (
    id INT AUTO_INCREMENT PRIMARY KEY,
    field_name VARCHAR(50) NOT NULL,
    field_label VARCHAR(100) NOT NULL,
    field_type ENUM('text', 'email', 'tel', 'textarea', 'checkbox') NOT NULL,
    placeholder TEXT,
    is_required BOOLEAN NOT NULL DEFAULT TRUE,
    position INT NOT NULL DEFAULT 0,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default form fields
INSERT INTO contact_form_fields (field_name, field_label, field_type, placeholder, is_required, position, is_active) VALUES
    ('name', 'Nama', 'text', 'Nama', 1, 1, 1),
    ('email', 'Email', 'email', 'Email', 1, 2, 1),
    ('phone_number', 'Telepon/Whatsapp', 'tel', 'Telepon/Whatsapp', 1, 3, 1),
    ('msg_subject', 'Judul Pesan', 'text', 'Judul Pesan', 1, 4, 1),
    ('message', 'Detail Pesan', 'textarea', 'Detail Pesan', 1, 5, 1),
    ('agreement', 'Saya menyetujui <a href="terms-condition.html">Syarat & Ketentuan</a> dan <a href="privacy-policy.html">Kebijakan Privasi.</a>', 'checkbox', '', 0, 6, 1);

-- Create contact submissions table
CREATE TABLE IF NOT EXISTS contact_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone_number VARCHAR(50),
    subject VARCHAR(255),
    message TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    status ENUM('new', 'read', 'replied', 'archived') DEFAULT 'new',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);