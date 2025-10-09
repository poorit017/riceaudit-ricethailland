<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login_admin.php");
    exit;
}
require '../config/config_db.php';
$pdo = connectDB(); // <--- เพิ่มบรรทัดนี้

// ตรวจสอบว่ามี id ส่งมาหรือไม่
if (!isset($_GET['id'])) {
    echo "ไม่พบรหัสสมาชิกที่ต้องการแก้ไข";
    exit;
}


$id = $_GET['id'];

// ดึงข้อมูลจากฐานข้อมูล
$stmt = $pdo->prepare("SELECT * FROM memberinfo WHERE Memberinfo_id = ?");
$stmt->execute([$id]);
$member = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$member) {
    echo "ไม่พบข้อมูลสมาชิก";
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขข้อมูลสมาชิก</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-xl font-bold mb-4">📝 แก้ไขข้อมูลสมาชิก</h1>
        <form action="update_memberinfo.php" method="POST" class="space-y-4">
            <input type="hidden" name="Memberinfo_id" value="<?= $member['Memberinfo_id'] ?>">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold">คำนำหน้า</label>
                    <input type="text" name="Memberinfo_titlename" value="<?= $member['Memberinfo_titlename'] ?>" class="w-full border p-2 rounded">
                </div>
                <div>
                    <label class="block font-semibold">ชื่อ</label>
                    <input type="text" name="Memberinfo_fname" value="<?= $member['Memberinfo_fname'] ?>" class="w-full border p-2 rounded">
                </div>
                <div>
                    <label class="block font-semibold">นามสกุล</label>
                    <input type="text" name="Memberinfo_lname" value="<?= $member['Memberinfo_lname'] ?>" class="w-full border p-2 rounded">
                </div>
                <div>
                    <label class="block font-semibold">เบอร์โทร</label>
                    <input type="text" name="Memberinfo_tel" value="<?= $member['Memberinfo_tel'] ?>" class="w-full border p-2 rounded">
                </div>
                <div>
                    <label class="block font-semibold">หน่วยงาน</label>
                    <input type="text" name="Memberinfo_agency" value="<?= $member['Memberinfo_agency'] ?>" class="w-full border p-2 rounded">
                </div>
                <div>
                    <label class="block font-semibold">ประเภทหน่วยงาน</label>
                    <input type="text" name="Memberinfo_typeagency" value="<?= $member['Memberinfo_typeagency'] ?>" class="w-full border p-2 rounded">
                </div>
                <div>
                    <label class="block font-semibold">ตำแหน่ง</label>
                    <input type="text" name="Memberinfo_pos" value="<?= $member['Memberinfo_pos'] ?>" class="w-full border p-2 rounded">
                </div>
                <div>
                    <label class="block font-semibold">ประเภทตำแหน่ง</label>
                    <input type="text" name="Memberinfo_typepos" value="<?= $member['Memberinfo_typepos'] ?>" class="w-full border p-2 rounded">
                </div>
            </div>

            <hr class="my-4">

            <?php for ($i = 1; $i <= 4; $i++): ?>
            <div class="grid grid-cols-5 gap-2">
                <div>
                    <label class="block text-sm">ระดับ<?= $i ?></label>
                    <input type="text" name="Memberinfo_c<?= $i ?>" value="<?= $member["Memberinfo_c$i"] ?>" class="w-full border p-1 rounded">
                </div>
                <div>
                    <label class="block text-sm">วุฒิ<?= $i ?></label>
                    <input type="text" name="Memberinfo_edu<?= $i ?>" value="<?= $member["Memberinfo_edu$i"] ?>" class="w-full border p-1 rounded">
                </div>
                <div>
                    <label class="block text-sm">สาขา<?= $i ?></label>
                    <input type="text" name="Memberinfo_branch<?= $i ?>" value="<?= $member["Memberinfo_branch$i"] ?>" class="w-full border p-1 rounded">
                </div>
                <div>
                    <label class="block text-sm">คณะ<?= $i ?></label>
                    <input type="text" name="Memberinfo_faculty<?= $i ?>" value="<?= $member["Memberinfo_faculty$i"] ?>" class="w-full border p-1 rounded">
                </div>
                <div>
                    <label class="block text-sm">สถาบัน<?= $i ?></label>
                    <input type="text" name="Memberinfo_inst<?= $i ?>" value="<?= $member["Memberinfo_inst$i"] ?>" class="w-full border p-1 rounded">
                </div>
            </div>
            <?php endfor; ?>

            <div class="mt-4">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">💾 บันทึกข้อมูล</button>
                <a href="index.php" class="inline-block bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded text-sm">
    ← ย้อนกลับ
</a>
            </div>
        </form>
    </div>
</body>
</html>
