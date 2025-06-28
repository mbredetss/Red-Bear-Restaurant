<?php
session_start();
require_once '../../../database.php';

header('Content-Type: application/json');

$koneksi = koneksiDatabase("red bear");
$items = [];

// Tentukan base URL untuk membuat URL gambar absolut
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$base_dir = rtrim(dirname(dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])))), '/\\');
$base_url = "$protocol$host$base_dir/";

$whereClause = "";
$params = [];
$types = "";

// Cek apakah user login atau pelanggan offline
if (isset($_SESSION['user_id'])) {
    // User online - cek apakah memiliki booking aktif
    $user_id = $_SESSION['user_id'];
    $today = date('Y-m-d');
    
    // Cek apakah user memiliki booking aktif hari ini
    $stmt_booking = $koneksi->prepare("
        SELECT tb.id 
        FROM table_bookings tb 
        WHERE tb.user_id = ? AND tb.booking_date = ? AND tb.status = 'booked'
        ORDER BY tb.booking_time ASC 
        LIMIT 1
    ");
    $stmt_booking->bind_param("is", $user_id, $today);
    $stmt_booking->execute();
    $result_booking = $stmt_booking->get_result();
    
    if ($result_booking->num_rows > 0) {
        // User memiliki booking - ambil pesanan berdasarkan booking_id
        $booking = $result_booking->fetch_assoc();
        $booking_id = $booking['id'];
        
        $whereClause = "o.booking_id = ?";
        $params[] = $booking_id;
        $types .= "i";
    } else {
        // User tidak memiliki booking - tidak ada pesanan untuk ditampilkan
        echo json_encode(['items' => []]);
        exit;
    }
    $stmt_booking->close();
    
} elseif (isset($_SESSION['scanned_table_id'])) {
    // User offline
    $session_code = session_id() . "_" . $_SESSION['scanned_table_id'];
    
    $stmt_session = $koneksi->prepare("SELECT id FROM offline_table_sessions WHERE session_code = ?");
    $stmt_session->bind_param("s", $session_code);
    $stmt_session->execute();
    $result_session = $stmt_session->get_result();
    if ($result_session->num_rows > 0) {
        $offline_session_id = $result_session->fetch_assoc()['id'];
        $whereClause = "o.offline_table_session_id = ?";
        $params[] = $offline_session_id;
        $types .= "i";
    } else {
        echo json_encode(['items' => []]);
        exit;
    }
    $stmt_session->close();
} else {
    // Tidak ada sesi valid
    echo json_encode(['items' => []]);
    exit;
}

// Query untuk mengambil pesanan dari tabel 'orders'
$sql = "
 SELECT
  o.menu_name,
  o.quantity,
  o.status,
  m.image_path,
  m.jenis
FROM orders o
LEFT JOIN menu m ON o.menu_id = m.id
WHERE $whereClause
ORDER BY o.id DESC;
";

$stmt = $koneksi->prepare($sql);
if ($stmt && !empty($types)) {
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $items[] = [
            'name' => $row['menu_name'],
            'quantity' => (int) $row['quantity'],
            'status' => $row['status'],
            'image' => $row['image_path'] ? $base_url . $row['image_path'] : null,
            'jenis' => $row['jenis'] ?? null
        ];
    }
    $stmt->close();
}

echo json_encode(['items' => $items]);
