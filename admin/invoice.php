<?php
require_once __DIR__ . '/../database.php'; // pastikan file koneksi benar

$query = "
    SELECT o.id, u.name AS nama_user, o.status, o.created_at
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
";

$result = $koneksi->query($query);
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
                    <th class="px-4 py-2 text-left">Tanggal</th>
                    <th class="px-4 py-2 text-left">Status</th>
                    <th class="px-4 py-2 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr class="border-t">
                        <td class="px-4 py-2"><?= $row['id'] ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($row['nama_user']) ?></td>
                        <td class="px-4 py-2"><?= $row['created_at'] ?></td>
                        <td class="px-4 py-2"><?= ucfirst($row['status']) ?></td>
                        <td class="px-4 py-2">
                            <?php if ($row['status'] != 'selesai'): ?>
                                <a href="#" class="text-blue-500">Konfirmasi</a>
                            <?php else: ?>
                                <span class="text-green-600">Selesai</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
