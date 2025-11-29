<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
  header("Location: index.php");
  exit;
}
include "koneksi.php";
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Data Mahasiswa - Admin</title>
<style>
  body {font-family:'Poppins',sans-serif;background:#f0f3f7;margin:0;display:flex;}
  .sidebar {background:#0a7ae9;color:#fff;width:250px;height:100vh;padding:20px;position:fixed;}
  .sidebar ul{list-style:none;padding:0;}
  .sidebar li{padding:12px 18px;border-radius:8px;cursor:pointer;}
  .sidebar li:hover{background:#1565c0;}
  .main-content{margin-left:270px;padding:25px;width:100%;}

  table{width:100%;border-collapse:collapse;background:#fff;margin-top:20px;}
  th,td{border:1px solid #ddd;padding:10px;text-align:center;}
  th{background:#1976d2;color:white;}

  input{padding:8px;width:90%;margin:5px;border-radius:5px;border:1px solid #ccc;}
  button{background:#1976d2;color:white;padding:10px 15px;border:none;border-radius:6px;cursor:pointer;}
  .edit-btn{background:#ffa000;color:#fff;}
  .delete-btn{background:#e53935;color:#fff;}

  /* Modal */
  .modal-bg {
    display:none; position:fixed; top:0; left:0; width:100%; height:100%;
    background:rgba(0,0,0,.5); padding-top:3%;
    z-index:999;
  }
  .modal-box {
    width:70%; background:white; margin:auto; padding:20px; border-radius:10px;
  }

  /* Scroll container */
  .modal-scroll {
    max-height:55vh;
    overflow-y:auto;
    padding-right:10px;
    border:1px solid #ddd;
  }
</style>
</head>
<body>

<?php include 'sidebar_admin.php'; ?>

<main class="main-content">
  <h1>Data Mahasiswa</h1>

  <!-- Tombol Tambah Banyak -->
  <button type="button" onclick="openModalJumlah()" style="background:#0a7ae9;margin-bottom:20px;">
    + Tambah Banyak Mahasiswa
  </button>

  <!-- Form Tambah Satuan -->
  <form method="POST">
    <input type="text" name="nama" placeholder="Nama Mahasiswa" required>
    <input type="text" name="nim" placeholder="NIM" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="text" name="password" placeholder="Password" required>
    <input type="text" name="kelas" placeholder="kelas" required>

    <button type="submit" name="tambah">Tambah Mahasiswa</button>
  </form>

<?php
// Tambah satuan
if (isset($_POST['tambah'])) {
  $nama     = $_POST['nama'];
  $nim      = $_POST['nim'];
  $email    = $_POST['email'];
  $password = $_POST['password'];
  $kelas    = $_POST['kelas'];

  $sql1 = "INSERT INTO mahasiswa_231051 (nama_mahasiswa_231051, nim_231051, email_231051, password_231051, kelas_231051)
           VALUES ('$nama', '$nim', '$email', '$password', '$kelas')";
  $sql2 = "INSERT INTO user_231051 (email_231051, password_231051, role_231051)
           VALUES ('$email', '$password', 'mahasiswa')";

  if ($koneksi->query($sql1) && $koneksi->query($sql2)) {
    echo "<p style='color:green;'>✅ Mahasiswa berhasil ditambahkan!</p>";
  } else {
    echo "<p style='color:red;'>❌ Gagal menambah: ".$koneksi->error."</p>";
  }
}

// Tambah banyak
if (isset($_POST['simpan_semua'])) {

  $namaList     = $_POST['nama_multi'];
  $nimList      = $_POST['nim_multi'];
  $emailList    = $_POST['email_multi'];
  $passwordList = $_POST['password_multi'];
  $kelasList    = $_POST['kelas_multi'];

  for ($i = 0; $i < count($namaList); $i++) {
    $nama     = $namaList[$i];
    $nim      = $nimList[$i];
    $email    = $emailList[$i];
    $password = $passwordList[$i];
    $kelas    = $kelasList[$i];

    $koneksi->query("INSERT INTO mahasiswa_231051 
      (nama_mahasiswa_231051, nim_231051, email_231051, password_231051, kelas_231051)
      VALUES ('$nama', '$nim', '$email', '$password', '$kelas')");

    $koneksi->query("INSERT INTO user_231051 
      (email_231051, password_231051, role_231051)
      VALUES ('$email', '$password', 'mahasiswa')");
  }

  echo "<p style='color:green;'>✅ Semua data mahasiswa berhasil ditambahkan!</p>";
}

// Hapus
if (isset($_GET['hapus'])) {
  $id = $_GET['hapus'];
  $qEmail = $koneksi->query("SELECT email_231051 FROM mahasiswa_231051 WHERE id_mahasiswa_231051='$id'");
  
  if ($qEmail && $qEmail->num_rows > 0) {
    $email = $qEmail->fetch_assoc()['email_231051'];
    $koneksi->query("DELETE FROM user_231051 WHERE email_231051='$email'");
  }

  $koneksi->query("DELETE FROM mahasiswa_231051 WHERE id_mahasiswa_231051='$id'");
  echo "<p style='color:red;'>🗑️ Data mahasiswa dihapus!</p>";
}

// Edit
if (isset($_POST['update'])) {
  $id       = $_POST['id'];
  $nama     = $_POST['nama'];
  $nim      = $_POST['nim'];
  $email    = $_POST['email'];
  $password = $_POST['password'];
  $kelas    = $_POST['kelas'];

  $sql1 = "UPDATE mahasiswa_231051 
           SET nama_mahasiswa_231051='$nama', nim_231051='$nim', email_231051='$email', password_231051='$password', kelas_231051='$kelas'
           WHERE id_mahasiswa_231051='$id'";

  $sql2 = "UPDATE user_231051 
           SET password_231051='$password'
           WHERE email_231051='$email' AND role_231051='mahasiswa'";

  if ($koneksi->query($sql1) && $koneksi->query($sql2)) {
    echo "<p style='color:green;'>✏️ Data mahasiswa diperbarui!</p>";
  } else {
    echo "<p style='color:red;'>❌ Gagal update: ".$koneksi->error."</p>";
  }
}

$res = $koneksi->query("SELECT * FROM mahasiswa_231051 ORDER BY id_mahasiswa_231051 DESC");
?>

<table>
  <tr>
    <th>No</th><th>Nama</th><th>NIM</th><th>Email</th><th>Kelas</th><th>Password</th><th>Aksi</th>
  </tr>

<?php
$no = 1;
while ($r = $res->fetch_assoc()) {
  echo "
  <tr>
    <form method='POST'>
      <td>$no</td>
      <td><input type='text' name='nama' value='{$r['nama_mahasiswa_231051']}'></td>
      <td><input type='text' name='nim' value='{$r['nim_231051']}'></td>
      <td><input type='email' name='email' value='{$r['email_231051']}'></td>
      <td><input type='text' name='kelas' value='{$r['kelas_231051']}'></td>
      <td><input type='text' name='password' value='{$r['password_231051']}'></td>
      <td>
        <input type='hidden' name='id' value='{$r['id_mahasiswa_231051']}'>
        <button type='submit' name='update' class='edit-btn'>Edit</button>
        <a href='?hapus={$r['id_mahasiswa_231051']}' class='delete-btn' style='padding:8px 12px;text-decoration:none;'>Hapus</a>
      </td>
    </form>
  </tr>";
  $no++;
}
?>
</table>

</main>

<!-- Modal input jumlah -->
<div id="modalJumlah" class="modal-bg">
  <div class="modal-box">
    <h2>Tambah Banyak Mahasiswa</h2>
    <label>Masukkan jumlah mahasiswa:</label>
    <input type="number" id="jumlahInput" placeholder="Misal: 30">
    <button onclick="buatFormBulk()">Buat Form</button>
    <button style="background:red" onclick="closeModal('modalJumlah')">Tutup</button>
  </div>
</div>

<!-- Modal Form Banyak -->
<div id="modalBulkForm" class="modal-bg">
  <div class="modal-box" style="width:80%; max-height:85vh; display:flex; flex-direction:column;">

    <h2>Form Input Banyak Mahasiswa</h2>

    <!-- FORM BENAR -->
    <form method="POST" style="display:flex; flex-direction:column; height:100%;">

      <!-- Scroll -->
      <div id="formContainer" class="modal-scroll" style="flex:1;"></div>

      <!-- Tombol -->
      <div style="margin-top:15px;">
        <button name="simpan_semua" type="submit" style="background:green;">Simpan Semua</button>
        <button type="button" onclick="closeModal('modalBulkForm')" style="background:red;">Tutup</button>
      </div>

    </form>

  </div>
</div>

<script>
function openModalJumlah(){
  document.getElementById("modalJumlah").style.display="block";
}
function closeModal(x){
  document.getElementById(x).style.display="none";
}

function buatFormBulk(){
  let jumlah = parseInt(document.getElementById("jumlahInput").value);
  if(jumlah < 1) return alert("Jumlah tidak valid!");

  let container = document.getElementById("formContainer");
  container.innerHTML = "";

  for(let i=1; i<=jumlah; i++){
    container.innerHTML += `
      <h3>Mahasiswa ${i}</h3>
      <input type="text" name="nama_multi[]" placeholder="Nama" required>
      <input type="text" name="nim_multi[]" placeholder="NIM" required>
      <input type="email" name="email_multi[]" placeholder="Email" required>
      <input type="text" name="password_multi[]" placeholder="Password" required>
      <input type="text" name="kelas_multi[]" placeholder="Kelas" required>
      <hr>
    `;
  }

  closeModal('modalJumlah');
  document.getElementById("modalBulkForm").style.display="block";
}
</script>

</body>
</html>
