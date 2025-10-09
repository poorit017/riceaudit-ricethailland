<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script> <!-- ไอคอน -->
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <form method="POST" action="login_process.php" class="w-full max-w-sm bg-white p-8 shadow-lg rounded-lg">
        <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">🔐 เข้าสู่ระบบผู้ดูแล</h2>

        <div class="mb-4">
            <label for="username" class="block text-gray-700 mb-1">ชื่อผู้ใช้</label>
            <input type="text" id="username" name="username" required class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Username" />
        </div>

        <div class="mb-6 relative">
            <label for="password" class="block text-gray-700 mb-1">รหัสผ่าน</label>
            <input type="password" id="password" name="password" required class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 pr-10" placeholder="Password" />
            <button type="button" onclick="togglePassword()" class="absolute right-2 top-9 text-gray-500">
                <i id="eyeIcon" class="ph ph-eye"></i>
            </button>
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 font-semibold">
            เข้าสู่ระบบ
        </button>
    </form>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById("password");
            const eyeIcon = document.getElementById("eyeIcon");
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                eyeIcon.classList.replace("ph-eye", "ph-eye-slash");
            } else {
                passwordInput.type = "password";
                eyeIcon.classList.replace("ph-eye-slash", "ph-eye");
            }
        }
    </script>

</body>
</html>
