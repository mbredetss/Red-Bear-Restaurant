<?php
require_once __DIR__ . '/../database.php'; // path koneksi yang benar

// Dummy data
$monthlySales = [120, 370, 180, 300, 150, 170, 290, 90, 210, 360, 280, 100];



// Ambil jumlah pelanggan (role 'user')
$queryCustomers = "SELECT COUNT(*) AS total_customers FROM users WHERE role = 'user'";
$resultCustomers = $koneksi->query($queryCustomers);
$customers = ($resultCustomers && $row = $resultCustomers->fetch_assoc()) ? $row['total_customers'] : 0;

// Ambil jumlah order
$queryOrders = "SELECT COUNT(*) AS total_orders FROM orders";
$resultOrders = $koneksi->query($queryOrders);
$orders = ($resultOrders && $row = $resultOrders->fetch_assoc()) ? $row['total_orders'] : 0;



$result = new ArrayObject([
    ['id' => 1, 'nama_user' => 'Budi', 'produk' => 'Nasi Goreng', 'jumlah' => 2, 'status' => 'Belum Bayar'],
    ['id' => 2, 'nama_user' => 'Siti', 'produk' => 'Es Teh', 'jumlah' => 1, 'status' => 'Terkonfirmasi']
], ArrayObject::ARRAY_AS_PROPS);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Beranda Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .hidden {
            display: none;
        }
    </style>
</head>

<body class="bg-gray-100 text-gray-800">
    <div class="flex h-screen">

        <!-- Sidebar -->
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r p-6 flex flex-col space-y-6">
            <h2 class="text-xl font-bold text-blue-700 mb-4">Admin Panel</h2>
            <nav class="flex flex-col gap-4">
                <a href="#" onclick="showContent('dashboard')" class="text-gray-700 hover:text-blue-600">📊
                    Dashboard</a>
                <a href="#" onclick="showContent('profile')" class="text-gray-700 hover:text-blue-600">👤 Admin
                    Profile</a>
                <a href="#" onclick="showContent('invoice')" class="text-gray-700 hover:text-blue-600">🧾 Invoice</a>
                <a href="#" onclick="showContent('laporan')" class="text-gray-700 hover:text-blue-600">📈 Laporan</a>
                <hr class="my-2">
                <a href="../home.php" class="text-gray-700 hover:text-blue-600">👥 Ke Halaman User</a>
                <a href="../admin/saldo/add_saldo.php" class="text-gray-700 hover:text-blue-600">💰 Tambah Saldo</a>
                <a href="../admin/tables/generate_qr.php" class="text-gray-700 hover:text-blue-600">📱 Generate QR
                    Meja</a>
                <a href="../admin/order/order.php" class="text-gray-700 hover:text-blue-600">🛒 Ke Halaman Order</a>
                <a href="../admin/menu/menu.php" class="text-gray-700 hover:text-blue-600">📜 Tambah Menu</a>
        </aside>


        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-y-auto">

            <!-- Topbar -->
            <header class="bg-white shadow p-4 flex justify-end items-center">
                <div class="relative">
                    <button onclick="toggleDropdown()" class="flex items-center gap-2 focus:outline-none">
                        <img src="https://i.pravatar.cc/40" class="w-10 h-10 rounded-full" />
                        <span class="font-semibold">Musharof</span>
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M19 9l-7 7-7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                    <div id="dropdown" class="hidden absolute right-0 mt-2 w-56 bg-white border rounded-lg shadow z-50">
                        <div class="p-4">
                            <p class="font-semibold">Musharof Chowdhury</p>
                            <p class="text-sm text-gray-500">randomuser@pimjo.com</p>
                        </div>
                        <hr>
                        <a href="#" onclick="showContent('profile')" class="block px-4 py-2 hover:bg-gray-100">👤 Edit
                            Profile</a>
                        <a href="#" class="block px-4 py-2 hover:bg-gray-100">⚙ Account Settings</a>
                        <a href="#" class="block px-4 py-2 hover:bg-gray-100">❓ Support</a>
                        <hr>
                        <a href="logout.php" class="block px-4 py-2 text-red-500 hover:bg-gray-100">🚪 Sign Out</a>
                    </div>
                </div>
            </header>

            <!-- Dynamic Content Container -->
            <main class="p-6 space-y-8">

                <!-- Dashboard -->
                <div id="content-dashboard">
                    <h1 class="text-2xl font-bold mb-4">Dashboard</h1>
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-6">
                        <div class="bg-white p-4 rounded shadow">
                            <p class="text-gray-500">Customers</p>
                            <h2 class="text-2xl font-bold"><?= $customers ?></h2>
                            <p class="text-green-500 mt-2">⬆ 11.01%</p>
                        </div>
                        <div class="bg-white p-4 rounded shadow">
                            <p class="text-gray-500">Orders</p>
                            <h2 class="text-2xl font-bold"><?= $orders ?></h2>
                            <p class="text-red-500 mt-2">⬇ 9.05%</p>
                        </div>
                        <div class="bg-white p-4 rounded shadow">
                            <p class="text-gray-500 mb-2">Monthly Target</p>
                            <canvas id="progressChart"></canvas>
                            <p class="mt-2 text-center text-green-600 font-semibold">75.55%</p>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded shadow mb-6">
                        <h2 class="text-xl font-semibold mb-4">Monthly Sales</h2>
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>

                <!-- Profile -->
                <div id="content-profile" class="hidden">
                    <div class="bg-white p-6 rounded shadow">
                        <h2 class="text-xl font-bold mb-4">Admin Profile</h2>
                        <div class="flex gap-6 mb-4">
                            <img src="https://i.pravatar.cc/100" class="rounded-full w-24 h-24" />
                            <div>
                                <p class="text-lg font-semibold">Musharof Chowdhury</p>
                                <p class="text-gray-500">randomuser@pimjo.com</p>
                                <p class="text-gray-500">+09 363 398 46</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div><strong>Nama:</strong> Musharof</div>
                            <div><strong>Alamat:</strong> Arizona, United States</div>
                            <div><strong>No HP:</strong> +09 363 398 46</div>
                            <div><strong>Email:</strong> randomuser@pimjo.com</div>
                        </div>
                    </div>
                </div>

                <!-- Invoice -->
                <div id="content-invoice" class="hidden">
                    <h2 class="text-xl font-bold mb-4">Riwayat Transaksi</h2>
                    <table class="min-w-full bg-white rounded shadow">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left">ID</th>
                                <th class="px-4 py-2 text-left">Nama</th>
                                <th class="px-4 py-2 text-left">Produk</th>
                                <th class="px-4 py-2 text-left">Jumlah</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2 text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result as $r): ?>
                                <tr class="border-t">
                                    <td class="px-4 py-2"><?= $r['id'] ?></td>
                                    <td class="px-4 py-2"><?= $r['nama_user'] ?></td>
                                    <td class="px-4 py-2"><?= $r['produk'] ?></td>
                                    <td class="px-4 py-2"><?= $r['jumlah'] ?></td>
                                    <td class="px-4 py-2"><?= $r['status'] ?></td>
                                    <td class="px-4 py-2">
                                        <?php if ($r['status'] != 'Terkonfirmasi'): ?>
                                            <a href="#" class="text-blue-600">Konfirmasi</a>
                                        <?php else: ?>
                                            <span class="text-green-600">Terkonfirmasi</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Laporan -->
                <div id="content-laporan" class="hidden">
    <h2 class="text-xl font-bold mb-4">Laporan Penjualan</h2>

    <?php
    require_once __DIR__ . '/../database.php';

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

    // Simpan juga sebagai JSON
    file_put_contents(__DIR__ . '/laporan.json', json_encode($data, JSON_PRETTY_PRINT));
    ?>

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
</div>



            </main>

            <!-- Tombol Navigasi di Tengah -->

        </div>

        <script>
            function toggleDropdown() {
                document.getElementById('dropdown').classList.toggle('hidden');
            }

            function showContent(id) {
                const contents = ['dashboard', 'profile', 'invoice', 'laporan'];
                contents.forEach(c => {
                    document.getElementById('content-' + c).classList.add('hidden');
                });
                document.getElementById('content-' + id).classList.remove('hidden');
            }

            // Sales Chart
            const ctx = document.getElementById('salesChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Sales',
                        data: <?= json_encode($monthlySales) ?>,
                        backgroundColor: '#6366F1'
                    }]
                },
                options: { responsive: true, scales: { y: { beginAtZero: true } } }
            });

            // Progress Chart
            const progressCtx = document.getElementById('progressChart').getContext('2d');
            new Chart(progressCtx, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [75.55, 24.45],
                        backgroundColor: ['#6366F1', '#E5E7EB'],
                        borderWidth: 0
                    }]
                },
                options: {
                    cutout: '80%',
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false }
                    }
                }
            });

            
        </script>
</body>

</html>