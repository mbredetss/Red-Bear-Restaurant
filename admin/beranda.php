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

    <style>
        body {
            margin: 0;
            font-family: 'Playfair Display', serif;
            background: url('image1.png') no-repeat center center fixed;
            background-size: cover;
            color: white;
        }

        .overlay {
            background-color: rgba(0, 0, 0, 0.6);
            min-height: 100vh;
            padding: 60px 20px;
        }

        h2 {
            font-size: 40px;
            text-align: center;
            margin-bottom: 40px;
        }

        .button-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }

        .button {
            padding: 12px 30px;
            font-size: 16px;
            font-weight: bold;
            border: 2px solid white;
            border-radius: 30px;
            background-color: transparent;
            color: white;
            text-decoration: none;
            transition: 0.3s ease;
            cursor: pointer;
        }

        .button:hover {
            background-color: white;
            color: #333;
        }

        table {
            width: 90%;
            margin: 30px auto;
            border-collapse: collapse;
            background-color: white;
            color: #333;
            border-radius: 10px;
            overflow: hidden;
        }

        table th, table td {
            padding: 12px 15px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #3498db;
            color: white;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }

        .aksi a {
            margin: 0 5px;
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
        }

        .aksi a:hover {
            text-decoration: underline;
        }
    </style>
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
                        <a href="hapus_transaksi.php?id=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a> |
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
</body>
</html>
