<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login_admin.php");
    exit;
}
include '../config/config.php';
if (isset($_POST['upload_photos'])) {
    $category_id = $_POST['upload_category_id'];

// ดึง year_name จาก category_id
$yearResult = $conn->prepare("SELECT years.year_name FROM categories JOIN years ON categories.year_id = years.id WHERE categories.id = ?");
$yearResult->bind_param("i", $category_id);
$yearResult->execute();
$yearResult->bind_result($year_name);
$yearResult->fetch();
$yearResult->close();

$upload_dir = "pictureyear/uploads/{$year_name}/";

    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
        $filename = basename($_FILES['photos']['name'][$key]);
        $target_file = $upload_dir . time() . '_' . $filename;

        // ตรวจสอบประเภทไฟล์
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['photos']['type'][$key], $allowed_types)) {
            echo "ไฟล์ไม่ถูกต้อง! โปรดลองใหม่.";
            continue;
        }

        // ตรวจสอบขนาดไฟล์
        if ($_FILES['photos']['size'][$key] > 5000000) { // ขนาดไฟล์ไม่เกิน 5MB
            echo "ไฟล์ขนาดใหญ่เกินไป!";
            continue;
        }

        if (move_uploaded_file($tmp_name, $target_file)) {
            // เตรียมการแทรกข้อมูลในฐานข้อมูล
            $stmt = $conn->prepare("INSERT INTO images (category_id, file_path) VALUES (?, ?)");
            $stmt->bind_param("is", $category_id, $target_file);

            if ($stmt->execute()) {
                echo "<p class='text-green-600'>✅ อัปโหลดเรียบร้อยแล้ว</p>";
            } else {
                echo "<p class='text-red-600'>เกิดข้อผิดพลาดในการอัปโหลด: " . $stmt->error . "</p>";
            }
        } else {
            echo "เกิดข้อผิดพลาดในการอัปโหลดไฟล์.";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_year'])) {
        $year = $_POST['year'];
        $stmt = $conn->prepare("INSERT INTO years (year_name) VALUES (?)");
        $stmt->bind_param("s", $year);
        if ($stmt->execute()) {
            echo "<p class='text-green-600'>เพิ่มปีใหม่เรียบร้อย</p>";
        } else {
            echo "<p class='text-red-600'>เกิดข้อผิดพลาดในการเพิ่มปี: " . $stmt->error . "</p>";
        }
    }

    if (isset($_POST['add_category'])) {
        $catName = $_POST['category_name'];
        $yearId = $_POST['year_id'];
        $stmt = $conn->prepare("INSERT INTO categories (category_name, year_id) VALUES (?, ?)");
        $stmt->bind_param("si", $catName, $yearId);
        if ($stmt->execute()) {
            echo "<p class='text-green-600'>เพิ่มหมวดหมู่เรียบร้อย</p>";
        } else {
            echo "<p class='text-red-600'>เกิดข้อผิดพลาดในการเพิ่มหมวดหมู่: " . $stmt->error . "</p>";
        }
    }
}

$years = $conn->query("SELECT * FROM years ORDER BY year_name DESC");
$categories = $conn->query("
    SELECT categories.*, years.year_name 
    FROM categories 
    JOIN years ON categories.year_id = years.id
    ORDER BY years.year_name DESC
");
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body class="bg-gray-100 p-6">
    <div class="mt-8">
        <a href="../admin_panel.php" class='bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 '>← กลับไปหน้าแอดมิน</a>
    </div>
    <h1 class="text-3xl font-bold mb-6 text-center">🛠 Manage Picture - ระบบจัดการรูปภาพ</h1>

    <!-- เพิ่มปี -->
    <div class="bg-white p-6 rounded shadow mb-6 max-w-xl mx-auto">
        <h2 class="text-xl font-semibold mb-2">เพิ่มปีใหม่</h2>
        <form method="POST">
            <input type="text" name="year" class="border p-2 w-full mb-2" placeholder="เช่น 2567" required>
            <button name="add_year" class="bg-blue-600 text-white px-4 py-2 rounded">เพิ่มปี</button>
        </form>
    </div>

    <!-- เพิ่มหมวดหมู่ -->
    <div class="bg-white p-6 rounded shadow mb-6 max-w-xl mx-auto">
        <h2 class="text-xl font-semibold mb-2">เพิ่มหมวดหมู่</h2>
        <form method="POST">
            <input type="text" name="category_name" class="border p-2 w-full mb-2" placeholder="ชื่อหมวดหมู่" required>
            <select name="year_id" class="border p-2 w-full mb-2" required>
                <option value="">เลือกปี</option>
                <?php while ($y = $years->fetch_assoc()): ?>
                    <option value="<?= $y['id'] ?>"><?= $y['year_name'] ?></option>
                <?php endwhile; ?>
            </select>
            <button name="add_category" class="bg-green-600 text-white px-4 py-2 rounded">เพิ่มหมวดหมู่</button>
        </form>
    </div>

    <!-- อัปโหลดรูป -->
    <div class="bg-white p-6 rounded shadow mb-6 max-w-xl mx-auto">
        <h2 class="text-xl font-semibold mb-2">อัปโหลดรูปภาพ</h2>
        <form method="POST" enctype="multipart/form-data">
            <label class="block mb-2">เลือกหมวดหมู่:</label>
            <select name="upload_category_id" required class="border p-2 w-full mb-2">
                <?php
                $catOpts = $conn->query("SELECT categories.*, years.year_name FROM categories JOIN years ON categories.year_id = years.id ORDER BY years.year_name DESC");
                while ($c = $catOpts->fetch_assoc()):
                ?>
                    <option value="<?= $c['id'] ?>"><?= $c['category_name'] ?> (ปี <?= $c['year_name'] ?>)</option>
                <?php endwhile; ?>
            </select>
            <input type="file" name="photos[]" multiple required class="border p-2 w-full mb-2">
            <button type="submit" name="upload_photos" class="bg-purple-600 text-white px-4 py-2 rounded">อัปโหลด</button>
        </form>
    </div>
    <h3 class="font-bold mt-4 mb-2">ปีทั้งหมด</h3>
    <?php
    $yearList = $conn->query("SELECT * FROM years ORDER BY year_name DESC");
    while ($y = $yearList->fetch_assoc()):  
    ?>
        <div class="flex justify-between bg-red-100 p-2 my-1 rounded">
            <span><a href="picture<?= $y['year_name'] ?>.html" target="_blank">ปี <?= $y['year_name'] ?></a></span>
            <a href="delete.php?type=year&id=<?= $y['id'] ?>" class="text-red-700 font-bold" onclick="return confirm('ลบปีนี้? หมวดหมู่และรูปทั้งหมดจะถูกลบด้วย!')">🗑 ลบปี</a>
        </div>
    <?php endwhile; ?>
    <h3 class="font-bold mt-4 mb-2">หมวดหมู่ทั้งหมด</h3>
    <?php
    $allCats = $conn->query("SELECT categories.id, categories.category_name, years.year_name FROM categories JOIN years ON categories.year_id = years.id ORDER BY years.year_name DESC , categories.category_name ASC ");
    while ($c = $allCats->fetch_assoc()):
    ?>
        <div class="flex justify-between bg-yellow-100 p-2 my-1 rounded">
            <span><?= $c['year_name'] ?> - <?= $c['category_name'] ?></span>
            <a href="delete.php?type=category&id=<?= $c['id'] ?>" class="text-red-600 font-bold" onclick="return confirm('ลบหมวดหมู่นี้? รูปทั้งหมดในหมวดหมู่จะถูกลบด้วย!')">🗑 ลบหมวดหมู่</a>
        </div>
    <?php endwhile; ?>
    <div class="max-w-4xl mx-auto p-8">
        <h1 class="text-2xl font-bold mb-4">Admin Panel</h1>

        <!-- ปุ่มที่จะใช้ในการสร้างไฟล์ -->
        <button id="generateButton" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-500">
            สร้างไฟล์รูปภาพ
        </button>

        <!-- ข้อความสถานะ -->
        <div id="statusMessage" class="mt-4 text-lg"></div>
    </div>

    <script>
        // เมื่อคลิกปุ่มให้ส่งคำขอไปยัง generate_picture.php
        document.getElementById('generateButton').addEventListener('click', function() {
            // แสดงข้อความกำลังประมวลผล
            document.getElementById('statusMessage').textContent = 'กำลังสร้างไฟล์...';

            // ส่งคำขอ AJAX ไปยัง generate_picture.php
            $.ajax({
                url: 'generate_picture.php',
                type: 'GET',
                success: function(response) {
                    // เมื่อสำเร็จให้แสดงข้อความ
                    document.getElementById('statusMessage').textContent = response;
                },
                error: function() {
                    // หากเกิดข้อผิดพลาด
                    document.getElementById('statusMessage').textContent = 'เกิดข้อผิดพลาดในการสร้างไฟล์';
                }
            });
        });
    </script>
    <!-- รูปภาพ -->
    <h3 class="font-bold mb-6 max-w-xl mx-auto">รูปภาพทั้งหมด (แยกตามหมวดหมู่)</h3>

    <form method="POST" action="delete_selected.php" onsubmit="return confirm('คุณแน่ใจว่าต้องการลบรูปภาพที่เลือก?');">
        <?php
        // ดึงรูปภาพทั้งหมดจัดกลุ่มตามปีและหมวดหมู่
        $grouped = $conn->query("
        SELECT 
            images.id, images.file_path, 
            categories.category_name, 
            years.year_name,
            categories.id AS cat_id
        FROM images
        JOIN categories ON images.category_id = categories.id 
        JOIN years ON categories.year_id = years.id 
        ORDER BY years.year_name DESC, categories.category_name ASC
    ");

        $groupedImages = [];
        while ($row = $grouped->fetch_assoc()) {
            $key = $row['year_name'] . '|' . $row['category_name'];
            $groupedImages[$key][] = $row;
        }

        foreach ($groupedImages as $group => $images):
            list($year, $category) = explode('|', $group);
        ?>
            <div class="bg-gray-50 border p-4 my-4 rounded">
                <h4 class="text-lg font-bold mb-2">📂 <?= $year ?> - <?= $category ?></h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <?php foreach ($images as $img): ?>
                        <div class="relative border rounded p-2 bg-white shadow">
                            <img src="<?= $img['file_path'] ?>" alt="" class="h-32 w-full object-cover rounded mb-2">
                            <label class="flex items-center space-x-2">
                                <input type="checkbox" name="delete_ids[]" value="<?= $img['id'] ?>" class="form-checkbox text-red-600">
                                <span class="text-sm">เลือก</span>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-6">
                    <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded">🗑 ลบรูปภาพที่เลือก</button>
                </div>
            </div>
        <?php endforeach; ?>
    </form>


</body>

</html>