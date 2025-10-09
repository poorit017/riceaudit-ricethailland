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

    // ดึงรหัสผ่านเดิมจากฐานข้อมูล
    $stmt = $conn->prepare("SELECT password FROM admin_users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // ตรวจสอบรหัสผ่านเดิม
    if ($user && password_verify($old_password_input, $user['password'])) {
        $hashed_new = password_hash($new_password, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE admin_users SET password = ? WHERE id = ?");
        $update->bind_param("si", $hashed_new, $id);
        $update->execute();

        $_SESSION['success_message'] = "✅ เปลี่ยนรหัสผ่านเรียบร้อยแล้ว";
        header("Location: admin_manage_users.php");
        exit;
    } else {
        $error = "❌ รหัสผ่านเดิมไม่ถูกต้อง";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>แก้ไขรหัสผ่าน</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">

    <form method="POST" class="bg-white p-6 rounded shadow-md max-w-md mx-auto">
        <h2 class="text-xl mb-4 font-semibold">🔒 เปลี่ยนรหัสผ่าน</h2>

        <?php if (!empty($error)): ?>
            <div class="bg-red-100 text-red-700 p-2 mb-4 rounded"><?= $error ?></div>
        <?php endif; ?>

        <label class="block mb-2 font-medium">รหัสผ่านเดิม:</label>
        <input type="password" name="old_password" placeholder="Old Password" required class="w-full p-2 mb-4 border rounded">

        <label class="block mb-2 font-medium">รหัสผ่านใหม่:</label>
        <input type="password" name="password" placeholder="New Password" required class="w-full p-2 mb-4 border rounded">

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded w-full">อัปเดต</button>
    </form>

    <div class="text-center mt-6">
        <a href="admin_manage_users.php" class='bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 '>← ย้อนกลับ</a>
        
    </div>

</body>
</html>
