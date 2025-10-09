<?php
require '../config/config_db.php';
$pdo = connectDB();

// ตรวจสอบว่ามีข้อมูล POST มาหรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับข้อมูลจากฟอร์ม
    $id = $_POST['Memberinfo_id'] ?? '';
    $titlename = $_POST['Memberinfo_titlename'] ?? '';
    $fname = $_POST['Memberinfo_fname'] ?? '';
    $lname = $_POST['Memberinfo_lname'] ?? '';
    $tel = $_POST['Memberinfo_tel'] ?? '';
    $agency = $_POST['Memberinfo_agency'] ?? '';
    $typeagency = $_POST['Memberinfo_typeagency'] ?? '';
    $pos = $_POST['Memberinfo_pos'] ?? '';
    $typepos = $_POST['Memberinfo_typepos'] ?? '';

    // รับข้อมูลระดับการศึกษา 4 ชุด
    $c = [];
    $edu = [];
    $branch = [];
    $faculty = [];
    $inst = [];
    for ($i = 1; $i <= 4; $i++) {
        $c[$i] = $_POST["Memberinfo_c$i"] ?? '';
        $edu[$i] = $_POST["Memberinfo_edu$i"] ?? '';
        $branch[$i] = $_POST["Memberinfo_branch$i"] ?? '';
        $faculty[$i] = $_POST["Memberinfo_faculty$i"] ?? '';
        $inst[$i] = $_POST["Memberinfo_inst$i"] ?? '';
    }

    // เตรียมคำสั่ง SQL แบบ Prepared Statement
    $sql = "UPDATE memberinfo SET 
        Memberinfo_titlename = ?, 
        Memberinfo_fname = ?, 
        Memberinfo_lname = ?, 
        Memberinfo_tel = ?, 
        Memberinfo_agency = ?, 
        Memberinfo_typeagency = ?, 
        Memberinfo_pos = ?, 
        Memberinfo_typepos = ?, 
        Memberinfo_c1 = ?, Memberinfo_edu1 = ?, Memberinfo_branch1 = ?, Memberinfo_faculty1 = ?, Memberinfo_inst1 = ?,
        Memberinfo_c2 = ?, Memberinfo_edu2 = ?, Memberinfo_branch2 = ?, Memberinfo_faculty2 = ?, Memberinfo_inst2 = ?,
        Memberinfo_c3 = ?, Memberinfo_edu3 = ?, Memberinfo_branch3 = ?, Memberinfo_faculty3 = ?, Memberinfo_inst3 = ?,
        Memberinfo_c4 = ?, Memberinfo_edu4 = ?, Memberinfo_branch4 = ?, Memberinfo_faculty4 = ?, Memberinfo_inst4 = ?
        WHERE Memberinfo_id = ?";

    $params = [
        $titlename, $fname, $lname, $tel, $agency, $typeagency, $pos, $typepos,
        $c[1], $edu[1], $branch[1], $faculty[1], $inst[1],
        $c[2], $edu[2], $branch[2], $faculty[2], $inst[2],
        $c[3], $edu[3], $branch[3], $faculty[3], $inst[3],
        $c[4], $edu[4], $branch[4], $faculty[4], $inst[4],
        $id
    ];

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        header("Location: list_member.php?message=update_success");
        exit;
    } catch (PDOException $e) {
        echo "เกิดข้อผิดพลาดในการอัปเดตข้อมูล: " . $e->getMessage();
    }
} else {
    echo "วิธีการเข้าถึงหน้านี้ไม่ถูกต้อง";
}
