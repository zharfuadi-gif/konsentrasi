# 🔧 TROUBLESHOOTING ERROR DASHBOARD DOSEN

## Error: `{"success":false,"message":"QR tidak ditemukan"}`

### 🚨 Masalah Utama:
Error ini muncul karena Anda mengakses **file yang salah**. Error berasal dari `prosesAbsensi.php` (untuk mahasiswa scan QR), bukan `dashboardDosen.php`.

---

## ✅ LANGKAH PERBAIKAN (Ikuti berurutan):

### Step 1: Cek Session
1. Buka browser
2. Akses: `http://localhost/konsentrasi/cek_session.php`
3. Lihat output:
   - ✅ Jika "SESSION VALID UNTUK DOSEN" → Lanjut Step 2
   - ❌ Jika tidak valid → Logout dan login ulang sebagai dosen

### Step 2: Cek Database
1. Akses: `http://localhost/konsentrasi/test_database.php`
2. Pastikan kolom `keterlambatan_menit_231051` ada
3. Jika TIDAK ADA:
   - Buka phpMyAdmin
   - Jalankan SQL:
   ```sql
   ALTER TABLE absensi_231051 
   ADD COLUMN keterlambatan_menit_231051 INT DEFAULT 0;
   ```

### Step 3: Akses Dashboard Dosen dengan URL yang Benar
**URL YANG BENAR:**
```
http://localhost/konsentrasi/dashboardDosen.php
```

**URL YANG SALAH (JANGAN DIAKSES):**
```
❌ http://localhost/konsentrasi/prosesAbsensi.php
❌ http://localhost/konsentrasi/dashboardMahasiswa.php
```

### Step 4: Clear Browser Cache
1. Tekan `Ctrl + Shift + Delete`
2. Pilih "Cookies and Cache"
3. Clear data
4. Atau gunakan **Incognito Mode** (Ctrl + Shift + N)

### Step 5: Login Ulang
1. Logout dari sistem
2. Akses: `http://localhost/konsentrasi/index.php`
3. Login dengan:
   - Email: (email dosen Anda)
   - Password: (password dosen)
   - **Role: DOSEN** ← Penting!
4. Klik Login
5. Anda akan otomatis diarahkan ke `dashboardDosen.php`

---

## 📋 CHECKLIST DEBUGGING

Cek satu per satu:

- [ ] Sudah jalankan SQL migration `migration_add_keterlambatan.sql`?
- [ ] Login menggunakan role **"dosen"** bukan "mahasiswa"?
- [ ] URL yang diakses adalah `dashboardDosen.php`?
- [ ] Browser cache sudah di-clear?
- [ ] Session valid (cek di `cek_session.php`)?
- [ ] Database memiliki kolom `keterlambatan_menit_231051`?

---

## 🔍 PENYEBAB UMUM ERROR INI:

### 1. **Salah URL**
   - ❌ Anda akses: `prosesAbsensi.php` atau `dashboardMahasiswa.php`
   - ✅ Harusnya: `dashboardDosen.php`

### 2. **Login dengan Role Salah**
   - ❌ Login sebagai "mahasiswa"
   - ✅ Harusnya: Login sebagai "dosen"

### 3. **Session Bermasalah**
   - Cookie browser corrupt
   - **Solusi**: Logout, clear cache, login ulang

### 4. **Bookmark/Shortcut Salah**
   - Bookmark mengarah ke URL yang salah
   - **Solusi**: Hapus bookmark lama, buat baru

---

## 🆘 SOLUSI CEPAT (Quick Fix):

```bash
1. Logout dari sistem
2. Tutup semua tab browser
3. Buka Incognito/Private window
4. Akses: http://localhost/konsentrasi/index.php
5. Login sebagai DOSEN
6. Pastikan diarahkan ke dashboardDosen.php
```

---

## 🧪 FILE TESTING YANG DIBUAT:

1. **`cek_session.php`** - Cek validitas session Anda
2. **`test_database.php`** - Cek kolom database
3. **`migration_add_keterlambatan.sql`** - SQL untuk add kolom

---

## ❓ JIKA MASIH ERROR:

Cek hal berikut:

### A. Pastikan Struktur File Benar:
```
konsentrasi/
├── index.php (Login page)
├── dashboardDosen.php (Dashboard dosen)
├── dashboardMahasiswa.php (Dashboard mahasiswa)
├── dashboardAdmin.php (Dashboard admin)
├── prosesAbsensi.php (Proses scan QR - hanya untuk mahasiswa)
└── ...
```

### B. Cek Role di Database:
```sql
SELECT * FROM user_231051 WHERE role_231051 = 'dosen';
```
Pastikan ada user dengan role 'dosen'.

### C. Cek Error Log:
1. Buka: `http://localhost/konsentrasi/dashboardDosen.php`
2. Buka Developer Tools (F12)
3. Tab "Console" - lihat error JavaScript
4. Tab "Network" - lihat HTTP requests

---

## 📞 MASIH BUTUH BANTUAN?

Jalankan perintah ini dan kirim hasilnya:

1. `http://localhost/konsentrasi/cek_session.php`
2. `http://localhost/konsentrasi/test_database.php`

Dengan informasi tersebut, masalah bisa diidentifikasi lebih detail.

---

## ✅ SETELAH BERHASIL:

Jika dashboard sudah terbuka dengan benar, Anda akan melihat:
- Tab: Dashboard, Absensi, QR Code
- Nama dosen di bagian atas
- Statistik absensi (4 kartu berwarna)
- Tabel absensi dengan filter

**BUKAN** melihat JSON error atau halaman scan QR mahasiswa!
