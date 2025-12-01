<?php
session_start();

// ==== CEK LOGIN ====
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'mahasiswa') {
  header("Location: index.php");
  exit;
}

// ==== KONEKSI DATABASE ====
$servername = "localhost";
$username   = "root";
$password   = "";
$database   = "absensiqr_231051";
$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
  die("Koneksi gagal: " . mysqli_connect_error());
}

// ==== AMBIL DATA MAHASISWA ====
$email = $_SESSION['email'];
$namaMahasiswa = $email;

// ==== AMBIL ID & KELAS MAHASISWA ====
$getMhs = mysqli_query($conn, "SELECT id_mahasiswa_231051, kelas_231051 
                               FROM mahasiswa_231051 
                               WHERE email_231051 = '$email'");
$mhsData = mysqli_fetch_assoc($getMhs);
$kelasMahasiswa = $mhsData['kelas_231051'];
$idMahasiswa = $mhsData['id_mahasiswa_231051'];

// ==== AMBIL JADWAL KULIAH ====
$jadwalQuery = mysqli_query($conn, "
  SELECT 
    m.kode_matakuliah_231051 AS kode_mk,
    m.nama_matakuliah_231051 AS nama_mk,
    k.nama_kelas_231051 AS kelas,
    d.nama_dosen_231051 AS dosen,
    j.hari_231051 AS hari,
    CONCAT(TIME_FORMAT(j.jam_mulai_231051, '%H:%i'), ' - ', TIME_FORMAT(j.jam_selesai_231051, '%H:%i')) AS jam,
    m.ruang_231051 AS ruang
  FROM matakuliah_231051 m
  LEFT JOIN kelas_231051 k ON m.id_kelas_231051 = k.id_kelas_231051
  LEFT JOIN dosen_231051 d ON m.id_dosen_231051 = d.id_dosen_231051
  LEFT JOIN jam_231051 j ON m.id_jam_231051 = j.id_jam_231051
  ORDER BY 
    FIELD(j.hari_231051, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'),
    j.jam_mulai_231051 ASC
");

// ==== RIWAYAT ABSENSI (FIX) ====
$absensiQuery = mysqli_query($conn, "
  SELECT 
      a.tanggal_231051,
      a.status_231051,
      m.nama_matakuliah_231051 AS nama_mk
  FROM absensi_231051 a
  LEFT JOIN matakuliah_231051 m 
      ON a.id_matakuliah_231051 = m.id_matakuliah_231051
  WHERE a.id_mahasiswa_231051 = '$idMahasiswa'
  ORDER BY a.tanggal_231051 DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Mahasiswa - Absensi QR</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/html5-qrcode" defer></script>

  <style>
    * {margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
    body {display:flex;min-height:100vh;background:#f4f7ff;color:#333;}

    /* Sidebar */
    .sidebar {
      width:250px;
      background:linear-gradient(180deg,#0066ff,#3b82f6);
      color:white;padding:30px 20px;
      display:flex;flex-direction:column;justify-content:space-between;
      border-top-right-radius:20px;border-bottom-right-radius:20px;
    }
    .sidebar h2{text-align:center;font-size:22px;font-weight:700;}
    .sidebar h2 span{font-weight:400;font-size:16px;}
    .sidebar ul{list-style:none;margin-top:40px;}
    .sidebar ul li{
      padding:12px 20px;border-radius:10px;margin-bottom:10px;
      cursor:pointer;transition:background 0.3s;
    }
    .sidebar ul li:hover{background:rgba(255,255,255,0.25);}
    .sidebar ul li.active{background:rgba(255,255,255,0.3);font-weight:600;}

    /* Main Content */
    .main-content{flex:1;padding:40px 60px;overflow-y:auto;}
    header{text-align:center;margin-bottom:40px;}
    header h1{font-size:26px;color:#1a56db;}
    header p{color:#555;margin-top:8px;font-size:15px;}

    .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:30px;}
    .card-modern{
      background:white;padding:25px;border-radius:18px;
      box-shadow:0 4px 10px rgba(0,0,0,0.1);text-align:center;transition:0.3s;
    }
    .card-modern:hover{transform:translateY(-3px);box-shadow:0 6px 16px rgba(0,0,0,0.15);}
    .card-modern h3{color:#1a56db;font-weight:600;margin-bottom:15px;}
    .card-modern button{
      background:#1a56db;color:white;border:none;padding:10px 18px;
      border-radius:10px;cursor:pointer;transition:background 0.3s;
    }
    .card-modern button:hover{background:#144cc3;}

    table{width:100%;border-collapse:collapse;background:white;border-radius:10px;overflow:hidden;box-shadow:0 3px 10px rgba(0,0,0,0.1);margin-top:15px;}
    table thead{background:#1a56db;color:white;}
    table th,table td{padding:10px 12px;text-align:center;font-size:14px;}
    table tr:nth-child(even){background:#f4f7ff;}
    table tr:hover{background:#e0ecff;}
    .status{padding:5px 10px;border-radius:8px;font-size:13px;font-weight:600;}
    .status.hadir{background:#d1fae5;color:#065f46;}
    .status.alfa{background:#fee2e2;color:#991b1b;}
    .status.terlambat{background:#fff3e0;color:#e65100;}
    .status.izin{background:#e3f2fd;color:#1565c0;}
    .status.sakit{background:#f3e5f5;color:#7b1fa2;}

    .content-section{display:none;}
    .content-section.active{display:block;}

    #reader{width:320px;margin:20px auto;}
  </style>
</head>

<body>
  <!-- Sidebar -->
  <aside class="sidebar">
    <div>
      <h2>Absensi<br><span>QR System</span></h2>
      <ul>
        <li class="active" onclick="showSection(event,'dashboard')">Dashboard</li>
        <li onclick="showSection(event,'scanqr')">Scan QR</li>
        <li onclick="showSection(event,'riwayat')">Riwayat Absensi</li>
      </ul>
    </div>
    <ul><li onclick="logout()">Logout</li></ul>
  </aside>

  <!-- Main Content -->
  <main class="main-content">

    <!-- Dashboard -->
    <section id="dashboard" class="content-section active">
      <header>
        <h1>Selamat Datang, <span><?= htmlspecialchars($namaMahasiswa) ?></span>!</h1>
        <p>Pastikan kamu hadir di setiap perkuliahan dan lakukan absensi tepat waktu.</p>
      </header>

      <div class="grid">
        <div class="card-modern">
          <h3>Jadwal Kuliah</h3>
          <p>Lihat jadwal perkuliahan kamu berdasarkan data sistem.</p>
          <button onclick="showSection(event,'jadwal')">Lihat Jadwal</button>
        </div>
        <div class="card-modern">
          <h3>Scan QR Absensi</h3>
          <p>Gunakan kamera untuk memindai QR dari dosen saat perkuliahan berlangsung.</p>
          <button onclick="showSection(event,'scanqr')">Scan Sekarang</button>
        </div>
        <div class="card-modern">
          <h3>Riwayat Absensi</h3>
          <p>Lihat catatan kehadiranmu di setiap mata kuliah.</p>
          <button onclick="showSection(event,'riwayat')">Lihat Riwayat</button>
        </div>
      </div>
    </section>

    <!-- Jadwal Kuliah -->
    <section id="jadwal" class="content-section">
      <h2 style="text-align:center;color:#1a56db;">Jadwal Kuliah Mahasiswa</h2>
      <table>
        <thead>
          <tr>
            <th>Kode MK</th>
            <th>Nama MK</th>
            <th>Kelas</th>
            <th>Dosen</th>
            <th>Hari</th>
            <th>Jam</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($jadwalQuery && mysqli_num_rows($jadwalQuery) > 0) {
            while ($row = mysqli_fetch_assoc($jadwalQuery)) {
              echo "<tr>
                <td>{$row['kode_mk']}</td>
                <td>{$row['nama_mk']}</td>
                <td>{$row['kelas']}</td>
                <td>{$row['dosen']}</td>
                <td>{$row['hari']}</td>
                <td>{$row['jam']}</td>
              </tr>";
            }
          } else {
            echo "<tr><td colspan='7'>Belum ada jadwal perkuliahan.</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </section>

    <!-- Scan QR -->
    <section id="scanqr" class="content-section">
      <h2>Scan QR Absensi</h2>
      <p style="text-align:center;">Arahkan kamera ke QR Code dari dosen.</p>

      <div id="reader"></div>

      <div style="text-align:center;margin-top:20px;">
        <button id="startCam">Aktifkan Kamera</button>
        <button id="stopCam" style="display:none;background:#ef4444;color:white;">Matikan Kamera</button>
      </div>

      <p id="scan-result" style="text-align:center;margin-top:15px;font-weight:600;"></p>
    </section>

    <!-- Riwayat Absensi -->
    <section id="riwayat" class="content-section">
      <h2>Riwayat Absensi</h2>
      <table>
        <thead>
          <tr><th>Tanggal</th><th>Mata Kuliah</th><th>Status</th></tr>
        </thead>
        <tbody>
          <?php
          if ($absensiQuery && mysqli_num_rows($absensiQuery) > 0) {
            while ($row = mysqli_fetch_assoc($absensiQuery)) {
              $statusClass = strtolower($row['status_231051']);
              // Make sure status class is valid
              if (!in_array($statusClass, ['hadir', 'alfa', 'terlambat', 'izin', 'sakit'])) {
                $statusClass = 'alfa'; // default to alfa if status is not recognized
              }
              echo "<tr>
                <td>{$row['tanggal_231051']}</td>
                <td>{$row['nama_mk']}</td>
                <td><span class='status {$statusClass}'>" . ucfirst($row['status_231051']) . "</span></td>
              </tr>";
            }
          } else {
            echo "<tr><td colspan='3'>Belum ada data absensi.</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </section>

  </main>

  <script>
    function showSection(event, id) {
      document.querySelectorAll('.content-section').forEach(sec => sec.classList.remove('active'));
      document.getElementById(id).classList.add('active');
      document.querySelectorAll('.sidebar li').forEach(li => li.classList.remove('active'));
      if (event) event.target.classList.add('active');
    }

    function logout() { window.location.href = 'logout.php'; }

    let html5QrCode;
    let isScanning = false;
    const startButton = document.getElementById("startCam");
    const stopButton = document.getElementById("stopCam");
    const resultElement = document.getElementById("scan-result");

    startButton.addEventListener("click", () => {
      html5QrCode = new Html5Qrcode("reader");

      const config = { fps: 10, qrbox: 250 };

      html5QrCode.start({ facingMode: "environment" }, config, qrCodeMessage => {

        if (isScanning) return;
        isScanning = true;

        resultElement.style.color = "#059669";
        resultElement.innerText = "✅ QR Terdeteksi: " + qrCodeMessage;

        // === KIRIM QR KE PHP UNTUK DISIMPAN ===
        fetch("prosesAbsensi.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: "qr_data=" + encodeURIComponent(qrCodeMessage)
        })
        .then(res => res.text())
        .then(response => {
          alert(response);
          isScanning = false;
        });

        html5QrCode.stop();
        startButton.style.display = "block"; 
        stopButton.style.display = "none";
      }).then(() => {
        startButton.style.display = "none";
        stopButton.style.display = "block";
      }).catch(err => {
        alert("Tidak dapat mengakses kamera: " + err);
      });
    });

    stopButton.addEventListener("click", () => {
      if (html5QrCode) html5QrCode.stop().then(() => {
        startButton.style.display = "block";
        stopButton.style.display = "none";
      });
    });
  </script>
</body>
</html>

<?php mysqli_close($conn); ?>
