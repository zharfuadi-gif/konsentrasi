<?php
session_start();
include "koneksi.php";

// Cek login dan role admin
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

// Tambah data
if (isset($_POST['tambah'])) {
    $nama = $_POST['nama'];
    $nip = $_POST['nip'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "INSERT INTO dosen_231051 (nama_dosen_231051, nip_231051, email_231051, password_231051)
            VALUES ('$nama', '$nip', '$email', '$password')";
    $koneksi->query($sql);
    header("Location: list_dosen.php");
    exit;
}

// Update data
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $nip = $_POST['nip'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "UPDATE dosen_231051 
            SET nama_dosen_231051='$nama', nip_231051='$nip', email_231051='$email', password_231051='$password'
            WHERE id_dosen_231051='$id'";
    $koneksi->query($sql);
    header("Location: list_dosen.php");
    exit;
}

// Hapus data
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $koneksi->query("DELETE FROM dosen_231051 WHERE id_dosen_231051='$id'");
    header("Location: list_dosen.php");
    exit;
}

// Ambil semua data dosen
$dosen = $koneksi->query("SELECT * FROM dosen_231051");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>List Dosen - Admin Panel</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
  font-family: 'Poppins', sans-serif;
  background-color: #f8f9fa;
}
.sidebar {
  width: 250px;
  height: 100vh;
  background-color: #0d6efd;
  color: white;
  position: fixed;
  left: 0;
  top: 0;
  padding: 20px;
}
.sidebar h4 {
  text-align: center;
  margin-bottom: 30px;
}
.sidebar a {
  display: block;
  color: white;
  text-decoration: none;
  padding: 10px;
  margin-bottom: 5px;
  border-radius: 5px;
}
.sidebar a:hover {
  background-color: #0056b3;
}
.main {
  margin-left: 270px;
  padding: 30px;
}
.table-container {
  background: white;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
</style>
</head>
<body>

<?php include 'sidebar_admin.php'; ?>

<div class="main">
  <h2 class="fw-bold mb-4">📋 List Data Dosen</h2>

  <!-- Tombol Tambah -->
  <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalTambah">+ Tambah Dosen</button>

  <div class="table-container">
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-primary text-center">
        <tr>
          <th>No</th>
          <th>Nama Dosen</th>
          <th>NIP</th>
          <th>Email</th>
          <th>Password</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $no = 1;
        if ($dosen->num_rows > 0) {
            while ($row = $dosen->fetch_assoc()) {
                echo "<tr>
                        <td class='text-center'>$no</td>
                        <td>{$row['nama_dosen_231051']}</td>
                        <td>{$row['nip_231051']}</td>
                        <td>{$row['email_231051']}</td>
                        <td>{$row['password_231051']}</td>
                        <td class='text-center'>
                          <button class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#modalEdit{$row['id_dosen_231051']}'>Edit</button>
                          <a href='?hapus={$row['id_dosen_231051']}' class='btn btn-danger btn-sm' onclick=\"return confirm('Yakin ingin menghapus data ini?')\">Hapus</a>
                        </td>
                      </tr>";

                // Modal Edit untuk setiap baris
                echo "
                <div class='modal fade' id='modalEdit{$row['id_dosen_231051']}' tabindex='-1'>
                  <div class='modal-dialog'>
                    <div class='modal-content'>
                      <div class='modal-header'>
                        <h5 class='modal-title'>Edit Dosen</h5>
                        <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                      </div>
                      <form method='POST'>
                        <div class='modal-body'>
                          <input type='hidden' name='id' value='{$row['id_dosen_231051']}'>
                          <div class='mb-3'>
                            <label>Nama Dosen</label>
                            <input type='text' name='nama' class='form-control' value='{$row['nama_dosen_231051']}' required>
                          </div>
                          <div class='mb-3'>
                            <label>NIP</label>
                            <input type='text' name='nip' class='form-control' value='{$row['nip_231051']}' required>
                          </div>
                          <div class='mb-3'>
                            <label>Email</label>
                            <input type='email' name='email' class='form-control' value='{$row['email_231051']}' required>
                          </div>
                          <div class='mb-3'>
                            <label>Password</label>
                            <input type='text' name='password' class='form-control' value='{$row['password_231051']}' required>
                          </div>
                        </div>
                        <div class='modal-footer'>
                          <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Batal</button>
                          <button type='submit' name='update' class='btn btn-success'>Simpan Perubahan</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
                ";
                $no++;
            }
        } else {
            echo "<tr><td colspan='6' class='text-center text-muted'>Belum ada data dosen</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Tambah Dosen -->
<div class="modal fade" id="modalTambah" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Dosen Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <div class="modal-body">
          <div class="mb-3">
            <label>Nama Dosen</label>
            <input type="text" name="nama" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>NIP</label>
            <input type="text" name="nip" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Password</label>
            <input type="text" name="password" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" name="tambah" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
