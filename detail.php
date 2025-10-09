<html>
<head>
  <title>รายละเอียด</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <link href="css/detail.css" rel="stylesheet">
  <style>
    table {
      border-collapse: collapse;
      width: 100%;
    }
    th, td {
      padding: 8px 16px;
      border: 1px solid #ccc;
      background-color: #fff;
    }
    th {
      background: #eee;
    }
    .tableFixHead {
      overflow-y: auto;
      height: 600px;
    }
    .tableFixHead thead th {
      position: sticky;
      top: 0;
    }
  </style>
</head>

<body>
  <?php
  include('navbar.html');
  require_once("config/config_db.php");

  $pdo = connectDB(); // ใช้ PDO

  $strMember_id = null;
  if (isset($_GET["Member_id"])) {
      $strMember_id = $_GET["Member_id"];
  }

  $sql = "SELECT * FROM member WHERE Member_id = :id";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(['id' => $strMember_id]);
  $result = $stmt->fetch();
  ?>

  <div class="container">
    <main class="col-md-12 ">
      <div class="container-fluid">
        <div class="row">
          <div class="table-responsive">
            <div align="center">
              <h3>รายละเอียด</h3>

              <div align="center">
                <table width="500" border="0">
                  <tr>
                    <th>ชื่อ-นามสกุล</th>
                    <td><?php echo $result["Member_titlename"] . $result["Member_firstname"] . ' ' . $result["Member_lastname"]; ?></td>
                  </tr>
                    <th>หลักสูตร</th>
                    <td>&nbsp;<?php echo $result["Member_course"]; ?></td>
                  </tr>
                  <tr>
                    <th>รุ่น</th>
                    <td>&nbsp;<?php echo empty($result["Member_time"]) ? 1 : $result["Member_time"]; ?></td>
                  </tr>
                  <tr>
                    <th>ปี</th>
                    <td>&nbsp;<?php echo $result["Member_year"]; ?></td>
                  </tr>
                  <tr>
                    <?php if (!empty($result["Member_certificate"])): ?>
                      <th>ประกาศนียบัตร</th>
                      <td align="center">
                        <a class="btn btn-info" href="web/uploads/<?php echo $result["Member_certificate"]; ?>" target="_blank">กดที่นี่</a>
                      </td>
                    <?php endif; ?>
                  </tr>
                </table>
                <br><br>
                <div align="center">
                  <a href="detail1.php?&Member_firstname=<?php echo $result["Member_firstname"]; ?>&Member_lastname=<?php echo $result["Member_lastname"]; ?>" class="btn btn-primary">
                    ข้อมูลการอบรม
                  </a>
                  <span style="margin-right: 2rem;"></span>
                  <a href="javascript:history.back(1)" class="btn btn-dark">
                    ย้อนกลับ
                  </a>
                </div>

</body>
</html>
