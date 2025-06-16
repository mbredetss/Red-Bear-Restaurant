<?php
include __DIR__ . '/../../../init.php';
require_once '../../../database.php';

$koneksi = koneksiDatabase("red bear");

// Ambil data dari menu.json
$jsonPath = __DIR__ . '/../menu.json';
$menuStatus = [];
if (file_exists($jsonPath)) {
  $menuStatus = json_decode(file_get_contents($jsonPath), true);
}

// Ambil gambar dari Cloudinary
$resources = $adminApi->assets(['type' => 'upload', 'prefix' => 'menu_items/']);
$menus = [];
foreach ($resources['resources'] as $res) {
  $publicId = pathinfo($res['public_id'], PATHINFO_FILENAME);

  $statusQuery = $koneksi->prepare("SELECT tersedia, jenis FROM menu WHERE public_id = ?");
  $statusQuery->bind_param("s", $publicId);
  $statusQuery->execute();
  $statusResult = $statusQuery->get_result();
  $statusRow = $statusResult->fetch_assoc();
  $tersedia = $statusRow['tersedia'] ?? false;
  $jenis = $statusRow['jenis'] ?? 'makanan';

  $menus[] = [
    'name' => $publicId,
    'tersedia' => $tersedia,
    'jenis' => $jenis,
    'image' => $res['secure_url']
  ];
}
header('Content-Type: application/json');
usort($menus, fn($a, $b) => strcmp($a['jenis'], $b['jenis']));
echo json_encode($menus);