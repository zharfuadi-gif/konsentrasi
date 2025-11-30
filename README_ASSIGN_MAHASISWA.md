# Fitur Assign Mahasiswa ke Dosen

## Deskripsi
Fitur ini memungkinkan admin untuk menugaskan mahasiswa kepada setiap dosen, dan menampilkan statistik ketidakhadiran (absence) dari mahasiswa yang ditugaskan.

## Setup Database

### 1. Jalankan Migration SQL
Buka phpMyAdmin atau MySQL client Anda, lalu jalankan file SQL berikut:
```
migration_dosen_mahasiswa.sql
```

File ini akan membuat tabel `dosen_mahasiswa_231051` yang berisi:
- `id_dm_231051` - Primary key
- `id_dosen_231051` - Foreign key ke tabel dosen
- `id_mahasiswa_231051` - Foreign key ke tabel mahasiswa  
- `tanggal_assign_231051` - Timestamp penugasan

### 2. Verifikasi Tabel
Pastikan tabel berhasil dibuat dengan query:
```sql
SHOW TABLES LIKE 'dosen_mahasiswa_231051';
```

## Cara Menggunakan

### Untuk Admin

1. **Login sebagai Admin**
   - Akses halaman login
   - Masuk dengan kredensial admin

2. **Akses Menu "Assign Mahasiswa ke Dosen"**
   - Klik menu "Assign Mahasiswa ke Dosen" di sidebar admin
   - Halaman akan menampilkan semua dosen beserta mahasiswa yang sudah ditugaskan

3. **Menambah Mahasiswa ke Dosen**
   - Klik tombol "+ Tambah Mahasiswa" pada card dosen yang diinginkan
   - Modal akan muncul menampilkan daftar semua mahasiswa
   - Centang mahasiswa yang ingin ditugaskan
   - Klik "Simpan Penugasan"

4. **Melihat Statistik**
   - Di setiap card dosen, akan ditampilkan:
     - Total jumlah mahasiswa yang ditugaskan
     - Total ketidakhadiran (Alfa) dari semua mahasiswa binaan
     - Daftar mahasiswa dengan detail:
       - Nama, NIM, Kelas
       - Jumlah ketidakhadiran (Alfa) per mahasiswa

5. **Menghapus Mahasiswa dari Dosen**
   - Klik tombol "Hapus" pada mahasiswa yang ingin dihapus
   - Konfirmasi penghapusan

### Untuk Dosen

1. **Login sebagai Dosen**
   - Akses halaman login
   - Masuk dengan kredensial dosen

2. **Melihat Dashboard**
   - Di halaman dashboard, akan muncul section "Statistik Mahasiswa Binaan"
   - Ditampilkan:
     - Total Mahasiswa Binaan
     - Total Ketidakhadiran (Alfa)
     
3. **Melihat Detail Mahasiswa**
   - Tabel akan menampilkan semua mahasiswa yang ditugaskan dengan statistik:
     - Jumlah Alfa (tidak hadir)
     - Jumlah Hadir
     - Jumlah Terlambat
     - Jumlah Izin

## Fitur

### Admin Panel (`assign_mahasiswa_dosen.php`)
- ✅ Tampilan card untuk setiap dosen
- ✅ Modal untuk memilih mahasiswa (checkbox)
- ✅ Statistik real-time ketidakhadiran
- ✅ Hapus mahasiswa dari dosen
- ✅ Responsif dan user-friendly
- ✅ Menghindari duplikasi penugasan

### Dosen Dashboard (`dashboardDosen.php`)
- ✅ Statistik mahasiswa binaan di dashboard
- ✅ Total ketidakhadiran (Alfa)
- ✅ Tabel detail per mahasiswa dengan semua status absensi
- ✅ Notifikasi jika belum ada mahasiswa yang ditugaskan

### Database
- ✅ Relasi many-to-many antara dosen dan mahasiswa
- ✅ Cascade delete (jika dosen/mahasiswa dihapus, relasi ikut terhapus)
- ✅ Unique constraint untuk mencegah duplikasi
- ✅ Timestamp untuk tracking kapan mahasiswa ditugaskan

## File yang Dibuat/Dimodifikasi

### File Baru:
1. `migration_dosen_mahasiswa.sql` - SQL migration untuk tabel relasi
2. `assign_mahasiswa_dosen.php` - Halaman admin untuk assign mahasiswa
3. `README_ASSIGN_MAHASISWA.md` - Dokumentasi fitur ini

### File yang Dimodifikasi:
1. `sidebar_admin.php` - Menambah menu "Assign Mahasiswa ke Dosen"
2. `dashboardDosen.php` - Menambah section statistik mahasiswa binaan

## Troubleshooting

### Tabel tidak ada
- Pastikan migration SQL sudah dijalankan
- Periksa koneksi database di `koneksi.php`

### Data tidak muncul
- Pastikan sudah ada data dosen dan mahasiswa di database
- Pastikan sudah melakukan assignment di halaman admin

### Error foreign key
- Pastikan tabel `dosen_231051` dan `mahasiswa_231051` sudah ada
- Pastikan kolom `id_dosen_231051` dan `id_mahasiswa_231051` memiliki tipe data yang sama

## Screenshot Fitur

### Admin - Assign Mahasiswa
- Daftar semua dosen dengan card
- Tombol tambah mahasiswa per dosen
- Statistik total mahasiswa dan ketidakhadiran
- Daftar mahasiswa dengan jumlah alfa per mahasiswa

### Dosen - Dashboard
- Kartu statistik mahasiswa binaan
- Kartu total ketidakhadiran
- Tabel detail mahasiswa dengan semua status absensi

## Pengembangan Selanjutnya

Beberapa fitur yang bisa ditambahkan:
- Export data mahasiswa binaan ke Excel/PDF
- Notifikasi otomatis jika mahasiswa alfa lebih dari X kali
- Filter dan pencarian mahasiswa di halaman assign
- Bulk assignment (assign multiple students to multiple dosen)
- History penugasan mahasiswa
