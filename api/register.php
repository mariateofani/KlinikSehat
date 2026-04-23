<?php  
session_start(); 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register Aplikasi</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-blue-50 via-indigo-500 to-purple-600 flex items-center justify-center min-h-screen">

    <!-- Card -->
    <div class="bg-white/90 backdrop-blur-lg shadow-2xl rounded-2xl p-8 w-full max-w-md">

        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">
            Buat Akun Baru
        </h2>

        <!-- Alert Error -->
        <?php if(isset($_SESSION['error'])) { ?>
            <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4 text-sm">
                <?= $_SESSION['error']; ?>
            </div>
        <?php unset($_SESSION['error']); } ?>

        <!-- Form -->
        <form action="process/prosesRegister.php" method="POST" onsubmit="return validateForm()">
            
            <!-- Nama -->
            <div class="mb-4">
                <label class="block text-gray-700 mb-1">Nama Lengkap</label>
                <input 
                    type="text" 
                    name="nama" 
                    placeholder="Masukkan nama lengkap"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none transition"
                    required
                >
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label class="block text-gray-700 mb-1">Alamat Email</label>
                <input 
                    type="email" 
                    name="email" 
                    placeholder="Masukkan email"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none transition"
                    required
                >
            </div>

            <!-- Password -->
            <div class="mb-6">
                <label class="block text-gray-700 mb-1">Password</label>

                <div class="relative">
                    <input 
                        type="password" 
                        id="password"
                        name="password" 
                        placeholder="Buat password"
                        class="w-full px-4 py-2 border rounded-lg pr-10 focus:ring-2 focus:ring-blue-500 focus:outline-none transition"
                        required
                    >

                    <!-- Icon mata -->
                    <button 
                        type="button" 
                        onclick="togglePassword()" 
                        class="absolute right-3 top-2.5 text-gray-500 hover:text-gray-700"
                    >
                        👁
                    </button>
                </div>
            </div>

            <!-- Button -->
            <button 
                type="submit" 
                class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition duration-300 shadow-md"
            >
                Daftar Sekarang
            </button>
        </form>

        <!-- Footer -->
        <div class="text-center text-sm text-gray-600 mt-6">
            Sudah punya akun? 
            <a href="login.php" class="text-blue-600 hover:underline font-medium">
                Login di sini
            </a>
        </div>

    </div>

    <!-- JS -->
    <script>
        function togglePassword() {
            const password = document.getElementById("password");

            if (password.type === "password") {
                password.type = "text";
            } else {
                password.type = "password";
            }
        }
    </script>
</body>
</html>