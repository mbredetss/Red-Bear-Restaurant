<?php
require_once '../../../database.php';
session_start();

header('Content-Type: application/json');

// Cek login admin
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Anda harus login untuk mengakses fitur ini.']);
    exit;
}

$koneksi = koneksiDatabase('red bear');

// Ambil data dari request
$input = json_decode(file_get_contents('php://input'), true);
$booking_id = isset($input['booking_id']) ? intval($input['booking_id']) : 0;

if (!$booking_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID booking tidak valid.']);
    exit;
}

$koneksi->begin_transaction();

try {
    // 1. Ambil data booking yang akan diarsipkan
    $stmt_get_booking = $koneksi->prepare("SELECT guest_count, booking_date, booking_time FROM table_bookings WHERE id = ?");
    $stmt_get_booking->bind_param("i", $booking_id);
    $stmt_get_booking->execute();
    $result_booking = $stmt_get_booking->get_result();
    if ($result_booking->num_rows === 0) {
        throw new Exception("Booking tidak ditemukan.");
    }
    $booking_data = $result_booking->fetch_assoc();
    $stmt_get_booking->close();

    // Gabungkan tanggal dan waktu menjadi timestamp
    $session_start_time = $booking_data['booking_date'] . ' ' . $booking_data['booking_time'];

    // 2. Arsipkan data booking ke tabel arsip sesi
    $stmt_archive_session = $koneksi->prepare("INSERT INTO archived_sessions (guest_count, session_start_time) VALUES (?, ?)");
    $stmt_archive_session->bind_param("is", $booking_data['guest_count'], $session_start_time);
    $stmt_archive_session->execute();
    $stmt_archive_session->close();

    // 3. Hapus booking dari tabel operasional
    $stmt_delete_booking = $koneksi->prepare("DELETE FROM table_bookings WHERE id = ?");
    $stmt_delete_booking->bind_param("i", $booking_id);
    $stmt_delete_booking->execute();
    $stmt_delete_booking->close();

    // Jika semua berhasil, commit transaksi
    $koneksi->commit();
    echo json_encode(['success' => true, 'message' => 'Booking berhasil diselesaikan dan diarsipkan.']);

} catch (Exception $e) {
    $koneksi->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal mengarsipkan booking: ' . $e->getMessage()]);
}

$koneksi->close(); 