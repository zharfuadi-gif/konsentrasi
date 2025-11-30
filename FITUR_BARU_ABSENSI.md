# Fitur Baru: Tracking Keterlambatan & Mahasiswa Alfa

## 📋 Ringkasan Fitur

Sistem absensi telah ditingkatkan dengan fitur-fitur berikut untuk memudahkan dosen dalam memantau kehadiran mahasiswa:

### 1. **Tracking Keterlambatan**
   - Sistem sekarang mencatat berapa menit mahasiswa terlambat
   - Informasi keterlambatan ditampilkan di dashboard dosen
   - Data keterlambatan tersimpan permanen di database

### 2. **Identifikasi Mahasiswa Alfa (Tidak Hadir)**
   - Mahasiswa yang tidak hadir ditandai dengan status "Alfa"
   - Baris data mahasiswa alfa diberi highlight warna merah
   - Filter khusus untuk melihat hanya mahasiswa yang alfa

### 3. **Statistik Real-time**
   - Dashboard menampilkan ringkasan jumlah:
     - 🚫 Tidak Hadir (Alfa)
     - ⏰ Terlambat
     - ✅ Hadir
     - 📋 Izin

### 4. **Filter Tingkat Lanjut**
   - Filter berdasarkan Mata Kuliah
   - Filter berdasarkan Status Kehadiran (Alfa, Terlambat, Hadir, Izin)
   - Kombinasi filter untuk analisis yang lebih spesifik

## 🚀 Cara Instalasi

### Step 1: Jalankan Migration Database

Sebelum menggunakan fitur baru, Anda perlu menambahkan kolom baru ke database:

1. Buka **phpMyAdmin** atau MySQL client Anda
2. Pilih database `absensiqr_231051`
3. Buka file `migration_add_keterlambatan.sql`
4. Copy dan jalankan query SQL berikut:

```sql
ALTER TABLE absensi_231051 
ADD COLUMN keterlambatan_menit_231051 INT DEFAULT 0 
COMMENT 'Jumlah menit keterlambatan (0 jika tidak terlambat)';
```

### Step 2: Verifikasi File yang Diupdate

Pastikan file-file berikut sudah diupdate:

- ✅ `prosesAbsensi.php` - Menyimpan data keterlambatan
- ✅ `dashboardDosen.php` - Menampilkan data keterlambatan dan filter

## 📖 Cara Menggunakan

### Untuk Dosen:

1. **Login ke Dashboard Dosen**
   - Masuk dengan akun dosen Anda

2. **Lihat Statistik**
   - Di tab "Absensi", Anda akan melihat 4 kartu statistik di bagian atas
   - Kartu menampilkan jumlah mahasiswa untuk setiap status

3. **Filter Data**
   - **Filter Mata Kuliah**: Pilih mata kuliah tertentu untuk melihat data spesifik
   - **Filter Status**: Pilih status untuk melihat hanya:
     - Mahasiswa yang Alfa (tidak hadir)
     - Mahasiswa yang Terlambat
     - Mahasiswa yang Hadir tepat waktu
     - Mahasiswa yang Izin

4. **Lihat Detail Keterlambatan**
   - Kolom "Keterlambatan_Menit" menampilkan berapa menit mahasiswa terlambat
   - Jika tidak terlambat, akan ditampilkan tanda "-"

5. **Identifikasi Mahasiswa Alfa**
   - Baris dengan status "Alfa" ditandai dengan background merah
   - Mudah dikenali dari warna yang berbeda

### Untuk Mahasiswa:

- Sistem tetap sama seperti sebelumnya
- Scan QR Code untuk absen
- Jika terlambat, sistem akan otomatis mencatat berapa menit keterlambatan
- Status akan menjadi "Terlambat" dengan informasi jumlah menit

## 🎨 Kode Warna Status

| Status | Warna Background | Ikon | Keterangan |
|--------|-----------------|------|------------|
| Alfa | 🔴 Merah Muda | 🚫 | Tidak hadir sama sekali |
| Terlambat | 🟡 Kuning | ⏰ | Hadir tapi terlambat |
| Hadir | 🟢 Hijau | ✅ | Hadir tepat waktu |
| Izin | 🔵 Biru | 📋 | Izin/Sakit |

## 🔧 Detail Teknis

### Database Schema
```
Tabel: absensi_231051
Kolom Baru: keterlambatan_menit_231051 (INT, DEFAULT 0)
```

### Logika Keterlambatan
- **Hadir**: Scan dalam waktu ≤ 2 menit dari jam mulai
- **Terlambat**: Scan > 2 menit dari jam mulai, tapi masih dalam jam kuliah
- **Alfa**: Auto-insert setelah jam kuliah berakhir jika belum scan

### Perhitungan Menit Terlambat
```php
$menitTerlambat = ceil(($waktuScan - $jamMulai) / 60);
```

## 📊 Contoh Penggunaan

### Skenario 1: Melihat Semua Mahasiswa Alfa
1. Buka tab "Absensi"
2. Pada dropdown "Status", pilih "🚫 Alfa (Tidak Hadir)"
3. Klik tombol "Filter"
4. Sistem akan menampilkan hanya mahasiswa yang tidak hadir

### Skenario 2: Melihat Mahasiswa Terlambat di Mata Kuliah Tertentu
1. Pilih mata kuliah dari dropdown "Mata Kuliah"
2. Pilih "⏰ Terlambat" dari dropdown "Status"
3. Klik "Filter"
4. Lihat kolom "Keterlambatan_Menit" untuk mengetahui berapa menit terlambat

### Skenario 3: Statistik Keseluruhan
1. Jangan pilih filter apapun (atau pilih "Semua")
2. Lihat kartu statistik di bagian atas
3. Anda akan melihat total keseluruhan untuk setiap status

## ❗ Catatan Penting

1. **Migration Wajib**: Jalankan SQL migration sebelum menggunakan fitur baru
2. **Data Lama**: Data absensi lama tidak akan memiliki informasi keterlambatan (default 0)
3. **Auto-Alfa**: Sistem otomatis menandai mahasiswa sebagai Alfa setelah jam kuliah berakhir
4. **Update Alfa**: Jika mahasiswa scan setelah di-mark Alfa, status akan diupdate menjadi Hadir/Terlambat

## 🐛 Troubleshooting

### Error: Unknown column 'keterlambatan_menit_231051'
**Solusi**: Anda belum menjalankan migration. Jalankan SQL di `migration_add_keterlambatan.sql`

### Kolom keterlambatan tidak muncul di tabel
**Solusi**: Refresh halaman atau clear cache browser

### Statistik tidak akurat
**Solusi**: Pastikan filter yang Anda pilih sudah sesuai. Reset filter dengan memilih "Semua"

## 👨‍💻 Developer Notes

File yang dimodifikasi:
- `prosesAbsensi.php` - Lines 91-105, 117-122, 135-139
- `dashboardDosen.php` - Lines 38-104, 106-153, 181-226, 250-285

## 📝 Changelog

### Version 2.0 (Current)
- ✅ Menambahkan tracking keterlambatan dalam menit
- ✅ Menambahkan filter berdasarkan status
- ✅ Menambahkan statistik real-time
- ✅ Menambahkan color coding untuk status
- ✅ Menambahkan ikon visual untuk setiap status
- ✅ Database schema update

### Version 1.0
- Fitur absensi dasar dengan QR Code
- Status: Hadir, Izin, Alfa (tanpa detail keterlambatan)
