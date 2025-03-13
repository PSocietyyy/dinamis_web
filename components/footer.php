<?php
// Include database connection if not already included
if (!isset($conn)) {
    require_once(__DIR__ . '/config.php');
}

// Fetch footer settings
$settings = [];
try {
    $stmt = $conn->query("SELECT setting_key, setting_value FROM footer_settings");
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch(PDOException $e) {
    // Fallback to defaults if there's an error
    error_log("Footer settings error: " . $e->getMessage());
}

// Fetch service links
$serviceLinks = [];
try {
    $stmt = $conn->prepare("SELECT title, url, icon FROM footer_links 
                            WHERE section = 'services' AND is_active = TRUE 
                            ORDER BY display_order ASC");
    $stmt->execute();
    $serviceLinks = $stmt->fetchAll();
} catch(PDOException $e) {
    error_log("Footer links error: " . $e->getMessage());
}

// Default values if not set in database
$companyName = $settings['company_name'] ?? 'Akademi Merdeka';
$companyAddress = $settings['company_address'] ?? 'Akademi Merdeka Office:
Perumahan Kheandra Kalijaga
Harjamukti, Cirebon, Jawa Barat';
$phone = $settings['company_phone'] ?? '+62 877-3542-6107';
$footerLogo = $settings['logo_footer'] ?? 'assets/images/logos/logo-footer.png';
$copyright = $settings['footer_copyright'] ?? 'Copyright © ' . date('Y') . ' <a href="/">Akademi Merdeka</a> as establisment date 2022';
$bulletinTitle = $settings['bulletin_title'] ?? 'Bulletin';
$bulletinText = $settings['bulletin_text'] ?? 'Informasi lain dapat diajukan kepada tim kami untuk ditindaklanjuti.';
?>

<footer class="footer-area footer-bg">
    <div class="container">
        <div class="footer-top pt-100 pb-70">
            <div class="row">
                <!-- Company Info Section -->
                <div class="col-lg-3 col-sm-6">
                    <div class="footer-widget">
                        <div class="footer-logo">
                            <a href="/"><img src="<?php echo htmlspecialchars($footerLogo); ?>" alt="<?php echo htmlspecialchars($companyName); ?> Logo" loading="lazy" width="270"></a>
                        </div>
                        <p>
                            <?php echo nl2br(htmlspecialchars($companyAddress)); ?>
                        </p>
                        <div class="footer-call-content">
                            <h3>Hubungi Kami</h3>
                            <span><a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $phone); ?>"><?php echo htmlspecialchars($phone); ?></a></span>
                            <i class='bx bx-headphone'></i>
                        </div>
                    </div>
                </div>

                <!-- Services Section - Dynamic -->
                <div class="col-lg-3 col-sm-6" style="padding-left: 50px;">
                    <div class="footer-widget pl-2">
                        <h3>Layanan Kami</h3>
                        <ul class="footer-list">
                            <?php if(empty($serviceLinks)): ?>
                                <!-- Default services if none found in database -->
                                <li><a href="services/penerbitan-jurnal"><i class='bx bx-chevron-right'></i> Penerbitan Jurnal </a></li>
                                <li><a href="services/penerbitan-hki"><i class='bx bx-chevron-right'></i> Penerbitan HKI </a></li>
                                <li><a href="services/pengolahan-statistik"><i class='bx bx-chevron-right'></i> Pengolahan Statistik </a></li>
                            <?php else: ?>
                                <?php foreach($serviceLinks as $link): ?>
                                    <li>
                                        <a href="<?php echo htmlspecialchars($link['url']); ?>">
                                            <i class='<?php echo htmlspecialchars($link['icon']); ?>'></i> 
                                            <?php echo htmlspecialchars($link['title']); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <!-- Blog Section - Static as requested -->
                <div class="col-lg-3 col-sm-6">
                    <div class="footer-widget pl-5">
                        <h3>Blog</h3>
                        <ul class="footer-blog">
                            <li>
                                <a href="blog/teknik-pembuatan-jurnal-artikel">
                                    <img src="assets/images/blog/blog-footer-jurnal.png" alt="Images" loading="lazy">
                                </a>
                                <div class="content">
                                    <h3><a href="blog/teknik-pembuatan-jurnal-artikel">Teknik Pembuatan Jurnal/Artikel</a></h3>
                                    <span>11 Jan 2023</span>
                                </div>
                            </li>
                            <li>
                                <a href="blog/langkah-langkah-mendapatkan-hak-cipta">
                                    <img src="assets/images/blog/blog-footer-hki.png" alt="Images" loading="lazy">
                                </a>
                                <div class="content">
                                    <h3><a href="blog/langkah-langkah-mendapatkan-hak-cipta">Langkah Langkah Mendapatkan HKI</a></h3>
                                    <span>08 Jan 2023</span>
                                </div>
                            </li>
                            <li>
                                <a href="blog/tips-konversi-kti-menjadi-buku-referensi-book-chapter">
                                    <img src="assets/images/blog/blog-footer-kti.png" alt="Images" loading="lazy">
                                </a>
                                <div class="content">
                                    <h3><a href="blog/tips-konversi-kti-menjadi-buku-referensi-book-chapter">Tips Konversi KTI Menjadi Buku</a></h3>
                                    <span>06 Jan 2023</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Bulletin Section -->
                <div class="col-lg-3 col-sm-6">
                    <div class="footer-widget">
                        <h3><?php echo htmlspecialchars($bulletinTitle); ?></h3>
                        <p><?php echo htmlspecialchars($bulletinText); ?></p>
                        <div class="newsletter-area">
                            <form class="newsletter-form" data-toggle="validator" method="POST">
                                <input type="email" class="form-control" placeholder="Enter Your Email" name="EMAIL" required autocomplete="off">
                                <button class="subscribe-btn" type="submit"><i class='bx bx-paper-plane'></i></button>
                                <div id="validator-newsletter" class="form-result"></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="copy-right-area">
            <div class="copy-right-text">
                <p><?php echo $copyright; ?></p>
            </div>
        </div>
    </div>
</footer>