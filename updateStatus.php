<?php
session_start();

// ===== CEK LOGIN SEBAGAI DOSEN =====
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
    echo json_encode(["success" => false, "message" => "Koneksi gagal: " . mysqli_connect_error()]);
    exit;
}

// ===== HANDLE POST REQUEST =====
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? '';
    $status = $_POST['status'] ?? '';
    
    // Validasi status
    $validStatuses = ['Hadir', 'Terlambat', 'Alfa', 'Izin', 'Sakit'];
    if (!in_array($status, $validStatuses)) {
        echo json_encode(["success" => false, "message" => "Status tidak valid"]);
        exit;
    }
    
    if (empty($id)) {
        echo json_encode(["success" => false, "message" => "ID absensi tidak valid"]);
        exit;
    }
    
    // Update status di database
    $query = "UPDATE absensi_231051 SET status_231051 = ? WHERE id_absensi_231051 = ?";
    $stmt = mysqli_prepare($conn, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "si", $status, $id);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["success" => true, "message" => "Status berhasil diubah"]);
        } else {
            echo json_encode(["success" => false, "message" => "Gagal mengupdate status"]);
        }
        
        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(["success" => false, "message" => "Gagal menyiapkan query"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Metode request tidak valid"]);
}

mysqli_close($conn);
?>