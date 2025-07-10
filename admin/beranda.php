<?php
require_once __DIR__ . '/../database.php';

// Menangani konfirmasi pesanan
if (isset($_GET['konfirmasi_id'])) {
    $konfirmasi_id = (int) $_GET['konfirmasi_id'];
    $koneksi->query("UPDATE orders SET status = 'selesai' WHERE id = $konfirmasi_id");

    // Redirect agar URL kembali bersih
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}



$monthlySales = array_fill(0, 12, 0); // Indeks dari 0 agar cocok dengan JS

$queryMonthlySales = "
    SELECT MONTH(created_at) AS bulan, COUNT(*) AS total
    FROM orders
    WHERE YEAR(created_at) = YEAR(CURDATE())
    GROUP BY MONTH(created_at)
";

$resultMonthly = $koneksi->query($queryMonthlySales);
while ($row = $resultMonthly->fetch_assoc()) {
    $bulan = (int) $row['bulan']; // bulan: 1-12
    $monthlySales[$bulan - 1] = (int) $row['total']; // array index 0-11
}



require_once __DIR__ . '/../database.php'; // path koneksi yang benar

// ======= Cek Kode Meja (Dimasukkan ke beranda.php) =======
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['check_table_submit'])) {
    $table_code = $_POST['table_code'] ?? '';
    if (!empty($table_code)) {
        $stmt = $koneksi->prepare("
            SELECT 
                tb.id AS booking_id,
                tb.table_code,
                u.name AS pelanggan,
                u.email,
                u.phone,
                tb.booking_date,
                tb.booking_time,
                tb.status,
                tb.jumlah_tamu,
                tb.catatan
            FROM table_bookings tb
            INNER JOIN users u ON tb.user_id = u.id
            WHERE tb.table_code = ?
        ");
        $stmt->bind_param("s", $table_code);
        $stmt->execute();
        $result = $stmt->get_result();
        $booking = $result->fetch_assoc();
        $stmt->close();
    } else {
        $booking = false;
    }
}

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
        <aside class="w-64 bg-white border-r p-6 flex flex-col space-y-6">
            <h2 class="text-xl font-bold text-blue-700 mb-4">Admin Panel</h2>
            <nav class="flex flex-col gap-4">
                <a href="#" onclick="showContent('dashboard')" class="text-gray-700 hover:text-blue-600">📊
                    Dashboard</a>
                <a href="#" onclick="showContent('profile')" class="text-gray-700 hover:text-blue-600">👤 Admin
                    Profile</a>
                <a href="#" onclick="showContent('invoice')" class="text-gray-700 hover:text-blue-600">🧾 Invoice</a>
                <a href="#" onclick="showContent('laporan')" class="text-gray-700 hover:text-blue-600">📈 Laporan</a>
                <a href="#" onclick="showContent('checkkode')" class="text-gray-700 hover:text-blue-600">🔍 Cek Kode
                    Meja</a>
                <hr class="my-2">
                <a href="../home.php" class="text-gray-700 hover:text-blue-600">👥 Ke Halaman User</a>
                <a href="../admin/saldo/add_saldo.php" class="text-gray-700 hover:text-blue-600">💰 Tambah Saldo</a>
                <a href="../admin/tables/generate_qr.php" class="text-gray-700 hover:text-blue-600">📱 Generate QR
                    Meja</a>
                <a href="../admin/order/order.php" class="text-gray-700 hover:text-blue-600">🛒 Ke Halaman Order</a>
                <a href="../admin/menu/menu.php" class="text-gray-700 hover:text-blue-600">📜 Tambah Menu</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-y-auto">



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
                <!-- Invoice -->
                <div id="content-invoice" class="hidden">
                    <h2 class="text-xl font-bold mb-4">Riwayat Transaksi</h2>

                    <?php
                    $queryInvoice = "
        SELECT o.id, u.name AS nama_user, o.status, o.created_at
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC
    ";
                    $invoiceResult = $koneksi->query($queryInvoice);
                    ?>

                    <table class="min-w-full bg-white rounded shadow">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left">ID</th>
                                <th class="px-4 py-2 text-left">Nama</th>
                                <th class="px-4 py-2 text-left">Tanggal</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2 text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $invoiceResult->fetch_assoc()): ?>
                                <tr class="border-t">
                                    <td class="px-4 py-2"><?= $row['id'] ?></td>
                                    <td class="px-4 py-2"><?= htmlspecialchars($row['nama_user']) ?></td>
                                    <td class="px-4 py-2"><?= $row['created_at'] ?></td>
                                    <td class="px-4 py-2"><?= ucfirst($row['status']) ?></td>
                                    <td class="px-4 py-2">
                                        <?php if ($row['status'] != 'selesai'): ?>
                                            <a href="?konfirmasi_id=<?= $row['id'] ?>"
                                                class="text-blue-600 hover:underline">Konfirmasi</a>
                                        <?php else: ?>
                                            <span class="text-green-600">Selesai</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>



                <!-- Laporan -->
                <div id="content-laporan" class="hidden">
                    <h2 class="text-xl font-bold mb-4">Laporan Penjualan</h2>

                    <?php
                    // Ambil filter periode dari URL, default "harian"
                    $periode = $_GET['periode'] ?? 'harian';
                    switch ($periode) {
                        case 'minggu':
                            $interval = '7 DAY';
                            break;
                        case 'bulan':
                            $interval = '30 DAY';
                            break;
                        case 'harian':
                        default:
                            $interval = '1 DAY';
                            break;
                    }

                    // Query laporan: ambil order dengan created_at >= NOW() - interval yang dipilih
                    $query = "
        SELECT u.name AS nama_user, o.created_at, o.status
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL $interval)
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
                    // Simpan data laporan ke file JSON (untuk digunakan eksport Word)
                    file_put_contents(__DIR__ . '/laporan.json', json_encode($data, JSON_PRETTY_PRINT));
                    ?>

                    <!-- Dropdown filter waktu (gunakan parameter 'periode') -->
                    <form method="GET" class="mb-4">
                        <label for="periode" class="mr-2 font-semibold">Filter Waktu:</label>
                        <select name="periode" id="periode" class="border rounded px-2 py-1"
                            onchange="this.form.submit()">
                            <option value="harian" <?= $periode == 'harian' ? 'selected' : '' ?>>Harian</option>
                            <option value="minggu" <?= $periode == 'minggu' ? 'selected' : '' ?>>Mingguan</option>
                            <option value="bulan" <?= $periode == 'bulan' ? 'selected' : '' ?>>Bulanan</option>
                        </select>
                    </form>

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
                    <?php else: ?>
                        <p class="text-gray-600 mb-4">Tidak ada transaksi pada periode ini.</p>
                    <?php endif; ?>

                    <!-- Tombol Export (selalu tampil meskipun data kosong) -->
                    <a href="export-laporan.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        📝 Export to Word
                    </a>
                </div>
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
                        <a href="export-laporan.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            📝 Export to Word
                        </a>
                    <?php else: ?>
                        <p class="text-gray-600">Tidak ada transaksi dalam 7 hari terakhir.</p>
                    <?php endif; ?>
                </div>
                <!-- Check Kode Meja -->
                <div id="content-checkkode" class="<?= (isset($_POST['check_table_submit']) ? '' : 'hidden') ?>">
                    <div class="bg-white p-6 rounded shadow">
                        <h2 class="text-xl font-bold mb-4">Cek Kode Meja</h2>
                        <form method="POST" class="flex gap-4 mb-4">
                            <input type="text" name="table_code" placeholder="Masukkan Kode Meja" required
                                class="border px-4 py-2 rounded w-64 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            <button type="submit" name="check_table_submit"
                                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                                🔍 Cek
                            </button>
                        </form>

                        <?php if (isset($booking) && $booking): ?>
                            <table class="min-w-full bg-white rounded shadow mb-4">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-2">Kode Meja</th>
                                        <th class="px-4 py-2">Pelanggan</th>
                                        <th class="px-4 py-2">Email</th>
                                        <th class="px-4 py-2">No HP</th>
                                        <th class="px-4 py-2">Tanggal</th>
                                        <th class="px-4 py-2">Waktu</th>
                                        <th class="px-4 py-2">Status</th>
                                        <th class="px-4 py-2">Jumlah Tamu</th>
                                        <th class="px-4 py-2">Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-t">
                                        <td class="px-4 py-2"><?= htmlspecialchars($booking['table_code']) ?></td>
                                        <td class="px-4 py-2"><?= htmlspecialchars($booking['pelanggan']) ?></td>
                                        <td class="px-4 py-2"><?= htmlspecialchars($booking['email']) ?></td>
                                        <td class="px-4 py-2"><?= htmlspecialchars($booking['phone'] ?? '-') ?></td>
                                        <td class="px-4 py-2"><?= htmlspecialchars($booking['booking_date']) ?></td>
                                        <td class="px-4 py-2"><?= htmlspecialchars($booking['booking_time']) ?></td>
                                        <td class="px-4 py-2"><?= htmlspecialchars($booking['status']) ?></td>
                                        <td class="px-4 py-2"><?= htmlspecialchars($booking['jumlah_tamu']) ?></td>
                                        <td class="px-4 py-2"><?= htmlspecialchars($booking['catatan'] ?? '-') ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        <?php elseif (isset($booking) && !$booking): ?>
                            <p class="text-red-500">Kode meja tidak valid atau tidak ditemukan.</p>
                        <?php endif; ?>
                    </div>
                </div>

            </main>
        </div>

        <script>
            function toggleDropdown() {
                document.getElementById('dropdown').classList.toggle('hidden');
            }

            function showContent(id) {
                const contents = ['dashboard', 'profile', 'invoice', 'laporan', 'checkkode'];
                contents.forEach(c => {
                    document.getElementById('content-' + c).classList.add('hidden');
                });
                document.getElementById('content-' + id).classList.remove('hidden');
            }

            // Sales Chart - Bar Chart (bulanan)
            const ctx = document.getElementById('salesChart').getContext('2d');
            const salesData = <?= json_encode(array_values($monthlySales)) ?>;
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Sales',
                        data: salesData,
                        backgroundColor: '#6366F1'
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });

            // Progress Chart - Monthly Target
            const currentMonth = new Date().getMonth(); // 0 = Jan, 11 = Dec
            const currentMonthSales = salesData[currentMonth] || 0;
            const target = 100;
            const percent = Math.min((currentMonthSales / target) * 100, 100).toFixed(2);

            const progressCtx = document.getElementById('progressChart').getContext('2d');
            new Chart(progressCtx, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [percent, 100 - percent],
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

            // Tampilkan persen ke elemen text
            document.addEventListener("DOMContentLoaded", () => {
                const text = document.querySelector('#progressChart').closest('div').querySelector('p.text-center');
                if (text) text.innerText = `${percent}%`;
            });
        </script>

</body>

</html>