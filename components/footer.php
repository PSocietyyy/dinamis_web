<?php
// Get footer data
$footer_info = getFooterInfo($conn);
$footer_services = getFooterServices($conn);
$footer_blog = getFooterBlog($conn);

// Helper function to find item by key
function findFooterItem($items, $key) {
    foreach ($items as $item) {
        if ($item['section_key'] === $key) {
            return $item;
        }
    }
    return null;
}

// Get specific footer items
$footer_logo = findFooterItem($footer_info, 'footer_logo');
$footer_address = findFooterItem($footer_info, 'footer_address');
$footer_phone = findFooterItem($footer_info, 'footer_phone');
$copyright = findFooterItem($footer_info, 'copyright');
?>
<footer class="footer-area footer-bg">
  <div class="container">
    <div class="footer-top pt-100 pb-70">
      <div class="row">
        <div class="col-lg-3 col-sm-6">
          <div class="footer-widget">
            <div class="footer-logo">
                <a href="/">
                    <img src="<?php echo htmlspecialchars($footer_logo['image_url'] ?? 'assets/images/logos/logo-footer.png'); ?>" alt="Logo Footer" loading="lazy" width="270">
                </a>
            </div>
            <p><?php echo $footer_address['content_value'] ?? 'Akademi Merdeka Office:<br>Perumahan Kheandra Kalijaga<br>Harjamukti, Cirebon, Jawa Barat'; ?></p>
            <div class="footer-call-content">
              <h3>Hubungi Kami</h3>
              <span><a href="<?php echo htmlspecialchars($footer_phone['link_url'] ?? 'tel:+62877-3542-6107'); ?>">
                <?php echo htmlspecialchars($footer_phone['content_value'] ?? '+62 877-3542-6107'); ?>
              </a></span>
              <i class='bx bx-headphone'></i>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-sm-6" style="padding-left: 50px;">
          <div class="footer-widget pl-2">
            <h3>Layanan Kami</h3>
            <ul class="footer-list">
              <?php foreach ($footer_services as $service): ?>
                <?php if ($service['is_active']): ?>
                  <li>
                    <a href="<?php echo htmlspecialchars($service['link_url']); ?>">
                      <i class='bx bx-chevron-right'></i> <?php echo htmlspecialchars($service['content_value']); ?>
                    </a>
                  </li>
                <?php endif; ?>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
        <div class="col-lg-3 col-sm-6">
          <div class="footer-widget pl-5">
            <h3>Blog</h3>
            <ul class="footer-blog">
              <?php foreach ($footer_blog as $blog): ?>
                <li>
                  <a href="<?php echo htmlspecialchars($blog['link_url']); ?>">
                    <img src="<?php echo htmlspecialchars($blog['image_url']); ?>" alt="<?php echo htmlspecialchars($blog['section_key']); ?>" loading="lazy">
                  </a>
                  <div class="content">
                    <h3><a href="<?php echo htmlspecialchars($blog['link_url']); ?>"><?php echo htmlspecialchars($blog['section_key']); ?></a></h3>
                    <span><?php echo htmlspecialchars($blog['content_value']); ?></span>
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
          </div>
        </div>
      </div>
    </div>
    <div class="copy-right-area">
      <div class="copy-right-text">
        <p><?php echo $copyright['content_value'] ?? 'Copyright © ' . date('Y') . ' <a href="https://akademimerdeka.com/">Akademi Merdeka</a> as establisment date 2022'; ?></p>
      </div>
    </div>
  </div>
</footer>