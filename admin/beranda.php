<?php
// session_start(); // Aktifkan jika butuh sesi admin

// Koneksi database (aktifkan saat sudah siap)
// require_once '../database.php';
// $koneksi = koneksiDatabase("red bear");
// $query = "SELECT * FROM transaksi";
// $result = $koneksi->query($query);

// Contoh data dummy untuk tampilan awal
$result = new ArrayObject([
    ['id' => 1, 'nama_user' => 'Budi', 'produk' => 'Nasi Goreng', 'jumlah' => 2, 'status' => 'Belum Bayar'],
    ['id' => 2, 'nama_user' => 'Siti', 'produk' => 'Es Teh', 'jumlah' => 1, 'status' => 'Terkonfirmasi']
], ArrayObject::ARRAY_AS_PROPS);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Beranda Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/style.css">

</head>

<body>
    <div class="overlay">
        <h2>Selamat Datang di Halaman Admin</h2>

        <!-- Tombol Navigasi di Tengah -->
        <div class="button-container">
            <a href="../admin/menu/menu.php" class="button">Ke Halaman Menu</a>
            <a href="../admin/order/order.php" class="button">Ke Halaman Order</a>
        </div>

        <h3 style="text-align:center; color:white; margin-top:40px;">Daftar Transaksi Pembelian</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Nama User</th>
                <th>Produk</th>
                <th>Jumlah</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
            <?php foreach ($result as $row): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['nama_user'] ?></td>
                    <td><?= $row['produk'] ?></td>
                    <td><?= $row['jumlah'] ?></td>
                    <td><?= $row['status'] ?></td>
                    <td class="aksi">
                        <a href="detail_transaksi.php?id=<?= $row['id'] ?>">Lihat</a> |
                        <a href="hapus_transaksi.php?id=<?= $row['id'] ?>"
                            onclick="return confirm('Yakin ingin menghapus?')">Hapus</a> |
                        <?php if ($row['status'] != 'Terkonfirmasi'): ?>
                            <a href="konfirmasi_transaksi.php?id=<?= $row['id'] ?>">Konfirmasi</a>
                        <?php else: ?>
                            <span style="color:green;">Terkonfirmasi</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

            <!-- Di dalam div button-container atau di mana pun Anda ingin menempatkannya -->
        <div class="button-container">
            <a href="../admin/menu/menu.php" class="button">Ke Halaman Menu</a>
            <a href="../admin/order/order.php" class="button">Ke Halaman Order</a>
            <a href="../admin/blog/manage_blog.php" class="button">Kelola Blog</a> <!-- Tambahkan ini -->
        </div>
        
</body>

</html>