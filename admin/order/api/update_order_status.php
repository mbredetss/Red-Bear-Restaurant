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
$order_id = null;
$status = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cek apakah data dikirim sebagai FormData
    if (isset($_POST['order_id']) && isset($_POST['status'])) {
        $order_id = intval($_POST['order_id']);
        $status = $_POST['status'];
    } else {
        // Cek apakah data dikirim sebagai JSON
        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['order_id']) && isset($input['status'])) {
            $order_id = intval($input['order_id']);
            $status = $input['status'];
        }
    }
}

if (!$order_id || !$status) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
    exit;
}

// Validasi status
$valid_statuses = ['menunggu', 'memasak', 'selesai', 'ditolak'];
if (!in_array($status, $valid_statuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Status tidak valid.']);
    exit;
}

$koneksi = koneksiDatabase('red bear');

try {
    // Update status pesanan
    $stmt = $koneksi->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true, 
                'message' => 'Status pesanan berhasil diperbarui'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Pesanan tidak ditemukan.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui status pesanan.']);
    }
    
    $stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
}

$koneksi->close();
?> 