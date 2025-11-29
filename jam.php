<?php
session_start();
include "koneksi.php";

// Cek login admin
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// ==== FUNGSI FLASH MESSAGE (warna: green/red) ====
function setMessage($msg, $color = 'green') {
    $_SESSION['flash_message'] = "<div class='alert alert-" . ($color === 'red' ? "error" : "success") . "'>$msg</div>";
}

// ================== HANDLE HAPUS ==================
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $ok = $koneksi->query("DELETE FROM jam_231051 WHERE id_jam_231051=$id");
    header("Location: jam.php?status=" . ($ok ? "deleted" : "error"));
    exit;
}

// ================== MODE EDIT (AMBIL DATA) ==================
$editMode = false;
$edit = ['id'=>'','hari'=>'','mulai'=>'','selesai'=>''];

if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $res = $koneksi->query("SELECT * FROM jam_231051 WHERE id_jam_231051=$id");
    if ($res && $res->num_rows > 0) {
        $r = $res->fetch_assoc();
        $editMode = true;
        $edit = [
            'id'     => $r['id_jam_231051'],
            'hari'   => $r['hari_231051'],
            'mulai'  => $r['jam_mulai_231051'],
            'selesai'=> $r['jam_selesai_231051']
        ];
    }
}

// ================== SIMPAN / UPDATE SATUAN ==================
if (isset($_POST['simpan'])) {
    $id         = $_POST['id'];
    $hari       = trim($_POST['hari']);
    $jam_mulai  = $_POST['jam_mulai'];
    $jam_selesai= $_POST['jam_selesai'];

    if ($jam_mulai >= $jam_selesai) {
        header("Location: jam.php?status=jam_invalid");
        exit;
    }

    if ($id == "") {
        // Tambah
        $sql = "INSERT INTO jam_231051 (hari_231051, jam_mulai_231051, jam_selesai_231051)
                VALUES ('$hari', '$jam_mulai', '$jam_selesai')";
        $ok = $koneksi->query($sql);
        header("Location: jam.php?status=" . ($ok ? "added" : "error"));
        exit;
    } else {
        // Update
        $sql = "UPDATE jam_231051 SET 
                hari_231051='$hari',
                jam_mulai_231051='$jam_mulai',
                jam_selesai_231051='$jam_selesai'
                WHERE id_jam_231051='$id'";
        $ok = $koneksi->query($sql);
        header("Location: jam.php?status=" . ($ok ? "updated" : "error"));
        exit;
    }
}

// ================== SIMPAN BANYAK ==================
if (isset($_POST['simpan_semua'])) {
    $hariList   = isset($_POST['hari_multi']) ? $_POST['hari_multi'] : [];
    $mulaiList  = isset($_POST['mulai_multi']) ? $_POST['mulai_multi'] : [];
    $selesaiList= isset($_POST['selesai_multi']) ? $_POST['selesai_multi'] : [];

    $total = count($hariList);
    $sukses = 0; $gagal = 0; $invalid = 0;

    for ($i = 0; $i < $total; $i++) {
        $hari   = trim($koneksi->real_escape_string($hariList[$i]));
        $mulai  = $koneksi->real_escape_string($mulaiList[$i]);
        $selesai= $koneksi->real_escape_string($selesaiList[$i]);

        // Skip baris kosong agar tidak error
        if ($hari === "" || $mulai === "" || $selesai === "") { $gagal++; continue; }

        // Validasi jam
        if ($mulai >= $selesai) { $invalid++; continue; }

        $ok = $koneksi->query("
            INSERT INTO jam_231051 (hari_231051, jam_mulai_231051, jam_selesai_231051)
            VALUES ('$hari', '$mulai', '$selesai')
        ");
        if ($ok) $sukses++; else $gagal++;
    }

    // Kirim ringkasan hasil
    echo "<script>
      alert('Selesai. Berhasil: $sukses, Invalid (mulai>=selesai): $invalid, Gagal: $gagal');
      window.location='jam.php?status=bulk_done';
    </script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Data Jam</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body{font-family:'Poppins',sans-serif;background:#f0f3f7;margin:0;display:flex;}
    .sidebar{background:#0a7ae9;color:#fff;width:250px;height:100vh;padding:20px;position:fixed;}
    .sidebar ul{list-style:none;padding:0;margin:0;}
    .sidebar li{padding:12px 18px;border-radius:8px;cursor:pointer;margin-bottom:6px;}
    .sidebar li:hover{background:#1565c0;}
    .active{background:#1565c0;}
    .main-content{margin-left:270px;padding:25px;width:100%;}
    h1{margin:0 0 10px 0}
    table{width:100%;border-collapse:collapse;background:#fff;margin-top:20px;}
    th,td{border:1px solid #ddd;padding:10px;text-align:center;}
    th{background:#1976d2;color:white;}
    input,select{padding:8px;width:90%;margin:5px;border-radius:6px;border:1px solid #ccc;}
    button{background:#1976d2;color:white;padding:8px 12px;border:none;border-radius:6px;cursor:pointer;}
    button:hover{opacity:.95}
    .edit-btn{background:#ffa000;}
    .delete-btn{background:#e53935;}
    .cancel-btn{background:gray;}
    .alert{padding:10px;margin:10px 0;border-radius:6px;font-weight:600;}
    .alert-success{background:#e8f5e9;border:1px solid #66bb6a;color:#2e7d32;}
    .alert-error{background:#ffebee;border:1px solid #ef5350;color:#c62828;}

    /* Modal */
    .modal-bg{
      display:none; position:fixed; top:0; left:0;
      width:100%; height:100%; background:rgba(0,0,0,.5);
      padding-top:3%; z-index:999;
    }
    .modal-box{
      width:70%; background:white; margin:auto; padding:20px; border-radius:10px;
      box-shadow:0 10px 30px rgba(0,0,0,.2);
    }
    .modal-scroll{
      max-height:55vh; overflow-y:auto; padding-right:10px; border:1px solid #ddd; border-radius:8px;
    }
  </style>
</head>
<body>

  <!-- Sidebar -->
  <aside class="sidebar">
    <h2>Admin Panel</h2>
    <ul>
      <li onclick="location.href='dashboardAdmin.php'">Dashboard</li>
      <li onclick="location.href='data_dosen.php'">Data Dosen</li>
      <li onclick="location.href='data_mahasiswa.php'">Data Mahasiswa</li>
      <li onclick="location.href='data_matkul.php'">Mata Kuliah</li>
      <li onclick="location.href='data_kelas.php'">Data Kelas</li>
      <li class="active" onclick="location.href='jam.php'">Data Jam</li>
      <li onclick="location.href='data_absensi.php'">Data Absensi</li>
      <li onclick="location.href='logout.php'">Logout</li>
    </ul>
  </aside>

  <!-- Main -->
  <main class="main-content">
    <h1>🕒 Data Jam Kuliah</h1>

    <!-- Notifikasi via ?status= -->
    <?php
      if (isset($_GET['status'])) {
        if ($_GET['status']=='added')   echo "<div class='alert alert-success'>✅ Data jam berhasil ditambahkan!</div>";
        elseif ($_GET['status']=='updated') echo "<div class='alert alert-success'>💾 Data jam berhasil diperbarui!</div>";
        elseif ($_GET['status']=='deleted') echo "<div class='alert alert-error'>🗑️ Data jam berhasil dihapus!</div>";
        elseif ($_GET['status']=='error')   echo "<div class='alert alert-error'>❌ Terjadi kesalahan!</div>";
        elseif ($_GET['status']=='jam_invalid') echo "<div class='alert alert-error'>❌ Jam selesai harus lebih besar dari jam mulai!</div>";
        elseif ($_GET['status']=='bulk_done') echo "<div class='alert alert-success'>✅ Proses tambah banyak selesai. Cek ringkasannya di alert sebelumnya.</div>";
      }
      // Flash message (jika menggunakan setMessage di masa depan)
      if (isset($_SESSION['flash_message'])) {
        echo $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
      }
    ?>

    <!-- Tombol Tambah Banyak -->
    <button onclick="openModalJumlah()" style="background:#0a7ae9;margin-bottom:15px;">
      + Tambah Banyak Jam
    </button>

    <!-- Form Satuan -->
    <form method="POST">
      <input type="hidden" name="id" value="<?php echo $edit['id']; ?>">
      <input type="text"  name="hari"       placeholder="Hari (contoh: Senin)" value="<?php echo htmlspecialchars($edit['hari']); ?>" required>
      <input type="time" name="jam_mulai" 
       value="<?php echo $edit['mulai'] ? date('H:i', strtotime($edit['mulai'])) : ''; ?>" required>

      <input type="time" name="jam_selesai"
       value="<?php echo $edit['selesai'] ? date('H:i', strtotime($edit['selesai'])) : ''; ?>" required>

      <button type="submit" name="simpan"><?php echo $editMode ? "💾 Simpan Perubahan" : "➕ Tambah Jam"; ?></button>
      <?php if ($editMode): ?>
        <a href="jam.php"><button type="button" class="cancel-btn">Batal</button></a>
      <?php endif; ?>
    </form>

    <!-- Tabel Data -->
    <table>
  <tr>
    <th>No</th>
    <th>Hari</th>
    <th>Jam Mulai</th>
    <th>Jam Selesai</th>
    <th>Aksi</th>
  </tr>
  <?php
    $no = 1;
    $result = $koneksi->query("SELECT * FROM jam_231051 ORDER BY id_jam_231051 ASC");
    if ($result && $result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {

        // Format jam menjadi H:i (tanpa detik)
        $jamMulai   = date("H:i", strtotime($row['jam_mulai_231051']));
        $jamSelesai = date("H:i", strtotime($row['jam_selesai_231051']));

        echo "<tr>
          <td>$no</td>
          <td>".htmlspecialchars($row['hari_231051'])."</td>
          <td>$jamMulai</td>
          <td>$jamSelesai</td>
          <td>
            <a href='?edit={$row['id_jam_231051']}'><button class='edit-btn'>Edit</button></a>
            <a href='?hapus={$row['id_jam_231051']}' onclick=\"return confirm('Yakin ingin menghapus data ini?');\">
              <button class='delete-btn'>Hapus</button>
            </a>
          </td>
        </tr>";

        $no++;
      }
    } else {
      echo "<tr><td colspan='5'>Belum ada data jam.</td></tr>";
    }
  ?>
</table>

  </main>

  <!-- MODAL: Input jumlah baris -->
  <div id="modalJumlah" class="modal-bg">
    <div class="modal-box">
      <h2>Tambah Banyak Jam</h2>
      <label>Masukkan jumlah baris:</label>
      <input type="number" id="jumlahInput" placeholder="Misal: 10" min="1">
      <button onclick="buatFormBulk()">Buat Form</button>
      <button onclick="closeModal('modalJumlah')" style="background:#e53935;margin-left:8px;">Tutup</button>
    </div>
  </div>

  <!-- MODAL: Form banyak -->
  <div id="modalBulkForm" class="modal-bg">
    <div class="modal-box" style="width:80%;max-height:85vh;display:flex;flex-direction:column;">
      <h2>Form Input Banyak Jam</h2>
      <form method="POST">
        <div id="formContainer" class="modal-scroll"></div>
        <button type="submit" name="simpan_semua" style="background:green;margin-top:10px;">Simpan Semua</button>
        <button type="button" onclick="closeModal('modalBulkForm')" style="background:#e53935;margin-top:6px;">Tutup</button>
      </form>
    </div>
  </div>

  <script>
    function openModalJumlah(){ document.getElementById("modalJumlah").style.display="block"; }
    function closeModal(id){ document.getElementById(id).style.display="none"; }
    function buatFormBulk(){
      const jumlah = parseInt(document.getElementById("jumlahInput").value);
      if (!jumlah || jumlah < 1) return alert("Jumlah tidak valid!");

      const container = document.getElementById("formContainer");
      container.innerHTML = "";

      let html = "";
      for (let i=1; i<=jumlah; i++){
        html += `
          <div style="padding:10px 12px">
            <h3 style="margin:8px 0 6px 0">Baris ${i}</h3>
            <input type="text"  name="hari_multi[]"    placeholder="Hari (misal: Senin)" required>
            <input type="time"  name="mulai_multi[]"   required>
            <input type="time"  name="selesai_multi[]" required>
            <hr>
          </div>
        `;
      }
      container.innerHTML = html;

      closeModal('modalJumlah');
      document.getElementById("modalBulkForm").style.display="block";
    }
  </script>
</body>
</html>
