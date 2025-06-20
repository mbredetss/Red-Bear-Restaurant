<?php
session_start();

require_once '../../../database.php'; // sesuaikan path

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['hasActiveOrder' => false]);
    exit;
}

$koneksi = koneksiDatabase("red bear");

$userId = $_SESSION['user_id'];

// Ambil status terakhir dari order_items yang belum selesai
$sql = "
    SELECT oi.status 
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ? AND oi.status NOT IN ('selesai', 'ditolak')
    ORDER BY oi.id DESC
    LIMIT 1
";

$stmt = $koneksi->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        'hasActiveOrder' => true,
        'status' => $row['status']
    ]);
} else {
    echo json_encode([
        'hasActiveOrder' => false
    ]);
}
