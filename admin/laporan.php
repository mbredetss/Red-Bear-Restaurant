<?php
require_once __DIR__ . '/../database.php';

// Ambil data 7 hari terakhir
$query = "
    SELECT u.name AS nama_user, o.created_at, o.status
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ORDER BY o.created_at DESC
";

$result = $koneksi->query($query);
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = [
        'nama' => $row['nama_user'],
        'tanggal' => $row['created_at'],
        'status' => $row['status']
    ];
}

// Simpan JSON
file_put_contents(__DIR__ . '/laporan.json', json_encode($data, JSON_PRETTY_PRINT));
?>

<h2 class="text-xl font-bold mb-4">Laporan Penjualan</h2>

<?php if (count($data) > 0): ?>
<table class="min-w-full bg-white rounded shadow mb-4">
    <thead class="bg-gray-100">
        <tr>
            <th class="px-4 py-2 text-left">Nama</th>
            <th class="px-4 py-2 text-left">Tanggal</th>
            <th class="px-4 py-2 text-left">Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data as $row): ?>
            <tr class="border-t">
                <td class="px-4 py-2"><?= htmlspecialchars($row['nama']) ?></td>
                <td class="px-4 py-2"><?= $row['tanggal'] ?></td>
                <td class="px-4 py-2"><?= $row['status'] ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Tombol Export -->
<a href="export-laporan.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
    📝 Export to Word
</a>
<?php else: ?>
    <p class="text-gray-600">Tidak ada transaksi dalam 7 hari terakhir.</p>
<?php endif; ?>
