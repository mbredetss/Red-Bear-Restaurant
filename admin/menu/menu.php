<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Admin Menu</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 p-8">
  <div id="message" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"></div>
  <!-- Form Tambah Menu -->
  <div class="bg-white p-6 rounded shadow mb-8 max-w-xl mx-auto">
    <h2 class="text-xl font-semibold mb-2">Tambah Menu</h2>
    <form id="form-tambah-menu" enctype="multipart/form-data">
      <div class="mb-4">
        <label class="block font-medium mb-1">Nama Menu</label>
        <input type="text" name="name" class="border rounded w-full px-3 py-2" required>
      </div>
      <div class="mb-4">
        <label class="block font-medium mb-1">Jenis</label>
        <select name="jenis" class="border rounded w-full px-3 py-2" required>
          <option value="" disabled selected>Pilih jenis</option>
          <option value="makanan">Makanan</option>
          <option value="minuman">Minuman</option>
        </select>
      </div>

      <div class="mb-4">
        <label class="block font-medium mb-1">Gambar</label>
        <input type="file" name="image" class="border rounded w-full px-3 py-2" required>
      </div>
      <button type="submit"
        class="bg-blue-600 hover:bg-blue-700 transition text-white px-4 py-2 rounded w-full font-semibold">Tambah</button>
    </form>
  </div>
  <!-- Daftar Menu -->
  <div id="menu-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"></div>

  <!-- Modal Edit -->
  <div id="modal-edit" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md relative">
      <button onclick="closeModal()"
        class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
      <h3 class="text-lg font-bold mb-4">Edit Menu</h3>
      <form id="form-edit-menu">
        <input type="hidden" name="old_name" id="modal-old-name">
        <div class="mb-3">
          <label class="block font-medium mb-1">Nama Menu</label>
          <input type="text" name="new_name" id="modal-new-name" class="border rounded w-full px-3 py-2" required>
        </div>
        <div class="flex gap-2 mt-4">
          <button type="submit"
            class="bg-green-600 hover:bg-green-700 transition text-white px-4 py-2 rounded font-semibold flex-1">Simpan</button>
          <button type="button" onclick="closeModal()"
            class="bg-gray-300 hover:bg-gray-400 transition text-gray-800 px-4 py-2 rounded font-semibold flex-1">Batal</button>
        </div>
      </form>
    </div>
  </div>

  <script src="script/script.js"></script>
</body>

</html>