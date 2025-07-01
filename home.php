<?php
require_once 'script/session_check.php'; // Mengimpor file session_check.php

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

$koneksi = koneksiDatabase("red bear");

// Ambil postingan blog yang sudah dipublikasikan
$blog_posts = [];
$query_blog = "SELECT bp.id, bp.title, bp.content, bp.image_path, bp.created_at, u.name as author_name FROM blog_posts bp JOIN users u ON bp.user_id = u.id WHERE bp.status = 'published' ORDER BY bp.created_at DESC LIMIT 3"; // Ambil 3 postingan terbaru
$result_blog = $koneksi->query($query_blog);
if ($result_blog) {
  while ($row = $result_blog->fetch_assoc()) {
    $blog_posts[] = $row;
  }
}
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Red Bear - Korean BBQ Test</title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800&family=Poppins:wght@400;500;600&display=swap"
    rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link href="style/styles.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    /* Animasi untuk modal */
    .animate-in {
      animation: modalSlideIn 0.3s ease-out forwards;
    }

    @keyframes modalSlideIn {
      from {
        opacity: 0;
        transform: scale(0.9) translateY(-20px);
      }

      to {
        opacity: 1;
        transform: scale(1) translateY(0);
      }
    }

    /* Hover effect untuk tombol guest count */
    #guestCountBtn:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* Focus state untuk accessibility */
    #guestCountBtn:focus {
      outline: none;
      ring: 2px;
      ring-color: #ef4444;
    }

    /* Disabled state untuk tombol kontrol */
    .opacity-50.cursor-not-allowed {
      pointer-events: none;
    }

    /* Responsive design untuk modal */
    @media (max-width: 640px) {
      #bookTableModal {
        padding: 0.5rem;
      }

      #bookTableModal>div {
        max-height: calc(100vh - 1rem);
        margin: 0;
      }

      #guestCountModal>div,
      #datePickerModal>div,
      #timePickerModal>div {
        margin: 1rem;
        max-width: calc(100vw - 2rem);
      }

      #guestCountBtn,
      #datePickerBtn,
      #timePickerBtn {
        padding: 0.75rem 1rem;
      }

      #guestCountBtn svg,
      #datePickerBtn svg,
      #timePickerBtn svg {
        width: 1.25rem;
        height: 1.25rem;
      }

      #calendarGrid {
        gap: 0.25rem;
      }

      #calendarGrid>div {
        width: 2rem;
        height: 2rem;
        font-size: 0.875rem;
      }

      #timeGrid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.5rem;
      }

      #timeGrid button {
        padding: 0.75rem 0.5rem;
        font-size: 0.875rem;
      }
    }

    /* Desktop scroll improvements */
    @media (min-width: 641px) {
      #bookTableModal {
        padding: 2rem;
      }

      #bookTableModal>div {
        max-height: calc(100vh - 4rem);
        margin: 0 auto;
      }

      #bookTableModal .overflow-y-auto {
        max-height: 50vh;
        scrollbar-width: thin;
        scrollbar-color: #c1c1c1 #f1f1f1;
        scroll-behavior: smooth;
      }

      #bookTableModal .overflow-y-auto::-webkit-scrollbar {
        width: 12px;
      }

      #bookTableModal .overflow-y-auto::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 6px;
        margin: 4px 0;
      }

      #bookTableModal .overflow-y-auto::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, #c1c1c1 0%, #a8a8a8 100%);
        border-radius: 6px;
        border: 2px solid #f1f1f1;
      }

      #bookTableModal .overflow-y-auto::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(180deg, #a8a8a8 0%, #909090 100%);
      }

      #bookTableModal .overflow-y-auto::-webkit-scrollbar-corner {
        background: #f1f1f1;
      }
    }

    /* Custom scrollbar untuk modal content */
    #bookTableModal .overflow-y-auto::-webkit-scrollbar {
      width: 8px;
    }

    #bookTableModal .overflow-y-auto::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 4px;
      margin: 4px 0;
    }

    #bookTableModal .overflow-y-auto::-webkit-scrollbar-thumb {
      background: #c1c1c1;
      border-radius: 4px;
      border: 2px solid #f1f1f1;
    }

    #bookTableModal .overflow-y-auto::-webkit-scrollbar-thumb:hover {
      background: #a8a8a8;
    }

    #bookTableModal .overflow-y-auto::-webkit-scrollbar-corner {
      background: #f1f1f1;
    }

    /* Firefox scrollbar */
    #bookTableModal .overflow-y-auto {
      scrollbar-width: thin;
      scrollbar-color: #c1c1c1 #f1f1f1;
    }

    /* Desktop specific scroll improvements */
    @media (min-width: 641px) {
      #bookTableModal .overflow-y-auto {
        max-height: 50vh;
        overflow-y: auto;
        overflow-x: hidden;
      }

      #bookTableModal .overflow-y-auto::-webkit-scrollbar {
        width: 10px;
      }

      #bookTableModal .overflow-y-auto::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, #c1c1c1 0%, #a8a8a8 100%);
        border-radius: 5px;
        border: 2px solid #f1f1f1;
      }

      #bookTableModal .overflow-y-auto::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(180deg, #a8a8a8 0%, #909090 100%);
      }
    }

    /* Smooth transitions untuk semua elemen */
    * {
      transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
      transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
      transition-duration: 150ms;
    }

    /* Enhanced scrollbar untuk PC */
    @media (min-width: 641px) {
      #bookTableModal .overflow-y-auto {
        scroll-behavior: smooth;
        scrollbar-width: thin;
        scrollbar-color: #c1c1c1 #f1f1f1;
      }

      #bookTableModal .overflow-y-auto::-webkit-scrollbar {
        width: 12px;
      }

      #bookTableModal .overflow-y-auto::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 6px;
        margin: 4px 0;
      }

      #bookTableModal .overflow-y-auto::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, #c1c1c1 0%, #a8a8a8 100%);
        border-radius: 6px;
        border: 2px solid #f1f1f1;
      }

      #bookTableModal .overflow-y-auto::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(180deg, #a8a8a8 0%, #909090 100%);
      }

      #bookTableModal .overflow-y-auto::-webkit-scrollbar-corner {
        background: #f1f1f1;
      }
    }
  </style>
</head>

<body class="bg-gray-50">
  <!-- Navbar -->
  <header id="navbar" class="fixed w-full z-50 flex items-center justify-between px-6 py-4">
    <a href="home.php">
      <img src="img/red-bear-logo.png" alt="Logo" class="h-16" style="border-radius: 50%;">
    </a>

    <nav class="flex gap-1 md:gap-4 items-center">
      <a href="#"
        class="hover:bg-white/10 text-white px-3 py-2 rounded-md font-semibold text-sm transition-colors">HOME</a>
      <a href="#menu"
        class="hover:bg-white/10 text-white px-3 py-2 rounded-md font-semibold text-sm transition-colors">MENU</a>
      <a href="#"
        class="hover:bg-white/10 text-white px-3 py-2 rounded-md font-semibold text-sm transition-colors">MERCHANDISE</a>
      <a href="#" id="tableStatusBtn"
        class="hover:bg-white/10 text-white px-3 py-2 rounded-md font-semibold text-sm transition-colors">STATUS
        MEJA</a>

      <!-- More Dropdown -->
      <div class="relative" id="more-dropdown">
        <button id="more-btn"
          class="hover:bg-white/10 text-white px-3 py-2 rounded-md font-semibold text-sm transition-colors flex items-center gap-1">
          MORE <i class="fas fa-chevron-down text-xs"></i>
        </button>
        <div id="more-menu"
          class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl py-1 z-50 text-gray-800 dropdown-menu">
          <a href="#about" class="block px-4 py-2 text-sm hover:bg-gray-100">ABOUT</a>
          <a href="#location" class="block px-4 py-2 text-sm hover:bg-gray-100">LOCATION</a>
          <a href="#blog" class="block px-4 py-2 text-sm hover:bg-gray-100">BLOG</a>
        </div>
      </div>

      <a id="bookTable" href="#"
        class="bg-red-600 text-white px-5 py-2 rounded-full font-bold text-sm hover:bg-red-700 transition-all duration-300 shadow-lg">BOOK
        A TABLE</a>

      <!-- Modal Book A Table -->
      <div id="bookTableModal"
        class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50 px-4 py-4 overflow-y-auto">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md min-h-fit max-h-none my-8 flex flex-col relative">
          <!-- Header -->
          <div class="p-6 border-b border-gray-200 flex-shrink-0">
            <button id="closeBookTableModal"
              class="absolute top-4 right-4 text-gray-400 hover:text-gray-800 text-2xl font-bold">&times;</button>
            <h2 class="text-2xl font-bold text-center text-gray-800">Book A Table</h2>
          </div>

          <!-- Content Area - Scrollable -->
          <div class="flex-1 p-6 overflow-y-auto max-h-[60vh]"
            style="scroll-behavior: smooth; scrollbar-width: thin; scrollbar-color: #c1c1c1 #f1f1f1;">
            <!-- Pilihan Jumlah Tamu -->
            <div class="w-full mb-4">
              <label class="block text-gray-700 font-semibold mb-2">Jumlah Tamu</label>
              <button id="guestCountBtn"
                class="w-full bg-red-800 text-white px-4 py-3 rounded-lg flex items-center justify-start hover:bg-red-900 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-red-500"
                aria-label="Pilih jumlah tamu" aria-haspopup="dialog">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                  </path>
                </svg>
                <span id="guestCountText" class="font-medium">2 Guests</span>
              </button>
              <input type="hidden" id="guestCount" value="2">
            </div>
            <!-- Pilihan Tanggal -->
            <div class="w-full mb-4">
              <label class="block text-gray-700 font-semibold mb-2">Tanggal</label>
              <button id="datePickerBtn"
                class="w-full bg-red-800 text-white px-4 py-3 rounded-lg flex items-center justify-start hover:bg-red-900 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 border border-white/20"
                aria-label="Pilih tanggal" aria-haspopup="dialog">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span id="selectedDateText" class="font-medium">Pilih Tanggal</span>
              </button>
              <input type="hidden" id="bookingDate" value="" />
            </div>
            <!-- Pilihan Waktu -->
            <div class="w-full mb-6">
              <label class="block text-gray-700 font-semibold mb-2">Waktu</label>
              <button id="timePickerBtn"
                class="w-full bg-red-800 text-white px-4 py-3 rounded-lg flex items-center justify-start hover:bg-red-900 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 border border-red-600"
                aria-label="Pilih waktu" aria-haspopup="dialog">
                <svg class="w-5 h-5 mr-3 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <circle cx="12" cy="12" r="10" stroke-width="2"></circle>
                  <polyline points="12,6 12,12 16,14" stroke-width="2"></polyline>
                </svg>
                <span id="selectedTimeText" class="font-medium">Select a time</span>
              </button>
              <input type="hidden" id="bookingTime" value="" />
            </div>
            <!-- Pilihan Meja -->
            <div class="w-full mb-6">
              <label class="block text-gray-700 font-semibold mb-2">Pilih Meja</label>
              <div id="tableIcons" class="grid grid-cols-4 gap-4 justify-items-center">
                <!-- Meja akan di-generate secara dinamis berdasarkan status real-time -->
              </div>
            </div>
            <!-- Spacer untuk memastikan ada ruang di bawah -->
            <div class="w-full h-4"></div>
          </div>

          <!-- Footer - Fixed -->
          <div class="p-6 border-t border-gray-200 flex-shrink-0">
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
              <div class="flex items-center">
                <i class="fas fa-info-circle text-yellow-600 mr-2"></i>
                <div>
                  <p class="text-sm font-medium text-yellow-800">Biaya Booking Meja</p>
                  <p class="text-lg font-bold text-yellow-900">Rp400.000</p>
                  <p class="text-xs text-yellow-700 mt-1">Saldo Anda: <span id="userSaldo"
                      class="font-medium">Rp<?php echo number_format($saldo, 0, ',', '.'); ?></span></p>
                </div>
              </div>
            </div>
            <button id="submitBookTable"
              class="w-full bg-red-600 text-white py-3 rounded-lg font-bold hover:bg-red-700 transition-colors shadow-lg hover:shadow-xl">
              <i class="fas fa-credit-card mr-2"></i>Pesan Meja (Rp400.000)
            </button>
          </div>
        </div>
      </div>

      <!-- Modal Pilih Jumlah Tamu -->
      <div id="guestCountModal"
        class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50 px-4" role="dialog"
        aria-labelledby="guestModalTitle" aria-modal="true">
        <div class="bg-red-800 rounded-xl shadow-2xl w-full max-w-sm p-6 relative">
          <!-- Tombol Tutup -->
          <button id="closeGuestCountModal"
            class="absolute top-4 right-4 text-white hover:text-gray-300 text-2xl font-bold w-8 h-8 rounded-full border border-white/30 flex items-center justify-center hover:bg-white/10 transition-colors"
            aria-label="Tutup modal">&times;</button>

          <!-- Header -->
          <div class="text-center mb-6">
            <h3 id="guestModalTitle" class="text-2xl font-bold text-white">Guests</h3>
          </div>

          <!-- Kontrol Jumlah Tamu -->
          <div class="flex items-center justify-between mb-8">
            <div class="text-white font-medium">Party size</div>
            <div class="flex items-center space-x-3">
              <button id="decreaseGuest"
                class="w-10 h-10 rounded-full border-2 border-white text-white hover:bg-white hover:text-red-800 transition-colors flex items-center justify-center font-bold text-lg"
                aria-label="Kurangi jumlah tamu">−</button>
              <div id="guestDisplay"
                class="w-16 h-10 rounded-lg border-2 border-white text-white flex items-center justify-center font-bold text-lg bg-transparent"
                role="status" aria-live="polite">2</div>
              <button id="increaseGuest"
                class="w-10 h-10 rounded-full border-2 border-white text-white hover:bg-white hover:text-red-800 transition-colors flex items-center justify-center font-bold text-lg"
                aria-label="Tambah jumlah tamu">+</button>
            </div>
          </div>

          <!-- Tombol Konfirmasi -->
          <button id="confirmGuestCount"
            class="w-full bg-black text-yellow-400 py-3 rounded-lg font-bold hover:bg-gray-900 transition-colors">
            Confirm
          </button>
        </div>
      </div>

      <!-- Modal Kalender -->
      <div id="datePickerModal"
        class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50 px-4" role="dialog"
        aria-labelledby="dateModalTitle" aria-modal="true">
        <div class="bg-red-800 rounded-xl shadow-2xl w-full max-w-sm p-6 relative">
          <!-- Tombol Tutup -->
          <button id="closeDatePickerModal"
            class="absolute top-4 right-4 text-white hover:text-gray-300 text-2xl font-bold w-8 h-8 rounded-full border border-white/30 flex items-center justify-center hover:bg-white/10 transition-colors"
            aria-label="Tutup modal">&times;</button>

          <!-- Header -->
          <div class="text-center mb-6">
            <h3 id="dateModalTitle" class="text-2xl font-bold text-white">Select a date</h3>
          </div>

          <!-- Navigasi Bulan -->
          <div class="flex items-center justify-between mb-6">
            <button id="prevMonth"
              class="w-8 h-8 rounded-full border border-white text-white hover:bg-white hover:text-red-800 transition-colors flex items-center justify-center">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
              </svg>
            </button>
            <div id="currentMonthYear" class="text-white font-semibold text-lg">Jul 2025</div>
            <button id="nextMonth"
              class="w-8 h-8 rounded-full border border-white text-white hover:bg-white hover:text-red-800 transition-colors flex items-center justify-center">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </button>
          </div>

          <!-- Label Hari -->
          <div class="grid grid-cols-7 gap-1 mb-3">
            <div class="text-white text-center text-sm font-medium py-2">Sun</div>
            <div class="text-white text-center text-sm font-medium py-2">Mon</div>
            <div class="text-white text-center text-sm font-medium py-2">Tue</div>
            <div class="text-white text-center text-sm font-medium py-2">Wed</div>
            <div class="text-white text-center text-sm font-medium py-2">Thu</div>
            <div class="text-white text-center text-sm font-medium py-2">Fri</div>
            <div class="text-white text-center text-sm font-medium py-2">Sat</div>
          </div>

          <!-- Grid Kalender -->
          <div id="calendarGrid" class="grid grid-cols-7 gap-1 mb-6">
            <!-- Tanggal akan di-generate via JavaScript -->
          </div>

          <!-- Tombol Konfirmasi -->
          <button id="confirmDate"
            class="w-full bg-black text-yellow-400 py-3 rounded-lg font-bold hover:bg-gray-900 transition-colors">
            Confirm
          </button>
        </div>
      </div>

      <!-- Modal Time Picker -->
      <div id="timePickerModal"
        class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50 px-4" role="dialog"
        aria-labelledby="timeModalTitle" aria-modal="true">
        <div class="bg-red-800 rounded-xl shadow-2xl w-full max-w-md p-6 relative">
          <!-- Tombol Tutup -->
          <button id="closeTimePickerModal"
            class="absolute top-4 right-4 text-white hover:text-gray-300 text-2xl font-bold w-8 h-8 rounded-full border border-white/30 flex items-center justify-center hover:bg-white/10 transition-colors"
            aria-label="Tutup modal">&times;</button>

          <!-- Header -->
          <div class="text-center mb-6">
            <h3 id="timeModalTitle" class="text-2xl font-bold text-white">Select a time</h3>
          </div>

          <!-- Grid Waktu -->
          <div id="timeGrid" class="grid grid-cols-3 gap-3 mb-6 overflow-y-auto" style="max-height:40vh;">
            <!-- Waktu akan di-generate via JavaScript -->
          </div>

          <!-- Tombol Konfirmasi -->
          <button id="confirmTime"
            class="w-full bg-black text-yellow-400 py-3 rounded-lg font-bold hover:bg-gray-900 transition-colors">
            Confirm
          </button>
        </div>
      </div>

      <a id="orderStatusBtn" href="#" class="relative text-white hover:bg-white/10 p-2 rounded-full">
        <i class="fas fa-shopping-cart text-xl"></i>
        <span id="orderBadge" class="absolute -top-0.5 -right-0.5 flex h-3 w-3">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
          <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
        </span>
      </a>

      <?php if ($user): ?>
        <!-- Tampilkan Avatar & Dropdown jika login -->
        <div class="relative ml-4">
          <button id="userAvatar"
            class="flex items-center justify-center h-10 w-10 rounded-full bg-white text-gray-800 border border-gray-300 shadow hover:shadow-lg transition duration-200 focus:outline-none">
            <i class="fas fa-user"></i>
          </button>

          <div id="profileDropdown"
            class="hidden absolute right-0 mt-3 w-56 bg-white rounded-xl shadow-lg z-50 text-sm text-gray-700 overflow-hidden ring-1 ring-black/5 transition-all duration-300 dropdown-menu">
            <div class="px-4 py-3 border-b">
              <p class="font-semibold">Halo, <?= htmlspecialchars($user['name']) ?></p>
              <p class="text-xs text-gray-500 mt-1">Saldo: <span
                  class="font-medium text-green-600">Rp<?= number_format($saldo, 0, ',', '.') ?></span></p>
              <div id="tableCodeInfo" class="mt-2 text-xs text-blue-600 hidden">
                <p class="font-semibold">Kode Meja Anda:</p>
                <p id="userTableCode" class="font-mono font-bold text-lg text-blue-800"></p>
                <p id="tableCodeDetails" class="text-blue-700"></p>
              </div>
            </div>
            <a href="logout.php" class="block px-4 py-2 hover:bg-gray-100">🚪 Logout</a>
          </div>
        </div>
      <?php else: ?>
        <!-- Tampilkan tombol login jika belum login -->
        <a href="login_register/login.php"
          class="bg-white text-gray-800 border border-gray-300 px-4 py-2 rounded-full hover:bg-gray-100 hover:shadow-md font-bold text-sm transition-all">Login</a>
      <?php endif; ?>
    </nav>
  </header>

  <!-- Modal Meja Tidak Tersedia -->
  <div id="tableUnavailableModal"
    class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50 px-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-8 relative text-center">
      <div class="mb-6">
        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Meja Tidak Tersedia</h2>
        <p class="text-gray-600" id="unavailableReason">Meja ini sudah ditempati oleh pelanggan lain.</p>
      </div>

      <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left">
        <h3 class="font-semibold text-gray-800 mb-2">Informasi Meja:</h3>
        <div id="tableInfo" class="text-sm text-gray-600">
          <!-- Informasi meja akan diisi via JavaScript -->
        </div>
      </div>

      <div class="flex flex-col sm:flex-row gap-3">
        <button id="closeUnavailableModal"
          class="flex-1 bg-gray-500 text-white py-3 rounded-lg font-bold hover:bg-gray-600 transition-colors">
          Tutup
        </button>
        <a href="home.php"
          class="flex-1 bg-red-600 text-white py-3 rounded-lg font-bold hover:bg-red-700 transition-colors text-center">
          Kembali ke Beranda
        </a>
      </div>
    </div>
  </div>

  <!-- Hero Section -->
  <section class="relative h-screen bg-cover bg-center carousel-slide overflow-hidden"
    style="background-image: url('img/image1.png');">
    <div class="carousel-bg absolute inset-0 transition-opacity duration-700 opacity-0 pointer-events-none"></div>
    <div class="absolute inset-0 hero-gradient flex flex-col items-center justify-center text-center text-white px-4">
      <h1 class="text-6xl md:text-7xl font-extrabold" style="font-family: 'Montserrat', sans-serif;">RED BEAR</h1>
      <p class="text-xl md:text-2xl mt-4 font-light tracking-wider">PREMIUM KOREAN BARBEQUE</p>

      <!-- Carousel indicators -->
      <div class="absolute bottom-10 flex gap-3 mt-8">
        <span class="carousel-indicator active w-3 h-3 bg-white rounded-full cursor-pointer" data-slide="0"></span>
        <span class="carousel-indicator w-3 h-3 bg-white/60 rounded-full cursor-pointer" data-slide="1"></span>
        <span class="carousel-indicator w-3 h-3 bg-white/60 rounded-full cursor-pointer" data-slide="2"></span>
      </div>

      <!-- Navigation buttons -->
      <button
        class="carousel-btn prev-btn absolute left-4 md:left-10 top-1/2 transform -translate-y-1/2 p-3 text-white bg-black/30 rounded-full hover:bg-black/50 transition-colors">&#10094;</button>
      <button
        class="carousel-btn next-btn absolute right-4 md:right-10 top-1/2 transform -translate-y-1/2 p-3 text-white bg-black/30 rounded-full hover:bg-black/50 transition-colors">&#10095;</button>
    </div>
  </section>

  <!-- Public Display + Menu Grid -->
  <section id="menu" class="py-20 bg-gray-50">
    <div class="text-center mb-12">
      <h2 class="text-4xl font-bold text-gray-800">TASTY BITES</h2>
      <p class="text-gray-500 mt-2">Discover our signature dishes and popular drinks.</p>
    </div>

    <!-- Menu Grid -->
    <div id="menu-container"
      class="container mx-auto px-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8"></div>
  </section>

  <div id="pesan-modal" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50 px-4">
    <div id="pesan-modal-content"
      class="bg-white p-6 rounded-xl shadow-2xl w-full max-w-sm space-y-4 relative transform transition-all opacity-0 scale-95">
      <button id="pesan-close"
        class="absolute top-3 right-4 text-gray-400 hover:text-gray-800 text-2xl font-bold">&times;</button>
      <h3 id="pesan-menu-nama" class="text-xl font-bold text-gray-800">Pesan Menu</h3>

      <div class="space-y-3">
        <div>
          <label for="pesan-nama" class="font-semibold text-gray-600 text-sm">Namas Pemesan</label>
          <input type="text" id="pesan-nama" placeholder="Masukkan nama Anda"
            class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" />
        </div>
        <div>
          <label for="pesan-tamu" class="font-semibold text-gray-600 text-sm">Jumlah Tamu di Meja</label>
          <input type="number" id="pesan-tamu" min="1" value="1"
            class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" />
        </div>
        <div>
          <label for="pesan-jumlah" class="font-semibold text-gray-600 text-sm">Jumlah Pesanan Menu Ini</label>
          <input type="number" id="pesan-jumlah" min="1" value="1"
            class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" />
        </div>
        <div>
          <label for="pesan-catatan" class="font-semibold text-gray-600 text-sm">Catatan (Opsional)</label>
          <textarea id="pesan-catatan" rows="2" placeholder="Contoh: Pedas, tidak pakai sayur"
            class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500"></textarea>
        </div>
      </div>

      <button id="pesan-kirim"
        class="w-full bg-red-600 text-white py-3 rounded-lg font-bold hover:bg-red-700 transition-colors shadow-lg hover:shadow-xl">
        <i class="fas fa-paper-plane mr-2"></i>Tambah Pesanan
      </button>
    </div>
  </div>

  <!-- Cart Modal -->
  <div id="cart-modal" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50 px-4">
    <div id="cart-modal-content"
      class="bg-white rounded-xl shadow-2xl w-full max-w-md max-h-[85vh] flex flex-col transform transition-all opacity-0 scale-95">
      <div class="p-6 border-b border-gray-200 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">Keranjang Pesanan</h2>
        <button id="cart-close" class="text-gray-400 hover:text-gray-800 text-2xl">&times;</button>
      </div>

      <!-- Cart Items -->
      <div class="flex-1 overflow-y-auto">
        <div id="cart-items" class="p-4">
          <!-- Cart items will be rendered here -->
        </div>
      </div>

      <!-- Checkout Form -->
      <div class="p-6 border-t border-gray-200 space-y-4">
        <div>
          <label for="checkout-nama" class="font-semibold text-gray-600 text-sm">Nama Pemesan</label>
          <input type="text" id="checkout-nama" placeholder="Masukkan nama Anda"
            class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" />
        </div>
        <div>
          <label for="checkout-tamu" class="font-semibold text-gray-600 text-sm">Jumlah Tamu di Meja</label>
          <input type="number" id="checkout-tamu" min="1" value="1"
            class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" />
        </div>
        <button id="checkout-btn"
          class="w-full bg-red-600 text-white py-3 rounded-lg font-bold hover:bg-red-700 transition-colors shadow-lg hover:shadow-xl">
          <i class="fas fa-check mr-2"></i>Checkout
        </button>
      </div>
    </div>
  </div>

  <!-- Modal Tampilan Menu Detail -->
  <div id="menu-modal"
    class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 hidden transition-opacity duration-300 px-4">
    <div
      class="relative bg-white rounded-xl overflow-hidden max-w-md w-full max-h-[90vh] scale-95 opacity-0 transform transition-all duration-300 flex flex-col"
      id="modal-content">

      <!-- Tombol Tutup -->
      <button id="modal-close"
        class="absolute top-3 right-3 text-white text-3xl z-20 bg-black/40 w-10 h-10 rounded-full hover:bg-black/60 transition-colors">&times;</button>

      <!-- Navigasi Kiri -->
      <button id="modal-prev"
        class="absolute left-3 top-1/2 transform -translate-y-1/2 text-white text-4xl z-20 bg-black/40 p-2 rounded-full hover:bg-black/60 transition-colors w-12 h-12 flex items-center justify-center">&lt;</button>

      <!-- Navigasi Kanan -->
      <button id="modal-next"
        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-white text-4xl z-20 bg-black/40 p-2 rounded-full hover:bg-black/60 transition-colors w-12 h-12 flex items-center justify-center">&gt;</button>

      <!-- Gambar Menu -->
      <div class="relative w-full h-80">
        <img id="modal-image" src="" alt="Menu Detail" class="w-full h-full object-cover" />
      </div>

      <!-- Nama Menu -->
      <div class="bg-white text-gray-800 text-center text-xl font-bold py-4 border-t border-gray-200" id="modal-name">
      </div>
    </div>
  </div>

  <!-- Modal Status Pesanan (Disederhanakan, digabung ke Detail) -->
  <div id="orderModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg shadow-lg text-center w-80">
      <h2 class="text-xl font-bold mb-4">Status Pesanan</h2>
      <p id="orderStatusText" class="text-gray-700 text-lg mb-6">
        <span id="orderStatusSpan">...</span>
      </p>
      <button id="closeOrderModal" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">Tutup</button>
    </div>
  </div>

  <!-- Modal Detail Pesanan dengan Tab -->
  <div id="orderDetailsModal" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50 px-4">
    <div id="orderDetailsModal-content"
      class="bg-white rounded-xl shadow-2xl w-full max-w-lg max-h-[90vh] flex flex-col transform transition-all opacity-0 scale-95">
      <div class="p-6 border-b border-gray-200 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">Your Orders</h2>
        <button id="closeOrderDetails" class="text-gray-400 hover:text-gray-800 text-2xl">&times;</button>
      </div>

      <!-- Tab Navigation -->
      <div class="flex flex-col sm:flex-row border-b border-gray-200 px-6 gap-2 sm:gap-0">
        <button id="tabAktif"
          class="flex-1 py-3 text-center font-semibold border-b-2 border-red-600 text-red-600 transition-colors">Active</button>
        <button id="tabSelesai"
          class="flex-1 py-3 text-center font-semibold text-gray-500 hover:text-red-600 border-b-2 border-transparent transition-colors">History</button>
      </div>

      <!-- Tab Content -->
      <div class="p-6 space-y-4 overflow-y-auto" style="max-height:calc(90vh - 160px);">
        <ul id="orderListAktif" class="space-y-4 text-gray-700">
          <!-- Pesanan Aktif akan diisi via JS -->
        </ul>
        <ul id="orderListSelesai" class="space-y-4 text-gray-700 hidden">
          <!-- Pesanan Selesai akan diisi via JS -->
        </ul>
      </div>
    </div>
  </div>

  <!-- Modal Status Meja -->
  <div id="tableStatusModal" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50 px-4">
    <div id="tableStatusModal-content"
      class="bg-white rounded-xl shadow-2xl w-full max-w-2xl p-8 relative transform transition-all opacity-0 scale-95">
      <button id="closeTableStatusModal"
        class="absolute top-3 right-4 text-gray-400 hover:text-gray-800 text-2xl font-bold">&times;</button>
      <h2 class="text-2xl font-bold text-center mb-6 text-gray-800">Status Meja Real-time</h2>
      <div id="tableStatusContainer" class="grid grid-cols-4 md:grid-cols-6 gap-4 justify-items-center">
        <!-- Status meja akan di-generate oleh JS -->
      </div>
      <div class="mt-6 flex justify-center space-x-6 text-sm">
        <div class="flex items-center"><span class="h-4 w-4 rounded-full bg-green-500 mr-2"></span>Tersedia</div>
        <div class="flex items-center"><span class="h-4 w-4 rounded-full bg-red-500 mr-2"></span>Tidak Tersedia</div>
      </div>
    </div>
  </div>

  <!-- WhatsApp Button -->
  <a href="https://api.whatsapp.com/send" target="_blank"
    class="fixed bottom-6 right-6 z-50 bg-green-500 text-white w-14 h-14 rounded-full flex items-center justify-center shadow-lg hover:bg-green-600 transition-all transform hover:scale-110">
    <i class="fab fa-whatsapp text-3xl"></i>
  </a>



 <!-- About Section -->
<section id="about" class="py-24 bg-gradient-to-b from-red-100 via-white to-gray-100">
  <div class="container mx-auto px-4 max-w-5xl">
    <div class="text-center mb-14">
      <h2 class="text-5xl font-extrabold text-red-700 mb-4" style="font-family: 'Montserrat', sans-serif;">About Red Bear</h2>
      <p class="text-gray-600 text-lg max-w-2xl mx-auto">
        Red Bear is your destination for authentic Korean Barbeque, offering premium meats, fresh ingredients, and a cozy atmosphere. Our mission is to bring the best of Korean cuisine to your table, ensuring every visit is a memorable dining experience.
      </p>
    </div>
    <div class="flex flex-col md:flex-row items-center gap-12 md:gap-16">
      <div class="w-full md:w-1/2 flex justify-center">
        <div class="relative group">
          <img src="img/about-red-bear.png" alt="About Red Bear" class="rounded-2xl shadow-2xl object-cover w-full max-w-md border-8 border-white group-hover:scale-105 transition-transform duration-300">
          <span class="absolute -top-4 -left-4 bg-red-600 text-white px-4 py-1 rounded-full text-xs font-bold shadow-lg">Since 2022</span>
        </div>
      </div>
      <div class="flex-1">
        <h3 class="text-3xl font-bold text-gray-800 mb-5">Why Choose Us?</h3>
        <ul class="space-y-4">
          <li class="flex items-start gap-3 group"></li>
            <span class="text-red-600 text-xl icon-effect"><i class="fas fa-drumstick-bite"></i></span>
            <span class="text-gray-700 text-lg">Premium quality meats and ingredients</span>
          </li>
          <li class="flex items-start gap-3 group">
            <span class="text-red-600 text-xl icon-effect"><i class="fas fa-fire"></i></span>
            <span class="text-gray-700 text-lg">Traditional Korean recipes with a modern twist</span>
          </li>
          <li class="flex items-start gap-3 group">
            <span class="text-red-600 text-xl icon-effect"><i class="fas fa-couch"></i></span>
            <span class="text-gray-700 text-lg">Comfortable and stylish dining environment</span>
          </li>
          <li class="flex items-start gap-3 group">
            <span class="text-red-600 text-xl icon-effect"><i class="fas fa-user-friends"></i></span>
            <span class="text-gray-700 text-lg">Friendly and attentive service</span>
          </li>
          <li class="flex items-start gap-3 group">
            <span class="text-red-600 text-xl icon-effect"><i class="fas fa-gift"></i></span>
            <span class="text-gray-700 text-lg">Perfect for family gatherings, friends, and celebrations</span>
          </li>
        </ul>
        <div class="mt-8">
          <a href="#menu" class="inline-block bg-red-600 hover:bg-red-700 text-white font-bold px-8 py-3 rounded-full shadow-lg transition-all text-lg">See Our Menu</a>
        </div>
      </div>
    </div>
  </div>
  <style>
    /* About Section Background Fix */
    #about {
      background: linear-gradient(135deg, #ffe5e5 0%, #fff 60%, #f3f4f6 100%);
      /* #ffe5e5 = soft red, #fff = white, #f3f4f6 = gray-100 */
      position: relative;
      z-index: 1;
    }
    #about::before {
      content: "";
      position: absolute;
      inset: 0;
      background: radial-gradient(ellipse at 60% 10%, rgba(255, 0, 0, 0.07) 0%, transparent 70%);
      z-index: 0;
      pointer-events: none;
    }
    #about > .container {
      position: relative;
      z-index: 2;
    }
    /* Icon hover effect */
    .icon-effect {
      transition: transform 0.25s cubic-bezier(.4,0,.2,1), color 0.25s;
      will-change: transform;
      cursor: pointer;
    }
    .group:hover .icon-effect,
    .group:focus .icon-effect {
      transform: scale(1.25) rotate(-8deg);
      color: #b91c1c; /* darker red */
      filter: drop-shadow(0 2px 8px rgba(239,68,68,0.15));
    }
  </style>
</section>

  <!-- Location Section -->
  <section id="location" class="py-24 bg-gradient-to-b from-gray-100 via-white to-red-50">
    <div class="container mx-auto px-4 max-w-5xl">
      <div class="text-center mb-14">
        <h2 class="text-5xl font-extrabold text-red-700 mb-4 location-title" style="font-family: 'Montserrat', sans-serif;">Our Location</h2>
        <p class="text-gray-600 text-lg max-w-2xl mx-auto location-desc">
          Find us in the heart of Jakarta and experience the best Korean BBQ in town. We can't wait to welcome you!
        </p>
      </div>
      <div class="flex flex-col md:flex-row items-center gap-12 md:gap-16">
        <div class="w-full md:w-1/2 flex justify-center">
          <div class="relative group w-full max-w-md rounded-2xl overflow-hidden shadow-2xl border-8 border-white location-map-container">
            <iframe 
              src="https://www.google.com/maps?q=Jakarta+Indonesia&output=embed" 
              width="100%" height="340"
              class="rounded-2xl w-full h-80 border-none location-map"
              style="min-height:320px;"
              allowfullscreen="" loading="lazy"></iframe>
            <span class="absolute top-4 left-4 bg-red-600 text-white px-4 py-1 rounded-full text-xs font-bold shadow-lg location-map-badge">See on Map</span>
          </div>
        </div>
        <div class="flex-1">
          <div class="bg-white rounded-2xl shadow-lg p-8 border border-gray-200 location-card">
            <h3 class="text-3xl font-bold text-gray-800 mb-4 location-card-title">Red Bear Restaurant</h3>
            <ul class="text-gray-700 space-y-4 text-lg">
              <li class="flex items-center">
                <span class="text-red-600 text-xl mr-3"><i class="fas fa-map-marker-alt"></i></span>
                Jl. Contoh Alamat No. 123, Jakarta, Indonesia
              </li>
              <li class="flex items-center">
                <span class="text-red-600 text-xl mr-3"><i class="fas fa-phone-alt"></i></span>
                <a href="tel:02112345678" class="hover:underline">(021) 1234-5678</a>
              </li>
              <li class="flex items-center">
                <span class="text-red-600 text-xl mr-3"><i class="fas fa-clock"></i></span>
                Open: <span class="font-semibold">10:00 - 22:00</span> (Everyday)
              </li>
              <li class="flex items-center">
                <span class="text-red-600 text-xl mr-3"><i class="fas fa-envelope"></i></span>
                <a href="mailto:info@redbear.com" class="hover:underline">info@redbear.com</a>
              </li>
            </ul>
            <div class="mt-8 flex gap-4">
              <a href="https://www.instagram.com/redbear.indonesia?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw==" target="_blank"
                class="inline-flex items-center bg-gradient-to-r from-red-600 to-yellow-500 text-white px-6 py-3 rounded-full font-bold shadow-lg hover:scale-105 transition-all text-lg location-btn-instagram">
                <i class="fab fa-instagram mr-2"></i> Instagram
              </a>
              <a href="https://wa.me/6281234567890" target="_blank"
                class="inline-flex items-center bg-green-500 text-white px-6 py-3 rounded-full font-bold shadow-lg hover:bg-green-600 hover:scale-105 transition-all text-lg location-btn-wa">
                <i class="fab fa-whatsapp mr-2"></i> WhatsApp
              </a>
            </div>
          </div>
        </div>
      </div>
      
    </div>
    <style>
      /* Fade-in animation for location section */
      #location {
        opacity: 0;
        transform: translateY(40px);
        transition: opacity 0.8s cubic-bezier(.4,0,.2,1), transform 0.8s cubic-bezier(.4,0,.2,1);
      }
      #location.visible {
        opacity: 1;
        transform: translateY(0);
      }
      /* Map zoom effect on hover */
      .location-map-container:hover .location-map {
        transform: scale(1.04) rotate(-1deg);
        box-shadow: 0 8px 32px rgba(255,0,0,0.15);
      }
      .location-map {
        transition: transform 0.4s cubic-bezier(.4,0,.2,1), box-shadow 0.4s;
      }
      /* Badge bounce */
      .location-map-badge {
        animation: badgeBounce 2s infinite;
      }
      @keyframes badgeBounce {
        0%, 100% { transform: translateY(0);}
        50% { transform: translateY(-8px);}
      }
      /* Card hover effect */
      .location-card {
        transition: box-shadow 0.3s, transform 0.3s;
      }
      .location-card:hover {
        box-shadow: 0 12px 40px rgba(255,0,0,0.13);
        transform: translateY(-4px) scale(1.02);
      }
      /* Button pulse */
      .location-btn-instagram, .location-btn-wa {
        position: relative;
        overflow: hidden;
      }
      .location-btn-instagram::after, .location-btn-wa::after {
        content: '';
        position: absolute;
        left: 50%; top: 50%;
        width: 0; height: 0;
        background: rgba(255,255,255,0.25);
        border-radius: 100%;
        transform: translate(-50%, -50%);
        opacity: 0;
        transition: width 0.4s, height 0.4s, opacity 0.4s;
        z-index: 0;
      }
      .location-btn-instagram:active::after, .location-btn-wa:active::after {
        width: 200%;
        height: 200%;
        opacity: 1;
        transition: 0s;
      }
      /* Title fade-in */
      .location-title, .location-desc, .location-card-title, .location-parking-title, .location-parking-desc {
        opacity: 0;
        transform: translateY(30px);
        transition: opacity 0.7s, transform 0.7s;
      }
      #location.visible .location-title { opacity: 1; transform: none; transition-delay: 0.1s;}
      #location.visible .location-desc { opacity: 1; transform: none; transition-delay: 0.2s;}
      #location.visible .location-card-title { opacity: 1; transform: none; transition-delay: 0.3s;}
      #location.visible .location-parking-title { opacity: 1; transform: none; transition-delay: 0.4s;}
      #location.visible .location-parking-desc { opacity: 1; transform: none; transition-delay: 0.5s;}
    </style>
    <script>
      // Intersection Observer for fade-in effect
      document.addEventListener('DOMContentLoaded', function () {
        var locationSection = document.getElementById('location');
        if ('IntersectionObserver' in window) {
          var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
              if (entry.isIntersecting) {
                locationSection.classList.add('visible');
                observer.disconnect();
              }
            });
          }, { threshold: 0.2 });
          observer.observe(locationSection);
        } else {
          // Fallback
          locationSection.classList.add('visible');
        }
      });
    </script>
  </section>



  <!-- Blog Section -->
  <section id="blog" class="py-24 bg-gradient-to-b from-white via-red-50 to-gray-100">
   <div class="container mx-auto px-4 max-w-6xl">
    <div class="text-center mb-14">
      <h2 class="text-5xl font-extrabold text-red-700 mb-4" style="font-family: 'Montserrat', sans-serif;">Our Latest Blog</h2>
      <p class="text-gray-600 text-lg max-w-2xl mx-auto">
       Stay updated with our news, recipes, and special events.
      </p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
      <?php if (empty($blog_posts)): ?>
       <p class="col-span-full text-center text-gray-500">Belum ada postingan blog yang dipublikasikan.</p>
      <?php else: ?>
       <?php foreach ($blog_posts as $post): ?>
        <div class="group bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 hover:shadow-2xl transition-all duration-300 flex flex-col">
          <?php if ($post['image_path']): ?>
           <div class="relative h-56 overflow-hidden">
            <img src="<?php echo htmlspecialchars($post['image_path']); ?>"
              alt="<?php echo htmlspecialchars($post['title']); ?>"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
            <span class="absolute top-4 left-4 bg-red-600 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg">Blog</span>
           </div>
          <?php endif; ?>
          <div class="flex-1 flex flex-col p-6">
           <h3 class="text-2xl font-bold mb-2 text-gray-800 group-hover:text-red-700 transition-colors"><?php echo htmlspecialchars($post['title']); ?></h3>
           <p class="text-gray-500 text-xs mb-3">Oleh: <span class="font-semibold"><?php echo htmlspecialchars($post['author_name']); ?></span> &bull; <?php echo date('d M Y', strtotime($post['created_at'])); ?></p>
           <p class="text-gray-700 mb-4 flex-1"><?php echo nl2br(substr(strip_tags($post['content']), 0, 120)); ?>...</p>
           <div class="mt-auto">
            <a href="blog_detail.php?id=<?php echo $post['id']; ?>"
              class="inline-flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 focus:bg-red-800 text-white px-5 py-2 rounded-full font-bold text-sm shadow-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-400 group-hover:scale-105 group-hover:shadow-lg">
              <i class="fas fa-book-open"></i> Baca Selengkapnya
            </a>
           </div>
          </div>
        </div>
       <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <div class="text-center mt-12">
      <a href="blog.php"
       class="inline-flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 focus:bg-red-800 text-white px-8 py-3 rounded-full font-bold text-lg shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-400 hover:scale-105">
        <i class="fas fa-list"></i> Lihat Semua Blog
      </a>
    </div>
    <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'user'): ?>
      <div class="text-center mt-8">
       <a href="blog_form.php"
        class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 focus:bg-blue-800 text-white px-6 py-3 rounded-full font-bold text-lg shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-400 hover:scale-105">
        <i class="fas fa-pen"></i> Tulis Blog
       </a>
      </div>
    <?php endif; ?>
    </div>
   <style>
    #blog {
      background: linear-gradient(135deg, #fff 0%, #fff5f5 60%, #f3f4f6 100%);
      position: relative;
      z-index: 1;
    }
    #blog .group:hover img {
      filter: brightness(0.96) saturate(1.1);
    }
    #blog .group:hover h3 {
      color: #b91c1c;
    }
    #blog .group .bg-red-600 {
      box-shadow: 0 2px 8px rgba(239,68,68,0.13);
    }
    #blog .group:hover .bg-red-600 {
      background: #b91c1c;
    }
    #blog .group .rounded-2xl {
      transition: box-shadow 0.3s, transform 0.3s;
    }
    #blog .group:hover .rounded-2xl {
      box-shadow: 0 12px 40px rgba(255,0,0,0.13);
      transform: translateY(-4px) scale(1.02);
    }
    /* Button improvement */
    #blog a, #blog button {
      transition: background 0.2s, color 0.2s, box-shadow 0.2s, transform 0.2s;
      outline: none;
    }
    #blog a:focus, #blog button:focus {
      box-shadow: 0 0 0 3px rgba(239,68,68,0.25);
    }
    #blog a:active, #blog button:active {
      transform: scale(0.97);
    }
   </style>
  </section>


  <!-- Footer -->
  <footer class="bg-gray-800 text-white pt-12 pb-8">
    <div class="container mx-auto px-6 md:px-12">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- About -->
        <div>
          <h4 class="text-lg font-bold mb-3">RED BEAR</h4>
          <p class="text-gray-400 text-sm">
            Experience the finest Korean Barbeque with premium ingredients and an unforgettable ambiance. Join us for a
            sizzling good time.
          </p>
        </div>
        <!-- Links -->
        <div>
          <h4 class="text-lg font-bold mb-3">Quick Links</h4>
          <ul class="text-sm space-y-2">
            <li><a href="#" class="text-gray-400 hover:text-white">Home</a></li>
            <li><a href="#menu" class="text-gray-400 hover:text-white">Menu</a></li>
          </ul>
        </div>
        <!-- Social -->
        <div>
          <h4 class="text-lg font-bold mb-3">Follow Us</h4>
          <div class="flex gap-4">
            <a href="https://www.instagram.com/redbear.indonesia?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw=="
              class="text-gray-400 hover:text-white text-2xl"><i class="fab fa-instagram"></i></a>
          </div>
        </div>
      </div>
      <div class="border-t border-gray-700 mt-8 pt-6 text-center text-gray-500 text-sm">
        <p>&copy; 2024 Red Bear. All Rights Reserved.</p>
      </div>
    </div>
  </footer>

  <script>
    // Melewatkan status scan ke Javascript
    window.hasScannedTable = <?php echo isset($_SESSION['scanned_table_id']) ? 'true' : 'false'; ?>;
    // Melewatkan status booking ke Javascript
    window.hasBookedTable = false; // Akan diupdate via loadUserTableCode()

    // Cek ketersediaan meja saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function () {
      <?php if (isset($_SESSION['table_available']) && !$_SESSION['table_available']): ?>
        // Jika meja tidak tersedia, tampilkan modal
        showTableUnavailableModal();
      <?php endif; ?>

      // Mulai pengecekan berkala jika user sudah scan meja
      if (window.hasScannedTable) {
        startTableStatusCheck();
      }
    });

    // Fungsi untuk menampilkan modal meja tidak tersedia
    function showTableUnavailableModal() {
      const modal = document.getElementById('tableUnavailableModal');
      const reason = document.getElementById('unavailableReason');
      const tableInfo = document.getElementById('tableInfo');

      // Set pesan berdasarkan alasan
      const unavailableReason = '<?php echo isset($_SESSION['table_unavailable_reason']) ? $_SESSION['table_unavailable_reason'] : ''; ?>';

      if (unavailableReason === 'booking') {
        reason.textContent = 'Meja ini sudah di-booking online oleh pelanggan lain.';
        tableInfo.innerHTML = `
          <p><strong>Status:</strong> <span class="text-blue-600">Di-booking Online</span></p>
          <p><strong>Tanggal:</strong> <?php echo date('d/m/Y'); ?></p>
          <p class="text-sm text-gray-500 mt-2">Silakan pilih meja lain atau booking meja untuk waktu yang berbeda.</p>
        `;
      } else if (unavailableReason === 'different_table') {
        const existingTableId = <?php echo isset($_SESSION['existing_table_id']) ? $_SESSION['existing_table_id'] : 'null'; ?>;
        reason.textContent = 'Anda sudah memiliki sesi aktif di meja lain.';
        tableInfo.innerHTML = `
          <p><strong>Status:</strong> <span class="text-purple-600">Sesi Aktif di Meja Lain</span></p>
          <p><strong>Meja Aktif:</strong> <span class="font-bold">Meja ${existingTableId}</span></p>
          <p><strong>Tanggal:</strong> <?php echo date('d/m/Y'); ?></p>
          <p class="text-sm text-gray-500 mt-2">Anda tidak dapat mengakses meja lain selama masih memiliki sesi aktif. Silakan kembali ke meja Anda atau selesaikan pesanan terlebih dahulu.</p>
        `;
      } else {
        reason.textContent = 'Meja ini sudah ditempati oleh pelanggan lain.';
        tableInfo.innerHTML = `
          <p><strong>Status:</strong> <span class="text-orange-600">Sedang Digunakan</span></p>
          <p><strong>Tanggal:</strong> <?php echo date('d/m/Y'); ?></p>
          <p class="text-sm text-gray-500 mt-2">Silakan pilih meja lain atau tunggu hingga meja ini kosong.</p>
        `;
      }

      modal.classList.remove('hidden');
      modal.classList.add('flex');

      // Hapus session data setelah ditampilkan
      <?php
      if (isset($_SESSION['table_available'])) {
        unset($_SESSION['table_available']);
        unset($_SESSION['table_unavailable_reason']);
        unset($_SESSION['existing_table_id']);
      }
      ?>
    }

    // Event listener untuk menutup modal
    document.getElementById('closeUnavailableModal').addEventListener('click', function () {
      document.getElementById('tableUnavailableModal').classList.add('hidden');
      document.getElementById('tableUnavailableModal').classList.remove('flex');
    });

    // Tutup modal jika klik di luar konten
    document.getElementById('tableUnavailableModal').addEventListener('click', function (e) {
      if (e.target === this) {
        this.classList.add('hidden');
        this.classList.remove('flex');
      }
    });

    // Fungsi untuk mengecek status meja secara berkala
    let tableStatusCheckInterval;

    function startTableStatusCheck() {
      const tableId = <?php echo isset($_SESSION['scanned_table_id']) ? $_SESSION['scanned_table_id'] : 'null'; ?>;
      if (!tableId) return;

      // Cek setiap 30 detik
      tableStatusCheckInterval = setInterval(function () {
        checkTableStatus(tableId);
      }, 30000);

      // Cek pertama kali
      checkTableStatus(tableId);
    }

    function checkTableStatus(tableId) {
      fetch(`admin/tables/api/check_table_status.php?table_id=${tableId}`)
        .then(res => res.json())
        .catch(error => {
          console.error('Gagal mengecek status meja:', error);
        });
    }

    // Hentikan pengecekan saat halaman ditutup
    window.addEventListener('beforeunload', function () {
      if (tableStatusCheckInterval) {
        clearInterval(tableStatusCheckInterval);
      }
    });
  </script>
  <script>
    
    // Modal Book A Table
    const bookTableBtn = document.getElementById('bookTable');
    const bookTableModal = document.getElementById('bookTableModal');
    const closeBookTableModal = document.getElementById('closeBookTableModal');
    bookTableBtn.addEventListener('click', function (e) {
      e.preventDefault();
      bookTableModal.classList.remove('hidden');
      bookTableModal.classList.add('flex');

      // Reset scroll position dan setup scrolling
      const contentArea = bookTableModal.querySelector('.overflow-y-auto');
      if (contentArea) {
        contentArea.scrollTop = 0;

        // Tambahkan smooth scrolling untuk PC
        if (window.innerWidth > 640) {
          contentArea.style.scrollBehavior = 'smooth';
          contentArea.style.overflowY = 'auto';
          contentArea.style.maxHeight = '50vh';

          // Tambahkan custom scrollbar untuk PC
          contentArea.style.setProperty('--scrollbar-width', '12px');
          contentArea.style.setProperty('--scrollbar-track-color', '#f1f1f1');
          contentArea.style.setProperty('--scrollbar-thumb-color', '#c1c1c1');

          // Tambahkan CSS untuk custom scrollbar
          if (!document.getElementById('customScrollbarStyle')) {
            const scrollbarStyle = document.createElement('style');
            scrollbarStyle.id = 'customScrollbarStyle';
            scrollbarStyle.textContent = `
          @media (min-width: 641px) {
            #bookTableModal .overflow-y-auto::-webkit-scrollbar {
              width: 12px;
            }
            #bookTableModal .overflow-y-auto::-webkit-scrollbar-track {
              background: #f1f1f1;
              border-radius: 6px;
              margin: 4px 0;
            }
            #bookTableModal .overflow-y-auto::-webkit-scrollbar-thumb {
              background: linear-gradient(180deg, #c1c1c1 0%, #a8a8a8 100%);
              border-radius: 6px;
              border: 2px solid #f1f1f1;
            }
            #bookTableModal .overflow-y-auto::-webkit-scrollbar-thumb:hover {
              background: linear-gradient(180deg, #a8a8a8 0%, #909090 100%);
            }
            #bookTableModal .overflow-y-auto::-webkit-scrollbar-corner {
              background: #f1f1f1;
            }
          }
        `;
            document.head.appendChild(scrollbarStyle);
          }

          // Tambahkan custom scrollbar styles
          contentArea.style.setProperty('--scrollbar-width', '12px');
          contentArea.style.setProperty('--scrollbar-track-color', '#f1f1f1');
          contentArea.style.setProperty('--scrollbar-thumb-color', '#c1c1c1');

          // Tambahkan CSS untuk custom scrollbar
          const scrollbarStyle = document.createElement('style');
          scrollbarStyle.textContent = `
        #bookTableModal .overflow-y-auto::-webkit-scrollbar {
          width: 12px;
        }
        #bookTableModal .overflow-y-auto::-webkit-scrollbar-track {
          background: #f1f1f1;
          border-radius: 6px;
          margin: 4px 0;
        }
        #bookTableModal .overflow-y-auto::-webkit-scrollbar-thumb {
          background: linear-gradient(180deg, #c1c1c1 0%, #a8a8a8 100%);
          border-radius: 6px;
          border: 2px solid #f1f1f1;
        }
        #bookTableModal .overflow-y-auto::-webkit-scrollbar-thumb:hover {
          background: linear-gradient(180deg, #a8a8a8 0%, #909090 100%);
        }
      `;
          document.head.appendChild(scrollbarStyle);
        }
      }

      updateTableStatus(); // Update status meja saat modal dibuka
      selectedTable = null;
    });
    closeBookTableModal.addEventListener('click', function () {
      bookTableModal.classList.add('hidden');
      bookTableModal.classList.remove('flex');
    });
    // Tutup modal jika klik di luar konten
    bookTableModal.addEventListener('click', function (e) {
      if (e.target === bookTableModal) {
        bookTableModal.classList.add('hidden');
        bookTableModal.classList.remove('flex');
      }
    });
    // Set min date hari ini untuk input tanggal (akan diupdate oleh date picker)
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    const todayString = `${yyyy}-${mm}-${dd}`;
    // Fungsi untuk mengisi opsi waktu (akan digunakan oleh time picker)
    function fillTimeOptions() {
      // Fungsi ini sekarang dihandle oleh modal time picker
      if (typeof generateTimeOptions === 'function') {
        generateTimeOptions();
      }
    }

    // Update opsi waktu saat tanggal berubah
    bookingDate.addEventListener('change', function () {
      fillTimeOptions();
      updateTableStatus();
    });

    // Update status meja saat waktu berubah
    bookingTime.addEventListener('change', updateTableStatus);

    // --- Integrasi Booking Table ---
    const tableIcons = document.querySelectorAll('#tableIcons .table-icon');
    let selectedTable = null;

    // Fungsi update status meja dari API real-time
    function updateTableStatus() {
      const date = bookingDate.value;
      const time = bookingTime.value;
      const url = time ? `./admin/tables/api/get_all_table_statuses.php?date=${date}&time=${time}` : `./admin/tables/api/get_all_table_statuses.php?date=${date}`;

      fetch(url)
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            const tableIconsContainer = document.getElementById('tableIcons');
            tableIconsContainer.innerHTML = '';

            data.tables.forEach((table) => {
              const isAvailable = table.status === 'available';
              const tableBtn = document.createElement('button');
              tableBtn.className = `table-icon rounded-full w-14 h-14 flex flex-col items-center justify-center transition-all duration-200 ${isAvailable
                  ? 'bg-green-100 border-2 border-green-500 hover:bg-green-200 focus:ring-2 focus:ring-green-500 cursor-pointer'
                  : 'bg-gray-300 border-2 border-gray-400 cursor-not-allowed opacity-60'
                }`;
              tableBtn.setAttribute('data-table', table.id);
              tableBtn.setAttribute('data-next-available', table.next_available_time || '');
              tableBtn.disabled = !isAvailable;

              tableBtn.innerHTML = `
                <span class="text-2xl">${isAvailable ? '🍽️' : '🔒'}</span>
                <span class="text-xs font-bold mt-1">Meja ${table.table_number}</span>
              `;

              tableBtn.addEventListener('click', function () {
                if (this.disabled) {
                  return;
                }
                document.querySelectorAll('.table-icon').forEach(btn => btn.classList.remove('ring-4', 'ring-red-500'));
                this.classList.add('ring-4', 'ring-red-500');
                selectedTable = this.getAttribute('data-table');
              });

              tableIconsContainer.appendChild(tableBtn);
            });
          }
        })
        .catch(error => {
          console.error('Gagal memuat status meja:', error);
        });
    }

    // Submit booking
    document.getElementById('submitBookTable').addEventListener('click', function () {
      const guestCount = document.getElementById('guestCount').value;
      const date = bookingDate.value;
      const time = bookingTime.value;
      if (!selectedTable) {
        alert('Silakan pilih meja yang tersedia.');
        return;
      }
      if (!date || !time) {
        alert('Tanggal dan waktu harus diisi.');
        return;
      }
      // Kirim booking
      const formData = new FormData();
      formData.append('table_id', selectedTable);
      formData.append('guest_count', guestCount);
      formData.append('date', date);
      formData.append('time', time);
      // Jika ada user_id, bisa ditambahkan di sini
      fetch('admin/book_table/api/book_table.php', {
        method: 'POST',
        body: formData
      })
        .then(res => res.json())
        .then(data => {
          alert(data.message);
          if (data.success) {
            bookTableModal.classList.add('hidden');
            bookTableModal.classList.remove('flex');
            // Update saldo user jika ada
            if (data.saldo_baru !== undefined) {
              document.getElementById('userSaldo').textContent = 'Rp' + data.saldo_baru.toLocaleString('id-ID');
              // Update saldo di navbar juga jika ada
              const navbarSaldo = document.querySelector('.text-green-600');
              if (navbarSaldo) {
                navbarSaldo.textContent = 'Rp' + data.saldo_baru.toLocaleString('id-ID');
              }
            }
            // Tampilkan kode meja
            if (data.table_code) {
              showTableCode(data.table_code, date, time);
            }
            // Aktifkan pemesanan makanan
            window.hasBookedTable = true;
            // Refresh menu untuk menampilkan tombol plus
            if (typeof renderMenu === 'function') {
              renderMenu();
            }
          } else {
            if (data.message && data.message.toLowerCase().includes('login')) {
              window.location.href = 'login_register/login.php';
            } else {
              updateTableStatus();
            }
          }
        });
    });

    // Fungsi untuk menampilkan kode meja
    function showTableCode(tableCode, date, time) {
      const tableCodeInfo = document.getElementById('tableCodeInfo');
      const userTableCode = document.getElementById('userTableCode');
      const tableCodeDetails = document.getElementById('tableCodeDetails');

      if (tableCodeInfo && userTableCode && tableCodeDetails) {
        userTableCode.textContent = tableCode;
        tableCodeDetails.textContent = `Tanggal: ${date} | Waktu: ${time}`;
        tableCodeInfo.classList.remove('hidden');
      }
    }

    // Fungsi untuk memuat kode meja user yang sudah ada
    function loadUserTableCode() {
      fetch('admin/book_table/api/get_user_table_code.php')
        .then(res => res.json())
        .then(data => {
          if (data.success && data.bookings.length > 0) {
            const booking = data.bookings[0]; // Ambil booking pertama
            showTableCode(booking.table_code, booking.booking_date, booking.booking_time);
            // Aktifkan pemesanan makanan
            window.hasBookedTable = true;
            // Refresh menu untuk menampilkan tombol plus
            if (typeof renderMenu === 'function') {
              renderMenu();
            }
          }
        })
        .catch(error => console.error('Gagal memuat kode meja:', error));
    }

    // Load kode meja saat halaman dimuat
    loadUserTableCode();

    // Update status meja saat modal dibuka
    bookTableBtn.addEventListener('click', function () {
      updateTableStatus();
      tableIcons.forEach(b => b.classList.remove('ring-4', 'ring-red-500'));
      selectedTable = null;
    });

    // Tambahkan CSS untuk memastikan modal responsive
    const bookTableModalStyle = document.createElement('style');
    bookTableModalStyle.textContent = `
      #bookTableModal {
        backdrop-filter: blur(4px);
      }
      
      #bookTableModal > div {
        animation: modalSlideIn 0.3s ease-out forwards;
      }
      
      @keyframes modalSlideIn {
        from {
          opacity: 0;
          transform: scale(0.95) translateY(-10px);
        }
        to {
          opacity: 1;
          transform: scale(1) translateY(0);
        }
      }
      
      /* Memastikan footer selalu terlihat */
      #bookTableModal .flex-shrink-0 {
        position: sticky;
        bottom: 0;
        background: white;
        z-index: 10;
      }
      
      /* Smooth scrolling untuk content area */
      #bookTableModal .overflow-y-auto {
        scroll-behavior: smooth;
      }
      
      /* Desktop scroll improvements */
      @media (min-width: 641px) {
        #bookTableModal .overflow-y-auto {
          max-height: 50vh;
          overflow-y: auto;
          overflow-x: hidden;
          scroll-behavior: smooth;
          scrollbar-width: thin;
          scrollbar-color: #c1c1c1 #f1f1f1;
        }
        
        #bookTableModal .overflow-y-auto::-webkit-scrollbar {
          width: 12px;
        }
        
        #bookTableModal .overflow-y-auto::-webkit-scrollbar-track {
          background: #f1f1f1;
          border-radius: 6px;
          margin: 4px 0;
          box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.1);
        }
        
        #bookTableModal .overflow-y-auto::-webkit-scrollbar-thumb {
          background: linear-gradient(180deg, #c1c1c1 0%, #a8a8a8 100%);
          border-radius: 6px;
          border: 2px solid #f1f1f1;
          transition: background 0.2s ease;
        }
        
        #bookTableModal .overflow-y-auto::-webkit-scrollbar-thumb:hover {
          background: linear-gradient(180deg, #a8a8a8 0%, #909090 100%);
          box-shadow: 0 0 8px rgba(0, 0, 0, 0.2);
        }
        
        #bookTableModal .overflow-y-auto::-webkit-scrollbar-corner {
          background: #f1f1f1;
        }
        
        /* Enhanced scrollbar untuk Firefox */
        #bookTableModal .overflow-y-auto {
          scrollbar-width: thin;
          scrollbar-color: #c1c1c1 #f1f1f1;
        }
        
        /* Smooth scroll animation */
        #bookTableModal .overflow-y-auto {
          scroll-behavior: smooth;
        }
      }
      
      /* Mobile scroll improvements */
      @media (max-width: 640px) {
        #bookTableModal .overflow-y-auto {
          max-height: 40vh;
        }
        
        #bookTableModal .overflow-y-auto::-webkit-scrollbar {
          width: 6px;
        }
      }
    `;
    document.head.appendChild(bookTableModalStyle);

    // Event listener untuk window resize
    window.addEventListener('resize', function () {
      const contentArea = bookTableModal.querySelector('.overflow-y-auto');
      if (contentArea && !bookTableModal.classList.contains('hidden')) {
        if (window.innerWidth > 640) {
          contentArea.style.maxHeight = '50vh';
          contentArea.style.scrollBehavior = 'smooth';
        } else {
          contentArea.style.maxHeight = '40vh';
        }
      }
    });

    // --- Modal Pilih Jumlah Tamu ---
    const guestCountBtn = document.getElementById('guestCountBtn');
    const guestCountModal = document.getElementById('guestCountModal');
    const closeGuestCountModal = document.getElementById('closeGuestCountModal');
    const decreaseGuest = document.getElementById('decreaseGuest');
    const increaseGuest = document.getElementById('increaseGuest');
    const guestDisplay = document.getElementById('guestDisplay');
    const confirmGuestCount = document.getElementById('confirmGuestCount');
    const guestCountText = document.getElementById('guestCountText');
    const guestCountInput = document.getElementById('guestCount');

    let currentGuestCount = 2;
    const minGuests = 1;
    const maxGuests = 8;

    // Event listeners untuk modal jumlah tamu akan diupdate di bawah

    // Kurangi jumlah tamu
    decreaseGuest.addEventListener('click', function () {
      if (currentGuestCount > minGuests) {
        currentGuestCount--;
        guestDisplay.textContent = currentGuestCount;
        updateGuestButtons();
      }
    });

    // Tambah jumlah tamu
    increaseGuest.addEventListener('click', function () {
      if (currentGuestCount < maxGuests) {
        currentGuestCount++;
        guestDisplay.textContent = currentGuestCount;
        updateGuestButtons();
      }
    });

    // Update status tombol berdasarkan jumlah tamu
    function updateGuestButtons() {
      decreaseGuest.disabled = currentGuestCount <= minGuests;
      increaseGuest.disabled = currentGuestCount >= maxGuests;

      if (currentGuestCount <= minGuests) {
        decreaseGuest.classList.add('opacity-50', 'cursor-not-allowed');
        decreaseGuest.classList.remove('hover:bg-white', 'hover:text-red-800');
      } else {
        decreaseGuest.classList.remove('opacity-50', 'cursor-not-allowed');
        decreaseGuest.classList.add('hover:bg-white', 'hover:text-red-800');
      }

      if (currentGuestCount >= maxGuests) {
        increaseGuest.classList.add('opacity-50', 'cursor-not-allowed');
        increaseGuest.classList.remove('hover:bg-white', 'hover:text-red-800');
      } else {
        increaseGuest.classList.remove('opacity-50', 'cursor-not-allowed');
        increaseGuest.classList.add('hover:bg-white', 'hover:text-red-800');
      }
    }

    // Konfirmasi pilihan jumlah tamu akan diupdate di bawah dengan animasi

    // Inisialisasi status tombol
    updateGuestButtons();

    // Tambahkan animasi smooth untuk modal
    function showModalWithAnimation(modal) {
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      // Trigger reflow untuk memastikan animasi berjalan
      modal.offsetHeight;
      modal.querySelector('div').classList.add('animate-in');
    }

    function hideModalWithAnimation(modal) {
      modal.querySelector('div').classList.remove('animate-in');
      setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
      }, 200);
    }

    // Update event listeners untuk menggunakan animasi
    guestCountBtn.addEventListener('click', function () {
      showModalWithAnimation(guestCountModal);
      guestDisplay.textContent = currentGuestCount;
    });

    closeGuestCountModal.addEventListener('click', function () {
      hideModalWithAnimation(guestCountModal);
    });

    guestCountModal.addEventListener('click', function (e) {
      if (e.target === guestCountModal) {
        hideModalWithAnimation(guestCountModal);
      }
    });

    confirmGuestCount.addEventListener('click', function () {
      guestCountInput.value = currentGuestCount;
      guestCountText.textContent = currentGuestCount === 1 ? '1 Guest' : `${currentGuestCount} Guests`;
      hideModalWithAnimation(guestCountModal);
    });

    // Keyboard support untuk modal
    document.addEventListener('keydown', function (e) {
      if (guestCountModal.classList.contains('flex')) {
        if (e.key === 'Escape') {
          hideModalWithAnimation(guestCountModal);
        } else if (e.key === 'ArrowLeft' || e.key === 'ArrowDown') {
          e.preventDefault();
          if (currentGuestCount > minGuests) {
            currentGuestCount--;
            guestDisplay.textContent = currentGuestCount;
            updateGuestButtons();
          }
        } else if (e.key === 'ArrowRight' || e.key === 'ArrowUp') {
          e.preventDefault();
          if (currentGuestCount < maxGuests) {
            currentGuestCount++;
            guestDisplay.textContent = currentGuestCount;
            updateGuestButtons();
          }
        } else if (e.key === 'Enter') {
          e.preventDefault();
          confirmGuestCount.click();
        }
      }
    });

    // Focus management untuk accessibility
    guestCountBtn.addEventListener('click', function () {
      showModalWithAnimation(guestCountModal);
      guestDisplay.textContent = currentGuestCount;
      // Focus pada tombol decrease setelah modal terbuka
      setTimeout(() => {
        decreaseGuest.focus();
      }, 300);
    });

    // Tambahkan ripple effect untuk tombol
    function createRipple(event) {
      const button = event.currentTarget;
      const circle = document.createElement('span');
      const diameter = Math.max(button.clientWidth, button.clientHeight);
      const radius = diameter / 2;

      circle.style.width = circle.style.height = `${diameter}px`;
      circle.style.left = `${event.clientX - button.offsetLeft - radius}px`;
      circle.style.top = `${event.clientY - button.offsetTop - radius}px`;
      circle.classList.add('ripple');

      const ripple = button.getElementsByClassName('ripple')[0];
      if (ripple) {
        ripple.remove();
      }

      button.appendChild(circle);
    }

    // Tambahkan ripple effect ke tombol-tombol
    [decreaseGuest, increaseGuest, confirmGuestCount].forEach(button => {
      button.addEventListener('click', createRipple);
    });

    // Tambahkan CSS untuk ripple effect
    const style = document.createElement('style');
    style.textContent = `
      .ripple {
        position: absolute;
        border-radius: 50%;
        transform: scale(0);
        animation: ripple 0.6s linear;
        background-color: rgba(255, 255, 255, 0.3);
      }
      
      @keyframes ripple {
        to {
          transform: scale(4);
          opacity: 0;
        }
      }
      
      #decreaseGuest, #increaseGuest, #confirmGuestCount {
        position: relative;
        overflow: hidden;
      }
    `;
    document.head.appendChild(style);

    // --- Modal Kalender ---
    const datePickerBtn = document.getElementById('datePickerBtn');
    const datePickerModal = document.getElementById('datePickerModal');
    const closeDatePickerModal = document.getElementById('closeDatePickerModal');
    const prevMonth = document.getElementById('prevMonth');
    const nextMonth = document.getElementById('nextMonth');
    const currentMonthYear = document.getElementById('currentMonthYear');
    const calendarGrid = document.getElementById('calendarGrid');
    const confirmDate = document.getElementById('confirmDate');
    const selectedDateText = document.getElementById('selectedDateText');
    const bookingDateInput = document.getElementById('bookingDate');

    let currentDate = new Date();
    let selectedDate = null;
    let currentMonth = currentDate.getMonth();
    let currentYear = currentDate.getFullYear();

    // Format tanggal untuk display
    function formatDateForDisplay(date) {
      const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
      const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
      return `${days[date.getDay()]} ${months[date.getMonth()]} ${date.getDate()}`;
    }

    // Format tanggal untuk input
    function formatDateForInput(date) {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    }

    // Generate kalender
    function generateCalendar(month, year) {
      const firstDay = new Date(year, month, 1);
      const lastDay = new Date(year, month + 1, 0);
      const startDate = new Date(firstDay);
      startDate.setDate(startDate.getDate() - firstDay.getDay());

      const todayString = formatDateForInput(today);

      calendarGrid.innerHTML = '';

      // Update header bulan dan tahun
      const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
      currentMonthYear.textContent = `${months[month]} ${year}`;

      // Generate 42 cells (6 weeks x 7 days)
      for (let i = 0; i < 42; i++) {
        const date = new Date(startDate);
        date.setDate(startDate.getDate() + i);

        const dateString = formatDateForInput(date);
        const isCurrentMonth = date.getMonth() === month;
        const isToday = dateString === todayString;
        const isSelected = selectedDate && formatDateForInput(selectedDate) === dateString;
        const isPast = date < new Date(today.getFullYear(), today.getMonth(), today.getDate());

        const dayElement = document.createElement('div');
        dayElement.className = 'w-8 h-8 rounded-lg flex items-center justify-center text-sm font-medium cursor-pointer transition-colors';

        if (!isCurrentMonth || isPast) {
          dayElement.className += ' text-gray-500 cursor-not-allowed';
        } else if (isSelected) {
          dayElement.className += ' bg-black text-white';
        } else if (isToday) {
          dayElement.className += ' bg-red-600 text-white';
        } else {
          dayElement.className += ' text-white hover:bg-red-600 hover:text-white';
        }

        dayElement.textContent = date.getDate();
        dayElement.setAttribute('data-date', dateString);

        if (isCurrentMonth && !isPast) {
          dayElement.addEventListener('click', function () {
            selectDate(date);
          });
        }

        calendarGrid.appendChild(dayElement);
      }
    }

    // Pilih tanggal
    function selectDate(date) {
      selectedDate = date;
      selectedDateText.textContent = formatDateForDisplay(date);
      bookingDateInput.value = formatDateForInput(date);
      generateCalendar(currentMonth, currentYear);
    }

    // Event listeners untuk modal kalender
    datePickerBtn.addEventListener('click', function () {
      showModalWithAnimation(datePickerModal);
      generateCalendar(currentMonth, currentYear);
    });

    closeDatePickerModal.addEventListener('click', function () {
      hideModalWithAnimation(datePickerModal);
    });

    datePickerModal.addEventListener('click', function (e) {
      if (e.target === datePickerModal) {
        hideModalWithAnimation(datePickerModal);
      }
    });

    // Navigasi bulan
    prevMonth.addEventListener('click', function () {
      currentMonth--;
      if (currentMonth < 0) {
        currentMonth = 11;
        currentYear--;
      }
      generateCalendar(currentMonth, currentYear);
    });

    nextMonth.addEventListener('click', function () {
      currentMonth++;
      if (currentMonth > 11) {
        currentMonth = 0;
        currentYear++;
      }
      generateCalendar(currentMonth, currentYear);
    });

    // Konfirmasi tanggal
    confirmDate.addEventListener('click', function () {
      if (selectedDate) {
        hideModalWithAnimation(datePickerModal);
        // Update waktu yang tersedia berdasarkan tanggal yang dipilih
        if (typeof fillTimeOptions === 'function') {
          fillTimeOptions();
        }
        if (typeof updateTableStatus === 'function') {
          updateTableStatus();
        }
      } else {
        alert('Silakan pilih tanggal terlebih dahulu.');
      }
    });

    // Keyboard support untuk modal kalender
    document.addEventListener('keydown', function (e) {
      if (datePickerModal.classList.contains('flex')) {
        if (e.key === 'Escape') {
          hideModalWithAnimation(datePickerModal);
        } else if (e.key === 'ArrowLeft') {
          e.preventDefault();
          prevMonth.click();
        } else if (e.key === 'ArrowRight') {
          e.preventDefault();
          nextMonth.click();
        } else if (e.key === 'Enter') {
          e.preventDefault();
          confirmDate.click();
        }
      }
    });

    // Tambahkan ripple effect ke tombol kalender
    [prevMonth, nextMonth, confirmDate].forEach(button => {
      button.addEventListener('click', createRipple);
    });

    // Inisialisasi kalender dengan tanggal hari ini

    selectDate(today);

    // Tambahkan CSS untuk kalender
    const calendarStyle = document.createElement('style');
    calendarStyle.textContent = `
      #calendarGrid > div {
        position: relative;
        overflow: hidden;
      }
      
      #calendarGrid > div:hover {
        transform: scale(1.1);
      }
      
      #calendarGrid > div.selected {
        animation: pulse 0.3s ease-in-out;
      }
      
      @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
      }
      
      #datePickerBtn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      }
      
      #prevMonth, #nextMonth {
        position: relative;
        overflow: hidden;
      }
    `;
    document.head.appendChild(calendarStyle);

    // Update fungsi selectDate untuk menambahkan animasi
    const originalSelectDate = selectDate;
    selectDate = function (date) {
      originalSelectDate(date);

      // Tambahkan animasi pada tanggal yang dipilih
      const selectedElement = calendarGrid.querySelector(`[data-date="${formatDateForInput(date)}"]`);
      if (selectedElement) {
        selectedElement.classList.add('selected');
        setTimeout(() => {
          selectedElement.classList.remove('selected');
        }, 300);
      }
    };

    // --- Modal Time Picker ---
    const timePickerBtn = document.getElementById('timePickerBtn');
    const timePickerModal = document.getElementById('timePickerModal');
    const closeTimePickerModal = document.getElementById('closeTimePickerModal');
    const timeGrid = document.getElementById('timeGrid');
    const confirmTime = document.getElementById('confirmTime');
    const selectedTimeText = document.getElementById('selectedTimeText');
    const bookingTimeInput = document.getElementById('bookingTime');

    let selectedTime = null;

    // Generate waktu dari 08:00 sampai 22:00 dengan interval 30 menit
    function generateTimeOptions() {
      timeGrid.innerHTML = '';

      const startHour = 8;
      const endHour = 22;
      const interval = 30; // menit

      for (let hour = startHour; hour <= endHour; hour++) {
        for (let minute = 0; minute < 60; minute += interval) {
          if (hour === endHour && minute > 0) break; // Stop at 22:00

          const time = new Date();
          time.setHours(hour, minute, 0, 0);

          const timeString = time.toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
          });

          const timeValue = time.toTimeString().slice(0, 5); // Format HH:MM

          // Cek apakah waktu sudah lewat untuk hari ini
          const now = new Date();
          const isToday = selectedDate && formatDateForInput(selectedDate) === formatDateForInput(now);
          const isPast = isToday && time < now;

          const timeBtn = document.createElement('button');
          timeBtn.className = `py-3 px-4 rounded-lg border border-white/30 text-white font-medium transition-colors ${isPast
              ? 'bg-gray-600 cursor-not-allowed opacity-50'
              : 'bg-red-700 hover:bg-red-600 hover:border-white/50'
            }`;
          timeBtn.textContent = timeString;
          timeBtn.setAttribute('data-time', timeValue);
          timeBtn.disabled = isPast;

          if (!isPast) {
            timeBtn.addEventListener('click', function () {
              selectTime(timeString, timeValue);
            });
          }

          timeGrid.appendChild(timeBtn);
        }
      }
    }

    // Pilih waktu
    function selectTime(timeString, timeValue) {
      selectedTime = timeValue;
      selectedTimeText.textContent = timeString;
      bookingTimeInput.value = timeValue;

      // Update tampilan tombol yang dipilih
      timeGrid.querySelectorAll('button').forEach(btn => {
        btn.classList.remove('bg-black', 'text-yellow-400');
        btn.classList.add('bg-red-700');
      });

      const selectedBtn = timeGrid.querySelector(`[data-time="${timeValue}"]`);
      if (selectedBtn) {
        selectedBtn.classList.remove('bg-red-700');
        selectedBtn.classList.add('bg-black', 'text-yellow-400');
      }
    }

    // Event listeners untuk modal time picker
    timePickerBtn.addEventListener('click', function () {
      showModalWithAnimation(timePickerModal);
      generateTimeOptions();
    });

    closeTimePickerModal.addEventListener('click', function () {
      hideModalWithAnimation(timePickerModal);
    });

    timePickerModal.addEventListener('click', function (e) {
      if (e.target === timePickerModal) {
        hideModalWithAnimation(timePickerModal);
      }
    });

    // Konfirmasi waktu
    confirmTime.addEventListener('click', function () {
      if (selectedTime) {
        hideModalWithAnimation(timePickerModal);
        // Update status meja berdasarkan waktu yang dipilih
        if (typeof updateTableStatus === 'function') {
          updateTableStatus();
        }
      } else {
        alert('Silakan pilih waktu terlebih dahulu.');
      }
    });

    // Keyboard support untuk modal time picker
    document.addEventListener('keydown', function (e) {
      if (timePickerModal.classList.contains('flex')) {
        if (e.key === 'Escape') {
          hideModalWithAnimation(timePickerModal);
        } else if (e.key === 'Enter') {
          e.preventDefault();
          confirmTime.click();
        }
      }
    });

    // Tambahkan ripple effect ke tombol time picker
    [confirmTime].forEach(button => {
      button.addEventListener('click', createRipple);
    });

    // Update fungsi selectDate untuk reset waktu saat tanggal berubah
    const originalSelectDateForTime = selectDate;
    selectDate = function (date) {
      originalSelectDateForTime(date);

      // Reset waktu yang dipilih saat tanggal berubah
      selectedTime = null;
      selectedTimeText.textContent = 'Select a time';
      bookingTimeInput.value = '';

      // Tambahkan animasi pada tanggal yang dipilih
      const selectedElement = calendarGrid.querySelector(`[data-date="${formatDateForInput(date)}"]`);
      if (selectedElement) {
        selectedElement.classList.add('selected');
        setTimeout(() => {
          selectedElement.classList.remove('selected');
        }, 300);
      }
    };

    // Tambahkan CSS untuk time picker
    const timePickerStyle = document.createElement('style');
    timePickerStyle.textContent = `
      #timeGrid button {
        position: relative;
        overflow: hidden;
      }
      
      #timeGrid button:hover:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
      }
      
      #timePickerBtn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      }
      
      #timeGrid button.selected {
        animation: timePulse 0.3s ease-in-out;
      }
      
      @keyframes timePulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
      }
    `;
    document.head.appendChild(timePickerStyle);


  </script>
  <script src="script/script.js"></script>
  <script src="script/menuLoad.js"></script>
</body>

</html>