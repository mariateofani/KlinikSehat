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

/* SEARCH */
$search = $_GET['search'] ?? "";
$roleFilter = $_GET['role'] ?? "";

/* DATA USER */
if ($search != "" || $roleFilter != "") {

    $query = "SELECT * FROM users WHERE 1=1";

    if ($search != "") {
        $query .= " AND (nama LIKE '%$search%' OR email LIKE '%$search%')";
    }

    if ($roleFilter != "") {
        $query .= " AND role='$roleFilter'";
    }

    $user = mysqli_query($koneksi, $query);

} else {
    $user = mysqli_query($koneksi, "SELECT * FROM users");
}

/* DATA SURVEY */
$data = mysqli_query($koneksi, "SELECT * FROM survey");
?>

<!doctype html>
<html>
<head>
<title>Dashboard Admin</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<!-- NAVBAR -->
<nav class="bg-white shadow-md">
  <div class="max-w-6xl mx-auto flex justify-between items-center p-4">
    <h1 class="font-bold text-blue-600 text-xl">Klinik Sehat</h1>

    <div class="flex items-center gap-4">
      <span class="text-gray-600">
        Halo, <b><?= $nama; ?></b>
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
        Halo Admin: <?= $nama; ?>
      </h1>

      <p class="text-gray-600 mb-6">
        Selamat datang di dashboard admin Klinik Sehat.
      </p>

      <div class="flex gap-3">
        <a href="#user" class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700">
          Kelola User
        </a>
        
        <a href="#kelola" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
          Kelola Data
        </a>

        <a href="#grafik" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700">
          Lihat Grafik
        </a>
      </div>
    </div>

    <img src="https://i.pinimg.com/736x/20/95/a7/2095a72f390f72074776019b7004d776.jpg"
         class="rounded-xl shadow-lg">
  </div>
</section>

<!-- ===================== -->
<!-- KELOLA USER -->
<!-- ===================== -->
<section id="user" class="max-w-6xl mx-auto mt-10">
<form method="GET" class="mb-4 flex gap-2">

  <input type="text" name="search" placeholder="Cari nama/email..."
    class="border p-2 rounded w-64" value="<?= $search ?>">

  <select name="role" class="border p-2 rounded">
    <option value="">Semua Role</option>
    <option value="admin" <?= $roleFilter=='admin'?'selected':'' ?>>Admin</option>
    <option value="user" <?= $roleFilter=='user'?'selected':'' ?>>User</option>
  </select>

  <button class="bg-blue-500 text-white px-4 py-2 rounded">
    Filter
  </button>

</form>

  <div class="bg-white p-6 rounded shadow">
    <h2 class="text-xl font-bold mb-4">Kelola User</h2>

    <table class="w-full border">
      <tr class="bg-gray-200">
        <th class="p-2">ID</th>
        <th class="p-2">Nama</th>
        <th class="p-2">Email</th>
        <th class="p-2">Role</th>
        <th class="p-2">Aksi</th>
      </tr>

      <?php while($u = mysqli_fetch_assoc($user)) { ?>
      <tr class="border-t">
        <td class="p-2"><?= $u['id_user'] ?? $u['id'] ?></td>
        <td class="p-2"><?= $u['nama'] ?></td>
        <td class="p-2"><?= $u['email'] ?></td>
        <td class="p-2"><?= $u['role'] ?></td>
        <td class="p-2">

          <a href="editusers.php?id=<?= $u['id_user'] ?? $u['id'] ?>" class="text-blue-500">
            Edit
          </a>

          <a href="process/prosesHapus.php?id=<?= $u['id_user'] ?? $u['id'] ?>"
             class="text-red-500 ml-2"
             onclick="return confirm('Yakin ingin hapus user ini?')">
            Hapus
          </a>

        </td>
      </tr>
      <?php } ?>
    </table>
  </div>
</section>

<!-- ===================== -->
<!-- KELOLA SURVEY -->
<!-- ===================== -->
<section id="kelola" class="max-w-6xl mx-auto mt-10">

  <div class="bg-white p-6 rounded shadow">
    <h2 class="text-xl font-bold mb-4">Data Survey Pasien</h2>

    <table class="w-full border">
      <tr class="bg-gray-200">
        <th class="p-2">Nama</th>
        <th class="p-2">Tanggal lahir</th>
        <th class="p-2">JK</th>
        <th class="p-2">Provinsi</th>
        <th class="p-2">HP</th>
        <th class="p-2">Total Skor</th>
      </tr>

    <?php if (mysqli_num_rows($data) > 0) { ?>
        <?php while($d = mysqli_fetch_assoc($data)) { ?>
        <tr class="border-t">
          <td class="p-2"><?= $d['nama'] ?></td>
          <td class="p-2"><?= $d['tanggal_lahir'] ?></td>
          <td class="p-2"><?= $d['jenis_kelamin'] ?></td>
          <td class="p-2"><?= $d['provinsi'] ?></td>
          <td class="p-2"><?= $d['no_hp'] ?></td>
          <td class="p-2"><?= $d['total_skor'] ?></td>
        </tr>
        <?php } ?>
    <?php } else { ?>
    <tr>
        <td colspan="6" class="text-center p-4 text-gray-500">
            Tidak ada data survey
        </td>
    </tr>
<?php } ?>
      
    </table>
  </div>
</section>

<!-- ===================== -->
<!-- GRAFIK -->
<!-- ===================== -->
<section id="grafik" class="max-w-6xl mx-auto mt-10 mb-10">

  <div class="bg-white p-6 rounded shadow">
    <h2 class="text-xl font-bold mb-4">Grafik Kepuasan</h2>

    <canvas id="myChart" class="mb-4"></canvas>

    <p class="text-gray-600">
      Grafik menampilkan rata-rata kepuasan dari hasil survey.
    </p>
  </div>
</section>

<?php
$result = mysqli_query($koneksi, "SELECT AVG(total_skor) as rata FROM survey");
$row = mysqli_fetch_assoc($result);
$rata = $row['rata'] ? round($row['rata'], 2) : 0;

// 🔥 RATA-RATA PER PERTANYAAN
$qAvg = mysqli_query($koneksi, "
    SELECT 
        AVG(q1) as q1, AVG(q2) as q2, AVG(q3) as q3, AVG(q4) as q4, AVG(q5) as q5,
        AVG(q6) as q6, AVG(q7) as q7, AVG(q8) as q8, AVG(q9) as q9, AVG(q10) as q10
    FROM survey
");
$q = mysqli_fetch_assoc($qAvg);

// 🔥 DISTRIBUSI KEPUASAN
$dist = mysqli_query($koneksi, "
    SELECT
        SUM(CASE WHEN total_skor <= 20 THEN 1 ELSE 0 END) as rendah,
        SUM(CASE WHEN total_skor > 20 AND total_skor <= 30 THEN 1 ELSE 0 END) as sedang,
        SUM(CASE WHEN total_skor > 30 THEN 1 ELSE 0 END) as tinggi
    FROM survey
");
$d = mysqli_fetch_assoc($dist);

// 🔥 TOTAL RESPONDEN
$total = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM survey");
$t = mysqli_fetch_assoc($total);
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const ctx = document.getElementById('myChart');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Kepuasan Pasien'],
        datasets: [{
            label: 'Rata-rata Skor',
            data: [<?= $rata ?>],
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true,
                max: 40
            }
        }
    }
});
</script>

</body>
</html>