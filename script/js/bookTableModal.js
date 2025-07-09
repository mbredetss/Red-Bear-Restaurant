// Modal Book A Table
const bookTableBtn = document.getElementById('bookTable');
const bookTableModal = document.getElementById('bookTableModal');
const closeBookTableModal = document.getElementById('closeBookTableModal');
bookTableBtn.addEventListener('click', function (e) {
    e.preventDefault();
    bookTableModal.classList.remove('hidden');
    bookTableModal.classList.add('flex');

    // Reset scroll position dan setup scrolling
    const contentArea = bookTableModal.querySelector('.overflow-y-auto');
    if (contentArea) {
        contentArea.scrollTop = 0;

        // Tambahkan smooth scrolling untuk PC
        if (window.innerWidth > 640) {
            contentArea.style.scrollBehavior = 'smooth';
            contentArea.style.overflowY = 'auto';
            contentArea.style.maxHeight = '50vh';

            // Tambahkan custom scrollbar untuk PC
            contentArea.style.setProperty('--scrollbar-width', '12px');
            contentArea.style.setProperty('--scrollbar-track-color', '#f1f1f1');
            contentArea.style.setProperty('--scrollbar-thumb-color', '#c1c1c1');

            // Tambahkan CSS untuk custom scrollbar
            if (!document.getElementById('customScrollbarStyle')) {
                const scrollbarStyle = document.createElement('style');
                scrollbarStyle.id = 'customScrollbarStyle';
                scrollbarStyle.textContent = `
      @media (min-width: 641px) {
        #bookTableModal .overflow-y-auto::-webkit-scrollbar {
          width: 12px;
        }
        #bookTableModal .overflow-y-auto::-webkit-scrollbar-track {
          background: #f1f1f1;
          border-radius: 6px;
          margin: 4px 0;
        }
        #bookTableModal .overflow-y-auto::-webkit-scrollbar-thumb {
          background: linear-gradient(180deg, #c1c1c1 0%, #a8a8a8 100%);
          border-radius: 6px;
          border: 2px solid #f1f1f1;
        }
        #bookTableModal .overflow-y-auto::-webkit-scrollbar-thumb:hover {
          background: linear-gradient(180deg, #a8a8a8 0%, #909090 100%);
        }
        #bookTableModal .overflow-y-auto::-webkit-scrollbar-corner {
          background: #f1f1f1;
        }
      }
    `;
                document.head.appendChild(scrollbarStyle);
            }

            // Tambahkan custom scrollbar styles
            contentArea.style.setProperty('--scrollbar-width', '12px');
            contentArea.style.setProperty('--scrollbar-track-color', '#f1f1f1');
            contentArea.style.setProperty('--scrollbar-thumb-color', '#c1c1c1');

            // Tambahkan CSS untuk custom scrollbar
            const scrollbarStyle = document.createElement('style');
            scrollbarStyle.textContent = `
    #bookTableModal .overflow-y-auto::-webkit-scrollbar {
      width: 12px;
    }
    #bookTableModal .overflow-y-auto::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 6px;
      margin: 4px 0;
    }
    #bookTableModal .overflow-y-auto::-webkit-scrollbar-thumb {
      background: linear-gradient(180deg, #c1c1c1 0%, #a8a8a8 100%);
      border-radius: 6px;
      border: 2px solid #f1f1f1;
    }
    #bookTableModal .overflow-y-auto::-webkit-scrollbar-thumb:hover {
      background: linear-gradient(180deg, #a8a8a8 0%, #909090 100%);
    }
  `;
            document.head.appendChild(scrollbarStyle);
        }
    }

    updateTableStatus(); // Update status meja saat modal dibuka
    selectedTable = null;
});
closeBookTableModal.addEventListener('click', function () {
    bookTableModal.classList.add('hidden');
    bookTableModal.classList.remove('flex');
});
// Tutup modal jika klik di luar konten
bookTableModal.addEventListener('click', function (e) {
    if (e.target === bookTableModal) {
        bookTableModal.classList.add('hidden');
        bookTableModal.classList.remove('flex');
    }
});

// Set min date hari ini untuk input tanggal (akan diupdate oleh date picker)
const today = new Date();
const yyyy = today.getFullYear();
const mm = String(today.getMonth() + 1).padStart(2, '0');
const dd = String(today.getDate()).padStart(2, '0');
const todayString = `${yyyy}-${mm}-${dd}`;
// Fungsi untuk mengisi opsi waktu (akan digunakan oleh time picker)
function fillTimeOptions() {
    // Fungsi ini sekarang dihandle oleh modal time picker
    if (typeof generateTimeOptions === 'function') {
        generateTimeOptions();
    }
}

// Update opsi waktu saat tanggal berubah
bookingDate.addEventListener('change', function () {
    fillTimeOptions();
    updateTableStatus();
});

// Update status meja saat waktu berubah
bookingTime.addEventListener('change', updateTableStatus);

// --- Integrasi Booking Table ---
const tableIcons = document.querySelectorAll('#tableIcons .table-icon');
let selectedTable = null;

// Fungsi update status meja dari API real-time
function updateTableStatus() {
    const date = bookingDate.value;
    const time = bookingTime.value;
    const url = time ? `./admin/tables/api/get_all_table_statuses.php?date=${date}&time=${time}` : `./admin/tables/api/get_all_table_statuses.php?date=${date}`;

    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const tableIconsContainer = document.getElementById('tableIcons');
                tableIconsContainer.innerHTML = '';

                data.tables.forEach((table) => {
                    const isAvailable = table.status === 'available';
                    const tableBtn = document.createElement('button');
                    tableBtn.className = `table-icon rounded-full w-14 h-14 flex flex-col items-center justify-center transition-all duration-200 ${isAvailable
                        ? 'bg-green-100 border-2 border-green-500 hover:bg-green-200 focus:ring-2 focus:ring-green-500 cursor-pointer'
                        : 'bg-gray-300 border-2 border-gray-400 cursor-not-allowed opacity-60'
                        }`;
                    tableBtn.setAttribute('data-table', table.id);
                    tableBtn.setAttribute('data-next-available', table.next_available_time || '');
                    tableBtn.disabled = !isAvailable;

                    tableBtn.innerHTML = `
            <span class="text-2xl">${isAvailable ? '🍽️' : '🔒'}</span>
            <span class="text-xs font-bold mt-1">Meja ${table.table_number}</span>
          `;

                    tableBtn.addEventListener('click', function () {
                        if (this.disabled) {
                            return;
                        }
                        document.querySelectorAll('.table-icon').forEach(btn => btn.classList.remove('ring-4', 'ring-red-500'));
                        this.classList.add('ring-4', 'ring-red-500');
                        selectedTable = this.getAttribute('data-table');
                    });

                    tableIconsContainer.appendChild(tableBtn);
                });
            }
        })
        .catch(error => {
            console.error('Gagal memuat status meja:', error);
        });
}

// Submit booking
document.getElementById('submitBookTable').addEventListener('click', function () {
    const guestCount = document.getElementById('guestCount').value;
    const date = bookingDate.value;
    const time = bookingTime.value;
    if (!selectedTable) {
        alert('Silakan pilih meja yang tersedia.');
        return;
    }
    if (!date || !time) {
        alert('Tanggal dan waktu harus diisi.');
        return;
    }
    // Kirim booking
    const formData = new FormData();
    formData.append('table_id', selectedTable);
    formData.append('guest_count', guestCount);
    formData.append('date', date);
    formData.append('time', time);
    // Jika ada user_id, bisa ditambahkan di sini
    fetch('admin/book_table/api/book_table.php', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                bookTableModal.classList.add('hidden');
                bookTableModal.classList.remove('flex');
                // Update saldo user jika ada
                if (data.saldo_baru !== undefined) {
                    document.getElementById('userSaldo').textContent = 'Rp' + data.saldo_baru.toLocaleString('id-ID');
                    // Update saldo di navbar juga jika ada
                    const navbarSaldo = document.querySelector('.text-green-600');
                    if (navbarSaldo) {
                        navbarSaldo.textContent = 'Rp' + data.saldo_baru.toLocaleString('id-ID');
                    }
                }
                // Tampilkan kode meja
                if (data.table_code) {
                    showTableCode(data.table_code, date, time);
                }
                // Aktifkan pemesanan makanan
                window.hasBookedTable = true;
                // Refresh menu untuk menampilkan tombol plus
                if (typeof renderMenu === 'function') {
                    renderMenu();
                }
            } else {
                if (data.message && data.message.toLowerCase().includes('login')) {
                    window.location.href = 'login_register/login.php';
                } else {
                    updateTableStatus();
                }
            }
        });
});

// Fungsi untuk menampilkan kode meja
function showTableCode(tableCode, date, time) {
    const tableCodeInfo = document.getElementById('tableCodeInfo');
    const userTableCode = document.getElementById('userTableCode');
    const tableCodeDetails = document.getElementById('tableCodeDetails');

    if (tableCodeInfo && userTableCode && tableCodeDetails) {
        userTableCode.textContent = tableCode;
        tableCodeDetails.textContent = `Tanggal: ${date} | Waktu: ${time}`;
        tableCodeInfo.classList.remove('hidden');
    }
}

// Fungsi untuk memuat kode meja user yang sudah ada
function loadUserTableCode() {
    fetch('admin/book_table/api/get_user_table_code.php')
        .then(res => res.json())
        .then(data => {
            if (data.success && data.bookings.length > 0) {
                const booking = data.bookings[0]; // Ambil booking pertama
                showTableCode(booking.table_code, booking.booking_date, booking.booking_time);
                // Aktifkan pemesanan makanan
                window.hasBookedTable = true;
                // Refresh menu untuk menampilkan tombol plus
                if (typeof renderMenu === 'function') {
                    renderMenu();
                }
            }
        })
        .catch(error => console.error('Gagal memuat kode meja:', error));
}

// Load kode meja saat halaman dimuat
loadUserTableCode();

// Update status meja saat modal dibuka
bookTableBtn.addEventListener('click', function () {
    updateTableStatus();
    tableIcons.forEach(b => b.classList.remove('ring-4', 'ring-red-500'));
    selectedTable = null;
});

// Tambahkan CSS untuk memastikan modal responsive
const bookTableModalStyle = document.createElement('style');
bookTableModalStyle.textContent = `
  #bookTableModal {
    backdrop-filter: blur(4px);
  }
  
  #bookTableModal > div {
    animation: modalSlideIn 0.3s ease-out forwards;
  }
  
  @keyframes modalSlideIn {
    from {
      opacity: 0;
      transform: scale(0.95) translateY(-10px);
    }
    to {
      opacity: 1;
      transform: scale(1) translateY(0);
    }
  }
  
  /* Memastikan footer selalu terlihat */
  #bookTableModal .flex-shrink-0 {
    position: sticky;
    bottom: 0;
    background: white;
    z-index: 10;
  }
  
  /* Smooth scrolling untuk content area */
  #bookTableModal .overflow-y-auto {
    scroll-behavior: smooth;
  }
  
  /* Desktop scroll improvements */
  @media (min-width: 641px) {
    #bookTableModal .overflow-y-auto {
      max-height: 50vh;
      overflow-y: auto;
      overflow-x: hidden;
      scroll-behavior: smooth;
      scrollbar-width: thin;
      scrollbar-color: #c1c1c1 #f1f1f1;
    }
    
    #bookTableModal .overflow-y-auto::-webkit-scrollbar {
      width: 12px;
    }
    
    #bookTableModal .overflow-y-auto::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 6px;
      margin: 4px 0;
      box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.1);
    }
    
    #bookTableModal .overflow-y-auto::-webkit-scrollbar-thumb {
      background: linear-gradient(180deg, #c1c1c1 0%, #a8a8a8 100%);
      border-radius: 6px;
      border: 2px solid #f1f1f1;
      transition: background 0.2s ease;
    }
    
    #bookTableModal .overflow-y-auto::-webkit-scrollbar-thumb:hover {
      background: linear-gradient(180deg, #a8a8a8 0%, #909090 100%);
      box-shadow: 0 0 8px rgba(0, 0, 0, 0.2);
    }
    
    #bookTableModal .overflow-y-auto::-webkit-scrollbar-corner {
      background: #f1f1f1;
    }
    
    /* Enhanced scrollbar untuk Firefox */
    #bookTableModal .overflow-y-auto {
      scrollbar-width: thin;
      scrollbar-color: #c1c1c1 #f1f1f1;
    }
    
    /* Smooth scroll animation */
    #bookTableModal .overflow-y-auto {
      scroll-behavior: smooth;
    }
  }
  
  /* Mobile scroll improvements */
  @media (max-width: 640px) {
    #bookTableModal .overflow-y-auto {
      max-height: 40vh;
    }
    
    #bookTableModal .overflow-y-auto::-webkit-scrollbar {
      width: 6px;
    }
  }
`;
document.head.appendChild(bookTableModalStyle);

// Event listener untuk window resize
window.addEventListener('resize', function () {
    const contentArea = bookTableModal.querySelector('.overflow-y-auto');
    if (contentArea && !bookTableModal.classList.contains('hidden')) {
        if (window.innerWidth > 640) {
            contentArea.style.maxHeight = '50vh';
            contentArea.style.scrollBehavior = 'smooth';
        } else {
            contentArea.style.maxHeight = '40vh';
        }
    }
}); 