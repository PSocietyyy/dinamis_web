-- Terms and conditions tables
USE akademi_merdeka;

-- Create terms and conditions content table
CREATE TABLE IF NOT EXISTS terms_conditions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create terms and conditions page settings table
CREATE TABLE IF NOT EXISTS terms_conditions_settings (
    id INT PRIMARY KEY DEFAULT 1,
    title VARCHAR(255) NOT NULL DEFAULT 'Syarat & Ketentuan',
    subtitle VARCHAR(255) DEFAULT 'Syarat & Ketentuan',
    inner_title VARCHAR(255) DEFAULT 'Syarat & Ketentuan',
    breadcrumb_parent VARCHAR(255) DEFAULT 'Tentang',
    breadcrumb_parent_link VARCHAR(255) DEFAULT '/',
    breadcrumb_current VARCHAR(255) DEFAULT 'Syarat & Ketentuan',
    banner_image VARCHAR(255) DEFAULT 'assets/images/shape/inner-shape.png',
    seo_title VARCHAR(255) DEFAULT 'Syarat & Ketentuan | Akademi Merdeka',
    seo_description TEXT,
    seo_keywords TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default settings
INSERT INTO terms_conditions_settings (
    title, subtitle, inner_title, 
    breadcrumb_parent, breadcrumb_parent_link, breadcrumb_current, 
    banner_image, seo_title
) VALUES (
    'Akademi Merdeka', 'Syarat & Ketentuan', 'Syarat & Ketentuan', 
    'Tentang', '/', 'Syarat & Ketentuan', 
    'assets/images/shape/inner-shape.png', 'Syarat & Ketentuan | Akademi Merdeka'
);

-- Insert initial terms and conditions sections
INSERT INTO terms_conditions (title, content, display_order, is_active) VALUES
('MEKANISME PROSES PENGERJAAN', '•&nbsp; Kirim data sesuai dengan layanan yand dipilih ke alamat email yang tersedia dari kami yaitu info@akademimerdeka.com<br> 
•&nbsp; Lakukan konfirmasi jika pelanggan telah melakukan pengiriman file ke alamat email kami melalui whatsaap pada nomor 0877 3542 6107<br>
•&nbsp; Kami akan memberikan balasan segera setelah pelanggan melakukan konfirmasi pada salah satu dari kedua nomer handphone atau telpon tersebut.<br>
•&nbsp; Jangka waktu pengerjaan dan besarnya biaya akan dikonfirmasikan kepada pelanggan melalui nomor yang masuk pada whatsaap kami.<br>
•&nbsp; Jika pelanggan menyetujui maka kami akan melakukan proses pembuatan selanjutnya.<br>
•&nbsp; Hasil kerjaan akan kami kirimkan setelah transfer via rekening dipastikan masuk baik dengan bukti rekening atau notifikasi dari bank kami.<br>
•&nbsp; Jika pelanggan belum menerima hasil pengerjaan seperti yang kami janjikan, padahal kami sudah melakukan pengiriman hasil melalui email maka kesalahan bukan dari kami, namun pelanggan dapat memindahkan ke alamat email lain yang aktif.<br>
•&nbsp; Jika pelanggan menghendaki hasil pengerjaan dalam bentuk hardcopy maka kami dapat mengirimkan hasil tersebut dalam hardcopy dengan biaya kirim dikenakan pada pelanggan.<br>
•&nbsp; Jika dari pekerjaan kami dilakukan revisi yang disebabkan kesalahan kami maka kami bertanggungjawab sepenuhnya untuk melakukan revisi tanpa ada penambahan biaya<br>
•&nbsp; Jika dari pekerjaan kami perlu dilakukan revisi yang disebabkan kesalahan pelanggan atau revisi atas permintaan pelanggan maka akan dilakukan penambahan biaya yang disesuaikan dengan tingkat besarnya perubahan yang dilakukan.<br>
•&nbsp; Biaya dapat diminta kembali 100% jika terbukti kami tidak mengirimkan hasil pekerjaan.', 1, 1),

('PEMBAYARAN', 'Pembayaran hanya dapat dilakukan di alamat rekening yang ditetapkan oleh team Akademi Merdeka. Setelah kami menerima pembayaran Anda, kami akan mengkonfirmasi pembayaran tersebut maksimal 2 jam setelah pembayaran (pada hari kerja). Setelah kami mengkonfirmasi pembayaran Anda, kami akan mengirim pesanan Anda maksimal 1 jam setelah status confirmed.', 2, 1),

('PELAYANAN PELANGGAN', 'Akademi Merdeka hanya akan melayani pertanyaan dari pelanggan seputar masalah teknis menggunakan jasa layanan kami dan pengiriman file.', 3, 1),

('KETENTUAN PEMBAYARAN', 'Pembayaran dilakukan oleh pelanggan segera kami telah melakukan proses pekerjaan. Jika dalam jangka waktu maksimal 1 × 24 jam setelah pemberitahuan olah data siap dan input data siap kirim tidak dilakukan transfer maka maka transaksi dianggap batal. Kecuali jika pelanggan bermaksud melakukan datang langsung ke alamat kantor kami atau melakukan pembayaran secara offline. Jika pembayaran dilakukan melalui Transfer Bank, Anda harus mengkonfirmasi pembayaran melalui Whatsaap.', 4, 1),

('METODE PEMBAYARAN', 'Untuk mempermudah transaksi Anda, Untuk pembayaran kami merekomendasikan pembayaran melalui Transfer Bank. Silakan melakukan transfer sesuai rincian pesanan ke No Rekening BSI: <b>72 1992 0991 a/n. Akademi Merdeka</b>.', 5, 1),

('PEMBAYARAN CASH LANGSUNG', 'Pelanggan juga bisa melakukan pembayaran cash dengan datang ke alamat kantor Akademi Merdeka ke Alamat: Perumahan Kheandra Kalijaga Harjamukti, Cirebon, Jawa Barat.', 6, 1);