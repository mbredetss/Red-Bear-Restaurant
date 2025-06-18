<?php
require_once '../../../database.php';
session_start();

$raw = file_get_contents("php://input");
error_log("RAW INPUT: " . $raw);

$input = json_decode($raw, true);
error_log("PARSED INPUT: " . print_r($input, true));


if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);
$menu_id = $input['menu_id'];
$quantity = $input['quantity'];
error_log("Menu ID: " . $menu_id);  // cek di log error

$koneksi = koneksiDatabase("red bear");

// Buat order baru
$koneksi->query("INSERT INTO orders (user_id) VALUES ({$_SESSION['user_id']})");
$order_id = $koneksi->insert_id;

// Tambahkan item ke order_items
$stmt = $koneksi->prepare("INSERT INTO order_items (order_id, menu_id, quantity, status) VALUES (?, ?, ?, 'menunggu')");
$stmt->bind_param("iii", $order_id, $menu_id, $quantity);
$stmt->execute();

echo json_encode(['message' => 'Pesanan berhasil dibuat!']);
