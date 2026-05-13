<?php
require_once __DIR__ . '/../service/koneksi.php';

// 🔥 AMBIL DATA (AMAN)
$nama = $_POST['nama'] ?? '';
$tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
$jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
$provinsi = $_POST['provinsi'] ?? '';
$no_hp = $_POST['no_hp'] ?? '';
$komentar = $_POST['komentar'] ?? '';

// 🔐 AMBIL & PAKSA ANGKA
$q1 = (int) ($_POST['q1'] ?? 0);
$q2 = (int) ($_POST['q2'] ?? 0);
$q3 = (int) ($_POST['q3'] ?? 0);
$q4 = (int) ($_POST['q4'] ?? 0);
$q5 = (int) ($_POST['q5'] ?? 0);
$q6 = (int) ($_POST['q6'] ?? 0);
$q7 = (int) ($_POST['q7'] ?? 0);
$q8 = (int) ($_POST['q8'] ?? 0);
$q9 = (int) ($_POST['q9'] ?? 0);
$q10 = (int) ($_POST['q10'] ?? 0);

// VALIDASI MINIMAL
if ($nama == '' || $tanggal_lahir == '' || $jenis_kelamin == '') {
    setcookie("error", "Data wajib belum lengkap!", time() + 5, "/");
    header("Location: ../dashboard.php");
    exit;
}

// HITUNG SKOR
$total_skor = $q1 + $q2 + $q3 + $q4 + $q5 + $q6 + $q7 + $q8 + $q9 + $q10;

// 🔒 INSERT (AMAN)
$stmt = $koneksi->prepare("
    INSERT INTO survey (
        nama,
        tanggal_lahir,
        jenis_kelamin,
        provinsi,
        no_hp,
        q1,q2,q3,q4,q5,q6,q7,q8,q9,q10,
        komentar,
        total_skor
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
");

$stmt->bind_param(
    "ssssssssssssssssi",
    $nama,
    $tanggal_lahir,
    $jenis_kelamin,
    $provinsi,
    $no_hp,
    $q1,$q2,$q3,$q4,$q5,$q6,$q7,$q8,$q9,$q10,
    $komentar,
    $total_skor
);

// EKSEKUSI
if ($stmt->execute()) {
    setcookie("success", "Survey berhasil dikirim!", time() + 5, "/");
} else {
    setcookie("error", "Gagal mengirim survey!", time() + 5, "/");
}

header("Location: ../dashboard.php");
exit;
?>