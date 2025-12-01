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
  echo "<th>Aksi</th>"; // Tambah kolom untuk aksi
  echo "</tr></thead><tbody>";

  while ($row = mysqli_fetch_assoc($result)) {
    // Tentukan class CSS berdasarkan status
    $rowClass = "";
    $status = $row['Status'] ?? '';

    if ($status == 'Alfa') {
      $rowClass = "status-alfa";
    } elseif ($status == 'Terlambat') {
      $rowClass = "status-terlambat";
    } elseif ($status == 'Hadir') {
      $rowClass = "status-hadir";
    } elseif ($status == 'Izin') {
      $rowClass = "status-izin";
    } elseif ($status == 'Sakit') {
      $rowClass = "status-sakit";
    }

    echo "<tr class='$rowClass'>";
    foreach ($fields as $f) {
      $col = $f->name;
      $value = $row[$col] ?? '';

      // Format kolom Keterlambatan_Menit
      if ($col == 'Keterlambatan_Menit') {
        if ($value > 0) {
          echo "<td><b>" . htmlspecialchars($value) . " menit</b></td>";
        } else {
          echo "<td>-</td>";
        }
      }
      // Tambahkan emoji untuk status
      elseif ($col == 'Status') {
        $icon = '';
        if ($value == 'Alfa') $icon = '🚫 ';
        elseif ($value == 'Terlambat') $icon = '⏰ ';
        elseif ($value == 'Hadir') $icon = '✅ ';
        elseif ($value == 'Izin') $icon = '📋 ';
        elseif ($value == 'Sakit') $icon = '🤒 ';
        echo "<td id='status_" . $row['ID_Absensi'] . "'><b>" . $icon . htmlspecialchars($value) . "</b></td>";
      }
      else {
        echo "<td>" . htmlspecialchars($value) . "</td>";
      }
    }
    // Kolom aksi untuk mengubah status
    echo "<td>
      <select onchange='updateStatus(" . $row['ID_Absensi'] . ", this.value)' style='padding:5px; border-radius:4px;'>
        <option value='Hadir' " . ($status == 'Hadir' ? 'selected' : '') . ">✅ Hadir</option>
        <option value='Terlambat' " . ($status == 'Terlambat' ? 'selected' : '') . ">⏰ Terlambat</option>
        <option value='Alfa' " . ($status == 'Alfa' ? 'selected' : '') . ">🚫 Alfa</option>
        <option value='Izin' " . ($status == 'Izin' ? 'selected' : '') . ">📋 Izin</option>
        <option value='Sakit' " . ($status == 'Sakit' ? 'selected' : '') . ">🤒 Sakit</option>
      </select>
    </td>";
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
    .status-alfa { background: #ffebee; color: #c62828; font-weight: bold; }
    .status-terlambat { background: #fff3e0; color: #e65100; }
    .status-hadir { background: #e8f5e9; color: #2e7d32; }
    .status-izin { background: #e3f2fd; color: #1565c0; }
    .status-sakit { background: #f3e5f5; color: #7b1fa2; }
    .stats-container { display: flex; gap: 15px; margin: 20px 0; flex-wrap: wrap; }
    .stat-card { flex: 1; min-width: 150px; padding: 15px; border-radius: 8px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    .stat-card h3 { margin: 0 0 5px 0; font-size: 32px; }
    .stat-card p { margin: 0; font-size: 14px; color: #666; }
    .stat-alfa { background: #ffebee; border-left: 4px solid #c62828; }
    .stat-terlambat { background: #fff3e0; border-left: 4px solid #e65100; }
    .stat-hadir { background: #e8f5e9; border-left: 4px solid #2e7d32; }
    .stat-izin { background: #e3f2fd; border-left: 4px solid #1565c0; }
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
    
    <?php
    // Get ID dosen from email
    $queryDosen = mysqli_query($conn, "SELECT id_dosen_231051 FROM dosen_231051 WHERE email_231051 = '$email'");
    if ($queryDosen && mysqli_num_rows($queryDosen) > 0) {
        $dosenData = mysqli_fetch_assoc($queryDosen);
        $id_dosen = $dosenData['id_dosen_231051'];
        
        // Get assigned students and their absence statistics
        $queryAssigned = mysqli_query($conn, "
            SELECT 
                m.id_mahasiswa_231051,
                m.nama_mahasiswa_231051,
                m.nim_231051,
                m.kelas_231051,
                (SELECT COUNT(*) FROM absensi_231051
                 WHERE id_mahasiswa_231051 = m.id_mahasiswa_231051
                 AND status_231051 = 'Alfa') as total_alfa,
                (SELECT COUNT(*) FROM absensi_231051
                 WHERE id_mahasiswa_231051 = m.id_mahasiswa_231051
                 AND status_231051 = 'Hadir') as total_hadir,
                (SELECT COUNT(*) FROM absensi_231051
                 WHERE id_mahasiswa_231051 = m.id_mahasiswa_231051
                 AND status_231051 = 'Terlambat') as total_terlambat,
                (SELECT COUNT(*) FROM absensi_231051
                 WHERE id_mahasiswa_231051 = m.id_mahasiswa_231051
                 AND status_231051 = 'Izin') as total_izin,
                (SELECT COUNT(*) FROM absensi_231051
                 WHERE id_mahasiswa_231051 = m.id_mahasiswa_231051
                 AND status_231051 = 'Sakit') as total_sakit
            FROM dosen_mahasiswa_231051 dm
            JOIN mahasiswa_231051 m ON dm.id_mahasiswa_231051 = m.id_mahasiswa_231051
            WHERE dm.id_dosen_231051 = '$id_dosen'
            ORDER BY m.nama_mahasiswa_231051
        ");
        
        $totalStudents = mysqli_num_rows($queryAssigned);
        $totalAbsent = 0;
        
        // Calculate total absent
        $queryTotalAbsent = mysqli_query($conn, "
            SELECT COUNT(*) as total
            FROM absensi_231051 a
            JOIN dosen_mahasiswa_231051 dm ON a.id_mahasiswa_231051 = dm.id_mahasiswa_231051
            WHERE dm.id_dosen_231051 = '$id_dosen' AND a.status_231051 = 'Alfa'
        ");
        if ($queryTotalAbsent) {
            $totalAbsentData = mysqli_fetch_assoc($queryTotalAbsent);
            $totalAbsent = $totalAbsentData['total'];
        }
    ?>
    
    <div style="margin-top:30px;">
        <h2>📊 Statistik Mahasiswa Binaan</h2>
        <div class="stats-container" style="margin-bottom:20px;">
            <div class="stat-card" style="background:#e3f2fd;border-left:4px solid #1976d2;">
                <h3><?= $totalStudents ?></h3>
                <p>Total Mahasiswa Binaan</p>
            </div>
            <div class="stat-card stat-alfa">
                <h3><?= $totalAbsent ?></h3>
                <p>Total Ketidakhadiran (Alfa)</p>
            </div>
        </div>
        
        <?php if ($totalStudents > 0): ?>
        <h3>Daftar Mahasiswa Binaan & Statistik Absensi</h3>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Mahasiswa</th>
                    <th>NIM</th>
                    <th>Kelas</th>
                    <th>🚫 Alfa</th>
                    <th>✅ Hadir</th>
                    <th>⏰ Terlambat</th>
                    <th>📋 Izin</th>
                    <th>🤒 Sakit</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                while ($student = mysqli_fetch_assoc($queryAssigned)): 
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($student['nama_mahasiswa_231051']) ?></td>
                    <td><?= htmlspecialchars($student['nim_231051']) ?></td>
                    <td><?= htmlspecialchars($student['kelas_231051']) ?></td>
                    <td class="status-alfa"><b><?= $student['total_alfa'] ?></b></td>
                    <td class="status-hadir"><b><?= $student['total_hadir'] ?></b></td>
                    <td class="status-terlambat"><b><?= $student['total_terlambat'] ?></b></td>
                    <td class="status-izin"><b><?= $student['total_izin'] ?></b></td>
                    <td class="status-sakit"><b><?= $student['total_sakit'] ?></b></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div style="background:#fff3cd;padding:15px;border-radius:8px;margin-top:15px;">
            <p style="margin:0;color:#856404;">⚠️ Belum ada mahasiswa yang ditugaskan kepada Anda. Silakan hubungi admin untuk menambahkan mahasiswa binaan.</p>
        </div>
        <?php endif; ?>
    </div>
    
    <?php } ?>
  </div>

  <!-- ABSENSI -->
<div id="absensi" class="content <?= $activeTab=='absensi'?'active':'' ?>">
  <h1>Riwayat Absensi</h1>

<?php
// ==== HITUNG STATISTIK ABSENSI ====
$selectedMK_stat = $_GET['matkul_filter'] ?? '';
$whereMK_stat = "";
if (!empty($selectedMK_stat)) {
    $safeMK_stat = mysqli_real_escape_string($conn, $selectedMK_stat);
    $whereMK_stat = "WHERE id_matakuliah_231051 = '$safeMK_stat'";
}

$statQuery = mysqli_query($conn, "
    SELECT 
        status_231051, 
        COUNT(*) as jumlah 
    FROM absensi_231051 
    $whereMK_stat
    GROUP BY status_231051
");

$stats = ['Hadir' => 0, 'Alfa' => 0, 'Terlambat' => 0, 'Izin' => 0, 'Sakit' => 0];
while ($s = mysqli_fetch_assoc($statQuery)) {
    $stats[$s['status_231051']] = $s['jumlah'];
}
?>

  <!-- STATISTIK CARDS -->
  <div class="stats-container">
    <div class="stat-card stat-alfa">
      <h3><?= $stats['Alfa'] ?></h3>
      <p>🚫 Tidak Hadir (Alfa)</p>
    </div>
    <div class="stat-card stat-terlambat">
      <h3><?= $stats['Terlambat'] ?></h3>
      <p>⏰ Terlambat</p>
    </div>
    <div class="stat-card stat-hadir">
      <h3><?= $stats['Hadir'] ?></h3>
      <p>✅ Hadir</p>
    </div>
    <div class="stat-card stat-izin">
      <h3><?= $stats['Izin'] ?></h3>
      <p>📋 Izin</p>
    </div>
    <div class="stat-card stat-sakit">
      <h3><?= $stats['Sakit'] ?></h3>
      <p>🤒 Sakit</p>
    </div>
  </div>

  <div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
    <h2 style="margin:0;">Data Lengkap</h2>

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
        
        <select name="status_filter">
            <?php $selectedStatus = $_GET['status_filter'] ?? ''; ?>
            <option value="">-- Semua Status --</option>
            <option value="Alfa" <?= $selectedStatus == 'Alfa' ? 'selected' : '' ?>>🚫 Alfa (Tidak Hadir)</option>
            <option value="Terlambat" <?= $selectedStatus == 'Terlambat' ? 'selected' : '' ?>>⏰ Terlambat</option>
            <option value="Hadir" <?= $selectedStatus == 'Hadir' ? 'selected' : '' ?>>✅ Hadir</option>
            <option value="Izin" <?= $selectedStatus == 'Izin' ? 'selected' : '' ?>>📋 Izin</option>
            <option value="Sakit" <?= $selectedStatus == 'Sakit' ? 'selected' : '' ?>>🤒 Sakit</option>
        </select>
        
        <button type="submit">Filter</button>
        <button type="button" onclick="window.location.href='laporanAbsensiPDF.php?matkul=<?= $selectedMK ?>'">Unduh PDF</button>
    </form>
  </div>

<?php
/* ============================================================
   AUTO INSERT ALFA/TERLAMBAT UNTUK MAHASISWA YANG BELUM ABSEN
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
        j.jam_mulai_231051,
        j.jam_selesai_231051
    FROM matakuliah_231051 mk
    JOIN jam_231051 j ON mk.id_jam_231051 = j.id_jam_231051
    WHERE j.hari_231051 = '$hariDB'
");

$now = time();

while ($jd = mysqli_fetch_assoc($queryJadwalHariIni)) {

    $jamMulai = strtotime($jd['jam_mulai_231051']);
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
            // Student didn't attend at all, mark as Alfa (absent)
            $status = 'Alfa';
            // Set late time to 0 for students marked as absent
            $menitTerlambat = 0;

            mysqli_query($conn, "
                INSERT INTO absensi_231051
                (id_mahasiswa_231051, id_matakuliah_231051, id_jam_231051, tanggal_231051, status_231051, keterlambatan_menit_231051)
                VALUES
                ('$idm', '$id_mk', '$id_jam', '$tanggalHariIni', '$status', '$menitTerlambat')
            ");
        }
    }
}

/* ============================================================
   END AUTO ALFA/TERLAMBAT
   ============================================================ */

// ==== QUERY ABSENSI + FILTER MATA KULIAH ====

$selectedMK = $_GET['matkul_filter'] ?? '';
$selectedStatus = $_GET['status_filter'] ?? '';

$whereConditions = [];
if (!empty($selectedMK)) {
    $safeMK = mysqli_real_escape_string($conn, $selectedMK);
    $whereConditions[] = "a.id_matakuliah_231051 = '$safeMK'";
}
if (!empty($selectedStatus)) {
    $safeStatus = mysqli_real_escape_string($conn, $selectedStatus);
    $whereConditions[] = "a.status_231051 = '$safeStatus'";
}

$whereMK = "";
if (!empty($whereConditions)) {
    $whereMK = "WHERE " . implode(" AND ", $whereConditions);
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
    a.status_231051 AS Status,
    a.keterlambatan_menit_231051 AS Keterlambatan_Menit
FROM absensi_231051 a
LEFT JOIN mahasiswa_231051 m ON a.id_mahasiswa_231051 = m.id_mahasiswa_231051
LEFT JOIN matakuliah_231051 mk ON a.id_matakuliah_231051 = mk.id_matakuliah_231051
LEFT JOIN jam_231051 j ON a.id_jam_231051 = j.id_jam_231051
$whereMK
ORDER BY a.tanggal_231051 DESC, a.status_231051 DESC
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

function updateStatus(absensiId, newStatus) {
  if (confirm('Apakah Anda yakin ingin mengubah status menjadi ' + newStatus + '?')) {
    // Send AJAX request to update the status
    fetch('updateStatus.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: 'id=' + encodeURIComponent(absensiId) + '&status=' + encodeURIComponent(newStatus)
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Update the status display in the table
        document.getElementById('status_' + absensiId).innerHTML = '<b>' + getIconForStatus(newStatus) + newStatus + '</b>';
        // Update row class if needed
        updateRowClass(absensiId, newStatus);
        alert('Status berhasil diubah menjadi ' + newStatus);
      } else {
        alert('Gagal mengubah status: ' + data.message);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Terjadi kesalahan saat mengubah status.');
    });
  } else {
    // Reset to original value if user cancels
    location.reload();
  }
}

function getIconForStatus(status) {
  switch(status) {
    case 'Hadir': return '✅ ';
    case 'Terlambat': return '⏰ ';
    case 'Alfa': return '🚫 ';
    case 'Izin': return '📋 ';
    case 'Sakit': return '🤒 ';
    default: return '';
  }
}

function updateRowClass(absensiId, status) {
  // Find the parent row and update its class based on status
  var row = document.querySelector(`tr:has(td #status_${absensiId})`);
  if (!row) {
    // Alternative method to find the row
    var allRows = document.querySelectorAll('tr');
    for (var i = 0; i < allRows.length; i++) {
      if (allRows[i].querySelector(`#status_${absensiId}`)) {
        row = allRows[i];
        break;
      }
    }
  }

  if (row) {
    row.className = 'status-' + status.toLowerCase();
  }
}
</script>

</body>
</html>

<?php mysqli_close($conn); ?>
