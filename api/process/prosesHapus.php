<?php
session_start();
include '../service/koneksi.php';

// 🔐 CEK ADMIN
if (!isset($_SESSION['email']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// 🔥 AMBIL ID
$id = $_GET['id'] ?? 0;

// VALIDASI
if (!$id) {
    $_SESSION['error'] = "ID tidak valid!";
    header("Location: ../dashboard_admin.php");
    exit;
}

// 🔒 HAPUS DATA (AMAN)
$stmt = $koneksi->prepare("DELETE FROM users WHERE id_user=?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION['success'] = "User berhasil dihapus!";
} else {
    $_SESSION['error'] = "Gagal hapus user!";
}

header("Location: ../dashboard_admin.php");
exit;
?>