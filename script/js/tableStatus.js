// Cek ketersediaan meja saat halaman dimuat
export function initTableStatusCheck() {
  if (window.hasScannedTable) {
    startTableStatusCheck();
  }
}

// Fungsi untuk menampilkan modal meja tidak tersedia
export function showTableUnavailableModal(unavailableReason, existingTableId = null) {
  const modal = document.getElementById('tableUnavailableModal');
  const reason = document.getElementById('unavailableReason');
  const tableInfo = document.getElementById('tableInfo');

  if (unavailableReason === 'booking') {
    reason.textContent = 'Meja ini sudah di-booking online oleh pelanggan lain.';
    tableInfo.innerHTML = `
      <p><strong>Status:</strong> <span class="text-blue-600">Di-booking Online</span></p>
      <p><strong>Tanggal:</strong> ${new Date().toLocaleDateString('id-ID')}</p>
      <p class="text-sm text-gray-500 mt-2">Silakan pilih meja lain atau booking meja untuk waktu yang berbeda.</p>
    `;
  } else if (unavailableReason === 'different_table') {
    reason.textContent = 'Anda sudah memiliki sesi aktif di meja lain.';
    tableInfo.innerHTML = `
      <p><strong>Status:</strong> <span class="text-purple-600">Sesi Aktif di Meja Lain</span></p>
      <p><strong>Meja Aktif:</strong> <span class="font-bold">Meja ${existingTableId}</span></p>
      <p><strong>Tanggal:</strong> ${new Date().toLocaleDateString('id-ID')}</p>
      <p class="text-sm text-gray-500 mt-2">Anda tidak dapat mengakses meja lain selama masih memiliki sesi aktif. Silakan kembali ke meja Anda atau selesaikan pesanan terlebih dahulu.</p>
    `;
  } else {
    reason.textContent = 'Meja ini sudah ditempati oleh pelanggan lain.';
    tableInfo.innerHTML = `
      <p><strong>Status:</strong> <span class="text-orange-600">Sedang Digunakan</span></p>
      <p><strong>Tanggal:</strong> ${new Date().toLocaleDateString('id-ID')}</p>
      <p class="text-sm text-gray-500 mt-2">Silakan pilih meja lain atau tunggu hingga meja ini kosong.</p>
    `;
  }

  modal.classList.remove('hidden');
  modal.classList.add('flex');
}

// Event listener untuk menutup modal
export function setupTableUnavailableModalEvents() {
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
}

// Fungsi untuk mengecek status meja secara berkala
let tableStatusCheckInterval;

export function startTableStatusCheck() {
  const tableId = window.scannedTableId || null;
  if (!tableId) return;

  // Cek setiap 30 detik
  tableStatusCheckInterval = setInterval(function () {
    checkTableStatus(tableId);
  }, 30000);

  // Cek pertama kali
  checkTableStatus(tableId);
}

export function checkTableStatus(tableId) {
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