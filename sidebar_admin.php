<!-- sidebar_admin.php -->
<aside class="sidebar">
  <h2>Admin Panel</h2>
  <ul>
    <li onclick="window.location.href='dashboardAdmin.php'">Dashboard</li>
    <li onclick="window.location.href='data_dosen.php'">Data Dosen</li>
    <li onclick="window.location.href='data_mahasiswa.php'">Data Mahasiswa</li>
    <li onclick="window.location.href='data_matakuliah.php'">Mata Kuliah</li>
    <li onclick="window.location.href='data_kelas.php'">Data Kelas</li>
    <li onclick="window.location.href='data_absensi.php'">Data Absensi</li>
    <li onclick="window.location.href='list_dosen.php'">List Dosen</li>
    <li onclick="window.location.href='jam.php'">list Jam Absensi</li>
    <li onclick="window.location.href='logout.php'">Logout</li>
  </ul>
</aside>

<style>
  .sidebar {
    background-color: #0a7ae9;
    color: white;
    width: 250px;
    height: 100vh;
    padding: 20px;
    position: fixed;
    left: 0;
    top: 0;
    border-radius: 0 15px 15px 0;
  }
  .sidebar ul { list-style: none; padding: 0; }
  .sidebar li {
    padding: 12px 18px;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s;
  }
  .sidebar li:hover, .sidebar li.active {
    background-color: #1565c0;
  }
</style>
