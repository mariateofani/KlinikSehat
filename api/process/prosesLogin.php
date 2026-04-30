<?php
ob_start();
require_once __DIR__ . '/../service/koneksi.php';

$email    = $_POST['email'];
$password = $_POST['password'];

$stmt = $koneksi->prepare("SELECT * FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {

    if (password_verify($password, $user['password'])) {

        // 🔐 SET COOKIE LOGIN
        setcookie("email", $user['email'], [
            'expires' => time() + 3600,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'None'
        ]);

        setcookie("nama", $user['nama'], [
            'expires' => time() + 3600,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'None'
        ]);

        setcookie("role", $user['role'] ?? 'user', [
            'expires' => time() + 3600,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'None'
        ]);

        // 🔀 REDIRECT BERDASARKAN ROLE
        if (($user['role'] ?? 'user') === 'admin') {
            header("Location: ../dashboard_admin.php");
        } else {
            header("Location: ../dashboard.php");
        }
        exit;

    } else {
        // ❌ ERROR PASSWORD
        setcookie("error", "Password salah!", time() + 5, "/");
        header("Location: ../login.php");
        exit;
    }

} else {
    // ❌ ERROR EMAIL
    setcookie("error", "Email tidak ditemukan!", time() + 5, "/");
    header("Location: ../login.php");
    exit;
}
?>