<?php session_start(); ?>

<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sistem Survei Kepuasan Pasien</title>

  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">


<div class="max-w-5xl mx-auto p-6">

  <!-- HEADER -->
  <div class="bg-white p-6 rounded-xl shadow text-center mb-6">

    <h1 class="text-2xl font-bold">Survei Kepuasan Pasien</h1>
    <p class="mb-5 text-gray-600">
      Silakan isi data dan jawaban dengan jujur
    </p>

    <a href="dashboard.php"
       class="inline-block bg-blue-600 text-white px-4 py-2 rounded-lg shadow-md hover:bg-green-700 transition">
      ← Kembali ke Dashboard
    </a>
  </div>

  <!-- FORM -->
  <form action="process/prosesSurvey.php" method="POST"
        class="bg-white p-6 rounded-xl shadow space-y-6">

    <!-- DATA PASIEN -->
    <div class= p-5>
        <h2 class="text-center text-lg font-bold mb-3">Data Pasien</h2>
        <h3 class="text-lg font-bold mb-1">
            Nama
        </h3>
        <input type="text" name="nama" placeholder="Nama Pasien"
             class="w-full border p-2 rounded mb-3" required>
        <h3 class="text-lg font-bold mb-1">
            Tanggal Lahir
        </h3>
        <input type="date" name="tanggal_lahir" placeholder="Tanggal Lahir"
        class="w-full border p-2 rounded mb-3" required>
        <h3 class="text-lg font-bold mb-1">
            Jenis Kelamin
        </h3>

        <select name="jenis_kelamin" 
            class="w-full border p-2 rounded mb-3" required>
                <option value="">Pilih Jenis Kelamin</option>
                <option value="L">Laki-laki</option>
                <option value="P">Perempuan</option>
        </select>
        
        <h3 class="text-lg font-bold mb-1">
            Provinsi
        </h3>
        <select id="provinsi" name="provinsi"
            class="w-full border p-2 rounded mb-3" required>
            <option value="">Pilih Provinsi</option>
        </select>
        <h3 class="text-lg font-bold mb-1">
            No. HP Pasien
        </h3>
        <input type="text" name="no_hp" placeholder="No. HP Pasien"
             class="w-full border p-2 rounded" required mb-10>

    </div>

    <!-- PERTANYAAN -->
    <div class="p-5">
      <h2 class="text-center text-lg font-bold mb-5">Pertanyaan Survei</h2>

      <?php
      $pertanyaan = [
        "Keramahan petugas",
        "Kecepatan pelayanan",
        "Fasilitas kesehatan",
        "Kebersihan ruangan",
        "Kenyamanan ruang tunggu",
        "Informasi layanan",
        "Sikap dokter",
        "Prosedur pelayanan",
        "Kualitas pelayanan",
        "Kepuasan keseluruhan"
      ];

      $i = 1;
      foreach ($pertanyaan as $p) {
      ?>

      <div class="mb-4">
        <p class="font-medium"><?= $i . ". " . $p ?></p>

        <select name="q<?= $i ?>" class="w-full border p-2 rounded" required>
          <option value="">Pilih jawaban</option>
          <option value="4">Sangat Baik</option>
          <option value="3">Baik</option>
          <option value="2">Cukup</option>
          <option value="1">Kurang</option>
        </select>
      </div>

      <?php $i++; } ?>
    </div>

    <!-- KOMENTAR -->
    <div class= p-5>
      <h2 class="text-lg font-bold mb-2">Kritik & Saran</h2>

      <textarea name="komentar" rows="4"
                class="w-full border p-2 rounded"
                placeholder="Tuliskan kritik atau saran"></textarea>
    </div>

    <!-- BUTTON -->
    <button type="submit"
            class="p-5 bg-green-600 text-white px-6 py-3 rounded hover:bg-green-700 w-full">
      Kirim Survey
    </button>

  </form>
</div>
<script>
fetch("get_alamat.php")
  .then(response => response.json())
  .then(res => {
    console.log(res); // buat debug

    let data = res.data[1]; // ambil data provinsi

    let select = document.getElementById("provinsi");

    data.forEach(item => {
      let option = document.createElement("option");
      option.value = item.domain_name;
      option.textContent = item.domain_name;
      select.appendChild(option);
    });
  })
  .catch(error => {
    console.error("Error ambil data BPS:", error);
  });
</script>
</body>
</html>