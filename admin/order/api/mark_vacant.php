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
$session_id = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cek apakah data dikirim sebagai FormData
    if (isset($_POST['session_id'])) {
        $session_id = intval($_POST['session_id']);
    } else {
        // Cek apakah data dikirim sebagai JSON
        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['session_id'])) {
            $session_id = intval($input['session_id']);
        }
    }
}

if (!$session_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID Sesi tidak valid.']);
    exit;
}

$koneksi = koneksiDatabase('red bear');

try {
    // Update status sesi menjadi vacant (tersedia)
    $stmt = $koneksi->prepare("UPDATE offline_table_sessions SET status = 'vacant' WHERE id = ?");
    $stmt->bind_param("i", $session_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Meja berhasil dikosongkan dan tersedia kembali.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Sesi tidak ditemukan atau sudah kosong.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mengosongkan meja.']);
    }
    
    $stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
}

$koneksi->close();
?> 