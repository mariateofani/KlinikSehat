<?php
ob_start();
session_start();
require_once __DIR__ . '/../service/koneksi.php';

$nama     = $_POST['nama'];
$email    = $_POST['email'];
$password = $_POST['password'];

if (empty($nama) || empty($email) || empty($password)) {
    $_SESSION['error'] = "Semua field wajib diisi!";
    header("Location: ../register.php");
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$role = "user";

// cek email
$cek = $koneksi->prepare("SELECT id_user FROM users WHERE email=?");
$cek->bind_param("s", $email);
$cek->execute();
$cek->store_result();

if ($cek->num_rows > 0) {
    $_SESSION['error'] = "Email sudah terdaftar!";
    header("Location: ../register.php");
    exit;
}

// insert
$stmt = $koneksi->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $nama, $email, $hash, $role);

if ($stmt->execute()) {
    $_SESSION['success'] = "Register berhasil!";
    header("Location: ../login.php");
} else {
    $_SESSION['error'] = "Register gagal!";
    header("Location: ../register.php");
}
exit;