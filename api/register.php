<?php

$error = $_COOKIE['error'] ?? null;
$success = $_COOKIE['success'] ?? null;

// hapus flash message setelah ditampilkan
if ($error) {
    setcookie("error", "", time() - 3600, "/");
}

if ($success) {
    setcookie("success", "", time() - 3600, "/");
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Register Aplikasi</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-blue-50 flex items-center justify-center min-h-screen">

    <div class="bg-white shadow-2xl rounded-2xl p-8 w-full max-w-md">

        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">
            Buat Akun Baru
        </h2>

        <!-- Alert Error -->
        <?php if (!empty($error)) : ?>
            <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4 text-sm">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Alert Success -->
        <?php if (!empty($success)) : ?>
            <div class="bg-green-100 text-green-700 p-3 rounded-lg mb-4 text-sm">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form action="process/prosesRegister.php" method="POST">

            <!-- Nama -->
            <div class="mb-4">
                <label class="block text-gray-700 mb-1">
                    Nama Lengkap
                </label>

                <input
                    type="text"
                    name="nama"
                    placeholder="Masukkan nama lengkap"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
                    required>
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label class="block text-gray-700 mb-1">
                    Alamat Email
                </label>

                <input
                    type="email"
                    name="email"
                    placeholder="Masukkan email"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
                    required>
            </div>

            <!-- Password -->
            <div class="mb-6">

                <label class="block text-gray-700 mb-1">
                    Password
                </label>

                <div class="relative">

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Buat password"
                        class="w-full px-4 py-2 border rounded-lg pr-12 focus:ring-2 focus:ring-blue-500 outline-none"
                        required>

                    <button
                        type="button"
                        onclick="togglePassword()"
                        class="absolute right-3 top-2 text-gray-500">

                        👁

                    </button>

                </div>

            </div>

            <button
                type="submit"
                class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700">

                Daftar Sekarang

            </button>

        </form>

        <div class="text-center text-sm text-gray-600 mt-6">

            Sudah punya akun?

            <a
                href="login.php"
                class="text-blue-600 hover:underline">

                Login di sini

            </a>

        </div>

    </div>

    <script>

        function togglePassword() {

            const password =
                document.getElementById("password");

            password.type =
                password.type === "password"
                    ? "text"
                    : "password";

        }

    </script>

</body>

</html>