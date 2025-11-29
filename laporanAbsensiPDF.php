<?php
session_start();

// ===== CEK LOGIN =====
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'dosen') {
    header("Location: index.php");
    exit;
}

// ===== KONEKSI DATABASE =====
$servername = "localhost";
$username   = "root";
$password   = "";
$database   = "absensiqr_231051";

$conn = mysqli_connect($servername, $username, $password, $database);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// ===== CEK ID MATKUL =====
if (!isset($_GET['matkul']) || empty($_GET['matkul'])) {
    die("Mata kuliah tidak dipilih.");
}
$id_mk = $_GET['matkul'];

// ===== AMBIL DATA MATA KULIAH =====
$matkulQuery = mysqli_query($conn, "SELECT nama_matakuliah_231051 FROM matakuliah_231051 WHERE id_matakuliah_231051='$id_mk'");
if (!$matkulQuery || mysqli_num_rows($matkulQuery) == 0) {
    die("Mata kuliah tidak ditemukan.");
}
$matkulData = mysqli_fetch_assoc($matkulQuery);
$namaMatkul = $matkulData['nama_matakuliah_231051'];

// ===== AMBIL DATA ABSENSI =====
$query = "
SELECT 
    m.nama_mahasiswa_231051 AS Nama_Mahasiswa,
    j.hari_231051 AS Hari,
    DATE_FORMAT(j.jam_mulai_231051, '%H:%i') AS Jam_Mulai,
    DATE_FORMAT(j.jam_selesai_231051, '%H:%i') AS Jam_Selesai,
    a.tanggal_231051 AS Tanggal,
    a.status_231051 AS Status
FROM absensi_231051 a
LEFT JOIN mahasiswa_231051 m ON a.id_mahasiswa_231051 = m.id_mahasiswa_231051
LEFT JOIN jam_231051 j ON a.id_jam_231051 = j.id_jam_231051
WHERE a.id_matakuliah_231051='$id_mk'
ORDER BY a.tanggal_231051 ASC
";

$result = mysqli_query($conn, $query);
if (!$result) {
    die("Query error: " . mysqli_error($conn));
}

// ===== DOMPDF =====
require 'vendor/autoload.php';
use Dompdf\Dompdf;

$dompdf = new Dompdf();

// ===== BANGUN HTML PDF =====
$html = "<h2 style='text-align:center;'>Laporan Absensi - $namaMatkul</h2>";
$html .= "<table border='1' cellpadding='5' cellspacing='0' width='100%' style='border-collapse: collapse;'>";
$html .= "<tr style='background-color: #ddd;'>
            <th>Nama Mahasiswa</th>
            <th>Hari</th>
            <th>Jam Mulai</th>
            <th>Jam Selesai</th>
            <th>Tanggal</th>
            <th>Status</th>
          </tr>";

while ($row = mysqli_fetch_assoc($result)) {
    $html .= "<tr>
                <td>{$row['Nama_Mahasiswa']}</td>
                <td>{$row['Hari']}</td>
                <td>{$row['Jam_Mulai']}</td>
                <td>{$row['Jam_Selesai']}</td>
                <td>{$row['Tanggal']}</td>
                <td>{$row['Status']}</td>
              </tr>";
}

$html .= "</table>";

// Load HTML ke Dompdf
$dompdf->loadHtml($html);

// Set ukuran kertas & orientasi
$dompdf->setPaper('A4', 'landscape');

// Render PDF
$dompdf->render();

// Tampilkan PDF di browser
$dompdf->stream("Laporan_Absensi_$namaMatkul.pdf", ["Attachment" => false]);

mysqli_close($conn);
?>
