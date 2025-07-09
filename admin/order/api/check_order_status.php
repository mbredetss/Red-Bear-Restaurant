<?php
session_start();

require_once '../../../database.php'; // sesuaikan path

header('Content-Type: application/json');

$koneksi = koneksiDatabase("red bear");
$hasActiveOrder = false;
$status = null;

// Cek apakah user login atau pelanggan offline
if (isset($_SESSION['user_id'])) {
    // User yang sudah login - cek pesanan berdasarkan booking_id
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
        // User memiliki booking - cek pesanan berdasarkan booking_id
        $booking = $result_booking->fetch_assoc();
        $booking_id = $booking['id'];
        
        $sql = "SELECT o.status 
                FROM orders o
                WHERE o.booking_id = ? AND o.status NOT IN ('selesai', 'ditolak')
                ORDER BY o.id DESC
                LIMIT 1";
        
        $stmt = $koneksi->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $booking_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                $hasActiveOrder = true;
                $status = $row['status'];
            }
            $stmt->close();
        }
    }
    // Jika user tidak memiliki booking, tidak ada pesanan aktif
    $stmt_booking->close();
    
} elseif (isset($_SESSION['scanned_table_id'])) {
    // Pelanggan offline - cek pesanan berdasarkan offline session
    $session_code = session_id() . "_" . $_SESSION['scanned_table_id'];
    $stmt_session = $koneksi->prepare("SELECT id FROM offline_table_sessions WHERE session_code = ?");
    $stmt_session->bind_param("s", $session_code);
    $stmt_session->execute();
    $result_session = $stmt_session->get_result();
    
    if ($result_session->num_rows > 0) {
        $offline_session_id = $result_session->fetch_assoc()['id'];
        $stmt_session->close();
        
        $sql = "SELECT o.status 
        FROM orders o
                WHERE o.offline_table_session_id = ? AND o.status NOT IN ('selesai', 'ditolak')
        ORDER BY o.id DESC
                LIMIT 1";

    $stmt = $koneksi->prepare($sql);
    if ($stmt) {
            $stmt->bind_param("i", $offline_session_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $hasActiveOrder = true;
            $status = $row['status'];
        }
        $stmt->close();
        }
    } else {
        $stmt_session->close();
    }
}

echo json_encode([
    'hasActiveOrder' => $hasActiveOrder,
    'status' => $status
]);
