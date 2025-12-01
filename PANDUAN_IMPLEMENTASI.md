# Panduan Implementasi Fitur Baru Absensi

## Fitur yang Diimplementasikan

### 1. Otomatis Mencatat Mahasiswa yang Tidak Absen sebagai Terlambat
- Ketika waktu kelas berakhir, mahasiswa yang belum absen akan otomatis dicatat dengan status "Terlambat"
- Sistem menghitung berapa menit keterlambatan sejak jam mulai kelas

### 2. Dosen Dapat Mencatat Mahasiswa sebagai Sakit
- Dosen dapat mengganti status absensi mahasiswa menjadi "Sakit" dari dashboard
- Ditandai dengan ikon 🤒 dan warna ungu pada tampilan

## File yang Diubah

1. `dashboardDosen.php` - Menambahkan fitur untuk mengganti status menjadi sakit dan otomatis menandai mahasiswa yang tidak hadir sebagai terlambat
2. `dashboardMahasiswa.php` - Memperbarui tampilan status absensi untuk menampilkan "Sakit" 
3. `data_absensi.php` - Memperbarui form untuk mendukung status "Sakit"
4. `updateStatus.php` - File untuk menangani perubahan status absensi melalui AJAX
5. `migration_add_sakit_status.sql` - File migrasi database untuk menambahkan opsi "Sakit" ke kolom status
6. `update_database.php` - Script PHP untuk menjalankan migrasi database

## Database Migration

Untuk menerapkan perubahan pada database, Anda perlu menjalankan perintah SQL berikut:

```sql
-- Update kolom status untuk menambahkan 'Sakit' ke ENUM
ALTER TABLE absensi_231051 MODIFY COLUMN status_231051 ENUM('Hadir', 'Terlambat', 'Alfa', 'Izin', 'Sakit') DEFAULT 'Alfa';

-- Tambahkan kolom catatan untuk informasi tambahan
ALTER TABLE absensi_231051 ADD COLUMN catatan_231051 TEXT DEFAULT NULL COMMENT 'Catatan tambahan untuk status khusus seperti sakit/izin';
```

### Cara Menjalankan Migrasi:
1. Buka phpMyAdmin atau MySQL client favorit Anda
2. Pilih database `absensiqr_231051`
3. Jalankan SQL di atas di SQL tab

## Fitur Lengkap

### Otomatis Tandai Terlambat
- Sistem akan memeriksa jadwal kelas saat membuka tab absensi
- Jika waktu saat ini sudah melewati waktu selesai kelas, mahasiswa yang belum absen akan otomatis dicatat sebagai "Terlambat"
- Keterlambatan dihitung dari waktu mulai kelas hingga waktu selesai kelas

### Fungsi Dosen untuk Mengganti Status
- Dalam tabel absensi di tab "Absensi", muncul dropdown baru untuk setiap baris
- Dosen dapat memilih antara Hadir, Terlambat, Alfa, Izin, atau Sakit
- Setelah mengganti, sistem akan menampilkan konfirmasi dan memperbarui status secara real-time

## Pengujian
- Buka dashboard dosen
- Periksa apakah kolom status sekarang memiliki pilihan "Sakit"
- Pastikan tampilan statistik menampilkan jumlah status sakit
- Uji fungsi mengganti status dari dropdown

## Catatan
- Pastikan database dimigrasi sebelum menggunakan fitur baru
- Fitur ini hanya tersedia untuk dosen
- Ikon digunakan untuk membedakan status: ✅ Hadir, ⏰ Terlambat, 🚫 Alfa, 📋 Izin, 🤒 Sakit