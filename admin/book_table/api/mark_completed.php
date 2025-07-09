<?php
require_once '../../../database.php';
session_start();

header('Content-Type: application/json');

// Cek login admin
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Anda harus login untuk mengakses fitur ini.']);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['table_id']) || !is_numeric($input['table_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID meja tidak valid.']);
    exit;
}

$table_id = intval($input['table_id']);
$koneksi = koneksiDatabase("red bear");

try {
    // Update status booking menjadi cancelled (selesai)
    $stmt = $koneksi->prepare("UPDATE table_bookings SET status = 'cancelled' WHERE table_id = ? AND status = 'booked'");
    $stmt->bind_param("i", $table_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true, 
                'message' => 'Booking berhasil ditandai sebagai selesai'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Tidak ada booking aktif untuk meja ini.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menyelesaikan booking.']);
    }
    
    $stmt->close();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
}
?> 