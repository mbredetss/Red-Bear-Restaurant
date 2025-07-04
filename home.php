<?php
require_once 'script/session_check.php'; // Mengimpor file session_check.php
// Logika untuk menangani scan QR Code Meja
include_once 'script/php/handle_qr_scan.php';

include_once 'script/php/init_db.php';

// Ambil postingan blog yang sudah dipublikasikan
require_once 'script/php/get_published_blog_posts.php';
$blog_posts = get_published_blog_posts($koneksi, 3);
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
      <a href="#merchandise"
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

  <!-- Merchandise Section -->
  <section id="merchandise" class="py-24 bg-gradient-to-b from-yellow-50 via-white to-red-50">
    <div class="container mx-auto px-4 max-w-6xl">
      <div class="text-center mb-14">
        <h2 class="text-5xl font-extrabold text-red-700 mb-4" style="font-family: 'Montserrat', sans-serif;">Red Bear
          Merchandise</h2>
        <p class="text-gray-600 text-lg max-w-2xl mx-auto">
          Show your love for Red Bear! Explore our exclusive collection of stylish and high-quality merchandise, perfect
          for gifts or your own collection.
        </p>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10">
        <!-- Merchandise Item 1 -->
        <div
          class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 hover:shadow-2xl transition-all duration-300 flex flex-col group">
          <div class="relative h-64 overflow-hidden">
            <img src="img/merch/tshirt-redbear.png" alt="Red Bear T-Shirt"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
            <span
              class="absolute top-4 left-4 bg-red-600 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg">Best
              Seller</span>
          </div>
          <div class="flex-1 flex flex-col p-6">
            <h3 class="text-2xl font-bold mb-2 text-gray-800 group-hover:text-red-700 transition-colors">Red Bear
              T-Shirt</h3>
            <p class="text-gray-700 mb-4 flex-1">Premium cotton t-shirt with exclusive Red Bear design. Available in all
              sizes!</p>
            <div class="flex items-center justify-between mt-auto">
              <span class="text-xl font-bold text-red-600">Rp120.000</span>
              <a href="https://wa.me/6281234567890?text=Halo%20Red%20Bear%2C%20saya%20ingin%20beli%20T-Shirt%20Red%20Bear"
                target="_blank"
                class="inline-flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white px-5 py-2 rounded-full font-bold text-sm shadow-md transition-all duration-200">
                <i class="fab fa-whatsapp"></i> Order
              </a>
            </div>
          </div>
        </div>
        <!-- Merchandise Item 2 -->
        <div
          class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 hover:shadow-2xl transition-all duration-300 flex flex-col group">
          <div class="relative h-64 overflow-hidden">
            <img src="img/merch/tumbler-redbear.png" alt="Red Bear Tumbler"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
            <span
              class="absolute top-4 left-4 bg-yellow-500 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg">Limited
              Edition</span>
          </div>
          <div class="flex-1 flex flex-col p-6">
            <h3 class="text-2xl font-bold mb-2 text-gray-800 group-hover:text-red-700 transition-colors">Red Bear
              Tumbler</h3>
            <p class="text-gray-700 mb-4 flex-1">Stay hydrated in style with our exclusive Red Bear tumbler. Keeps your
              drink hot or cold for hours.</p>
            <div class="flex items-center justify-between mt-auto">
              <span class="text-xl font-bold text-red-600">Rp85.000</span>
              <a href="https://wa.me/6281234567890?text=Halo%20Red%20Bear%2C%20saya%20ingin%20beli%20Tumbler%20Red%20Bear"
                target="_blank"
                class="inline-flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white px-5 py-2 rounded-full font-bold text-sm shadow-md transition-all duration-200">
                <i class="fab fa-whatsapp"></i> Order
              </a>
            </div>
          </div>
        </div>
        <!-- Merchandise Item 3 -->
        <div
          class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 hover:shadow-2xl transition-all duration-300 flex flex-col group">
          <div class="relative h-64 overflow-hidden">
            <img src="img/merch/totebag-redbear.png" alt="Red Bear Tote Bag"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
            <span
              class="absolute top-4 left-4 bg-blue-600 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg">New
              Arrival</span>
          </div>
          <div class="flex-1 flex flex-col p-6">
            <h3 class="text-2xl font-bold mb-2 text-gray-800 group-hover:text-red-700 transition-colors">Red Bear Tote
              Bag</h3>
            <p class="text-gray-700 mb-4 flex-1">Eco-friendly tote bag with cute Red Bear print. Perfect for daily use
              or as a gift!</p>
            <div class="flex items-center justify-between mt-auto">
              <span class="text-xl font-bold text-red-600">Rp55.000</span>
              <a href="https://wa.me/6281234567890?text=Halo%20Red%20Bear%2C%20saya%20ingin%20beli%20Tote%20Bag%20Red%20Bear"
                target="_blank"
                class="inline-flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white px-5 py-2 rounded-full font-bold text-sm shadow-md transition-all duration-200">
                <i class="fab fa-whatsapp"></i> Order
              </a>
            </div>
          </div>
        </div>
        <!-- Add more merchandise items as needed -->
      </div>
      <div class="text-center mt-12">
        <a href="https://www.instagram.com/redbear.indonesia/" target="_blank"
          class="inline-flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded-full font-bold text-lg shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-400 hover:scale-105">
          <i class="fab fa-instagram"></i> See More on Instagram
        </a>
      </div>
    </div>
  </section>



  <!-- About Section -->
  <section id="about" class="py-24 bg-gradient-to-b from-red-100 via-white to-gray-100">
    <div class="container mx-auto px-4 max-w-5xl">
      <div class="text-center mb-14">
        <h2 class="text-5xl font-extrabold text-red-700 mb-4" style="font-family: 'Montserrat', sans-serif;">About Red
          Bear</h2>
        <p class="text-gray-600 text-lg max-w-2xl mx-auto">
          Red Bear is your destination for authentic Korean Barbeque, offering premium meats, fresh ingredients, and a
          cozy atmosphere. Our mission is to bring the best of Korean cuisine to your table, ensuring every visit is a
          memorable dining experience.
        </p>
      </div>
      <div class="flex flex-col md:flex-row items-center gap-12 md:gap-16">
        <div class="w-full md:w-1/2 flex justify-center">
          <div class="relative group">
            <img src="img/about-red-bear.png" alt="About Red Bear"
              class="rounded-2xl shadow-2xl object-cover w-full max-w-md border-8 border-white group-hover:scale-105 transition-transform duration-300">
            <span
              class="absolute -top-4 -left-4 bg-red-600 text-white px-4 py-1 rounded-full text-xs font-bold shadow-lg">Since
              2022</span>
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
            <a href="#menu"
              class="inline-block bg-red-600 hover:bg-red-700 text-white font-bold px-8 py-3 rounded-full shadow-lg transition-all text-lg">See
              Our Menu</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Location Section -->
  <section id="location" class="py-24 bg-gradient-to-b from-gray-100 via-white to-red-50">
    <div class="container mx-auto px-4 max-w-5xl">
      <div class="text-center mb-14">
        <h2 class="text-5xl font-extrabold text-red-700 mb-4 location-title"
          style="font-family: 'Montserrat', sans-serif;">Our Location</h2>
        <p class="text-gray-600 text-lg max-w-2xl mx-auto location-desc">
          Find us in the heart of Jakarta and experience the best Korean BBQ in town. We can't wait to welcome you!
        </p>
      </div>
      <div class="flex flex-col md:flex-row items-center gap-12 md:gap-16">
        <div class="w-full md:w-1/2 flex justify-center">
          <div
            class="relative group w-full max-w-md rounded-2xl overflow-hidden shadow-2xl border-8 border-white location-map-container">
            <iframe src="https://www.google.com/maps?q=Jakarta+Indonesia&output=embed" width="100%" height="340"
              class="rounded-2xl w-full h-80 border-none location-map" style="min-height:320px;" allowfullscreen=""
              loading="lazy"></iframe>
            <span
              class="absolute top-4 left-4 bg-red-600 text-white px-4 py-1 rounded-full text-xs font-bold shadow-lg location-map-badge">See
              on Map</span>
          </div>
        </div>
        <div class="flex-1">
          <div class="bg-white rounded-2xl shadow-lg p-8 border border-gray-200 location-card">
            <h3 class="text-3xl font-bold text-gray-800 mb-4 location-card-title">Red Bear Restaurant</h3>
            <ul class="text-gray-700 space-y-4 text-lg">
              <li class="flex items-center group">
                <span class="text-red-600 text-xl mr-3 icon-effect"><i class="fas fa-map-marker-alt"></i></span>
                Jl. Contoh Alamat No. 123, Jakarta, Indonesia
              </li>
              <li class="flex items-center group">
                <span class="text-red-600 text-xl mr-3 icon-effect"><i class="fas fa-phone-alt"></i></span>
                <a href="tel:02112345678" class="hover:underline">(021) 1234-5678</a>
              </li>
              <li class="flex items-center group">
                <span class="text-red-600 text-xl mr-3 icon-effect"><i class="fas fa-clock"></i></span>
                Open: <span class="font-semibold">10:00 - 22:00</span> (Everyday)
              </li>
              <li class="flex items-center group">
                <span class="text-red-600 text-xl mr-3 icon-effect"><i class="fas fa-envelope"></i></span>
                <a href="mailto:info@redbear.com" class="hover:underline">info@redbear.com</a>
              </li>
            </ul>
            <div class="mt-8 flex gap-4">
              <a href="https://www.instagram.com/redbear.indonesia?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw=="
                target="_blank"
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
  </section>

  <!-- Blog Section -->
  <section id="blog" class="py-24 bg-gradient-to-b from-white via-red-50 to-gray-100">
    <div class="container mx-auto px-4 max-w-6xl">
      <div class="text-center mb-14">
        <h2 class="text-5xl font-extrabold text-red-700 mb-4" style="font-family: 'Montserrat', sans-serif;">Our Latest
          Blog</h2>
        <p class="text-gray-600 text-lg max-w-2xl mx-auto">
          Stay updated with our news, recipes, and special events.
        </p>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
        <?php if (empty($blog_posts)): ?>
          <p class="col-span-full text-center text-gray-500">Belum ada postingan blog yang dipublikasikan.</p>
        <?php else: ?>
          <?php foreach ($blog_posts as $post): ?>
            <div
              class="group bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 hover:shadow-2xl transition-all duration-300 flex flex-col">
              <?php if ($post['image_path']): ?>
                <div class="relative h-56 overflow-hidden">
                  <img src="<?php echo htmlspecialchars($post['image_path']); ?>"
                    alt="<?php echo htmlspecialchars($post['title']); ?>"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                  <span
                    class="absolute top-4 left-4 bg-red-600 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg">Blog</span>
                </div>
              <?php endif; ?>
              <div class="flex-1 flex flex-col p-6">
                <h3 class="text-2xl font-bold mb-2 text-gray-800 group-hover:text-red-700 transition-colors">
                  <?php echo htmlspecialchars($post['title']); ?>
                </h3>
                <p class="text-gray-500 text-xs mb-3">Oleh: <span
                    class="font-semibold"><?php echo htmlspecialchars($post['author_name']); ?></span> &bull;
                  <?php echo date('d M Y', strtotime($post['created_at'])); ?>
                </p>
                <p class="text-gray-700 mb-4 flex-1"><?php echo nl2br(substr(strip_tags($post['content']), 0, 120)); ?>...
                </p>
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

  <script type="module">
    window.hasScannedTable = <?php echo isset($_SESSION['scanned_table_id']) ? 'true' : 'false'; ?>;

    window.hasBookedTable = false;

    import { initTableStatusCheck, showTableUnavailableModal, setupTableUnavailableModalEvents } from './script/js/tableStatus.js';
    // Setup event listener modal
    setupTableUnavailableModalEvents();
    // Tampilkan modal jika meja tidak tersedia
    <?php if (isset($_SESSION['table_available']) && !$_SESSION['table_available']): ?>
      showTableUnavailableModal(
        '<?php echo isset($_SESSION['table_unavailable_reason']) ? $_SESSION['table_unavailable_reason'] : ''; ?>',
        <?php echo isset($_SESSION['existing_table_id']) ? $_SESSION['existing_table_id'] : 'null'; ?>
      );
    <?php endif; ?>
    // Mulai pengecekan berkala jika user sudah scan meja
    initTableStatusCheck();
  </script>
  <script src="script/js/fade-inEffectBlog.js"></script>
  <script src="script/js/bookTableModal.js"></script>
  <script src="script/js/guestCountModal.js"></script>
  <script src="script/js/datePickerModal.js"></script>
  <script src="script/js/timePickerModal.js"></script>
  <script src="script/script.js"></script>
  <script src="script/menuLoad.js"></script>
</body>

</html>