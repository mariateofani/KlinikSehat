<?php
// 🔐 CEK LOGIN DARI COOKIE
if (!isset($_COOKIE['email'])) {
    header("Location: login.php");
    exit;
}

// 🔥 AMBIL DATA DARI COOKIE
$nama  = $_COOKIE['nama']  ?? 'User';
$role  = $_COOKIE['role']  ?? 'user';
$email = $_COOKIE['email'] ?? '';

// 🔒 VALIDASI ROLE
if ($role !== 'user') {
    header("Location: login.php");
    exit;
}

// =====================
// AMBIL DATA SURVEY DARI DB (untuk grafik)
// =====================
include 'service/koneksi.php';

// Total responden
$total_resp = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM survey"))['total'] ?? 0;

// Rata-rata tiap pertanyaan
$avg_result = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT AVG(q1) a1, AVG(q2) a2, AVG(q3) a3, AVG(q4) a4, AVG(q5) a5,
            AVG(q6) a6, AVG(q7) a7, AVG(q8) a8, AVG(q9) a9, AVG(q10) a10,
            AVG(total_skor) as rata_total
     FROM survey"));

$rata_total   = $avg_result ? round($avg_result['rata_total'], 1) : 0;
$persen_puas  = $rata_total > 0 ? round(($rata_total / 40) * 100, 1) : 0;

// Data per kategori (untuk radar/bar chart)
$avg_q = [];
for ($i = 1; $i <= 10; $i++) {
    $avg_q[] = $avg_result ? round($avg_result["a$i"], 2) : 0;
}

// Distribusi skor (untuk doughnut chart)
$dist_kurang   = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM survey WHERE total_skor <= 15"))['c'] ?? 0;
$dist_cukup    = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM survey WHERE total_skor BETWEEN 16 AND 24"))['c'] ?? 0;
$dist_baik     = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM survey WHERE total_skor BETWEEN 25 AND 32"))['c'] ?? 0;
$dist_sangat   = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM survey WHERE total_skor >= 33"))['c'] ?? 0;

// Data per jenis kelamin
$jk_l = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM survey WHERE jenis_kelamin='L'"))['c'] ?? 0;
$jk_p = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM survey WHERE jenis_kelamin='P'"))['c'] ?? 0;

// Apakah user sudah pernah isi survey?
$sudah_survey = false;
$stmt_chk = $koneksi->prepare("SELECT COUNT(*) as c FROM survey WHERE nama = (SELECT nama FROM users WHERE email=?)");
$stmt_chk->bind_param("s", $email);
$stmt_chk->execute();
$res_chk = $stmt_chk->get_result()->fetch_assoc();
// (opsional info saja)
?>
<?php if (isset($_COOKIE['success'])) { ?>
    <div id="alert-success" style="position:fixed;top:20px;right:20px;z-index:9999;"
         class="bg-green-500 text-white px-5 py-3 rounded-lg shadow-lg">
        <?= htmlspecialchars($_COOKIE['success']); ?>
    </div>
<?php setcookie("success", "", time() - 3600, "/"); } ?>

<?php if (isset($_COOKIE['error'])) { ?>
    <div id="alert-error" style="position:fixed;top:20px;right:20px;z-index:9999;"
         class="bg-red-500 text-white px-5 py-3 rounded-lg shadow-lg">
        <?= htmlspecialchars($_COOKIE['error']); ?>
    </div>
<?php setcookie("error", "", time() - 3600, "/"); } ?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard – Klinik Sehat</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    html { scroll-behavior: smooth; }
    .card-stat { transition: transform 0.2s, box-shadow 0.2s; }
    .card-stat:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.12); }
  </style>
</head>

<body class="bg-gray-100">

<!-- ===================== -->
<!-- NAVBAR                -->
<!-- ===================== -->
<nav class="bg-white shadow-md sticky top-0 z-50">
  <div class="max-w-6xl mx-auto flex justify-between items-center p-4">
    <div class="flex items-center gap-2">
      <span class="text-2xl">🏥</span>
      <h1 class="font-bold text-blue-600 text-xl">Klinik Sehat</h1>
    </div>

    <div class="hidden md:flex items-center gap-6 text-gray-600 text-sm font-medium">
      <a href="#info" class="hover:text-blue-600 transition">Info</a>
      <a href="#grafik" class="hover:text-blue-600 transition">Grafik Survey</a>
    </div>

    <div class="flex items-center gap-4">
      <div class="flex items-center gap-2">
        <div class="bg-blue-100 text-blue-700 w-9 h-9 rounded-full flex items-center justify-center font-bold text-sm">
          <?= strtoupper(substr($nama, 0, 1)); ?>
        </div>
        <span class="text-gray-700 text-sm">Halo, <b><?= htmlspecialchars($nama); ?></b></span>
      </div>
      <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 text-sm transition">
        Logout
      </a>
    </div>
  </div>
</nav>

<!-- ===================== -->
<!-- HERO                  -->
<!-- ===================== -->
<section class="bg-sky-100 py-16">
  <div class="max-w-6xl mx-auto grid md:grid-cols-2 items-center gap-10 px-6">
    <div>
      <h1 class="text-4xl font-bold text-blue-700 mt-4 mb-3">
        Selamat Datang, <span class="text-blue-900"><?= htmlspecialchars($nama); ?></span>!
      </h1>
      <p class="text-gray-600 mb-6 leading-relaxed">
        Pantau layanan, lihat informasi terkini klinik, dan berikan penilaian
        kepuasan Anda. Data survey Anda membantu kami meningkatkan kualitas pelayanan.
      </p>
      <div class="flex flex-wrap gap-3">
        <a href="survey.php" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition shadow-md font-semibold">
          📝 Isi Survey
        </a>
        <a href="#grafik" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition shadow-md font-semibold">
          Lihat Grafik ↓
        </a>
      </div>
    </div>
    <div class="relative">
      <img src="https://i.pinimg.com/736x/20/95/a7/2095a72f390f72074776019b7004d776.jpg"
           class="rounded-2xl shadow-2xl w-full object-cover" alt="Klinik Sehat">
      <div class="absolute -bottom-4 -left-4 bg-white shadow-lg rounded-xl px-4 py-3 flex items-center gap-2">
        <span class="text-green-500 text-xl">✅</span>
        <div>
          <p class="text-xs text-gray-500">Login sebagai</p>
          <p class="text-sm font-bold text-green-600">Pasien Terdaftar</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===================== -->
<!-- RINGKASAN STATISTIK   -->
<!-- ===================== -->
<section id="info" class="max-w-6xl mx-auto py-10 px-6">
  <h2 class="text-2xl font-bold mb-2 text-gray-800">Ringkasan Data Klinik</h2>
  <p class="text-gray-500 mb-6 text-sm">Data diperbarui secara langsung dari sistem.</p>

  <div class="grid md:grid-cols-4 gap-5">

    <div class="card-stat bg-white rounded-xl shadow p-5 border-l-4 border-blue-500">
      <p class="text-3xl font-bold text-blue-600"><?= $total_resp ?></p>
      <p class="text-gray-600 mt-1 font-medium">Total Responden Survey</p>
      <p class="text-xs text-gray-400 mt-1">Semua pasien yang telah mengisi survey</p>
    </div>

    <div class="card-stat bg-white rounded-xl shadow p-5 border-l-4 border-green-500">
      <p class="text-3xl font-bold text-green-600"><?= $rata_total ?> <span class="text-lg text-gray-400">/40</span></p>
      <p class="text-gray-600 mt-1 font-medium">Rata-rata Skor Kepuasan</p>
      <p class="text-xs text-gray-400 mt-1">Skor maksimal adalah 40 poin</p>
    </div>

    <div class="card-stat bg-white rounded-xl shadow p-5 border-l-4 border-purple-500">
      <p class="text-3xl font-bold text-purple-600"><?= $persen_puas ?>%</p>
      <p class="text-gray-600 mt-1 font-medium">Tingkat Kepuasan</p>
      <p class="text-xs text-gray-400 mt-1">Persentase kepuasan pasien secara keseluruhan</p>
    </div>

    <div class="card-stat bg-white rounded-xl shadow p-5 border-l-4 border-orange-500">
      <p class="text-3xl font-bold text-orange-600"><?= $dist_sangat ?></p>
      <p class="text-gray-600 mt-1 font-medium">Pasien Sangat Puas</p>
      <p class="text-xs text-gray-400 mt-1">Skor ≥ 33 dari 40 poin</p>
    </div>

  </div>
</section>

<!-- ===================== -->
<!-- GRAFIK SURVEY LIVE    -->
<!-- ===================== -->
<section id="grafik" class="max-w-6xl mx-auto pb-10 px-6">
  <h2 class="text-2xl font-bold mb-2 text-gray-800">Grafik Hasil Survey Kepuasan</h2>
  <p class="text-gray-500 mb-6 text-sm">
    Grafik di bawah menampilkan data hasil survey terkini secara langsung dari database.
    <?php if ($total_resp > 0): ?>
      Total <strong><?= $total_resp ?> responden</strong> telah mengisi survey.
    <?php else: ?>
      Belum ada data survey yang masuk.
    <?php endif; ?>
  </p>

  <?php if ($total_resp == 0): ?>
  <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-8 text-center text-yellow-700">
    <p class="text-5xl mb-4">📋</p>
    <p class="font-semibold text-lg mb-2">Belum Ada Data Survey</p>
    <p class="text-sm">Jadilah yang pertama mengisi survey kepuasan untuk membantu klinik kami!</p>
    <a href="survey.php" class="inline-block mt-4 bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
      Isi Survey Sekarang
    </a>
  </div>
  <?php else: ?>

  <div class="grid md:grid-cols-2 gap-6 mb-6">

    <!-- Grafik Bar – Rata-rata per Aspek -->
    <div class="bg-white rounded-xl shadow p-6">
      <h3 class="font-bold text-gray-800 mb-1">📊 Rata-rata Skor per Aspek Pelayanan</h3>
      <p class="text-xs text-gray-500 mb-4">Skala 1–4 per aspek</p>
      <canvas id="chartBar" height="220"></canvas>
    </div>

    <!-- Grafik Doughnut – Distribusi Kepuasan -->
    <div class="bg-white rounded-xl shadow p-6">
      <h3 class="font-bold text-gray-800 mb-1">🥧 Distribusi Tingkat Kepuasan</h3>
      <p class="text-xs text-gray-500 mb-4">Berdasarkan total skor survey</p>
      <canvas id="chartDoughnut" height="220"></canvas>
    </div>

  </div>

  <div class="grid md:grid-cols-2 gap-6">

    <!-- Grafik Pie – Jenis Kelamin -->
    <div class="bg-white rounded-xl shadow p-6">
      <h3 class="font-bold text-gray-800 mb-1">👥 Responden Berdasarkan Jenis Kelamin</h3>
      <p class="text-xs text-gray-500 mb-4">Data demografis responden survey</p>
      <canvas id="chartGender" height="220"></canvas>
    </div>

    <!-- Tabel Ringkasan -->
    <div class="bg-white rounded-xl shadow p-6">
      <h3 class="font-bold text-gray-800 mb-4">📋 Ringkasan Aspek Penilaian</h3>
      <div class="overflow-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-blue-50 text-blue-700">
              <th class="text-left p-2 rounded-l">Aspek</th>
              <th class="p-2">Rata-rata</th>
              <th class="p-2 rounded-r">Kategori</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $aspek = [
              "Keramahan Petugas","Kecepatan Pelayanan","Fasilitas Kesehatan",
              "Kebersihan Ruangan","Kenyamanan Ruang Tunggu","Informasi Layanan",
              "Sikap Dokter","Prosedur Pelayanan","Kualitas Pelayanan","Kepuasan Keseluruhan"
            ];
            for ($i = 0; $i < 10; $i++) {
              $v = $avg_q[$i];
              $kat = $v >= 3.5 ? ['Sangat Baik','bg-green-100 text-green-700']
                   : ($v >= 2.5 ? ['Baik','bg-blue-100 text-blue-700']
                   : ($v >= 1.5 ? ['Cukup','bg-yellow-100 text-yellow-700']
                                : ['Kurang','bg-red-100 text-red-700']));
            ?>
            <tr class="border-b border-gray-50 hover:bg-gray-50">
              <td class="p-2 text-gray-700"><?= $aspek[$i] ?></td>
              <td class="p-2 text-center font-semibold"><?= $v ?></td>
              <td class="p-2 text-center">
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold <?= $kat[1] ?>">
                  <?= $kat[0] ?>
                </span>
              </td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
  <?php endif; ?>
</section>
</section>

<!-- FOOTER -->
<footer class="text-center p-6 text-gray-500 bg-white border-t">
  © 2026 Klinik Sehat Digital
</footer>

<!-- ===================== -->
<!-- CHART.JS SCRIPTS      -->
<!-- ===================== -->
<script>
<?php if ($total_resp > 0): ?>

// Data dari PHP
const avgQ   = <?= json_encode($avg_q) ?>;
const labels = [
  "Keramahan","Kecepatan","Fasilitas","Kebersihan","Ruang Tunggu",
  "Info Layanan","Sikap Dokter","Prosedur","Kualitas","Kepuasan"
];

// === BAR CHART – rata-rata per aspek ===
new Chart(document.getElementById('chartBar'), {
  type: 'bar',
  data: {
    labels: labels,
    datasets: [{
      label: 'Rata-rata Skor (maks 4)',
      data: avgQ,
      backgroundColor: [
        '#3B82F6','#10B981','#8B5CF6','#F59E0B','#EF4444',
        '#06B6D4','#84CC16','#F97316','#EC4899','#6366F1'
      ],
      borderRadius: 6,
    }]
  },
  options: {
    responsive: true,
    scales: {
      y: { beginAtZero: true, max: 4,
           ticks: { stepSize: 1 },
           grid: { color: '#F3F4F6' } },
      x: { ticks: { font: { size: 10 } } }
    },
    plugins: { legend: { display: false } }
  }
});

// === DOUGHNUT – distribusi kepuasan ===
new Chart(document.getElementById('chartDoughnut'), {
  type: 'doughnut',
  data: {
    labels: ['Kurang (≤15)', 'Cukup (16-24)', 'Baik (25-32)', 'Sangat Baik (≥33)'],
    datasets: [{
      data: [
        <?= $dist_kurang ?>,
        <?= $dist_cukup  ?>,
        <?= $dist_baik   ?>,
        <?= $dist_sangat ?>
      ],
      backgroundColor: ['#EF4444','#F59E0B','#3B82F6','#10B981'],
      borderWidth: 2
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: 'bottom', labels: { padding: 15, font: { size: 11 } } }
    },
    cutout: '60%'
  }
});

// === PIE – jenis kelamin ===
new Chart(document.getElementById('chartGender'), {
  type: 'pie',
  data: {
    labels: ['Laki-laki', 'Perempuan'],
    datasets: [{
      data: [<?= $jk_l ?>, <?= $jk_p ?>],
      backgroundColor: ['#3B82F6','#EC4899'],
      borderWidth: 2
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: 'bottom', labels: { padding: 15 } }
    }
  }
});

<?php endif; ?>

// Auto-hide alerts
setTimeout(() => {
  document.getElementById('alert-success')?.remove();
  document.getElementById('alert-error')?.remove();
}, 4000);
</script>

</body>
</html>