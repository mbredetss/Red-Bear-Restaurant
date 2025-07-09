<?php
require_once '../../database.php';
session_start();

// Cek login dan peran admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../home.php');
    exit;
}

$koneksi = koneksiDatabase('red bear');

// Ambil data pesanan dikelompokkan berdasarkan meja
$query = "
    SELECT 
        t.table_number,
        t.id as table_id,
        COALESCE(ots.guest_count, tb.guest_count) as guest_count,
        COALESCE(ots.session_code, tb.table_code) as session_code,
        COALESCE(ots.created_at, tb.created_at) as session_created,
        COALESCE(ots.id, tb.id) as session_id,
        COUNT(o.id) as total_orders,
        SUM(CASE WHEN o.status = 'menunggu' THEN 1 ELSE 0 END) as pending_orders,
        SUM(CASE WHEN o.status = 'memasak' THEN 1 ELSE 0 END) as cooking_orders,
        SUM(CASE WHEN o.status = 'selesai' THEN 1 ELSE 0 END) as completed_orders,
        SUM(CASE WHEN o.status = 'ditolak' THEN 1 ELSE 0 END) as rejected_orders,
        CASE 
            WHEN ots.id IS NOT NULL THEN 'offline'
            WHEN tb.id IS NOT NULL THEN 'booking'
            ELSE 'unknown'
        END as order_type
    FROM tables t
    LEFT JOIN offline_table_sessions ots ON t.id = ots.table_id AND ots.status = 'occupied'
    LEFT JOIN table_bookings tb ON t.id = tb.table_id AND tb.booking_date = CURDATE() AND tb.status = 'booked'
    LEFT JOIN orders o ON (ots.id = o.offline_table_session_id OR tb.id = o.booking_id)
    WHERE (ots.id IS NOT NULL OR tb.id IS NOT NULL)
    GROUP BY t.id, t.table_number, ots.id, tb.id, ots.guest_count, tb.guest_count, ots.session_code, tb.table_code, ots.created_at, tb.created_at
    ORDER BY t.table_number ASC, session_created DESC
";

$result = $koneksi->query($query);
$tableGroups = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Tampilkan semua meja yang memiliki sesi aktif (baik ada pesanan maupun tidak)
        $tableGroups[] = $row;
    }
}

// Ambil detail pesanan untuk setiap meja
$detailedOrders = [];
foreach ($tableGroups as $table) {
    $detailQuery = "
        SELECT 
            o.id,
            o.menu_name,
            o.quantity,
            o.catatan,
            o.status,
            o.created_at,
            o.username,
            o.order_type
        FROM orders o
        WHERE (o.offline_table_session_id = ? OR o.booking_id = ?)
        ORDER BY o.created_at DESC
    ";
    
    $stmt = $koneksi->prepare($detailQuery);
    $stmt->bind_param('ii', $table['session_id'], $table['session_id']);
    $stmt->execute();
    $detailResult = $stmt->get_result();
    
    $orders = [];
    while ($order = $detailResult->fetch_assoc()) {
        $orders[] = $order;
    }
    
    $detailedOrders[$table['table_number']] = $orders;
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Kelola Pesanan</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-gray-900">Kelola Pesanan</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="../beranda.php" class="text-gray-600 hover:text-gray-900">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-clock text-yellow-500 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Menunggu</dt>
                                    <dd class="text-lg font-medium text-gray-900" id="count-menunggu">0</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-fire text-orange-500 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Sedang Dimasak</dt>
                                    <dd class="text-lg font-medium text-gray-900" id="count-memasak">0</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Selesai</dt>
                                    <dd class="text-lg font-medium text-gray-900" id="count-selesai">0</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-times-circle text-red-500 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Ditolak</dt>
                                    <dd class="text-lg font-medium text-gray-900" id="count-ditolak">0</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="search-orders" class="block text-sm font-medium text-gray-700 mb-2">Cari Pesanan</label>
                        <input type="text" id="search-orders" placeholder="Cari berdasarkan nomor meja, nama menu, atau pemesan..." 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-2">Filter Status</label>
                        <select id="status-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="all">Semua Status</option>
                            <option value="menunggu">Menunggu</option>
                            <option value="memasak">Sedang Dimasak</option>
                            <option value="selesai">Selesai</option>
                            <option value="ditolak">Ditolak</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Daftar Pesanan per Meja</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Kelola status pesanan pelanggan berdasarkan meja</p>
                </div>
                
                <?php if (empty($tableGroups)): ?>
                <div class="p-8 text-center">
                    <div class="text-gray-500">
                        <i class="fas fa-utensils text-4xl mb-4"></i>
                        <p class="text-lg">Belum ada pesanan aktif</p>
                        <p class="text-sm">Pesanan akan muncul di sini setelah pelanggan melakukan pemesanan</p>
                    </div>
                </div>
                <?php else: ?>
                
                <div class="space-y-6 p-6">
                    <?php foreach ($tableGroups as $table): ?>
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <!-- Table Header -->
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="flex items-center justify-center w-12 h-12 bg-blue-500 text-white rounded-full font-bold text-lg">
                                        <?= $table['table_number'] ?>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900">Meja <?= $table['table_number'] ?></h4>
                                        <div class="flex items-center space-x-4 text-sm text-gray-600">
                                            <span><i class="fas fa-users mr-1"></i><?= $table['guest_count'] ?> tamu</span>
                                            <span><i class="fas fa-clock mr-1"></i><?= date('d/m/Y H:i', strtotime($table['session_created'])) ?></span>
                                            <span><i class="fas fa-shopping-cart mr-1"></i><?= $table['total_orders'] ?> pesanan</span>
                                            <?php if ($table['order_type'] === 'booking'): ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <i class="fas fa-calendar-check mr-1"></i>Booking Online
                                            </span>
                                            <?php else: ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-qrcode mr-1"></i>Scan QR
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Status Summary -->
                                <div class="flex items-center space-x-3">
                                    <?php if ($table['pending_orders'] > 0): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-clock mr-1"></i><?= $table['pending_orders'] ?> menu menunggu persetujuan
                                    </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($table['cooking_orders'] > 0): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                        <i class="fas fa-fire mr-1"></i><?= $table['cooking_orders'] ?> sedang di masak
                                    </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($table['completed_orders'] > 0): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check mr-1"></i><?= $table['completed_orders'] ?> menu telah selesai
                                    </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($table['rejected_orders'] > 0): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times mr-1"></i><?= $table['rejected_orders'] ?>menu ditolak
                                    </span>
                                    <?php endif; ?>
                                    
                                    <!-- Tombol Aksi Meja -->
                                    <div class="flex items-center space-x-2">
                                        <?php if ($table['order_type'] === 'offline'): ?>
                                        <button class="table-action-btn bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs transition-colors shadow-md" 
                                                data-table-id="<?= $table['table_id'] ?>" data-session-id="<?= $table['session_id'] ?>" data-action="complete-all">
                                            <i class="fas fa-check-double mr-1"></i>Selesaikan Semua
                                        </button>
                                        <button class="table-action-btn bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs transition-colors shadow-md" 
                                                data-table-id="<?= $table['table_id'] ?>" data-session-id="<?= $table['session_id'] ?>" data-action="vacate-table">
                                            <i class="fas fa-door-open mr-1"></i>Kosongkan Meja
                                        </button>
                                        <?php else: ?>
                                        <button class="table-action-btn bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs transition-colors shadow-md" 
                                                data-table-id="<?= $table['table_id'] ?>" data-session-id="<?= $table['session_id'] ?>" data-action="complete-booking">
                                            <i class="fas fa-check mr-1"></i>Selesaikan Booking
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Orders List -->
                        <div class="divide-y divide-gray-200">
                            <?php if (empty($detailedOrders[$table['table_number']])): ?>
                            <!-- Meja belum ada pesanan -->
                            <div class="px-6 py-8 text-center">
                                <div class="text-gray-400">
                                    <i class="fas fa-utensils text-3xl mb-3"></i>
                                    <p class="text-sm font-medium text-gray-500">Belum ada pesanan</p>
                                    <p class="text-xs text-gray-400 mt-1">Pelanggan belum memesan menu apapun</p>
                                </div>
                            </div>
                            <?php else: ?>
                            <?php foreach ($detailedOrders[$table['table_number']] as $order): ?>
                            <div class="px-6 py-4 hover:bg-gray-50 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-red-100 flex items-center justify-center">
                                                <i class="fas fa-utensils text-red-600"></i>
                                            </div>
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-3">
                                                <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($order['menu_name']) ?></div>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    x<?= $order['quantity'] ?>
                                                </span>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium status-badge status-<?= $order['status'] ?>">
                                                    <?= ucfirst($order['status']) ?>
                                                </span>
                                            </div>
                                            <div class="text-sm text-gray-500 mt-1">
                                                <span class="font-medium"><?= htmlspecialchars($order['username']) ?></span>
                                                <span class="mx-2">•</span>
                                                <span><?= date('H:i', strtotime($order['created_at'])) ?></span>
                                                <?php if ($order['catatan']): ?>
                                                <span class="mx-2">•</span>
                                                <span class="text-xs text-gray-400">Catatan: <?= htmlspecialchars($order['catatan']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Action Buttons -->
                                    <div class="flex items-center space-x-2 action-buttons">
                                        <?php if ($order['status'] === 'menunggu'): ?>
                                            <button class="status-btn bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs transition-colors shadow-md" 
                                                    data-order-id="<?= $order['id'] ?>" data-status="memasak">
                                                <i class="fas fa-fire mr-1"></i>Terima Pesanan
                                            </button>
                                        <?php elseif ($order['status'] === 'memasak'): ?>
                                            <button class="status-btn bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs transition-colors shadow-md" 
                                                    data-order-id="<?= $order['id'] ?>" data-status="selesai">
                                                <i class="fas fa-check mr-1"></i>Tandai Sebagai Selesai
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if (in_array($order['status'], ['menunggu', 'memasak'])): ?>
                                            <button class="status-btn bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs transition-colors shadow-md" 
                                                    data-order-id="<?= $order['id'] ?>" data-status="ditolak">
                                                <i class="fas fa-times mr-1"></i>Tolak Pesanan
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="script.js"></script>
</body>
</html>
