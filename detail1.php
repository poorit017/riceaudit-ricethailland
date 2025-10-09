<!DOCTYPE html>
<html>
<head>
   <title>Detail1</title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
   <link href="css/detail1.css" rel="stylesheet">
   <style>
      body { background-color: white; margin: 0; }
      .container-fluid { padding: 0; }
      .table-form-container { display: flex; justify-content: center; align-items: center; }
      .table-container { overflow-x: auto; overflow-y: auto; }
      table { border-collapse: collapse; width: 100%; border: 1px solid black; }
      th, td { padding: 8px; white-space: nowrap; border: 1px solid black; }
      .tableFixHead thead th {
         position: sticky; top: 0; background: #eee;
         box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.4); z-index: 2;
      }
      @media (max-width: 768px) {
         table { margin-left: -2rem; font-size: 13px; }
         th, td { padding: 6px; position: relative; }
      }
   </style>
</head>

<body>
<?php
ini_set('display_errors', 1);
error_reporting(~0);

include('navbar.html');
require_once("config/config_db.php");

$pdo = connectDB();

// รับค่า firstname, lastname
$strMember_fname = $_GET["Member_firstname"] ?? null;
$strMember_lname = $_GET["Member_lastname"] ?? null;

// ดึงข้อมูลจาก member
$sql = "SELECT * FROM member WHERE Member_firstname LIKE :fname AND Member_lastname LIKE :lname";
$stmt = $pdo->prepare($sql);
$stmt->execute(['fname' => $strMember_fname, 'lname' => $strMember_lname]);
$result1 = $stmt->fetch();

if (!$result1) {
   echo "<div class='container'><h3>ไม่พบข้อมูลผู้ใช้งาน</h3></div>";
   exit;
}

$Idmember = $result1["ID_Member"];

// ดึงข้อมูลจาก memberinfo
$sql2 = "SELECT * FROM memberinfo WHERE Memberinfo_id = :id";
$stmt2 = $pdo->prepare($sql2);
$stmt2->execute(['id' => $Idmember]);
$result2 = $stmt2->fetch();

// ดึงประวัติการอบรม
$sql3 = "SELECT Member_course, Member_year, Member_certificate, Member_id 
         FROM member 
         WHERE ID_Member = :id
         ORDER BY Member_course ASC, Member_year ASC";
$stmt3 = $pdo->prepare($sql3);
$stmt3->execute(['id' => $Idmember]);
$trainings = $stmt3->fetchAll();
?>

<div class="container">
   <h1 class="text-center" style='margin-top: 3rem;'>ประวัติผู้ฝึกอบรม</h1>
   <h3>
      <?php
         echo $result1["Member_titlename"] . $result1["Member_firstname"] . " " . $result1["Member_lastname"];
      ?>
   </h3>

   <?php if (!empty($result2["Memberinfo_pos"])): ?>
      <h5>ตำแหน่ง: <?= $result2["Memberinfo_pos"] ?></h5>
   <?php endif; ?>

   <?php if (!empty($result2["Memberinfo_typepos"])): ?>
      <h5>ประเภทตำแหน่ง: <?= $result2["Memberinfo_typepos"] ?></h5>
   <?php endif; ?>

   <?php if (!empty($result2["Memberinfo_agency"])): ?>
      <h5>หน่วยงาน: <?= $result2["Memberinfo_agency"] ?></h5>
   <?php endif; ?>

   <?php if (!empty($result2["Memberinfo_c1"]) || !empty($result2["Memberinfo_c2"]) || !empty($result2["Memberinfo_c3"]) || !empty($result2["Memberinfo_c4"])): ?>
      <div align="center" style="margin-top: 3rem;"><h1>ประวัติการศึกษา</h1></div><br>
   <?php endif; ?>

   <?php for ($i=1; $i<=4; $i++): ?>
      <?php if (!empty($result2["Memberinfo_c$i"])): ?>
         <h5>
            <?= $result2["Memberinfo_c$i"] ?><br>
            <?php if (!empty($result2["Memberinfo_branch$i"])): ?>
               <b>สาขา:</b> <?= $result2["Memberinfo_branch$i"] ?><br>
            <?php endif; ?>           
            <?php if (!empty($result2["Memberinfo_faculty$i"])): ?>
               <b>คณะ:</b> <?= $result2["Memberinfo_faculty$i"] ?><br>
            <?php endif; ?>
            <?php if (!empty($result2["Memberinfo_inst$i"])): ?>
               <b>สถาบัน:</b> <?= $result2["Memberinfo_inst$i"] ?>
            <?php endif; ?>
         </h5><br>
      <?php endif; ?>
   <?php endfor; ?>

   <h1 class="text-center" style='margin-top: 3rem; margin-bottom: 2rem;'>ข้อมูลประวัติการฝึกอบรม</h1>

   <div class="tableFixHead">
      <table class="table table-hover" width="auto" border="1">
         <thead class="text-center">
            <tr>
               <th>ลำดับ</th>
               <th>หลักสูตร</th>
               <th>ปี</th>
               <th>ประกาศนียบัตร</th>
               <th>รายละเอียด</th>
            </tr>
         </thead>
         <tbody>
            <?php $i=1; foreach($trainings as $row): ?>
               <tr>
                  <td align="center"><?= $i ?></td>
                  <td><?= $row["Member_course"] ?></td>
                  <td class="text-center"><?= $row["Member_year"] ?></td>
                  <td align="center">
                     <?php if (empty($row["Member_certificate"])): ?>
                        <a class="btn btn-secondary">ไม่สามารถแสดงได้</a>
                     <?php else: ?>
                        <a class="btn btn-info" href="web/uploads/<?= $row["Member_certificate"] ?>" target="_blank">กดที่นี่</a>
                     <?php endif; ?>
                  </td>
                  <td align="center">
                     <a class="btn btn-info" href="detail.php?Member_id=<?= $row["Member_id"] ?>">กดที่นี่</a>
                  </td>
               </tr>
            <?php $i++; endforeach; ?>
         </tbody>
      </table>
   </div>

   <div align="center">
      <a href="search.php" class="btn btn-dark">ย้อนกลับ</a>
   </div>
</div>
</body>
</html>
