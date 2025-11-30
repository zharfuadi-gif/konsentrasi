<?php
// TEST DATABASE & KOLOM KETERLAMBATAN
include "koneksi.php";

echo "<h2>CEK DATABASE & KOLOM</h2>";
echo "<pre>";

// Cek koneksi
if ($koneksi->connect_error) {
    echo "❌ Koneksi Database GAGAL: " . $koneksi->connect_error . "\n";
} else {
    echo "✅ Koneksi Database BERHASIL\n";
}

echo "\n==============================================\n";
echo "CEK KOLOM 'keterlambatan_menit_231051':\n";
echo "==============================================\n";

// Cek apakah kolom keterlambatan ada
$cekKolom = $koneksi->query("SHOW COLUMNS FROM absensi_231051 LIKE 'keterlambatan_menit_231051'");

if ($cekKolom && $cekKolom->num_rows > 0) {
    echo "✅ Kolom 'keterlambatan_menit_231051' DITEMUKAN\n";
    
    $kolom = $cekKolom->fetch_assoc();
    echo "\nDetail Kolom:\n";
    print_r($kolom);
} else {
    echo "❌ Kolom 'keterlambatan_menit_231051' TIDAK DITEMUKAN\n\n";
    echo "⚠️  SOLUSI:\n";
    echo "Jalankan SQL berikut di phpMyAdmin:\n\n";
    echo "ALTER TABLE absensi_231051 \n";
    echo "ADD COLUMN keterlambatan_menit_231051 INT DEFAULT 0;\n";
}

echo "\n==============================================\n";
echo "CEK STRUKTUR TABEL absensi_231051:\n";
echo "==============================================\n";

$struktur = $koneksi->query("SHOW COLUMNS FROM absensi_231051");
if ($struktur) {
    while ($row = $struktur->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
}

echo "\n==============================================\n";
echo "SAMPLE DATA ABSENSI (5 terakhir):\n";
echo "==============================================\n";

$sample = $koneksi->query("SELECT * FROM absensi_231051 ORDER BY id_absensi_231051 DESC LIMIT 5");
if ($sample && $sample->num_rows > 0) {
    while ($row = $sample->fetch_assoc()) {
        echo "ID: " . $row['id_absensi_231051'] . 
             " | Status: " . $row['status_231051'];
        
        // Cek apakah kolom keterlambatan ada di hasil
        if (isset($row['keterlambatan_menit_231051'])) {
            echo " | Terlambat: " . $row['keterlambatan_menit_231051'] . " menit";
        }
        echo "\n";
    }
} else {
    echo "Tidak ada data absensi.\n";
}

echo "</pre>";

$koneksi->close();
?>
