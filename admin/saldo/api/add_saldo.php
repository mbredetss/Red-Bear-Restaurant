<?php
require_once '../../../database.php';
session_start();

header('Content-Type: application/json');

// Cek login admin
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Anda harus login untuk mengakses fitur ini.']);
    exit;
}

$koneksi = koneksiDatabase('red bear');

// Ambil data dari request
$input = json_decode(file_get_contents('php://input'), true);
$user_id = isset($input['user_id']) ? intval($input['user_id']) : 0;
$amount = isset($input['amount']) ? intval($input['amount']) : 0;

if (!$user_id || !$amount) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
    exit;
}

if ($amount < 1000) {
    echo json_encode(['success' => false, 'message' => 'Jumlah saldo minimal Rp1.000.']);
    exit;
}

// Cek apakah user ada
$stmt = $koneksi->prepare('SELECT id, saldo FROM users WHERE id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'User tidak ditemukan.']);
    exit;
}

$user = $result->fetch_assoc();
$saldo_baru = $user['saldo'] + $amount;

// Update saldo user
$stmt = $koneksi->prepare('UPDATE users SET saldo = ? WHERE id = ?');
$stmt->bind_param('di', $saldo_baru, $user_id);
$success = $stmt->execute();
$stmt->close();

if ($success) {
    echo json_encode([
        'success' => true, 
        'message' => 'Saldo berhasil ditambahkan! Saldo baru: Rp' . number_format($saldo_baru, 0, ',', '.'),
        'saldo_baru' => $saldo_baru
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menambah saldo.']);
} 