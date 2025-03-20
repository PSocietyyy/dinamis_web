<!doctype html>
<html lang="id">
  <?php
  include('components/head.php')
  ?>
  <body>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WC2H98R" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

    <div class="preloader">
      <div class="d-table">
        <div class="d-table-cell">
          <div class="spinner"></div>
        </div>
      </div>
    </div>    
    <?php
    include('components/navbar.php')
    ?>
    <div class="search-overlay">
        <div class="d-table">
          <div class="d-table-cell">
            <div class="search-layer"></div>
            <div class="search-layer"></div>
            <div class="search-layer"></div>
            <div class="search-close"><span class="search-close-line"></span><span class="search-close-line"></span></div>
            <div class="search-form">
              <form><input type="text" class="input-search" placeholder="Search here..."><button type="submit"><i class='bx bx-search'></i></button></form>
            </div>
          </div>
        </div>
      </div>
    <div class="inner-banner">
      <div class="container">
        <div class="inner-title text-center">
          <h3>FAQ</h3>
          <ul>
            <li><a href="/">Tentang</a></li>
            <li><i class='bx bx-chevrons-right'></i></li>
            <li>FAQ</li>
          </ul>
        </div>
      </div>
      <div class="inner-shape"><img src="assets/images/shape/inner-shape.png" alt="Images"></div>
    </div>
    <div class="faq-area pt-100 pb-70">
      <div class="container">
        <div class="section-title text-center">
          <h2>Frequently Asked Questions</h2>
          <p class="margin-auto">Beberapa pertanyaan yang sering disampaikan</p>
        </div>
        <div class="row pt-45">
          <div class="col-lg-6">
            <div class="faq-content">
              <div class="faq-accordion">
                <ul class="accordion">
                  <li class="accordion-item"><a class="accordion-title" href="javascript:void(0)"><i class='bx bx-minus'></i> Mengapa harus Akademi Merdeka? </a>
                    <div class="accordion-content show">
                      <p> Karena Akademi Merdeka merupakan plaform digital yang lengkap dan mendetail dalam menyelesaikan persoalan Insan Akademi. </p>
                    </div>
                  </li>
                  <li class="accordion-item"><a class="accordion-title" href="javascript:void(0)"><i class='bx bx-plus'></i> Apa saja layanan Akademi Merdeka? </a>
                    <div class="accordion-content">
                      <p> Berbagai macam layanan kami sediakan mulai dari pendampingan Jurnal, JAD, SERDOS, TKDA/TKBI, Pengolahan Statistika, dll. </p>
                    </div>
                  </li>
                  <li class="accordion-item"><a class="accordion-title" href="javascript:void(0)"><i class='bx bx-plus'></i> Apa keunggulan dari Akademi Merdeka? </a>
                    <div class="accordion-content">
                      <p> Setiap Insan Akademisi akan didampingi satu supervisi yang expert dalam bidangnya, sehingga dapat fokus untuk membantu. </p>
                    </div>
                  </li>                  
                </ul>
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="faq-content">
              <div class="faq-accordion">
                <ul class="accordion">
                  <li class="accordion-item"><a class="accordion-title" href="javascript:void(0)"><i class='bx bx-plus'></i> Bagaimana cara menghubungi Tim Kami? </a>
                    <div class="accordion-content show">
                      <p> Jika ada kendala dalam penyelesaian dapat menghubungi Whatsaap (087 735 426 107) atau email (info@akademimerdeka.com). </p>
                    </div>
                  </li>
                  <li class="accordion-item"><a class="accordion-title" href="javascript:void(0)"><i class='bx bx-plus'></i> Bagaimana proses publikasi yang dilakukan oleh Kami? </a>
                    <div class="accordion-content">
                      <p> Naskah yang masuk langsung kami proses, kemudian dilakukan screening, layouting, cek plagiasi. </p>
                    </div>
                  </li>
                  <li class="accordion-item"><a class="accordion-title" href="javascript:void(0)"><i class='bx bx-plus'></i> Apa boleh pembayaran dilakukan secara bertahap? </a>
                    <div class="accordion-content">
                      <p> Proses pembayaran dapat dilakukan bertahap, alur pembayarannya akan dilakukan setelah MoU diberikan dan disepakati. </p>
                    </div>
                  </li>                  
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php
    include('components/footer.php')
    ?>  
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/owl.carousel.min.js"></script>
    <script src="assets/js/jquery.magnific-popup.min.js"></script>
    <script src="assets/js/jquery.nice-select.min.js"></script>
    <script src="assets/js/wow.min.js"></script>
    <script src="assets/js/meanmenu.js"></script>
    <script src="assets/js/jquery.ajaxchimp.min.js"></script>
    <script src="assets/js/form-validator.min.js"></script>
    <script src="assets/js/contact-form-script.js"></script>
    <script src="assets/js/custom.js"></script>
  </body>
</html>