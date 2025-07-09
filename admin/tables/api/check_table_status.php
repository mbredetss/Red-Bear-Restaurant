<?php
require_once '../../../database.php';
header('Content-Type: application/json');

$koneksi = koneksiDatabase('red bear');

// Ambil table_id dari parameter
$table_id = isset($_GET['table_id']) ? intval($_GET['table_id']) : 0;

if (!$table_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID meja tidak valid.']);
    exit;
}

// Cek apakah meja ada di database
$table_query = "SELECT id, table_number FROM tables WHERE id = ?";
$stmt_table = $koneksi->prepare($table_query);
$stmt_table->bind_param("i", $table_id);
$stmt_table->execute();
$table_result = $stmt_table->get_result();

if ($table_result->num_rows === 0) {
    $stmt_table->close();
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Meja tidak ditemukan.']);
    exit;
}

$table_data = $table_result->fetch_assoc();
$stmt_table->close();

// Cek status meja hari ini
$today = date('Y-m-d');

// 1. Cek booking online
$booking_query = "SELECT id, guest_count, booking_time, table_code, user_id 
                  FROM table_bookings 
                  WHERE table_id = ? AND booking_date = ? AND status = 'booked'";
$stmt_booking = $koneksi->prepare($booking_query);
$stmt_booking->bind_param("is", $table_id, $today);
$stmt_booking->execute();
$booking_result = $stmt_booking->get_result();

$booking_info = null;
if ($booking_result->num_rows > 0) {
    $booking_info = $booking_result->fetch_assoc();
    
    // Ambil nama user jika ada
    if ($booking_info['user_id']) {
        $user_query = "SELECT name FROM users WHERE id = ?";
        $stmt_user = $koneksi->prepare($user_query);
        $stmt_user->bind_param("i", $booking_info['user_id']);
        $stmt_user->execute();
        $user_result = $stmt_user->get_result();
        if ($user_result->num_rows > 0) {
            $booking_info['user_name'] = $user_result->fetch_assoc()['name'];
        }
        $stmt_user->close();
    }
}
$stmt_booking->close();

// 2. Cek sesi offline
$offline_query = "SELECT id, guest_count, session_code, created_at 
                  FROM offline_table_sessions 
                  WHERE table_id = ? AND status = 'occupied' AND DATE(created_at) = ?";
$stmt_offline = $koneksi->prepare($offline_query);
$stmt_offline->bind_param("is", $table_id, $today);
$stmt_offline->execute();
$offline_result = $stmt_offline->get_result();

$offline_info = null;
if ($offline_result->num_rows > 0) {
    $offline_info = $offline_result->fetch_assoc();
}
$stmt_offline->close();

// 3. Tentukan status dan informasi
$status = 'available';
$status_text = 'Tersedia';
$occupied_by = null;
$occupied_since = null;
$guest_count = null;

if ($booking_info) {
    $status = 'booked';
    $status_text = 'Di-booking Online';
    $occupied_by = $booking_info['user_name'] ?? 'Pelanggan Online';
    $occupied_since = $booking_info['booking_time'];
    $guest_count = $booking_info['guest_count'];
} elseif ($offline_info) {
    $status = 'occupied';
    $status_text = 'Sedang Digunakan';
    $occupied_by = 'Pelanggan Offline';
    $occupied_since = date('H:i', strtotime($offline_info['created_at']));
    $guest_count = $offline_info['guest_count'];
}

// 4. Kembalikan hasil
echo json_encode([
    'success' => true,
    'table_id' => $table_id,
    'table_number' => $table_data['table_number'],
    'status' => $status,
    'status_text' => $status_text,
    'occupied_by' => $occupied_by,
    'occupied_since' => $occupied_since,
    'guest_count' => $guest_count,
    'booking_info' => $booking_info,
    'offline_info' => $offline_info,
    'is_available' => $status === 'available'
]);
?> 