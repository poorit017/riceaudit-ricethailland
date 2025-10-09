<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login_admin.php");
    exit;
}
include '../config/config.php'; // MySQLi

// อัพโหลดรูป
if (isset($_POST['upload'])) {
    $targetDir = "../web/img/";
    $fileName = basename($_FILES["image"]["name"]);
    $targetFile = $targetDir . $fileName;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
        $type = $_POST['type']; // carousel หรือ section
        $table = $type === 'carousel' ? 'carousel_images' : 'carousel_section_images';

        $stmt = $conn->prepare("INSERT INTO $table (image_path) VALUES (?)");
        $imagePath = "web/img/" . $fileName;
        $stmt->bind_param("s", $imagePath);
        $stmt->execute();
        $stmt->close();
    }
}

// ลบรูป
if (isset($_GET['delete'])) {
    $type = $_GET['type'];
    $table = $type === 'carousel' ? 'carousel_images' : 'carousel_section_images';
    $id = (int)$_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM $table WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการรูปภาพ Carousel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Kanit', sans-serif; }
        .container { max-width: 900px; }
        .image-thumb { border-radius: 8px; object-fit: cover; }
        .card { border-radius: 12px; }
    </style>
</head>
    <body class="bg-gray-100 p-6">
        <div class="container py-4">
    <!-- ปุ่มย้อนกลับ -->

    <div class="mt-8">
        <a href="../admin_panel.php" class='bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 '>← กลับไปหน้าแอดมิน</a>
    </div>
<div class="container py-4">
        <div class="mb-4">
        <a href="../admin_panel.php" class="btn btn-warning">
            ← กลับไปหน้าแอดมิน
        </a>
    </div>
    <h2 class="text-center mb-4">📷 จัดการรูปภาพ Carousel & Section</h2>

    <!-- ฟอร์มอัพโหลด -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data" class="row g-3">
                <div class="col-md-5">
                    <input type="file" name="image" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <select name="type" class="form-select">
                        <option value="carousel">Carousel</option>
                        <option value="section">Section</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" name="upload" class="btn btn-primary w-100">อัพโหลด</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Carousel Images -->
    <h4 class="mb-3">🖼 Carousel Images</h4>
    <div class="row g-3 mb-4">
        <?php
        $result = $conn->query("SELECT * FROM carousel_images ORDER BY id DESC");
        while ($row = $result->fetch_assoc()):
        ?>
            <div class="col-md-4 text-center">
                <div class="card shadow-sm p-2">
                    <img src="../<?= htmlspecialchars($row['image_path']) ?>" class="img-fluid image-thumb" style="height: 180px;">
                    <a href="?delete=<?= $row['id'] ?>&type=carousel" class="btn btn-danger btn-sm mt-2" onclick="return confirm('ต้องการลบรูปนี้หรือไม่?')">ลบ</a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- Section Images -->
    <h4 class="mb-3">🖼 Section Images</h4>
    <div class="row g-3">
        <?php
        $result = $conn->query("SELECT * FROM carousel_section_images ORDER BY id DESC");
        while ($row = $result->fetch_assoc()):
        ?>
            <div class="col-md-4 text-center">
                <div class="card shadow-sm p-2">
                    <img src="../<?= htmlspecialchars($row['image_path']) ?>" class="img-fluid image-thumb" style="height: 180px;">
                    <a href="?delete=<?= $row['id'] ?>&type=section" class="btn btn-danger btn-sm mt-2" onclick="return confirm('ต้องการลบรูปนี้หรือไม่?')">ลบ</a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>
</body>
</html>
