<?php
session_start();
include '../service/koneksi.php';

// 🔐 CEK LOGIN ADMIN
if (!isset($_SESSION['email']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit;
}

// 🔥 AMBIL ID
$id = $_GET['id'] ?? 0;

// 🔒 AMBIL DATA (AMAN)
$stmt = $koneksi->prepare("SELECT * FROM users WHERE id_user=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    header("Location: dashboard_admin.php");
    exit;
}
?>

<!doctype html>
<html>
<head>
<title>Edit User</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">

<div class="bg-white w-full max-w-md p-8 rounded-xl shadow-lg">

    <h2 class="text-2xl font-bold text-center text-blue-600 mb-6">
        Edit User
    </h2>

    <form action="process/prosesEdit.php" method="POST" class="space-y-4">

        <input type="hidden" name="id" value="<?= $data['id_user'] ?>">

        <!-- Nama -->
        <div>
            <label class="text-sm font-semibold text-gray-600">Nama</label>
            <input type="text" name="nama"
                value="<?= $data['nama'] ?>"
                class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-blue-400 outline-none"
                required>
        </div>

        <!-- Email -->
        <div>
            <label class="text-sm font-semibold text-gray-600">Email</label>
            <input type="email" name="email"
                value="<?= $data['email'] ?>"
                class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-blue-400 outline-none"
                required>
        </div>

        <!-- Role -->
        <div>
            <label class="text-sm font-semibold text-gray-600">Role</label>
            <select name="role"
                class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-blue-400 outline-none">

                <option value="admin" <?= $data['role']=='admin'?'selected':'' ?>>
                    Admin
                </option>

                <option value="user" <?= $data['role']=='user'?'selected':'' ?>>
                    User
                </option>

            </select>
        </div>

        <!-- Button -->
        <button type="submit"
            class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition">
            Update User
        </button>

        <!-- Back -->
        <a href="dashboard_admin.php"
            class="block text-center text-sm text-gray-500 hover:text-gray-700 mt-2">
            ← Kembali ke Dashboard
        </a>

    </form>

</div>

</body>
</html>