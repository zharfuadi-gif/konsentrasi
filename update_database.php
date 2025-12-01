<?php
// Script to update the database schema for the new features

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "absensiqr_231051";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected to database successfully\n";

// Update the status column to include 'Sakit' in ENUM
$sql = "ALTER TABLE absensi_231051 MODIFY COLUMN status_231051 ENUM('Hadir', 'Terlambat', 'Alfa', 'Izin', 'Sakit') DEFAULT 'Alfa';";

if ($conn->query($sql) === TRUE) {
    echo "Status column updated successfully to include 'Sakit' option\n";
} else {
    echo "Error updating status column: " . $conn->error . "\n";
}

// Add catatan column if it doesn't exist
$checkCol = $conn->query("SHOW COLUMNS FROM absensi_231051 LIKE 'catatan_231051'");
if ($checkCol->num_rows == 0) {
    $sql2 = "ALTER TABLE absensi_231051 ADD COLUMN catatan_231051 TEXT DEFAULT NULL COMMENT 'Catatan tambahan untuk status khusus seperti sakit/izin';";

    if ($conn->query($sql2) === TRUE) {
        echo "Catatan column added successfully\n";
    } else {
        echo "Error adding catatan column: " . $conn->error . "\n";
    }
} else {
    echo "Catatan column already exists\n";
}

$conn->close();
echo "Database update completed!\n";
?>