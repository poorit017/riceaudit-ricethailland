<?php
include "../config/config_news.php";


if (!isset($_GET["id"])) {
    echo "ไม่พบข่าวที่ต้องการแสดง";
    exit;
}

$news_id = intval($_GET["id"]);
$stmt = $conn->prepare("SELECT * FROM news WHERE id = ?");
$stmt->bind_param("i", $news_id);
$stmt->execute();
$result = $stmt->get_result();
$news = $result->fetch_assoc();

if (!$news) {
    echo "ไม่พบข่าว";
    exit;
}

// ดึงภาพประกอบเพิ่มเติม
$images = [];
$res_images = $conn->query("SELECT image_url FROM news_images WHERE news_id = $news_id");
while ($img = $res_images->fetch_assoc()) {
    $images[] = $img["image_url"];
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($news["title"]) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style1.css">
</head>

<body>
    <div class="container mt-4">
        <h2><?= htmlspecialchars($news["title"]) ?></h2>
        <p><small class="text-muted">เผยแพร่เมื่อ: <?= $news["created_at"] ?></small></p>

        <?php if (!empty($news["cover_image"])): ?>
            <img src="<?= htmlspecialchars($news["cover_image"]) ?>" class="img-fluid mb-3" alt="ภาพปก">
        <?php endif; ?>

        <div class="mb-4">
            <?= nl2br(htmlspecialchars($news["content"])) ?>
        </div>

        <?php if (!empty($images)): ?>
            <h5>ภาพประกอบเพิ่มเติม</h5>
            <div class="row">
                <?php foreach ($images as $img): ?>
                    <div class="col-md-3 mb-3">
                            <img src="<?= htmlspecialchars($img) ?>" class="img-fluid rounded" alt="ภาพประกอบ">
                        </a>
                    </div> 
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <a href="../index.php" class="btn btn-secondary mt-4">กลับหน้าแรก</a>
    </div>
</body>

</html>