<?php
session_start();

require_once '../../../database.php'; // sesuaikan path

header('Content-Type: application/json');

$koneksi = koneksiDatabase("red bear");
$hasActiveOrder = false;
$status = null;

$whereClause = "";
$params = [];
$types = "";

// Cek apakah user login atau pelanggan offline
if (isset($_SESSION['user_id'])) {
    $whereClause = "o.user_id = ?";
    $params[] = $_SESSION['user_id'];
    $types .= "i";
} elseif (isset($_SESSION['scanned_table_id'])) {
    $session_code = session_id() . "_" . $_SESSION['scanned_table_id'];
    $stmt_session = $koneksi->prepare("SELECT id FROM offline_table_sessions WHERE session_code = ?");
    $stmt_session->bind_param("s", $session_code);
    $stmt_session->execute();
    $result_session = $stmt_session->get_result();
    if ($result_session->num_rows > 0) {
        $offline_session_id = $result_session->fetch_assoc()['id'];
        $whereClause = "o.offline_table_session_id = ?";
        $params[] = $offline_session_id;
        $types .= "i";
    }
}

if (!empty($whereClause)) {
    // Query baru untuk mengecek pesanan aktif dari tabel 'orders'
    $sql = "
        SELECT o.status 
        FROM orders o
        WHERE $whereClause AND o.status NOT IN ('selesai', 'ditolak')
        ORDER BY o.id DESC
        LIMIT 1
    ";

    $stmt = $koneksi->prepare($sql);
    if ($stmt) {
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $hasActiveOrder = true;
            $status = $row['status'];
        }
        $stmt->close();
    }
}

echo json_encode([
    'hasActiveOrder' => $hasActiveOrder,
    'status' => $status
]);
