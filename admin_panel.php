<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login_admin.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md text-center">
        <h1 class="text-2xl font-bold text-green-700 mb-4">🎉 สวัสดีคุณ <?= htmlspecialchars($_SESSION['admin_username']) ?>!</h1>
        
        <div class="space-y-4">
        <a href="admin/admin_manage_users.php" class="block w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                ➕ ไปจัดการแอดมิน
            </a>
        <a href="manage-carousel/admin_images.php" class="block w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                ➕ ไปจัดการรูปภาพ Carousel
            </a>
            <a href="picture/manage_picture.php" class="block w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                ➕ ไปจัดการรูปภาพ
            </a>
            <a href="news/news_admin.php" class="block w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                ➕ ไปจัดประชาสัมพันธ์
            </a>
            <a href="manage-knowledge" class="block w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                ➕ ไปจัดการเอกสารองค์ความรู้
            </a>
            <a href="manage-member" class="block w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                ➕ ไปจัดการหน้าข้อมูลผู้อบรม
            </a>
            <a href="logout.php" class="block w-full bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded">
                🔒 ออกจากระบบ
            </a>
        </div>
    </div>

</body>
</html>
