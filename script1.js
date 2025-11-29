// function login() {
//   const username = document.getElementById("username").value;
//   const password = document.getElementById("password").value;
//   const role = document.getElementById("role").value;
//   const errorMessage = document.getElementById("error-message");

//   if (!username || !password || !role) {
//     errorMessage.textContent = "Semua field harus diisi!";
//     return;
//   }

//   // Contoh simulasi login (nanti bisa diganti cek ke database)
//   if (role === "admin") {
//     window.location.href = "dashboardAdmin.php";
//   } else if (role === "dosen") {
//     window.location.href = "dashboardDosen.php";
//   } else if (role === "mahasiswa") {
//     window.location.href = "dashboardMahasiswa.php";
//   } else {
//     errorMessage.textContent = "Role tidak valid.";
//   }
  
// }

async function login(event) {
  event.preventDefault();

  const email = document.getElementById("username").value;
  const password = document.getElementById("password").value;
  const role = document.getElementById("role").value;
  const errorMessage = document.getElementById("error-message");

  if (!email || !password || !role) {
    errorMessage.textContent = "Semua field harus diisi!";
    return;
  }

  // Kirim data ke PHP untuk dicek ke database
  const response = await fetch("login.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `email=${email}&password=${password}&role=${role}`
  });

  const result = await response.json();

  if (result.success) {
    // Redirect ke dashboard sesuai role
    if (role === "admin") window.location.href = "admin/dashboard.php";
    else if (role === "dosen") window.location.href = "dosen/dashboard.php";
    else if (role === "mahasiswa") window.location.href = "mahasiswa/dashboard.php";
  } else {
    errorMessage.textContent = result.message;
  }
}

// async function login() {
//   const username = document.getElementById("username").value.trim(); // ini berisi email
//   const password = document.getElementById("password").value.trim();
//   const role = document.getElementById("role").value;
//   const errorMessage = document.getElementById("error-message");

//   errorMessage.textContent = "";

//   if (!username || !password || !role) {
//     errorMessage.textContent = "Semua field harus diisi!";
//     return;
//   }

//   try {
//     const response = await fetch("index.php", {
//       method: "POST",
//       headers: { "Content-Type": "application/json" },
//       body: JSON.stringify({ email, password, role }), // dikirim ke PHP
//     });

//     const result = await response.json();

//     if (result.status === "success") {
//       window.location.href = result.redirect;
//     } else {
//       errorMessage.textContent = result.message;
//     }
//   } catch (err) {
//     errorMessage.textContent = "Terjadi kesalahan koneksi ke server.";

//   }
// }

