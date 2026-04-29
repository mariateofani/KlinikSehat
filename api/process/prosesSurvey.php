<?php
session_start();
require_once __DIR__ . '/../service/koneksi.php';

// DATA PASIEN
$nama = $_POST['nama'];
$tanggal_lahir = $_POST['tanggal_lahir'];
$jenis_kelamin = $_POST['jenis_kelamin'];
$provinsi = $_POST['provinsi'];
$no_hp = $_POST['no_hp'];

// JAWABAN SURVEY
$q1 = $_POST['q1'];
$q2 = $_POST['q2'];
$q3 = $_POST['q3'];
$q4 = $_POST['q4'];
$q5 = $_POST['q5'];
$q6 = $_POST['q6'];
$q7 = $_POST['q7'];
$q8 = $_POST['q8'];
$q9 = $_POST['q9'];
$q10 = $_POST['q10'];

$komentar = $_POST['komentar'];

// HITUNG TOTAL SKOR
$total_skor = $q1 + $q2 + $q3 + $q4 + $q5 + $q6 + $q7 + $q8 + $q9 + $q10;

// INSERT KE DATABASE
$query = "INSERT INTO survey (
    nama,
    tanggal_lahir,
    jenis_kelamin,
    provinsi,
    no_hp,
    q1,q2,q3,q4,q5,q6,q7,q8,q9,q10,
    komentar,
    total_skor
) VALUES (
    '$nama',
    '$tanggal_lahir',
    '$jenis_kelamin',
    '$provinsi',
    '$no_hp',
    '$q1','$q2','$q3','$q4','$q5','$q6','$q7','$q8','$q9','$q10',
    '$komentar',
    '$total_skor'
)";


// setelah INSERT berhasil/gagal
if (mysqli_query($koneksi, $query)) {
    $_SESSION['success'] = "Survey berhasil dikirim!";
} else {
    $_SESSION['error'] = "Gagal mengirim survey!";
}

header("Location: ../dashboard.php");
exit;
?>