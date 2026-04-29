<?php
ob_start();
session_start();
require_once __DIR__ . '/../service/koneksi.php';

$nama     = $_POST['nama'];
$email    = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$role     = "user";

$query = "INSERT INTO users (nama, email, password, role) 
          VALUES ('$nama', '$email', '$password', '$role')";
$result = mysqli_query($koneksi, $query);

if($result){
    $_SESSION['success'] = "Register berhasil!";
    header("Location: ../login.php");
} else {
    $_SESSION['error'] = "Register gagal!";
    header("Location: ../register.php");
}
?>