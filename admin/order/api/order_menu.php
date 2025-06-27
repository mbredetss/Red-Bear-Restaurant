<?php
require_once '../../../database.php';
session_start();

header('Content-Type: application/json');

// Validasi baru: Cek apakah user sudah scan meja
if (!isset($_SESSION['scanned_table_id'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['success' => false, 'message' => 'Silakan scan QR code di meja Anda terlebih dahulu untuk memesan.']);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);

// Validasi input dasar
if (!isset($input['menu_id'], $input['quantity'], $input['username'], $input['guest_count'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Data pesanan tidak lengkap.']);
    exit;
}

$table_id = $_SESSION['scanned_table_id'];
$menu_id = $input['menu_id'];
$quantity = $input['quantity'];
$menu_name = isset($input['menu_name']) ? $input['menu_name'] : 'Menu tidak diketahui';
$username = trim($input['username']);
$guest_count = intval($input['guest_count']);
$note = isset($input['note']) ? trim($input['note']) : null;

if (empty($username) || $guest_count <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nama pemesan dan jumlah tamu harus valid.']);
    exit;
}

$koneksi = koneksiDatabase("red bear");

// --- Logika Sesi Meja Offline ---
$session_code = session_id() . "_" . $table_id;
$stmt = $koneksi->prepare("SELECT id FROM offline_table_sessions WHERE session_code = ? AND status = 'occupied'");
$stmt->bind_param("s", $session_code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Jika tidak ada, buat sesi baru dengan guest_count
    $stmt_insert = $koneksi->prepare("INSERT INTO offline_table_sessions (table_id, guest_count, session_code) VALUES (?, ?, ?)");
    $stmt_insert->bind_param("iis", $table_id, $guest_count, $session_code);
    $stmt_insert->execute();
    $offline_session_id = $stmt_insert->insert_id;
    $stmt_insert->close();
} else {
    // Jika sudah ada, ambil ID-nya dan update guest_count
    $row = $result->fetch_assoc();
    $offline_session_id = $row['id'];
    $stmt_update = $koneksi->prepare("UPDATE offline_table_sessions SET guest_count = ? WHERE id = ?");
    $stmt_update->bind_param("ii", $guest_count, $offline_session_id);
    $stmt_update->execute();
    $stmt_update->close();
}
$stmt->close();
// --- Akhir Logika Sesi ---

// Logika baru: Setiap item yang dipesan adalah satu baris di tabel 'orders'
$stmt = $koneksi->prepare(
    "INSERT INTO orders (offline_table_session_id, username, menu_id, menu_name, quantity, catatan, status) 
     VALUES (?, ?, ?, ?, ?, ?, 'menunggu')"
);
$stmt->bind_param("isisis", $offline_session_id, $username, $menu_id, $menu_name, $quantity, $note);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true, 'message' => 'Pesanan berhasil ditambahkan!']);
