<?php
// SIMPLE TEST FILE - NO SESSION REQUIRED
echo "<h1>TEST FILE - DOSEN DASHBOARD</h1>";
echo "<pre>";
echo "PHP Version: " . phpversion() . "\n";
echo "Current File: " . __FILE__ . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "\n==============================================\n";
echo "SESSION DATA:\n";
echo "==============================================\n";

session_start();
print_r($_SESSION);

echo "\n==============================================\n";
echo "If you see this, the file is loading correctly!\n";
echo "==============================================\n";
echo "</pre>";

echo "<hr>";
echo "<h2>Next Steps:</h2>";
echo "<ol>";
echo "<li>If session is empty → <a href='index.php'>Go to Login</a></li>";
echo "<li>If session exists → <a href='dashboardDosen.php'>Try Dashboard Dosen</a></li>";
echo "</ol>";
?>
