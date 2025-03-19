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

