<?php
include "koneksi.php";

// Jika submit banyak data
if (isset($_POST['simpan_semua'])) {

    $jumlah = count($_POST['nama']);

    for ($i = 0; $i < $jumlah; $i++) {

        if ($_POST['nama'][$i] == "") continue; // skip baris kosong

        $nama     = $_POST['nama'][$i];
        $nip      = $_POST['nip'][$i];
        $email    = $_POST['email'][$i];
        $username = $_POST['username'][$i];
        $password = $_POST['password'][$i];
        $role     = "dosen";

        // Simpan ke tabel dosen
        $sql1 = "INSERT INTO dosen_231051 (nama_dosen_231051, nip_231051, email_231051, password_231051)
                 VALUES ('$nama', '$nip', '$email', '$password')";

        // Simpan ke tabel user
        $sql2 = "INSERT INTO user_231051 (email_231051, password_231051, role_231051)
                 VALUES ('$email', '$password', '$role')";

        $koneksi->query($sql1);
        $koneksi->query($sql2);
    }

    echo "<script>alert('Semua data dosen berhasil ditambahkan!'); window.location='list_dosen.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Tambah Banyak Dosen</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow">
        <div class="card-body">
            <h3 class="text-center mb-4">Tambah Banyak Dosen</h3>

            <!-- FORM INPUT JUMLAH -->
            <div class="mb-3">
                <label>Masukkan jumlah dosen yang ingin ditambahkan:</label>
                <input type="number" id="jumlah" class="form-control" placeholder="Misal: 50">
            </div>

            <button class="btn btn-primary" onclick="buatForm()">Buat Form</button>

            <hr>

            <!-- FORM DINAMIS MUNCUL DI SINI -->
            <form method="POST" id="formBanyak">

                <div id="formContainer"></div>

                <button type="submit" name="simpan_semua" class="btn btn-success mt-3 d-none" id="tombolSimpan">
                    Simpan Semua
                </button>

            </form>

        </div>
    </div>

</div>

<!-- SCRIPT BUAT FORM DINAMIS -->
<script>
function buatForm() {
    let jumlah = document.getElementById('jumlah').value;
    let container = document.getElementById('formContainer');
    let tombolSimpan = document.getElementById('tombolSimpan');

    if (jumlah <= 0) {
        alert("Masukkan jumlah valid!");
        return;
    }

    container.innerHTML = ""; // reset form

    for (let i = 1; i <= jumlah; i++) {
        container.innerHTML += `
            <div class="border p-3 mb-3 rounded">
                <h5>Data Dosen #${i}</h5>
                <div class="row">
                    <div class="col-md-3">
                        <label>Nama Dosen</label>
                        <input type="text" name="nama[]" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label>NIP</label>
                        <input type="text" name="nip[]" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label>Email</label>
                        <input type="email" name="email[]" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label>Username</label>
                        <input type="text" name="username[]" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label>Password</label>
                        <input type="text" name="password[]" class="form-control" required>
                    </div>
                </div>
            </div>
        `;
    }

    tombolSimpan.classList.remove("d-none");
}
</script>

</body>
</html>
