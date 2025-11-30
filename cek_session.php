<?php
session_start();

echo "<h2>CEK SESSION DEBUGGING</h2>";
echo "<pre>";
echo "==============================================\n";
echo "SESSION DATA:\n";
echo "==============================================\n";
print_r($_SESSION);
echo "\n==============================================\n";

if (isset($_SESSION['login'])) {
    echo "✅ Login: " . ($_SESSION['login'] ? 'TRUE' : 'FALSE') . "\n";
} else {
    echo "❌ Login: TIDAK ADA\n";
}

if (isset($_SESSION['role'])) {
    echo "✅ Role: " . $_SESSION['role'] . "\n";
} else {
    echo "❌ Role: TIDAK ADA\n";
}

if (isset($_SESSION['email'])) {
    echo "✅ Email: " . $_SESSION['email'] . "\n";
} else {
    echo "❌ Email: TIDAK ADA\n";
}

echo "==============================================\n";

// Cek redirect
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'dosen') {
    echo "\n⚠️  MASALAH DITEMUKAN:\n";
    echo "Session tidak valid untuk dosen!\n";
    echo "Anda akan diarahkan ke halaman login.\n\n";
    echo "<a href='index.php'>Kembali ke Login</a>";
} else {
    echo "\n✅ SESSION VALID UNTUK DOSEN!\n";
    echo "<a href='dashboardDosen.php'>Akses Dashboard Dosen</a>";
}

echo "</pre>";
?>
