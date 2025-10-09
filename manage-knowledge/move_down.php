<?php
include_once "../config/config.php";

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // ค้นหาข้อมูลปัจจุบัน
    $stmt = $conn->prepare("SELECT id, sort_order FROM knowledge WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $current = $stmt->get_result()->fetch_assoc();

    if ($current) {
        // ค้นหาข้อมูลที่อยู่ถัดไป
        $stmt2 = $conn->prepare("SELECT id, sort_order FROM knowledge WHERE sort_order > ? ORDER BY sort_order ASC LIMIT 1");
        $stmt2->bind_param("i", $current['sort_order']);
        $stmt2->execute();
        $next = $stmt2->get_result()->fetch_assoc();

        if ($next) {
            // สลับ sort_order
            $stmt3 = $conn->prepare("UPDATE knowledge SET sort_order = ? WHERE id = ?");
            $stmt3->bind_param("ii", $next['sort_order'], $current['id']);
            $stmt3->execute();

            $stmt4 = $conn->prepare("UPDATE knowledge SET sort_order = ? WHERE id = ?");
            $stmt4->bind_param("ii", $current['sort_order'], $next['id']);
            $stmt4->execute();
        }
    }
}

header("Location: index.php");
exit;
