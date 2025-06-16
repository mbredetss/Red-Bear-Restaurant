let menuData = [];
let editingMenu = null;

function showMessage(msg) {
  const el = document.getElementById('message');
  el.textContent = msg;
  el.classList.remove('hidden');
  setTimeout(() => el.classList.add('hidden'), 3000);
}

function loadMenu() {
  fetch('api/menu_list.php')
    .then(res => res.json())
    .then(data => {
      menuData = data;
      const menuList = document.getElementById('menu-list');
      menuList.innerHTML = '';
      if (data.length === 0) {
        menuList.innerHTML = '<div class="col-span-3 text-center text-gray-500">Belum ada menu.</div>';
        return;
      }
      data.forEach(menu => {
        menuList.innerHTML += `
          <div class="bg-white rounded-xl shadow-lg overflow-hidden flex flex-col hover:shadow-2xl transition">
            <img src="${menu.image}" alt="Menu Image" class="w-full h-48 object-cover">
            <div class="p-4 flex-1 flex flex-col justify-between">
              <div>
                <h2 class="text-lg font-bold mb-1 truncate">${menu.name}</h2>
                <button onclick="toggleStatus('${menu.name}')" 
class="flex-1 ${menu.tersedia ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-500 hover:bg-gray-600'} transition text-white px-3 py-1 rounded font-semibold">
${menu.tersedia ? 'Tandai Sebagai Tidak Tersedia' : 'Tandai Sebagai Tersedia'}
</button>

              </div>
              <div class="flex gap-2 mt-4">
                <button onclick="openEditModal('${menu.name}')" class="flex-1 bg-yellow-500 hover:bg-yellow-600 transition text-white px-3 py-1 rounded font-semibold">Edit</button>
                <button onclick="deleteMenu('${menu.name}')" class="flex-1 bg-red-600 hover:bg-red-700 transition text-white px-3 py-1 rounded font-semibold">Hapus</button>
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
  document.getElementById('modal-old-name').value = editingMenu.name;
  document.getElementById('modal-new-name').value = editingMenu.name;
  document.getElementById('modal-edit').classList.remove('hidden');
}

function closeModal() {
  document.getElementById('modal-edit').classList.add('hidden');
  editingMenu = null;
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


function toggleStatus(name) {
  fetch('api/menu_toggle_status.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `name=${encodeURIComponent(name)}`
  })
    .then(res => res.json())
    .then(res => {
      showMessage(res.message);
      loadMenu();
    });
}

loadMenu();