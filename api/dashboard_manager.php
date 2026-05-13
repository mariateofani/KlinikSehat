<?php
include 'service/koneksi.php';

// 🔐 CEK LOGIN DARI COOKIE
if (!isset($_COOKIE['email'])) {
    header("Location: login.php");
    exit;
}

// 🔥 AMBIL DATA COOKIE
$nama  = $_COOKIE['nama']  ?? 'Manager';
$role  = $_COOKIE['role']  ?? 'user';

// 🔒 VALIDASI HARUS MANAGER
if ($role !== 'manager') {
    header("Location: login.php");
    exit;
}

// =====================
// FILTER & PENCARIAN
// =====================
$filter_jk     = $_GET['jk']      ?? '';
$filter_prov   = $_GET['provinsi'] ?? '';
$search        = $_GET['search']   ?? '';
$export        = $_GET['export']   ?? '';

// =====================
// QUERY DATA SURVEY
// =====================
$where = "WHERE 1=1";
if ($filter_jk)   $where .= " AND jenis_kelamin='" . mysqli_real_escape_string($koneksi, $filter_jk) . "'";
if ($filter_prov) $where .= " AND provinsi='" . mysqli_real_escape_string($koneksi, $filter_prov) . "'";
if ($search)      $where .= " AND (nama LIKE '%" . mysqli_real_escape_string($koneksi, $search) . "%' OR provinsi LIKE '%" . mysqli_real_escape_string($koneksi, $search) . "%')";

$data_survey = mysqli_query($koneksi, "SELECT * FROM survey $where ORDER BY id DESC");

// =====================
// STATISTIK
// =====================
$stats = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT COUNT(*) as total,
            AVG(total_skor) as rata,
            MAX(total_skor) as maks,
            MIN(total_skor) as mini
     FROM survey $where"));

$total_resp  = $stats['total'] ?? 0;
$rata_total  = $stats['rata']  ? round($stats['rata'], 1) : 0;
$skor_maks   = $stats['maks']  ?? 0;
$skor_mini   = $stats['mini']  ?? 0;
$persen_puas = $rata_total > 0 ? round(($rata_total / 40) * 100, 1) : 0;

// Distribusi
$dist_kurang = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM survey $where AND total_skor <= 15"))['c'] ?? 0;
$dist_cukup  = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM survey $where AND total_skor BETWEEN 16 AND 24"))['c'] ?? 0;
$dist_baik   = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM survey $where AND total_skor BETWEEN 25 AND 32"))['c'] ?? 0;
$dist_sangat = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM survey $where AND total_skor >= 33"))['c'] ?? 0;

// Rata-rata per aspek
$avg_res = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT AVG(q1) a1,AVG(q2) a2,AVG(q3) a3,AVG(q4) a4,AVG(q5) a5,
            AVG(q6) a6,AVG(q7) a7,AVG(q8) a8,AVG(q9) a9,AVG(q10) a10
     FROM survey $where"));
$avg_q = [];
for ($i = 1; $i <= 10; $i++) {
    $avg_q[] = $avg_res ? round($avg_res["a$i"], 2) : 0;
}

// Jenis kelamin
$jk_l = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM survey $where AND jenis_kelamin='L'"))['c'] ?? 0;
$jk_p = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM survey $where AND jenis_kelamin='P'"))['c'] ?? 0;

// List provinsi (untuk filter)
$list_prov = mysqli_query($koneksi, "SELECT DISTINCT provinsi FROM survey ORDER BY provinsi");

// =====================
// EXPORT CSV
// =====================
if ($export === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="laporan_survey_kliniksehat_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');
    // BOM for Excel agar UTF-8 terbaca
    fputs($out, "\xEF\xBB\xBF");
    fputcsv($out, ['No','Nama','Tanggal Lahir','Jenis Kelamin','Provinsi','No HP',
                   'Q1','Q2','Q3','Q4','Q5','Q6','Q7','Q8','Q9','Q10',
                   'Total Skor','Komentar','Kategori']);
    $rows = mysqli_query($koneksi, "SELECT * FROM survey $where ORDER BY id DESC");
    $no = 1;
    while ($r = mysqli_fetch_assoc($rows)) {
        $kat = $r['total_skor'] >= 33 ? 'Sangat Baik'
             : ($r['total_skor'] >= 25 ? 'Baik'
             : ($r['total_skor'] >= 16 ? 'Cukup' : 'Kurang'));
        fputcsv($out, [
            $no++, $r['nama'], $r['tanggal_lahir'], $r['jenis_kelamin'],
            $r['provinsi'], $r['no_hp'],
            $r['q1'],$r['q2'],$r['q3'],$r['q4'],$r['q5'],
            $r['q6'],$r['q7'],$r['q8'],$r['q9'],$r['q10'],
            $r['total_skor'], $r['komentar'], $kat
        ]);
    }
    fclose($out);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Manager – Klinik Sehat</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    html { scroll-behavior: smooth; }
    .card-stat { transition: transform 0.2s, box-shadow 0.2s; }
    .card-stat:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.12); }
    @media print {
      .no-print { display: none !important; }
      body { background: white !important; }
    }
  </style>
</head>

<body class="bg-gray-100">

<!-- ===================== -->
<!-- NAVBAR                -->
<!-- ===================== -->
<nav class="bg-white shadow-md sticky top-0 z-50 no-print">
  <div class="max-w-6xl mx-auto flex justify-between items-center p-4">
    <div class="flex items-center gap-2">
      <span class="text-2xl">🏥</span>
      <h1 class="font-bold text-blue-600 text-xl">Klinik Sehat</h1>
      <span class="bg-orange-100 text-orange-700 text-xs font-semibold px-2 py-0.5 rounded-full ml-2">Manager</span>
    </div>

    <div class="hidden md:flex items-center gap-6 text-gray-600 text-sm font-medium">
      <a href="#statistik" class="hover:text-blue-600 transition">Statistik</a>
      <a href="#grafik"    class="hover:text-blue-600 transition">Grafik</a>
      <a href="#laporan"   class="hover:text-blue-600 transition">Laporan</a>
    </div>

    <div class="flex items-center gap-4">
      <div class="flex items-center gap-2">
        <div class="bg-orange-100 text-orange-700 w-9 h-9 rounded-full flex items-center justify-center font-bold text-sm">
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
<section class="bg-gradient-to-r from-orange-50 to-amber-50 py-16">
  <div class="max-w-6xl mx-auto grid md:grid-cols-2 items-center gap-10 px-6">
    <div>
      <h1 class="text-4xl font-bold text-orange-700 mt-4 mb-3">
        Laporan Survey Kepuasan, <span class="text-orange-900"><?= htmlspecialchars($nama); ?></span>!
      </h1>
      <p class="text-gray-600 mb-6 leading-relaxed">
        Pantau dan analisis hasil survey kepuasan pasien secara menyeluruh.
        Data dapat diunduh dalam format CSV atau dicetak sebagai laporan resmi.
      </p>
      <div class="flex flex-wrap gap-3">
        <a href="#laporan" class="bg-orange-600 text-white px-6 py-3 rounded-lg hover:bg-orange-700 transition shadow-md font-semibold">
          📋 Lihat Laporan
        </a>
        <a href="?export=csv<?= $filter_jk ? '&jk='.$filter_jk : '' ?><?= $filter_prov ? '&provinsi='.urlencode($filter_prov) : '' ?><?= $search ? '&search='.urlencode($search) : '' ?>"
           class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition shadow-md font-semibold">
          ⬇️ Unduh CSV
        </a>
        <a href="#grafik" class="border border-orange-600 text-orange-600 px-6 py-3 rounded-lg hover:bg-orange-50 transition font-semibold">
          Lihat Grafik ↓
        </a>
      </div>
    </div>

    <div class="relative">
      <img src="https://i.pinimg.com/736x/20/95/a7/2095a72f390f72074776019b7004d776.jpg"
           class="rounded-2xl shadow-2xl w-full object-cover" alt="Klinik Sehat Manager">
      <div class="absolute -bottom-4 -left-4 bg-white shadow-lg rounded-xl px-4 py-3 flex items-center gap-2">
        <span class="text-orange-500 text-xl">📊</span>
        <div>
          <p class="text-xs text-gray-500">Login sebagai</p>
          <p class="text-sm font-bold text-orange-600">Manager Klinik</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===================== -->
<!-- STATISTIK RINGKASAN   -->
<!-- ===================== -->
<section id="statistik" class="max-w-6xl mx-auto py-10 px-6">
  <h2 class="text-2xl font-bold mb-2 text-gray-800">Ringkasan Statistik Survey</h2>
  <p class="text-gray-500 mb-6 text-sm">Data langsung dari database, diperbarui secara real-time.</p>

  <div class="grid md:grid-cols-4 gap-5 mb-4">

    <div class="card-stat bg-white rounded-xl shadow p-5 border-l-4 border-blue-500">
      <p class="text-3xl font-bold text-blue-600"><?= $total_resp ?></p>
      <p class="text-gray-600 mt-1 font-medium">Total Responden</p>
      <p class="text-xs text-gray-400 mt-1">Survey yang telah diterima</p>
    </div>

    <div class="card-stat bg-white rounded-xl shadow p-5 border-l-4 border-green-500">
      <p class="text-3xl font-bold text-green-600"><?= $rata_total ?><span class="text-lg text-gray-400">/40</span></p>
      <p class="text-gray-600 mt-1 font-medium">Rata-rata Skor</p>
      <p class="text-xs text-gray-400 mt-1">Skor maksimal 40 poin</p>
    </div>

    <div class="card-stat bg-white rounded-xl shadow p-5 border-l-4 border-purple-500">
      <p class="text-3xl font-bold text-purple-600"><?= $persen_puas ?>%</p>
      <p class="text-gray-600 mt-1 font-medium">Tingkat Kepuasan</p>
      <p class="text-xs text-gray-400 mt-1">Persentase dari skor maksimal</p>
    </div>

    <div class="card-stat bg-white rounded-xl shadow p-5 border-l-4 border-orange-500">
      <p class="text-3xl font-bold text-orange-600"><?= $dist_sangat ?></p>
      <p class="text-gray-600 mt-1 font-medium">Sangat Puas</p>
      <p class="text-xs text-gray-400 mt-1">Skor ≥ 33 poin</p>
    </div>

  </div>

  <!-- Sub statistik -->
  <div class="grid md:grid-cols-4 gap-5">
    <div class="bg-red-50 rounded-xl p-4 border border-red-100 text-center">
      <p class="text-2xl font-bold text-red-500"><?= $dist_kurang ?></p>
      <p class="text-sm text-gray-600 mt-1">Kurang (≤ 15)</p>
    </div>
    <div class="bg-yellow-50 rounded-xl p-4 border border-yellow-100 text-center">
      <p class="text-2xl font-bold text-yellow-500"><?= $dist_cukup ?></p>
      <p class="text-sm text-gray-600 mt-1">Cukup (16–24)</p>
    </div>
    <div class="bg-blue-50 rounded-xl p-4 border border-blue-100 text-center">
      <p class="text-2xl font-bold text-blue-500"><?= $dist_baik ?></p>
      <p class="text-sm text-gray-600 mt-1">Baik (25–32)</p>
    </div>
    <div class="bg-green-50 rounded-xl p-4 border border-green-100 text-center">
      <p class="text-2xl font-bold text-green-500"><?= $dist_sangat ?></p>
      <p class="text-sm text-gray-600 mt-1">Sangat Baik (≥ 33)</p>
    </div>
  </div>
</section>

<!-- ===================== -->
<!-- GRAFIK                -->
<!-- ===================== -->
<section id="grafik" class="max-w-6xl mx-auto pb-10 px-6">
  <h2 class="text-2xl font-bold mb-2 text-gray-800">Grafik Analisis Survey</h2>
  <p class="text-gray-500 mb-6 text-sm">Visualisasi data survey kepuasan pasien.</p>

  <?php if ($total_resp == 0): ?>
  <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-8 text-center text-yellow-700">
    <p class="text-5xl mb-4">📋</p>
    <p class="font-semibold text-lg">Belum Ada Data Survey</p>
  </div>
  <?php else: ?>

  <div class="grid md:grid-cols-2 gap-6 mb-6">

    <div class="bg-white rounded-xl shadow p-6">
      <h3 class="font-bold text-gray-800 mb-1">📊 Rata-rata Skor per Aspek</h3>
      <p class="text-xs text-gray-500 mb-4">Skala 1–4 per aspek pelayanan</p>
      <canvas id="chartBar" height="220"></canvas>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
      <h3 class="font-bold text-gray-800 mb-1">🥧 Distribusi Kepuasan</h3>
      <p class="text-xs text-gray-500 mb-4">Berdasarkan total skor survey</p>
      <canvas id="chartDoughnut" height="220"></canvas>
    </div>

  </div>

  <div class="grid md:grid-cols-2 gap-6">

    <div class="bg-white rounded-xl shadow p-6">
      <h3 class="font-bold text-gray-800 mb-1">👥 Responden per Jenis Kelamin</h3>
      <p class="text-xs text-gray-500 mb-4">Komposisi demografis responden</p>
      <canvas id="chartGender" height="220"></canvas>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
      <h3 class="font-bold text-gray-800 mb-4">📋 Tabel Rata-rata per Aspek</h3>
      <table class="w-full text-sm">
        <thead>
          <tr class="bg-orange-50 text-orange-700">
            <th class="text-left p-2">Aspek Penilaian</th>
            <th class="p-2">Rata-rata</th>
            <th class="p-2">Kategori</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $aspek = ["Keramahan Petugas","Kecepatan Pelayanan","Fasilitas Kesehatan",
                    "Kebersihan Ruangan","Kenyamanan Ruang Tunggu","Informasi Layanan",
                    "Sikap Dokter","Prosedur Pelayanan","Kualitas Pelayanan","Kepuasan Keseluruhan"];
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
  <?php endif; ?>
</section>

<!-- ===================== -->
<!-- FILTER & LAPORAN      -->
<!-- ===================== -->
<section id="laporan" class="max-w-6xl mx-auto pb-16 px-6">
  <div class="flex flex-wrap justify-between items-center mb-4 gap-3">
    <div>
      <h2 class="text-2xl font-bold text-gray-800">📋 Laporan Data Survey</h2>
      <p class="text-gray-500 text-sm">Detail data responden survey kepuasan pasien</p>
    </div>
    <div class="flex gap-2 no-print">
      <a href="?export=csv<?= $filter_jk ? '&jk='.$filter_jk : '' ?><?= $filter_prov ? '&provinsi='.urlencode($filter_prov) : '' ?><?= $search ? '&search='.urlencode($search) : '' ?>"
         class="bg-green-600 text-white px-5 py-2 rounded-lg hover:bg-green-700 transition text-sm font-semibold shadow">
        ⬇️ Unduh CSV
      </a>
      <button onclick="window.print()"
              class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-semibold shadow">
        🖨️ Cetak Laporan
      </button>
    </div>
  </div>

  <!-- FILTER -->
  <form method="GET" class="bg-white rounded-xl shadow p-4 mb-4 flex flex-wrap gap-3 items-end no-print">
    <div>
      <label class="block text-xs text-gray-500 mb-1 font-medium">Cari Nama/Provinsi</label>
      <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
             placeholder="Ketik nama atau provinsi..."
             class="border p-2 rounded-lg text-sm w-56 focus:outline-none focus:ring-2 focus:ring-blue-400">
    </div>
    <div>
      <label class="block text-xs text-gray-500 mb-1 font-medium">Jenis Kelamin</label>
      <select name="jk" class="border p-2 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
        <option value="">Semua</option>
        <option value="L" <?= $filter_jk=='L'?'selected':'' ?>>Laki-laki</option>
        <option value="P" <?= $filter_jk=='P'?'selected':'' ?>>Perempuan</option>
      </select>
    </div>
    <div>
      <label class="block text-xs text-gray-500 mb-1 font-medium">Provinsi</label>
      <select name="provinsi" class="border p-2 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
        <option value="">Semua Provinsi</option>
        <?php while ($p = mysqli_fetch_assoc($list_prov)): ?>
        <option value="<?= htmlspecialchars($p['provinsi']) ?>"
          <?= $filter_prov === $p['provinsi'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($p['provinsi']) ?>
        </option>
        <?php endwhile; ?>
      </select>
    </div>
    <button type="submit"
            class="bg-blue-500 text-white px-5 py-2 rounded-lg hover:bg-blue-600 text-sm transition">
      🔍 Filter
    </button>
    <a href="dashboard_manager.php"
       class="bg-gray-200 text-gray-700 px-5 py-2 rounded-lg hover:bg-gray-300 text-sm transition">
      Reset
    </a>
  </form>

  <!-- Keterangan filter aktif -->
  <?php if ($filter_jk || $filter_prov || $search): ?>
  <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-2 text-sm text-blue-700 mb-4 flex items-center gap-2">
    <span>🔍 Filter aktif:</span>
    <?php if ($search): ?><span class="bg-blue-100 px-2 py-0.5 rounded">Cari: "<?= htmlspecialchars($search) ?>"</span><?php endif; ?>
    <?php if ($filter_jk): ?><span class="bg-blue-100 px-2 py-0.5 rounded">JK: <?= $filter_jk=='L'?'Laki-laki':'Perempuan' ?></span><?php endif; ?>
    <?php if ($filter_prov): ?><span class="bg-blue-100 px-2 py-0.5 rounded">Provinsi: <?= htmlspecialchars($filter_prov) ?></span><?php endif; ?>
    <span class="text-gray-500">— <?= $total_resp ?> data ditemukan</span>
  </div>
  <?php endif; ?>

  <!-- TABEL DATA SURVEY -->
  <div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="p-4 border-b border-gray-100 flex justify-between items-center">
      <span class="font-semibold text-gray-700 text-sm">
        Total: <strong><?= $total_resp ?></strong> data survey
      </span>
      <span class="text-xs text-gray-400">Data per <?= date('d M Y H:i') ?></span>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="bg-orange-50 text-orange-800 text-xs uppercase tracking-wide">
            <th class="p-3 text-left">No</th>
            <th class="p-3 text-left">Nama</th>
            <th class="p-3 text-left">Tgl Lahir</th>
            <th class="p-3">JK</th>
            <th class="p-3 text-left">Provinsi</th>
            <th class="p-3">No HP</th>
            <th class="p-3">Skor</th>
            <th class="p-3">Kategori</th>
            <th class="p-3 text-left no-print">Detail</th>
          </tr>
        </thead>
        <tbody>
          <?php if (mysqli_num_rows($data_survey) > 0):
                $no = 1;
                while ($d = mysqli_fetch_assoc($data_survey)):
                  $skor = $d['total_skor'];
                  $kat  = $skor >= 33 ? ['Sangat Baik','bg-green-100 text-green-700']
                        : ($skor >= 25 ? ['Baik','bg-blue-100 text-blue-700']
                        : ($skor >= 16 ? ['Cukup','bg-yellow-100 text-yellow-700']
                                       : ['Kurang','bg-red-100 text-red-700']));
                  $persen = round(($skor/40)*100);
          ?>
          <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
            <td class="p-3 text-gray-500"><?= $no++ ?></td>
            <td class="p-3 font-medium text-gray-800"><?= htmlspecialchars($d['nama']) ?></td>
            <td class="p-3 text-gray-600"><?= $d['tanggal_lahir'] ?></td>
            <td class="p-3 text-center">
              <span class="<?= $d['jenis_kelamin']=='L' ? 'text-blue-600' : 'text-pink-600' ?> font-semibold">
                <?= $d['jenis_kelamin'] == 'L' ? '♂ L' : '♀ P' ?>
              </span>
            </td>
            <td class="p-3 text-gray-600"><?= htmlspecialchars($d['provinsi']) ?></td>
            <td class="p-3 text-center text-gray-600"><?= htmlspecialchars($d['no_hp']) ?></td>
            <td class="p-3 text-center">
              <div class="flex flex-col items-center">
                <span class="font-bold text-gray-800"><?= $skor ?>/40</span>
                <div class="w-16 bg-gray-200 rounded-full h-1.5 mt-1">
                  <div class="<?= $skor>=33?'bg-green-500':($skor>=25?'bg-blue-500':($skor>=16?'bg-yellow-500':'bg-red-500')) ?> h-1.5 rounded-full"
                       style="width:<?= $persen ?>%"></div>
                </div>
              </div>
            </td>
            <td class="p-3 text-center">
              <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $kat[1] ?>">
                <?= $kat[0] ?>
              </span>
            </td>
            <td class="p-3 no-print">
              <button onclick="toggleDetail(<?= $d['id'] ?>)"
                      class="text-blue-600 hover:underline text-xs">
                Lihat Detail
              </button>
            </td>
          </tr>
          <!-- ROW DETAIL (expandable) -->
          <tr id="detail-<?= $d['id'] ?>" class="hidden bg-blue-50">
            <td colspan="9" class="p-4">
              <div class="grid md:grid-cols-2 gap-4">
                <div>
                  <p class="font-semibold text-sm text-gray-700 mb-2">Skor Per Aspek:</p>
                  <?php
                  $aspek_short = ["Keramahan","Kecepatan","Fasilitas","Kebersihan","Ruang Tunggu",
                                  "Info Layanan","Sikap Dokter","Prosedur","Kualitas","Kepuasan"];
                  for ($qi = 1; $qi <= 10; $qi++):
                    $qv = $d["q$qi"];
                    $qkat = $qv==4?'Sangat Baik':($qv==3?'Baik':($qv==2?'Cukup':'Kurang'));
                    $qcol = $qv==4?'text-green-600':($qv==3?'text-blue-600':($qv==2?'text-yellow-600':'text-red-600'));
                  ?>
                  <div class="flex justify-between text-xs py-1 border-b border-blue-100">
                    <span class="text-gray-600"><?= $qi ?>. <?= $aspek_short[$qi-1] ?></span>
                    <span class="font-semibold <?= $qcol ?>"><?= $qv ?>/4 – <?= $qkat ?></span>
                  </div>
                  <?php endfor; ?>
                </div>
                <div>
                  <p class="font-semibold text-sm text-gray-700 mb-2">Kritik & Saran:</p>
                  <div class="bg-white rounded-lg p-3 text-sm text-gray-600 italic border border-blue-100 min-h-16">
                    <?= $d['komentar'] ? htmlspecialchars($d['komentar']) : '<span class="text-gray-400">Tidak ada komentar</span>' ?>
                  </div>
                </div>
              </div>
            </td>
          </tr>
          <?php endwhile;
          else: ?>
          <tr>
            <td colspan="9" class="text-center py-12 text-gray-400">
              <p class="text-4xl mb-2">📭</p>
              <p class="font-semibold">Tidak ada data survey yang ditemukan</p>
            </td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- CATATAN LAPORAN -->
  <div class="mt-4 bg-gray-50 border border-gray-200 rounded-lg p-4 text-xs text-gray-500">
    <strong>Catatan:</strong> Laporan ini dihasilkan secara otomatis dari sistem Klinik Sehat Digital
    pada <?= date('d F Y, H:i:s') ?>. 
    Untuk mengunduh seluruh data dalam format spreadsheet, klik tombol "Unduh CSV".
  </div>
</section>

<!-- FOOTER -->
<footer class="text-center p-6 text-gray-500 bg-white border-t">
  © 2026 Klinik Sehat Digital – Dashboard Manager
</footer>

<!-- CHART SCRIPTS -->
<script>
<?php if ($total_resp > 0): ?>
const avgQ   = <?= json_encode($avg_q) ?>;
const labels = [
  "Keramahan","Kecepatan","Fasilitas","Kebersihan","Ruang Tunggu",
  "Info Layanan","Sikap Dokter","Prosedur","Kualitas","Kepuasan"
];

new Chart(document.getElementById('chartBar'), {
  type: 'bar',
  data: {
    labels,
    datasets: [{
      label: 'Rata-rata (maks 4)',
      data: avgQ,
      backgroundColor: [
        '#F97316','#EAB308','#10B981','#3B82F6','#8B5CF6',
        '#EC4899','#06B6D4','#84CC16','#F59E0B','#6366F1'
      ],
      borderRadius: 6
    }]
  },
  options: {
    responsive: true,
    scales: {
      y: { beginAtZero: true, max: 4, ticks: { stepSize: 1 }, grid: { color: '#F3F4F6' } },
      x: { ticks: { font: { size: 10 } } }
    },
    plugins: { legend: { display: false } }
  }
});

new Chart(document.getElementById('chartDoughnut'), {
  type: 'doughnut',
  data: {
    labels: ['Kurang (≤15)', 'Cukup (16-24)', 'Baik (25-32)', 'Sangat Baik (≥33)'],
    datasets: [{
      data: [<?= $dist_kurang ?>,<?= $dist_cukup ?>,<?= $dist_baik ?>,<?= $dist_sangat ?>],
      backgroundColor: ['#EF4444','#F59E0B','#3B82F6','#10B981'],
      borderWidth: 2
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { position: 'bottom', labels: { padding: 15, font: { size: 11 } } } },
    cutout: '60%'
  }
});

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
    plugins: { legend: { position: 'bottom', labels: { padding: 15 } } }
  }
});
<?php endif; ?>

function toggleDetail(id) {
  const row = document.getElementById('detail-' + id);
  row.classList.toggle('hidden');
}
</script>

</body>
</html>