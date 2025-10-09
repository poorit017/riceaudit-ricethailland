<?php
require 'config/config_db.php';
require_once("navbar.html");
$pdo = connectDB();

// ดึงปีทั้งหมด
$years = $pdo->query("SELECT DISTINCT Member_year FROM member ORDER BY Member_year")->fetchAll(PDO::FETCH_COLUMN);

// ดึงหลักสูตรทั้งหมดตามลำดับที่เจอ
$courses_stmt = $pdo->query("SELECT Member_course, MIN(ID_Member) as first_seen FROM member GROUP BY Member_course ORDER BY first_seen ASC");
$courses = [];
while($row = $courses_stmt->fetch(PDO::FETCH_ASSOC)){
    $courses[] = $row['Member_course'];
}

// เตรียมข้อมูล pivot
$data = [];
foreach ($years as $year) {
    foreach ($courses as $course) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM member WHERE Member_year = ? AND Member_course = ?");
        $stmt->execute([$year, $course]);
        $data[$year][$course] = $stmt->fetchColumn();
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>Pivot Report</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="css/search1.css" rel="stylesheet">
<style>
.pivot-table { width: 100%; border-collapse: collapse; }
.pivot-table th, .pivot-table td { text-align:center; vertical-align: middle; min-width: 80px; padding: 5px; }
.pivot-table th { background-color: #343a40; color: white; }
.pivot-table tfoot th { background-color: #17a2b8; color: white; }
.pivot-table td.highlight { background-color: #ffc107; font-weight: bold; }

.checkbox-container { 
    margin-bottom: 15px; 
    display: flex; 
    flex-wrap: wrap; 
    gap: 5px; 
    justify-content: center;
}
.custom-checkbox { 
    display:inline-flex; 
    align-items: center;
    border:1px solid #ccc; 
    border-radius:5px; 
    padding:5px 10px; 
    cursor:pointer; 
    user-select:none; 
    transition:0.2s;
    background-color: #007bff;
    color: white;
}
.custom-checkbox input { display:none; }
.custom-checkbox.inactive { 
    background-color: #f8f9fa; 
    color: #555; 
    border-color: #ccc; 
}

/* Responsive ปรับ checkbox เป็นแถวเดียวในมือถือ */
@media (max-width: 576px) {
    .custom-checkbox { flex: 1 1 100%; justify-content: center; }
}
</style>
</head>
<body class="p-4">

<h1 class="mb-4 text-center ">📊 รายงานผู้เข้าอบรม</h1>

<div class="card shadow-lg mb-4">
  <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
    <span>เลือกหลักสูตร</span>
    <button id="checkAllBtn" class="btn btn-sm btn-light">แสดงทั้งหมด</button>
  </div>
  <div class="card-body">
    <div class="checkbox-container">
      <?php foreach($courses as $course): ?>
          <label class="custom-checkbox active">
              <input class="course-checkbox" type="checkbox" value="<?= htmlspecialchars($course) ?>" checked>
              <span class="badge bg-primary"><?= htmlspecialchars($course) ?></span>
          </label>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div class="card shadow-lg">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered table-striped table-hover pivot-table align-middle" id="pivotTable">
        <thead class="table-dark sticky-top">
          <tr>
              <th>ปี</th>
              <?php foreach($courses as $course): ?>
                  <th class="course-col"><?= htmlspecialchars($course) ?></th>
              <?php endforeach; ?>
              <th>รวม</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($years as $year): ?>
          <tr>
              <th><?= $year ?></th>
              <?php foreach($courses as $course): ?>
                  <td class="course-cell"><?= number_format($data[$year][$course] ?? 0) ?></td>
              <?php endforeach; ?>
              <th class="row-total"></th>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr class="table-info">
              <th>รวม</th>
              <?php foreach($courses as $course): ?>
                  <th class="col-total"></th>
              <?php endforeach; ?>
              <th class="grand-total"></th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>

<script>
function updateTotals() {
    const table = document.getElementById('pivotTable');
    const checkboxes = document.querySelectorAll('.course-checkbox');
    const activeCourses = Array.from(checkboxes).filter(c => c.checked).map(c => c.value);

    const headerCells = table.querySelectorAll('thead .course-col');

    // ซ่อน/แสดงหัวตาราง
    headerCells.forEach((th, i) => {
        if (activeCourses.includes(th.textContent)) {
            th.style.display = '';
        } else {
            th.style.display = 'none';
        }
    });

    // ซ่อน/แสดงเซลล์ในแต่ละแถว
    table.querySelectorAll('tbody tr').forEach(tr => {
        tr.querySelectorAll('.course-cell').forEach((td, i) => {
            const courseName = headerCells[i].textContent;
            if (activeCourses.includes(courseName)) {
                td.style.display = '';
                td.classList.toggle('highlight', parseInt(td.textContent) > 0);
            } else {
                td.style.display = 'none';
                td.classList.remove('highlight');
            }
        });
    });

    // รวมแถว
    table.querySelectorAll('tbody tr').forEach(tr => {
        let sum = 0;
        tr.querySelectorAll('.course-cell').forEach(td => {
            if (td.style.display !== 'none') {
                sum += parseInt(td.textContent) || 0;
            }
        });
        tr.querySelector('.row-total').textContent = sum;
    });

    // รวมคอลัมน์
    const colTotals = Array(headerCells.length).fill(0);
    table.querySelectorAll('tbody tr').forEach(tr => {
        tr.querySelectorAll('.course-cell').forEach((td, i) => {
            if (td.style.display !== 'none') {
                colTotals[i] += parseInt(td.textContent) || 0;
            }
        });
    });

    // แสดงผลรวมคอลัมน์ (ซ่อนคอลัมน์ด้วย)
    const colThs = table.querySelectorAll('tfoot .col-total');
    colThs.forEach((th, i) => {
        if (headerCells[i].style.display === 'none') {
            th.style.display = 'none';
        } else {
            th.style.display = '';
            th.textContent = colTotals[i];
        }
    });

    // รวมทั้งหมด
    const grandTotal = colTotals.reduce((a, b, i) => {
        return headerCells[i].style.display !== 'none' ? a + b : a;
    }, 0);
    table.querySelector('.grand-total').textContent = grandTotal;
}

// Event checkbox
document.querySelectorAll('.course-checkbox').forEach(cb => {
    cb.addEventListener('change', () => {
        cb.parentElement.classList.toggle('inactive', !cb.checked);
        updateTotals();
    });
});

// ปุ่มแสดงทั้งหมด
document.getElementById('checkAllBtn').addEventListener('click', () => {
    document.querySelectorAll('.course-checkbox').forEach(cb => {
        cb.checked = true;
        cb.parentElement.classList.remove('inactive');
    });
    updateTotals();
});

// เริ่มต้น
updateTotals();
</script>

</body>
</html>
