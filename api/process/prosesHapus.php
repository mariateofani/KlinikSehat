<?php
session_start();
include '../service/koneksi.php';

if (!isset($_SESSION['email']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = $_GET['id'];

$query = mysqli_query($koneksi, "
    DELETE FROM users 
    WHERE id_user='$id'
");

if ($query) {
    header("Location: ../dashboard_admin.php?hapus=berhasil");
} else {
    echo "Gagal hapus data!";
}
?>