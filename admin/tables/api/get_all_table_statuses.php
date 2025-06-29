<?php
require_once '../../../database.php';
header('Content-Type: application/json');

$koneksi = koneksiDatabase('red bear');

// Ambil parameter tanggal dan waktu jika ada
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$time = isset($_GET['time']) ? $_GET['time'] : null;

// 1. Ambil semua meja sebagai dasar
$tables_query = "SELECT id, table_number FROM tables ORDER BY table_number ASC";
$tables_result = $koneksi->query($tables_query);
$all_tables = [];
while ($row = $tables_result->fetch_assoc()) {
    $all_tables[$row['id']] = [
        'id' => $row['id'],
        'table_number' => $row['table_number'],
        'status' => 'available', // Status default
        'next_available_time' => null
    ];
}

// 2. Ambil meja yang di-booking online (status 'booked') dengan jarak waktu 2 jam
if ($time) {
    $bookings_query = "SELECT table_id, booking_time FROM table_bookings 
                       WHERE status = 'booked' AND booking_date = ? 
                       AND ABS(TIME_TO_SEC(TIMEDIFF(booking_time, ?)) / 3600) < 2";
    $stmt_bookings = $koneksi->prepare($bookings_query);
    $stmt_bookings->bind_param("ss", $date, $time);
    $stmt_bookings->execute();
    $bookings_result = $stmt_bookings->get_result();
    while ($row = $bookings_result->fetch_assoc()) {
        if (isset($all_tables[$row['table_id']])) {
            $all_tables[$row['table_id']]['status'] = 'unavailable';
            // Hitung waktu tersedia berikutnya (2 jam setelah booking time)
            $booking_time = strtotime($row['booking_time']);
            $next_available = date('H:i', $booking_time + (2 * 3600));
            $all_tables[$row['table_id']]['next_available_time'] = $next_available;
        }
    }
    $stmt_bookings->close();
} else {
    // Jika tidak ada waktu spesifik, cek semua booking hari ini
$bookings_query = "SELECT table_id FROM table_bookings WHERE status = 'booked' AND booking_date = ?";
$stmt_bookings = $koneksi->prepare($bookings_query);
    $stmt_bookings->bind_param("s", $date);
$stmt_bookings->execute();
$bookings_result = $stmt_bookings->get_result();
while ($row = $bookings_result->fetch_assoc()) {
    if (isset($all_tables[$row['table_id']])) {
        $all_tables[$row['table_id']]['status'] = 'unavailable';
    }
}
$stmt_bookings->close();
}

// 3. Ambil meja yang ditempati offline (status 'occupied') dengan jarak waktu 2 jam
if ($time) {
    // Cek sesi offline yang masih aktif dan berjarak kurang dari 2 jam
    $offline_query = "SELECT os.table_id, os.created_at 
                      FROM offline_table_sessions os 
                      WHERE os.status = 'occupied' 
                      AND DATE(os.created_at) = ? 
                      AND ABS(TIME_TO_SEC(TIMEDIFF(TIME(os.created_at), ?)) / 3600) < 2";
    $stmt_offline = $koneksi->prepare($offline_query);
    $stmt_offline->bind_param("ss", $date, $time);
    $stmt_offline->execute();
    $offline_result = $stmt_offline->get_result();
    while ($row = $offline_result->fetch_assoc()) {
        if (isset($all_tables[$row['table_id']])) {
            $all_tables[$row['table_id']]['status'] = 'unavailable';
            // Hitung waktu tersedia berikutnya (2 jam setelah sesi dimulai)
            $session_time = strtotime($row['created_at']);
            $next_available = date('H:i', $session_time + (2 * 3600));
            $all_tables[$row['table_id']]['next_available_time'] = $next_available;
        }
    }
    $stmt_offline->close();
} else {
    // Jika tidak ada waktu spesifik, cek semua sesi offline hari ini
    $offline_query = "SELECT table_id FROM offline_table_sessions WHERE status = 'occupied' AND DATE(created_at) = ?";
    $stmt_offline = $koneksi->prepare($offline_query);
    $stmt_offline->bind_param("s", $date);
    $stmt_offline->execute();
    $offline_result = $stmt_offline->get_result();
while ($row = $offline_result->fetch_assoc()) {
    if (isset($all_tables[$row['table_id']])) {
        $all_tables[$row['table_id']]['status'] = 'unavailable';
            $all_tables[$row['table_id']]['next_available_time'] = 'Setelah pelanggan selesai';
        }
    }
    $stmt_offline->close();
}

// Kembalikan hasil dalam bentuk array
echo json_encode(['success' => true, 'tables' => array_values($all_tables)]); 