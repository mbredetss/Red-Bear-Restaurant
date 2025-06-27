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

    // 3. Ambil dan arsipkan item pesanan terkait
    $stmt_get_orders = $koneksi->prepare("SELECT menu_name, quantity, status FROM orders WHERE offline_table_session_id = ?");
    $stmt_get_orders->bind_param("i", $session_id);
    $stmt_get_orders->execute();
    $result_orders = $stmt_get_orders->get_result();
    
    $stmt_archive_order = $koneksi->prepare("INSERT INTO archived_orders (menu_name, quantity) VALUES (?, ?)");
    while ($order = $result_orders->fetch_assoc()) {
        if ($order['status'] === 'selesai') {
            $stmt_archive_order->bind_param("si", $order['menu_name'], $order['quantity']);
            $stmt_archive_order->execute();
        }
        // Jika status bukan 'selesai', tidak diarsipkan
    }
    $stmt_archive_order->close();
    $stmt_get_orders->close();

    // 4. Hapus item pesanan dari tabel operasional
    $stmt_delete_orders = $koneksi->prepare("DELETE FROM orders WHERE offline_table_session_id = ?");
    $stmt_delete_orders->bind_param("i", $session_id);
    $stmt_delete_orders->execute();
    $stmt_delete_orders->close();
    
    // 5. Hapus sesi dari tabel operasional
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