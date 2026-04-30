<?php
require_once __DIR__ . '/../service/koneksi.php';

// 🔐 CEK LOGIN DARI COOKIE
if (!isset($_COOKIE['email']) || !isset($_COOKIE['role'])) {
    header("Location: ../login.php");
    exit;
}

// 🔒 HARUS ADMIN
if ($_COOKIE['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// 🔥 AMBIL DATA
$id    = $_POST['id'] ?? null;
$nama  = $_POST['nama'] ?? '';
$email = $_POST['email'] ?? '';
$role  = $_POST['role'] ?? 'user';

// VALIDASI
if (!$id || $nama == '' || $email == '') {
    setcookie("error", "Data tidak lengkap!", time() + 5, "/");
    header("Location: ../dashboard_admin.php");
    exit;
}

// 🔒 UPDATE (AMAN)
$stmt = $koneksi->prepare("
    UPDATE users 
    SET nama=?, email=?, role=? 
    WHERE id_user=?
");
$stmt->bind_param("sssi", $nama, $email, $role, $id);

// EKSEKUSI
if ($stmt->execute()) {
    setcookie("success", "User berhasil diupdate!", time() + 5, "/");
} else {
    setcookie("error", "Gagal update user!", time() + 5, "/");
}

header("Location: ../dashboard_admin.php");
exit;
?>