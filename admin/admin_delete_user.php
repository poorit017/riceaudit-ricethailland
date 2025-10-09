<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login_admin.php");
    exit;
}
include '../config/config.php';

$id = intval($_GET['id']);
$stmt = $conn->prepare("DELETE FROM admin_users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: admin_manage_users.php");
exit;
?>
