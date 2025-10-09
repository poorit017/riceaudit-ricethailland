<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login_admin.php");
    exit;
}

require '../config/config_db.php';
$pdo = connectDB();

// ตรวจสอบว่ามีข้อมูลที่ส่งมาหรือไม่
$member_ids = $_POST['member_id'] ?? [];

if (empty($member_ids) || !isset($_FILES['certificate'])) {
    echo "❌ ไม่พบข้อมูลที่ส่งมา<br>";
    echo "<a href='upload_certificate.php'>🔙 กลับหน้าหลัก</a>";
    exit;
}

$successCount = 0;
$errorCount = 0;

foreach ($member_ids as $index => $member_id) {
    // ตรวจสอบว่ามีไฟล์และไม่มี error
    if (isset($_FILES['certificate']['error'][$index]) && $_FILES['certificate']['error'][$index] === UPLOAD_ERR_OK) {
        $safeFileName = basename($_FILES['certificate']['name'][$index]);
        $tmpFilePath = $_FILES['certificate']['tmp_name'][$index];

        // ตรวจสอบประเภทไฟล์ (PDF เท่านั้น)
        $allowedTypes = ['application/pdf'];
        $fileMimeType = mime_content_type($tmpFilePath);

        if (!in_array($fileMimeType, $allowedTypes)) {
            echo "❌ ไฟล์ของ ID $member_id ไม่ใช่ PDF<br>";
            $errorCount++;
            continue;
        }

        // ดึงข้อมูลสมาชิก
        $stmt = $pdo->prepare("SELECT Member_year, Member_course, Member_time FROM member WHERE Member_id = ?");
        $stmt->execute([$member_id]);
        $member = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$member) {
            echo "❌ ไม่พบข้อมูลสมาชิกสำหรับ ID: $member_id<br>";
            $errorCount++;
            continue;
        }

        $year = $member['Member_year'];
        $course = $member['Member_course'];
        $time = $member['Member_time'];

        // เตรียม path ปลายทาง
        $uploadDir =  "../web/uploads/$year/$course/$time/";
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $destination = $uploadDir . $safeFileName;
                    $relativePath = "$year/$course/$time/$safeFileName";
        // ย้ายไฟล์
        if (move_uploaded_file($tmpFilePath, $destination)) {
            $stmt = $pdo->prepare("UPDATE member SET Member_certificate = ? WHERE Member_id = ?");
            $stmt->execute([$relativePath, $member_id]);
            echo "✔️ อัปโหลดสำเร็จ: $safeFileName (ID: $member_id)<br>";
            $successCount++;
        } else {
            echo "❌ อัปโหลดล้มเหลว: $safeFileName (ID: $member_id)<br>";
            $errorCount++;
        }
    } else {
        echo "❌ ไม่มีไฟล์แนบสำหรับ ID: $member_id<br>";
        $errorCount++;
    }
}

// สรุปผล
echo "<hr>";
echo "✅ อัปโหลดสำเร็จ: $successCount รายการ<br>";
echo "<br><a href='upload_certificate.php' class='btn btn-primary mt-3'>🔙 กลับหน้าหลัก</a>";
