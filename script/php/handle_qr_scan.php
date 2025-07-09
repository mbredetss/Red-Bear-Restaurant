<?php
// Logika untuk menangani scan QR Code Meja
if (isset($_GET['table_id']) && is_numeric($_GET['table_id'])) {
  $table_id = intval($_GET['table_id']);

  // Cek apakah pelanggan sudah memiliki sesi aktif di meja lain
  $koneksi = koneksiDatabase("red bear");
  $current_session_code = session_id() . "_" . $table_id;

  // Cek apakah pelanggan sudah memiliki sesi aktif di meja lain
  $existing_session_query = "SELECT table_id FROM offline_table_sessions WHERE session_code LIKE ? AND status = 'occupied' AND DATE(created_at) = ?";
  $session_pattern = session_id() . "_%";
  $today = date('Y-m-d');
  $stmt_existing = $koneksi->prepare($existing_session_query);
  $stmt_existing->bind_param("ss", $session_pattern, $today);
  $stmt_existing->execute();
  $existing_result = $stmt_existing->get_result();
  $has_existing_session = $existing_result->num_rows > 0;
  $stmt_existing->close();

  if ($has_existing_session) {
    $existing_session = $existing_result->fetch_assoc();
    $existing_table_id = $existing_session['table_id'];

    // Jika mencoba scan meja yang berbeda, tolak akses
    if ($existing_table_id != $table_id) {
      $_SESSION['table_available'] = false;
      $_SESSION['table_unavailable_reason'] = 'different_table';
      $_SESSION['existing_table_id'] = $existing_table_id;
    } else {
      // Jika scan meja yang sama, izinkan akses
      $_SESSION['scanned_table_id'] = $table_id;
      $_SESSION['table_available'] = true;
    }
  } else {
    // Cek ketersediaan meja sebelum menyimpan ke session
    // Cek apakah meja sudah di-booking online hari ini
    $booking_query = "SELECT id FROM table_bookings WHERE table_id = ? AND booking_date = ? AND status = 'booked'";
    $stmt_booking = $koneksi->prepare($booking_query);
    $stmt_booking->bind_param("is", $table_id, $today);
    $stmt_booking->execute();
    $booking_result = $stmt_booking->get_result();
    $has_booking = $booking_result->num_rows > 0;
    $stmt_booking->close();

    // Cek apakah meja sudah ditempati offline hari ini
    $offline_query = "SELECT id FROM offline_table_sessions WHERE table_id = ? AND status = 'occupied' AND DATE(created_at) = ?";
    $stmt_offline = $koneksi->prepare($offline_query);
    $stmt_offline->bind_param("is", $table_id, $today);
    $stmt_offline->execute();
    $offline_result = $stmt_offline->get_result();
    $has_offline_session = $offline_result->num_rows > 0;
    $stmt_offline->close();

    // Jika meja tersedia, simpan ke session
    if (!$has_booking && !$has_offline_session) {
      $_SESSION['scanned_table_id'] = $table_id;
      $_SESSION['table_available'] = true;
    } else {
      $_SESSION['table_available'] = false;
      $_SESSION['table_unavailable_reason'] = $has_booking ? 'booking' : 'offline';
    }
  }
} 