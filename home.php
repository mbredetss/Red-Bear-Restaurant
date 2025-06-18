<?php
include __DIR__ . '/init.php';
require_once 'script/session_check.php'; // Mengimpor file session_check.php
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Red Bear</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link href="style/styles.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body class="bg-white-100">
  <!-- Navbar -->
  <header id="navbar" class="fixed w-full z-50 flex items-center justify-between px-6 py-4">
    <a href="home.php">
      <img src="img/red-bear-logo.png" alt="Logo" class="h-20" style="border-radius: 50%;">
    </a>

    <nav class="flex gap-6 items-center">
      <a href="#" class="hover:bg-white hover:text-black px-3 py-1 rounded font-bold text-white">HOME</a>
      <a href="#menu" class="hover:bg-white hover:text-black px-3 py-1 rounded font-bold text-white">MENU</a>
      <a href="#" class="hover:bg-white hover:text-black px-3 py-1 rounded font-bold text-white">ABOUT</a>
      <a href="#" class="hover:bg-white hover:text-black px-3 py-1 rounded font-bold text-white">FOLLOW</a>
      <a href="#" class="hover:bg-white hover:text-black px-3 py-1 rounded font-bold text-white">LOCATION</a>
      <a href="#" class="hover:bg-white hover:text-black px-3 py-1 rounded font-bold text-white">MERCHANDISE</a>
      <a href="#" class="hover:bg-white hover:text-black px-3 py-1 rounded font-bold text-white">PRESS</a>
      <a href="#" class="hover:bg-white hover:text-black px-3 py-1 rounded font-bold text-white">BLOG</a>
      <a id="bookTable" href="#" class="bg-black text-white px-4 py-2 rounded font-bold">BOOK A TABLE</a>
      <a id="orderStatusBtn" href="#"
        class="relative hover:bg-white hover:text-black px-3 py-2 rounded font-bold text-white flex items-center gap-2">
        <i class="fas fa-receipt text-lg"></i>
        <span id="orderBadge"
          class="absolute -top-1 -right-2 bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full hidden">
          •
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
            class="hidden absolute right-0 mt-3 w-56 bg-white rounded-xl shadow-lg z-50 text-sm text-gray-700 overflow-hidden ring-1 ring-black/5 transition-all duration-300">
            <div class="px-4 py-3 border-b">
              <p class="font-semibold">Halo, <?= htmlspecialchars($user['name']) ?></p>
              <p class="text-xs text-gray-500 mt-1">Saldo: <span
                  class="font-medium text-green-600">Rp<?= number_format($saldo, 0, ',', '.') ?></span></p>
            </div>
            <a href="logout.php" class="block px-4 py-2 hover:bg-gray-100">🚪 Logout</a>
          </div>
        </div>
      <?php else: ?>
        <!-- Tampilkan tombol login jika belum login -->
        <a href="login_register/login.php"
          class="ml-4 bg-white text-black border border-black px-4 py-2 rounded hover:bg-black hover:text-white font-bold transition">Login</a>
      <?php endif; ?>

      </div>

    </nav>
  </header>

  <!-- Hero Section -->
  <section class="relative h-screen bg-cover bg-center carousel-slide overflow-hidden"
    style="background-image: url('img/image.png');">
    <div class="carousel-bg absolute inset-0 transition-opacity duration-700 opacity-0 pointer-events-none"></div>
    <div
      class="absolute inset-0 bg-black bg-opacity-50 flex flex-col items-center justify-center text-center text-white px-4">
      <h1 class="text-5xl font-extrabold">RED BEAR</h1>
      <p class="text-xl mt-2">Korean Barbeque</p>

      <!-- Carousel indicators -->
      <div class="flex gap-3 mt-8">
        <span class="carousel-indicator active w-3 h-3 bg-white rounded-full cursor-pointer" data-slide="0"></span>
        <span class="carousel-indicator w-3 h-3 bg-white rounded-full opacity-60 cursor-pointer" data-slide="1"></span>
        <span class="carousel-indicator w-3 h-3 bg-white rounded-full opacity-60 cursor-pointer" data-slide="2"></span>
      </div>

      <!-- Navigation buttons -->
      <button
        class="carousel-btn prev-btn absolute left-10 top-1/2 transform -translate-y-1/2 p-3 text-white bg-black bg-opacity-50 rounded-full hover:bg-opacity-75">&#60;</button>
      <button
        class="carousel-btn next-btn absolute right-10 top-1/2 transform -translate-y-1/2 p-3 text-white bg-black bg-opacity-50 rounded-full hover:bg-opacity-75">&#62;</button>
    </div>
  </section>

  <!-- Public Display + Menu Grid -->
  <section id="menu" class="flex flex-col items-center py-12">
    <div class="flex items-center mb-8 justify-between w-full max-w-5xl">
      <img src="img/sapi.png" alt="sapi" style="height: 8rem;" />
      <h1 class="text-4xl font-bold text-center flex-1">TASTY BITES</h1>
      <img src="img/sapi.png" alt="sapi" style="height: 8rem;" />
    </div>

    <!-- Menu Grid -->
    <div id="menu-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4"></div>
  </section>

  <div id="pesan-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
    <div class="bg-white p-6 rounded-lg w-80 space-y-4 relative">
      <button id="pesan-close" class="absolute top-2 right-3 text-xl font-bold">&times;</button>
      <h3 id="pesan-menu-nama" class="text-lg font-semibold"></h3>
      <input type="number" id="pesan-jumlah" min="1" value="1" class="w-full p-2 border rounded" />
      <button id="pesan-kirim" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Pesan
        Sekarang</button>
    </div>
  </div>


  <!-- Modal Tampilan Menu Detail -->
  <div id="menu-modal"
    class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 hidden transition-opacity duration-300">
    <div
      class="relative bg-white rounded-xl overflow-hidden max-w-md w-full max-h-screen overflow-y-auto scale-95 opacity-0 transform transition-all duration-300"
      id="modal-content">

      <!-- Tombol Tutup -->
      <button id="modal-close"
        class="absolute top-2 right-2 text-white text-3xl z-10 bg-black bg-opacity-50 px-2 rounded-full">&times;</button>

      <!-- Navigasi Kiri -->
      <button id="modal-prev"
        class="absolute left-2 top-1/2 transform -translate-y-1/2 text-white text-4xl z-10 bg-black bg-opacity-50 px-3 rounded-full">&lt;</button>

      <!-- Navigasi Kanan -->
      <button id="modal-next"
        class="absolute right-2 top-1/2 transform -translate-y-1/2 text-white text-4xl z-10 bg-black bg-opacity-50 px-3 rounded-full">&gt;</button>

      <!-- Gambar Menu -->
      <img id="modal-image" src="" alt="Menu Detail" class="w-full object-cover max-h-[400px]" />

      <!-- Nama Menu -->
      <div class="bg-white text-black text-center text-lg font-semibold py-3 border-t" id="modal-name"></div>
    </div>
  </div>

  <!-- Modal (dengan tombol Pesanan Saya) -->
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
  <div id="orderDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg shadow-lg w-[500px] max-h-[80vh] overflow-auto">
      <h2 class="text-2xl font-bold mb-4">Detail Pesanan Anda</h2>

      <!-- Tab Navigation -->
      <div class="flex border-b mb-4">
        <button id="tabAktif" class="flex-1 py-2 text-center font-medium border-b-2 border-black">Aktif</button>
        <button id="tabSelesai"
          class="flex-1 py-2 text-center font-medium text-gray-500 hover:text-black">Selesai</button>
      </div>

      <!-- Tab Content -->
      <ul id="orderListAktif" class="space-y-3 text-gray-700">
        <!-- Pesanan Aktif akan diisi via JS -->
      </ul>
      <ul id="orderListSelesai" class="space-y-3 text-gray-700 hidden">
        <!-- Pesanan Selesai akan diisi via JS -->
      </ul>

      <div class="text-right mt-4">
        <button id="closeOrderDetails" class="px-4 py-2 bg-black text-white rounded hover:bg-gray-800">Tutup</button>
      </div>
    </div>
  </div>

  <!-- WhatsApp Button -->
  <a href="https://api.whatsapp.com/send" target="_blank" class="fixed bottom-4 right-4 z-50">
    <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp" class="h-12 w-12">
  </a>

  <script src="script/script.js"></script>
  <script src="script/menuLoad.js"></script>
</body>

</html>