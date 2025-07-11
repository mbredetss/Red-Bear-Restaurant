# README INISIALISASI

## Cara Inisialisasi & Menjalankan Proyek

1. **Ekspor Database**
   - Buka folder `database` pada proyek ini.
   - Temukan file `red_bear.sql`.
   - Ekspor/import file `red_bear.sql` ke database MySQL Anda menggunakan phpMyAdmin atau command line.
   - Contoh via command line:
     ```bash
     mysql -u [username] -p [nama_database] < database/red_bear.sql
     ```
   - Pastikan pengaturan koneksi database di file `database.php` sudah sesuai dengan konfigurasi server Anda.

2. **Jalankan Proyek**
   - Setelah database berhasil diimpor, Anda dapat langsung menjalankan website ini di server lokal (misal: XAMPP/Laragon) atau server hosting yang mendukung PHP dan MySQL.
   - Akses website melalui browser ke alamat sesuai konfigurasi server Anda.

3. **Selesai!**
   - Website siap digunakan tanpa konfigurasi tambahan.

---

Jika mengalami kendala, pastikan:
- Versi PHP dan MySQL sudah sesuai standar umum (PHP 7.4+ direkomendasikan).
- File dan folder memiliki permission yang cukup.
- Sudah mengimpor database dengan benar.

Selamat menggunakan Red-Bear-Restaurant Web! 