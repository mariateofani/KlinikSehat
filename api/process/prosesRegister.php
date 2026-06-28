<?php
ob_start();
require_once __DIR__ . '/../service/koneksi.php';

$nama     = trim($_POST['nama']);
$email    = trim($_POST['email']);
$password = trim($_POST['password']);

if (empty($nama) || empty($email) || empty($password)) {
    setcookie("error", "Semua field wajib diisi!", time() + 5, "/");
    header("Location: ../register.php");
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$role = "user";

// Cek email
$cek = $koneksi->prepare("SELECT id_user FROM users WHERE email = ?");
$cek->bind_param("s", $email);
$cek->execute();
$cek->store_result();

if ($cek->num_rows > 0) {
    setcookie("error", "Email sudah terdaftar!", time() + 5, "/");
    header("Location: ../register.php");
    exit;
}

// Insert data
$stmt = $koneksi->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $nama, $email, $hash, $role);

if ($stmt->execute()) {

    setcookie("success", "Register berhasil! Silakan login.", time() + 5, "/");
    header("Location: ../login.php");
    exit;

} else {

    setcookie("error", "Register gagal!", time() + 5, "/");
    header("Location: ../register.php");
    exit;

}