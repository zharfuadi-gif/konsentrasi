<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') { 
  header("Location: index.php"); 
  exit; 
}
include "koneksi.php";

// === HANDLE HAPUS ===
if (isset($_GET['hapus'])) {
  $hapusID = $_GET['hapus'];
  $koneksi->query("DELETE FROM kelas_231051 WHERE id_kelas_231051='$hapusID'");
  echo "<script>alert('✅ Data kelas berhasil dihapus!'); window.location='data_kelas.php';</script>";
  exit;
}

// === HANDLE SIMPAN SATUAN / UPDATE ===
$editMode = false;
$editID = "";
$editNama = "";
$editTahun = "";

if (isset($_GET['edit'])) {
  $editID = $_GET['edit'];
  $res = $koneksi->query("SELECT * FROM kelas_231051 WHERE id_kelas_231051='$editID'");
  if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    $editNama = $row['nama_kelas_231051'];
    $editTahun = $row['tahun_ajaran_231051'];
    $editMode = true;
  }
}

if (isset($_POST['simpan'])) {
  $nama = $_POST['nama'];
  $tahun = $_POST['tahun'];
  $id = $_POST['id'];

  if ($id == "") {
    // Tambah baru
    $sql = "INSERT INTO kelas_231051 (nama_kelas_231051, tahun_ajaran_231051)
            VALUES ('$nama', '$tahun')";
    $koneksi->query($sql);
    echo "<script>alert('✅ Kelas berhasil ditambahkan!'); window.location='data_kelas.php';</script>";
    exit;
  } else {
    // Update
    $sql = "UPDATE kelas_231051 
            SET nama_kelas_231051='$nama', tahun_ajaran_231051='$tahun'
            WHERE id_kelas_231051='$id'";
    $koneksi->query($sql);
    echo "<script>alert('✅ Data kelas berhasil diperbarui!'); window.location='data_kelas.php';</script>";
    exit;
  }
}

// === HANDLE TAMBAH BANYAK KELAS ===
if (isset($_POST['simpan_semua'])) {

  $namaList  = $_POST['nama_multi'];
  $tahunList = $_POST['tahun_multi'];

  for ($i = 0; $i < count($namaList); $i++) {
    $nama  = $koneksi->real_escape_string($namaList[$i]);
    $tahun = $koneksi->real_escape_string($tahunList[$i]);

    $koneksi->query("
      INSERT INTO kelas_231051 (nama_kelas_231051, tahun_ajaran_231051)
      VALUES ('$nama', '$tahun')
    ");
  }

  echo "<script>alert('✅ Semua data kelas berhasil ditambahkan!'); window.location='data_kelas.php';</script>";
  exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Data Kelas</title>
  <style>
    body{font-family:'Poppins',sans-serif;background:#f0f3f7;margin:0;display:flex;}
    .sidebar{background:#0a7ae9;color:#fff;width:250px;height:100vh;padding:20px;position:fixed;}
    .sidebar ul{list-style:none;padding:0;}
    .sidebar li{padding:12px 18px;border-radius:8px;cursor:pointer;}
    .sidebar li:hover{background:#1565c0;}
    .main-content{margin-left:270px;padding:25px;width:100%;}

    table{width:100%;border-collapse:collapse;background:#fff;margin-top:20px;}
    th,td{border:1px solid #ddd;padding:10px;text-align:center;}
    th{background:#1976d2;color:white;}

    input{padding:8px;width:90%;margin:5px;border-radius:5px;border:1px solid #ccc;}
    button{background:#1976d2;color:white;padding:8px 12px;border:none;border-radius:5px;cursor:pointer;}
    .edit-btn{background:#ffa000!important;}
    .delete-btn{background:#e53935!important;}

    /* MODAL */
    .modal-bg{
      display:none; position:fixed; top:0; left:0;
      width:100%; height:100%; background:rgba(0,0,0,.5);
      padding-top:3%; z-index:999;
    }
    .modal-box{
      width:70%; background:white; margin:auto; padding:20px; border-radius:10px;
    }
    .modal-scroll{
      max-height:55vh; overflow-y:auto; padding-right:10px; border:1px solid #ddd;
    }
  </style>
</head>
<body>

<?php include 'sidebar_admin.php'; ?>

<main class="main-content">
  <h1>Data Kelas</h1>

  <!-- Tombol Tambah Banyak -->
  <button onclick="openModalJumlah()" style="background:#0a7ae9;margin-bottom:15px;">
    + Tambah Banyak Kelas
  </button>

  <!-- Form Tambah / Edit -->
  <form method="POST">
    <input type="hidden" name="id" value="<?php echo $editID; ?>">
    <input type="text" name="nama" placeholder="Nama Kelas" value="<?php echo $editNama; ?>" required>
    <input type="text" name="tahun" placeholder="Tahun Ajaran" value="<?php echo $editTahun; ?>" required>

    <button type="submit" name="simpan">
      <?php echo $editMode ? "💾 Update Kelas" : "➕ Tambah Kelas"; ?>
    </button>

    <?php if ($editMode): ?>
      <a href="data_kelas.php"><button type="button">Batal</button></a>
    <?php endif; ?>
  </form>

  <!-- Tabel Data -->
  <table>
    <tr>
      <th>No</th>
      <th>Nama Kelas</th>
      <th>Tahun Ajaran</th>
      <th>Aksi</th>
    </tr>
    <?php
    $no=1;
    $res=$koneksi->query("SELECT * FROM kelas_231051 ORDER BY id_kelas_231051 DESC");

    if ($res && $res->num_rows > 0) {
      while($r=$res->fetch_assoc()){
        echo "
        <tr>
          <td>$no</td>
          <td>{$r['nama_kelas_231051']}</td>
          <td>{$r['tahun_ajaran_231051']}</td>
          <td>
            <a href='?edit={$r['id_kelas_231051']}'><button class='edit-btn'>Edit</button></a>
            <a href='?hapus={$r['id_kelas_231051']}' onclick=\"return confirm('Yakin ingin hapus kelas ini?');\">
              <button class='delete-btn'>Hapus</button>
            </a>
          </td>
        </tr>";
        $no++;
      }
    } else {
      echo "<tr><td colspan='4'>Belum ada data kelas.</td></tr>";
    }
    ?>
  </table>

</main>

<!-- MODAL INPUT JUMLAH -->
<div id="modalJumlah" class="modal-bg">
  <div class="modal-box">
    <h2>Tambah Banyak Kelas</h2>
    <label>Masukkan jumlah kelas:</label>
    <input type="number" id="jumlahInput" placeholder="Misal: 10">
    <button onclick="buatFormBulk()">Buat Form</button>
    <button onclick="closeModal('modalJumlah')" style="background:red;">Tutup</button>
  </div>
</div>

<!-- MODAL FORM BANYAK -->
<div id="modalBulkForm" class="modal-bg">
  <div class="modal-box" style="width:80%;max-height:85vh;display:flex;flex-direction:column;">
    
    <h2>Form Input Banyak Kelas</h2>

    <form method="POST">
      <div id="formContainer" class="modal-scroll"></div>

      <button type="submit" name="simpan_semua" style="background:green;margin-top:10px;">Simpan Semua</button>
      <button type="button" onclick="closeModal('modalBulkForm')" style="background:red;margin-top:5px;">Tutup</button>
    </form>

  </div>
</div>

<script>
function openModalJumlah(){
  document.getElementById("modalJumlah").style.display = "block";
}
function closeModal(id){
  document.getElementById(id).style.display = "none";
}

function buatFormBulk(){
  let jumlah = parseInt(document.getElementById("jumlahInput").value);
  if (jumlah < 1) return alert("Jumlah tidak valid!");

  let container = document.getElementById("formContainer");
  container.innerHTML = "";

  for (let i = 1; i <= jumlah; i++) {
    container.innerHTML += `
      <h3>Kelas ${i}</h3>
      <input type="text" name="nama_multi[]" placeholder="Nama Kelas" required>
      <input type="text" name="tahun_multi[]" placeholder="Tahun Ajaran" required>
      <hr>
    `;
  }

  closeModal('modalJumlah');
  document.getElementById("modalBulkForm").style.display = "block";
}
</script>

</body>
</html>
