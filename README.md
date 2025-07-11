# Red-Bear-Restaurant

> **PENTING:** Untuk petunjuk inisialisasi dan cara menjalankan proyek ini, silakan baca terlebih dahulu file `README_INISIALISASI.md` yang sudah disediakan di repository ini.

![alt text](<Screenshot 2025-07-09 201800.png>)

## Deskripsi Proyek: Sistem Manajemen Restoran Berbasis Web

Proyek ini adalah sebuah aplikasi web untuk manajemen restoran yang memiliki fitur lengkap, baik untuk pengelolaan internal (admin) maupun interaksi dengan pelanggan. Sistem ini dibangun menggunakan PHP di sisi backend dan kemungkinan menggunakan HTML, CSS (dengan potensi integrasi Tailwind di masa depan), serta JavaScript di sisi frontend.

### Fitur Utama:

1. **Manajemen Menu**
   - Admin dapat menambah, mengedit, menghapus, dan mengatur status menu makanan/minuman.
   - Data menu disimpan dalam file JSON dan dapat diakses melalui API.

2. **Pemesanan Meja (Book Table)**
   - Fitur untuk pelanggan memesan meja secara online.
   - Admin dapat memantau status meja, menandai meja sebagai selesai atau kosong, dan memeriksa ketersediaan meja.

3. **Manajemen Order**
   - Pelanggan dapat memesan menu yang tersedia.
   - Admin dapat memantau status pesanan, menandai pesanan sebagai selesai, dan mengelola pesanan secara real-time.

4. **Manajemen Blog**
   - Admin dapat membuat, mengedit, dan mengelola status postingan blog yang berkaitan dengan restoran (misal: promosi, artikel kuliner, dll).
   - Terdapat halaman khusus untuk menampilkan detail blog kepada pengunjung.

5. **Manajemen Saldo**
   - Fitur untuk menambah saldo, kemungkinan untuk sistem pembayaran internal atau dompet digital pelanggan.

6. **Manajemen Meja**
   - Admin dapat memantau status seluruh meja, menghasilkan QR code untuk tiap meja, dan mengelola status meja.

7. **Autentikasi Pengguna**
   - Sistem login dan registrasi untuk admin maupun pelanggan.
   - Terdapat backend untuk proses autentikasi.

8. **Database**
   - Menggunakan file SQL untuk menyimpan data penting seperti menu, pesanan, meja, blog, user, dan sesi.

9. **Frontend Interaktif**
   - Terdapat berbagai script JavaScript untuk mendukung interaksi pengguna seperti modal booking, date picker, efek animasi, dsb.

10. **Asset Management**
    - Menyimpan gambar-gambar menu, blog, dan logo restoran.

---

**Kesimpulan:**
Proyek ini adalah sistem manajemen restoran berbasis web yang cukup lengkap, mendukung operasional restoran secara digital mulai dari pemesanan meja, pengelolaan menu, manajemen pesanan, hingga publikasi blog dan sistem saldo. Sistem ini cocok untuk restoran yang ingin melakukan digitalisasi layanan dan meningkatkan efisiensi operasional serta pengalaman pelanggan.
