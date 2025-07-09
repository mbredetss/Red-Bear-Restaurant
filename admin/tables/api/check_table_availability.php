<?php
require_once '../../../database.php';
session_start();

header('Content-Type: application/json');

if (!isset($_GET['table_id']) || !is_numeric($_GET['table_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID meja tidak valid']);
    exit;
}

$table_id = intval($_GET['table_id']);
$koneksi = koneksiDatabase("red bear");

// Cek apakah pelanggan sudah memiliki sesi aktif di meja lain
$current_session_code = session_id() . "_" . $table_id;
$session_pattern = session_id() . "_%";
$today = date('Y-m-d');

$existing_session_query = "SELECT table_id FROM offline_table_sessions WHERE session_code LIKE ? AND status = 'occupied' AND DATE(created_at) = ?";
$stmt_existing = $koneksi->prepare($existing_session_query);
$stmt_existing->bind_param("ss", $session_pattern, $today);
$stmt_existing->execute();
$existing_result = $stmt_existing->get_result();
$has_existing_session = $existing_result->num_rows > 0;
$stmt_existing->close();

if ($has_existing_session) {
    $existing_session = $existing_result->fetch_assoc();
    $existing_table_id = $existing_session['table_id'];
    
    // Jika mencoba akses meja yang berbeda, tolak akses
    if ($existing_table_id != $table_id) {
        echo json_encode([
            'success' => false,
            'is_available' => false,
            'reason' => 'different_table',
            'existing_table_id' => $existing_table_id,
            'message' => 'Anda sudah memiliki sesi aktif di meja lain'
        ]);
        exit;
    } else {
        // Jika akses meja yang sama, izinkan
        echo json_encode([
            'success' => true,
            'is_available' => true,
            'reason' => 'same_table',
            'message' => 'Meja tersedia untuk sesi Anda'
        ]);
        exit;
    }
}

// Cek apakah meja sudah di-booking online hari ini
$booking_query = "SELECT id FROM table_bookings WHERE table_id = ? AND booking_date = ? AND status = 'booked'";
$stmt_booking = $koneksi->prepare($booking_query);
$stmt_booking->bind_param("is", $table_id, $today);
$stmt_booking->execute();
$booking_result = $stmt_booking->get_result();
$has_booking = $booking_result->num_rows > 0;
$stmt_booking->close();

// Cek apakah meja sudah ditempati offline hari ini
$offline_query = "SELECT id FROM offline_table_sessions WHERE table_id = ? AND status = 'occupied' AND DATE(created_at) = ?";
$stmt_offline = $koneksi->prepare($offline_query);
$stmt_offline->bind_param("is", $table_id, $today);
$stmt_offline->execute();
$offline_result = $stmt_offline->get_result();
$has_offline_session = $offline_result->num_rows > 0;
$stmt_offline->close();

// Tentukan ketersediaan meja
if ($has_booking) {
    echo json_encode([
        'success' => true,
        'is_available' => false,
        'reason' => 'booking',
        'message' => 'Meja sudah di-booking online'
    ]);
} elseif ($has_offline_session) {
    echo json_encode([
        'success' => true,
        'is_available' => false,
        'reason' => 'offline',
        'message' => 'Meja sudah ditempati offline'
    ]);
} else {
    echo json_encode([
        'success' => true,
        'is_available' => true,
        'reason' => 'available',
        'message' => 'Meja tersedia'
    ]);
}
?> 