<?php
ob_start();
require_once __DIR__ . '/../service/koneksi.php';

$email    = $_POST['email'];
$password = $_POST['password'];

$stmt = $koneksi->prepare("SELECT * FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user   = $result->fetch_assoc();

if ($user) {

    if (password_verify($password, $user['password'])) {

        $role = $user['role'] ?? 'user';

        // 🔐 SET COOKIE LOGIN
        $cookie_opts = [
            'expires'  => time() + 3600,
            'path'     => '/',
            'secure'   => true,
            'httponly' => true,
            'samesite' => 'None'
        ];

        setcookie("email", $user['email'], $cookie_opts);
        setcookie("nama",  $user['nama'],  $cookie_opts);
        setcookie("role",  $role,          $cookie_opts);

        // 🔀 REDIRECT BERDASARKAN ROLE
        if ($role === 'admin') {
            header("Location: ../dashboard_admin.php");
        } elseif ($role === 'manager') {
            header("Location: ../dashboard_manager.php");
        } else {
            header("Location: ../dashboard.php");
        }
        exit;

    } else {
        setcookie("error", "Password salah!", time() + 5, "/");
        header("Location: ../login.php");
        exit;
    }

} else {
    setcookie("error", "Email tidak ditemukan!", time() + 5, "/");
    header("Location: ../login.php");
    exit;
}
?>