<?php
require_once '../../database.php';
session_start();

// Cek login admin
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login_register/login.php');
    exit;
}

$koneksi = koneksiDatabase('red bear');

// Ambil data booking online (booked)
$online_bookings = [];
$query_online = "SELECT tb.id, t.table_number, u.name as user_name, tb.guest_count, tb.booking_date, tb.booking_time 
                 FROM table_bookings tb 
                 JOIN tables t ON tb.table_id = t.id 
                 JOIN users u ON tb.user_id = u.id 
                 WHERE tb.status = 'booked' 
                 ORDER BY tb.booking_date ASC, tb.booking_time ASC";
$result_online = $koneksi->query($query_online);
while($row = $result_online->fetch_assoc()) {
    $online_bookings[] = $row;
}

// Ambil data meja offline (occupied)
$offline_sessions = [];
$query_offline = "SELECT os.id, t.table_number, os.created_at 
                  FROM offline_table_sessions os
                  JOIN tables t ON os.table_id = t.id
                  WHERE os.status = 'occupied'
                  ORDER BY os.created_at ASC";
$result_offline = $koneksi->query($query_offline);
while($row = $result_offline->fetch_assoc()) {
    $offline_sessions[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Kelola Status Meja</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <h1 class="text-2xl font-bold text-gray-900">Kelola Status Meja</h1>
                    <a href="../../admin/beranda.php" class="text-gray-600 hover:text-gray-900">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- Booking Online -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md mb-8">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200 bg-red-50">
                    <h3 class="text-lg leading-6 font-medium text-red-800"><i class="fas fa-user-clock mr-2"></i>Meja Dipesan (Booking Online)</h3>
                </div>
                <?php if (count($online_bookings) > 0): ?>
                    <ul class="divide-y divide-gray-200">
                        <?php foreach ($online_bookings as $row): ?>
                            <li class="px-4 py-4 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-gray-900">Meja <?php echo $row['table_number']; ?></p>
                                            <div class="mt-1 text-sm text-gray-500">
                                                <p><i class="fas fa-user mr-1"></i><?php echo htmlspecialchars($row['user_name']); ?> (<?php echo $row['guest_count']; ?> tamu)</p>
                                                <p><i class="fas fa-calendar mr-1"></i><?php echo date('d/m/Y', strtotime($row['booking_date'])); ?> - <i class="fas fa-clock mr-1"></i><?php echo $row['booking_time']; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <button onclick="markAsCompleted(<?php echo $row['id']; ?>)" class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                        <i class="fas fa-check mr-1"></i> Selesai
                                    </button>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-center text-gray-500 py-6">Tidak ada meja yang dibooking secara online.</p>
                <?php endif; ?>
            </div>

            <!-- Sesi Offline -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200 bg-yellow-50">
                    <h3 class="text-lg leading-6 font-medium text-yellow-800"><i class="fas fa-utensils mr-2"></i>Meja Ditempati (Pelanggan Offline)</h3>
                </div>
                <?php if (count($offline_sessions) > 0): ?>
                    <ul class="divide-y divide-gray-200">
                        <?php foreach ($offline_sessions as $row): ?>
                             <li class="px-4 py-4 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-900">Meja <?php echo $row['table_number']; ?> (Sejak: <?php echo date('H:i', strtotime($row['created_at'])); ?>)</p>
                                    <button onclick="markAsVacant(<?php echo $row['id']; ?>)" class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                        <i class="fas fa-check mr-1"></i> Selesai
                                    </button>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-center text-gray-500 py-6">Tidak ada meja yang ditempati oleh pelanggan offline.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        function markAsCompleted(bookingId) {
            if (confirm('Apakah Anda yakin ingin menandai booking ini sebagai selesai? Meja akan kembali tersedia.')) {
                fetch('api/mark_completed.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        booking_id: bookingId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Booking berhasil ditandai sebagai selesai!');
                        location.reload();
                    } else {
                        alert('Gagal menandai booking: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memproses permintaan.');
                });
            }
        }

        function markAsVacant(sessionId) {
            if (confirm('Apakah Anda yakin ingin menandai sesi ini sebagai selesai? Meja akan kembali tersedia.')) {
                fetch('api/mark_vacant.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ session_id: sessionId })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Sesi berhasil ditandai sebagai selesai.');
                        location.reload();
                    } else {
                        alert('Gagal: ' + data.message);
                    }
                });
            }
        }
    </script>
</body>
</html>
