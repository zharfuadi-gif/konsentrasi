<?php
session_start();

// ===== CEK LOGIN =====
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'mahasiswa') {
    echo json_encode(["success" => false, "message" => "Akses ditolak"]);
    exit;
}

// ===== KONEKSI DATABASE =====
$conn = mysqli_connect("localhost", "root", "", "absensiqr_231051");
if (!$conn) {
    echo json_encode(["success" => false, "message" => "Koneksi gagal: " . mysqli_connect_error()]);
    exit;
}

// ===== TIMEZONE =====
date_default_timezone_set("Asia/Makassar");
mysqli_query($conn, "SET time_zone = '+08:00'");

// ===== DATA QR =====
if (!isset($_POST['qr_data'])) {
    echo json_encode(["success" => false, "message" => "QR tidak ditemukan"]);
    exit;
}

$qr = $_POST['qr_data'];
$parts = explode("|", $qr);

if (count($parts) < 3) {
    echo json_encode(["success" => false, "message" => "QR tidak valid"]);
    exit;
}

$id_mk    = $parts[0];
$id_kelas = $parts[1];
$id_jam   = $parts[2];

// ===== AMBIL ID MAHASISWA =====
$email = $_SESSION['email'];
$getMhs = mysqli_query($conn, "SELECT id_mahasiswa_231051 FROM mahasiswa_231051 WHERE email_231051='$email'");

if (!$getMhs || mysqli_num_rows($getMhs) == 0) {
    echo json_encode(["success" => false, "message" => "Mahasiswa tidak ditemukan"]);
    exit;
}

$mhs = mysqli_fetch_assoc($getMhs);
$id_mahasiswa = $mhs['id_mahasiswa_231051'];

// ===== AMBIL JAM KULIAH =====
$getJam = mysqli_query($conn,
    "SELECT jam_mulai_231051, jam_selesai_231051 
     FROM jam_231051 
     WHERE id_jam_231051='$id_jam'"
);

if (!$getJam || mysqli_num_rows($getJam) == 0) {
    echo json_encode(["success" => false, "message" => "Jadwal jam tidak ditemukan"]);
    exit;
}

$jam = mysqli_fetch_assoc($getJam);

$jamMulai       = strtotime($jam['jam_mulai_231051']);
$jamSelesai     = strtotime($jam['jam_selesai_231051']);
$now            = time();
$batasHadir     = $jamMulai + 120; // 2 menit

$tanggal = date("Y-m-d");

// ===== CEK APAKAH SUDAH ADA ABSENSI HARI INI (TERMUKA JIKA STATUS = ALFA) =====
$cek = mysqli_query($conn,
    "SELECT id_absensi_231051, status_231051 
     FROM absensi_231051
     WHERE id_mahasiswa_231051='$id_mahasiswa'
     AND tanggal_231051='$tanggal'
     LIMIT 1"
);

// ================================
//   ATUR STATUS BERDASARKAN WAKTU
// ================================

// Jam sudah berakhir → tidak bisa scan
if ($now > $jamSelesai) {
    echo json_encode(["success" => false, "message" => "Absensi sudah berakhir"]);
    exit;
}

// Variabel untuk menyimpan menit keterlambatan
$menitTerlambat = 0;

// Tepat waktu ≤ 2 menit
if ($now <= $batasHadir) {
    $status = "Hadir";
    $info = "";
    $menitTerlambat = 0;
}
// Terlambat tapi masih dalam jam kuliah
elseif ($now <= $jamSelesai) {
    $menitTerlambat = ceil(($now - $jamMulai) / 60);
    $status = "Terlambat";
    $info = " ($menitTerlambat menit terlambat)";
}

// ================================
//     UPDATE / INSERT ABSENSI
// ================================

// ===== JIKA SUDAH ADA DATA (Termasuk jika ALFA) → UPDATE =====
if (mysqli_num_rows($cek) > 0) {

    $row = mysqli_fetch_assoc($cek);

    // Jika sebelumnya ALFA → tetap diperbolehkan update
    $update = mysqli_query($conn,
        "UPDATE absensi_231051 SET 
            id_matakuliah_231051='$id_mk',
            id_jam_231051='$id_jam',
            status_231051='$status',
            keterlambatan_menit_231051='$menitTerlambat'
         WHERE id_absensi_231051='{$row['id_absensi_231051']}'"
    );

    if ($update) {
        echo json_encode(["success" => true, "message" => "Absensi berhasil: $status$info"]);
    } else {
        echo json_encode(["success" => false, "message" => "Gagal update: " . mysqli_error($conn)]);
    }

} else {

    // ===== INSERT jika belum ada baris apapun =====
    $insert = mysqli_query($conn,
        "INSERT INTO absensi_231051 
            (id_mahasiswa_231051, id_matakuliah_231051, id_jam_231051, tanggal_231051, status_231051, keterlambatan_menit_231051)
         VALUES 
            ('$id_mahasiswa', '$id_mk', '$id_jam', '$tanggal', '$status', '$menitTerlambat')"
    );

    if ($insert) {
        echo json_encode(["success" => true, "message" => "Absensi berhasil: $status$info"]);
    } else {
        echo json_encode(["success" => false, "message" => "Gagal absen: " . mysqli_error($conn)]);
    }
}

?>
