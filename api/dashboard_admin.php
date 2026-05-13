<?php
include 'service/koneksi.php';

// 🔐 CEK LOGIN DARI COOKIE
if (!isset($_COOKIE['email'])) {
    header("Location: login.php");
    exit;
}

// 🔥 AMBIL DATA COOKIE
$nama = $_COOKIE['nama'] ?? 'Admin';
$role = $_COOKIE['role'] ?? 'user';

// 🔒 VALIDASI HARUS ADMIN
if ($role !== 'admin') {
    header("Location: login.php");
    exit;
}

// =====================
// ACTIVE TAB
// =====================
$tab = $_GET['tab'] ?? 'dashboard';

// =====================
// FILTER USER
// =====================
$search     = $_GET['search']     ?? '';
$roleFilter = $_GET['role_filter'] ?? '';

$whereUser = "WHERE 1=1";
if ($search)     $whereUser .= " AND (nama LIKE '%" . mysqli_real_escape_string($koneksi, $search) . "%' OR email LIKE '%" . mysqli_real_escape_string($koneksi, $search) . "%')";
if ($roleFilter) $whereUser .= " AND role='" . mysqli_real_escape_string($koneksi, $roleFilter) . "'";

$user_result = mysqli_query($koneksi, "SELECT * FROM users $whereUser ORDER BY id_user DESC");

// =====================
// STATISTIK RINGKASAN
// =====================
$total_user    = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM users"))['c'] ?? 0;
$total_admin   = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM users WHERE role='admin'"))['c'] ?? 0;
$total_pasien  = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM users WHERE role='user'"))['c'] ?? 0;
$total_manager = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM users WHERE role='manager'"))['c'] ?? 0;

$total_survey = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM survey"))['c'] ?? 0;
$stats_survey = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT AVG(total_skor) as rata, MAX(total_skor) as maks, MIN(total_skor) as mini FROM survey"));
$rata_skor    = $stats_survey['rata'] ? round($stats_survey['rata'], 1) : 0;
$persen_puas  = $rata_skor > 0 ? round(($rata_skor / 40) * 100, 1) : 0;

// Distribusi kepuasan
$dist_kurang = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM survey WHERE total_skor <= 15"))['c'] ?? 0;
$dist_cukup  = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM survey WHERE total_skor BETWEEN 16 AND 24"))['c'] ?? 0;
$dist_baik   = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM survey WHERE total_skor BETWEEN 25 AND 32"))['c'] ?? 0;
$dist_sangat = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM survey WHERE total_skor >= 33"))['c'] ?? 0;

// Rata-rata per aspek
$avg_res = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT AVG(q1)a1,AVG(q2)a2,AVG(q3)a3,AVG(q4)a4,AVG(q5)a5,
            AVG(q6)a6,AVG(q7)a7,AVG(q8)a8,AVG(q9)a9,AVG(q10)a10 FROM survey"));
$avg_q = [];
for ($i = 1; $i <= 10; $i++) $avg_q[] = $avg_res ? round($avg_res["a$i"], 2) : 0;

// Jenis kelamin
$jk_l = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM survey WHERE jenis_kelamin='L'"))['c'] ?? 0;
$jk_p = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as c FROM survey WHERE jenis_kelamin='P'"))['c'] ?? 0;

// Data survey (untuk tab kelola survey)
$survey_search = $_GET['survey_search'] ?? '';
$whereS = $survey_search ? "WHERE nama LIKE '%" . mysqli_real_escape_string($koneksi, $survey_search) . "%' OR provinsi LIKE '%" . mysqli_real_escape_string($koneksi, $survey_search) . "%'" : "";
$data_survey = mysqli_query($koneksi, "SELECT * FROM survey $whereS ORDER BY id DESC");

// Export CSV survey
if (($_GET['export'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="survey_kliniksehat_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');
    fputs($out, "\xEF\xBB\xBF");
    fputcsv($out, ['No','Nama','Tgl Lahir','JK','Provinsi','No HP','Q1','Q2','Q3','Q4','Q5','Q6','Q7','Q8','Q9','Q10','Total Skor','Komentar','Kategori']);
    $rows = mysqli_query($koneksi, "SELECT * FROM survey ORDER BY id DESC");
    $no = 1;
    while ($r = mysqli_fetch_assoc($rows)) {
        $kat = $r['total_skor']>=33?'Sangat Baik':($r['total_skor']>=25?'Baik':($r['total_skor']>=16?'Cukup':'Kurang'));
        fputcsv($out, [$no++,$r['nama'],$r['tanggal_lahir'],$r['jenis_kelamin'],$r['provinsi'],$r['no_hp'],
                       $r['q1'],$r['q2'],$r['q3'],$r['q4'],$r['q5'],$r['q6'],$r['q7'],$r['q8'],$r['q9'],$r['q10'],
                       $r['total_skor'],$r['komentar'],$kat]);
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
  <title>Dashboard Admin – Klinik Sehat</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    html { scroll-behavior: smooth; }
    .sidebar-link { transition: all 0.2s; }
    .sidebar-link:hover, .sidebar-link.active { background: #EFF6FF; color: #2563EB; border-left: 3px solid #2563EB; }
    .sidebar-link.active { font-weight: 600; }
    .card-stat { transition: transform 0.2s, box-shadow 0.2s; }
    .card-stat:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
    .tab-content { display: none; }
    .tab-content.active { display: block; }
    @media (max-width: 768px) {
      #sidebar { display: none; }
      #sidebar.open { display: flex; }
    }
  </style>
</head>

<body class="bg-gray-100 min-h-screen">

<!-- ===================== -->
<!-- TOP NAVBAR            -->
<!-- ===================== -->
<nav class="bg-white shadow-md fixed top-0 left-0 right-0 z-50">
  <div class="flex justify-between items-center px-6 py-3">

    <div class="flex items-center gap-3">
      <!-- Hamburger mobile -->
      <button onclick="document.getElementById('sidebar').classList.toggle('open')"
              class="md:hidden text-gray-500 hover:text-blue-600 text-2xl">☰</button>
      <div class="flex items-center gap-2">
        <span class="text-2xl">🏥</span>
        <h1 class="font-bold text-blue-600 text-xl">Klinik Sehat</h1>
        <span class="bg-red-100 text-red-600 text-xs font-bold px-2 py-0.5 rounded-full">Admin</span>
      </div>
    </div>

    <div class="flex items-center gap-3">
      <div class="flex items-center gap-2">
        <div class="bg-red-100 text-red-700 w-9 h-9 rounded-full flex items-center justify-center font-bold text-sm">
          <?= strtoupper(substr($nama, 0, 1)) ?>
        </div>
        <div class="hidden md:block text-right">
          <p class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($nama) ?></p>
          <p class="text-xs text-gray-400">Administrator</p>
        </div>
      </div>
      <a href="logout.php"
         class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 text-sm transition">
        Logout
      </a>
    </div>

  </div>
</nav>

<!-- ===================== -->
<!-- LAYOUT WRAPPER        -->
<!-- ===================== -->
<div class="flex pt-16 min-h-screen">

  <!-- ===================== -->
  <!-- SIDEBAR               -->
  <!-- ===================== -->
  <aside id="sidebar"
         class="w-60 bg-white shadow-lg flex-col fixed left-0 top-16 bottom-0 z-40 overflow-y-auto hidden md:flex">

    <div class="p-4 border-b border-gray-100">
      <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Menu Utama</p>
    </div>

    <nav class="flex-1 py-2">

      <button onclick="switchTab('dashboard')"
              id="nav-dashboard"
              class="sidebar-link active w-full flex items-center gap-3 px-5 py-3 text-sm text-gray-600 border-l-3 border-transparent">
        <span class="text-lg">📊</span> Dashboard
      </button>

      <button onclick="switchTab('user')"
              id="nav-user"
              class="sidebar-link w-full flex items-center gap-3 px-5 py-3 text-sm text-gray-600 border-l-3 border-transparent">
        <span class="text-lg">👥</span> Kelola User
      </button>

      <button onclick="switchTab('survey')"
              id="nav-survey"
              class="sidebar-link w-full flex items-center gap-3 px-5 py-3 text-sm text-gray-600 border-l-3 border-transparent">
        <span class="text-lg">📋</span> Data Survey
      </button>

      <button onclick="switchTab('grafik')"
              id="nav-grafik"
              class="sidebar-link w-full flex items-center gap-3 px-5 py-3 text-sm text-gray-600 border-l-3 border-transparent">
        <span class="text-lg">📈</span> Grafik & Analisis
      </button>

    </nav>

    <div class="p-4 border-t border-gray-100">
      <div class="bg-blue-50 rounded-lg p-3">
        <p class="text-xs text-blue-600 font-semibold mb-1">📅 Hari ini</p>
        <p class="text-xs text-gray-500"><?= date('d F Y') ?></p>
        <p class="text-xs text-gray-500"><?= date('H:i') ?> WIB</p>
      </div>
    </div>

  </aside>

  <!-- ===================== -->
  <!-- MAIN CONTENT          -->
  <!-- ===================== -->
  <main class="flex-1 md:ml-60 p-6">

    <!-- ================== -->
    <!-- TAB: DASHBOARD     -->
    <!-- ================== -->
    <div id="tab-dashboard" class="tab-content active">

      <!-- Header -->
      <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Selamat Datang, <?= htmlspecialchars($nama) ?>! 👋</h2>
        <p class="text-gray-500 text-sm mt-1">Berikut ringkasan data sistem Klinik Sehat hari ini.</p>
      </div>

      <!-- STAT CARDS – USER -->
      <div class="mb-3">
        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-3">📊 Statistik Pengguna</p>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

          <div class="card-stat bg-white rounded-xl shadow p-5 border-l-4 border-blue-500">
            <p class="text-3xl font-bold text-blue-600"><?= $total_user ?></p>
            <p class="text-gray-600 text-sm mt-1 font-medium">Total User</p>
            <p class="text-xs text-gray-400 mt-0.5">Semua akun terdaftar</p>
          </div>

          <div class="card-stat bg-white rounded-xl shadow p-5 border-l-4 border-red-500">
            <p class="text-3xl font-bold text-red-600"><?= $total_admin ?></p>
            <p class="text-gray-600 text-sm mt-1 font-medium">Admin</p>
            <p class="text-xs text-gray-400 mt-0.5">Akun administrator</p>
          </div>

          <div class="card-stat bg-white rounded-xl shadow p-5 border-l-4 border-green-500">
            <p class="text-3xl font-bold text-green-600"><?= $total_pasien ?></p>
            <p class="text-gray-600 text-sm mt-1 font-medium">Pasien</p>
            <p class="text-xs text-gray-400 mt-0.5">Akun pengguna biasa</p>
          </div>

          <div class="card-stat bg-white rounded-xl shadow p-5 border-l-4 border-orange-500">
            <p class="text-3xl font-bold text-orange-600"><?= $total_manager ?></p>
            <p class="text-gray-600 text-sm mt-1 font-medium">Manager</p>
            <p class="text-xs text-gray-400 mt-0.5">Akun manajer</p>
          </div>

        </div>
      </div>

      <!-- STAT CARDS – SURVEY -->
      <div class="mb-6">
        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-3 mt-5">📋 Statistik Survey</p>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

          <div class="card-stat bg-white rounded-xl shadow p-5 border-l-4 border-purple-500">
            <p class="text-3xl font-bold text-purple-600"><?= $total_survey ?></p>
            <p class="text-gray-600 text-sm mt-1 font-medium">Total Responden</p>
            <p class="text-xs text-gray-400 mt-0.5">Survey yang masuk</p>
          </div>

          <div class="card-stat bg-white rounded-xl shadow p-5 border-l-4 border-teal-500">
            <p class="text-3xl font-bold text-teal-600"><?= $rata_skor ?><span class="text-base text-gray-400">/40</span></p>
            <p class="text-gray-600 text-sm mt-1 font-medium">Rata-rata Skor</p>
            <p class="text-xs text-gray-400 mt-0.5">Skor total kepuasan</p>
          </div>

          <div class="card-stat bg-white rounded-xl shadow p-5 border-l-4 border-indigo-500">
            <p class="text-3xl font-bold text-indigo-600"><?= $persen_puas ?>%</p>
            <p class="text-gray-600 text-sm mt-1 font-medium">Tingkat Kepuasan</p>
            <p class="text-xs text-gray-400 mt-0.5">Dari skor maksimal</p>
          </div>

          <div class="card-stat bg-white rounded-xl shadow p-5 border-l-4 border-green-400">
            <p class="text-3xl font-bold text-green-500"><?= $dist_sangat ?></p>
            <p class="text-gray-600 text-sm mt-1 font-medium">Sangat Puas</p>
            <p class="text-xs text-gray-400 mt-0.5">Skor ≥ 33 poin</p>
          </div>

        </div>
      </div>

      <!-- PROGRESS KEPUASAN -->
      <div class="bg-white rounded-xl shadow p-6 mb-6">
        <h3 class="font-bold text-gray-800 mb-4">Indikator Kepuasan Keseluruhan</h3>
        <div class="flex items-center gap-4 mb-3">
          <span class="text-sm text-gray-600 w-32">Tingkat Kepuasan</span>
          <div class="flex-1 bg-gray-200 rounded-full h-4 overflow-hidden">
            <div class="h-4 rounded-full transition-all duration-700
              <?= $persen_puas >= 75 ? 'bg-green-500' : ($persen_puas >= 50 ? 'bg-yellow-400' : 'bg-red-400') ?>"
              style="width: <?= $persen_puas ?>%"></div>
          </div>
          <span class="text-sm font-bold text-gray-700 w-12"><?= $persen_puas ?>%</span>
        </div>
        <div class="grid grid-cols-4 gap-3 mt-4">
          <div class="bg-red-50 rounded-lg p-3 text-center border border-red-100">
            <p class="text-xl font-bold text-red-500"><?= $dist_kurang ?></p>
            <p class="text-xs text-gray-500">Kurang (≤15)</p>
          </div>
          <div class="bg-yellow-50 rounded-lg p-3 text-center border border-yellow-100">
            <p class="text-xl font-bold text-yellow-500"><?= $dist_cukup ?></p>
            <p class="text-xs text-gray-500">Cukup (16–24)</p>
          </div>
          <div class="bg-blue-50 rounded-lg p-3 text-center border border-blue-100">
            <p class="text-xl font-bold text-blue-500"><?= $dist_baik ?></p>
            <p class="text-xs text-gray-500">Baik (25–32)</p>
          </div>
          <div class="bg-green-50 rounded-lg p-3 text-center border border-green-100">
            <p class="text-xl font-bold text-green-500"><?= $dist_sangat ?></p>
            <p class="text-xs text-gray-500">Sangat Baik (≥33)</p>
          </div>
        </div>
      </div>

      <!-- SHORTCUT AKSI -->
      <div class="bg-white rounded-xl shadow p-6">
        <h3 class="font-bold text-gray-800 mb-4">Akses Cepat</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
          <button onclick="switchTab('user')"
                  class="flex flex-col items-center gap-2 bg-blue-50 hover:bg-blue-100 rounded-xl p-4 transition">
            <span class="text-3xl">👥</span>
            <span class="text-sm font-medium text-blue-700">Kelola User</span>
          </button>
          <button onclick="switchTab('survey')"
                  class="flex flex-col items-center gap-2 bg-green-50 hover:bg-green-100 rounded-xl p-4 transition">
            <span class="text-3xl">📋</span>
            <span class="text-sm font-medium text-green-700">Data Survey</span>
          </button>
          <button onclick="switchTab('grafik')"
                  class="flex flex-col items-center gap-2 bg-purple-50 hover:bg-purple-100 rounded-xl p-4 transition">
            <span class="text-3xl">📈</span>
            <span class="text-sm font-medium text-purple-700">Grafik</span>
          </button>
          <a href="?export=csv"
             class="flex flex-col items-center gap-2 bg-orange-50 hover:bg-orange-100 rounded-xl p-4 transition">
            <span class="text-3xl">⬇️</span>
            <span class="text-sm font-medium text-orange-700">Unduh CSV</span>
          </a>
        </div>
      </div>

    </div><!-- /tab-dashboard -->


    <!-- ================== -->
    <!-- TAB: KELOLA USER   -->
    <!-- ================== -->
    <div id="tab-user" class="tab-content">

      <div class="flex justify-between items-center mb-6">
        <div>
          <h2 class="text-2xl font-bold text-gray-800">👥 Kelola User</h2>
          <p class="text-gray-500 text-sm mt-1">Manajemen akun pengguna sistem Klinik Sehat</p>
        </div>
      </div>

      <!-- Filter -->
      <form method="GET" class="bg-white rounded-xl shadow p-4 mb-4 flex flex-wrap gap-3 items-end">
        <input type="hidden" name="tab" value="user">
        <div>
          <label class="block text-xs text-gray-500 font-medium mb-1">Cari Nama / Email</label>
          <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                 placeholder="Ketik nama atau email..."
                 class="border p-2 rounded-lg text-sm w-56 focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>
        <div>
          <label class="block text-xs text-gray-500 font-medium mb-1">Filter Role</label>
          <select name="role_filter" class="border p-2 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            <option value="">Semua Role</option>
            <option value="admin"   <?= $roleFilter=='admin'   ?'selected':'' ?>>Admin</option>
            <option value="user"    <?= $roleFilter=='user'    ?'selected':'' ?>>User / Pasien</option>
            <option value="manager" <?= $roleFilter=='manager' ?'selected':'' ?>>Manager</option>
          </select>
        </div>
        <button type="submit"
                class="bg-blue-500 text-white px-5 py-2 rounded-lg hover:bg-blue-600 text-sm transition">
          🔍 Filter
        </button>
        <a href="?tab=user"
           class="bg-gray-200 text-gray-700 px-5 py-2 rounded-lg hover:bg-gray-300 text-sm transition">
          Reset
        </a>
      </form>

      <!-- Tabel User -->
      <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex justify-between items-center">
          <span class="text-sm font-semibold text-gray-700">
            Ditemukan: <strong><?= mysqli_num_rows($user_result) ?></strong> akun
          </span>
          <span class="text-xs text-gray-400">Total seluruh: <?= $total_user ?> akun</span>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="bg-blue-50 text-blue-800 text-xs uppercase tracking-wide">
                <th class="p-3 text-left">No</th>
                <th class="p-3 text-left">Nama</th>
                <th class="p-3 text-left">Email</th>
                <th class="p-3 text-center">Role</th>
                <th class="p-3 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (mysqli_num_rows($user_result) > 0):
                    $no = 1;
                    while ($u = mysqli_fetch_assoc($user_result)):
                      $uid = $u['id_user'] ?? $u['id'];
                      $roleBadge = match($u['role']) {
                        'admin'   => 'bg-red-100 text-red-700',
                        'manager' => 'bg-orange-100 text-orange-700',
                        default   => 'bg-green-100 text-green-700',
                      };
                      $roleLabel = match($u['role']) {
                        'admin'   => '🔴 Admin',
                        'manager' => '🟠 Manager',
                        default   => '🟢 Pasien',
                      };
              ?>
              <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                <td class="p-3 text-gray-400 text-xs"><?= $no++ ?></td>
                <td class="p-3">
                  <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold flex-shrink-0">
                      <?= strtoupper(substr($u['nama'], 0, 1)) ?>
                    </div>
                    <span class="font-medium text-gray-800"><?= htmlspecialchars($u['nama']) ?></span>
                  </div>
                </td>
                <td class="p-3 text-gray-500"><?= htmlspecialchars($u['email']) ?></td>
                <td class="p-3 text-center">
                  <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $roleBadge ?>">
                    <?= $roleLabel ?>
                  </span>
                </td>
                <td class="p-3 text-center">
                  <div class="flex justify-center gap-2">
                    <a href="editusers.php?id=<?= $uid ?>"
                       class="bg-blue-50 text-blue-600 hover:bg-blue-100 px-3 py-1.5 rounded-lg text-xs font-medium transition">
                      ✏️ Edit
                    </a>
                    <a href="process/prosesHapus.php?id=<?= $uid ?>"
                       onclick="return confirm('Yakin ingin menghapus akun <?= htmlspecialchars(addslashes($u['nama'])) ?>?')"
                       class="bg-red-50 text-red-600 hover:bg-red-100 px-3 py-1.5 rounded-lg text-xs font-medium transition">
                      🗑️ Hapus
                    </a>
                  </div>
                </td>
              </tr>
              <?php endwhile;
              else: ?>
              <tr>
                <td colspan="5" class="text-center py-12 text-gray-400">
                  <p class="text-4xl mb-2">👤</p>
                  <p class="font-semibold">Tidak ada user yang ditemukan</p>
                </td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div><!-- /tab-user -->


    <!-- ================== -->
    <!-- TAB: DATA SURVEY   -->
    <!-- ================== -->
    <div id="tab-survey" class="tab-content">

      <div class="flex flex-wrap justify-between items-center mb-6 gap-3">
        <div>
          <h2 class="text-2xl font-bold text-gray-800">📋 Data Survey Pasien</h2>
          <p class="text-gray-500 text-sm mt-1">Seluruh hasil survey kepuasan pasien Klinik Sehat</p>
        </div>
        <a href="?tab=survey&export=csv"
           class="bg-green-600 text-white px-5 py-2 rounded-lg hover:bg-green-700 text-sm font-semibold shadow transition">
          ⬇️ Unduh CSV
        </a>
      </div>

      <!-- Cari Survey -->
      <form method="GET" class="bg-white rounded-xl shadow p-4 mb-4 flex flex-wrap gap-3 items-end">
        <input type="hidden" name="tab" value="survey">
        <div>
          <label class="block text-xs text-gray-500 font-medium mb-1">Cari Nama / Provinsi</label>
          <input type="text" name="survey_search" value="<?= htmlspecialchars($survey_search) ?>"
                 placeholder="Ketik nama atau provinsi..."
                 class="border p-2 rounded-lg text-sm w-64 focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>
        <button type="submit"
                class="bg-blue-500 text-white px-5 py-2 rounded-lg hover:bg-blue-600 text-sm transition">
          🔍 Cari
        </button>
        <a href="?tab=survey"
           class="bg-gray-200 text-gray-700 px-5 py-2 rounded-lg hover:bg-gray-300 text-sm transition">
          Reset
        </a>
      </form>

      <!-- Tabel Survey -->
      <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex justify-between items-center">
          <span class="text-sm font-semibold text-gray-700">
            Total: <strong><?= mysqli_num_rows($data_survey) ?></strong> data survey
          </span>
          <span class="text-xs text-gray-400">Klik "Detail" untuk lihat skor per aspek</span>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="bg-purple-50 text-purple-800 text-xs uppercase tracking-wide">
                <th class="p-3 text-left">No</th>
                <th class="p-3 text-left">Nama</th>
                <th class="p-3 text-left">Tgl Lahir</th>
                <th class="p-3 text-center">JK</th>
                <th class="p-3 text-left">Provinsi</th>
                <th class="p-3 text-center">No HP</th>
                <th class="p-3 text-center">Skor</th>
                <th class="p-3 text-center">Kategori</th>
                <th class="p-3 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (mysqli_num_rows($data_survey) > 0):
                    $no = 1;
                    while ($d = mysqli_fetch_assoc($data_survey)):
                      $skor = $d['total_skor'];
                      $pct  = round(($skor/40)*100);
                      $kat  = $skor>=33 ? ['Sangat Baik','bg-green-100 text-green-700']
                            : ($skor>=25 ? ['Baik','bg-blue-100 text-blue-700']
                            : ($skor>=16 ? ['Cukup','bg-yellow-100 text-yellow-700']
                                         : ['Kurang','bg-red-100 text-red-700']));
                      $barColor = $skor>=33?'bg-green-500':($skor>=25?'bg-blue-500':($skor>=16?'bg-yellow-400':'bg-red-400'));
              ?>
              <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                <td class="p-3 text-gray-400 text-xs"><?= $no++ ?></td>
                <td class="p-3 font-medium text-gray-800"><?= htmlspecialchars($d['nama']) ?></td>
                <td class="p-3 text-gray-500 text-xs"><?= $d['tanggal_lahir'] ?></td>
                <td class="p-3 text-center">
                  <span class="<?= $d['jenis_kelamin']=='L'?'text-blue-600':'text-pink-600' ?> font-bold text-xs">
                    <?= $d['jenis_kelamin']=='L' ? '♂ L' : '♀ P' ?>
                  </span>
                </td>
                <td class="p-3 text-gray-600 text-xs"><?= htmlspecialchars($d['provinsi']) ?></td>
                <td class="p-3 text-center text-gray-500 text-xs"><?= htmlspecialchars($d['no_hp']) ?></td>
                <td class="p-3 text-center">
                  <div class="flex flex-col items-center gap-1">
                    <span class="font-bold text-gray-800"><?= $skor ?><span class="text-gray-400 text-xs">/40</span></span>
                    <div class="w-16 bg-gray-200 rounded-full h-1.5">
                      <div class="<?= $barColor ?> h-1.5 rounded-full" style="width:<?= $pct ?>%"></div>
                    </div>
                  </div>
                </td>
                <td class="p-3 text-center">
                  <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $kat[1] ?>"><?= $kat[0] ?></span>
                </td>
                <td class="p-3 text-center">
                  <button onclick="toggleSurveyDetail(<?= $d['id'] ?>)"
                          class="bg-purple-50 text-purple-600 hover:bg-purple-100 px-3 py-1.5 rounded-lg text-xs font-medium transition">
                    🔍 Detail
                  </button>
                </td>
              </tr>
              <!-- DETAIL ROW -->
              <tr id="sdetail-<?= $d['id'] ?>" class="hidden bg-purple-50">
                <td colspan="9" class="p-4">
                  <div class="grid md:grid-cols-2 gap-4">
                    <div>
                      <p class="font-semibold text-sm text-gray-700 mb-2">Skor Per Aspek:</p>
                      <?php
                      $aspek = ["Keramahan Petugas","Kecepatan Pelayanan","Fasilitas Kesehatan",
                                "Kebersihan Ruangan","Kenyamanan Ruang Tunggu","Informasi Layanan",
                                "Sikap Dokter","Prosedur Pelayanan","Kualitas Pelayanan","Kepuasan Keseluruhan"];
                      for ($qi = 1; $qi <= 10; $qi++):
                        $qv  = $d["q$qi"];
                        $qkl = $qv==4?'text-green-600':($qv==3?'text-blue-600':($qv==2?'text-yellow-600':'text-red-600'));
                        $qkt = $qv==4?'Sangat Baik':($qv==3?'Baik':($qv==2?'Cukup':'Kurang'));
                      ?>
                      <div class="flex justify-between items-center text-xs py-1.5 border-b border-purple-100">
                        <span class="text-gray-600"><?= $qi ?>. <?= $aspek[$qi-1] ?></span>
                        <span class="font-semibold <?= $qkl ?>"><?= $qv ?>/4 – <?= $qkt ?></span>
                      </div>
                      <?php endfor; ?>
                    </div>
                    <div>
                      <p class="font-semibold text-sm text-gray-700 mb-2">Kritik & Saran:</p>
                      <div class="bg-white rounded-lg p-3 text-sm text-gray-600 italic border border-purple-100 min-h-20">
                        <?= $d['komentar'] ? htmlspecialchars($d['komentar']) : '<span class="text-gray-400 not-italic">Tidak ada komentar</span>' ?>
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
                  <p class="font-semibold">Tidak ada data survey ditemukan</p>
                </td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div><!-- /tab-survey -->


    <!-- ================== -->
    <!-- TAB: GRAFIK        -->
    <!-- ================== -->
    <div id="tab-grafik" class="tab-content">

      <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">📈 Grafik & Analisis Survey</h2>
        <p class="text-gray-500 text-sm mt-1">
          Visualisasi data survey kepuasan pasien secara lengkap.
          <?php if ($total_survey > 0): ?>
            Total <strong><?= $total_survey ?> responden</strong> telah mengisi survey.
          <?php endif; ?>
        </p>
      </div>

      <?php if ($total_survey == 0): ?>
      <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-10 text-center text-yellow-700">
        <p class="text-5xl mb-4">📋</p>
        <p class="font-semibold text-lg">Belum Ada Data Survey</p>
        <p class="text-sm mt-1">Grafik akan tampil setelah ada pasien yang mengisi survey.</p>
      </div>
      <?php else: ?>

      <!-- Baris 1: Bar + Doughnut -->
      <div class="grid md:grid-cols-2 gap-6 mb-6">

        <div class="bg-white rounded-xl shadow p-6">
          <h3 class="font-bold text-gray-800 mb-1">📊 Rata-rata Skor Per Aspek</h3>
          <p class="text-xs text-gray-500 mb-4">Skala 1 (Kurang) – 4 (Sangat Baik) per aspek pelayanan</p>
          <canvas id="chartBar" height="230"></canvas>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
          <h3 class="font-bold text-gray-800 mb-1">🥧 Distribusi Tingkat Kepuasan</h3>
          <p class="text-xs text-gray-500 mb-4">Pengelompokan berdasarkan total skor</p>
          <canvas id="chartDoughnut" height="230"></canvas>
        </div>

      </div>

      <!-- Baris 2: Pie + Tabel Aspek -->
      <div class="grid md:grid-cols-2 gap-6 mb-6">

        <div class="bg-white rounded-xl shadow p-6">
          <h3 class="font-bold text-gray-800 mb-1">👥 Responden per Jenis Kelamin</h3>
          <p class="text-xs text-gray-500 mb-4">Komposisi demografis responden survey</p>
          <canvas id="chartGender" height="230"></canvas>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
          <h3 class="font-bold text-gray-800 mb-4">📋 Rekap Aspek Penilaian</h3>
          <div class="overflow-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase">
                  <th class="text-left p-2">Aspek</th>
                  <th class="p-2 text-center">Rata-rata</th>
                  <th class="p-2 text-center">Status</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $aspek = ["Keramahan Petugas","Kecepatan Pelayanan","Fasilitas Kesehatan",
                          "Kebersihan Ruangan","Kenyamanan Ruang Tunggu","Informasi Layanan",
                          "Sikap Dokter","Prosedur Pelayanan","Kualitas Pelayanan","Kepuasan Keseluruhan"];
                for ($i = 0; $i < 10; $i++):
                  $v   = $avg_q[$i];
                  $kat = $v>=3.5 ? ['Sangat Baik','bg-green-100 text-green-700']
                       : ($v>=2.5 ? ['Baik','bg-blue-100 text-blue-700']
                       : ($v>=1.5 ? ['Cukup','bg-yellow-100 text-yellow-700']
                                  : ['Kurang','bg-red-100 text-red-700']));
                ?>
                <tr class="border-b border-gray-50 hover:bg-gray-50">
                  <td class="p-2 text-gray-700 text-xs"><?= $aspek[$i] ?></td>
                  <td class="p-2 text-center font-bold"><?= $v ?></td>
                  <td class="p-2 text-center">
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold <?= $kat[1] ?>"><?= $kat[0] ?></span>
                  </td>
                </tr>
                <?php endfor; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>

      <!-- Baris 3: Line Chart Simulasi -->
      <div class="bg-white rounded-xl shadow p-6">
        <h3 class="font-bold text-gray-800 mb-1">📉 Perbandingan Aspek Pelayanan</h3>
        <p class="text-xs text-gray-500 mb-4">Perbandingan skor rata-rata semua aspek dalam satu grafik</p>
        <canvas id="chartLine" height="110"></canvas>
      </div>

      <?php endif; ?>

    </div><!-- /tab-grafik -->

  </main>
</div><!-- /layout wrapper -->

<!-- FOOTER -->
<footer class="text-center py-4 text-gray-400 text-sm bg-white border-t md:ml-60">
  © 2026 Klinik Sehat Digital – Dashboard Admin
</footer>

<!-- ===================== -->
<!-- SCRIPTS               -->
<!-- ===================== -->
<script>
// =====================
// TAB SWITCHER
// =====================
function switchTab(name) {
  // Sembunyikan semua tab
  document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
  // Hapus active dari semua nav
  document.querySelectorAll('.sidebar-link').forEach(el => el.classList.remove('active'));
  // Tampilkan tab aktif
  document.getElementById('tab-' + name).classList.add('active');
  document.getElementById('nav-' + name).classList.add('active');
  // Render chart kalau masuk tab grafik
  if (name === 'grafik') renderCharts();
}

// =====================
// TOGGLE DETAIL SURVEY
// =====================
function toggleSurveyDetail(id) {
  const row = document.getElementById('sdetail-' + id);
  row.classList.toggle('hidden');
}

// =====================
// CHART.JS
// =====================
let chartsRendered = false;

function renderCharts() {
  if (chartsRendered) return;
  chartsRendered = true;

  <?php if ($total_survey > 0): ?>
  const avgQ = <?= json_encode($avg_q) ?>;
  const labels = ["Keramahan","Kecepatan","Fasilitas","Kebersihan",
                  "Ruang Tunggu","Info Layanan","Sikap Dokter","Prosedur","Kualitas","Kepuasan"];
  const colors = ['#3B82F6','#10B981','#8B5CF6','#F59E0B','#EF4444',
                  '#06B6D4','#84CC16','#F97316','#EC4899','#6366F1'];

  // BAR CHART
  new Chart(document.getElementById('chartBar'), {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Rata-rata (maks 4)',
        data: avgQ,
        backgroundColor: colors,
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

  // DOUGHNUT
  new Chart(document.getElementById('chartDoughnut'), {
    type: 'doughnut',
    data: {
      labels: ['Kurang (≤15)','Cukup (16-24)','Baik (25-32)','Sangat Baik (≥33)'],
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

  // PIE – GENDER
  new Chart(document.getElementById('chartGender'), {
    type: 'pie',
    data: {
      labels: ['Laki-laki','Perempuan'],
      datasets: [{
        data: [<?= $jk_l ?>,<?= $jk_p ?>],
        backgroundColor: ['#3B82F6','#EC4899'],
        borderWidth: 2
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { position: 'bottom', labels: { padding: 15 } } }
    }
  });

  // LINE – Perbandingan aspek
  new Chart(document.getElementById('chartLine'), {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'Rata-rata Skor per Aspek',
        data: avgQ,
        borderColor: '#3B82F6',
        backgroundColor: 'rgba(59,130,246,0.08)',
        pointBackgroundColor: colors,
        pointRadius: 6,
        tension: 0.4,
        fill: true
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: { beginAtZero: true, max: 4, ticks: { stepSize: 1 }, grid: { color: '#F3F4F6' } },
        x: { ticks: { font: { size: 10 } } }
      },
      plugins: { legend: { labels: { font: { size: 12 } } } }
    }
  });
  <?php endif; ?>
}

// Kalau tab default adalah grafik, render langsung
if (document.getElementById('tab-grafik').classList.contains('active')) {
  renderCharts();
}
</script>

</body>
</html>