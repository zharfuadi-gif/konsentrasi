<?php
$koneksi = new mysqli("localhost", "root", "", "absensiqr_231051");

// Cek koneksi
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}
?>
