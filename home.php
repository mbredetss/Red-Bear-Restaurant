<?php
include __DIR__ . '/init.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Red Bear</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link href="style/styles.css" rel="stylesheet">
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



  <!-- WhatsApp Button -->
  <a href="https://api.whatsapp.com/send" target="_blank" class="fixed bottom-4 right-4 z-50">
    <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp" class="h-12 w-12">
  </a>

  <script src="script/script.js"></script>
  <script src="script/menuLoad.js"></script>
</body>

</html>