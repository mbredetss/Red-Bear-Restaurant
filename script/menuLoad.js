document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('menu-container');
  if (!container) return; // Exit if no menu container on this page

  let menuData = [];
  let currentIndex = 0;
  let cart = []; // Cart untuk menyimpan item yang akan dipesan


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
      : menuData.map((menu, index) => {
        if (!menu.tersedia) {
          // Menu habis
          return `
          <div class="bg-white shadow-lg rounded-xl overflow-hidden relative group cursor-not-allowed opacity-80 select-none" data-index="${index}">
            <div class="relative">
              <img src="${menu.image}" alt="${menu.name}" class="w-full h-64 object-cover grayscale brightness-50" />
              <div class="absolute inset-0 bg-black bg-opacity-70 flex items-center justify-center">
                <span class="text-white text-2xl font-extrabold bg-black/70 px-6 py-3 rounded-full">HABIS</span>
              </div>
            </div>
          </div>`;
        } else {
          // Menu tersedia
          return `
          <div class="bg-white shadow-lg rounded-xl overflow-hidden relative group cursor-pointer transition-transform duration-300 hover:-translate-y-2" data-index="${index}">
            <div class="relative">
              <img src="${menu.image}" alt="${menu.name}" class="w-full h-64 object-cover transition-all duration-300 group-hover:opacity-40" />
              <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-60 transition-all duration-300 pointer-events-none"></div>
              <div class="absolute inset-0 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                <span class="text-white text-xl font-bold mb-2 bg-black/60 px-4 py-2 rounded-full">${menu.name}</span>
                ${window.hasScannedTable ? `
                <button class="add-to-cart-btn bg-white bg-opacity-80 hover:bg-red-600 hover:text-white text-red-600 rounded-full w-12 h-12 flex items-center justify-center text-2xl font-bold shadow transition-all duration-200 mt-2" data-index="${index}">
                  <i class="fas fa-plus"></i>
                </button>
                ` : ''}
              </div>
            </div>
          </div>`;
        }
      }).join('');
  };

  // --- MODAL INPUT JUMLAH & CATATAN ---
  let addToCartIndex = null;
  function showAddToCartModal(index) {
    addToCartIndex = index;
    const menu = menuData[index];
    let modal = document.getElementById('addToCartModal');
    if (!modal) {
      modal = document.createElement('div');
      modal.id = 'addToCartModal';
      modal.className = 'fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 px-4';
      modal.innerHTML = `
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm p-8 relative flex flex-col items-center">
          <button id="closeAddToCartModal" class="absolute top-3 right-4 text-gray-400 hover:text-gray-800 text-2xl font-bold">&times;</button>
          <h2 class="text-2xl font-bold text-center mb-6 text-gray-800">${menu.name}</h2>
          <img src="${menu.image}" alt="${menu.name}" class="w-32 h-32 object-cover rounded-lg mb-4">
          <div class="w-full mb-4">
            <label class="block text-gray-700 font-semibold mb-1">Jumlah</label>
            <input type="number" id="addToCartQty" min="1" value="1" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500">
          </div>
          <div class="w-full mb-6">
            <label class="block text-gray-700 font-semibold mb-1">Catatan (opsional)</label>
            <textarea id="addToCartNote" rows="2" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500" placeholder="Contoh: Tidak pakai lombok ya! lambung saya sakit"></textarea>
          </div>
          <button id="addToCartConfirm" class="w-full bg-red-600 text-white py-3 rounded-lg font-bold hover:bg-red-700 transition-colors shadow-lg hover:shadow-xl">
            <i class="fas fa-plus mr-2"></i>Tambahkan ke Keranjang
          </button>
        </div>
      `;
      document.body.appendChild(modal);
    } else {
      modal.querySelector('h2').textContent = menu.name;
      modal.querySelector('img').src = menu.image;
      modal.querySelector('img').alt = menu.name;
      modal.querySelector('#addToCartQty').value = 1;
      modal.querySelector('#addToCartNote').value = '';
      modal.classList.remove('hidden');
      modal.classList.add('flex');
    }
    modal.querySelector('#closeAddToCartModal').onclick = () => {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    };
    modal.querySelector('#addToCartConfirm').onclick = () => {
      const qty = parseInt(modal.querySelector('#addToCartQty').value, 10);
      const note = modal.querySelector('#addToCartNote').value;
      if (qty < 1) return;
      const menu = menuData[addToCartIndex];
      const existingItem = cart.find(item => item.menu_id === menu.id && item.note === note);
      if (existingItem) {
        existingItem.quantity += qty;
      } else {
        cart.push({
          menu_id: menu.id,
          menu_name: menu.name,
          image: menu.image,
          quantity: qty,
          note: note
        });
      }
      updateCartBadge();
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      showCartCapsule();
    };
  }

  container.addEventListener('click', (e) => {
    const card = e.target.closest('.group');
    if (!card) return;
    const index = parseInt(card.dataset.index, 10);
    if (e.target.classList.contains('add-to-cart-btn') || e.target.closest('.add-to-cart-btn')) {
      e.stopPropagation();
      showAddToCartModal(index);
      return;
    }
    openDetailModal(index);
  });

  // --- KAPSUL CART DI BAWAH ---
  function showCartCapsule() {
    let capsule = document.getElementById('cartCapsule');
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    if (totalItems === 0) {
      if (capsule) capsule.style.display = 'none';
      return;
    }
    if (!capsule) {
      capsule = document.createElement('div');
      capsule.id = 'cartCapsule';
      capsule.className = 'fixed left-1/2 -translate-x-1/2 bottom-6 z-50 flex items-center justify-between bg-red-600 rounded-full px-6 py-3 shadow-lg min-w-[180px] max-w-xs w-auto cursor-pointer transition-all';
      capsule.innerHTML = `
        <span id="cartCapsuleCount" class="text-white font-bold text-lg">${totalItems}</span>
        <span class="ml-4 flex items-center justify-center">
          <i class="fas fa-shopping-basket text-white text-2xl"></i>
        </span>
      `;
      document.body.appendChild(capsule);
    } else {
      capsule.style.display = 'flex';
      capsule.querySelector('#cartCapsuleCount').textContent = totalItems;
    }
    capsule.onclick = () => {
      capsule.style.display = 'none';
      renderCartModal();
    };
  }

  // --- MODAL CART ---
  function renderCartModal() {
    // Sembunyikan capsule saat modal cart terbuka
    const existingCapsule = document.getElementById('cartCapsule');
    if (existingCapsule) existingCapsule.style.display = 'none';

    let modal = document.getElementById('cart-modal');
    if (!modal) {
      modal = document.createElement('div');
      modal.id = 'cart-modal';
      modal.className = 'fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 px-4';
      document.body.appendChild(modal);
    }
    modal.innerHTML = `
      <div id="cart-modal-content" class="bg-white rounded-xl shadow-2xl w-full max-w-md max-h-[90vh] flex flex-col transform transition-all opacity-100 scale-100 relative">
        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
          <h2 class="text-2xl font-bold text-gray-800">Daftar Pesanan</h2>
          <button id="cart-close" class="text-gray-400 hover:text-gray-800 text-2xl">&times;</button>
        </div>
        <div class="flex-1 overflow-y-auto p-4" style="min-height:120px;max-height:calc(90vh - 160px);">
          ${cart.length === 0 ? '<div class="text-center text-gray-500 py-8">Keranjang kosong</div>' : cart.map((item, idx) => `
            <div class="bg-white rounded-xl shadow p-4 mb-4 flex flex-col gap-2">
              <div class="flex items-center justify-between">
                <div class="font-bold text-black text-lg">${item.menu_name}</div>
                <img src="${item.image}" alt="${item.menu_name}" class="w-16 h-16 object-cover rounded-lg ml-4">
              </div>
              <div class="flex items-center justify-between mt-2">
                <div class="flex items-center gap-2">
                  <button class="cart-qty-btn border border-green-500 text-green-600 rounded-full w-8 h-8 flex items-center justify-center text-lg font-bold" data-idx="${idx}" data-action="decrease"><i class="fas fa-minus"></i></button>
                  <span class="font-semibold text-lg">${item.quantity}</span>
                  <button class="cart-qty-btn border border-green-500 text-green-600 rounded-full w-8 h-8 flex items-center justify-center text-lg font-bold" data-idx="${idx}" data-action="increase"><i class="fas fa-plus"></i></button>
                </div>
                <button class="cart-edit-btn flex items-center gap-1 text-black ml-2" data-idx="${idx}">
                  <span class="bg-gray-200 rounded-full p-2"><i class="fas fa-pen"></i></span>
                  <span>Edit</span>
                </button>
              </div>
              ${item.note ? `<div class="text-xs text-gray-500 mt-1">Catatan: ${item.note}</div>` : ''}
            </div>
          `).join('')}
        </div>
        <div class="border-t border-gray-200 bg-white px-6 py-4 w-full" style="position:sticky;bottom:0;left:0;right:0;z-index:20;">
          <button id="checkout-btn" class="w-full bg-red-600 text-white py-3 rounded-lg font-bold hover:bg-red-700 transition-colors shadow-lg hover:shadow-xl">
            <i class="fas fa-check mr-2"></i>Pesan Sekarang!
          </button>
        </div>
      </div>
    `;
    modal.querySelector('#cart-close').onclick = () => {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      showCartCapsule();
    };
    // Qty control
    modal.querySelectorAll('.cart-qty-btn').forEach(btn => {
      btn.onclick = (e) => {
        const idx = parseInt(btn.dataset.idx, 10);
        const action = btn.dataset.action;
        if (action === 'increase') cart[idx].quantity++;
        if (action === 'decrease') {
          if (cart[idx].quantity > 1) {
            cart[idx].quantity--;
          } else {
            cart.splice(idx, 1);
          }
        }
        updateCartBadge();
        if (cart.length === 0) {
          // Tutup modal jika cart kosong
          modal.classList.add('hidden');
          modal.classList.remove('flex');
          showCartCapsule();
          return;
        }
        renderCartModal();
      };
    });
    // Edit note
    modal.querySelectorAll('.cart-edit-btn').forEach(btn => {
      btn.onclick = (e) => {
        const idx = parseInt(btn.dataset.idx, 10);
        showEditNoteModal(idx);
      };
    });
    // Checkout
    modal.querySelector('#checkout-btn').onclick = () => {
      if (cart.length === 0) {
        alert('Keranjang kosong!');
        return;
      }
      // Modal input nama pemesan & jumlah tamu
      let checkoutModal = document.getElementById('checkoutModal');
      if (!checkoutModal) {
        checkoutModal = document.createElement('div');
        checkoutModal.id = 'checkoutModal';
        checkoutModal.className = 'fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 px-4';
        checkoutModal.innerHTML = `
          <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm p-8 relative flex flex-col items-center">
            <button id="closeCheckoutModal" class="absolute top-3 right-4 text-gray-400 hover:text-gray-800 text-2xl font-bold">&times;</button>
            <h2 class="text-2xl font-bold text-center mb-6 text-gray-800">Konfirmasi Pesanan</h2>
            <div class="w-full mb-4">
              <label class="block text-gray-700 font-semibold mb-1">Nama Pemesan</label>
              <input type="text" id="checkout-nama" placeholder="Masukkan nama Anda" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500" />
            </div>
            <div class="w-full mb-6">
              <label class="block text-gray-700 font-semibold mb-1">Jumlah Tamu di Meja</label>
              <input type="number" id="checkout-tamu" min="1" value="1" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500" />
            </div>
            <button id="checkoutConfirmBtn" class="w-full bg-red-600 text-white py-3 rounded-lg font-bold hover:bg-red-700 transition-colors shadow-lg hover:shadow-xl">
              <i class="fas fa-check mr-2"></i>Pesan Sekarang!
            </button>
          </div>
        `;
        document.body.appendChild(checkoutModal);
      }
      checkoutModal.classList.remove('hidden');
      checkoutModal.classList.add('flex');
      checkoutModal.querySelector('#closeCheckoutModal').onclick = () => {
        checkoutModal.classList.add('hidden');
        checkoutModal.classList.remove('flex');
      };
      checkoutModal.querySelector('#checkoutConfirmBtn').onclick = async () => {
        const namaPemesan = checkoutModal.querySelector('#checkout-nama').value;
        const jumlahTamu = parseInt(checkoutModal.querySelector('#checkout-tamu').value, 10);
        if (!namaPemesan || !jumlahTamu) {
          alert('Nama pemesan dan jumlah tamu harus diisi.');
          return;
        }
        checkoutModal.querySelector('#checkoutConfirmBtn').disabled = true;
        checkoutModal.querySelector('#checkoutConfirmBtn').innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
        // Kirim semua item dalam cart
        const promises = cart.map(item =>
          fetch('./admin/order/api/order_menu.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              menu_id: item.menu_id,
              menu_name: item.menu_name,
              quantity: item.quantity,
              username: namaPemesan,
              guest_count: jumlahTamu,
              note: item.note
            })
          }).then(res => res.json())
        );
        const results = await Promise.all(promises);
        const success = results.every(result => result.success);
        if (success) {
          alert('Semua pesanan berhasil ditambahkan!');
          cart = [];
          updateCartBadge();
          document.getElementById('cart-modal').classList.add('hidden');
          document.getElementById('cart-modal').classList.remove('flex');
          checkoutModal.classList.add('hidden');
          checkoutModal.classList.remove('flex');
          showCartCapsule();
          // (opsional) refresh status pesanan
          if (typeof checkOrderStatus === 'function') checkOrderStatus();
        } else {
          alert('Beberapa pesanan gagal ditambahkan. Silakan coba lagi.');
        }
        checkoutModal.querySelector('#checkoutConfirmBtn').disabled = false;
        checkoutModal.querySelector('#checkoutConfirmBtn').innerHTML = '<i class="fas fa-check mr-2"></i>Pesan Sekarang!';
      };
    };
    modal.classList.remove('hidden');
    modal.classList.add('flex');
  }

  // --- MODAL EDIT CATATAN ---
  function showEditNoteModal(idx) {
    let modal = document.getElementById('editNoteModal');
    if (!modal) {
      modal = document.createElement('div');
      modal.id = 'editNoteModal';
      modal.className = 'fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 px-4';
      modal.innerHTML = `
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm p-8 relative flex flex-col items-center">
          <button id="closeEditNoteModal" class="absolute top-3 right-4 text-gray-400 hover:text-gray-800 text-2xl font-bold">&times;</button>
          <h2 class="text-2xl font-bold text-center mb-6 text-gray-800">Edit Catatan</h2>
          <textarea id="editNoteInput" rows="3" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500" placeholder="Catatan untuk pesanan ini..."></textarea>
          <button id="editNoteSave" class="w-full mt-6 bg-red-600 text-white py-3 rounded-lg font-bold hover:bg-red-700 transition-colors shadow-lg hover:shadow-xl">
            <i class="fas fa-save mr-2"></i>Simpan Catatan
          </button>
        </div>
      `;
      document.body.appendChild(modal);
    }
    modal.querySelector('#editNoteInput').value = cart[idx].note || '';
    modal.querySelector('#closeEditNoteModal').onclick = () => {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    };
    modal.querySelector('#editNoteSave').onclick = () => {
      cart[idx].note = modal.querySelector('#editNoteInput').value;
      updateCartBadge();
      renderCartModal();
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    };
    modal.classList.remove('hidden');
    modal.classList.add('flex');
  }

  function updateCartBadge() {
    showCartCapsule();
  }

  const renderCart = () => {
    const cartContainer = document.getElementById('cart-items');
    if (!cartContainer) return;

    if (cart.length === 0) {
      cartContainer.innerHTML = '<div class="text-center text-gray-500 py-8">Keranjang kosong</div>';
      return;
    }

    cartContainer.innerHTML = cart.map((item, index) => `
      <div class="flex items-center space-x-4 p-4 border-b border-gray-200">
        <img src="${item.image}" alt="${item.menu_name}" class="w-16 h-16 object-cover rounded-lg">
        <div class="flex-1">
          <h4 class="font-semibold text-gray-800">${item.menu_name}</h4>
          <div class="flex items-center space-x-2 mt-2">
            <button class="quantity-btn bg-gray-200 text-gray-700 w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-300" data-index="${index}" data-action="decrease">-</button>
            <span class="quantity-display font-semibold text-lg">${item.quantity}</span>
            <button class="quantity-btn bg-gray-200 text-gray-700 w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-300" data-index="${index}" data-action="increase">+</button>
          </div>
        </div>
        <button class="remove-item-btn text-red-500 hover:text-red-700" data-index="${index}">
          <i class="fas fa-trash"></i>
        </button>
      </div>
    `).join('');
  };

  // Event listeners untuk cart
  document.addEventListener('click', (e) => {
    if (e.target.classList.contains('quantity-btn')) {
      const index = parseInt(e.target.dataset.index);
      const action = e.target.dataset.action;

      if (action === 'increase') {
        cart[index].quantity += 1;
      } else if (action === 'decrease') {
        if (cart[index].quantity > 1) {
          cart[index].quantity -= 1;
        } else {
          cart.splice(index, 1);
        }
      }

      renderCart();
      updateCartBadge();
    }

    if (e.target.classList.contains('remove-item-btn')) {
      const index = parseInt(e.target.dataset.index);
      cart.splice(index, 1);
      renderCart();
      updateCartBadge();
    }
  });

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
          let statusText = '';
          let statusColor = '';
          if (item.status === 'memasak') {
            if (item.jenis === 'makanan') {
              statusText = 'sedang di masak';
              statusColor = 'text-yellow-500';
            } else if (item.jenis === 'minuman') {
              statusText = 'sedang dibuat';
              statusColor = 'text-yellow-500';
            } else {
              statusText = item.status;
              statusColor = 'text-yellow-500';
            }
          } else if (item.status === 'selesai') {
            statusText = 'selesai';
            statusColor = 'text-green-600';
          } else {
            statusText = item.status;
            statusColor = 'text-blue-600';
          }
          const li = `
            <li class="border-b last:border-b-0 py-3 flex items-center">
              <img src="${item.image || 'img/default.png'}" alt="${item.name}" class="w-16 h-16 object-cover rounded-lg mr-4">
              <div class="flex-1">
                <div class="flex justify-between items-start">
                  <span class="font-bold text-gray-800">${item.name}</span>
                  <span class="text-sm bg-gray-100 font-medium px-2 py-1 rounded-md">x${item.quantity}</span>
                </div>
                <div class="text-sm text-gray-600 mt-1"><span class="font-semibold capitalize ${statusColor}">${statusText}</span></div>
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
    }).catch(() => { });
  };
  checkOrderStatus();
  setInterval(checkOrderStatus, 15000);

  // --- Logika untuk Modal Status Meja Real-time ---
  const tableStatusBtn = document.getElementById('tableStatusBtn');
  const tableStatusModal = document.getElementById('tableStatusModal');
  const tableStatusModalContent = document.getElementById('tableStatusModal-content');
  const closeTableStatusModal = document.getElementById('closeTableStatusModal');
  const tableStatusContainer = document.getElementById('tableStatusContainer');

  if (tableStatusBtn) {
    function renderTableStatuses(tables) {
      if (!tableStatusContainer) return;
      tableStatusContainer.innerHTML = tables.map(table => {
        let bgColor = 'bg-green-500';
        let icon = '<i class="fas fa-check text-white"></i>';
        if (table.status !== 'available') {
          bgColor = 'bg-red-500';
          icon = '<i class="fas fa-times text-white"></i>';
        }
        return `
                  <div class="flex flex-col items-center">
                      <div class="w-16 h-16 rounded-full flex items-center justify-center ${bgColor} shadow-md text-2xl">
                          ${icon}
                      </div>
                      <span class="mt-2 font-semibold text-gray-700">Meja ${table.table_number}</span>
                  </div>
              `;
      }).join('');
    }

    function fetchTableStatuses() {
      fetch('./admin/tables/api/get_all_table_statuses.php')
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            renderTableStatuses(data.tables);
          }
        })
        .catch(error => console.error('Gagal memuat status meja:', error));
    }

    tableStatusBtn.addEventListener('click', (e) => {
      e.preventDefault();
      fetchTableStatuses();
      openModal(tableStatusModal, tableStatusModalContent);
    });

    closeTableStatusModal.addEventListener('click', () => closeModal(tableStatusModal, tableStatusModalContent));
    tableStatusModal.addEventListener('click', (e) => { if (e.target === tableStatusModal) closeModal(tableStatusModal, tableStatusModalContent); });

    setInterval(fetchTableStatuses, 15000);
  }

  // Initialize cart badge
  updateCartBadge();
});
