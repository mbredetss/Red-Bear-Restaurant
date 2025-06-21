document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('menu-container');
  let menuData = [];
  let currentIndex = 0;
  let selectedMenuIndex = null;

  // --- MODAL & CONTENT ELEMENTS ---
  const pesanModal = document.getElementById('pesan-modal');
  const pesanModalContent = document.querySelector('#pesan-modal > div'); // More robust selector
  const detailsModal = document.getElementById('orderDetailsModal');
  const detailsModalContent = document.querySelector('#orderDetailsModal > div');

  // Fetch menu data
  fetch('./admin/menu/api/menu_list.php')
      .then(res => res.json())
      .then(data => {
          menuData = data.sort((a, b) => {
              if (a.jenis === b.jenis) return 0;
              return a.jenis === 'makanan' ? -1 : 1;
          });
          renderMenu();
      })
      .catch(error => console.error('Gagal memuat menu:', error));

  function renderMenu() {
      if (!container) return;
      container.innerHTML = '';
      if (menuData.length === 0) {
          container.innerHTML = '<div class="col-span-full text-center text-gray-500">Belum ada menu.</div>';
          return;
      }

      menuData.forEach((menu, index) => {
          const isAvailable = menu.tersedia;
          const card = document.createElement('div');
          card.className = 'bg-white shadow-lg rounded-xl overflow-hidden relative group cursor-pointer transition-transform duration-300 hover:-translate-y-2';

          card.innerHTML = `
              <div class="relative">
                  <img src="${menu.image}" alt="${menu.name}" class="w-full h-64 object-cover transition-transform duration-300 group-hover:scale-105 ${!isAvailable ? 'grayscale' : ''}" />
                  ${!isAvailable ? `
                      <div class="absolute inset-0 bg-black bg-opacity-60 flex items-center justify-center">
                          <span class="text-white text-2xl font-bold tracking-widest">HABIS</span>
                      </div>
                  ` : `
                      <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end items-center p-4">
                          <button class="pesan-btn bg-red-600 text-white px-4 py-2 rounded-full font-bold text-sm hover:bg-red-700 transition-all transform translate-y-4 group-hover:translate-y-0" data-index="${index}">Pesan</button>
                      </div>
                  `}
              </div>
              <div class="p-4">
                  <h3 class="font-bold text-lg text-gray-800">${menu.name}</h3>
                  <p class="text-gray-600">Rp${parseInt(menu.price).toLocaleString('id-ID')}</p>
              </div>
          `;
          
          card.addEventListener('click', (e) => {
              if (!e.target.classList.contains('pesan-btn')) {
                  openDetailModal(index);
              }
          });
          container.appendChild(card);
      });
  }

  // --- OPEN/CLOSE MODAL GENERIC FUNCTIONS ---
  function openModal(modal, content) {
      if (!modal || !content) return;
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      setTimeout(() => {
          content.classList.remove('opacity-0', 'scale-95');
      }, 10); // Short delay for transition
  }

  function closeModal(modal, content) {
      if (!modal || !content) return;
      content.classList.add('opacity-0', 'scale-95');
      setTimeout(() => {
          modal.classList.add('hidden');
          modal.classList.remove('flex');
      }, 300); // Match transition duration
  }

  // --- PESAN MODAL LOGIC ---
  function handlePesan(index) {
      fetch('./script/check_session.php')
          .then(res => res.json())
          .then(data => {
              if (!data.loggedIn) {
                  alert("Anda harus login terlebih dahulu untuk memesan.");
                  window.location.href = "./login_register/login.php";
              } else {
                  selectedMenuIndex = index;
                  const menu = menuData[index];
                  document.getElementById('pesan-menu-nama').textContent = menu.name;
                  document.getElementById('pesan-jumlah').value = 1;
                  openModal(pesanModal, pesanModalContent);
              }
          });
  }
  
  document.getElementById('pesan-kirim').addEventListener('click', () => {
      const jumlah = parseInt(document.getElementById('pesan-jumlah').value);
      if (jumlah > 0 && selectedMenuIndex !== null) {
          const menu = menuData[selectedMenuIndex];
          fetch('./admin/order/api/order_menu.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ menu_id: menu.id, quantity: jumlah })
          })
          .then(res => res.json())
          .then(data => {
              alert(data.message);
              if(data.success) {
                 closeModal(pesanModal, pesanModalContent);
                 checkOrderStatus(); // Refresh badge
              }
          });
      }
  });

  document.getElementById('pesan-close').addEventListener('click', () => closeModal(pesanModal, pesanModalContent));
  pesanModal.addEventListener('click', (e) => {
      if (e.target === pesanModal) closeModal(pesanModal, pesanModalContent);
  });

  // --- MENU DETAIL MODAL LOGIC ---
  const menuDetailModal = document.getElementById('menu-modal');
  const menuDetailContent = document.getElementById('modal-content');
  const modalImage = document.getElementById('modal-image');
  const modalName = document.getElementById('modal-name');

  function openDetailModal(index) {
      currentIndex = index;
      const menu = menuData[currentIndex];
      modalImage.src = menu.image;
      modalName.textContent = menu.name;
      openModal(menuDetailModal, menuDetailContent);
  }
  
  function closeDetailModal() {
      closeModal(menuDetailModal, menuDetailContent);
  }

  function showPrev() {
      currentIndex = (currentIndex - 1 + menuData.length) % menuData.length;
      openDetailModal(currentIndex);
  }

  function showNext() {
      currentIndex = (currentIndex + 1) % menuData.length;
      openDetailModal(currentIndex);
  }
  
  document.getElementById('modal-close').addEventListener('click', closeDetailModal);
  document.getElementById('modal-prev').addEventListener('click', showPrev);
  document.getElementById('modal-next').addEventListener('click', showNext);
  menuDetailModal.addEventListener('click', (e) => {
      if (e.target === menuDetailModal) closeDetailModal();
  });

  // Event delegation for "Pesan" buttons on menu cards
  container.addEventListener('click', function (e) {
      if (e.target.classList.contains('pesan-btn')) {
          e.stopPropagation();
          const index = e.target.dataset.index;
          handlePesan(parseInt(index));
      }
  });
  
  // --- ORDER STATUS & DETAILS MODAL ---
  const orderStatusBtn = document.getElementById('orderStatusBtn');
  const closeDetailsBtn = document.getElementById('closeOrderDetails');
  const tabAktifBtn = document.getElementById('tabAktif');
  const tabSelesaiBtn = document.getElementById('tabSelesai');
  const listAktif = document.getElementById('orderListAktif');
  const listSelesai = document.getElementById('orderListSelesai');
  
  function switchToAktif() {
    tabAktifBtn.classList.add('border-red-600', 'text-red-600');
    tabAktifBtn.classList.remove('text-gray-500', 'border-transparent');
    tabSelesaiBtn.classList.add('text-gray-500', 'border-transparent');
    tabSelesaiBtn.classList.remove('border-red-600', 'text-red-600');
    listAktif.classList.remove('hidden');
    listSelesai.classList.add('hidden');
  }

  function switchToSelesai() {
    tabSelesaiBtn.classList.add('border-red-600', 'text-red-600');
    tabSelesaiBtn.classList.remove('text-gray-500', 'border-transparent');
    tabAktifBtn.classList.add('text-gray-500', 'border-transparent');
    tabAktifBtn.classList.remove('border-red-600', 'text-red-600');
    listSelesai.classList.remove('hidden');
    listAktif.classList.add('hidden');
  }

  tabAktifBtn.addEventListener('click', switchToAktif);
  tabSelesaiBtn.addEventListener('click', switchToSelesai);

  orderStatusBtn.addEventListener('click', e => {
      e.preventDefault();
      fetch('./admin/order/api/get_user_orders.php')
          .then(res => res.json())
          .then(data => {
              listAktif.innerHTML = '';
              listSelesai.innerHTML = '';

              if (!data.items || data.items.length === 0) {
                  listAktif.innerHTML = '<li class="text-center text-gray-500 py-4">Anda belum memiliki pesanan.</li>';
              } else {
                  data.items.forEach(item => {
                      const li = document.createElement('li');
                      li.className = 'border-b last:border-b-0 py-3';
                      const imageHtml = item.image ?
                          `<img src="${item.image}" alt="${item.name}" class="w-16 h-16 object-cover rounded-lg mr-4">` :
                          `<div class="w-16 h-16 bg-gray-200 rounded-lg mr-4 flex items-center justify-center"><i class="fas fa-utensils text-gray-400"></i></div>`;
                      
                      li.innerHTML = `
                          <div class="flex items-center">
                            ${imageHtml}
                            <div class="flex-1">
                              <div class="flex justify-between items-start">
                                <span class="font-bold text-gray-800">${item.name}</span>
                                <span class="text-sm bg-gray-100 text-gray-700 font-medium px-2 py-1 rounded-md">x${item.quantity}</span>
                              </div>
                              <div class="text-sm text-gray-600 mt-1">Status: <span class="font-semibold capitalize ${item.status === 'selesai' ? 'text-green-600' : 'text-blue-600'}">${item.status}</span></div>
                            </div>
                          </div>`;
                      
                      if (item.status === 'selesai' || item.status === 'ditolak') {
                          listSelesai.appendChild(li);
                      } else {
                          listAktif.appendChild(li);
                      }
                  });
                   if (listAktif.innerHTML === '') {
                      listAktif.innerHTML = '<li class="text-center text-gray-500 py-4">Tidak ada pesanan aktif.</li>';
                   }
                   if (listSelesai.innerHTML === '') {
                      listSelesai.innerHTML = '<li class="text-center text-gray-500 py-4">Tidak ada riwayat pesanan.</li>';
                   }
              }
              switchToAktif();
              openModal(detailsModal, detailsModalContent);
          })
          .catch(error => console.error('Gagal memuat detail pesanan:', error));
  });

  closeDetailsBtn.addEventListener('click', () => closeModal(detailsModal, detailsModalContent));
  detailsModal.addEventListener('click', (e) => {
      if (e.target === detailsModal) closeModal(detailsModal, detailsModalContent);
  });

  // --- BADGE & ORDER STATUS CHECK ---
  function checkOrderStatus() {
      fetch('./admin/order/api/check_order_status.php')
          .then(res => res.json())
          .then(data => {
              const badge = document.getElementById('orderBadge');
              if (!badge) return;
              
              if (data.hasActiveOrder && data.status !== 'selesai' && data.status !== 'ditolak') {
                  badge.style.display = 'flex';
              } else {
                  badge.style.display = 'none';
              }
          })
          .catch(err => {}); // Fail silently
  }
  
  // Initial and periodic checks
  checkOrderStatus();
  setInterval(checkOrderStatus, 10000);
});