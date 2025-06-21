document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('menu-container');
  let menuData = [];
  let currentIndex = 0;
  let selectedMenuIndex = null;

  // --- MODAL & CONTENT ELEMENTS ---
  const pesanModal = document.getElementById('pesan-modal');
  const pesanModalContent = document.querySelector('#pesan-modal > div');
  const detailsModal = document.getElementById('orderDetailsModal');
  const detailsModalContent = document.querySelector('#orderDetailsModal > div');
  const menuDetailModal = document.getElementById('menu-modal');
  const menuDetailContent = document.getElementById('modal-content');

  if (!container) return; // Exit if no menu container on page

  // Fetch menu data
  fetch('./admin/menu/api/menu_list.php')
    .then(res => res.json())
    .then(data => {
      menuData = data.sort((a, b) => (a.jenis === 'makanan' ? -1 : 1));
      renderMenu();
    })
    .catch(error => console.error('Gagal memuat menu:', error));

  function renderMenu() {
    container.innerHTML = menuData.length === 0 
      ? '<div class="col-span-full text-center text-gray-500">Belum ada menu.</div>'
      : menuData.map((menu, index) => {
        const isAvailable = menu.tersedia;
        return `
        <div class="bg-white shadow-lg rounded-xl overflow-hidden relative group cursor-pointer transition-transform duration-300 hover:-translate-y-2" data-index="${index}">
          <div class="relative">
            <img src="${menu.image}" alt="${menu.name}" class="w-full h-64 object-cover transition-transform duration-300 group-hover:scale-105 ${!isAvailable ? 'grayscale' : ''}" />
            ${!isAvailable ? `
              <div class="absolute inset-0 bg-black bg-opacity-60 flex items-center justify-center">
                <span class="text-white text-2xl font-bold tracking-widest">HABIS</span>
              </div>` 
            : `
              <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end items-center p-4">
                <button class="pesan-btn bg-red-600 text-white px-4 py-2 rounded-full font-bold text-sm hover:bg-red-700 transition-all transform translate-y-4 group-hover:translate-y-0" data-index="${index}">Pesan</button>
              </div>`
            }
          </div>
          <div class="p-4">
            <h3 class="font-bold text-lg text-gray-800 truncate">${menu.name}</h3>
          </div>
        </div>`;
      }).join('');
  }

  // --- GENERIC MODAL FUNCTIONS ---
  function openModal(modal, content) {
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setTimeout(() => content.classList.remove('opacity-0', 'scale-95'), 10);
  }
  function closeModal(modal, content) {
    content.classList.add('opacity-0', 'scale-95');
    setTimeout(() => modal.classList.add('hidden'), 300);
  }

  // --- EVENT DELEGATION FOR MENU ---
  container.addEventListener('click', (e) => {
    const card = e.target.closest('.group');
    if (!card) return;
    
    const index = card.dataset.index;
    if (e.target.classList.contains('pesan-btn')) {
      e.stopPropagation();
      handlePesan(parseInt(index));
    } else {
      openDetailModal(parseInt(index));
    }
  });

  // --- PESAN MODAL ---
  function handlePesan(index) {
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
  }

  document.getElementById('pesan-kirim').addEventListener('click', function() {
    const btn = this;
    const jumlah = parseInt(document.getElementById('pesan-jumlah').value);
    if (jumlah > 0 && selectedMenuIndex !== null) {
      btn.disabled = true; // --- FIX: Disable button on click
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
        btn.disabled = false; // --- FIX: Re-enable button
        btn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Place Order';
      });
    }
  });
  document.getElementById('pesan-close').addEventListener('click', () => closeModal(pesanModal, pesanModalContent));
  pesanModal.addEventListener('click', (e) => { if (e.target === pesanModal) closeModal(pesanModal, pesanModalContent); });

  // --- MENU DETAIL MODAL ---
  function openDetailModal(index) {
    currentIndex = index;
    const menu = menuData[currentIndex];
    document.getElementById('modal-image').src = menu.image;
    document.getElementById('modal-name').textContent = menu.name;
    openModal(menuDetailModal, menuDetailContent);
  }
  document.getElementById('modal-close').addEventListener('click', () => closeModal(menuDetailModal, menuDetailContent));
  document.getElementById('modal-prev').addEventListener('click', () => openDetailModal((currentIndex - 1 + menuData.length) % menuData.length));
  document.getElementById('modal-next').addEventListener('click', () => openDetailModal((currentIndex + 1) % menuData.length));
  menuDetailModal.addEventListener('click', (e) => { if (e.target === menuDetailModal) closeModal(menuDetailModal, menuDetailContent); });

  // --- ORDER STATUS & DETAILS MODAL ---
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
  function switchToAktif() {
    tabAktifBtn.classList.add('border-red-600', 'text-red-600');
    tabSelesaiBtn.classList.remove('border-red-600', 'text-red-600');
    document.getElementById('orderListAktif').classList.remove('hidden');
    document.getElementById('orderListSelesai').classList.add('hidden');
  }
  tabAktifBtn.addEventListener('click', switchToAktif);
  tabSelesaiBtn.addEventListener('click', () => {
    tabSelesaiBtn.classList.add('border-red-600', 'text-red-600');
    tabAktifBtn.classList.remove('border-red-600', 'text-red-600');
    document.getElementById('orderListSelesai').classList.remove('hidden');
    document.getElementById('orderListAktif').classList.add('hidden');
  });
  document.getElementById('closeOrderDetails').addEventListener('click', () => closeModal(detailsModal, detailsModalContent));
  detailsModal.addEventListener('click', (e) => { if (e.target === detailsModal) closeModal(detailsModal, detailsModalContent); });

  // --- BADGE STATUS CHECK ---
  function checkOrderStatus() {
    fetch('./admin/order/api/check_order_status.php').then(res => res.json()).then(data => {
      const badge = document.getElementById('orderBadge');
      if (badge) badge.style.display = (data.hasActiveOrder && data.status !== 'selesai' && data.status !== 'ditolak') ? 'flex' : 'none';
    });
  }
  checkOrderStatus();
  setInterval(checkOrderStatus, 10000);

  // --- NAVBAR SCROLL EFFECT ---
  const navbar = document.getElementById('navbar');
  if(navbar) {
    window.addEventListener('scroll', () => {
      if (window.scrollY > 50) {
        navbar.classList.add('nav-scrolled');
      } else {
        navbar.classList.remove('nav-scrolled');
      }
    });
  }

  // --- HERO CAROUSEL ---
  const slideElement = document.querySelector('.carousel-slide');
  if (slideElement) {
    const images = ['img/image1.png', 'img/image2.png', 'img/image3.png'];
    let currentSlide = 0;
    const indicators = document.querySelectorAll('.carousel-indicator');
    const bg = document.querySelector('.carousel-bg');

    const updateSlide = () => {
      if(!bg) return;
      bg.style.backgroundImage = `url('${images[currentSlide]}')`;
      bg.style.opacity = 1;

      setTimeout(() => {
        slideElement.style.backgroundImage = `url('${images[currentSlide]}')`;
        bg.style.opacity = 0;
      }, 700);

      indicators.forEach((indicator, index) => {
        indicator.classList.toggle('active', index === currentSlide);
        indicator.classList.toggle('bg-white', index === currentSlide);
        indicator.classList.toggle('bg-white/60', index !== currentSlide);
      });
    };

    const nextSlide = () => {
      currentSlide = (currentSlide + 1) % images.length;
      updateSlide();
    };
    
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn');

    if(prevBtn && nextBtn) {
      nextBtn.addEventListener('click', nextSlide);
      prevBtn.addEventListener('click', () => {
        currentSlide = (currentSlide - 1 + images.length) % images.length;
        updateSlide();
      });
    }
    
    indicators.forEach(indicator => {
      indicator.addEventListener('click', () => {
        if(indicator.dataset.slide) {
          currentSlide = parseInt(indicator.dataset.slide);
          updateSlide();
        }
      });
    });

    setInterval(nextSlide, 5000);
    updateSlide();
  }

  // --- DROPDOWN HANDLER ---
  const setupDropdown = (triggerId, menuId) => {
    const trigger = document.getElementById(triggerId);
    const menu = document.getElementById(menuId);

    if (trigger && menu) {
      trigger.addEventListener('click', (e) => {
        e.stopPropagation();
        // Close other open menus before toggling the current one
        document.querySelectorAll('.dropdown-menu').forEach(m => {
          if (m.id !== menuId) {
            m.classList.add('hidden');
          }
        });
        menu.classList.toggle('hidden');
      });
    }
  };

  setupDropdown('userAvatar', 'profileDropdown');
  setupDropdown('more-btn', 'more-menu');
  
  // Close all dropdowns when clicking outside
  window.addEventListener('click', (e) => {
    // A click anywhere on the window should hide all dropdowns,
    // unless the click was on a trigger. The trigger's own listener handles the logic.
    let isDropdownClick = e.target.closest('.dropdown-menu') !== null;
    let isTriggerClick = e.target.closest('#userAvatar') !== null || e.target.closest('#more-btn') !== null;

    if (!isDropdownClick && !isTriggerClick) {
      document.querySelectorAll('.dropdown-menu').forEach(menu => {
        menu.classList.add('hidden');
      });
    }
  });
});
