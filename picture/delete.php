<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login_admin.php");
    exit;
}
include '../config/config.php';

// ฟังก์ชันลบโฟลเดอร์และไฟล์ทั้งหมดในนั้น
function deleteFolder($folderPath) {
    if (!is_dir($folderPath)) return;
    $files = array_diff(scandir($folderPath), ['.', '..']);
    foreach ($files as $file) {
        $filePath = "$folderPath/$file";
        is_dir($filePath) ? deleteFolder($filePath) : unlink($filePath);
    }
    rmdir($folderPath);
}

if (isset($_GET['type']) && isset($_GET['id'])) {
    $type = $_GET['type'];
    $id = (int)$_GET['id'];

    if ($type === 'year') {
        // ลบไฟล์ picturexxxx.html
        $getYear = $conn->query("SELECT year_name FROM years WHERE id = $id")->fetch_assoc();
        $yearName = $getYear['year_name'];
        $filename = "picture$yearName.html";
        if (file_exists($filename)) {
            unlink($filename);
        }

        // ลบโฟลเดอร์ uploads/ปี
        $folderToDelete = "pictureyear/uploads/$yearName";
        deleteFolder($folderToDelete);

        // ลบหมวดหมู่ + รูปภาพ
        $categories = $conn->query("SELECT id FROM categories WHERE year_id = $id");
        while ($cat = $categories->fetch_assoc()) {
            $catId = $cat['id'];
            $images = $conn->query("SELECT file_path FROM images WHERE category_id = $catId");
            while ($img = $images->fetch_assoc()) {
                unlink($img['file_path']);
            }
            $conn->query("DELETE FROM images WHERE category_id = $catId");
        }
        $conn->query("DELETE FROM categories WHERE year_id = $id");
        $conn->query("DELETE FROM years WHERE id = $id");
    }

    if ($type === 'category') {
        $images = $conn->query("SELECT file_path FROM images WHERE category_id = $id");
        while ($img = $images->fetch_assoc()) {
            unlink($img['file_path']);
        }
        $conn->query("DELETE FROM images WHERE category_id = $id");
        $conn->query("DELETE FROM categories WHERE id = $id");
    }

    if ($type === 'image') {
        $img = $conn->query("SELECT file_path FROM images WHERE id = $id")->fetch_assoc();
        unlink($img['file_path']);
        $conn->query("DELETE FROM images WHERE id = $id");
    }
}

header("Location: manage_picture.php");
exit;
?>
