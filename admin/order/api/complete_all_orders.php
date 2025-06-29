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
    // Update semua pesanan yang masih aktif (menunggu atau memasak) menjadi selesai
    $stmt = $koneksi->prepare("
        UPDATE orders o 
        JOIN offline_table_sessions ots ON o.offline_table_session_id = ots.id 
        SET o.status = 'selesai' 
        WHERE ots.table_id = ? AND ots.status = 'occupied' 
        AND o.status IN ('menunggu', 'memasak')
    ");
    $stmt->bind_param("i", $table_id);
    
    if ($stmt->execute()) {
        $affected_rows = $stmt->affected_rows;
        echo json_encode([
            'success' => true, 
            'message' => "Berhasil menyelesaikan {$affected_rows} pesanan"
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menyelesaikan pesanan.']);
    }
    
    $stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
}

$koneksi->close();
?> 