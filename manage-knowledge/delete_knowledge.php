<?php
include '../config/config.php'; 

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // 🔍 ค้นหา path ของไฟล์
    $sql = "SELECT file_path FROM knowledge WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($filePath);
    $stmt->fetch();
    $stmt->close();

    // 🗑️ ลบไฟล์จากโฟลเดอร์
    if (!empty($filePath) && file_exists($filePath)) {
        unlink($filePath);
    }

    // ❌ ลบข้อมูลจากฐานข้อมูล
    $sql = "DELETE FROM knowledge WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // ✅ ลบสำเร็จ
        header("Location: index.php?status=deleted");
        exit;
    } else {
        // ❌ ลบไม่สำเร็จ
        header("Location: index.php?status=error");
        exit;
    }
} else {
    // ❌ ไม่มี ID ส่งมา
    header("Location: index.php?status=invalid");
    exit;
}
?>
