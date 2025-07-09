<?php
require_once '../../../database.php';
session_start();

header('Content-Type: application/json');

// Cek login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Anda harus login untuk memesan meja.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$koneksi = koneksiDatabase('red bear');

// Ambil data dari request (POST)
$table_id = isset($_POST['table_id']) ? intval($_POST['table_id']) : 0;
$guest_count = isset($_POST['guest_count']) ? intval($_POST['guest_count']) : 0;
$date = isset($_POST['date']) ? $_POST['date'] : null;
$time = isset($_POST['time']) ? $_POST['time'] : null;

if (!$table_id || !$guest_count || !$date || !$time) {
    echo json_encode(['success' => false, 'message' => 'Data booking tidak lengkap.']);
    exit;
}

// Cek saldo user
$stmt = $koneksi->prepare('SELECT saldo FROM users WHERE id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($saldo);
$stmt->fetch();
$stmt->close();

$biaya_booking = 400000; // 400.000 rupiah

if ($saldo < $biaya_booking) {
    echo json_encode(['success' => false, 'message' => 'Saldo tidak mencukupi. Dibutuhkan Rp' . number_format($biaya_booking, 0, ',', '.') . ' untuk booking meja.']);
    exit;
}

// Cek apakah meja sudah dibooking pada waktu & tanggal tersebut
$stmt = $koneksi->prepare('SELECT COUNT(*) FROM table_bookings WHERE table_id = ? AND booking_date = ? AND booking_time = ? AND status = "booked"');
$stmt->bind_param('iss', $table_id, $date, $time);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count > 0) {
    echo json_encode(['success' => false, 'message' => 'Meja sudah dibooking pada waktu tersebut.']);
    exit;
}

// Fungsi untuk menghasilkan kode meja yang aman
function generateTableCode() {
    // Kode 12 digit: 4 digit angka + 4 digit huruf + 4 digit angka
    $numbers1 = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    $letters = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 4));
    $numbers2 = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    return $numbers1 . $letters . $numbers2;
}

// Generate kode meja yang unik
do {
    $table_code = generateTableCode();
    $stmt = $koneksi->prepare('SELECT COUNT(*) FROM table_bookings WHERE table_code = ?');
    $stmt->bind_param('s', $table_code);
    $stmt->execute();
    $stmt->bind_result($code_exists);
    $stmt->fetch();
    $stmt->close();
} while ($code_exists > 0);

// Mulai transaksi
$koneksi->begin_transaction();

try {
    // Simpan booking dengan kode meja
    $stmt = $koneksi->prepare('INSERT INTO table_bookings (table_id, user_id, guest_count, booking_date, booking_time, table_code) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('iiisss', $table_id, $user_id, $guest_count, $date, $time, $table_code);
    $success = $stmt->execute();
    $stmt->close();
    
    if (!$success) {
        throw new Exception('Gagal menyimpan booking.');
    }
    
    // Kurangi saldo user
    $saldo_baru = $saldo - $biaya_booking;
    $stmt = $koneksi->prepare('UPDATE users SET saldo = ? WHERE id = ?');
    $stmt->bind_param('di', $saldo_baru, $user_id);
    $success = $stmt->execute();
    $stmt->close();
    
    if (!$success) {
        throw new Exception('Gagal mengupdate saldo.');
    }
    
    // Commit transaksi
    $koneksi->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Booking meja berhasil! Silahkan cek code meja di menu profile',
        'table_code' => $table_code,
        'saldo_baru' => $saldo_baru
    ]);
    
} catch (Exception $e) {
    // Rollback jika ada error
    $koneksi->rollback();
    echo json_encode(['success' => false, 'message' => 'Gagal booking meja: ' . $e->getMessage()]);
} 