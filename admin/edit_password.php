<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login_admin.php");
    exit;
}
include '../config/config.php';

$id = intval($_GET['id']);
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $old_password_input = $_POST['old_password'];
    $new_password = $_POST['password'];

    // ดึงรหัสผ่านเดิมจาก DB
    $stmt = $conn->prepare("SELECT password FROM admin_users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // ตรวจสอบรหัสเก่า
    if ($user && password_verify($old_password_input, $user['password'])) {
        $hashed_new = password_hash($new_password, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE admin_users SET password = ? WHERE id = ?");
        $update->bind_param("si", $hashed_new, $id);
        $update->execute();
        header("Location: admin_manage_users.php");
        exit;
        
    } else {
        $error = "❌ รหัสผ่านเดิมไม่ถูกต้อง";
    }
}
?>
