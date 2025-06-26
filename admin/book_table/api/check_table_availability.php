<?php
require_once '../../../database.php';

$koneksi = koneksiDatabase('red bear');
header('Content-Type: application/json');

// Ambil parameter tanggal dan waktu dari request
$date = isset($_GET['date']) ? $_GET['date'] : null;
$time = isset($_GET['time']) ? $_GET['time'] : null;

if (!$date || !$time) {
    echo json_encode(['success' => false, 'message' => 'Tanggal dan waktu wajib diisi.']);
    exit;
}

// Ambil semua meja
$tables = [];
$result = $koneksi->query('SELECT id, table_number, capacity FROM tables ORDER BY table_number ASC');
while ($row = $result->fetch_assoc()) {
    $tables[$row['id']] = [
        'id' => $row['id'],
        'table_number' => $row['table_number'],
        'capacity' => $row['capacity'],
        'status' => 'available'
    ];
}

// Cek booking pada tanggal & waktu tersebut
$stmt = $koneksi->prepare('SELECT table_id FROM table_bookings WHERE booking_date = ? AND booking_time = ? AND status = "booked"');
$stmt->bind_param('ss', $date, $time);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    if (isset($tables[$row['table_id']])) {
        $tables[$row['table_id']]['status'] = 'booked';
    }
}
$stmt->close();

// Output status semua meja
$tables = array_values($tables); // reset index

echo json_encode(['success' => true, 'tables' => $tables]); 