<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}
require '../config/config_db.php';

$pdo = connectDB();
 if (isset($_GET['message']) && $_GET['message'] === 'update_success'): ?>
<script>
    alert("อัปเดตข้อมูลเรียบร้อยแล้ว!");
</script>
<?php endif; 

// รับค่าจากฟอร์ม (ถ้ามี)
$fname = isset($_GET['fname']) ? trim($_GET['fname']) : '';
$lname = isset($_GET['lname']) ? trim($_GET['lname']) : '';

// สร้างเงื่อนไขค้นหา
$where = [];
$params = [];

if ($fname !== '') {
    $where[] = "Memberinfo_fname LIKE :fname";
    $params[':fname'] = "%$fname%";
}
if ($lname !== '') {
    $where[] = "Memberinfo_lname LIKE :lname";
    $params[':lname'] = "%$lname%";
}

// สร้าง SQL
$sql = "SELECT Memberinfo_id, Memberinfo_fname, Memberinfo_lname, Memberinfo_agency FROM memberinfo";
if (count($where) > 0) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แสดงข้อมูลสมาชิก</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 py-8">
<div class="container mx-auto px-4">
    <h1 class="text-2xl font-bold mb-6">📋 รายชื่อสมาชิก</h1>
<a href="index.php" class="inline-block bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-1 px-2 rounded text-sm mb-4">
    ← ย้อนกลับ
</a>

    <!-- ฟอร์มค้นหา -->
    <form method="get" class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4 max-w-md">
        <div>
            <label for="fname" class="block mb-2 font-semibold">ค้นหาด้วยชื่อ</label>
            
            <input
                type="text"
                id="fname"
                name="fname"
                value="<?= htmlspecialchars($fname) ?>"
                placeholder="ชื่อ"
                class="border border-gray-300 rounded px-3 py-2 w-full"
            >
        </div>
        <div>
            <label for="lname" class="block mb-2 font-semibold">ค้นหาด้วยนามสกุล</label>
            <input
                type="text"
                id="lname"
                name="lname"
                value="<?= htmlspecialchars($lname) ?>"
                placeholder="นามสกุล"
                class="border border-gray-300 rounded px-3 py-2 w-full"
            >
        </div>
        <div class="flex items-end">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 w-full">
                ค้นหา
            </button>
        </div>
    </form>

    <table class="table-auto w-full bg-white shadow-md rounded-lg overflow-hidden">
        <thead class="bg-gray-200">
        <tr>
            <th class="px-4 py-2">ID</th>
            <th class="px-4 py-2">ชื่อ</th>
            <th class="px-4 py-2">นามสกุล</th>
            <th class="px-4 py-2">หน่วยงาน</th>
            <th class="px-4 py-2">จัดการ</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($stmt->rowCount() > 0): ?>
            <?php while ($row = $stmt->fetch()): ?>
                <tr class="border-b hover:bg-gray-100">
                    <td class="px-4 py-2"><?= htmlspecialchars($row['Memberinfo_id']) ?></td>
                    <td class="px-4 py-2"><?= htmlspecialchars($row['Memberinfo_fname']) ?></td>
                    <td class="px-4 py-2"><?= htmlspecialchars($row['Memberinfo_lname']) ?></td>
                    <td class="px-4 py-2"><?= htmlspecialchars($row['Memberinfo_agency']) ?></td>
                    <td class="px-4 py-2">
                        <a href="edit_memberinfo.php?id=<?= htmlspecialchars($row['Memberinfo_id']) ?>" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                            แก้ไข
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" class="text-center py-4 text-gray-600">ไม่พบข้อมูลสมาชิก</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
