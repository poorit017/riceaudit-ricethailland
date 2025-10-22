<?php
include "../config/config_news.php";
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login_admin.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("❌ ไม่พบ ID ข่าวที่ต้องการแก้ไข");
}

$id = intval($_GET['id']);

// ดึงข้อมูลข่าว
$stmt = $conn->prepare("SELECT * FROM news WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$news = $stmt->get_result()->fetch_assoc();

if (!$news) {
    die("❌ ไม่พบข่าวนี้ในระบบ");
}

// เมื่อกดบันทึกการแก้ไข
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_news"])) {
    $title = $_POST["title"];
    $content = $_POST["content"];
    $cover_path = $news['cover_image']; // ใช้รูปเดิมก่อน

    // ถ้ามีการอัปโหลดภาพปกใหม่
    if (!empty($_FILES["cover_image"]["name"])) {
        if (file_exists($cover_path)) unlink($cover_path); // ลบของเก่า
        $cover_dir = "uploads/cover/";
        if (!is_dir($cover_dir)) mkdir($cover_dir, 0777, true);
        $filename = time() . "_" . basename($_FILES["cover_image"]["name"]);
        $cover_path = $cover_dir . $filename;
        move_uploaded_file($_FILES["cover_image"]["tmp_name"], $cover_path);
    }

    // อัปเดตข้อมูล
    $stmt_update = $conn->prepare("UPDATE news SET title=?, content=?, cover_image=? WHERE id=?");
    $stmt_update->bind_param("sssi", $title, $content, $cover_path, $id);
    $stmt_update->execute();

    // ถ้ามีการเพิ่มภาพประกอบใหม่
    if (!empty($_FILES["detail_images"]["name"][0])) {
        $detail_dir = "uploads/details/";
        if (!is_dir($detail_dir)) mkdir($detail_dir, 0777, true);
        foreach ($_FILES["detail_images"]["tmp_name"] as $index => $tmpName) {
            if ($_FILES["detail_images"]["error"][$index] === 0) {
                $fileName = time() . "_" . basename($_FILES["detail_images"]["name"][$index]);
                $filePath = $detail_dir . $fileName;
                move_uploaded_file($tmpName, $filePath);
                $conn->query("INSERT INTO news_images (news_id, image_url) VALUES ($id, '$filePath')");
            }
        }
    }

    echo "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4'>✅ แก้ไขข้อมูลเรียบร้อย</div>";
    // โหลดข้อมูลใหม่
    $stmt = $conn->prepare("SELECT * FROM news WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $news = $stmt->get_result()->fetch_assoc();
}

// ดึงภาพประกอบทั้งหมด
$images = $conn->query("SELECT * FROM news_images WHERE news_id = $id");
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขข่าวประชาสัมพันธ์</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<div class="max-w-5xl mx-auto bg-white p-6 mt-8 rounded shadow">
    <h1 class="text-2xl font-bold mb-6 text-center">✏️ แก้ไขข่าวประชาสัมพันธ์</h1>

    <form method="post" enctype="multipart/form-data" class="space-y-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700">หัวข้อ</label>
            <input type="text" name="title" value="<?= htmlspecialchars($news['title']) ?>" required
                   class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700">เนื้อหา</label>
            <textarea name="content" rows="5" required
                      class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"><?= htmlspecialchars($news['content']) ?></textarea>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700">ภาพปกเดิม</label>
            <img src="<?= htmlspecialchars($news['cover_image']) ?>" class="w-40 rounded mb-2">
            <input type="file" name="cover_image" accept="image/*" class="mt-1 block w-full">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700">เพิ่มภาพประกอบใหม่ (เลือกได้หลายรูป)</label>
            <input type="file" name="detail_images[]" accept="image/*" multiple class="mt-1 block w-full">
        </div>

        <button type="submit" name="update_news"
                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">💾 บันทึกการแก้ไข</button>
    </form>

    <h2 class="text-xl font-semibold mt-10 mb-4">📷 ภาพประกอบเดิม</h2>
    <div class="grid grid-cols-3 gap-4">
        <?php while ($img = $images->fetch_assoc()) { ?>
            <div class="relative">
                <img src="<?= htmlspecialchars($img['image_url']) ?>" class="rounded shadow">
                <form method="post" action="remove_image.php" class="absolute top-1 right-1">
                    <input type="hidden" name="id" value="<?= $img['id'] ?>">
                    <input type="hidden" name="news_id" value="<?= $id ?>">
                    <button type="submit" class="bg-red-500 text-white rounded-full px-2 text-xs">✕</button>
                </form>
            </div>
        <?php } ?>
    </div>

    <div class="mt-6">
        <a href="news_admin.php" class="inline-block bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">← กลับ</a>
    </div>
</div>
</body>
</html>
