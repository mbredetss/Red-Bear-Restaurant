# Sistem Admin Order Management - Red Bear Restaurant

## Deskripsi
Sistem admin order management untuk mengelola pesanan pelanggan di restoran Red Bear. Sistem ini memungkinkan admin untuk melihat, mengelola, dan memperbarui status pesanan berdasarkan meja.

## Fitur Utama

### 1. Tampilan Daftar Pesanan per Meja
- Menampilkan semua meja yang memiliki sesi aktif (baik ada pesanan maupun tidak)
- Mengelompokkan pesanan berdasarkan meja
- Menampilkan statistik status pesanan per meja
- Menampilkan informasi detail meja (jumlah tamu, waktu mulai, tipe sesi)

### 2. Manajemen Status Pesanan
- **Terima Pesanan**: Mengubah status dari "menunggu" ke "memasak"
- **Tandai Sebagai Selesai**: Mengubah status dari "memasak" ke "selesai"
- **Tolak Pesanan**: Mengubah status menjadi "ditolak"

### 3. Aksi Meja
- **Selesaikan Semua**: Menyelesaikan semua pesanan aktif di meja offline
- **Kosongkan Meja**: Mengubah status meja offline menjadi tersedia kembali
- **Selesaikan Booking**: Mengubah status booking menjadi tersedia kembali

### 4. Fitur Pencarian dan Filter
- Pencarian berdasarkan nomor meja, nama menu, atau pemesan
- Filter berdasarkan status pesanan
- Tampilan real-time statistik pesanan

## Struktur File

```
admin/order/
├── order.php                 # Halaman utama admin order
├── script.js                 # JavaScript untuk interaksi
├── style.css                 # Styling khusus
├── test_buttons.html         # File test untuk debugging
├── test_simple.html          # File test sederhana
└── api/
    ├── update_order_status.php    # Update status pesanan
    ├── complete_all_orders.php    # Selesaikan semua pesanan
    ├── mark_vacant.php            # Kosongkan meja offline
    └── mark_completed.php         # Selesaikan booking
```

## Database Schema

### Tabel `orders`
```sql
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `offline_table_session_id` int(11) DEFAULT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `menu_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `catatan` text DEFAULT NULL,
  `status` enum('menunggu','memasak','selesai','ditolak') NOT NULL DEFAULT 'menunggu',
  `order_type` enum('offline','booking') NOT NULL DEFAULT 'offline',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
);
```

### Tabel `offline_table_sessions`
```sql
CREATE TABLE `offline_table_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_id` int(11) NOT NULL,
  `guest_count` int(11) NOT NULL DEFAULT 1,
  `session_code` varchar(255) NOT NULL,
  `status` enum('occupied','vacant') DEFAULT 'occupied',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
);
```

### Tabel `table_bookings`
```sql
CREATE TABLE `table_bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `guest_count` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `table_code` varchar(12) NOT NULL,
  `status` enum('booked','cancelled') DEFAULT 'booked',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
);
```

## API Endpoints

### 1. Update Order Status
**Endpoint**: `POST /admin/order/api/update_order_status.php`
**Data**: 
- `order_id` (int): ID pesanan
- `status` (string): Status baru ('menunggu', 'memasak', 'selesai', 'ditolak')

### 2. Complete All Orders
**Endpoint**: `POST /admin/order/api/complete_all_orders.php`
**Data**:
- `table_id` (int): ID meja

### 3. Mark Vacant (Kosongkan Meja)
**Endpoint**: `POST /admin/order/api/mark_vacant.php`
**Data**:
- `session_id` (int): ID sesi offline

### 4. Mark Completed (Selesaikan Booking)
**Endpoint**: `POST /admin/order/api/mark_completed.php`
**Data**:
- `table_id` (int): ID meja

## Cara Penggunaan

### 1. Akses Halaman Admin
- Login sebagai admin
- Akses `/admin/order/order.php`

### 2. Mengelola Status Pesanan
1. Cari pesanan yang ingin diupdate
2. Klik tombol sesuai aksi yang diinginkan:
   - **Terima Pesanan**: Untuk memulai memasak
   - **Tandai Sebagai Selesai**: Untuk menyelesaikan pesanan
   - **Tolak Pesanan**: Untuk menolak pesanan

### 3. Mengelola Meja
1. Pilih meja yang ingin dikelola
2. Klik tombol sesuai aksi:
   - **Selesaikan Semua**: Menyelesaikan semua pesanan di meja
   - **Kosongkan Meja**: Membuat meja tersedia kembali (offline)
   - **Selesaikan Booking**: Membuat meja tersedia kembali (booking)

## Status Pesanan

| Status | Deskripsi | Tombol Aksi |
|--------|-----------|-------------|
| menunggu | Pesanan baru, menunggu persetujuan | Terima Pesanan, Tolak Pesanan |
| memasak | Pesanan sedang diproses | Tandai Sebagai Selesai, Tolak Pesanan |
| selesai | Pesanan telah selesai | - |
| ditolak | Pesanan ditolak | - |

## Status Meja

### Offline Table Sessions
| Status | Deskripsi |
|--------|-----------|
| occupied | Meja sedang digunakan |
| vacant | Meja tersedia |

### Table Bookings
| Status | Deskripsi |
|--------|-----------|
| booked | Meja sudah di-booking |
| cancelled | Booking dibatalkan/tersedia |

## Troubleshooting

### Tombol Tidak Berfungsi
1. Buka Developer Tools (F12)
2. Lihat tab Console untuk error messages
3. Pastikan file JavaScript ter-load dengan benar
4. Cek apakah ada error di Network tab

### Data Tidak Muncul
1. Pastikan ada sesi aktif di database
2. Cek query di file `order.php`
3. Pastikan koneksi database berfungsi

### API Error
1. Cek log error PHP
2. Pastikan parameter yang dikirim benar
3. Cek apakah user sudah login sebagai admin

## Keamanan

- Semua endpoint memerlukan login admin
- Validasi input untuk mencegah SQL injection
- Prepared statements untuk query database
- Validasi status yang diizinkan

## Performa

- Query dioptimasi dengan index yang tepat
- Pagination untuk data yang besar (jika diperlukan)
- Caching untuk data yang sering diakses
- Lazy loading untuk gambar dan data

## Pengembangan Selanjutnya

1. **Notifikasi Real-time**: Menggunakan WebSocket untuk notifikasi pesanan baru
2. **Export Data**: Fitur export data pesanan ke Excel/PDF
3. **Dashboard Analytics**: Grafik dan statistik pesanan
4. **Multi-language**: Dukungan bahasa Indonesia dan Inggris
5. **Mobile App**: Aplikasi mobile untuk admin

## Kontribusi

Untuk berkontribusi pada pengembangan sistem ini:
1. Fork repository
2. Buat branch untuk fitur baru
3. Commit perubahan
4. Buat Pull Request

## Lisensi

Sistem ini dikembangkan untuk Red Bear Restaurant. Semua hak cipta dilindungi. 