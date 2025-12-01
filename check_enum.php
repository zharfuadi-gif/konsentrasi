<?php
$conn = new mysqli('localhost', 'root', '', 'absensiqr_231051');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Check the structure of the absensi_231051 table
$result = $conn->query("DESCRIBE absensi_231051");
echo "Structure of absensi_231051 table:\n";
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " | " . $row['Type'] . " | " . $row['Null'] . " | " . $row['Key'] . " | " . $row['Default'] . " | " . $row['Extra'] . "\n";
}

// Check if status column is ENUM and get its values
$result = $conn->query("SHOW COLUMNS FROM absensi_231051 LIKE 'status_231051'");
$row = $result->fetch_assoc();
echo "\nStatus column details:\n";
print_r($row);

// If it's an ENUM, we can get the possible values
$columnType = $row['Type'];
if (preg_match("/^enum\((.*)\)$/i", $columnType, $matches)) {
    echo "\nENUM values: " . $matches[1] . "\n";
} else {
    echo "\nStatus column is not ENUM, it's: " . $columnType . "\n";
}

$conn->close();
?>