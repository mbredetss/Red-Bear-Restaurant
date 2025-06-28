<?php
require_once '../../../database.php';
session_start();

header('Content-Type: application/json');

$input = json_decode(file_get_contents("php://input"), true);

// Validasi input dasar
if (!isset($input['menu_id'], $input['quantity'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Data pesanan tidak lengkap.']);
    exit;
}

$menu_id = $input['menu_id'];
$quantity = $input['quantity'];
$menu_name = isset($input['menu_name']) ? $input['menu_name'] : 'Menu tidak diketahui';
$note = isset($input['note']) ? trim($input['note']) : null;

$koneksi = koneksiDatabase("red bear");

// Cek apakah user sudah login dan memiliki booking meja hari ini
$booking_id = null;
$offline_session_id = null;
$username = null;
$guest_count = null;
$order_type = 'offline';

if (isset($_SESSION['user_id'])) {
    // User login - cek booking meja
    $user_id = $_SESSION['user_id'];
    $today = date('Y-m-d');
    $stmt = $koneksi->prepare("
        SELECT tb.id, tb.table_id, tb.table_code, tb.guest_count, u.name
        FROM table_bookings tb 
        JOIN users u ON tb.user_id = u.id
        WHERE tb.user_id = ? AND tb.booking_date = ? AND tb.status = 'booked'
        ORDER BY tb.booking_time ASC 
        LIMIT 1
    ");
    $stmt->bind_param("is", $user_id, $today);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $booking = $result->fetch_assoc();
        $booking_id = $booking['id'];
        $username = $booking['name'];
        $guest_count = $booking['guest_count'];
        $order_type = 'booking';
    }
    $stmt->close();
}

// Jika tidak ada booking, cek scan QR (pelanggan offline)
if (!$booking_id && isset($_SESSION['scanned_table_id'])) {
    $table_id = $_SESSION['scanned_table_id'];
    $username = isset($input['username']) ? trim($input['username']) : 'Pelanggan';
    $guest_count = isset($input['guest_count']) ? intval($input['guest_count']) : 1;
    
    if (empty($username) || $guest_count <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Nama pemesan dan jumlah tamu harus valid.']);
        exit;
    }
    
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
}

// Jika tidak ada booking atau scan QR, tolak pesanan
if (!$booking_id && !$offline_session_id) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Silakan pesan meja terlebih dahulu atau scan QR code di meja Anda.']);
    exit;
}

// Simpan pesanan berdasarkan tipe
if ($order_type === 'booking') {
    // Pesanan untuk user yang sudah booking
    $stmt = $koneksi->prepare(
        "INSERT INTO orders (booking_id, username, menu_id, menu_name, quantity, catatan, status, order_type) 
         VALUES (?, ?, ?, ?, ?, ?, 'menunggu', 'booking')"
    );
    $stmt->bind_param("isisis", $booking_id, $username, $menu_id, $menu_name, $quantity, $note);
} else {
    // Pesanan untuk pelanggan offline
    $stmt = $koneksi->prepare(
        "INSERT INTO orders (offline_table_session_id, username, menu_id, menu_name, quantity, catatan, status, order_type) 
         VALUES (?, ?, ?, ?, ?, ?, 'menunggu', 'offline')"
    );
    $stmt->bind_param("isisis", $offline_session_id, $username, $menu_id, $menu_name, $quantity, $note);
}

$stmt->execute();
$stmt->close();

echo json_encode(['success' => true, 'message' => 'Pesanan berhasil ditambahkan!']);
