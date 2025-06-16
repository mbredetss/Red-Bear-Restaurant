document.addEventListener('DOMContentLoaded', () => {
    let menuData = [];
    let currentIndex = 0;
  
    const container = document.getElementById('menu-container');
  
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
  
        card.innerHTML = `
          <img src="${menu.image}" alt="${menu.name}"
            class="w-full h-65 object-cover transition-transform duration-300 group-hover:scale-105 ${!isAvailable ? 'grayscale opacity-70' : ''}" />
          ${!isAvailable ? `
            <div class="absolute inset-0 bg-black bg-opacity-60 flex items-center justify-center">
              <span class="text-white text-2xl font-bold">HABIS</span>
            </div>
          ` : `
            <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition duration-300">
              <span class="text-white text-xl font-bold">${menu.name}</span>
            </div>
          `}
        `;
  
        card.addEventListener('click', () => openModal(index));
        container.appendChild(card);
      });
    }
  
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
  