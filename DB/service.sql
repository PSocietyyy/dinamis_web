CREATE TABLE service_categories(
    id INT PRIMARY KEY AUTO_INCREMENT,
    categories_name VARCHAR(50)
);

CREATE TABLE service_features(
    id INT PRIMARY KEY AUTO_INCREMENT,
    feature_name VARCHAR(100) NOT NULL,
    feature_category_id INT,
    feature_path VARCHAR(100) NOT NULL,
    feature_image_path TEXT NOT NULL,
    FOREIGN KEY (feature_category_id) REFERENCES service_categories(id)
);

INSERT INTO service_categories(categories_name) 
VALUES  ("Pendampingan"),
        ("Penerbitan"),
        ("Pengolahan"),
        ("Pembuatan"),
        ("Konversi");

-- Query untuk mengisi tabel service_features berdasarkan HTML
INSERT INTO service_features(feature_name, feature_category_id, feature_path, feature_image_path) 
VALUES 
-- Pendampingan (kategori_id = 1)
("Penerbitan Jurnal", 1, "services/penerbitan-jurnal", "assets/images/uploads/services/services-jurnal.jpg"),
("TKDA/TKBI", 1, "services/pendampingan-tkda", "assets/images/uploads/services/services-tkda.jpg"),
("OJS", 1, "services/pendampingan-ojs", "assets/images/uploads/services/services-ojs.jpg"),

-- Penerbitan (kategori_id = 2)
("HKI, Paten, Merk", 2, "services/penerbitan-hki", "assets/images/uploads/services/services-hki.jpg"),

-- Pengolahan (kategori_id = 3)
("Statistik", 3, "services/pengolahan-statistik", "assets/images/uploads/services/services-statistik.jpg"),

-- Pembuatan (kategori_id = 4)
("Media Ajar", 4, "services/media-ajar", "assets/images/uploads/services/services-mediaajar.jpg"),
("E-Learning", 4, "services/elearning", "assets/images/uploads/services/services-elearning.jpg"),

-- Konversi (kategori_id = 5)
("KTI", 5, "services/konversi-kti", "assets/images/uploads/services/services-kti.jpg");

CREATE TABLE service_articles (
  id INT PRIMARY KEY AUTO_INCREMENT,
  slug INT,
  title VARCHAR(255) NOT NULL,
  content TEXT NOT NULL,  -- menyimpan HTML hasil konversi Quill
  image_path VARCHAR(255) DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO service_articles(slug, title, content, image_path) VALUES
("hki_paten_merk", "HKI/Paten/Merk". '<h2 class="ql-align-justify"><strong>HKI/Paten/Merk</strong></h2><p class="ql-align-justify">Pendaftaran Merek HKI/Paten adalah bagian yang memang perlu dilakukan oleh setiap akademisi untuk menambah karya yang original dan melakukan tridarama perguruan tinggi, atau bagi praktisi atau UMKM sekalipun legalitas originalitas itu sangat penting untuk membuat karya yang dipatenkan. Jasa ini merupakan Konsultan HKI dan penerbitan HKI/Paten/Merk terdaftar yang terpercaya dengan biaya murah dan syarat yang mudah Untuk UMKM dan Perusahaan.</p><p class="ql-align-justify"><br></p><p class="ql-align-justify">Banyak kategori yang dapat di HKI/Paten/Merk, kami akan memberikan solusi jika Anda masih bingung dengan istilah yang masih belum dipahami, dan kita akan siapkan semua administrasi yang diharuskan dalam pendaftar HKI.</p><p class="ql-align-justify"><br></p><h2 class="ql-align-justify"><strong>Persyaratan Wajib</strong></h2><p class="ql-align-justify">Pemilik Hak Cipta:</p><ul class="service-article-list service-article-rs"><li><i class="bx bxs-check-circle"></i>Kartu Tanda Penduduk (KTP)</li><li><i class="bx bxs-check-circle"></i>Nomor Pokok Wajib Pajak (NPWP)</li><li><i class="bx bxs-check-circle"></i>Surat Pernyataan Ciptaan</li></ul><p><br></p><p class="ql-align-justify">Pengalihan Hak Cipta:</p><ul class="service-article-list service-article-rs"><li><i class="bx bxs-check-circle"></i>Surat Pernyataan Pengalihan Hak Cipta</li><li><i class="bx bxs-check-circle"></i>Kartu Tanda Penduduk (KTP) Pencipta</li><li><i class="bx bxs-check-circle"></i>Nomor Pokok Wajib Pajak (NPWP) Pencipta</li><li><i class="bx bxs-check-circle"></i>Kartu Tanda Penduduk (KTP) Pemilik Hak Cipta</li><li><i class="bx bxs-check-circle"></i>Nomor Pokok Wajib Pajak (NPWP) Pemilik Hak Cipta</li></ul><p><br></p>', "assets/images/uploads/articles/article_67da9d7c66087.jpg");