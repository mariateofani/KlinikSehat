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

// 🔥 AMBIL ID
$id = $_GET['id'] ?? 0;

// VALIDASI
if (!$id) {
    setcookie("error", "ID tidak valid!", time() + 5, "/");
    header("Location: ../dashboard_admin.php");
    exit;
}

// 🔒 HAPUS DATA (AMAN)
$stmt = $koneksi->prepare("DELETE FROM users WHERE id_user=?");
$stmt->bind_param("i", $id);

// EKSEKUSI
if ($stmt->execute()) {
    setcookie("success", "User berhasil dihapus!", time() + 5, "/");
} else {
    setcookie("error", "Gagal hapus user!", time() + 5, "/");
}

header("Location: ../dashboard_admin.php");
exit;
?>