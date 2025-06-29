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
    // Update semua pesanan untuk meja ini menjadi selesai
    $stmt = $koneksi->prepare("
        UPDATE orders o 
        JOIN offline_table_sessions ots ON o.offline_table_session_id = ots.id 
        SET o.status = 'selesai' 
        WHERE ots.table_id = ? AND o.status IN ('menunggu', 'memasak')
    ");
    $stmt->bind_param("i", $table_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true, 
                'message' => 'Semua pesanan untuk meja ini telah diselesaikan'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Tidak ada pesanan aktif untuk meja ini.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menyelesaikan pesanan.']);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
}
?> 