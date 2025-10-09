<?php
include_once "../config/config.php";
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login_admin.php");
    exit;
}

// รับ id จากพารามิเตอร์
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo "ไม่พบเอกสารที่ต้องการแก้ไข";
    exit;
}

// ดึงข้อมูลเอกสารเดิม
$stmt = $conn->prepare("SELECT * FROM knowledge WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$doc = $result->fetch_assoc();

if (!$doc) {
    echo "ไม่พบเอกสารที่ต้องการแก้ไข";
    exit;
}

$error = '';
$success = '';

// ถ้าส่งข้อมูลแก้ไข
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];

    // ถ้ามีไฟล์ใหม่อัปโหลด
    if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
        $uploadDir = '../web/knowlegde/';
        // สร้างโฟลเดอร์ถ้ายังไม่มี
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        // ลบไฟล์เก่า (ถ้ามี)
        if (file_exists($doc['file_path'])) {
            unlink($doc['file_path']);
        }

        $filename =  basename($_FILES['file']['name']);
        $path = $uploadDir . $filename;
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $path)) {
            $error = "เกิดข้อผิดพลาดในการอัปโหลดไฟล์";
        }
    } else {
        // ไม่อัปโหลดไฟล์ใหม่ ใช้ไฟล์เดิม
        $path = $doc['file_path'];
    }

    if (!$error) {
    $stmtUpdate = $conn->prepare("UPDATE knowledge SET title = ?, description = ?, file_path = ? WHERE id = ?");
    $stmtUpdate->bind_param("sssi", $title, $description, $path, $id);
        if ($stmtUpdate->execute()) {
            $success = "อัปเดตเอกสารเรียบร้อยแล้ว";
            // โหลดข้อมูลใหม่จาก DB เพื่อแสดงในฟอร์ม
            $doc['title'] = $title;
            $doc['description'] = $description;
            $doc['file_path'] = $path;

        } else {
            $error = "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>แก้ไขเอกสารองค์ความรู้</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-lg mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-2xl font-bold mb-6">แก้ไขเอกสารองค์ความรู้</h1>

        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-red-200 text-red-800 rounded"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="mb-4 p-3 bg-green-200 text-green-800 rounded"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-4">
                <label class="block font-semibold mb-2" for="title">ชื่อเอกสาร</label>
                <input type="text" id="title" name="title" class="w-full border border-gray-300 rounded px-3 py-2" required value="<?= htmlspecialchars($doc['title']) ?>">
            </div>
            <div class="mb-4">
                <label class="block font-semibold mb-2" for="description">รายละเอียด</label>
                <textarea id="description" name="description" class="w-full border border-gray-300 rounded px-3 py-2" rows="4"><?= htmlspecialchars($doc['description']) ?></textarea>
            </div>

            <div class="mb-4">
                <label class="block font-semibold mb-2" for="file">ไฟล์เอกสาร (อัปโหลดไฟล์ใหม่ถ้าต้องการเปลี่ยน)</label>
                <input type="file" id="file" name="file" class="w-full">
                <?php if ($doc['file_path'] && file_exists($doc['file_path'])): ?>
                    <p class="mt-2 text-sm text-gray-600">ไฟล์ปัจจุบัน: <a href="<?= htmlspecialchars($doc['file_path']) ?>" target="_blank" class="text-blue-600 hover:underline">เปิดดูไฟล์</a></p>
                <?php else: ?>
                    <p class="mt-2 text-sm text-gray-600">ไม่มีไฟล์เอกสารในระบบ</p>
                <?php endif; ?>
            </div>

            <div class="flex justify-between">
                <a href="index.php" class="bg-yellow-400 hover:bg-yellow-500 text-white px-4 py-2 rounded">ย้อนกลับ</a>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">บันทึก</button>
            </div>
        </form>
    </div>
</body>
</html>
