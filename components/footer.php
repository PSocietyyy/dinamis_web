<?php
// Include database connection if not already included
if (!isset($conn)) {
    require_once './config.php';
}

// Get footer settings from database
$footer_settings = [
    'footer_logo' => 'assets/images/logos/logo-footer.png',
    'footer_company_name' => 'Akademi Merdeka',
    'footer_company_address' => 'Perumahan Kheandra Kalijaga<br>Harjamukti, Cirebon, Jawa Barat',
    'footer_company_phone' => '+62 877-3542-6107',
    'footer_company_email' => 'info@akademimerdeka.com',
    'footer_copyright_text' => 'Copyright © 2023 <a href="https://akademimerdeka.com/">Akademi Merdeka</a> as establisment date 2022',
    'footer_bg_color' => '#343a40',
    'footer_text_color' => '#ffffff',
    'footer_whatsapp_link' => 'https://wa.me/6287735426107'
];

try {
    $stmt = $conn->query("SELECT setting_key, setting_value FROM site_settings WHERE setting_group = 'footer'");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Update settings array with database values
    foreach($settings as $key => $value) {
        $footer_settings[$key] = $value;
    }
} catch(PDOException $e) {
    // If error, use default settings
}

// Get footer links from database
$footer_links = [
    'layanan' => [],
    'informasi' => [],
    'support' => []
];

try {
    $stmt = $conn->query("SELECT * FROM footer_links WHERE is_active = 1 ORDER BY section, position");
    $links = $stmt->fetchAll();
    
    // Organize links by section
    foreach($links as $link) {
        if(!isset($footer_links[$link['section']])) {
            $footer_links[$link['section']] = [];
        }
        $footer_links[$link['section']][] = $link;
    }
} catch(PDOException $e) {
    // If error, use empty array
}

// Get blog posts for footer display
$footer_blogs = [];
try {
    // This query assumes you have a posts or blogs table
    // Modify as needed based on your actual blog database structure
    $stmt = $conn->query("SELECT title, url, image, DATE_FORMAT(published_at, '%d %b %Y') as date 
                        FROM posts WHERE is_published = 1 
                        ORDER BY published_at DESC LIMIT 3");
    $footer_blogs = $stmt->fetchAll();
} catch(PDOException $e) {
    // If error or table doesn't exist, use default blog data
    $footer_blogs = [
        [
            'title' => 'Teknik Pembuatan Jurnal/Artikel',
            'url' => 'blog/teknik-pembuatan-jurnal-artikel',
            'image' => 'assets/images/blog/blog-footer-jurnal.png',
            'date' => '11 Jan 2023'
        ],
        [
            'title' => 'Langkah Langkah Mendapatkan HKI',
            'url' => 'blog/langkah-langkah-mendapatkan-hak-cipta',
            'image' => 'assets/images/blog/blog-footer-hki.png',
            'date' => '08 Jan 2023'
        ],
        [
            'title' => 'Tips Konversi KTI Menjadi Buku',
            'url' => 'blog/tips-konversi-kti-menjadi-buku-referensi-book-chapter',
            'image' => 'assets/images/blog/blog-footer-kti.png',
            'date' => '06 Jan 2023'
        ]
    ];
}

// Custom CSS for footer colors
$footer_css = "
<style>
    .footer-area.footer-bg {
        background-color: " . $footer_settings['footer_bg_color'] . ";
        color: " . $footer_settings['footer_text_color'] . ";
    }
    .footer-widget h3 {
        color: " . $footer_settings['footer_text_color'] . ";
    }
    .footer-list a {
        color: " . $footer_settings['footer_text_color'] . ";
        opacity: 0.8;
    }
    .footer-list a:hover {
        opacity: 1;
    }
    .copy-right-text p, .copy-right-text a {
        color: " . $footer_settings['footer_text_color'] . ";
    }
</style>
";

// Output custom CSS
echo $footer_css;
?>

<footer class="footer-area footer-bg">
    <div class="container">
        <div class="footer-top pt-100 pb-70">
            <div class="row">
                <div class="col-lg-3 col-sm-6">
                    <div class="footer-widget">
                        <div class="footer-logo">
                            <a href="/">
                                <img src="<?php echo $footer_settings['footer_logo']; ?>" alt="<?php echo htmlspecialchars($footer_settings['footer_company_name']); ?>" loading="lazy" width="270">
                            </a>
                        </div>
                        <p><?php echo $footer_settings['footer_company_address']; ?></p>
                        <div class="footer-call-content">
                            <h3>Hubungi Kami</h3>
                            <span>
                                <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $footer_settings['footer_company_phone']); ?>">
                                    <?php echo htmlspecialchars($footer_settings['footer_company_phone']); ?>
                                </a>
                            </span>
                            <i class='bx bx-headphone'></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-sm-6" style="padding-left: 50px;">
                    <div class="footer-widget pl-2">
                        <h3>Layanan Kami</h3>
                        <ul class="footer-list">
                            <?php if(!empty($footer_links['layanan'])): ?>
                                <?php foreach($footer_links['layanan'] as $link): ?>
                                <li>
                                    <a href="<?php echo htmlspecialchars($link['url']); ?>">
                                        <i class='bx bx-chevron-right'></i> <?php echo htmlspecialchars($link['title']); ?>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <!-- Default links if none in database -->
                                <li><a href="services/penerbitan-jurnal"><i class='bx bx-chevron-right'></i> Penerbitan Jurnal </a></li>
                                <li><a href="services/penerbitan-hki"><i class='bx bx-chevron-right'></i> Penerbitan HKI </a></li>
                                <li><a href="services/pengolahan-statistik"><i class='bx bx-chevron-right'></i> Pengolahan Statistik </a></li>
                                <li><a href="services/pendampingan-ojs"><i class='bx bx-chevron-right'></i> Pendampingan OJS </a></li>
                                <li><a href="services/pendampingan-tkda"><i class='bx bx-chevron-right'></i> Pendampingan TKDA/TKBI </a></li>
                                <li><a href="services/konversi-kti"><i class='bx bx-chevron-right'></i> Konversi KTI </a></li>
                                <li><a href="services/media-ajar"><i class='bx bx-chevron-right'></i> Pembuatan Media Ajar </a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
                
                <div class="col-lg-3 col-sm-6">
                    <div class="footer-widget pl-5">
                        <h3>Blog</h3>
                        <ul class="footer-blog">
                            <?php foreach($footer_blogs as $blog): ?>
                            <li>
                                <a href="<?php echo htmlspecialchars($blog['url']); ?>">
                                    <img src="<?php echo htmlspecialchars($blog['image']); ?>" alt="<?php echo htmlspecialchars($blog['title']); ?>" loading="lazy">
                                </a>
                                <div class="content">
                                    <h3>
                                        <a href="<?php echo htmlspecialchars($blog['url']); ?>">
                                            <?php echo htmlspecialchars($blog['title']); ?>
                                        </a>
                                    </h3>
                                    <span><?php echo htmlspecialchars($blog['date']); ?></span>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                
                <div class="col-lg-3 col-sm-6">
                    <div class="footer-widget">
                        <h3>Bulletin</h3>
                        <p>Informasi lain dapat diajukan kepada tim kami untuk ditindaklanjuti.</p>
                        <div class="newsletter-area">
                            <form class="newsletter-form" data-toggle="validator" method="POST">
                                <input type="email" class="form-control" placeholder="Enter Your Email" name="EMAIL" required autocomplete="off">
                                <button class="subscribe-btn" type="submit"><i class='bx bx-paper-plane'></i></button>
                                <div id="validator-newsletter" class="form-result"></div>
                            </form>
                        </div>
                        
                        <?php if(!empty($footer_settings['footer_whatsapp_link'])): ?>
                        <div class="mt-4">
                            <a href="<?php echo htmlspecialchars($footer_settings['footer_whatsapp_link']); ?>" class="btn btn-light btn-sm">
                                <i class='bx bxl-whatsapp'></i> WhatsApp Kami
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="copy-right-area">
            <div class="copy-right-text">
                <p><?php echo $footer_settings['footer_copyright_text']; ?></p>
            </div>
        </div>
    </div>
</footer>