<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Aplikasi</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-blue-50 via-indigo-500 to-purple-600 flex items-center justify-center min-h-screen">

    <!-- Card -->
    <div class="bg-white/90 backdrop-blur-lg shadow-2xl rounded-2xl p-8 w-full max-w-md">

        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">
            Login
        </h2>

        <!-- Success -->
        <?php if(isset($_SESSION['success'])) { ?>
            <div class="bg-green-100 text-green-700 p-3 rounded-lg mb-4 text-sm">
                <?= $_SESSION['success']; ?>
            </div>
        <?php unset($_SESSION['success']); } ?>

        <!-- Error -->
        <?php if(isset($_SESSION['error'])) { ?>
            <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4 text-sm">
                <?= $_SESSION['error']; ?>
            </div>
        <?php unset($_SESSION['error']); } ?>

        <!-- Form -->
        <form action="process/prosesLogin.php" method="POST" onsubmit="return validateForm()">

            <!-- Email -->
            <div class="mb-4">
                <label class="block text-gray-700 mb-1">Alamat Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="Masukkan email Anda"
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
                        placeholder="Masukkan password Anda"
                        class="w-full px-4 py-2 border rounded-lg pr-10 focus:ring-2 focus:ring-blue-500 focus:outline-none transition"
                        required
                    >

                    <!-- Eye button -->
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
                class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition duration-300 shadow-md"
            >
                Login
            </button>
        </form>

        <!-- Footer -->
        <div class="text-center text-sm text-gray-600 mt-6">
            Belum punya akun? 
            <a href="register.php" class="text-blue-600 hover:underline font-medium">
                Daftar di sini
            </a>
        </div>

    </div>

    <!-- Toggle password -->
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