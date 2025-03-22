<!doctype html>
<html lang="id">
  <?php
  require_once('./config.php'); 
  include('components/head.php');

  // Read 
  $query = "
  SELECT 
    f.id AS feature_id,
    c.id AS category_id,
    c.categories_name AS category_name,
    f.feature_name,
    f.feature_image_path,
    a.slug
  FROM 
      service_features f
  INNER JOIN 
      service_categories c ON c.id = f.feature_category_id
  LEFT JOIN 
      service_articles a ON a.feature_id = f.id 
  ORDER BY 
      c.categories_name ASC,
      f.feature_name ASC;
  ";
  $stmt = $conn->prepare($query);

  try {
    $stmt->execute();
    $results = $stmt->fetchAll();
    // var_dump($result);
  } catch (PDOException $e) {
    echo "Error". $e->getMessage();
  }
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
          <h3>Layanan</h3>
          <ul>
            <li><a href="/">Home</a></li>
            <li><i class='bx bx-chevrons-right'></i></li>
            <li>Layanan Kami</li>
          </ul>
        </div>
      </div>
      <div class="inner-shape"><img src="assets/images/shape/inner-shape.png" alt="Images"></div>
    </div>
    <section class="services-widget-area pt-100 pb-70">
      <div class="container">
        <div class="section-title text-center"><span class="sp-color2">Layanan</span>
          <h2>Layanan Kami</h2>
        </div>
        <div class="row pt-45">
          <!--
          <div class="col-lg-4 col-md-6">
            <div class="services-item"><a href="services/karya-ilmiah"><img src="assets/images/services/services-karya-ilmiah.jpg" alt="Images" loading="lazy"></a>
              <div class="content"><i><img src="assets/images/services/ico-karyailmiah-p.png" width="50" height="50" style="margin-top:-10px;"></i><span><a href="services/karya-ilmiah" style="color: #330065;">Pendampingan</a></span>
                <h3><a href="services/karya-ilmiah">Karya Ilmiah</a></h3>
              </div>
            </div>
          </div>
        -->
          <?php
          foreach($results as $result){ ?>
          <div class="col-lg-4 col-md-6">
            <div class="services-item"><a href="?s=<?= $result['slug'] ?? "#" ?>"><img src="<?= $result['feature_image_path']?>" alt="Images" loading="lazy"></a>
              <div class="content"><i><img src="<?= $result['feature_image_path'] ?>" width="50" height="50" style="margin-top:-10px;"></i><span><a href="?s=<?= $result['slug'] ?? "#" ?>" style="color: #330065;"><?= $result['category_name'] ?></a></span>
                <h3><a href="?s=<?= $result['slug'] ?? "#" ?>"><?= $result['feature_name'] ?></a></h3>
              </div>
            </div>
          </div>
          <?php  
          }
          ?>
          
        </div>
      </div>
    </section>
    <section class="clients-area pt-100 pb-70">
      <div class="container">
        <div class="section-title text-center"><span class="sp-color1">Testimonial</span>
          <h2>Kita telah dipercaya di beberapa perusahaan dan instansi</h2>
        </div>
        <div class="clients-slider owl-carousel owl-theme pt-45">
          <div class="clients-content">
            <div class="content"><img src="assets/images/clients-img/testi-4.jpg" alt="Images" loading="lazy"><i class='bx bxs-quote-alt-left'></i>
              <h3>Bayu Saputra</h3><span>Mahasiswa</span>
            </div>
            <p> “Adanya tim Akademi Merdeka membantu saya dalam penerbitan jurnal dengan metode yang efektif, membuat saya cepat memahami.”<br><br></p>
          </div>
          <div class="clients-content">
            <div class="content"><img src="assets/images/clients-img/testi-3.jpg" alt="Images" loading="lazy"><i class='bx bxs-quote-alt-left'></i>
              <h3>Aryo Supratman</h3><span>Dosen</span>
            </div>
            <p> “Akademi Merdeka tidak hanya sekedar membantu dalam kenaikan Jabatan Fungsional, namun sebagai penasehat dan pendengar yang baik. Tim sangat responsif dan tanggap jika ada persoalan.” </p>
          </div>
          <div class="clients-content">
            <div class="content"><img src="assets/images/clients-img/testi-6.jpg" alt="Images" loading="lazy"><i class='bx bxs-quote-alt-left'></i>
              <h3>Syadid</h3><span>Mahasiswa</span>
            </div>
            <p> “Tim Akademi Merdeka membantu pembuatan media ajar mulai dari penyusunan indikator dan memberikan inovasi yang sangat baik.”<br><br></p>
          </div>
          <div class="clients-content">
            <div class="content"><img src="assets/images/clients-img/testi-1.jpg" alt="Images" loading="lazy"><i class='bx bxs-quote-alt-left'></i>
              <h3>Alya Afifah</h3><span>Mahasiswa</span>
            </div>
            <p> “Desain yang diberikan oleh tim Akademi Merdeka sangat kekinian, sehingga buku yang diterbitkan semakin menarik perhatian pembaca.” <br><br></p>
          </div>
          <div class="clients-content">
            <div class="content"><img src="assets/images/clients-img/testi-2.jpg" alt="Images" loading="lazy"><i class='bx bxs-quote-alt-left'></i>
              <h3>Arini Sulistiawati</h3><span>Mahasiswa</span>
            </div>
            <p> “Pelayanan Pembuatan HKI sangat cepat. Tim hanya memerlukan 20 menit saja untuk mengirimkan sertifikat HKI kepada saya.”<br><br></p>
          </div>
        </div>
      </div>
      <div class="client-circle">
        <div class="client-circle-1">
          <div class="circle"></div>
        </div>
        <div class="client-circle-2">
          <div class="circle"></div>
        </div>
        <div class="client-circle-3">
          <div class="circle"></div>
        </div>
        <div class="client-circle-4">
          <div class="circle"></div>
        </div>
        <div class="client-circle-5">
          <div class="circle"></div>
        </div>
        <div class="client-circle-6">
          <div class="circle"></div>
        </div>
        <div class="client-circle-7">
          <div class="circle"></div>
        </div>
      </div>
    </section>
    <!--<div class="brand-area ptb-100">-->
    <!--  <div class="container">-->
    <!--    <div class="brand-slider owl-carousel owl-theme">-->
    <!--      <div class="brand-item"><img src="assets/images/brand-logo/sp-1-p.png" class="brand-logo-one" alt="Images" loading="lazy"><img src="assets/images/brand-logo/sp-1.png" class="brand-logo-two" alt="Images" loading="lazy"></div>-->
    <!--      <div class="brand-item"><img src="assets/images/brand-logo/sp-2-p.png" class="brand-logo-one" alt="Images" loading="lazy"><img src="assets/images/brand-logo/sp-2.png" class="brand-logo-two" alt="Images" loading="lazy"></div>-->
    <!--      <div class="brand-item"><img src="assets/images/brand-logo/sp-3-p.png" class="brand-logo-one" alt="Images" loading="lazy"><img src="assets/images/brand-logo/sp-3.png" class="brand-logo-two" alt="Images" loading="lazy"></div>-->
    <!--      <div class="brand-item"><img src="assets/images/brand-logo/sp-4-p.png" class="brand-logo-one" alt="Images" loading="lazy"><img src="assets/images/brand-logo/sp-4.png" class="brand-logo-two" alt="Images" loading="lazy"></div>-->
    <!--      <div class="brand-item"><img src="assets/images/brand-logo/sp-5-p.png" class="brand-logo-one" alt="Images" loading="lazy"><img src="assets/images/brand-logo/sp-5.png" class="brand-logo-two" alt="Images" loading="lazy"></div>-->
    <!--      <div class="brand-item"><img src="assets/images/brand-logo/sp-6-p.png" class="brand-logo-one" alt="Images" loading="lazy"><img src="assets/images/brand-logo/sp-6.png" class="brand-logo-two" alt="Images" loading="lazy"></div>-->
    <!--    </div>-->
    <!--  </div>-->
    <!--</div>-->
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
