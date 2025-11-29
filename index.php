<?php
session_start();
include "koneksi.php";

if (isset($_POST['login'])) {
    $email = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Ambil data user sesuai email dan role
    $sql = "SELECT * FROM user_231051 WHERE email_231051 = ? AND role_231051 = ?";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("ss", $email, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Cek password langsung (tanpa hash)
        if ($password === $user['password_231051']) {
            $_SESSION['login'] = true;
            $_SESSION['role'] = $user['role_231051'];
            $_SESSION['email'] = $user['email_231051'];

            // Redirect sesuai role
            if ($role == "admin") {
                header("Location: dashboardAdmin.php");
            } elseif ($role == "dosen") {
                header("Location: dashboardDosen.php");
            } elseif ($role == "mahasiswa") {
                header("Location: dashboardMahasiswa.php");
            }
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Email atau role tidak cocok!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Absensi QR</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
  background: #f8f9fa;
  font-family: 'Poppins', sans-serif;
}
.card {
  border: none;
  border-radius: 10px;
}
.btn-primary {
  background-color: #007bff;
  border: none;
}
</style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-4">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h3 class="text-center mb-4 fw-bold">Login Absensi QR</h3>
                    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Role</label>
                            <select name="role" class="form-control" required>
                                <option value="">-- Pilih Role --</option>
                                <option value="admin">Admin</option>
                                <option value="dosen">Dosen</option>
                                <option value="mahasiswa">Mahasiswa</option>
                            </select>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
