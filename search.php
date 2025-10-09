<?php
require_once("config/config_db.php");
require_once("navbar.html");

$strKeyword = $strKeyword2 = $strKeyword3 = $strKeyword4 = "";

try {
    $pdo = connectDB();

    // ดึงหลักสูตร
    $stmt = $pdo->query("SELECT DISTINCT Member_course FROM member ");
    $courses = $stmt->fetchAll();

    // ดึงปี
    $stmt2 = $pdo->query("SELECT DISTINCT Member_year FROM member WHERE Member_year IS NOT NULL ORDER BY Member_year ASC ");
    $years = $stmt2->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <title>ค้นหาผู้อบรม</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <link href="css/search1.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2 class="text-center">ค้นหาผู้อบรม</h2>
        <form method="post" action="">
            <div class="row g-3 align-items-center">
                <div class="col-md-3">
                    <label class="form-label">ค้นหาชื่อ:</label>
                    <input class="form-control" name="txtKeyword" type="text" value="<?= htmlspecialchars($_POST['txtKeyword'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">นามสกุล:</label>
                    <input class="form-control" name="txtKeyword2" type="text" value="<?= htmlspecialchars($_POST['txtKeyword2'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">หลักสูตร:</label>
                    <select class="form-select" name="txtKeyword3">
                        <option value=""></option>
                        <?php foreach ($courses as $course) : ?>
                            <option value="<?= htmlspecialchars($course['Member_course']) ?>" <?= (($_POST['txtKeyword3'] ?? '') == $course['Member_course']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($course['Member_course']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">ปี:</label>
                    <select class="form-select" name="txtKeyword4">
                        <option value=""></option>
                        <?php foreach ($years as $year) : ?>
                            <option value="<?= htmlspecialchars($year['Member_year']) ?>" <?= (($_POST['txtKeyword4'] ?? '') == $year['Member_year']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($year['Member_year']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="text-center mt-3">
                <button type="submit" class="btn btn-primary px-4">ค้นหา</button>
            </div>
        </form>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $conditions = [];
            $params = [];

            if (!empty($_POST['txtKeyword'])) {
                $conditions[] = "Member_firstname LIKE :fname";
                $params[':fname'] = "%" . $_POST['txtKeyword'] . "%";
            }
            if (!empty($_POST['txtKeyword2'])) {
                $conditions[] = "Member_lastname LIKE :lname";
                $params[':lname'] = "%" . $_POST['txtKeyword2'] . "%";
            }
            if (!empty($_POST['txtKeyword3'])) {
                $conditions[] = "Member_course = :course";
                $params[':course'] = $_POST['txtKeyword3'];
            }
            if (!empty($_POST['txtKeyword4'])) {
                $conditions[] = "Member_year = :year";
                $params[':year'] = $_POST['txtKeyword4'];
            }

            $where = '';
            if (count($conditions) > 0) {
                $where = "WHERE " . implode(" AND ", $conditions);
            }

            $sql = "SELECT Member_id,Member_titlename, Member_firstname, Member_lastname,Member_course,Member_year FROM member $where   ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll();
            if ($results) {
                echo '<div class="table-responsive mt-4">';
                echo '<div class="table-container tableFixHead">';
                echo '<table class="table table-bordered table-striped">';
                echo '<thead class="text-center"><tr><th>ลำดับ</th><th>ชื่อ - นามสกุล</th><th>หลักสูตร</th><th>ปี</th><th>ดูรายระเอียด</th></tr></thead><tbody>';
                $count = 1;
                foreach ($results as $row) {
                    echo '<tr>';
                    echo '<td class="text-center">' . $count . '</td>';
                    echo '<td>' . htmlspecialchars($row['Member_titlename']) .
                        htmlspecialchars($row['Member_firstname']) . ' ' .
                        htmlspecialchars($row['Member_lastname']) . '</td>';
                    echo '<td class="text-center">' . htmlspecialchars($row['Member_course']) . '</td>';
                    echo '<td class="text-center">' . htmlspecialchars($row['Member_year']) . '</td>';
                    echo '<td align="center"><a class="btn btn-info" href="detail.php?Member_id=' . $row["Member_id"] . '"> กดที่นี่</td>';
                    echo '</tr>';
                    $count++;
                }
                echo '</tbody></table></div></div>';
            } else {
                echo '<p class="text-center mt-4 text-danger">ไม่พบข้อมูล</p>';
            }
        }
        ?>
    </div>
</body>
</html>