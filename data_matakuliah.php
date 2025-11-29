<?php
session_start();

// Pastikan user sudah login dan role admin
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
  header("Location: index.php");
  exit;
}

// === KONEKSI DATABASE ===
$koneksi = new mysqli("localhost", "root", "", "absensiqr_231051");

// Cek koneksi
if ($koneksi->connect_error) {
  die("Koneksi gagal: " . $koneksi->connect_error);
}

// === TAMBAH SATUAN ===
if (isset($_POST['tambah'])) {
  $nama = $koneksi->real_escape_string($_POST['nama']);
  $kode = $koneksi->real_escape_string($_POST['kode']);
  $id_dosen = $koneksi->real_escape_string($_POST['id_dosen']);
  $id_kelas = $koneksi->real_escape_string($_POST['id_kelas']);
  $id_jam   = $koneksi->real_escape_string($_POST['id_jam']);

  $sql = "INSERT INTO matakuliah_231051 
          (nama_matakuliah_231051, kode_matakuliah_231051, id_dosen_231051, id_kelas_231051, id_jam_231051)
          VALUES ('$nama', '$kode', '$id_dosen', '$id_kelas', '$id_jam')";

  if ($koneksi->query($sql)) {
    echo "<script>alert('✅ Data mata kuliah berhasil ditambahkan!'); window.location='".$_SERVER['PHP_SELF']."';</script>";
  } else {
    echo "<script>alert('❌ Gagal menambah data: " . $koneksi->error . "');</script>";
  }
}

// === TAMBAH BANYAK DATA ===
if (isset($_POST['simpan_semua'])) {

  $namaList  = $_POST['nama_multi'];
  $kodeList  = $_POST['kode_multi'];
  $dosenList = $_POST['dosen_multi'];
  $kelasList = $_POST['kelas_multi'];
  $jamList   = $_POST['jam_multi'];

  for ($i = 0; $i < count($namaList); $i++) {
    $nama  = $koneksi->real_escape_string($namaList[$i]);
    $kode  = $koneksi->real_escape_string($kodeList[$i]);
    $dosen = $koneksi->real_escape_string($dosenList[$i]);
    $kelas = $koneksi->real_escape_string($kelasList[$i]);
    $jam   = $koneksi->real_escape_string($jamList[$i]);

    $koneksi->query("
      INSERT INTO matakuliah_231051 
      (nama_matakuliah_231051, kode_matakuliah_231051, id_dosen_231051, id_kelas_231051, id_jam_231051)
      VALUES ('$nama', '$kode', '$dosen', '$kelas', '$jam')
    ");
  }

  echo "<script>alert('✅ Semua data mata kuliah berhasil ditambahkan!'); window.location='".$_SERVER['PHP_SELF']."';</script>";
}

// === HAPUS DATA ===
if (isset($_GET['hapus'])) {
  $id = intval($_GET['hapus']);
  $koneksi->query("DELETE FROM matakuliah_231051 WHERE id_matakuliah_231051 = $id");
  echo "<script>alert('🗑️ Data berhasil dihapus!'); window.location='".$_SERVER['PHP_SELF']."';</script>";
}

// === EDIT DATA ===
if (isset($_POST['update'])) {
  $id   = intval($_POST['id']);
  $nama = $koneksi->real_escape_string($_POST['nama']);
  $kode = $koneksi->real_escape_string($_POST['kode']);
  $id_dosen = $koneksi->real_escape_string($_POST['id_dosen']);
  $id_kelas = $koneksi->real_escape_string($_POST['id_kelas']);
  $id_jam   = $koneksi->real_escape_string($_POST['id_jam']);

  $sql = "UPDATE matakuliah_231051 SET
          nama_matakuliah_231051='$nama',
          kode_matakuliah_231051='$kode',
          id_dosen_231051='$id_dosen',
          id_kelas_231051='$id_kelas',
          id_jam_231051='$id_jam'
          WHERE id_matakuliah_231051=$id";

  if ($koneksi->query($sql)) {
    echo "<script>alert('✅ Data berhasil diperbarui!'); window.location='".$_SERVER['PHP_SELF']."';</script>";
  } else {
    echo "<script>alert('❌ Gagal update data: " . $koneksi->error . "');</script>";
  }
}

// === AMBIL DATA UNTUK EDIT ===
$editData = null;
if (isset($_GET['edit'])) {
  $id = intval($_GET['edit']);
  $result = $koneksi->query("SELECT * FROM matakuliah_231051 WHERE id_matakuliah_231051=$id");
  $editData = $result->fetch_assoc();
}

// === TAMPILKAN DATA ===
$res = $koneksi->query("SELECT * FROM matakuliah_231051 ORDER BY id_matakuliah_231051 DESC");

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Data Mata Kuliah</title>

<style>
  body {font-family:'Poppins',sans-serif;background:#f0f3f7;margin:0;display:flex;}
  .sidebar {background:#0a7ae9;color:#fff;width:250px;height:100vh;padding:20px;position:fixed;}
  .main-content {margin-left:270px;padding:25px;width:100%;}

  table {width:100%;border-collapse:collapse;background:#fff;margin-top:20px;}
  th,td {border:1px solid #ddd;padding:10px;text-align:center;}
  th {background:#1976d2;color:white;}

  input {padding:8px;width:90%;margin:5px;border-radius:5px;border:1px solid #ccc;}
  button {background:#1976d2;color:white;padding:8px 12px;border:none;border-radius:6px;cursor:pointer;}
  button:hover {background:#1259a5;}

  .edit-form {background:#fff;padding:15px;border-radius:10px;margin-top:15px;box-shadow:0 2px 6px rgba(0,0,0,0.1);}
  .modal-bg {display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.5);padding-top:5%;z-index:999;}
  .modal-box {width:70%;background:white;margin:auto;padding:20px;border-radius:10px;}
  .modal-scroll {max-height:55vh;overflow-y:auto;padding-right:10px;border:1px solid #ddd;}
</style>

</head>
<body>

<?php include 'sidebar_admin.php'; ?>

<main class="main-content">

  <h1>📚 Data Mata Kuliah</h1>

  <button onclick="openModalJumlah()" style="background:#0a7ae9;margin-bottom:20px;">+ Tambah Banyak Mata Kuliah</button>

  <!-- FORM TAMBAH / EDIT -->
  <div class="edit-form">
    <form method="POST">
      <?php if ($editData): ?>
        <input type="hidden" name="id" value="<?= $editData['id_matakuliah_231051'] ?>">
      <?php endif; ?>

      <input type="text" name="nama" placeholder="Nama Mata Kuliah" required value="<?= $editData['nama_matakuliah_231051'] ?? '' ?>">
      <input type="text" name="kode" placeholder="Kode Matkul" required value="<?= $editData['kode_matakuliah_231051'] ?? '' ?>">
      <input type="text" name="id_dosen" placeholder="ID Dosen" required value="<?= $editData['id_dosen_231051'] ?? '' ?>">
      <input type="text" name="id_kelas" placeholder="ID Kelas" required value="<?= $editData['id_kelas_231051'] ?? '' ?>">
      <input type="text" name="id_jam" placeholder="ID Jam" required value="<?= $editData['id_jam_231051'] ?? '' ?>">

      <?php if ($editData): ?>
        <button type="submit" name="update">💾 Simpan Perubahan</button>
        <a href="<?= $_SERVER['PHP_SELF'] ?>"><button type="button">❌ Batal</button></a>
      <?php else: ?>
        <button type="submit" name="tambah">➕ Tambah Matkul</button>
      <?php endif; ?>
    </form>
  </div>

  <!-- TABEL DATA -->
  <table>
    <tr>
      <th>No</th>
      <th>Nama</th>
      <th>Kode</th>
      <th>ID Dosen</th>
      <th>ID Kelas</th>
      <th>ID Jam</th>
      <th>Aksi</th>
    </tr>

    <?php
    if ($res->num_rows > 0) {
      $no = 1;
      while ($r = $res->fetch_assoc()) {
        echo "
        <tr>
          <td>$no</td>
          <td>{$r['nama_matakuliah_231051']}</td>
          <td>{$r['kode_matakuliah_231051']}</td>
          <td>{$r['id_dosen_231051']}</td>
          <td>{$r['id_kelas_231051']}</td>
          <td>{$r['id_jam_231051']}</td>
          <td>
            <a href='?edit={$r['id_matakuliah_231051']}'><button>✏️ Edit</button></a>
            <a href='?hapus={$r['id_matakuliah_231051']}' onclick=\"return confirm('Yakin ingin menghapus?');\"><button style='background:red;'>🗑️ Hapus</button></a>
          </td>
        </tr>
        ";
        $no++;
      }
    } else {
      echo "<tr><td colspan='7'>Belum ada data mata kuliah.</td></tr>";
    }
    ?>
  </table>

</main>

<!-- MODAL MASUKKAN JUMLAH -->
<div id="modalJumlah" class="modal-bg">
  <div class="modal-box">
    <h2>Tambah Banyak Mata Kuliah</h2>
    <label>Masukkan jumlah matkul:</label>
    <input type="number" id="jumlahInput" placeholder="Misal: 10">
    <button onclick="buatFormBulk()">Buat Form</button>
    <button onclick="closeModal('modalJumlah')" style="background:red;">Tutup</button>
  </div>
</div>

<!-- MODAL FORM BANYAK -->
<div id="modalBulkForm" class="modal-bg">
  <div class="modal-box" style="width:80%;max-height:85vh;display:flex;flex-direction:column;">

    <h2>Form Input Banyak Mata Kuliah</h2>

    <form method="POST" style="display:flex;flex-direction:column;height:100%;">
      <div id="formContainer" class="modal-scroll" style="flex:1;"></div>

      <div style="margin-top:15px;">
        <button type="submit" name="simpan_semua" style="background:green;">Simpan Semua</button>
        <button type="button" onclick="closeModal('modalBulkForm')" style="background:red;">Tutup</button>
      </div>
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
  if(jumlah < 1) return alert("Jumlah tidak valid!");

  let container = document.getElementById("formContainer");
  container.innerHTML = "";

  for(let i=1; i<=jumlah; i++){
    container.innerHTML += `
      <h3>Mata Kuliah ${i}</h3>
      <input type="text" name="nama_multi[]" placeholder="Nama Matkul" required>
      <input type="text" name="kode_multi[]" placeholder="Kode Matkul" required>
      <input type="text" name="dosen_multi[]" placeholder="ID Dosen" required>
      <input type="text" name="kelas_multi[]" placeholder="ID Kelas" required>
      <input type="text" name="jam_multi[]" placeholder="ID Jam" required>
      <hr>
    `;
  }

  closeModal('modalJumlah');
  document.getElementById("modalBulkForm").style.display="block";
}
</script>

</body>
</html>
