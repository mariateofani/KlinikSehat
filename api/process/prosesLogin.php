<?php
ob_start();
session_start();

require_once __DIR__ . '/../service/koneksi.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$stmt = $koneksi->prepare("SELECT * FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {

    if (password_verify($password, $user['password'])) {

        $role = $user['role'] ?? 'user';

        // COOKIE LOGIN
        $cookie_opts = [
            'expires'  => time() + 3600,
            'path'     => '/',
            'secure'   => false, // ubah true kalau sudah HTTPS
            'httponly' => true,
            'samesite' => 'Lax'
        ];

        setcookie("email", $user['email'], $cookie_opts);
        setcookie("nama", $user['nama'], $cookie_opts);
        setcookie("role", $role, $cookie_opts);

        // hapus pesan lama
        unset($_SESSION['error']);
        unset($_SESSION['success']);

        // redirect sesuai role
        if ($role === 'admin') {
            header("Location: ../dashboard_admin.php");
        } elseif ($role === 'manager') {
            header("Location: ../dashboard_manager.php");
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
?>