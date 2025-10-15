<?php include 'config/config_news.php'; ?>

<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <title>หน้าแรก</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- CSS -->
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
  <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
  <link href="css/style1.css" rel="stylesheet">
</head>

<body id="home">
  <main>
    <header>
      <div class="container" class="navbar navbar-light">
        <div class="logo">
          <a href="#home" class="scroll"><img class src="web/img/ตรากอง.png" alt="โลโก้ของเว็บไซต์">
          </a>
        </div>
      </div>
    </header>

    <body id="home">
      <!-- Nav Bar Start -->
      <div class="nav-bar">
        <div class="container">
          <nav class="navbar navbar-expand-lg bg-dark navbar-dark">
            <a href="#" class="navbar-brand">MENU</a>
            <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
              <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-between" id="navbarCollapse">
              <div class="navbar-nav mr-auto">
                <a href="#home" onclick="scrollToSection('home')" class="nav-item nav-link ">หน้าแรก</a>
                <div class="nav-item dropdown">
                  <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">ค้นหา</a>
                  <div class="dropdown-menu">
                    <a href="dashboard.php" class="dropdown-item">หน้าแรก</a>
                    <a href="search.php" class="dropdown-item">ค้นหาผู้ผ่านการอบรม</a>
                    <a href="countall.php" class="dropdown-item">จำนวนผู้ผ่านการอบรมหลักสูตรแต่ละปี</a>
                  </div>
                </div>
                <a href="#blog" onclick="scrollToSection('blog')" class="nav-item nav-link">รูปภาพการอบรม</a>
                <a href="#about" onclick="scrollToSection('about')" class="nav-item nav-link">ประชาสัมพันธ์</a>
                <a href="#Service" onclick="scrollToSection('Service')" class="nav-item nav-link">เอกสารองค์ความรู้</a>
                <!-- <a href="contact.html" class="nav-item nav-link">ติดต่อเรา</a> -->
              </div>

            </div>
          </nav>
        </div>
      </div>
      <!-- Carousel Start -->
      <?php include 'config/config.php'; // เชื่อมต่อฐานข้อมูล
      $carouselImages = [];
      $result = $conn->query("SELECT image_path FROM carousel_images ORDER BY id ASC");
      if ($result) {
        while ($row = $result->fetch_assoc()) {
          $carouselImages[] = $row['image_path'];
        }
      }
      ?>

      <div class="carousel">
        <div class="container-fluid">
          <div class="owl-carousel">
            <?php foreach ($carouselImages as $img): ?>
              <div class="carousel-item">
                <div class="carousel-img">
                  <img src="<?= htmlspecialchars($img) ?>" alt="Image">
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php
      $sectionImages = [];
      $result = $conn->query("SELECT image_path FROM carousel_section_images ORDER BY id ASC");
      if ($result) {
        while ($row = $result->fetch_assoc()) {
          $sectionImages[] = $row['image_path'];
        }
      }
      ?>

      <div class="container my-5"> <!-- my-5 = เว้นระยะบน-ล่างของทั้ง section -->
        <div class="row g-4"> <!-- g-4 = ช่องว่างระหว่างรูป -->
          <?php foreach ($sectionImages as $img): ?>
            <div class="col-md-4 col-sm-6 text-center">
              <img src="<?= htmlspecialchars($img) ?>" class="img-fluid rounded mb-4" style="width: 300px; height: 300px; object-fit: cover;" alt="Section Image">
            </div>
          <?php endforeach; ?>
        </div>
      </div>


      <!-- Carousel END -->

      <!-- Section: รูปภาพการอบรม -->
      <div class="container my-5" id="blog">
        <div class="section-header text-center mb-4">
          <h2 class="text-success">รูปภาพการอบรม</h2>
        </div>
        <div class="row">
          <?php
          include 'config/config.php';
          $years = $conn->query("SELECT * FROM years ORDER BY year_name DESC");
          $count = 1;
          while ($row = $years->fetch_assoc()):
          ?>
            <div class="col-md-4 mb-4">
              <div class="card h-100 shadow-sm">
                <img src="web/img/<?= $count ?>.jpg" class="card-img-top" alt="ภาพปี <?= $row['year_name'] ?>">
                <div class="card-body">
                  <h5 class="card-title">
                    <a href="picture/picture<?= $row['year_name'] ?>.html" target="_blank" class="text-decoration-none text-primary">
                      รูปภาพการอบรม ปี <?= htmlspecialchars($row['year_name']) ?>
                    </a>
                  </h5>
                </div>
              </div>
            </div>
          <?php
            $count++;
          endwhile;
          ?>
        </div>
      </div>

      <!-- Section: ประชาสัมพันธ์ -->

      <?php
      include 'config/config_news.php';
      $result = $conn->query("SELECT * FROM news ORDER BY created_at DESC");
      if ($result->num_rows > 0): ?>
        <div class="container my-5" id="about">
          <div class="section-header text-center mb-4">
            <h2 class="text-success">ประชาสัมพันธ์</h2>
          </div>
          <div class="row">
            <?php while ($row = $result->fetch_assoc()):
              $image = !empty($row['cover_image']) ? "news/" . $row['cover_image'] : null;
              $link = "news/news-detail.php?id=" . $row["id"];
            ?>
              <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                  <?php if ($image): ?>
                    <a href="<?= $link ?>" target="_blank">
                      <img src="<?= htmlspecialchars($image) ?>" class="card-img-top" alt="ภาพข่าว">
                    </a>
                  <?php endif; ?>
                  <div class="card-body d-flex flex-column">
                    <h5 class="card-title">
                      <a href="<?= $link ?>" target="_blank" class="text-dark text-decoration-none">
                        <?= htmlspecialchars($row['title']) ?>
                      </a>
                    </h5>
                    <p class="text-muted small mt-auto">เผยแพร่เมื่อ: <?= date("d/m/Y", strtotime($row['created_at'])) ?></p>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          </div>
        </div>
      <?php endif;
      $conn->close(); // ✅ ปิดการเชื่อมต่อฐานข้อมูล 
      ?>
      <?php
      include 'config/config.php';
      $result = $conn->query("SELECT * FROM knowledge ORDER BY sort_order asc");
      ?>
      <!-- Section:  องค์ความรู้ -->
      <section class="Service" id="Service">
        <div class="service">
          <div class="container">
            <div class="section-header text-center">
              <h2 class="text-success">เอกสารองค์ความรู้</h2>
            </div>
            <div class="row">
              <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                  <div class="col-lg-3 col-md-6 mb-4">
                    <div class="service-item text-center">
                      <a href="<?php echo $row['file_path']; ?>" target="_blank">
                        <img src="web/img/document.png" title="<?php echo htmlspecialchars($row['title']); ?>" style="width: 30%">
                      </a>
                      <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                      <p class="text-muted"><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                    </div>
                  </div>
                <?php endwhile; ?>
              <?php else: ?>
                <div class="col-12 text-center">
                  <p>ไม่พบเอกสารในขณะนี้</p>
                </div>
              <?php endif; ?>
              <?php $conn->close(); // ✅ ปิดการเชื่อมต่อฐานข้อมูล 
              ?>
            </div>
          </div>
        </div>
      </section>

      <!--Footter Start-->
      <div class="footer">
        <div class="container">
          <div class="row">
            <div class="col-lg-6 col-md-6">
              <div class="footer-contact">
                <h2>เกี่ยวกับเรา</h2>
                <P><i class="fa fa-map-marker-alt"></i>กองตรวจสอบรับรองมาตรฐานข้าวและผลิตภัณฑ์
                  <br>&nbsp; &nbsp;&nbsp; กรมการข้าว เลขที่ 2177 ถนนพหลโยธิน แขวงลาดยาว
                  <br>&nbsp; &nbsp;&nbsp; เขตจตุจักร กรุงเทพฯ 10900
                </p>
                <br><i class="fa fa-phone-alt">&nbsp; &nbsp;</i>02-561-21744</p>
                <p><i class="fa fa-envelope"></i>dric.rcms@gmail.com</p>
                <a class="footer-link" href="login_admin.php" target="_blank"><i class="fa fa-user-shield"></i> เข้าสู่ระบบผู้ดูแล</a>
              </div>
            </div>
            <div class="col-lg-2 col-md-6">
              <div class="footer-link">
                <h2>โครงสร้างเว็บ</h2>
                <a href="#home">หน้าแรก</a>
                <a href="#blog" onclick="scrollToSection('blog')">รูปภาพการอบรม</a>
                <a href="#Service" onclick="scrollToSection('Service')">เอกสารองค์ความรู้</a>
              </div>
            </div>
            <div class="col-lg-4 col-md-6">
              <div class="footer-link">
                <h2>ค้นหาผู้ผ่านการอบรม</h2>
                <a href="dashboard.php" target="_blank">หน้าแรก</a>
                <a href="search.php" target="_blank">ค้นหาผู้ผ่านการอบรม</a>
                <a href="countall.php" target="_blank">จำนวนผู้ผ่านการอบรมหลักสูตรแต่ละปี</a>
              </div>
            </div>
          </div>
        </div>
        <div class="container copyright">
          <p>&copy; <a href="#home">Your Site Name</a>, All Right Reserved. Designed By <a
              href="https://htmlcodex.com">HTML Codex</a></p>
        </div>
      </div>
      <!--Footter End-->
      <script>
        function scrollToSection(sectionId) {
          const section = document.getElementById(sectionId);
          section.scrollIntoView({
            behavior: "smooth"
          });
        }
        document.addEventListener("DOMContentLoaded", function() {
          fetch('get_news.php') // เรียก API ดึงข้อมูลจากฐานข้อมูล
            .then(response => response.json())
            .then(data => {
              const newsContainer = document.getElementById("news-container");
              const newsSection = document.getElementById("news-section");

              // ตรวจสอบว่ามีข้อมูลข่าวหรือไม่
              if (data.length > 0) {
                newsContainer.innerHTML = data.map(news => `
            <div class='col-md-4 d-flex'>
              <div class='card mb-3 w-100'>
                <a href='${news.link_url}' target='_blank'>
                  <img src='${news.image_url}' class='card-img-top' alt='ข่าว'>
                </a>
                <div class='card-body'>
                  <h5 class='card-title'>
                    <a href='${news.link_url}' target='_blank' style='text-decoration: none; color: black;'>${news.title}</a>
                  </h5>
                  <p class='card-text'>${news.content}</p>
                </div>
              </div>
            </div>
          `).join('');
              } else {
                // ถ้าไม่มีข่าว ให้ซ่อนส่วนแสดงผล
                newsSection.style.display = "none";
              }
            })
            .catch(error => console.error("Error fetching news:", error));
        });
      </script>
      <!-- JavaScript Libraries -->
      <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
      <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
      <script src="lib/easing/easing.min.js"></script>
      <script src="lib/owlcarousel/owl.carousel.min.js"></script>
      <script src="lib/waypoints/waypoints.min.js"></script>
      <script src="lib/counterup/counterup.min.js"></script>

      <!-- Template Javascript -->
      <script src="js/main.js"></script>



</html>