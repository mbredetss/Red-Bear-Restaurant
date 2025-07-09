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

$input = json_decode(file_get_contents('php://input'), true);
$session_id = isset($input['session_id']) ? intval($input['session_id']) : 0;

if (!$session_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID Sesi tidak valid.']);
    exit;
}

$koneksi = koneksiDatabase('red bear');
$koneksi->begin_transaction();

try {
    // 1. Ambil data sesi yang akan diarsipkan
    $stmt_get_session = $koneksi->prepare("SELECT guest_count, created_at FROM offline_table_sessions WHERE id = ?");
    $stmt_get_session->bind_param("i", $session_id);
    $stmt_get_session->execute();
    $result_session = $stmt_get_session->get_result();
    if ($result_session->num_rows === 0) {
        throw new Exception("Sesi tidak ditemukan.");
    }
    $session_data = $result_session->fetch_assoc();
    $stmt_get_session->close();

    // 2. Arsipkan data sesi
    $stmt_archive_session = $koneksi->prepare("INSERT INTO archived_sessions (guest_count, session_start_time) VALUES (?, ?)");
    $stmt_archive_session->bind_param("is", $session_data['guest_count'], $session_data['created_at']);
    $stmt_archive_session->execute();
    $stmt_archive_session->close();

    // 3. Hapus sesi dari tabel operasional (orders tetap ada untuk riwayat)
    $stmt_delete_session = $koneksi->prepare("DELETE FROM offline_table_sessions WHERE id = ?");
    $stmt_delete_session->bind_param("i", $session_id);
    $stmt_delete_session->execute();
    $stmt_delete_session->close();

    // Jika semua berhasil, commit transaksi
    $koneksi->commit();
    echo json_encode(['success' => true, 'message' => 'Meja berhasil dikosongkan dan sesi diarsipkan.']);

} catch (Exception $e) {
    $koneksi->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal mengarsipkan sesi: ' . $e->getMessage()]);
}

$koneksi->close(); 