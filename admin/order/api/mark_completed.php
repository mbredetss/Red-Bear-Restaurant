<?php
require_once '../../../database.php';
session_start();
header('Content-Type: application/json');

// Cek login admin
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
    exit;
}

// Ambil data dari POST request (FormData atau JSON)
$table_id = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cek apakah data dikirim sebagai FormData
    if (isset($_POST['table_id'])) {
        $table_id = intval($_POST['table_id']);
    } else {
        // Cek apakah data dikirim sebagai JSON
        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['table_id'])) {
            $table_id = intval($input['table_id']);
        }
    }
}

if (!$table_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID meja tidak valid.']);
    exit;
}

$koneksi = koneksiDatabase('red bear');

try {
    // Update status booking menjadi cancelled (tersedia kembali)
    $stmt = $koneksi->prepare("UPDATE table_bookings SET status = 'cancelled' WHERE table_id = ? AND status = 'booked' AND booking_date = CURDATE()");
    $stmt->bind_param("i", $table_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true, 
                'message' => 'Booking berhasil diselesaikan dan meja tersedia kembali'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Tidak ada booking aktif untuk meja ini.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menyelesaikan booking.']);
    }
    
    $stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
}

$koneksi->close();
?> 