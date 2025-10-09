<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login_admin.php");
    exit;
}
include '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_ids'])) {
    $ids = $_POST['delete_ids'];
    foreach ($ids as $id) {
        // ดึง path เพื่อลบไฟล์
        $res = $conn->query("SELECT file_path FROM images WHERE id = $id");
        if ($row = $res->fetch_assoc()) {
            $file = $row['file_path'];
            if (file_exists($file)) {
                unlink($file); // ลบไฟล์จริง
            }
        }
        // ลบในฐานข้อมูล
        $conn->query("DELETE FROM images WHERE id = $id");
    }
}

header("Location: manage_picture.php");
exit();
