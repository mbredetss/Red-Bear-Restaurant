<?php
require_once '../../../database.php';
session_start();

header('Content-Type: application/json');

// Cek login admin
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Anda harus login untuk mengakses fitur ini.']);
    exit;
}

// Ambil data dari request
$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['order_id']) || !isset($input['status'])) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
    exit;
}

$order_id = intval($input['order_id']);
$status = $input['status'];

// Validasi status yang diizinkan
$allowed_statuses = ['menunggu', 'memasak', 'selesai', 'ditolak'];
if (!in_array($status, $allowed_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Status tidak valid.']);
    exit;
}

$koneksi = koneksiDatabase('red bear');

try {
    // Update status pesanan
    $stmt = $koneksi->prepare('UPDATE orders SET status = ? WHERE id = ?');
    $stmt->bind_param('si', $status, $order_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true, 
                'message' => 'Status pesanan berhasil diupdate menjadi ' . ucfirst($status)
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Pesanan tidak ditemukan.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mengupdate status pesanan.']);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
}
?> 