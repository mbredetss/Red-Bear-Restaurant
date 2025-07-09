let menuData = [];
let editingMenu = null;

function showMessage(msg) {
  const el = document.getElementById('message');
  el.textContent = msg;
  el.classList.remove('hidden', 'translate-y-16');
  setTimeout(() => {
    el.classList.add('translate-y-16');
    setTimeout(() => el.classList.add('hidden'), 300); // Wait for transition to finish
  }, 3000);
}

function loadMenu() {
  fetch('api/menu_list.php')
    .then(res => res.json())
    .then(data => {
      menuData = data;
      const menuList = document.getElementById('menu-list');
      menuList.innerHTML = '';
      if (data.length === 0) {
        menuList.innerHTML = '<div class="col-span-full text-center text-gray-500 py-10">Belum ada menu yang ditambahkan.</div>';
        return;
      }
      data.forEach(menu => {
        const menuId = menu.name.replace(/[^a-zA-Z0-9]/g, '-').toLowerCase(); // More robust ID
        menuList.innerHTML += `
          <div class="bg-white rounded-xl shadow-md overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
            <div class="relative">
              <img src="${menu.image}" alt="${menu.name}" class="w-full h-48 object-cover">
              <span class="absolute top-2 right-2 bg-blue-500 text-white text-xs font-semibold px-2 py-1 rounded-full capitalize">${menu.jenis}</span>
              <div class="absolute bottom-0 left-0 w-full h-20 bg-gradient-to-t from-black to-transparent opacity-80"></div>
              <h2 class="absolute bottom-3 left-4 text-xl font-bold text-white truncate w-11/12" title="${menu.name}">${menu.name}</h2>
            </div>
            <div class="p-4">
                <div class="flex items-center justify-between mb-4">
                    <span id="status-text-${menuId}" class="text-sm font-medium ${menu.tersedia ? 'text-green-600' : 'text-red-600'}">
                        ${menu.tersedia ? 'Tersedia' : 'Tidak Tersedia'}
                    </span>
                    <label for="toggle-${menuId}" class="flex items-center cursor-pointer">
                        <div class="relative">
                            <input type="checkbox" id="toggle-${menuId}" class="sr-only" onchange="toggleStatus(this, '${menu.name}')" ${menu.tersedia ? 'checked' : ''}>
                            <div id="toggle-bg-${menuId}" class="block ${menu.tersedia ? 'bg-green-500' : 'bg-red-500'} w-14 h-8 rounded-full transition-colors"></div>
                            <div id="toggle-dot-${menuId}" class="dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition-transform duration-300 ease-in-out transform ${menu.tersedia ? 'translate-x-full' : ''}"></div>
                        </div>
                    </label>
                </div>
                <div class="flex gap-2 mt-2">
                    <button onclick="openEditModal('${menu.name}')" class="flex-1 inline-flex items-center justify-center bg-yellow-500 hover:bg-yellow-600 transition-all text-white px-3 py-2 rounded-md font-semibold text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L14.732 3.732z"></path></svg>
                        Edit
                    </button>
                    <button onclick="deleteMenu('${menu.name}')" class="flex-1 inline-flex items-center justify-center bg-red-600 hover:bg-red-700 transition-all text-white px-3 py-2 rounded-md font-semibold text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Hapus
                    </button>
                </div>
            </div>
          </div>
        `;
      });
    });
}

document.getElementById('form-tambah-menu').onsubmit = function (e) {
  e.preventDefault();
  const formData = new FormData(this);
  fetch('api/menu_add.php', {
    method: 'POST',
    body: formData
  })
    .then(res => res.json())
    .then(res => {
      showMessage(res.message);
      loadMenu();
      this.reset();
    });
};

function deleteMenu(name) {
  if (!confirm('Yakin hapus menu?')) return;
  fetch('api/menu_delete.php?name=' + encodeURIComponent(name))
    .then(res => res.json())
    .then(res => {
      showMessage(res.message);
      loadMenu();
    });
}

// Modal logic
function openEditModal(name) {
  editingMenu = menuData.find(m => m.name === name);
  if (!editingMenu) return;

  const modal = document.getElementById('modal-edit');
  const modalContent = document.getElementById('modal-content');

  document.getElementById('modal-old-name').value = editingMenu.name;
  document.getElementById('modal-new-name').value = editingMenu.name;

  modal.classList.remove('hidden');
  setTimeout(() => {
    modal.classList.remove('opacity-0');
    modalContent.classList.remove('scale-95');
  }, 10); // Delay to ensure transition is applied
}

function closeModal() {
  const modal = document.getElementById('modal-edit');
  const modalContent = document.getElementById('modal-content');

  modal.classList.add('opacity-0');
  modalContent.classList.add('scale-95');

  setTimeout(() => {
    modal.classList.add('hidden');
    editingMenu = null;
  }, 300); // Match transition duration
}

document.getElementById('form-edit-menu').onsubmit = function (e) {
  e.preventDefault();
  const oldName = document.getElementById('modal-old-name').value;
  const newName = document.getElementById('modal-new-name').value;

  fetch('api/menu_edit.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `old_name=${encodeURIComponent(oldName)}&new_name=${encodeURIComponent(newName)}`
  })
    .then(res => res.json())
    .then(res => {
      showMessage(res.message);
      closeModal();
      loadMenu();
    });
};

function toggleStatus(element, name) {
    const menuId = name.replace(/[^a-zA-Z0-9]/g, '-').toLowerCase();
    const statusTextElement = document.getElementById(`status-text-${menuId}`);
    const toggleBg = document.getElementById(`toggle-bg-${menuId}`);
    const toggleDot = document.getElementById(`toggle-dot-${menuId}`);
    const isTersedia = element.checked;

    // Optimistic UI Update
    statusTextElement.textContent = isTersedia ? 'Tersedia' : 'Tidak Tersedia';
    statusTextElement.className = `text-sm font-medium ${isTersedia ? 'text-green-600' : 'text-red-600'}`;

    if (isTersedia) {
        toggleBg.classList.replace('bg-red-500', 'bg-green-500');
        toggleDot.classList.add('translate-x-full');
    } else {
        toggleBg.classList.replace('bg-green-500', 'bg-red-500');
        toggleDot.classList.remove('translate-x-full');
    }
    
    // Find the menu in local data and update it
    const menuToUpdate = menuData.find(m => m.name === name);
    if (menuToUpdate) {
        menuToUpdate.tersedia = isTersedia;
    }

    fetch('api/menu_toggle_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `name=${encodeURIComponent(name)}`
    })
    .then(res => res.json())
    .then(res => {
        showMessage(res.message);
        // No need to call loadMenu() anymore, UI is already updated.
        // If API call fails, you might want to revert the UI change here.
    })
    .catch(() => {
        // Revert UI on failure
        element.checked = !isTersedia;
        statusTextElement.textContent = !isTersedia ? 'Tersedia' : 'Tidak Tersedia';
        statusTextElement.className = `text-sm font-medium ${!isTersedia ? 'text-green-600' : 'text-red-600'}`;
        if (isTersedia) {
            toggleBg.classList.replace('bg-green-500', 'bg-red-500');
            toggleDot.classList.remove('translate-x-full');
        } else {
            toggleBg.classList.replace('bg-red-500', 'bg-green-500');
            toggleDot.classList.add('translate-x-full');
        }
        if (menuToUpdate) {
            menuToUpdate.tersedia = !isTersedia;
        }
        showMessage("Gagal mengubah status, silakan coba lagi.");
    });
}

loadMenu();