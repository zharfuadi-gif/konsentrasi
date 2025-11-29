<?php
session_start();

// ===== CEK LOGIN =====
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'dosen') {
  header("Location: index.php");
  exit;
}

// ===== KONEKSI DATABASE =====
$servername = "localhost";
$username   = "root";
$password   = "";
$database   = "absensiqr_231051";

$conn = mysqli_connect($servername, $username, $password, $database);
if (!$conn) {
  die("Koneksi gagal: " . mysqli_connect_error());
}

// ===== AMBIL DATA DOSEN =====
$email = $_SESSION['email'];

// ===== CEK KETERSEDIAAN KOLOM NAMA =====
$cekKolom = mysqli_query($conn, "SHOW COLUMNS FROM user_231051 LIKE 'nama_231051'");
if ($cekKolom && mysqli_num_rows($cekKolom) > 0) {
    $queryNama = mysqli_query($conn, "SELECT nama_231051 FROM user_231051 WHERE email_231051 = '$email'");
    if ($queryNama && mysqli_num_rows($queryNama) > 0) {
        $dataNama = mysqli_fetch_assoc($queryNama);
        $namaDosen = $dataNama['nama_231051'];
    } else {
        $namaDosen = $email;
    }
} else {
    $namaDosen = $email;
}

// ===== FUNGSI TABEL =====
function render_table_from_result($result)
{
  global $conn;

  if (!$result) {
    echo "<p style='color:red;'>Error query: " . htmlspecialchars(mysqli_error($conn)) . "</p>";
    return;
  }

  if (mysqli_num_rows($result) == 0) {
    echo "<p>Tidak ada data.</p>";
    return;
  }

  $fields = mysqli_fetch_fields($result);

  echo "<table><thead><tr>";
  foreach ($fields as $f) echo "<th>" . htmlspecialchars($f->name) . "</th>";
  echo "</tr></thead><tbody>";

  while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    foreach ($fields as $f) {
      $col = $f->name;
      echo "<td>" . htmlspecialchars($row[$col] ?? '') . "</td>";
    }
    echo "</tr>";
  }

  echo "</tbody></table>";
}

// ============ QR CODE HANDLER ================
$qr = "";
$activeTab = "dashboard";

if (isset($_POST['generate'])) {
    $activeTab = "qrcode";

    $id_kelas = $_POST['kelas'];
    $id_mk    = $_POST['mk'];
    $id_jam   = $_POST['jam'];

    $qr_value = "$id_mk|$id_kelas|$id_jam|" . date("Y-m-d");

    $qr = "https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=" . urlencode($qr_value);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Dashboard Dosen</title>

  <style>
    body { font-family: Poppins, Arial; margin: 0; display: flex; background: #f5f7fb; }
    .sidebar { width: 220px; background: #1a237e; color: #fff; height: 100vh; padding: 20px; position: fixed; }
    .sidebar button { display: block; width: 100%; padding: 10px; border: none; background: none; color: #fff;
      text-align: left; cursor: pointer; border-radius: 6px; margin-bottom: 10px; }
    .sidebar button:hover, .sidebar button.active { background: #3949ab; }
    .main { margin-left: 220px; padding: 25px; width: calc(100% - 220px); }
    .content { display: none; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
    .content.active { display: block; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    th, td { border: 1px solid #e9edf3; padding: 10px; }
    th { background: #1a237e; color: #fff; }
    .qr-box { text-align: center; margin-top: 30px; }
    select, button.qr-btn {
      padding: 10px; border-radius: 8px; border: 1px solid #aaa; margin: 8px 0; width: 250px;
    }
    button.qr-btn { background: #1a237e; color: white; cursor: pointer; }
  </style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
  <h2>Dosen Panel</h2>
  <button class="tablink <?= $activeTab=='dashboard'?'active':'' ?>" onclick="openPage('dashboard', this)">Dashboard</button>
  <button class="tablink <?= $activeTab=='absensi'?'active':'' ?>" onclick="openPage('absensi', this)">Absensi</button>
  <button class="tablink <?= $activeTab=='qrcode'?'active':'' ?>" onclick="openPage('qrcode', this)">QR Code</button>
  <button onclick="window.location.href='logout.php'">Logout</button>
</div>

<!-- MAIN -->
<div class="main">

  <!-- DASHBOARD -->
  <div id="dashboard" class="content <?= $activeTab=='dashboard'?'active':'' ?>">
    <h1>Selamat Datang, <?= htmlspecialchars($namaDosen) ?>!</h1>
  </div>

  <!-- ABSENSI -->
<div id="absensi" class="content <?= $activeTab=='absensi'?'active':'' ?>">
  <div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
    <h1>Riwayat Absensi</h1>

    <!-- Filter dan Unduh PDF -->
    <form method="GET" style="display:flex; gap:10px; align-items:center;">
        <select name="matkul_filter">
            <option value="">-- Semua Mata Kuliah --</option>
            <?php 
            $mk = mysqli_query($conn, "SELECT * FROM matakuliah_231051 ORDER BY nama_matakuliah_231051");
            $selectedMK = $_GET['matkul_filter'] ?? '';
            while($m = mysqli_fetch_assoc($mk)):
            ?>
            <option value="<?= $m['id_matakuliah_231051'] ?>" <?= $selectedMK == $m['id_matakuliah_231051'] ? 'selected' : '' ?>>
                <?= $m['nama_matakuliah_231051'] ?>
            </option>
            <?php endwhile; ?>
        </select>
        <button type="submit">Filter</button>
        <button type="button" onclick="window.location.href='laporanAbsensiPDF.php?matkul=<?= $selectedMK ?>'">Unduh PDF</button>
    </form>
  </div>

<?php
/* ============================================================
   AUTO INSERT ALFA UNTUK MAHASISWA YANG BELUM ABSEN
   ============================================================ */

date_default_timezone_set("Asia/Makassar");
$tanggalHariIni = date("Y-m-d");
$hariIni = date("l");

$hariMap = [
    "Monday" => "Senin",
    "Tuesday" => "Selasa",
    "Wednesday" => "Rabu",
    "Thursday" => "Kamis",
    "Friday" => "Jumat",
    "Saturday" => "Sabtu",
    "Sunday" => "Minggu"
];

$hariDB = $hariMap[$hariIni];

$queryJadwalHariIni = mysqli_query($conn, "
    SELECT 
        mk.id_matakuliah_231051,
        mk.id_kelas_231051,
        j.id_jam_231051,
        j.jam_selesai_231051
    FROM matakuliah_231051 mk
    JOIN jam_231051 j ON mk.id_jam_231051 = j.id_jam_231051
    WHERE j.hari_231051 = '$hariDB'
");

$now = time();

while ($jd = mysqli_fetch_assoc($queryJadwalHariIni)) {

    $jamSelesai = strtotime($jd['jam_selesai_231051']);

    if ($now < $jamSelesai) continue;

    $id_mk    = $jd['id_matakuliah_231051'];
    $id_jam   = $jd['id_jam_231051'];
    $id_kelas = $jd['id_kelas_231051'];

    $queryMahasiswa = mysqli_query($conn, "
        SELECT id_mahasiswa_231051
        FROM mahasiswa_231051
        WHERE id_kelas_231051 = '$id_kelas'
    ");

    while ($m = mysqli_fetch_assoc($queryMahasiswa)) {
        $idm = $m['id_mahasiswa_231051'];

        $cekAbsen = mysqli_query($conn, "
            SELECT id_absensi_231051 
            FROM absensi_231051
            WHERE id_mahasiswa_231051 = '$idm'
            AND tanggal_231051 = '$tanggalHariIni'
            AND id_matakuliah_231051 = '$id_mk'
            LIMIT 1
        ");

        if (mysqli_num_rows($cekAbsen) == 0) {
            mysqli_query($conn, "
                INSERT INTO absensi_231051
                (id_mahasiswa_231051, id_matakuliah_231051, id_jam_231051, tanggal_231051, status_231051)
                VALUES
                ('$idm', '$id_mk', '$id_jam', '$tanggalHariIni', 'Alfa')
            ");
        }
    }
}

/* ============================================================
   END AUTO ALFA
   ============================================================ */

// ==== QUERY ABSENSI + FILTER MATA KULIAH ====

$selectedMK = $_GET['matkul_filter'] ?? '';

$whereMK = "";
if (!empty($selectedMK)) {
    $safeMK = mysqli_real_escape_string($conn, $selectedMK);
    $whereMK = "WHERE a.id_matakuliah_231051 = '$safeMK'";
}

$queryAbsensi = "
SELECT 
    a.id_absensi_231051 AS ID_Absensi,
    m.nama_mahasiswa_231051 AS Nama_Mahasiswa,
    mk.nama_matakuliah_231051 AS Mata_Kuliah,
    j.hari_231051 AS Hari,
    DATE_FORMAT(j.jam_mulai_231051, '%H:%i') AS Jam_Mulai,
    DATE_FORMAT(j.jam_selesai_231051, '%H:%i') AS Jam_Selesai,
    a.tanggal_231051 AS Tanggal,
    a.status_231051 AS Status
FROM absensi_231051 a
LEFT JOIN mahasiswa_231051 m ON a.id_mahasiswa_231051 = m.id_mahasiswa_231051
LEFT JOIN matakuliah_231051 mk ON a.id_matakuliah_231051 = mk.id_matakuliah_231051
LEFT JOIN jam_231051 j ON a.id_jam_231051 = j.id_jam_231051
$whereMK
ORDER BY a.tanggal_231051 DESC
";

$resultAbsensi = mysqli_query($conn, $queryAbsensi);
render_table_from_result($resultAbsensi);
?>

</div>

  <!-- QR CODE -->
  <div id="qrcode" class="content <?= $activeTab=='qrcode'?'active':'' ?>">
    <h1>QR Code Absensi</h1>
    <p>Pilih kelas, mata kuliah, dan jam untuk membuat QR Code absensi.</p>

    <?php
    $kelas = mysqli_query($conn, "SELECT * FROM kelas_231051 ORDER BY nama_kelas_231051");
    $mk    = mysqli_query($conn, "SELECT * FROM matakuliah_231051 ORDER BY nama_matakuliah_231051");
    $jam   = mysqli_query($conn, "SELECT * FROM jam_231051 ORDER BY jam_mulai_231051");
    ?>

    <form method="POST">
      <select name="kelas" required>
        <option value="">-- Pilih Kelas --</option>
        <?php while ($k = mysqli_fetch_assoc($kelas)): ?>
        <option value="<?= $k['id_kelas_231051'] ?>"><?= $k['nama_kelas_231051'] ?></option>
        <?php endwhile; ?>
      </select>

      <select name="mk" required>
        <option value="">-- Pilih Mata Kuliah --</option>
        <?php while ($m = mysqli_fetch_assoc($mk)): ?>
        <option value="<?= $m['id_matakuliah_231051'] ?>"><?= $m['nama_matakuliah_231051'] ?></option>
        <?php endwhile; ?>
      </select>

      <select name="jam" required>
        <option value="">-- Pilih Jam --</option>
        <?php while ($j = mysqli_fetch_assoc($jam)): ?>
          <?php 
        $jamMulai   = date("H:i", strtotime($j['jam_mulai_231051']));
        $jamSelesai = date("H:i", strtotime($j['jam_selesai_231051']));
        ?>
        <option value="<?= $j['id_jam_231051'] ?>">
          <?= $j['hari_231051'] ?> | <?= $jamMulai ?> - <?= $jamSelesai ?>
        </option>

        <?php endwhile; ?>
      </select>

      <br>
      <button type="submit" name="generate" class="qr-btn">Generate QR Code</button>
    </form>

    <?php if ($qr != ""): ?>
    <div class="qr-box">
      <h3>QR Code Absensi</h3>
      <img src="<?= $qr ?>" alt="QR Code">
      <p><b>Data QR:</b> <?= htmlspecialchars($qr_value) ?></p>
    </div>
    <?php endif; ?>

  </div>

</div>

<script>
function openPage(name, el) {
  document.querySelectorAll('.content').forEach(c => c.classList.remove('active'));
  document.getElementById(name).classList.add('active');

  document.querySelectorAll('.tablink').forEach(b => b.classList.remove('active'));
  el.classList.add('active');
}
</script>

</body>
</html>

<?php mysqli_close($conn); ?>
