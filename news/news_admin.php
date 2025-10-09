<?php include "../config/config_news.php"; 
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login_admin.php");
    exit;
}?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการประชาสัมพันธ์</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">

    <div class="max-w-6xl mx-auto p-6 bg-white shadow mt-8 rounded-lg">
    <h1 class="text-3xl font-bold mb-6 text-center">🛠 จัดการประชาสัมพันธ์</h1>
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_news"])) {
            $title = $_POST["title"];
            $content = $_POST["content"];

            $cover_dir = "uploads/cover/";
            $detail_dir = "uploads/details/";
            if (!is_dir($cover_dir)) mkdir($cover_dir, 0777, true);
            if (!is_dir($detail_dir)) mkdir($detail_dir, 0777, true);

            $cover_path = '';
            if (!empty($_FILES["cover_image"]["name"])) {
                $filename = time() . "_" . basename($_FILES["cover_image"]["name"]);
                $cover_path = $cover_dir . $filename;
                move_uploaded_file($_FILES["cover_image"]["tmp_name"], $cover_path);
            }

            $stmt = $conn_news->prepare("INSERT INTO news (title, content, cover_image) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $title, $content, $cover_path);
            $stmt->execute();
            $news_id = $stmt->insert_id;

            if (!empty($_FILES["detail_images"]["name"][0])) {
                foreach ($_FILES["detail_images"]["tmp_name"] as $index => $tmpName) {
                    if ($_FILES["detail_images"]["error"][$index] === 0) {
                        $fileName = time() . "_" . basename($_FILES["detail_images"]["name"][$index]);
                        $filePath = $detail_dir . $fileName;
                        move_uploaded_file($tmpName, $filePath);
                        $conn_news->query("INSERT INTO news_images (news_id, image_url) VALUES ($news_id, '$filePath')");
                    }
                }
            }

            echo "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4'>เพิ่มข่าวเรียบร้อย</div>";
        }

        if (isset($_POST["delete_news"])) {
            $id = $_POST["news_id"];

            $res = $conn->query("SELECT cover_image FROM news WHERE id = $id");
            $row = $res->fetch_assoc();
            if ($row && file_exists($row['cover_image'])) unlink($row['cover_image']);

            $res2 = $conn->query("SELECT image_url FROM news_images WHERE news_id = $id");
            while ($img = $res2->fetch_assoc()) {
                if (file_exists($img['image_url'])) unlink($img['image_url']);
            }

            $conn_news->query("DELETE FROM news_images WHERE news_id = $id");
            $conn_news->query("DELETE FROM news WHERE id = $id");

            echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'>ลบข่าวเรียบร้อย</div>";
        }
        ?>

        <form method="post" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700">หัวข้อประชาสัมพันธ์</label>
                <input type="text" name="title" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">เนื้อหาประชาสัมพันธ์</label>
                <textarea name="content" required rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"></textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">ภาพปกข่าว (1 รูป)</label>
                <input type="file" name="cover_image" accept="image/*" required class="mt-1 block w-full">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">ภาพประกอบ (หลายรูป)</label>
                <input type="file" name="detail_images[]" accept="image/*" multiple class="mt-1 block w-full">
            </div>
            <button type="submit" name="add_news" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">เพิ่มข่าว</button>
        </form>

        <h3 class="text-xl font-semibold mt-10 mb-4">รายการประชาสัมพันธ์</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 bg-white shadow-md rounded-lg">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-mid text-sm font-medium text-gray-600">ภาพปก</th>
                        <th class="px-4 py-2 text-mid text-sm font-medium text-gray-600">หัวข้อ</th>
                        <th class="px-4 py-2 text-mid text-sm font-medium text-gray-600">ดู</th>
                        <th class="px-4 py-2 text-mid text-sm font-medium text-gray-600">วันที่</th>
                        <th class="px-4 py-2 text-mid text-sm font-medium text-gray-600">ลบ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php
                    $result = $conn->query("SELECT * FROM news ORDER BY created_at DESC");
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td class='px-4 py-2'><img src='" . htmlspecialchars($row["cover_image"]) . "' class='w-20 h-14 object-cover rounded'></td>";
                        echo "<td class='px-4 py-2'>" . htmlspecialchars($row["title"]) . "</td>";
                        echo "<td class='px-4 py-2'>
                        <a href='news-detail.php?id=" . $row["id"] . "' target='_blank'
                           class='inline-block bg-blue-500 text-white px-4 py-1 rounded hover:bg-blue-600 text-sm text-center'>
                           ดู
                        </a>
                      </td>";                        echo "<td class='px-4 py-2 text-sm text-gray-600'>" . $row["created_at"] . "</td>";
                        echo "<td class='px-4 py-2'>
                            <form method='post' onsubmit=\"return confirm('ยืนยันการลบข่าวนี้?');\">
                                <input type='hidden' name='news_id' value='" . $row["id"] . "'>
                                <button type='submit' name='delete_news' class='bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 text-sm'>ลบ</button>
                            </form>
                          </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            <a href="../admin_panel.php" class="inline-block bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">← กลับไปหน้าแอดมิน</a>
        </div>

    </div>

</body>

</html>