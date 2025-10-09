<?php
require '../vendor/autoload.php';
require '../config/config_db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

$pdo = connectDB();

$years   = $_POST['years']   ?? [];
$courses = $_POST['courses'] ?? [];
$types   = $_POST['types']   ?? [];

if (empty($years) || empty($courses)) {
    die("❌ กรุณาเลือกปีและหลักสูตร");
}

$placeholders_years   = implode(",", array_fill(0, count($years), "?"));
$placeholders_courses = implode(",", array_fill(0, count($courses), "?"));
$placeholders_types   = implode(",", array_fill(0, count($types), "?"));

// 📌 Query Sheet 1
$sql1 = "SELECT 
            CONCAT(m.Member_titlename, m.Member_firstname, ' ', m.Member_lastname) AS fullname,
            m.Member_id, m.Member_course, m.Member_time, m.Member_year,
            mi.Memberinfo_agency, mi.Memberinfo_typeagency
        FROM member m
        JOIN memberinfo mi ON m.ID_Member = mi.Memberinfo_id
        WHERE m.Member_year IN ($placeholders_years)
          AND m.Member_course IN ($placeholders_courses)
          AND mi.Memberinfo_typeagency IN ($placeholders_types)
        ORDER BY m.Member_year DESC, m.Member_course ASC, m.Member_time ASC, m.Member_id ASC";

$stmt1 = $pdo->prepare($sql1);
$stmt1->execute(array_merge($years, $courses, $types));
$data = $stmt1->fetchAll(PDO::FETCH_ASSOC);

// 📌 Query Sheet 2 + ใช้สำหรับแยกหลักสูตร
$sql2 = "SELECT DISTINCT 
            CONCAT(m.Member_titlename, m.Member_firstname, ' ', m.Member_lastname) AS fullname,
            m.Member_course,
            mi.Memberinfo_agency,
            mi.Memberinfo_typeagency
        FROM member m
        JOIN memberinfo mi ON m.ID_Member = mi.Memberinfo_id
        WHERE m.Member_year IN ($placeholders_years)
          AND m.Member_course IN ($placeholders_courses)
          AND mi.Memberinfo_typeagency IN ($placeholders_types)
        ORDER BY m.Member_course ASC, fullname ASC";

$stmt2 = $pdo->prepare($sql2);
$stmt2->execute(array_merge($years, $courses, $types));
$data_unique = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// ✅ จัดกลุ่มข้อมูลตามหลักสูตร สำหรับสร้างชีตแยก
$coursesData = [];
foreach ($data_unique as $d) {
    $course = $d['Member_course'];
    if (!isset($coursesData[$course])) {
        $coursesData[$course] = [];
    }
    $coursesData[$course][] = $d;
}

// 📌 Query Sheet 3 (ข้อมูลผู้อบรมแบบละเอียด)
$sql3 = "SELECT 
            CONCAT(m.Member_titlename, m.Member_firstname, ' ', m.Member_lastname) AS fullname,
            MAX(mi.Memberinfo_tel) AS Memberinfo_tel,
            MAX(mi.Memberinfo_agency) AS Memberinfo_agency,
            MAX(mi.Memberinfo_typeagency) AS Memberinfo_typeagency,
            MAX(mi.Memberinfo_pos) AS Memberinfo_pos,
            MAX(mi.Memberinfo_typepos) AS Memberinfo_typepos,
            MAX(mi.Memberinfo_c1) AS Memberinfo_c1, MAX(mi.Memberinfo_edu1) AS Memberinfo_edu1, 
            MAX(mi.Memberinfo_branch1) AS Memberinfo_branch1, MAX(mi.Memberinfo_faculty1) AS Memberinfo_faculty1, MAX(mi.Memberinfo_inst1) AS Memberinfo_inst1,
            MAX(mi.Memberinfo_c2) AS Memberinfo_c2, MAX(mi.Memberinfo_edu2) AS Memberinfo_edu2, 
            MAX(mi.Memberinfo_branch2) AS Memberinfo_branch2, MAX(mi.Memberinfo_faculty2) AS Memberinfo_faculty2, MAX(mi.Memberinfo_inst2) AS Memberinfo_inst2,
            MAX(mi.Memberinfo_c3) AS Memberinfo_c3, MAX(mi.Memberinfo_edu3) AS Memberinfo_edu3, 
            MAX(mi.Memberinfo_branch3) AS Memberinfo_branch3, MAX(mi.Memberinfo_faculty3) AS Memberinfo_faculty3, MAX(mi.Memberinfo_inst3) AS Memberinfo_inst3,
            MAX(mi.Memberinfo_c4) AS Memberinfo_c4, MAX(mi.Memberinfo_edu4) AS Memberinfo_edu4, 
            MAX(mi.Memberinfo_branch4) AS Memberinfo_branch4, MAX(mi.Memberinfo_faculty4) AS Memberinfo_faculty4, MAX(mi.Memberinfo_inst4) AS Memberinfo_inst4
        FROM member m
        JOIN memberinfo mi ON m.ID_Member = mi.Memberinfo_id
        WHERE m.Member_year IN ($placeholders_years)
          AND m.Member_course IN ($placeholders_courses)
          AND mi.Memberinfo_typeagency IN ($placeholders_types)
        GROUP BY fullname
        ORDER BY fullname ASC";

$stmt3 = $pdo->prepare($sql3);
$stmt3->execute(array_merge($years, $courses, $types));
$data_detail = $stmt3->fetchAll(PDO::FETCH_ASSOC);

// 🔽 สร้าง Excel
$spreadsheet = new Spreadsheet();

$styleHeader = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F81BD']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
];

// ================== Sheet 1 ==================
$sheet1 = $spreadsheet->getActiveSheet();
$sheet1->setTitle("รายงานตามปี-รอบ");

$headers1 = ["ลำดับ", "ชื่อ - สกุล", "หลักสูตร", "รอบ", "ปี", "หน่วยงาน", "ประเภทหน่วยงาน"];
$col = "A";
foreach ($headers1 as $h) {
    $sheet1->setCellValue($col."1", $h);
    $col++;
}
$sheet1->getStyle("A1:G1")->applyFromArray($styleHeader);

$row = 2; $no = 1;
foreach ($data as $d) {
    $sheet1->setCellValue("A$row", $no++);
    $sheet1->setCellValue("B$row", $d['fullname']);
    $sheet1->setCellValue("C$row", $d['Member_course']);
    $sheet1->setCellValue("D$row", $d['Member_time']);
    $sheet1->setCellValue("E$row", $d['Member_year']);
    $sheet1->setCellValue("F$row", $d['Memberinfo_agency']);
    $sheet1->setCellValue("G$row", $d['Memberinfo_typeagency']);
    $row++;
}
$sheet1->getStyle("A1:G".($row-1))->applyFromArray([
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
]);
foreach (range('A','G') as $c) $sheet1->getColumnDimension($c)->setAutoSize(true);
$sheet1->freezePane('A2');

// ================== Sheet 2 ==================
$sheet2 = $spreadsheet->createSheet();
$sheet2->setTitle("รายงานข้อมูลผู้อบรม");

$headers3 = [
    "ลำดับ", "ชื่อ - สกุล", "เบอร์โทรศัพท์", "หน่วยงาน", "ประเภทหน่วยงาน",
    "ตำแหน่ง", "ประเภทตำแหน่ง",
    "ปวส.", "วุฒิปวส.", "คณะปวส.", "สาขาปวส.", "สถาบันปวส.",
    "ปริญญาตรี", "วุฒิปริญญาตรี", "คณะปริญญาตรี", "สาขาปริญญาตรี", "สถาบันปริญญาตรี",
    "ปริญญาโท", "วุฒิปริญญาโท", "คณะปริญญาโท", "สาขาปริญญาโท", "สถาบันปริญญาโท",
    "ปริญญาเอก", "วุฒิปริญญาเอก", "คณะปริญญาเอก", "สาขาปริญญาเอก", "สถาบันปริญญาเอก"
];
$col = "A";
foreach ($headers3 as $h) $sheet2->setCellValue($col++."1", $h);
$sheet2->getStyle("A1:AA1")->applyFromArray($styleHeader);

$row = 2; $no = 1;
foreach ($data_detail as $d) {
    $sheet2->fromArray(array_merge([$no++], array_values($d)), null, "A$row");
    $row++;
}
$sheet2->getStyle("A1:AA".($row-1))->applyFromArray([
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
]);
foreach (range('A','AA') as $c) $sheet2->getColumnDimension($c)->setAutoSize(true);
$sheet2->freezePane('A2');

// ================== Sheet แยกตามหลักสูตร ==================
foreach ($coursesData as $course => $members) {
    $sheet = $spreadsheet->createSheet();
    $sheet->setTitle(mb_substr($course, 0, 25)); // จำกัดชื่อ sheet ไม่เกิน 31 ตัวอักษร

    $headers = ["ลำดับ", "ชื่อ - สกุล", "หน่วยงาน", "ประเภทหน่วยงาน"];
    $col = "A";
    foreach ($headers as $h) $sheet->setCellValue($col++."1", $h);
    $sheet->getStyle("A1:D1")->applyFromArray($styleHeader);

    $row = 2; $no = 1;
    foreach ($members as $m) {
        $sheet->setCellValue("A$row", $no++);
        $sheet->setCellValue("B$row", $m['fullname']);
        $sheet->setCellValue("C$row", $m['Memberinfo_agency']);
        $sheet->setCellValue("D$row", $m['Memberinfo_typeagency']);
        $row++;
    }

    $sheet->getStyle("A1:D".($row-1))->applyFromArray([
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ]);
    foreach (range('A','D') as $c) $sheet->getColumnDimension($c)->setAutoSize(true);
    $sheet->freezePane('A2');
}

// ส่งออกไฟล์
$filename = "รายงานอบรม_" . date("Ymd_His") . ".xlsx";
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment;filename=\"$filename\"");
header("Cache-Control: max-age=0");

$writer = new Xlsx($spreadsheet);
$writer->save("php://output");
exit;
