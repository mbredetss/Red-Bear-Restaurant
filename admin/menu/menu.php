<?php
session_start();
require_once '../../database.php';

// Cek login dan peran admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../home.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Admin Menu</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
  <style>
    /* CSS custom untuk transisi modal dan beberapa fine-tuning jika diperlukan */
    #modal-edit.hidden {
      display: none;
    }
  </style>
</head>

<body class="bg-gray-50 text-gray-800">

  <div class="container mx-auto px-4 py-8">
    <header class="flex justify-between items-center mb-10">
      <div>
        <h1 class="text-4xl font-bold text-gray-900">Manajemen Menu</h1>
        <p class="text-gray-600 mt-2">Tambah, edit, dan kelola item menu restoran Anda.</p>
      </div>
      <a href="../beranda.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
        <i class="fas fa-arrow-left mr-2"></i>
        Kembali ke Dashboard
      </a>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Form Tambah Menu -->
      <div class="lg:col-span-1">
        <div class="bg-white p-6 rounded-lg shadow-md sticky top-8">
          <h2 class="text-2xl font-semibold mb-5">Tambah Menu Baru</h2>
          <form id="form-tambah-menu" enctype="multipart/form-data" class="space-y-4">
            <div>
              <label for="name" class="block font-medium mb-1 text-gray-700">Nama Menu</label>
              <input type="text" id="name" name="name" class="border border-gray-300 rounded-lg w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
              <label for="jenis" class="block font-medium mb-1 text-gray-700">Jenis</label>
              <select id="jenis" name="jenis" class="border border-gray-300 rounded-lg w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                <option value="" disabled selected>Pilih jenis</option>
                <option value="makanan">Makanan</option>
                <option value="minuman">Minuman</option>
              </select>
            </div>
            <div>
              <label for="image" class="block font-medium mb-1 text-gray-700">Gambar</label>
              <input type="file" id="image" name="image" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required>
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 transition-all text-white px-4 py-2.5 rounded-lg w-full font-semibold focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
              Tambah Menu
            </button>
          </form>
        </div>
      </div>

      <!-- Daftar Menu -->
      <div id="menu-list-container" class="lg:col-span-2">
        <div id="menu-list" class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Menu items will be loaded here -->
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Edit -->
  <div id="modal-edit" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 hidden opacity-0 transition-opacity duration-300">
    <div id="modal-content" class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md relative transform scale-95 transition-all duration-300">
      <button onclick="closeModal()" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 text-3xl font-light">&times;</button>
      <h3 class="text-2xl font-bold mb-5">Edit Nama Menu</h3>
      <form id="form-edit-menu">
        <input type="hidden" name="old_name" id="modal-old-name">
        <div class="mb-4">
          <label for="modal-new-name" class="block font-medium mb-1 text-gray-700">Nama Menu Baru</label>
          <input type="text" name="new_name" id="modal-new-name" class="border border-gray-300 rounded-lg w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>
        <div class="flex gap-3 mt-6">
          <button type="submit" class="bg-green-600 hover:bg-green-700 transition-all text-white px-4 py-2 rounded-lg font-semibold flex-1 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">Simpan Perubahan</button>
          <button type="button" onclick="closeModal()" class="bg-gray-200 hover:bg-gray-300 transition-all text-gray-800 px-4 py-2 rounded-lg font-semibold flex-1">Batal</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Message Toast -->
  <div id="message" class="fixed bottom-5 right-5 hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-lg transform translate-y-16 transition-transform duration-300"></div>

  <script src="script/script.js"></script>
</body>

</html>