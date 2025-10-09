<?php
include_once "../config/config.php";
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login_admin.php");
    exit;
}

// ดึงข้อมูลเอกสารทั้งหมดเรียงตามลำดับ
$documents = $conn->query("SELECT * FROM knowledge ORDER BY sort_order ASC");

// อัพโหลดไฟล์
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $file = $_FILES['file'];

    if ($file['error'] === 0) {
        $uploadDir = '../web/knowlegde/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filename =  basename($file['name']);
        $path = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $path)) {
            $dbPath = '../web/knowlegde/' . $filename;

            // หา sort_order ล่าสุด +1
            $result = $conn->query("SELECT MAX(sort_order) AS max_sort FROM knowledge");
            $row = $result->fetch_assoc();
            $nextSort = $row['max_sort'] + 1;
            $stmt = $conn->prepare("INSERT INTO knowledge (title, description, file_path, sort_order) VALUES (?, ?, ?, ?)");
            if (!$stmt) {
                die("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("sssi", $title, $description, $dbPath, $nextSort);
            $stmt->execute();
            $stmt->close();

            header("Location: index.php");
            exit;
        } else {
            $error = "เกิดข้อผิดพลาดในการย้ายไฟล์";
        }
    } else {
        $error = "เกิดข้อผิดพลาดในการอัปโหลดไฟล์";
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>จัดการองค์ความรู้</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
    <?php if (isset($_GET['status'])): ?>
        <script>
            <?php if ($_GET['status'] === 'deleted'): ?>
                alert('ลบข้อมูลสำเร็จ');
            <?php elseif ($_GET['status'] === 'error'): ?>
                alert('เกิดข้อผิดพลาดในการลบ');
            <?php elseif ($_GET['status'] === 'invalid'): ?>
                alert('ไม่พบข้อมูลที่จะลบ');
            <?php endif; ?>
        </script>
    <?php endif; ?>
</head>

<body class="bg-gray-100 p-6">
    <div class="max-w-5xl mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-3xl font-bold mb-6 text-green-700">จัดการองค์ความรู้</h1>

        <?php if (isset($error)): ?>
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Form อัพโหลด -->
        <form method="POST" enctype="multipart/form-data" class="mb-8">
            <div class="mb-4">
                <label class="block font-semibold mb-1" for="title">ชื่อเอกสาร</label>
                <input type="text" id="title" name="title" class="w-full border border-gray-300 rounded p-2" required />
            </div>
            <div class="mb-4">
                <label class="block font-semibold mb-1" for="description">รายละเอียด</label>
                <textarea id="description" name="description" class="w-full border border-gray-300 rounded p-2" rows="3"></textarea>
            </div>
            <div class="mb-4">
                <label class="block font-semibold mb-1" for="file">เลือกไฟล์</label>
                <input type="file" id="file" name="file" class="w-full" required />
            </div>
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded shadow">
                อัพโหลด
            </button>
            <a href="../admin_panel.php" class="bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-2 rounded">ย้อนกลับ</a>
        </form>

        <!-- ตารางแสดงข้อมูล -->
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-300 rounded">
                <thead class="bg-green-600 text-white">
                    <tr>
                        <th class="p-3 text-left">#</th>
                        <th class="p-3 text-left">ชื่อเอกสาร</th>
                        <th class="p-3 text-left">รายละเอียด</th>
                        <th class="p-3 text-left">ไฟล์</th>
                        <th class="p-3 text-center">ลำดับ</th>
                        <th class="p-3 text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($documents->num_rows > 0): ?>
                        <?php $i = 1;
                        while ($doc = $documents->fetch_assoc()): ?>
                            <tr class="border-t border-gray-300 hover:bg-gray-50">
                                <td class="p-3"><?= $i++ ?></td>
                                <td class="p-3"><?= htmlspecialchars($doc['title']) ?></td>
                                <td class="p-3"><?= htmlspecialchars($doc['description']) ?></td>
                                <td class="p-3">
                                    <a href="<?= htmlspecialchars($doc['file_path']) ?>" target="_blank" class="text-blue-600 hover:underline">
                                        ดาวน์โหลด
                                    </a>
                                </td>
                                <td class="p-3 text-center space-x-1">
                                    <a href="move_up.php?id=<?= $doc['id'] ?>" class="text-sm text-blue-600">⬆️</a>
                                    <a href="move_down.php?id=<?= $doc['id'] ?>" class="text-sm text-blue-600">⬇️</a>
                                </td>
                                <td class="p-3 text-center space-x-2">
                                    <a href="edit_knowledge.php?id=<?= $doc['id'] ?>" class="text-yellow-600 hover:text-yellow-800 font-semibold">
                                        แก้ไข
                                    </a>
                                    <a href="delete_knowledge.php?id=<?= $doc['id'] ?>" onclick="return confirm('ยืนยันการลบเอกสารนี้?')" class="text-red-600 hover:text-red-800 font-semibold">
                                        ลบ
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center p-4 text-gray-500">ไม่มีเอกสารในระบบ</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</body>

</html>