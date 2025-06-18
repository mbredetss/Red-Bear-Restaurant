<?php
session_start();
require_once '../../../database.php';
require_once '../../../init.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['items' => []]);
    exit;
}

$koneksi = koneksiDatabase("red bear");
$userId = $_SESSION['user_id'];

// Ambil semua order_items untuk user ini, terbaru dulu
$sql = "
 SELECT
  m.public_id AS menu_name,
  oi.quantity,
  oi.status
FROM orders o
JOIN order_items oi ON o.id     = oi.order_id
JOIN menu        m  ON oi.menu_id = m.id
WHERE o.user_id = ?
ORDER BY oi.id DESC;

";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

// Ambil semua gambar dari Cloudinary sekali saja
$imageMapping = [];
try {
    $resources = $adminApi->assets(['type' => 'upload', 'prefix' => 'menu_items/']);
    foreach ($resources['resources'] as $res) {
        $publicId = pathinfo($res['public_id'], PATHINFO_FILENAME);
        $imageMapping[$publicId] = $res['secure_url'];
    }
} catch (Exception $e) {
    // Jika gagal mengambil dari Cloudinary, gunakan mapping kosong
    $imageMapping = [];
}

$items = [];
while ($row = $result->fetch_assoc()) {
    $publicId = $row['menu_name'];
    $imageUrl = $imageMapping[$publicId] ?? '';
    
    $items[] = [
        'name' => $row['menu_name'],
        'quantity' => (int) $row['quantity'],
        'status' => $row['status'],
        'image' => $imageUrl
    ];
}

echo json_encode(['items' => $items]);
