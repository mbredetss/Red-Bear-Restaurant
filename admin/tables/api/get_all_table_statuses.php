<?php
require_once '../../../database.php';
header('Content-Type: application/json');

$koneksi = koneksiDatabase('red bear');

// 1. Ambil semua meja sebagai dasar
$tables_query = "SELECT id, table_number FROM tables ORDER BY table_number ASC";
$tables_result = $koneksi->query($tables_query);
$all_tables = [];
while ($row = $tables_result->fetch_assoc()) {
    $all_tables[$row['id']] = [
        'id' => $row['id'],
        'table_number' => $row['table_number'],
        'status' => 'available' // Status default
    ];
}

// 2. Ambil meja yang di-booking online (status 'booked')
$today = date('Y-m-d');
$bookings_query = "SELECT table_id FROM table_bookings WHERE status = 'booked' AND booking_date = ?";
$stmt_bookings = $koneksi->prepare($bookings_query);
$stmt_bookings->bind_param("s", $today);
$stmt_bookings->execute();
$bookings_result = $stmt_bookings->get_result();
while ($row = $bookings_result->fetch_assoc()) {
    if (isset($all_tables[$row['table_id']])) {
        $all_tables[$row['table_id']]['status'] = 'unavailable';
    }
}
$stmt_bookings->close();

// 3. Ambil meja yang ditempati offline (status 'occupied')
$offline_query = "SELECT table_id FROM offline_table_sessions WHERE status = 'occupied'";
$offline_result = $koneksi->query($offline_query);
while ($row = $offline_result->fetch_assoc()) {
    if (isset($all_tables[$row['table_id']])) {
        $all_tables[$row['table_id']]['status'] = 'unavailable';
    }
}

// Kembalikan hasil dalam bentuk array
echo json_encode(['success' => true, 'tables' => array_values($all_tables)]); 