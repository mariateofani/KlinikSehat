<?php
session_start();
include '../service/koneksi.php';

if (!isset($_SESSION['email']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$id    = $_POST['id'];
$nama  = $_POST['nama'];
$email = $_POST['email'];
$role  = $_POST['role'];

// UPDATE (pakai id_user atau id)
$query = mysqli_query($koneksi, "
    UPDATE users 
    SET nama='$nama', email='$email', role='$role'
    WHERE id_user='$id'
");

if ($query) {
    header("Location: ../dashboard_admin.php?edit=berhasil");
} else {
    echo "Gagal update data!";
}
?>