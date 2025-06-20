<?php
session_start();
require_once '../../../database.php';


if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['items' => []]);
    exit;
}

$koneksi = koneksiDatabase("red bear");
$userId = $_SESSION['user_id'];

// Ambil semua order_items untuk user ini, beserta path gambarnya
$sql = "
 SELECT
  m.public_id AS menu_name,
  m.image_path,
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

// Tentukan base URL untuk membuat URL gambar absolut
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$base_dir = rtrim(dirname(dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])))), '/\\');
$base_url = "$protocol$host$base_dir/";

$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = [
        'name' => $row['menu_name'],
        'quantity' => (int) $row['quantity'],
        'status' => $row['status'],
        'image' => $base_url . $row['image_path']
    ];
}

echo json_encode(['items' => $items]);
