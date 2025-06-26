<?php
require_once '../../database.php';
session_start();

// Cek login admin
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login_register/login.php');
    exit;
}

$koneksi = koneksiDatabase('red bear');

// Ambil data booking aktif
$query = "SELECT tb.*, t.table_number, u.name as user_name, u.email 
          FROM table_bookings tb 
          JOIN tables t ON tb.table_id = t.id 
          JOIN users u ON tb.user_id = u.id 
          WHERE tb.status = 'booked' 
          ORDER BY tb.booking_date ASC, tb.booking_time ASC";
$result = $koneksi->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Kelola Booking Meja</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-gray-900">Kelola Booking Meja</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="../../admin/beranda.php" class="text-gray-600 hover:text-gray-900">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-calendar-check text-2xl text-green-600"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Total Booking Aktif
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            <?php echo $result->num_rows; ?>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-table text-2xl text-blue-600"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Meja Tersedia
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            <?php 
                                            $total_tables = $koneksi->query("SELECT COUNT(*) as total FROM tables")->fetch_assoc()['total'];
                                            echo $total_tables - $result->num_rows;
                                            ?>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-users text-2xl text-purple-600"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Total Tamu Hari Ini
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            <?php 
                                            $today = date('Y-m-d');
                                            $total_guests = $koneksi->query("SELECT SUM(guest_count) as total FROM table_bookings WHERE booking_date = '$today' AND status = 'booked'")->fetch_assoc()['total'];
                                            echo $total_guests ? $total_guests : 0;
                                            ?>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking Table -->
                <div class="bg-white shadow overflow-hidden sm:rounded-md">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Daftar Booking Aktif
                        </h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">
                            Kelola status booking meja dan tandai user yang sudah selesai
                        </p>
                    </div>
                    
                    <?php if ($result->num_rows > 0): ?>
                        <ul class="divide-y divide-gray-200">
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <li class="px-4 py-4 sm:px-6">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="h-10 w-10 rounded-full bg-red-100 flex items-center justify-center">
                                                    <i class="fas fa-table text-red-600"></i>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="flex items-center">
                                                    <p class="text-sm font-medium text-gray-900">
                                                        Meja <?php echo $row['table_number']; ?>
                                                    </p>
                                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        Aktif
                                                    </span>
                                                </div>
                                                <div class="mt-1 flex items-center text-sm text-gray-500">
                                                    <p>
                                                        <i class="fas fa-user mr-1"></i>
                                                        <?php echo htmlspecialchars($row['user_name']); ?> 
                                                        (<?php echo $row['guest_count']; ?> tamu)
                                                    </p>
                                                </div>
                                                <div class="mt-1 flex items-center text-sm text-gray-500">
                                                    <p>
                                                        <i class="fas fa-calendar mr-1"></i>
                                                        <?php echo date('d/m/Y', strtotime($row['booking_date'])); ?>
                                                    </p>
                                                    <p class="ml-4">
                                                        <i class="fas fa-clock mr-1"></i>
                                                        <?php echo $row['booking_time']; ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <button 
                                                onclick="markAsCompleted(<?php echo $row['id']; ?>)"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors"
                                            >
                                                <i class="fas fa-check mr-1"></i>
                                                Selesai
                                            </button>
                                        </div>
                                    </div>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-center py-12">
                            <i class="fas fa-table text-4xl text-gray-300 mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada booking aktif</h3>
                            <p class="text-gray-500">Semua meja saat ini tersedia untuk booking.</p>
                        </div>
                    <?php endif; ?>
                </div>
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
    </script>
</body>
</html>
