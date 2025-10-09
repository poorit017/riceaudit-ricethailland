<?php
require_once("config/config_db.php");
require_once("navbar.html");
$conn = connectDB(); // PDO

// === 1. ปีทั้งหมด ===
$years_sql = "SELECT DISTINCT Member_year FROM member ORDER BY Member_year";
$years_stmt = $conn->query($years_sql);
$years = $years_stmt->fetchAll(PDO::FETCH_COLUMN);

// === 2. รวมทุกปี ===
$sql_total = "SELECT Member_year, COUNT(*) as countyear FROM member GROUP BY Member_year";
$total_stmt = $conn->query($sql_total);
$total_by_year = array_fill_keys($years, 0);
foreach ($total_stmt as $row) {
    $total_by_year[$row['Member_year']] = $row['countyear'];
}

// === 3. รวมทุกหลักสูตร (ทุกปี) เรียงตามลำดับเจอครั้งแรก ===
$sql_total_course = "
    SELECT Member_course, COUNT(*) as total_count, MIN(ID_Member) as first_seen
    FROM member 
    GROUP BY Member_course
    ORDER BY first_seen ASC
";
$total_course_stmt = $conn->query($sql_total_course);
$total_by_course_all = [];
$course_colors = [];
$color_idx = 0;
foreach ($total_course_stmt as $row) {
    $total_by_course_all[$row['Member_course']] = $row['total_count'];
    $course_colors[$row['Member_course']] = "hsl(".($color_idx*60%360).",70%,60%)";
    $color_idx++;
}

// === 4. ข้อมูลรายปีตามหลักสูตร ===
$sql_courses = "SELECT Member_year, Member_course, COUNT(*) as countcourse, MIN(ID_Member) as first_seen
                FROM member
                GROUP BY Member_year, Member_course
                ORDER BY first_seen ASC";
$courses_stmt = $conn->query($sql_courses);

$data_by_course = [];
foreach ($courses_stmt as $row) {
    $course = $row['Member_course'];
    if (!isset($data_by_course[$course])) {
        $data_by_course[$course] = array_fill_keys($years, 0);
    }
    $data_by_course[$course][$row['Member_year']] = $row['countcourse'];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>Dashboard - Training Stats</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="css/search1.css" rel="stylesheet">
<style>
.card { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); transition: 0.3s; }
.card:hover { transform: translateY(-5px); }
.chart-container { position: relative; height: 500px !important; width: 100% !important; }
</style>
</head>
<body>
<div class="container py-5">
    <h2 class="fw-bold mb-4 text-center">📊 สถิติการอบรม</h2>

    <!-- Dropdown เลือกหลักสูตร -->
    <div class="mb-4 text-center">
        <select class="form-select w-auto d-inline-block" id="courseSelect">
            <option value="overall">ภาพรวมทั้งหมด</option>
            <?php foreach(array_keys($data_by_course) as $i => $course): ?>
            <option value="course-<?php echo $i; ?>"><?php echo htmlspecialchars($course); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

<!-- Overall Tab -->
<div id="overallContent">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card p-4">
                <h5 class="fw-bold text-primary mb-3">จำนวนผู้ผ่านการอบรมรวมทุกปี</h5>
                <div class="chart-container" style="height:500px;">
                    <canvas id="totalChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    

    <div class="row mb-4">
        <div class="col-12">
            <div class="card p-4">
                <h5 class="fw-bold text-warning mb-3">จำนวนผู้เข้าร่วมอบรมรวมทุกหลักสูตร</h5>
                <div class="chart-container" style="height:500px;">
                    <canvas id="totalByCourseChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>


    <!-- Course Charts -->
    <?php $idx=0; foreach($data_by_course as $course=>$data): ?>
    <div class="courseContent" id="course-<?php echo $idx; ?>" style="display:none;">
        <div class="card p-3 mb-4">
            <h5 class="fw-bold text-success mb-3">หลักสูตร <?php echo htmlspecialchars($course); ?></h5>
            <div class="chart-container">
                <canvas id="chart_<?php echo $idx; ?>"></canvas>
            </div>
        </div>
    </div>
    <?php $idx++; endforeach; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// PHP Data
const years = <?php echo json_encode(array_values($years), JSON_UNESCAPED_UNICODE); ?>;
const totalData = <?php echo json_encode(array_values($total_by_year), JSON_UNESCAPED_UNICODE); ?>;
const courseData = <?php echo json_encode($data_by_course, JSON_UNESCAPED_UNICODE); ?>;
const totalByCourseLabels = <?php echo json_encode(array_keys($total_by_course_all), JSON_UNESCAPED_UNICODE); ?>;
const totalByCourseValues = <?php echo json_encode(array_values($total_by_course_all), JSON_UNESCAPED_UNICODE); ?>;
const courseColors = <?php echo json_encode($course_colors, JSON_UNESCAPED_UNICODE); ?>;

// Gradient helper
function createGradient(ctx, color) {
    const gradient = ctx.createLinearGradient(0,0,0,400);
    gradient.addColorStop(0, color);
    gradient.addColorStop(1, 'rgba(255,255,255,0.3)');
    return gradient;
}

// Total by year
new Chart(document.getElementById('totalChart'), {
    type:'line',
    data:{
        labels: years,
        datasets:[{
            label:'ผู้ผ่านการอบรม',
            data: totalData,
            fill:true,
            backgroundColor: ctx => createGradient(ctx.chart.ctx, 'rgba(54,162,235,0.5)'),
            borderColor:'#36A2EB',
            tension:0.3,
            pointBackgroundColor:'#36A2EB',
            pointRadius:5
        }]
    },
    options:{responsive:true, plugins:{legend:{display:true}}}
});

// Total by course
new Chart(document.getElementById('totalByCourseChart'), {
    type:'bar',
    data:{
        labels: totalByCourseLabels,
        datasets:[{
            label:'รวมทุกปี',
            data: totalByCourseValues,
            backgroundColor: totalByCourseLabels.map(l=>courseColors[l]),
            borderRadius:8
        }]
    },
    options:{
        indexAxis:'y',
        responsive:true,
        plugins:{legend:{display:false}},
        scales:{x:{beginAtZero:true}}
    }
});
// กราฟเส้นรวมทุกหลักสูตรรายปี
const datasetsAllCourses = Object.entries(courseData).map(([courseName, data], i) => ({
    label: courseName,
    data: Object.values(data),
    borderColor: courseColors[courseName],
    backgroundColor: 'transparent',
    tension: 0.3,
    pointRadius: 4
}));

new Chart(document.getElementById('allCoursesLineChart'), {
    type: 'line',
    data: {
        labels: years,
        datasets: datasetsAllCourses
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                mode: 'index',
                intersect: false
            }
        },
        interaction: {
            mode: 'nearest',
            axis: 'x',
            intersect: false
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});


// Each course charts
let idx=0;
for(const [courseName, data] of Object.entries(courseData)){
    new Chart(document.getElementById(`chart_${idx}`), {
        type:'bar',
        data:{labels:years,datasets:[{label:courseName,data:Object.values(data),backgroundColor:courseColors[courseName],borderRadius:6}]},
        options:{responsive:true,scales:{y:{beginAtZero:true}}}
    });
    idx++;
}

// Dropdown switch
const select = document.getElementById('courseSelect');
select.addEventListener('change', e=>{
    const value = e.target.value;
    document.getElementById('overallContent').style.display = value==='overall'?'block':'none';
    document.querySelectorAll('.courseContent').forEach(el=>el.style.display='none');
    if(value!=='overall') document.getElementById(value).style.display='block';
});
</script>
</body>
</html>
