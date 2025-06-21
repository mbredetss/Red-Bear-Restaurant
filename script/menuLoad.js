document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('menu-container');
  if (!container) return; // Exit if no menu container on this page

  let menuData = [];
  let currentIndex = 0;
  let selectedMenuIndex = null;

  const pesanModal = document.getElementById('pesan-modal');
  const pesanModalContent = document.querySelector('#pesan-modal > div');
  const detailsModal = document.getElementById('orderDetailsModal');
  const detailsModalContent = document.querySelector('#orderDetailsModal > div');
  const menuDetailModal = document.getElementById('menu-modal');
  const menuDetailContent = document.getElementById('modal-content');

  const openModal = (modal, content) => {
    if (!modal || !content) return;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setTimeout(() => content.classList.remove('opacity-0', 'scale-95'), 10);
  };

  const closeModal = (modal, content) => {
    if (!modal || !content) return;
    content.classList.add('opacity-0', 'scale-95');
    setTimeout(() => modal.classList.add('hidden'), 300);
  };

  fetch('./admin/menu/api/menu_list.php')
    .then(res => res.json())
    .then(data => {
      menuData = data.sort((a, b) => (a.jenis === 'makanan' ? -1 : 1));
      renderMenu();
    })
    .catch(error => console.error('Gagal memuat menu:', error));

  const renderMenu = () => {
    container.innerHTML = menuData.length === 0 
      ? '<div class="col-span-full text-center text-gray-500">Belum ada menu.</div>'
      : menuData.map((menu, index) => `
        <div class="bg-white shadow-lg rounded-xl overflow-hidden relative group cursor-pointer transition-transform duration-300 hover:-translate-y-2" data-index="${index}">
          <div class="relative">
            <img src="${menu.image}" alt="${menu.name}" class="w-full h-64 object-cover transition-transform duration-300 group-hover:scale-105 ${!menu.tersedia ? 'grayscale' : ''}" />
            ${!menu.tersedia ? `
              <div class="absolute inset-0 bg-black bg-opacity-60 flex items-center justify-center">
                <span class="text-white text-2xl font-bold tracking-widest">HABIS</span>
              </div>` : `
              <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end items-center p-4">
                <button class="pesan-btn bg-red-600 text-white px-4 py-2 rounded-full font-bold text-sm hover:bg-red-700 transition-all transform translate-y-4 group-hover:translate-y-0" data-index="${index}">Pesan</button>
              </div>`
            }
          </div>
          <div class="p-4">
            <h3 class="font-bold text-lg text-gray-800 truncate">${menu.name}</h3>
          </div>
        </div>`).join('');
  };

  container.addEventListener('click', (e) => {
    const card = e.target.closest('.group');
    if (!card) return;
    const index = parseInt(card.dataset.index, 10);
    if (e.target.classList.contains('pesan-btn')) {
      e.stopPropagation();
      handlePesan(index);
    } else {
      openDetailModal(index);
    }
  });

  const handlePesan = (index) => {
    fetch('./script/check_session.php').then(res => res.json()).then(data => {
      if (!data.loggedIn) {
        alert("Anda harus login terlebih dahulu.");
        window.location.href = "./login_register/login.php";
      } else {
        selectedMenuIndex = index;
        const menu = menuData[index];
        document.getElementById('pesan-menu-nama').textContent = menu.name;
        document.getElementById('pesan-jumlah').value = 1;
        openModal(pesanModal, pesanModalContent);
      }
    });
  };

  document.getElementById('pesan-kirim').addEventListener('click', function() {
    const btn = this;
    const jumlah = parseInt(document.getElementById('pesan-jumlah').value, 10);
    if (jumlah > 0 && selectedMenuIndex !== null) {
      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memesan...';
      const menu = menuData[selectedMenuIndex];
      fetch('./admin/order/api/order_menu.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ menu_id: menu.id, quantity: jumlah })
      })
      .then(res => res.json())
      .then(data => {
        alert(data.message);
        if (data.success) {
          closeModal(pesanModal, pesanModalContent);
          checkOrderStatus();
        }
      })
      .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Place Order';
      });
    }
  });

  document.getElementById('pesan-close').addEventListener('click', () => closeModal(pesanModal, pesanModalContent));
  pesanModal.addEventListener('click', (e) => { if (e.target === pesanModal) closeModal(pesanModal, pesanModalContent); });

  const openDetailModal = (index) => {
    currentIndex = index;
    const menu = menuData[currentIndex];
    document.getElementById('modal-image').src = menu.image;
    document.getElementById('modal-name').textContent = menu.name;
    openModal(menuDetailModal, menuDetailContent);
  };

  document.getElementById('modal-close').addEventListener('click', () => closeModal(menuDetailModal, menuDetailContent));
  document.getElementById('modal-prev').addEventListener('click', () => openDetailModal((currentIndex - 1 + menuData.length) % menuData.length));
  document.getElementById('modal-next').addEventListener('click', () => openDetailModal((currentIndex + 1) % menuData.length));
  menuDetailModal.addEventListener('click', (e) => { if (e.target === menuDetailModal) closeModal(menuDetailModal, menuDetailContent); });

  const orderStatusBtn = document.getElementById('orderStatusBtn');
  orderStatusBtn.addEventListener('click', e => {
    e.preventDefault();
    fetch('./admin/order/api/get_user_orders.php').then(res => res.json()).then(data => {
      const listAktif = document.getElementById('orderListAktif');
      const listSelesai = document.getElementById('orderListSelesai');
      listAktif.innerHTML = '';
      listSelesai.innerHTML = '';
      let hasAktif = false, hasSelesai = false;
      if (data.items && data.items.length > 0) {
        data.items.forEach(item => {
          const li = `
            <li class="border-b last:border-b-0 py-3 flex items-center">
              <img src="${item.image || 'img/default.png'}" alt="${item.name}" class="w-16 h-16 object-cover rounded-lg mr-4">
              <div class="flex-1">
                <div class="flex justify-between items-start">
                  <span class="font-bold text-gray-800">${item.name}</span>
                  <span class="text-sm bg-gray-100 font-medium px-2 py-1 rounded-md">x${item.quantity}</span>
                </div>
                <div class="text-sm text-gray-600 mt-1">Status: <span class="font-semibold capitalize ${item.status === 'selesai' ? 'text-green-600' : 'text-blue-600'}">${item.status}</span></div>
              </div>
            </li>`;
          if (item.status === 'selesai' || item.status === 'ditolak') {
            listSelesai.innerHTML += li;
            hasSelesai = true;
          } else {
            listAktif.innerHTML += li;
            hasAktif = true;
          }
        });
      }
      if (!hasAktif) listAktif.innerHTML = '<li class="text-center text-gray-500 py-4">Tidak ada pesanan aktif.</li>';
      if (!hasSelesai) listSelesai.innerHTML = '<li class="text-center text-gray-500 py-4">Tidak ada riwayat pesanan.</li>';
      switchToAktif();
      openModal(detailsModal, detailsModalContent);
    });
  });

  const tabAktifBtn = document.getElementById('tabAktif');
  const tabSelesaiBtn = document.getElementById('tabSelesai');
  const switchToAktif = () => {
    tabAktifBtn.classList.add('border-red-600', 'text-red-600');
    tabSelesaiBtn.classList.remove('border-red-600', 'text-red-600');
    document.getElementById('orderListAktif').classList.remove('hidden');
    document.getElementById('orderListSelesai').classList.add('hidden');
  };
  tabAktifBtn.addEventListener('click', switchToAktif);
  tabSelesaiBtn.addEventListener('click', () => {
    tabSelesaiBtn.classList.add('border-red-600', 'text-red-600');
    tabAktifBtn.classList.remove('border-red-600', 'text-red-600');
    document.getElementById('orderListSelesai').classList.remove('hidden');
    document.getElementById('orderListAktif').classList.add('hidden');
  });
  document.getElementById('closeOrderDetails').addEventListener('click', () => closeModal(detailsModal, detailsModalContent));
  detailsModal.addEventListener('click', (e) => { if (e.target === detailsModal) closeModal(detailsModal, detailsModalContent); });

  const checkOrderStatus = () => {
    fetch('./admin/order/api/check_order_status.php').then(res => res.json()).then(data => {
      const badge = document.getElementById('orderBadge');
      if (badge) badge.style.display = (data.hasActiveOrder && data.status !== 'selesai' && data.status !== 'ditolak') ? 'flex' : 'none';
    }).catch(() => {});
  };
  checkOrderStatus();
  setInterval(checkOrderStatus, 15000);
});
