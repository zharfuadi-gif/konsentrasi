<?php
session_start();

// Set header to prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnosa Lengkap - Dashboard Dosen</title>
    <style>
        body { font-family: 'Courier New', monospace; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        h1 { color: #1a237e; }
        h2 { color: #3949ab; border-bottom: 2px solid #3949ab; padding-bottom: 5px; }
        pre { background: #f0f0f0; padding: 10px; border-radius: 5px; overflow-x: auto; }
        .btn { display: inline-block; padding: 10px 20px; background: #1a237e; color: white; 
               text-decoration: none; border-radius: 5px; margin: 5px; }
        .btn:hover { background: #3949ab; }
    </style>
</head>
<body>

<h1>🔍 DIAGNOSA LENGKAP DASHBOARD DOSEN</h1>

<!-- 1. INFO SERVER -->
<div class="container">
    <h2>1️⃣ Informasi Server</h2>
    <pre><?php
    echo "PHP Version: " . phpversion() . "\n";
    echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
    echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "\n";
    echo "Script Filename: " . __FILE__ . "\n";
    echo "Request Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
    echo "Request URI: " . $_SERVER['REQUEST_URI'] . "\n";
    echo "HTTP Host: " . $_SERVER['HTTP_HOST'] . "\n";
    ?></pre>
</div>

<!-- 2. CEK SESSION -->
<div class="container">
    <h2>2️⃣ Status Session</h2>
    <?php if (empty($_SESSION)): ?>
        <p class="error">❌ SESSION KOSONG - Anda belum login!</p>
        <p>Silakan login terlebih dahulu.</p>
    <?php else: ?>
        <p class="success">✅ Session ditemukan</p>
        <pre><?php print_r($_SESSION); ?></pre>
        
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'dosen'): ?>
            <p class="success">✅ Role: DOSEN (Valid)</p>
        <?php else: ?>
            <p class="error">❌ Role bukan dosen: <?php echo $_SESSION['role'] ?? 'TIDAK ADA'; ?></p>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
            <p class="success">✅ Status Login: TRUE</p>
        <?php else: ?>
            <p class="error">❌ Status Login: FALSE atau tidak ada</p>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- 3. CEK DATABASE -->
<div class="container">
    <h2>3️⃣ Koneksi Database</h2>
    <?php
    $conn = @mysqli_connect("localhost", "root", "", "absensiqr_231051");
    if ($conn):
    ?>
        <p class="success">✅ Koneksi database BERHASIL</p>
        
        <!-- Cek kolom keterlambatan -->
        <?php
        $cekKolom = mysqli_query($conn, "SHOW COLUMNS FROM absensi_231051 LIKE 'keterlambatan_menit_231051'");
        if ($cekKolom && mysqli_num_rows($cekKolom) > 0):
        ?>
            <p class="success">✅ Kolom 'keterlambatan_menit_231051' DITEMUKAN</p>
        <?php else: ?>
            <p class="error">❌ Kolom 'keterlambatan_menit_231051' TIDAK DITEMUKAN</p>
            <p class="warning">⚠️  Jalankan migration SQL:</p>
            <pre>ALTER TABLE absensi_231051 
ADD COLUMN keterlambatan_menit_231051 INT DEFAULT 0;</pre>
        <?php endif; ?>
        
        <!-- Cek data user dosen -->
        <?php
        $countDosen = mysqli_query($conn, "SELECT COUNT(*) as total FROM user_231051 WHERE role_231051 = 'dosen'");
        $totalDosen = mysqli_fetch_assoc($countDosen)['total'];
        ?>
        <p>Total user dengan role 'dosen': <strong><?php echo $totalDosen; ?></strong></p>
        
    <?php else: ?>
        <p class="error">❌ Koneksi database GAGAL</p>
        <p>Error: <?php echo mysqli_connect_error(); ?></p>
    <?php endif; ?>
</div>

<!-- 4. CEK FILE -->
<div class="container">
    <h2>4️⃣ Keberadaan File</h2>
    <?php
    $files = [
        'index.php' => 'Halaman Login',
        'dashboardDosen.php' => 'Dashboard Dosen',
        'dashboardMahasiswa.php' => 'Dashboard Mahasiswa',
        'prosesAbsensi.php' => 'Proses Absensi (untuk mahasiswa)',
        'koneksi.php' => 'File Koneksi Database',
        'migration_add_keterlambatan.sql' => 'File Migration SQL'
    ];
    
    foreach ($files as $file => $desc):
        $exists = file_exists($file);
        $class = $exists ? 'success' : 'error';
        $icon = $exists ? '✅' : '❌';
        echo "<p class='$class'>$icon $desc ($file)</p>";
    endforeach;
    ?>
</div>

<!-- 5. TEST AKSES -->
<div class="container">
    <h2>5️⃣ Status Akses Dashboard Dosen</h2>
    <?php
    if (empty($_SESSION)):
        echo "<p class='error'>❌ TIDAK BISA AKSES - Belum login</p>";
        echo "<p><strong>Solusi:</strong> Klik tombol 'Login' di bawah</p>";
    elseif (!isset($_SESSION['login']) || $_SESSION['login'] !== true):
        echo "<p class='error'>❌ TIDAK BISA AKSES - Session login tidak valid</p>";
        echo "<p><strong>Solusi:</strong> Logout dan login ulang</p>";
    elseif (!isset($_SESSION['role']) || $_SESSION['role'] !== 'dosen'):
        echo "<p class='error'>❌ TIDAK BISA AKSES - Role bukan dosen (Role: " . ($_SESSION['role'] ?? 'TIDAK ADA') . ")</p>";
        echo "<p><strong>Solusi:</strong> Login dengan akun dosen</p>";
    else:
        echo "<p class='success'>✅ BISA AKSES - Session valid untuk dosen</p>";
        echo "<p><strong>Anda siap mengakses Dashboard Dosen!</strong></p>";
    endif;
    ?>
</div>

<!-- 6. BROWSER INFO -->
<div class="container">
    <h2>6️⃣ Informasi Browser</h2>
    <pre><?php
    echo "User Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown') . "\n";
    echo "Accept: " . ($_SERVER['HTTP_ACCEPT'] ?? 'Unknown') . "\n";
    ?></pre>
    
    <p class="warning">⚠️  Jika Anda melihat cache issue:</p>
    <ul>
        <li>Tekan <strong>Ctrl + Shift + Delete</strong> untuk clear cache</li>
        <li>Atau gunakan <strong>Incognito Mode</strong> (Ctrl + Shift + N)</li>
        <li>Hard refresh: <strong>Ctrl + F5</strong></li>
    </ul>
</div>

<!-- 7. SOLUSI & ACTION -->
<div class="container">
    <h2>7️⃣ Langkah Selanjutnya</h2>
    
    <?php if (empty($_SESSION)): ?>
        <p><strong>Anda belum login. Silakan login terlebih dahulu:</strong></p>
        <a href="index.php" class="btn">🔐 Login</a>
        
    <?php elseif (!isset($_SESSION['role']) || $_SESSION['role'] !== 'dosen'): ?>
        <p><strong>Role tidak sesuai. Logout dan login sebagai dosen:</strong></p>
        <a href="logout.php" class="btn">🚪 Logout</a>
        <a href="index.php" class="btn">🔐 Login Ulang</a>
        
    <?php else: ?>
        <p class="success"><strong>✅ Session valid! Anda bisa akses dashboard dosen:</strong></p>
        <a href="dashboardDosen.php" class="btn">📊 Buka Dashboard Dosen</a>
        <a href="logout.php" class="btn">🚪 Logout</a>
    <?php endif; ?>
    
    <hr>
    
    <p><strong>File Testing Lainnya:</strong></p>
    <a href="cek_session.php" class="btn">Cek Session</a>
    <a href="test_database.php" class="btn">Cek Database</a>
    <a href="test_dosen.php" class="btn">Test Dosen</a>
</div>

<!-- 8. TROUBLESHOOTING ERROR JSON -->
<div class="container">
    <h2>8️⃣ Troubleshooting Error JSON</h2>
    <p>Jika Anda melihat error: <code>{"success":false,"message":"QR tidak ditemukan"}</code></p>
    
    <p class="error"><strong>PENYEBAB:</strong></p>
    <ol>
        <li><strong>URL Salah</strong> - Anda mungkin akses <code>prosesAbsensi.php</code> bukan <code>dashboardDosen.php</code></li>
        <li><strong>Browser Cache</strong> - Browser menampilkan halaman lama dari cache</li>
        <li><strong>AJAX Request Failed</strong> - Ada JavaScript yang gagal request (cek Console F12)</li>
        <li><strong>Redirect Error</strong> - Ada redirect yang salah</li>
    </ol>
    
    <p class="success"><strong>SOLUSI:</strong></p>
    <ol>
        <li><strong>Pastikan URL benar:</strong>
            <pre>✅ http://localhost:8000/dashboardDosen.php
❌ http://localhost:8000/prosesAbsensi.php</pre>
        </li>
        <li><strong>Clear browser cache</strong> - Ctrl + Shift + Delete</li>
        <li><strong>Gunakan Incognito Mode</strong> - Ctrl + Shift + N</li>
        <li><strong>Hard Refresh</strong> - Ctrl + F5</li>
        <li><strong>Cek Developer Tools</strong> - F12 → Tab Console untuk lihat error</li>
    </ol>
</div>

<div class="container" style="background: #e3f2fd; border-left: 4px solid #1976d2;">
    <h2>💡 Tips</h2>
    <p>Jika masih error setelah mengikuti semua langkah:</p>
    <ol>
        <li>Screenshot halaman ini</li>
        <li>Buka Developer Tools (F12)</li>
        <li>Screenshot tab Console dan Network</li>
        <li>Kirimkan screenshot untuk debugging lebih lanjut</li>
    </ol>
</div>

<div style="text-align: center; margin-top: 30px; color: #666;">
    <p>Halaman diagnosa dibuat otomatis • <?php echo date('Y-m-d H:i:s'); ?></p>
</div>

</body>
</html>
