<?php
session_start();
include 'config/config.php';

$username = $_POST['username'];
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if (password_verify($password, $row['password'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header("Location: admin_panel.php");
        exit;
    } else {
        echo "❌ รหัสผ่านไม่ตรง <a href='login_admin.php'>ลองใหม่</a>";
    }
} else {
    echo "❌ ไม่พบผู้ใช้นี้ในระบบ <a href='login_admin.php'>ลองใหม่</a>";
}?>