<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
  header("Location: index.php");
  exit;
}
include "koneksi.php";

// Handle assignment of students to dosen
if (isset($_POST['assign_students'])) {
  $id_dosen = $_POST['id_dosen'];
  $selected_students = $_POST['students'] ?? [];
  
  if (!empty($selected_students)) {
    foreach ($selected_students as $id_mahasiswa) {
      // Check if already assigned
      $check = $koneksi->query("SELECT * FROM dosen_mahasiswa_231051 
                               WHERE id_dosen_231051='$id_dosen' 
                               AND id_mahasiswa_231051='$id_mahasiswa'");
      
      if ($check->num_rows == 0) {
        $koneksi->query("INSERT INTO dosen_mahasiswa_231051 
                        (id_dosen_231051, id_mahasiswa_231051) 
                        VALUES ('$id_dosen', '$id_mahasiswa')");
      }
    }
    echo "<script>alert('✅ Mahasiswa berhasil ditambahkan ke dosen!'); window.location='assign_mahasiswa_dosen.php';</script>";
    exit;
  }
}

// Handle removal of student from dosen
if (isset($_GET['remove'])) {
  $id = $_GET['remove'];
  $koneksi->query("DELETE FROM dosen_mahasiswa_231051 WHERE id_dm_231051='$id'");
  echo "<script>alert('🗑️ Mahasiswa dihapus dari dosen!'); window.location='assign_mahasiswa_dosen.php';</script>";
  exit;
}

// Get all dosen
$dosen_list = $koneksi->query("SELECT * FROM dosen_231051 ORDER BY nama_dosen_231051");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Assign Mahasiswa ke Dosen - Admin</title>
<style>
  body {font-family:'Poppins',sans-serif;background:#f0f3f7;margin:0;display:flex;}
  .sidebar {background:#0a7ae9;color:#fff;width:250px;height:100vh;padding:20px;position:fixed;}
  .main-content{margin-left:270px;padding:25px;width:100%;}
  
  .dosen-card {
    background:#fff;
    padding:20px;
    margin-bottom:20px;
    border-radius:10px;
    box-shadow:0 2px 10px rgba(0,0,0,0.1);
  }
  
  .dosen-header {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:15px;
    padding-bottom:15px;
    border-bottom:2px solid #e0e0e0;
  }
  
  .dosen-header h3 {margin:0;color:#1976d2;}
  .dosen-info {color:#666;font-size:14px;}
  
  .student-list {
    display:grid;
    grid-template-columns:repeat(auto-fill, minmax(250px, 1fr));
    gap:10px;
    margin-top:15px;
  }
  
  .student-item {
    background:#f5f5f5;
    padding:10px;
    border-radius:6px;
    display:flex;
    justify-content:space-between;
    align-items:center;
  }
  
  .student-item .name {font-weight:500;}
  .student-item .nim {font-size:12px;color:#666;}
  
  .remove-btn {
    background:#e53935;
    color:white;
    border:none;
    padding:5px 10px;
    border-radius:4px;
    cursor:pointer;
    font-size:12px;
  }
  
  .add-btn {
    background:#43a047;
    color:white;
    border:none;
    padding:8px 16px;
    border-radius:6px;
    cursor:pointer;
  }
  
  .add-btn:hover {background:#2e7d32;}
  
  /* Modal */
  .modal {
    display:none;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.5);
    z-index:999;
  }
  
  .modal-content {
    background:white;
    width:600px;
    max-height:80vh;
    margin:50px auto;
    padding:25px;
    border-radius:10px;
    overflow-y:auto;
  }
  
  .modal-header {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
    padding-bottom:15px;
    border-bottom:2px solid #e0e0e0;
  }
  
  .close-btn {
    background:none;
    border:none;
    font-size:24px;
    cursor:pointer;
  }
  
  .checkbox-list {
    max-height:400px;
    overflow-y:auto;
  }
  
  .checkbox-item {
    padding:10px;
    border-bottom:1px solid #eee;
    display:flex;
    align-items:center;
  }
  
  .checkbox-item input {
    margin-right:10px;
  }
  
  .checkbox-item label {
    cursor:pointer;
    flex:1;
  }
  
  .save-btn {
    background:#1976d2;
    color:white;
    border:none;
    padding:10px 20px;
    border-radius:6px;
    cursor:pointer;
    margin-top:15px;
  }
  
  .stats {
    display:inline-block;
    background:#e3f2fd;
    color:#1976d2;
    padding:5px 12px;
    border-radius:20px;
    font-size:14px;
    font-weight:600;
  }
  
  .absence-count {
    background:#ffebee;
    color:#c62828;
    padding:5px 12px;
    border-radius:20px;
    font-size:14px;
    font-weight:600;
    margin-left:10px;
  }
</style>
</head>
<body>

<?php include 'sidebar_admin.php'; ?>

<main class="main-content">
  <h1>👥 Assign Mahasiswa ke Dosen</h1>
  <p>Kelola penugasan mahasiswa untuk setiap dosen dan lihat statistik ketidakhadiran.</p>

  <?php while ($dosen = $dosen_list->fetch_assoc()): ?>
  
  <div class="dosen-card">
    <div class="dosen-header">
      <div>
        <h3><?= htmlspecialchars($dosen['nama_dosen_231051']) ?></h3>
        <div class="dosen-info">
          NIP: <?= htmlspecialchars($dosen['nip_231051']) ?> | 
          Email: <?= htmlspecialchars($dosen['email_231051']) ?>
        </div>
      </div>
      <button class="add-btn" onclick="openModal(<?= $dosen['id_dosen_231051'] ?>, '<?= htmlspecialchars($dosen['nama_dosen_231051']) ?>')">
        + Tambah Mahasiswa
      </button>
    </div>
    
    <?php
    // Get assigned students for this dosen
    $assigned = $koneksi->query("
      SELECT 
        dm.id_dm_231051,
        m.id_mahasiswa_231051,
        m.nama_mahasiswa_231051,
        m.nim_231051,
        m.kelas_231051,
        (SELECT COUNT(*) FROM absensi_231051 
         WHERE id_mahasiswa_231051 = m.id_mahasiswa_231051 
         AND status_231051 = 'Alfa') as alfa_count
      FROM dosen_mahasiswa_231051 dm
      JOIN mahasiswa_231051 m ON dm.id_mahasiswa_231051 = m.id_mahasiswa_231051
      WHERE dm.id_dosen_231051 = '{$dosen['id_dosen_231051']}'
      ORDER BY m.nama_mahasiswa_231051
    ");
    
    $total_students = $assigned->num_rows;
    $total_absent = 0;
    ?>
    
    <div style="margin-bottom:10px;">
      <span class="stats">Total Mahasiswa: <?= $total_students ?></span>
      <?php
      // Calculate total absent
      $assigned_temp = $koneksi->query("
        SELECT SUM(
          (SELECT COUNT(*) FROM absensi_231051 
           WHERE id_mahasiswa_231051 = m.id_mahasiswa_231051 
           AND status_231051 = 'Alfa')
        ) as total_alfa
        FROM dosen_mahasiswa_231051 dm
        JOIN mahasiswa_231051 m ON dm.id_mahasiswa_231051 = m.id_mahasiswa_231051
        WHERE dm.id_dosen_231051 = '{$dosen['id_dosen_231051']}'
      ");
      $total_alfa_data = $assigned_temp->fetch_assoc();
      $total_absent = $total_alfa_data['total_alfa'] ?? 0;
      ?>
      <span class="absence-count">Total Tidak Hadir (Alfa): <?= $total_absent ?></span>
    </div>
    
    <?php if ($total_students > 0): ?>
    <div class="student-list">
      <?php while ($student = $assigned->fetch_assoc()): ?>
      <div class="student-item">
        <div>
          <div class="name"><?= htmlspecialchars($student['nama_mahasiswa_231051']) ?></div>
          <div class="nim">NIM: <?= htmlspecialchars($student['nim_231051']) ?> | Kelas: <?= htmlspecialchars($student['kelas_231051']) ?></div>
          <div class="nim" style="color:#c62828;font-weight:600;">Alfa: <?= $student['alfa_count'] ?> kali</div>
        </div>
        <a href="?remove=<?= $student['id_dm_231051'] ?>" 
           onclick="return confirm('Hapus mahasiswa ini dari dosen?')">
          <button class="remove-btn">Hapus</button>
        </a>
      </div>
      <?php endwhile; ?>
    </div>
    <?php else: ?>
    <p style="color:#999;font-style:italic;">Belum ada mahasiswa yang ditugaskan</p>
    <?php endif; ?>
  </div>
  
  <?php endwhile; ?>
</main>

<!-- Modal for adding students -->
<div id="assignModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2 id="modalTitle">Tambah Mahasiswa ke Dosen</h2>
      <button class="close-btn" onclick="closeModal()">&times;</button>
    </div>
    
    <form method="POST" id="assignForm">
      <input type="hidden" name="id_dosen" id="dosenId">
      
      <div class="checkbox-list">
        <?php
        // Get all students
        $all_students = $koneksi->query("SELECT * FROM mahasiswa_231051 ORDER BY nama_mahasiswa_231051");
        while ($mhs = $all_students->fetch_assoc()):
        ?>
        <div class="checkbox-item">
          <input type="checkbox" 
                 name="students[]" 
                 value="<?= $mhs['id_mahasiswa_231051'] ?>" 
                 id="student_<?= $mhs['id_mahasiswa_231051'] ?>">
          <label for="student_<?= $mhs['id_mahasiswa_231051'] ?>">
            <strong><?= htmlspecialchars($mhs['nama_mahasiswa_231051']) ?></strong><br>
            <small>NIM: <?= htmlspecialchars($mhs['nim_231051']) ?> | Kelas: <?= htmlspecialchars($mhs['kelas_231051']) ?></small>
          </label>
        </div>
        <?php endwhile; ?>
      </div>
      
      <button type="submit" name="assign_students" class="save-btn">Simpan Penugasan</button>
    </form>
  </div>
</div>

<script>
function openModal(dosenId, dosenName) {
  document.getElementById('dosenId').value = dosenId;
  document.getElementById('modalTitle').textContent = 'Tambah Mahasiswa ke ' + dosenName;
  document.getElementById('assignModal').style.display = 'block';
  
  // Uncheck all checkboxes
  document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
}

function closeModal() {
  document.getElementById('assignModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
  const modal = document.getElementById('assignModal');
  if (event.target == modal) {
    closeModal();
  }
}
</script>

</body>
</html>
