<?php
require_once '../../../database.php';
session_start();

header('Content-Type: application/json');

// Cek login admin
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Anda harus login untuk mengakses fitur ini.']);
    exit;
}

$koneksi = koneksiDatabase('red bear');

// Ambil data dari request
$input = json_decode(file_get_contents('php://input'), true);
$booking_id = isset($input['booking_id']) ? intval($input['booking_id']) : 0;

if (!$booking_id) {
    echo json_encode(['success' => false, 'message' => 'ID booking tidak valid.']);
    exit;
}

// Cek apakah booking ada dan masih aktif
$stmt = $koneksi->prepare('SELECT id, user_id, table_id FROM table_bookings WHERE id = ? AND status = "booked"');
$stmt->bind_param('i', $booking_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Booking tidak ditemukan atau sudah selesai.']);
    exit;
}

$booking = $result->fetch_assoc();

// Update status booking menjadi completed
$stmt = $koneksi->prepare('UPDATE table_bookings SET status = "completed" WHERE id = ?');
$stmt->bind_param('i', $booking_id);
$success = $stmt->execute();
$stmt->close();

if ($success) {
    echo json_encode([
        'success' => true, 
        'message' => 'Booking berhasil ditandai sebagai selesai. Meja kembali tersedia.'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal mengupdate status booking.']);
} 