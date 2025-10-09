<?php
require '../config/config_db.php';
$pdo = connectDB(); // ← ต้องมี

 if (isset($_GET['message']) && $_GET['message'] === 'delete_success'): ?>
<script>
    alert("ลบข้อมูลเรียบร้อยแล้ว!");
</script>
<?php endif; 


// จากนั้นจึงสามารถใช้ $pdo ได้อย่างปลอดภัย
$stmt = $pdo->prepare("SELECT * FROM member WHERE ...");

// 🔄 ดึงรายการปีและหลักสูตรทั้งหมด
$years = $pdo->query("SELECT DISTINCT Member_year FROM member ORDER BY Member_year DESC")->fetchAll(PDO::FETCH_COLUMN);
$courses = $pdo->query("SELECT DISTINCT Member_course FROM member ORDER BY Member_course ASC")->fetchAll(PDO::FETCH_COLUMN);


// 🔍 เงื่อนไขค้นหา
$where = [];
$params = [];

if (!empty($_GET['name'])) {
    $where[] = "(mi.Memberinfo_fname LIKE ? OR mi.Memberinfo_lname LIKE ?)";
    $params[] = '%' . $_GET['name'] . '%';
    $params[] = '%' . $_GET['name'] . '%';
}

if (!empty($_GET['course'])) {
    $where[] = "m.Member_course LIKE ?";
    $params[] = '%' . $_GET['course'] . '%';
}

if (!empty($_GET['year'])) {
    $where[] = "m.Member_year LIKE ?";
    $params[] = '%' . $_GET['year'] . '%';
}

// 🔗 SQL
$sql = "SELECT m.Member_id, mi.Memberinfo_fname, mi.Memberinfo_lname, m.Member_course, m.Member_year
        FROM member m
        JOIN memberinfo mi ON m.ID_Member = mi.Memberinfo_id";

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการผู้ผ่านการอบรม</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 py-8">
<div class="container mx-auto px-4">
    <h1 class="text-2xl font-bold mb-6">📋 รายชื่อข้อมูลผู้ผ่านการอบรม</h1>
<a href="index.php" class="inline-block bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-1 px-2 rounded text-sm mb-4">
    ← ย้อนกลับ
</a>
    <!-- ฟอร์มค้นหา -->
    <form class="row g-2 mb-4" method="GET">
        <div class="col-md-4">
            <input type="text" name="name" class="form-control" placeholder="ค้นหาชื่อหรือสกุล" value="<?= htmlspecialchars($_GET['name'] ?? '') ?>">
        </div>
        <div class="col-md-3">
            <select name="course" class="form-select">
                <option value="">-- เลือกหลักสูตร --</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= htmlspecialchars($course) ?>" <?= ($_GET['course'] ?? '') == $course ? 'selected' : '' ?>>
                        <?= htmlspecialchars($course) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

                <div class="col-md-2">
            <select name="year" class="form-select">
                <option value="">-- เลือกปี --</option>
                <?php foreach ($years as $year): ?>
                    <option value="<?= htmlspecialchars($year) ?>" <?= ($_GET['year'] ?? '') == $year ? 'selected' : '' ?>>
                        <?= htmlspecialchars($year) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3 d-flex">
            <button type="submit" class="btn btn-primary me-2">ค้นหา</button>
            <a href="training_manage.php" class="btn btn-secondary">รีเซ็ต</a>
        </div>
    </form>

    <!-- ตารางแสดงผล -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped bg-white">
            <thead class="table-dark">
                <tr>
                    <th style="width: 5%">ลำดับ</th>
                    <th>ชื่อ</th>
                    <th>นามสกุล</th>
                    <th>หลักสูตร</th>
                    <th style="width: 10%">ปี</th>
                    <th style="width: 10%">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data)): ?>
                    <tr><td colspan="6" class="text-center">ไม่พบข้อมูล</td></tr>
                <?php else: ?>
                    <?php foreach ($data as $index => $row): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($row['Memberinfo_fname']) ?></td>
                            <td><?= htmlspecialchars($row['Memberinfo_lname']) ?></td>
                            <td><?= htmlspecialchars($row['Member_course']) ?></td>
                            <td><?= htmlspecialchars($row['Member_year']) ?></td>
                            <td>
                                <a href="delete_training.php?id=<?= $row['Member_id'] ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบข้อมูลนี้?');">ลบ</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
