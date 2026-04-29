<?php
ob_start();
session_start();
require_once __DIR__ . '/../service/koneksi.php';

$email    = $_POST['email'];
$password = $_POST['password'];

// 🔍 Ambil user berdasarkan email
$stmt = $koneksi->prepare("SELECT * FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {

    if (password_verify($password, $user['password'])) {

        // ✅ SESSION
        $_SESSION['email'] = $user['email'];
        $_SESSION['nama']  = $user['nama'];
        $_SESSION['role']  = $user['role'];

        // 🔥 Redirect sesuai role
        if ($user['role'] === 'admin') {
            header("Location: ../dashboard_admin.php");
        } else {
            header("Location: ../dashboard.php");
        }
        exit;

    } else {
        $_SESSION['error'] = "Password salah!";
        header("Location: ../login.php");
        exit;
    }

} else {
    $_SESSION['error'] = "Email tidak ditemukan!";
    header("Location: ../login.php");
    exit;
}