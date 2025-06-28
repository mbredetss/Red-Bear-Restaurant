<?php
require_once '../../../database.php';
session_start();

header('Content-Type: application/json');

// Cek login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Anda harus login untuk mengakses fitur ini.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$koneksi = koneksiDatabase('red bear');

// Ambil kode meja user yang sedang aktif (booking hari ini)
$today = date('Y-m-d');
$stmt = $koneksi->prepare('
    SELECT tb.table_code, tb.booking_date, tb.booking_time, t.table_number 
    FROM table_bookings tb 
    JOIN tables t ON tb.table_id = t.id 
    WHERE tb.user_id = ? AND tb.booking_date = ? AND tb.status = "booked" 
    ORDER BY tb.booking_time ASC
');
$stmt->bind_param('is', $user_id, $today);
$stmt->execute();
$result = $stmt->get_result();

$bookings = [];
while ($row = $result->fetch_assoc()) {
    $bookings[] = [
        'table_code' => $row['table_code'],
        'booking_date' => $row['booking_date'],
        'booking_time' => $row['booking_time'],
        'table_number' => $row['table_number']
    ];
}
$stmt->close();

echo json_encode([
    'success' => true,
    'bookings' => $bookings
]); 