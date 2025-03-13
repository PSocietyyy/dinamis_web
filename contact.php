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
          <h3>Kontak</h3>
          <ul>
            <li><a href="/">Home</a></li>
            <li><i class='bx bx-chevrons-right'></i></li>
            <li>Kontak</li>
          </ul>
        </div>
      </div>
      <div class="inner-shape"><img src="assets/images/shape/inner-shape.png" alt="Images"></div>
    </div>
    <div class="contact-form-area pt-100 pb-70">
      <div class="container">
        <div class="section-title text-center">
          <h2>Ada Pertanyaan? Silahkan lengkapi form dibawah ini</h2>
        </div>
        <div class="row pt-45">
          <div class="col-lg-4">
            <div class="contact-info mr-20"><span>Hubungi Kami</span>
              <h2>Mari bergabung bersama kami</h2>
              <p>Akademi Merdeka Office: </p>
              <ul>
                <li>
                    <div class="content"><i class='bx bxs-map'></i>
                      <h3>Address</h3><span>Perumahan Kheandra Kalijaga<br>
                        Harjamukti, Cirebon, Jawa Barat</span>
                    </div>
                  </li>
                <li>
                  <div class="content"><i class='bx bx-phone-call'></i>
                    <h3>Phone Number</h3><a href="tel:+62877-3542-6107">+62 877-3542-6107</a>
                  </div>
                </li>
                <li>
                  <div class="content"><i class='bx bx-message'></i>
                    <h3>Email</h3><a href="mailto:info@akademimerdeka.com"><span>info@akademimerdeka.com</span></a>
                  </div>
                </li>
              </ul>
            </div>
          </div>
          <div class="col-lg-8">
            <div class="contact-form">
              <form id="contactForm">
                <div class="row">
                  <div class="col-lg-6">
                    <div class="form-group"><label>Nama <span>*</span></label><input type="text" name="name" id="name" class="form-control" required data-error="Mohon lengkapi Nama Anda" placeholder="Nama">
                      <div class="help-block with-errors"></div>
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="form-group"><label>Email <span>*</span></label><input type="email" name="email" id="email" class="form-control" required data-error="Mohon lengkapi Email Anda" placeholder="Email">
                      <div class="help-block with-errors"></div>
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="form-group"><label>Telepon/Whatsapp <span>*</span></label><input type="text" name="phone_number" id="phone_number" required data-error="Mohon lengkapi Nomor Telepon/Whatsapp Anda" class="form-control" placeholder="Telepon/Whatsapp">
                      <div class="help-block with-errors"></div>
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="form-group"><label>Judul Pesan <span>*</span></label><input type="text" name="msg_subject" id="msg_subject" class="form-control" required data-error="Mohon lengkapi Judul Pesan" placeholder="Judul Pesan">
                      <div class="help-block with-errors"></div>
                    </div>
                  </div>
                  <div class="col-lg-12 col-md-12">
                    <div class="form-group"><label>Detail Pesan <span>*</span></label><textarea name="message" class="form-control" id="message" cols="30" rows="8" required data-error="Mohon lengkapi Detail Pesan" placeholder="Detail Pesan"></textarea>
                      <div class="help-block with-errors"></div>
                    </div>
                  </div>
                  <div class="col-lg-12 col-md-12">
                    <div class="agree-label"><input type="checkbox" id="chb1"><label for="chb1"> Saya menyetujui <a href="terms-condition.html">Syarat & Ketentuan</a> dan <a href="privacy-policy.html">Kebijakan Privasi.</a></label></div>
                  </div>
                  <div class="col-lg-12 col-md-12 text-center"><button type="submit" class="default-btn btn-bg-two border-radius-50"> Submit <i class='bx bx-chevron-right'></i></button>
                    <div id="msgSubmit" class="h3 text-center hidden"></div>
                    <div class="clearfix"></div>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="map-area">
      <div class="container-fluid m-0 p-0">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3962.019486213893!2d108.54767291372046!3d-6.767477868057374!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6e7d988b9404fb0f%3A0x5acb2b6afbaeac6f!2sAkademi%20Merdeka!5e0!3m2!1sid!2sid!4v1678334730904!5m2!1sid!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
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