<?php
// require_once '../database.php';

// $koneksi = koneksiDatabase("red bear");
// // Ambil semua data transaksi
// $query = "SELECT * FROM transaksi";
// $result = $koneksi->query($query);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Beranda Admin</title>
</head>

<body>
    <h2>Selamat Datang di Halaman Admin</h2>

    <!-- Tombol Navigasi -->
    <button onclick="location.href='../admin/menu/menu.php'">Ke Halaman Menu</button>
    <button onclick="location.href='../admin/order/order.php'">Ke Halaman Order</button>

    <h3>Daftar Transaksi Pembelian</h3>
    <table  cellpadding="8" cellspacing="0" border="1">
        <tr>
            <th>ID</th>
            <th>Nama User</th>
            <th>Produk</th>
            <th>Jumlah</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['nama_user'] ?></td>
                <td><?= $row['produk'] ?></td>
                <td><?= $row['jumlah'] ?></td>
                <td><?= $row['status'] ?></td>
                <td>
                    <a href="detail_transaksi.php?id=<?= $row['id'] ?>">Lihat</a> |
                    <a href="hapus_transaksi.php?id=<?= $row['id'] ?>"
                        onclick="return confirm('Yakin ingin menghapus?')">Hapus</a> |
                    <?php if ($row['status'] != 'Terkonfirmasi'): ?>
                        <a href="konfirmasi_transaksi.php?id=<?= $row['id'] ?>">Konfirmasi</a>
                    <?php else: ?>
                        <span>Terkonfirmasi</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>

</html>