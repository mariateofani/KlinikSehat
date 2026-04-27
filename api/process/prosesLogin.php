<?php
session_start();
require_once __DIR__ . '/../service/koneksi.php';

$email = $_POST['email'];
$password = $_POST['password'];

$query = "SELECT * FROM users WHERE email='$email'";
$result = mysqli_query($koneksi, $query);

if (!$result) {
    die(mysqli_error($koneksi));
}

$user = mysqli_fetch_assoc($result);

if ($user) {

    if (password_verify($password, $user['password'])) {

        // SESSION
        $_SESSION['email'] = $user['email'];
        $_SESSION['nama']  = $user['nama'];
        $_SESSION['role']  = $user['role'];

        // 🔥 REDIRECT BERDASARKAN ROLE
        if ($user['role'] == 'admin') {
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