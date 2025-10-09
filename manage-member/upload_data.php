<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login_admin.php");
    exit;
}
ini_set('max_execution_time', 900);
require '../vendor/autoload.php';
require 'db.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$pdo = connectDB();

// ✅ เมื่อ Upload Excel
if (isset($_POST['upload'])) {
    $fileName = $_FILES['excel']['tmp_name'];
    $spreadsheet = IOFactory::load($fileName);
    $worksheet = $spreadsheet->getActiveSheet();
    $data = [];

    foreach ($worksheet->getRowIterator(2) as $row) {
        $cells = [];
        foreach ($row->getCellIterator() as $cell) {
            $cells[] = trim($cell->getValue());
        }
        $data[] = $cells;
    }

    echo "<h3>Preview ข้อมูลจาก Excel</h3>";
    echo "<form method='post'>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr>
        <th>คำนำหน้า</th><th>ชื่อ</th><th>สกุล</th><th>เบอร์โทร</th><th>หน่วยงาน</th><th>ประเภทหน่วยงาน</th><th>หลักสูตร</th><th>ปี</th><th>รอบ</th>
        <th>ตำแหน่ง</th><th>ประเภทตำแหน่ง</th>
        <th>c1</th><th>edu1</th><th>branch1</th><th>faculty1</th><th>inst1</th>
        <th>c2</th><th>edu2</th><th>branch2</th><th>faculty2</th><th>inst2</th>
        <th>c3</th><th>edu3</th><th>branch3</th><th>faculty3</th><th>inst3</th>
        <th>c4</th><th>edu4</th><th>branch4</th><th>faculty4</th><th>inst4</th>
        </tr>";
    foreach ($data as $row) {
        echo "<tr>";
        foreach ($row as $cell) {
            echo "<td>" . htmlspecialchars($cell) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    echo "<input type='hidden' name='data' value='" . htmlspecialchars(json_encode($data)) . "'>";
    echo "<button type='submit' name='confirm'>ยืนยันการบันทึกข้อมูล</button>";
    echo "</form>";
    exit;
}

// ✅ เมื่อยืนยัน Insert/Update
if (isset($_POST['confirm'])) {
    $data = json_decode($_POST['data'], true);

    try {
        $pdo->beginTransaction();

        foreach ($data as $row) {
            list($title, $fname, $lname, $tel, $agency, $typeagency, $course, $year, $time, $pos, $typepos,
                $c1,$edu1,$branch1,$faculty1,$inst1,
                $c2,$edu2,$branch2,$faculty2,$inst2,
                $c3,$edu3,$branch3,$faculty3,$inst3,
                $c4,$edu4,$branch4,$faculty4,$inst4) = $row;

            $key = strtolower(trim($fname)) . '_' . strtolower(trim($lname));

            // 🔍 ตรวจสอบว่ามีข้อมูลใน memberinfo หรือไม่
            $stmt = $pdo->prepare("SELECT * FROM memberinfo WHERE LOWER(Memberinfo_fname)=? AND LOWER(Memberinfo_lname)=?");
            $stmt->execute([strtolower(trim($fname)), strtolower(trim($lname))]);
            $existing = $stmt->fetch();

            if ($existing) {
                // ✅ UPDATE เฉพาะคอลัมน์ที่ไม่ว่าง
                $fields = [
                    'Memberinfo_titlename' => $title, 'Memberinfo_tel' => $tel, 'Memberinfo_agency' => $agency,
                    'Memberinfo_typeagency' => $typeagency, 'Memberinfo_pos' => $pos, 'Memberinfo_typepos' => $typepos,
                    'Memberinfo_c1' => $c1, 'Memberinfo_edu1' => $edu1, 'Memberinfo_branch1' => $branch1, 'Memberinfo_faculty1' => $faculty1, 'Memberinfo_inst1' => $inst1,
                    'Memberinfo_c2' => $c2, 'Memberinfo_edu2' => $edu2, 'Memberinfo_branch2' => $branch2, 'Memberinfo_faculty2' => $faculty2, 'Memberinfo_inst2' => $inst2,
                    'Memberinfo_c3' => $c3, 'Memberinfo_edu3' => $edu3, 'Memberinfo_branch3' => $branch3, 'Memberinfo_faculty3' => $faculty3, 'Memberinfo_inst3' => $inst3,
                    'Memberinfo_c4' => $c4, 'Memberinfo_edu4' => $edu4, 'Memberinfo_branch4' => $branch4, 'Memberinfo_faculty4' => $faculty4, 'Memberinfo_inst4' => $inst4
                ];
                $updateParts = [];
                $updateValues = [];
                foreach ($fields as $col => $val) {
                    if ($val !== null && $val !== '') {
                        $updateParts[] = "$col = ?";
                        $updateValues[] = $val;
                    }
                }
                if (!empty($updateParts)) {
                    $updateValues[] = $existing['Memberinfo_id'];
                    $sql = "UPDATE memberinfo SET " . implode(',', $updateParts) . " WHERE Memberinfo_id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($updateValues);
                    echo "<p style='color: blue;'>🟦 UPDATE: $fname $lname สำเร็จ</p>";
                }
                $memberinfo_id = $existing['Memberinfo_id'];
            } else {
// เตรียม SQL
$stmt = $pdo->prepare("
INSERT INTO memberinfo (
    Memberinfo_titlename, Memberinfo_fname, Memberinfo_lname, Memberinfo_tel, 
    Memberinfo_agency, Memberinfo_typeagency, Memberinfo_pos, Memberinfo_typepos,
    Memberinfo_c1, Memberinfo_edu1, Memberinfo_branch1, Memberinfo_faculty1, Memberinfo_inst1,
    Memberinfo_c2, Memberinfo_edu2, Memberinfo_branch2, Memberinfo_faculty2, Memberinfo_inst2,
    Memberinfo_c3, Memberinfo_edu3, Memberinfo_branch3, Memberinfo_faculty3, Memberinfo_inst3,
    Memberinfo_c4, Memberinfo_edu4, Memberinfo_branch4, Memberinfo_faculty4, Memberinfo_inst4
) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
");

// ใส่ค่าให้ครบ 29 ตัวเรียงตามคอลัมน์
$stmt->execute([
    $title, $fname, $lname, $tel,
    $agency, $typeagency, $pos, $typepos,
    $c1, $edu1, $branch1, $faculty1, $inst1,
    $c2, $edu2, $branch2, $faculty2, $inst2,
    $c3, $edu3, $branch3, $faculty3, $inst3,
    $c4, $edu4, $branch4, $faculty4, $inst4
]);
                $memberinfo_id = $pdo->lastInsertId();
                echo "<p style='color: green;'>✅ INSERT: $fname $lname สำเร็จ</p>";
            }

            // ✅ Insert into member (หลังหลักสูตร/ปี/รอบมีค่า)
            if ($course && $year && $time) {
                $keyMember = strtolower(trim($title)) . '_' . strtolower(trim($fname)) . '_' . strtolower(trim($lname)) . '_' . strtolower(trim($course)) . '_' . strtolower(trim($year)) . '_' . strtolower(trim($time)) . '_' . $memberinfo_id;
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM member WHERE
                    Member_titlename=? AND Member_firstname=? AND Member_lastname=? AND Member_course=? AND Member_year=? AND Member_time=? AND ID_Member=?");
                $stmt->execute([$title, $fname, $lname, $course, $year, $time, $memberinfo_id]);
                if ($stmt->fetchColumn() == 0) {
                    $stmt = $pdo->prepare("INSERT INTO member
                        (Member_titlename, Member_firstname, Member_lastname, Member_course, Member_year, Member_time, ID_Member)
                        VALUES (?,?,?,?,?,?,?)");
                    $stmt->execute([$title, $fname, $lname, $course, $year, $time, $memberinfo_id]);
                    echo "<p style='color: green;'>✅ INSERT (member): $fname $lname - $course/$year/$time</p>";
                } else {
                    echo "<p style='color: orange;'>🟧 ซ้ำ ข้าม (member): $fname $lname - $course/$year/$time</p>";
                }
            }
        }

        $pdo->commit();
        echo "<p style='color: green;'>✅ การบันทึกข้อมูลเสร็จสมบูรณ์</p>";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>📥 อัปโหลด Excel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #f9fafb;
            padding-top: 60px;
        }
        .container-box {
            background-color: #fff;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .button {
            margin-bottom: 10px;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: 0.2s;
        }
        .button:hover {
            background-color: #218838;
            text-decoration: none;
        }
        .heading {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 25px;
        }
        .form-label {
            font-weight: 500;
        }
        
    </style>
</head>
<body>
    <div class="container">
        <div class="container-box mx-auto" style="max-width: 600px;">
            <div class="text-center">
                <h1 class="heading">📥 อัปโหลดข้อมูลจาก Excel</h1>
                <a href="sample_training_data.xlsx" class="button mb-3" target="_blank">📄 ดาวน์โหลดตัวอย่าง Excel</a>
                <a href="training_form.xlsx" class="button mb-3 bg-blue-500 hover:bg-blue-600">📑 ดาวน์โหลดไฟล์แบบฟอร์ม</a>

            </div>
                <p class="text-danger" style="font-weight:600; margin-top:10px;">
        หมายเหตุ: ถ้าช่องไหนไม่มีข้อมูล ให้เว้นว่างไว้
            <form method="post" enctype="multipart/form-data">
                <div class="mb-4">
                    <label class="form-label" for="excel">เลือกไฟล์ Excel</label>
                    <input type="file" name="excel" id="excel" accept=".xlsx,.xls" class="form-control" required>
                    <small class="text-muted">รองรับเฉพาะไฟล์ .xlsx หรือ .xls</small>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" name="upload" class="button w-full">✅ อัปโหลดและตรวจสอบข้อมูล</button>
                </div>
            <a href="index.php" class="block w-full bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded">
                 ← ย้อนกลับ
            </a>


            </form>
        </div>
    </div>
</body>
</html>

