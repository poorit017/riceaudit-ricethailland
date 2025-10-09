<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login_admin.php");
    exit;
}

ini_set('max_file_uploads', 1000);
require '../vendor/autoload.php';
require '../config/config_db.php';

$pdo = connectDB();

// ดึงปีและหลักสูตร
$years = $pdo->query("SELECT DISTINCT Member_year FROM member ORDER BY Member_year DESC")->fetchAll(PDO::FETCH_COLUMN);
$courses = $pdo->query("SELECT DISTINCT Member_course FROM member ORDER BY Member_course ASC")->fetchAll(PDO::FETCH_COLUMN);

$results = [];
$name = isset($_GET['name']) ? trim($_GET['name']) : '';
$year = isset($_GET['year']) ? $_GET['year'] : '';
$course = isset($_GET['course']) ? $_GET['course'] : '';

if (isset($_GET['search'])) {
    $query = "SELECT * FROM member WHERE 1=1";
    $params = [];
    if (!empty($name)) {
        $query .= " AND (Member_firstname LIKE ? OR Member_lastname LIKE ?)";
        $params[] = "%$name%";
        $params[] = "%$name%";
    }
    if (!empty($year)) {
        $query .= " AND Member_year = ?";
        $params[] = $year;
    }
    if (!empty($course)) {
        $query .= " AND Member_course = ?";
        $params[] = $course;
    }
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>อัปโหลดใบประกาศนียบัตร</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #f9fafb;
            padding-top: 60px;
        }
        .container-box {
            background-color: #fff;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .button {
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: 0.2s;
        }
        .button:hover {
            background-color: #218838;
            text-decoration: none;
        }
        .heading {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 25px;
        }
        .form-label {
            margin-bottom: 25px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="text-center mb-5">
            <h1 class="heading">📄 อัปโหลดใบประกาศนียบัตร</h1>
        </div>
<a href="index.php" class="inline-block bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-1 px-2 rounded text-sm mb-4">
    ← ย้อนกลับ
</a>


        <!-- 🔍 ฟอร์มค้นหา -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="get" class="row g-3">
                    <div class="col-md-4">
                        <label for="name" class="form-label">ค้นหาด้วยชื่อหรือนามสกุล</label>
                        <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($name) ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="year" class="form-label">ปีการอบรม</label>
                        <select id="year" name="year" class="form-select">
                            <option value="">-- เลือกปี --</option>
                            <?php foreach ($years as $y): ?>
                                <option value="<?= htmlspecialchars($y) ?>" <?= ($year == $y) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($y) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="course" class="form-label">หลักสูตร</label>
                        <select id="course" name="course" class="form-select">
                            <option value="">-- เลือกหลักสูตร --</option>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?= htmlspecialchars($c) ?>" <?= ($course == $c) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" name="search" class="btn btn-primary w-100 shadow">ค้นหา</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 🔽 แสดงตาราง -->
        <?php if (!empty($results)): ?>
            <form method="post" action="save_certificate.php" enctype="multipart/form-data">
                <div class="table-responsive shadow-sm rounded">
                    <table class="table table-bordered table-hover align-middle bg-white">
                        <thead class="table-primary text-center">
                            <tr>
                                <th>ลำดับ</th>
                                <th>ชื่อ</th>
                                <th>นามสกุล</th>
                                <th>หลักสูตร</th>
                                <th>รอบ</th>
                                <th>ปี</th>
                                <th>ใบประกาศ</th>
                                <th>อัปโหลด</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $index => $row): ?>
                                <tr>
                                    <td class="text-center"><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($row['Member_firstname']) ?></td>
                                    <td><?= htmlspecialchars($row['Member_lastname']) ?></td>
                                    <td><?= htmlspecialchars($row['Member_course']) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($row['Member_time']) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($row['Member_year']) ?></td>
                                    <td class="text-center">
                                        <?php if (!empty($row['Member_certificate'])): ?>
                                            <a href="../web/uploads/<?= $row['Member_certificate'] ?>" target="_blank" class="btn btn-sm btn-outline-info">📄 ดู</a>
                                        <?php else: ?>
                                            <span class="text-danger">❌ ไม่มี</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <input type="hidden" name="member_id[]" value="<?= $row['Member_id'] ?>">
                                        <input type="file" name="certificate[]" class="form-control form-control-sm">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-success px-4 py-2 shadow">✅ อัปโหลดไฟล์ที่เลือกไว้</button>
                </div>
            </form>
        <?php elseif (isset($_GET['search'])): ?>
            <div class="alert alert-warning text-center shadow mt-4">
                ไม่พบข้อมูลที่ค้นหา 🕵️‍♂️
            </div>
        <?php endif; ?>
    </div>
</body>
</html>