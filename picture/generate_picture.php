    <?php
    session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../login_admin.php");
    exit;
}
include '../config/config.php';

    $years = $conn->query("SELECT * FROM years");

    while ($year = $years->fetch_assoc()) {
        $year_id = $year['id'];
        $year_name = $year['year_name'];

        $html = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>รูปภาพการอบรมปี '.$year_name.'</title>
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        <link rel="stylesheet" href="../css/style1.css">
    </head>
    <body class="bg-gray-100">
        <header class="bg-green-600 text-white p-3 justify-center">
            <h1 class="text-3xl font-bold">รูปภาพการอบรมปี '.$year_name.'</h1>
        </header>
        <button class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-600 mt-8 mb-4 ml-8" onclick="window.location.href=\'../index.php\'">ย้อนกลับ</button>
        <div class="max-w-4xl mx-auto flex flex-wrap gap-2 mt-4">';
        
        $cats = $conn->query("SELECT * FROM categories WHERE year_id = $year_id ORDER BY category_name ASC");
        $galleryJs = "const galleries = {\n";

        $galleryDivs = '';
        $jsInit = '';

        $count = 1;
        while ($cat = $cats->fetch_assoc()) {
            $cat_id = $cat['id'];
            $cat_name = $cat['category_name'];
            $galleryId = "photoGallery$count";

            $html .= "<button class=\"px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-600\" onclick=\"showGallery('$galleryId')\">$cat_name</button>";

            $galleryDivs .= "<div class=\"max-w-4xl mx-auto mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 ".($count > 1 ? 'hidden' : '')."\" id=\"$galleryId\"></div>\n";

            $images = $conn->query("SELECT * FROM images WHERE category_id = $cat_id");
            $galleryJs .= "  $galleryId: [\n";
            while ($img = $images->fetch_assoc()) {
                $galleryJs .= "    '{$img['file_path']}',\n";
            }
            $galleryJs .= "  ],\n";

            $count++;
        }

        $galleryJs .= "};";

        $html .= "</div>\n" . $galleryDivs;

        // Add modal and scripts (เหมือนต้นฉบับ)
        $html .= '
        <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center hidden">
            <div class="relative">
                <button class="absolute left-2 top-1/2 transform -translate-y-1/2 bg-white px-4 py-2 rounded-full shadow-md" onclick="changeImage(-1)">&#9664;</button>
                <img id="enlargedImage" src="" alt="Enlarged Photo" class="max-w-full max-h-screen">
                <button class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-white px-4 py-2 rounded-full shadow-md" onclick="changeImage(1)">&#9654;</button>
            </div>
        </div>

        <script>
            let currentGallery = [];
            let currentIndex = 0;

            function addPhoto(galleryId, src, index, galleryArray) {
                const gallery = document.getElementById(galleryId);
                const photoDiv = document.createElement("div");
                photoDiv.className = "bg-white p-4 rounded-md shadow-md";

                const img = document.createElement("img");
                img.src = src;
                img.alt = "Photo";
                img.className = "w-full h-full object-cover mb-4 rounded-md cursor-pointer";
                img.addEventListener("click", () => showEnlargedImage(index, galleryArray));

                photoDiv.appendChild(img);
                gallery.appendChild(photoDiv);
            }

            function showGallery(galleryId) {
                document.querySelectorAll("[id^=\'photoGallery\']").forEach(gallery => gallery.classList.add("hidden"));
                document.getElementById(galleryId).classList.remove("hidden");
            }

            function showEnlargedImage(index, gallery) {
                currentGallery = gallery;
                currentIndex = index;
                document.getElementById("enlargedImage").src = currentGallery[currentIndex];
                document.getElementById("imageModal").classList.remove("hidden");
            }

            function changeImage(direction) {
                currentIndex = (currentIndex + direction + currentGallery.length) % currentGallery.length;
                document.getElementById("enlargedImage").src = currentGallery[currentIndex];
            }

            function hideModal() {
                document.getElementById("imageModal").classList.add("hidden");
            }

            document.getElementById("imageModal").addEventListener("click", event => {
                if (event.target === document.getElementById("imageModal")) hideModal();
            });

            document.addEventListener("keydown", event => {
                if (!document.getElementById("imageModal").classList.contains("hidden")) {
                    if (event.key === "ArrowRight") changeImage(1);
                    if (event.key === "ArrowLeft") changeImage(-1);
                    if (event.key === "Escape") hideModal();
                }
            });

            ' . $galleryJs . '

            Object.keys(galleries).forEach(galleryId => {
                galleries[galleryId].forEach((imageUrl, index) => {
                    addPhoto(galleryId, imageUrl, index, galleries[galleryId]);
                });
            });
        </script>
    </body>
    </html>';

        file_put_contents("picture$year_name.html", $html);
    }

    echo "✅ สร้างไฟล์ picture ปีต่าง ๆ เสร็จแล้ว";
    ?>
