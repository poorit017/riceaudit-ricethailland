<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login_admin.php");
    exit;
}
include '../config/config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>จัดการผู้ดูแลระบบ</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">

    <h1 class="text-2xl font-bold mb-6">👤 จัดการผู้ดูแลระบบ</h1>

    <!-- ฟอร์มเพิ่มผู้ใช้ -->
    <form method="POST" action="admin_register_user.php" class="bg-white p-6 rounded shadow-md mb-8 max-w-md">
        <h2 class="text-xl mb-4 font-semibold">➕ เพิ่มแอดมินใหม่</h2>
        <input type="text" name="username" placeholder="Username" required class="w-full p-2 mb-4 border rounded">
        <input type="password" name="password" placeholder="Password" required class="w-full p-2 mb-4 border rounded">
        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded w-full">สร้างผู้ใช้</button>
    </form>

    <!-- ตารางรายชื่อ -->
    <h2 class="text-xl font-semibold mb-4">📋 รายชื่อผู้ดูแลระบบ</h2>
    <table class="w-full bg-white rounded shadow-md">
        <thead>
<!--             <tr class="bg-gray-200 text-left">
                <th class="p-3">#</th>
                <th class="p-3">Username</th>
                <th class="p-3">จัดการ</th>
            </tr> -->
            <tr class="bg-gray-200 text-left">
    <th class="p-3">#</th>
    <th class="p-3">Username</th>
    <th class="p-3">จัดการ</th>
</tr>

        </thead>
        <tbody>
            <?php
             if (isset($_SESSION['success_message'])): ?>
                <div class="bg-green-100 text-green-700 border border-green-400 px-4 py-3 rounded mb-6 shadow-md" id="alertBox">
                    <?= $_SESSION['success_message'] ?>
                    <button onclick="document.getElementById('alertBox').style.display='none'" class="float-right font-bold">&times;</button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif;
            
            $result = $conn->query("SELECT * FROM admin_users");
            $i = 1;
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                <td class='p-3 border-t'>$i</td>
                <td class='p-3 border-t'>{$row['username']}</td>
                <td class='p-3 border-t space-x-2'>
                    <a href='admin_edit_user.php?id={$row['id']}' class='bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600'>แก้ไข</a>
                    <a href='admin_delete_user.php?id={$row['id']}' class='bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600'>ลบ</a>
                </td>
            </tr>";            
                $i++;
            }
            ?>
        </tbody>
    </table>
    <div class="mt-8">
        <a href="../admin_panel.php" class='bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 '>← กลับไปหน้าแอดมิน</a>
    </div>
</body>
</html>
