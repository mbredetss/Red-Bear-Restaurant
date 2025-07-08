<?php
$result = new ArrayObject([
    ['id' => 1, 'nama_user' => 'Budi', 'produk' => 'Nasi Goreng', 'jumlah' => 2, 'status' => 'Belum Bayar'],
    ['id' => 2, 'nama_user' => 'Siti', 'produk' => 'Es Teh', 'jumlah' => 1, 'status' => 'Terkonfirmasi']
], ArrayObject::ARRAY_AS_PROPS);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Invoice</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 p-6">
    <h1 class="text-2xl font-bold mb-6">Daftar Transaksi</h1>
    <div class="bg-white shadow rounded overflow-x-auto">
        <table class="min-w-full table-auto">
            <thead class="bg-gray-200">
                <tr>
                    <th class="px-4 py-2 text-left">ID</th>
                    <th class="px-4 py-2 text-left">Nama User</th>
                    <th class="px-4 py-2 text-left">Produk</th>
                    <th class="px-4 py-2 text-left">Jumlah</th>
                    <th class="px-4 py-2 text-left">Status</th>
                    <th class="px-4 py-2 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row): ?>
                    <tr class="border-t">
                        <td class="px-4 py-2"><?= $row['id'] ?></td>
                        <td class="px-4 py-2"><?= $row['nama_user'] ?></td>
                        <td class="px-4 py-2"><?= $row['produk'] ?></td>
                        <td class="px-4 py-2"><?= $row['jumlah'] ?></td>
                        <td class="px-4 py-2"><?= $row['status'] ?></td>
                        <td class="px-4 py-2">
                            <?php if ($row['status'] != 'Terkonfirmasi'): ?>
                                <a href="#" class="text-blue-500">Konfirmasi</a>
                            <?php else: ?>
                                <span class="text-green-600">Terkonfirmasi</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>