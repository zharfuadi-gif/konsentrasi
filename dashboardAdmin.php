<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
  header("Location: index.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard Admin - Absensi QR System</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f0f3f7;
      margin: 0;
      display: flex;
    }
    .sidebar {
      background-color: #0a7ae9;
      color: white;
      width: 250px;
      height: 100vh;
      padding: 20px;
      position: fixed;
      left: 0;
      top: 0;
      border-radius: 0 15px 15px 0;
    }
    .sidebar ul { list-style: none; padding: 0; }
    .sidebar li {
      padding: 12px 18px;
      border-radius: 8px;
      cursor: pointer;
      transition: background-color 0.3s;
    }
    .sidebar li:hover, .sidebar li.active {
      background-color: #1565c0;
    }
    .main-content {
      margin-left: 270px;
      padding: 25px;
      width: 100%;
    }
    h1 { color: #1976d2; text-align: center; }
    button { padding: 10px 15px; border: none; border-radius: 6px; background: #1976d2; color: white; cursor: pointer; }
  </style>
</head>
<body>
<?php include 'sidebar_admin.php'; ?>

  <main class="main-content">
    <h1>Selamat Datang di Dashboard Admin</h1>
    <p style="text-align:center;">Halo, <?= $_SESSION['email']; ?>! Pilih menu di sidebar untuk mengelola data.</p>
  </main>
</body>
</html>
