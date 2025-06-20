<?php
require_once '../../../database.php';

$koneksi = koneksiDatabase("red bear");

// Ambil data dari database
$result = $koneksi->query("SELECT id, public_id, image_path, tersedia, jenis FROM menu");

// Tentukan base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$base_dir = rtrim(dirname(dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])))), '/\\'); // Naik 3 level dari /admin/menu/api
$base_url = "$protocol$host$base_dir/";

$menus = [];
while ($row = $result->fetch_assoc()) {
  $menus[] = [
    'id' => $row['id'],
    'name' => $row['public_id'],
    'image' => $base_url . $row['image_path'], // Buat URL absolut
    'tersedia' => (bool)$row['tersedia'],
    'jenis' => $row['jenis']
  ];
}

header('Content-Type: application/json');
usort($menus, fn($a, $b) => strcmp($a['jenis'], $b['jenis']));
echo json_encode($menus);