<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Mahasiswa - Absensi QR</title>
  <link rel="stylesheet" href="style.css">
</head>
<body class="dashboard-body">
  <nav class="navbar">
    <h2>Absensi QR System</h2>
    <div class="nav-user">
      <span id="userName">Mahasiswa</span>
      <button onclick="logout()">Logout</button>
    </div>
  </nav>

  <main class="dashboard-content">
    <section class="card profile-card">
      <h3>Profil Mahasiswa</h3>
      <p><strong>Nama:</strong> <span id="namaMahasiswa">-</span></p>
      <p><strong>NIM:</strong> <span id="nimMahasiswa">-</span></p>
      <p><strong>Kelas:</strong> <span id="kelasMahasiswa">-</span></p>
    </section>

    <section class="card absensi-card">
      <h3>Riwayat Absensi</h3>
      <table class="absensi-table">
        <thead>
          <tr>
            <th>Tanggal</th>
            <th>Mata Kuliah</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody id="absensiList">
          <!-- Data absensi akan dimasukkan lewat JS -->
        </tbody>
      </table>
    </section>
  </main>

  <script src="script.js"></script>
</body>
</html>
