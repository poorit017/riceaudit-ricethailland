<?php
require_once("../config/config_db.php");
$pdo = connectDB();

if (isset($_GET['id'])) {
    $member_id = $_GET['id'];

    try {
        // เริ่ม transaction
        $pdo->beginTransaction();

        // ดึงรหัสบุคคล (ID_Member) จากตาราง member โดยใช้ id หลัก
        $stmtSelect = $pdo->prepare("SELECT ID_Member FROM member WHERE Member_id = ?");
        $stmtSelect->execute([$member_id]);
        $result = $stmtSelect->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            throw new Exception("ไม่พบข้อมูลสมาชิกที่ต้องการลบ");
        }

        $id_member = $result['ID_Member'];

        // ลบข้อมูลการอบรมจากตาราง member
        $stmtDelete = $pdo->prepare("DELETE FROM member WHERE Member_id = ?");
        $stmtDelete->execute([$member_id]);

        // ตรวจสอบว่ายังมีข้อมูลการอบรมของบุคคลนี้อยู่หรือไม่
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM member WHERE ID_Member = ?");
        $stmtCheck->execute([$id_member]);
        $count = $stmtCheck->fetchColumn();

        // ถ้าไม่มีข้อมูลการอบรมของบุคคลนี้แล้ว ให้ลบจาก memberinfo
        if ($count == 0) {
            $stmtDeleteInfo = $pdo->prepare("DELETE FROM memberinfo WHERE Memberinfo_id = ?");
            $stmtDeleteInfo->execute([$id_member]);
        }

        $pdo->commit();
        header("Location: training_manage.php?message=delete_success");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
} else {
    echo "ไม่พบข้อมูลที่ต้องการลบ";
}
?>
