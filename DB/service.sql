CREATE TABLE service_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    categories_name VARCHAR(50) NOT NULL
);

CREATE TABLE service_features (
    id INT PRIMARY KEY AUTO_INCREMENT,
    feature_name VARCHAR(100) NOT NULL,
    feature_category_id INT,
    feature_image_path TEXT NOT NULL,
    FOREIGN KEY (feature_category_id) REFERENCES service_categories(id) ON DELETE SET NULL
);

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

ALTER TABLE service_articles 
ADD CONSTRAINT unique_feature UNIQUE (feature_id);


-- Insert data ke service_categories
INSERT INTO service_categories (categories_name) 
VALUES  
    ('Pendampingan'),
    ('Penerbitan'),
    ('Pengolahan'),
    ('Pembuatan'),
    ('Konversi');

-- Insert data ke service_features
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

-- Insert data ke service_articles
INSERT INTO service_articles (slug, feature_id, title, content, image_path) 
VALUES (
    'hki_paten_merk', 
    NULL, -- Feature_id diperbolehkan NULL
    'HKI/Paten/Merk', 
    '<h2 class="ql-align-justify"><strong>HKI/Paten/Merk</strong></h2>
    <p class="ql-align-justify">Pendaftaran Merek HKI/Paten adalah bagian yang memang perlu dilakukan oleh setiap akademisi untuk menambah karya yang original dan melakukan tridarama perguruan tinggi. Jasa ini merupakan Konsultan HKI dan penerbitan HKI/Paten/Merk terdaftar yang terpercaya.</p>
    
    <h2 class="ql-align-justify"><strong>Persyaratan Wajib</strong></h2>
    <p class="ql-align-justify">Pemilik Hak Cipta:</p>
    <ul class="service-article-list service-article-rs">
        <li><i class="bx bxs-check-circle"></i> Kartu Tanda Penduduk (KTP)</li>
        <li><i class="bx bxs-check-circle"></i> Nomor Pokok Wajib Pajak (NPWP)</li>
        <li><i class="bx bxs-check-circle"></i> Surat Pernyataan Ciptaan</li>
    </ul>

    <p class="ql-align-justify">Pengalihan Hak Cipta:</p>
    <ul class="service-article-list service-article-rs">
        <li><i class="bx bxs-check-circle"></i> Surat Pernyataan Pengalihan Hak Cipta</li>
        <li><i class="bx bxs-check-circle"></i> Kartu Tanda Penduduk (KTP) Pencipta</li>
        <li><i class="bx bxs-check-circle"></i> Nomor Pokok Wajib Pajak (NPWP) Pencipta</li>
        <li><i class="bx bxs-check-circle"></i> Kartu Tanda Penduduk (KTP) Pemilik Hak Cipta</li>
        <li><i class="bx bxs-check-circle"></i> Nomor Pokok Wajib Pajak (NPWP) Pemilik Hak Cipta</li>
    </ul>',
    'assets/images/uploads/articles/article_67da9d7c66087.jpg'
);
