<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ระบบจัดการข้อมูลการอบรม</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md text-center">
        <h1 class="text-2xl font-bold text-green-700 mb-4">🎉 สวัสดีคุณ <?= htmlspecialchars($_SESSION['admin_username']) ?>!</h1>
        
        <div class="space-y-4">
        <a href="training_manage.php" class="block w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                ➕ ข้อมูลผู้ผ่านการอบรม
            </a>
        <a href="list_member.php" class="block w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                ➕ แก้ไขข้อมูลส่วนตัวผู้ผ่านการอบรม
            </a>
        <a href="upload_data.php" class="block w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                ➕ อัพโหลดข้อมูลผู้ผ่านการอบรม
            </a>
            <a href="upload_certificate.php" class="block w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                ➕ อัพโหลดใบประกาศนียบัตรผู้ผ่านการอบรม
            </a>
                    <a href="filter_export.php" class="block w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                ➕ ดาวน์โหลดข้อมูลการอบรมและข้อมูลผู้อบรม
            </a>
            <a href="../admin_panel.php" class="block w-full bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded">
                 ← ย้อนกลับ
            </a>
        </div>
    </div>
</body>
</html>
