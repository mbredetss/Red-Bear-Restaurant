document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('menu-container');
  let menuData = [];
  let currentIndex = 0;
  let selectedMenuIndex = null;

  // Fetch data dari backend
  fetch('./admin/menu/api/menu_list.php')
    .then(res => res.json())
    .then(data => {
      // Urutkan: makanan dulu, lalu minuman
      menuData = data.sort((a, b) => {
        if (a.jenis === b.jenis) return 0;
        return a.jenis === 'makanan' ? -1 : 1;
      });
      renderMenu();
    })
    .catch(error => {
      console.error('Gagal memuat menu:', error);
    });

  function renderMenu() {
    container.innerHTML = '';

    if (menuData.length === 0) {
      container.innerHTML = '<div class="col-span-3 text-center text-gray-500">Belum ada menu.</div>';
      return;
    }

    menuData.forEach((menu, index) => {
      const isAvailable = menu.tersedia;
      const card = document.createElement('div');
      card.className = 'bg-white shadow rounded overflow-hidden relative group cursor-pointer';

      card.innerHTML = card.innerHTML = `
      <img src="${menu.image}" alt="${menu.name}"
        class="w-full h-65 object-cover transition-transform duration-300 group-hover:scale-105 ${!isAvailable ? 'grayscale opacity-70' : ''}" />
      ${!isAvailable ? `
        <div class="absolute inset-0 bg-black bg-opacity-60 flex items-center justify-center">
          <span class="text-white text-2xl font-bold">HABIS</span>
        </div>
      ` : `
        <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 flex flex-col justify-center items-center transition duration-300 space-y-2">
          <span class="text-white text-xl font-bold">${menu.name}</span>
                <!-- Button -->
      <button class="pesan-btn bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600"
  data-index="${index}">
  Pesan
</button>

        </div>
      `}
    `;
      card.addEventListener('click', (e) => {
        // Jangan buka modal detail jika yang diklik adalah tombol pesan
        if (!e.target.classList.contains('pesan-btn')) {
          openModal(index);
        }
      });
      container.appendChild(card);
    });
  }

  container.addEventListener('click', function (e) {
    if (e.target.classList.contains('pesan-btn')) {
      e.stopPropagation();
      const index = e.target.dataset.index;
      handlePesan(parseInt(index));
    }
  });

  function handlePesan(index) {
    fetch('./script/check_session.php')
      .then(res => res.json())
      .then(data => {
        if (!data.loggedIn) {
          alert("Anda harus login terlebih dahulu.");
          window.location.href = "./login_register/login.php";
        } else {
          selectedMenuIndex = index;
          const menu = menuData[index];
          document.getElementById('pesan-menu-nama').textContent = menu.name;
          document.getElementById('pesan-jumlah').value = 1;
          const modal = document.getElementById('pesan-modal');
          modal.classList.remove('hidden');
          modal.classList.add('flex');
        }
      });
  }

  document.getElementById('pesan-close').addEventListener('click', () => {
    const modal = document.getElementById('pesan-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
  });

  // Menutup modal ketika mengklik di luar modal
  document.getElementById('pesan-modal').addEventListener('click', (e) => {
    if (e.target.id === 'pesan-modal') {
      const modal = document.getElementById('pesan-modal');
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    }
  });

  document.getElementById('pesan-kirim').addEventListener('click', () => {
    const jumlah = parseInt(document.getElementById('pesan-jumlah').value);
    const menu = menuData[selectedMenuIndex];


    fetch('./admin/order/api/order_menu.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ menu_id: menu.id, quantity: jumlah })
    })
      .then(res => res.json())
      .then(data => {
        alert(data.message);
        const modal = document.getElementById('pesan-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
      });
  });

  const modal = document.getElementById('menu-modal');
  const modalContent = document.getElementById('modal-content');
  const modalImage = document.getElementById('modal-image');
  const modalName = document.getElementById('modal-name');
  const modalClose = document.getElementById('modal-close');
  const modalPrev = document.getElementById('modal-prev');
  const modalNext = document.getElementById('modal-next');

  function openModal(index) {
    currentIndex = index;
    const menu = menuData[currentIndex];
    modalImage.src = menu.image;
    modalName.textContent = menu.name;

    modal.classList.remove('hidden');
    setTimeout(() => {
      modalContent.classList.remove('scale-95', 'opacity-0');
      modalContent.classList.add('scale-100', 'opacity-100');
    }, 10);
  }

  function closeModal() {
    modalContent.classList.remove('scale-100', 'opacity-100');
    modalContent.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
      modal.classList.add('hidden');
    }, 300);
  }

  function showPrev() {
    currentIndex = (currentIndex - 1 + menuData.length) % menuData.length;
    openModal(currentIndex);
  }

  function showNext() {
    currentIndex = (currentIndex + 1) % menuData.length;
    openModal(currentIndex);
  }

  modalClose.addEventListener('click', closeModal);
  modalPrev.addEventListener('click', showPrev);
  modalNext.addEventListener('click', showNext);
  modal.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
  });
});

// Real-time check status tiap 10 detik
setInterval(() => {
  fetch('./admin/order/api/check_order_status.php')
    .then(res => res.json())
    .then(data => {
      const badge = document.getElementById('orderBadge');
      // Tampilkan badge hanya jika ada active order
      // dan status bukan 'selesai' atau 'ditolak'
      if (
        data.hasActiveOrder &&
        data.status !== 'selesai' &&
        data.status !== 'ditolak'
      ) {
        badge.classList.remove('hidden');
      } else {
        badge.classList.add('hidden');
      }
    })
    .catch(err => {
      console.error('Error checking order status:', err);
    });
}, 10000);

document.getElementById('closeOrderModal').addEventListener('click', () => {
  document.getElementById('orderModal').classList.add('hidden');
});

// Tutup modal manual
document.getElementById('closeOrderModal').addEventListener('click', () => {
  document.getElementById('orderModal').classList.add('hidden');
});

const detailsModal = document.getElementById('orderDetailsModal');
const tabAktifBtn = document.getElementById('tabAktif');
const tabSelesaiBtn = document.getElementById('tabSelesai');
const listAktif = document.getElementById('orderListAktif');
const listSelesai = document.getElementById('orderListSelesai');
const closeDetailsBtn = document.getElementById('closeOrderDetails');

function switchToAktif() {
  tabAktifBtn.classList.add('border-black', 'text-black');
  tabAktifBtn.classList.remove('text-gray-500');
  tabSelesaiBtn.classList.remove('border-black', 'text-black');
  tabSelesaiBtn.classList.add('text-gray-500');
  listAktif.classList.remove('hidden');
  listSelesai.classList.add('hidden');
}

function switchToSelesai() {
  tabSelesaiBtn.classList.add('border-black', 'text-black');
  tabSelesaiBtn.classList.remove('text-gray-500');
  tabAktifBtn.classList.remove('border-black', 'text-black');
  tabAktifBtn.classList.add('text-gray-500');
  listAktif.classList.add('hidden');
  listSelesai.classList.remove('hidden');
}

tabAktifBtn.addEventListener('click', switchToAktif);
tabSelesaiBtn.addEventListener('click', switchToSelesai);

// Saat klik "PESANAN SAYA"
document.getElementById('orderStatusBtn').addEventListener('click', e => {
  e.preventDefault();
  fetch('./admin/order/api/get_user_orders.php')
    .then(res => res.json())
    .then(data => {
      // Reset lists
      listAktif.innerHTML = '';
      listSelesai.innerHTML = '';

      if (data.items.length === 0) {
        listAktif.innerHTML = '<li class="text-center text-gray-500">Belum ada pesanan.</li>';
      } else {
        data.items.forEach(item => {
          const li = document.createElement('li');
          li.className = 'border-b pb-3';
          
          // Tampilkan gambar jika tersedia
          const imageHtml = item.image ? 
            `<img src="${item.image}" alt="${item.name}" class="w-16 h-16 object-cover rounded-lg mr-3">` : 
            `<div class="w-16 h-16 bg-gray-200 rounded-lg mr-3 flex items-center justify-center">
              <i class="fas fa-utensils text-gray-400"></i>
             </div>`;
          
          li.innerHTML = `
            <div class="flex items-center">
              ${imageHtml}
              <div class="flex-1">
                <div class="flex justify-between items-start">
                  <span class="font-medium text-gray-800">${item.name}</span>
                  <span class="text-sm bg-gray-100 px-2 py-1 rounded">x${item.quantity}</span>
                </div>
                <div class="text-sm text-gray-600 mt-1">
                  Status: 
                  <span class="font-medium ${
                    item.status === 'selesai' ? 'text-green-600' : 
                    item.status === 'ditolak' ? 'text-red-600' : 
                    'text-blue-600'
                  }">${item.status}</span>
                </div>
              </div>
            </div>
          `;
          
          // Pilih tab berdasarkan status
          if (item.status === 'selesai') {
            listSelesai.appendChild(li);
          } else {
            listAktif.appendChild(li);
          }
        });
      }

      // Default tampil tab "Aktif"
      switchToAktif();
      detailsModal.classList.remove('hidden');
    });
});

// Tutup modal
closeDetailsBtn.addEventListener('click', () => {
  detailsModal.classList.add('hidden');
});
