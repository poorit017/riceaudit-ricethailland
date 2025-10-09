<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login_admin.php");
    exit;
}
require '../config/config_db.php';
$pdo = connectDB();

// ดึงปีทั้งหมด
$years = $pdo->query("SELECT DISTINCT Member_year FROM member ORDER BY Member_year DESC")->fetchAll(PDO::FETCH_COLUMN);

// ดึงหลักสูตรทั้งหมด
$courses = $pdo->query("SELECT DISTINCT Member_course FROM member ORDER BY Member_course ASC")->fetchAll(PDO::FETCH_COLUMN);

// ดึงประเภทหน่วยงานจาก memberinfo
$types = $pdo->query("SELECT DISTINCT Memberinfo_typeagency 
                      FROM memberinfo 
                      WHERE Memberinfo_typeagency IS NOT NULL AND Memberinfo_typeagency <> '' 
                      ORDER BY Memberinfo_typeagency")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>Export Excel รายงานอบรม</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .btn-yellow {
            background-color: #eab308;
            color: white;
        }

        .btn-yellow:hover {
            background-color: #ca8a04;
            color: white;
        }
    </style>
</head>

<body class="bg-light">

    <div class="container mt-5">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">📊 Export Excel รายงานอบรม</h4>
            </div>

            <div class="card-body">
                <form method="post" action="export_excel.php">
                    <div class="mb-3">
                        <label class="form-label fw-bold">เลือกปี</label>
                        <select name="years[]" class="form-select" multiple size="5" required>
                            <?php foreach ($years as $y): ?>
                                <option value="<?= htmlspecialchars($y) ?>"><?= htmlspecialchars($y) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text text-danger">กด Ctrl (Windows) หรือ Command (Mac) เพื่อเลือกหลายปี</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">เลือกหลักสูตร</label>
                        <select name="courses[]" class="form-select" multiple size="8" required>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text text-danger">กด Ctrl (Windows) หรือ Command (Mac) เพื่อเลือกหลายหลักสูตร</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">เลือกประเภทหน่วยงาน</label>
                        <select name="types[]" class="form-select" multiple size="8" required>
                            <?php foreach ($types as $t): ?>
                                <option value="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text text-danger">กด Ctrl (Windows) หรือ Command (Mac) เพื่อเลือกหลายประเภทหน่วยงาน</div>
                    </div>

                    <button type="submit" class="btn btn-success btn-lg">
                        📥 ดาวน์โหลด Excel
                    </button>
                    <a href="index.php" class="btn btn-warning btn-lg ms-2">
                        ← ย้อนกลับ
                    </a>
                </form>
            </div>
        </div>
    </div>

</body>

</html>