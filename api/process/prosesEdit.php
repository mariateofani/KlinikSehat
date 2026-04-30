<?php
session_start();
require_once __DIR__ . '/../service/koneksi.php';

// 🔐 CEK ADMIN
if (!isset($_SESSION['email']) || ($_SESSION['role'] ?? '') !== 'admin') {
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
    $_SESSION['error'] = "Data tidak lengkap!";
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

if ($stmt->execute()) {
    $_SESSION['success'] = "User berhasil diupdate!";
} else {
    $_SESSION['error'] = "Gagal update user!";
}

header("Location: ../dashboard_admin.php");
exit;
?>