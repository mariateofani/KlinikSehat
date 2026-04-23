<?php
session_start();

if (!isset($_SESSION['email']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}
?>
<?php if (isset($_SESSION['success'])) { ?>
    <div class="bg-green-500 text-white p-3 rounded-lg mb-4 text-center">
        <?= $_SESSION['success']; ?>
    </div>
<?php unset($_SESSION['success']); } ?>

<?php if (isset($_SESSION['error'])) { ?>
    <div class="bg-red-500 text-white p-3 rounded-lg mb-4 text-center">
        <?= $_SESSION['error']; ?>
    </div>
<?php unset($_SESSION['error']); } ?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Klinik</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-6">

<!-- NAVBAR -->
<nav class="bg-white shadow-md">
  <div class="max-w-6xl mx-auto flex justify-between items-center p-4">
    <h1 class="font-bold text-blue-600 text-xl">Klinik Sehat</h1>

    <div class="flex items-center gap-4">
      <span class="text-gray-600">
        Halo, <b><?= $_SESSION['nama']; ?></b>
      </span>

      <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">
        Logout
      </a>
    </div>
  </div>
</nav>

<!-- HERO -->
<section class="bg-sky-100 py-20">
  <div class="max-w-6xl mx-auto grid md:grid-cols-2 items-center gap-10 px-6">

    <div>
      <h1 class="text-4xl font-bold text-blue-700 mb-6">
        Selamat Datang di Layanan Kesehatan Digital, 
        <span class="text-blue-900"><?= $_SESSION['nama']; ?></span>
      </h1>

      <p class="text-gray-600 mb-6">
        Klinik Sehat menyediakan layanan kesehatan modern dengan sistem digital untuk mempermudah pasien mendapatkan pelayanan secara cepat dan realtime.
      </p>

      <a href="#layanan" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
        Lihat Layanan
      </a>
    </div>

    <img src="https://i.pinimg.com/736x/20/95/a7/2095a72f390f72074776019b7004d776.jpg"
         class="rounded-xl shadow-lg">
  </div>
</section>

<!-- INFORMASI -->
<section class="max-w-6xl mx-auto py-16 px-6">
  <h2 class="text-2xl font-bold text-center mb-4">Informasi Klinik</h2>

  <p class="text-center text-gray-600 max-w-3xl mx-auto">
    Klinik Sehat memberikan pelayanan kesehatan umum, pemeriksaan rutin, konsultasi dokter, serta berbagai layanan kesehatan lainnya untuk masyarakat. Kami berkomitmen memberikan pelayanan yang ramah, cepat, dan profesional.
  </p>
</section>

<!-- LAYANAN -->
<section id="layanan" class="max-w-6xl mx-auto pb-16 px-6">
  <h2 class="text-2xl font-bold text-center mb-10">Layanan Klinik</h2>

  <div class="grid md:grid-cols-3 gap-8">

    <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-xl hover:-translate-y-2 transition">
      <img src="https://i.pinimg.com/736x/20/95/a7/2095a72f390f72074776019b7004d776.jpg" class="rounded mb-4">
      <h3 class="font-bold text-lg mb-2">Pemeriksaan Umum</h3>
      <p class="text-gray-600">Pemeriksaan kesehatan oleh tenaga medis profesional.</p>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-xl hover:-translate-y-2 transition">
      <img src="https://i.pinimg.com/736x/bc/cb/37/bccb37b604d37d26e1f506522af1b4ad.jpg" class="rounded mb-4">
      <h3 class="font-bold text-lg mb-2">Konsultasi Dokter</h3>
      <p class="text-gray-600">Konsultasi dengan dokter untuk berbagai masalah kesehatan.</p>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-xl hover:-translate-y-2 transition">
      <img src="https://i.pinimg.com/736x/6e/b1/1a/6eb11a8180352442c3ee0d168d765608.jpg" class="rounded mb-4">
      <h3 class="font-bold text-lg mb-2">Pelayanan Gigi</h3>
      <p class="text-gray-600">Perawatan kesehatan gigi oleh dokter gigi profesional.</p>
    </div>

  </div>

  <!-- 🔥 BUTTON SURVEY -->
  <div class="text-center mt-10">
    <a href="survey.php"
       class="inline-block bg-green-600 text-white px-6 py-3 rounded-lg shadow-md hover:bg-green-700 transition">
      Isi Survey Kepuasan
    </a>
  </div>

</section>

<!-- FITUR -->
<section class="bg-blue-50 py-16">
  <div class="max-w-6xl mx-auto text-center px-6">
    <h2 class="text-2xl font-bold mb-10">Fitur Klinik Sehat Digital</h2>

    <div class="grid md:grid-cols-4 gap-6">

      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="font-bold mb-2">Pendaftaran Online</h3>
        <p class="text-gray-600 text-sm">Pasien dapat mendaftar tanpa harus datang ke klinik.</p>
      </div>

      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="font-bold mb-2">Informasi Realtime</h3>
        <p class="text-gray-600 text-sm">Pasien dapat melihat informasi layanan secara realtime.</p>
      </div>

      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="font-bold mb-2">Profil Pasien</h3>
        <p class="text-gray-600 text-sm">Data pasien tersimpan secara digital.</p>
      </div>

      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="font-bold mb-2">Pelayanan Cepat</h3>
        <p class="text-gray-600 text-sm">Sistem pelayanan lebih cepat dan efisien.</p>
      </div>

    </div>
  </div>
</section>

<!-- FOOTER -->
<footer class="text-center p-6 text-gray-500">
  © 2026 Klinik Sehat Digital
</footer>

</body>
</html>