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
  <title>Data Absensi</title>
  <style>
    body{font-family:'Poppins',sans-serif;background:#f0f3f7;margin:0;display:flex;}
    .sidebar{background:#0a7ae9;color:#fff;width:250px;height:100vh;padding:20px;position:fixed;}
    .main-content{margin-left:270px;padding:25px;width:100%;}

    table{width:100%;border-collapse:collapse;background:#fff;margin-top:20px;}
    th,td{border:1px solid #ddd;padding:10px;text-align:center;}
    th{background:#1976d2;color:white;}

    input,select{padding:8px;width:90%;margin:5px;border-radius:5px;border:1px solid #ccc;}
    button{background:#1976d2;color:white;padding:8px 12px;border:none;border-radius:6px;cursor:pointer;}
    .edit-btn{background:#ffa000;}
    .delete-btn{background:#e53935;}
    .cancel-btn{background:gray;}

    .alert{padding:10px;margin:10px 0;border-radius:5px;}
    .alert-success{background:#c8e6c9;color:#256029;}
    .alert-error{background:#ffcdd2;color:#b71c1c;}

    /* MODAL */
    .modal-bg{
      display:none; position:fixed; top:0; left:0;
      width:100%; height:100%; background:rgba(0,0,0,.5);
      padding-top:3%; z-index:999;
    }
    .modal-box{
      width:80%; background:white; margin:auto; padding:20px;
      border-radius:10px; max-height:85vh; overflow-y:auto;
    }
  </style>
</head>
<body>

<?php include 'sidebar_admin.php'; ?>

<main class="main-content">
    <h1>📋 Data Absensi</h1>

<?php
// ==== NOTIFIKASI ====
if (isset($_GET['status'])) {
  if ($_GET['status']=='added') echo "<div class='alert alert-success'>✅ Absensi berhasil ditambahkan!</div>";
  elseif ($_GET['status']=='updated') echo "<div class='alert alert-success'>💾 Data berhasil diperbarui!</div>";
  elseif ($_GET['status']=='deleted') echo "<div class='alert alert-error'>🗑️ Data berhasil dihapus!</div>";
  elseif ($_GET['status']=='error') echo "<div class='alert alert-error'>❌ Terjadi kesalahan!</div>";
}

// ==== MODE EDIT ====
$editMode = false;
$edit = ['id'=>'','mhs'=>'','matkul'=>'','jam'=>'','tgl'=>'','status'=>''];

if (isset($_GET['edit'])) {
  $id = intval($_GET['edit']);
  $res = $koneksi->query("SELECT * FROM absensi_231051 WHERE id_absensi_231051=$id");
  if ($res && $res->num_rows > 0) {
    $r = $res->fetch_assoc();
    $editMode = true;
    $edit = [
      'id'=>$r['id_absensi_231051'],
      'mhs'=>$r['id_mahasiswa_231051'],
      'matkul'=>$r['id_matakuliah_231051'],
      'jam'=>$r['id_jam_231051'],
      'tgl'=>$r['tanggal_231051'],
      'status'=>$r['status_231051']
    ];
  }
}

// ==== HAPUS ====
if (isset($_GET['hapus'])) {
  $id = intval($_GET['hapus']);
  $ok = $koneksi->query("DELETE FROM absensi_231051 WHERE id_absensi_231051=$id");
  header("Location: data_absensi.php?status=".($ok?"deleted":"error"));
  exit;
}

// ==== SIMPAN SATUAN ====
if (isset($_POST['simpan'])) {
  $id = $_POST['id'];
  $mhs = $_POST['id_mahasiswa'];
  $matkul = $_POST['id_matakuliah'];
  $jam = $_POST['id_jam'];
  $tgl = $_POST['tanggal'];
  $status = $_POST['status'];

  if ($id=="") {
    $sql = "INSERT INTO absensi_231051 
            (id_mahasiswa_231051,id_matakuliah_231051,id_jam_231051,tanggal_231051,status_231051)
            VALUES ('$mhs','$matkul','$jam','$tgl','$status')";
    $ok = $koneksi->query($sql);
    header("Location: data_absensi.php?status=".($ok?"added":"error"));
    exit;
  } else {
    $sql = "UPDATE absensi_231051 SET
            id_mahasiswa_231051='$mhs',
            id_matakuliah_231051='$matkul',
            id_jam_231051='$jam',
            tanggal_231051='$tgl',
            status_231051='$status'
            WHERE id_absensi_231051='$id'";
    $ok = $koneksi->query($sql);
    header("Location: data_absensi.php?status=".($ok?"updated":"error"));
    exit;
  }
}

// ==== SIMPAN BANYAK (FIX TOTAL) ====
if (isset($_POST['simpan_semua'])) {

  $mhsList = $_POST['mhs_multi'];
  $matkulList = $_POST['matkul_multi'];
  $jamList = $_POST['jam_multi'];
  $tglList = $_POST['tgl_multi'];
  $statusList = $_POST['status_multi'];

  for ($i=0; $i<count($mhsList); $i++) {
    $mhs = $mhsList[$i];
    $matkul = $matkulList[$i];
    $jam = $jamList[$i];
    $tgl = $tglList[$i];
    $status = $statusList[$i];

    $koneksi->query("
      INSERT INTO absensi_231051 
      (id_mahasiswa_231051,id_matakuliah_231051,id_jam_231051,tanggal_231051,status_231051)
      VALUES ('$mhs','$matkul','$jam','$tgl','$status')
    ");
  }

  echo "<script>alert('✅ Semua data absensi berhasil ditambahkan!'); window.location='data_absensi.php';</script>";
  exit;
}
?>

<!-- ==== TOMBOL TAMBAH BANYAK ==== -->
<button onclick="openModalJumlah()" style="background:#0a7ae9;margin-bottom:15px;">
  + Tambah Banyak Absensi
</button>

<!-- ==== FORM SATUAN ==== -->
<form method="POST">
  <input type="hidden" name="id" value="<?= $edit['id']; ?>">
  <input type="number" name="id_mahasiswa" placeholder="ID Mahasiswa" value="<?= $edit['mhs']; ?>" required>
  <input type="number" name="id_matakuliah" placeholder="ID Matakuliah" value="<?= $edit['matkul']; ?>" required>
  <input type="number" name="id_jam" placeholder="ID Jam" value="<?= $edit['jam']; ?>" required>
  <input type="date" name="tanggal" value="<?= $edit['tgl']; ?>" required>
  <select name="status" required>
    <option value="">-- Pilih Status --</option>
    <option value="Hadir" <?= $edit['status']=="Hadir"?"selected":""; ?>>Hadir</option>
    <option value="Izin" <?= $edit['status']=="Izin"?"selected":""; ?>>Izin</option>
    <option value="Alfa" <?= $edit['status']=="Alfa"?"selected":""; ?>>Alfa</option>
  </select>

  <button type="submit" name="simpan"><?= $editMode ? "💾 Simpan Perubahan" : "➕ Tambah Absensi"; ?></button>
  <?php if ($editMode): ?>
    <a href="data_absensi.php"><button type="button" class="cancel-btn">Batal</button></a>
  <?php endif; ?>
</form>

<!-- ==== TABEL DATA ==== -->
<table>
  <tr>
    <th>No</th>
    <th>ID Mhs</th>
    <th>ID Matkul</th>
    <th>ID Jam</th>
    <th>Tanggal</th>
    <th>Status</th>
    <th>Aksi</th>
  </tr>

<?php
$no=1;
$res=$koneksi->query("SELECT * FROM absensi_231051 ORDER BY id_absensi_231051 DESC");

if ($res && $res->num_rows>0) {
  while($r=$res->fetch_assoc()){
    echo "
      <tr>
        <td>$no</td>
        <td>{$r['id_mahasiswa_231051']}</td>
        <td>{$r['id_matakuliah_231051']}</td>
        <td>{$r['id_jam_231051']}</td>
        <td>{$r['tanggal_231051']}</td>
        <td>{$r['status_231051']}</td>
        <td>
          <a href='?edit={$r['id_absensi_231051']}'><button class='edit-btn'>Edit</button></a>
          <a href='?hapus={$r['id_absensi_231051']}' onclick=\"return confirm('Hapus data?');\"><button class='delete-btn'>Hapus</button></a>
        </td>
      </tr>
    ";
    $no++;
  }
} else {
  echo "<tr><td colspan='7'>Belum ada data absensi</td></tr>";
}
?>
</table>

</main>

<!-- ==== MODAL INPUT JUMLAH ==== -->
<div id="modalJumlah" class="modal-bg">
  <div class="modal-box">
    <h2>Tambah Banyak Absensi</h2>
    <label>Masukkan jumlah data absensi:</label>
    <input type="number" id="jumlahInput" placeholder="Misal: 20">
    <button onclick="buatFormBulk()">Buat Form</button>
    <button onclick="closeModal('modalJumlah')" style="background:red;">Tutup</button>
  </div>
</div>

<!-- ==== MODAL FORM BANYAK ==== -->
<div id="modalBulkForm" class="modal-bg">
  <div class="modal-box">

    <h2>Form Input Banyak Absensi</h2>

    <!-- ✅ FORM MENGAPIT SEMUA INPUT -->
    <form method="POST">

      <div id="formContainer"></div>

      <button type="submit" name="simpan_semua" style="background:green;margin-top:10px;">Simpan Semua</button>
      <button type="button" onclick="closeModal('modalBulkForm')" style="background:red;margin-top:5px;">Tutup</button>

    </form>

  </div>
</div>

<script>
function openModalJumlah(){
  document.getElementById("modalJumlah").style.display="block";
}
function closeModal(id){
  document.getElementById(id).style.display="none";
}

function buatFormBulk(){
  let jumlah = parseInt(document.getElementById("jumlahInput").value);
  if (jumlah < 1) return alert("Jumlah tidak valid!");

  let container = document.getElementById("formContainer");
  container.innerHTML = "";

  for (let i=1; i<=jumlah; i++) {
    container.innerHTML += `
      <h3>Data Absensi ${i}</h3>

      <input type="number" name="mhs_multi[]" placeholder="ID Mahasiswa" required>
      <input type="number" name="matkul_multi[]" placeholder="ID Matakuliah" required>
      <input type="number" name="jam_multi[]" placeholder="ID Jam" required>
      <input type="date" name="tgl_multi[]" required>

      <select name="status_multi[]" required>
        <option value="">-- Status --</option>
        <option value="Hadir">Hadir</option>
        <option value="Izin">Izin</option>
        <option value="Alfa">Alfa</option>
      </select>

      <hr>
    `;
  }

  closeModal('modalJumlah');
  document.getElementById("modalBulkForm").style.display="block";
}
</script>

</body>
</html>
