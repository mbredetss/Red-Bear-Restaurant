// --- Modal Kalender ---
const datePickerBtn = document.getElementById('datePickerBtn');
const datePickerModal = document.getElementById('datePickerModal');
const closeDatePickerModal = document.getElementById('closeDatePickerModal');
const prevMonth = document.getElementById('prevMonth');
const nextMonth = document.getElementById('nextMonth');
const currentMonthYear = document.getElementById('currentMonthYear');
const calendarGrid = document.getElementById('calendarGrid');
const confirmDate = document.getElementById('confirmDate');
const selectedDateText = document.getElementById('selectedDateText');
const bookingDateInput = document.getElementById('bookingDate');

let currentDate = new Date();
let selectedDate = null;
let currentMonth = currentDate.getMonth();
let currentYear = currentDate.getFullYear();

// Format tanggal untuk display
function formatDateForDisplay(date) {
  const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  return `${days[date.getDay()]} ${months[date.getMonth()]} ${date.getDate()}`;
}

// Format tanggal untuk input
function formatDateForInput(date) {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}

// Generate kalender
function generateCalendar(month, year) {
  const firstDay = new Date(year, month, 1);
  const lastDay = new Date(year, month + 1, 0);
  const startDate = new Date(firstDay);
  startDate.setDate(startDate.getDate() - firstDay.getDay());

  const today = new Date();
  const todayString = formatDateForInput(today);

  calendarGrid.innerHTML = '';

  // Update header bulan dan tahun
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  currentMonthYear.textContent = `${months[month]} ${year}`;

  // Generate 42 cells (6 weeks x 7 days)
  for (let i = 0; i < 42; i++) {
    const date = new Date(startDate);
    date.setDate(startDate.getDate() + i);

    const dateString = formatDateForInput(date);
    const isCurrentMonth = date.getMonth() === month;
    const isToday = dateString === todayString;
    const isSelected = selectedDate && formatDateForInput(selectedDate) === dateString;
    const isPast = date < new Date(today.getFullYear(), today.getMonth(), today.getDate());

    const dayElement = document.createElement('div');
    dayElement.className = 'w-8 h-8 rounded-lg flex items-center justify-center text-sm font-medium cursor-pointer transition-colors';

    if (!isCurrentMonth || isPast) {
      dayElement.className += ' text-gray-500 cursor-not-allowed';
    } else if (isSelected) {
      dayElement.className += ' bg-black text-white';
    } else if (isToday) {
      dayElement.className += ' bg-red-600 text-white';
    } else {
      dayElement.className += ' text-white hover:bg-red-600 hover:text-white';
    }

    dayElement.textContent = date.getDate();
    dayElement.setAttribute('data-date', dateString);

    if (isCurrentMonth && !isPast) {
      dayElement.addEventListener('click', function () {
        selectDate(date);
      });
    }

    calendarGrid.appendChild(dayElement);
  }
}

// Pilih tanggal
function selectDate(date) {
  selectedDate = date;
  selectedDateText.textContent = formatDateForDisplay(date);
  bookingDateInput.value = formatDateForInput(date);
  generateCalendar(currentMonth, currentYear);
}

// Event listeners untuk modal kalender
datePickerBtn.addEventListener('click', function () {
  showModalWithAnimation(datePickerModal);
  generateCalendar(currentMonth, currentYear);
});

closeDatePickerModal.addEventListener('click', function () {
  hideModalWithAnimation(datePickerModal);
});

datePickerModal.addEventListener('click', function (e) {
  if (e.target === datePickerModal) {
    hideModalWithAnimation(datePickerModal);
  }
});

// Navigasi bulan
prevMonth.addEventListener('click', function () {
  currentMonth--;
  if (currentMonth < 0) {
    currentMonth = 11;
    currentYear--;
  }
  generateCalendar(currentMonth, currentYear);
});

nextMonth.addEventListener('click', function () {
  currentMonth++;
  if (currentMonth > 11) {
    currentMonth = 0;
    currentYear++;
  }
  generateCalendar(currentMonth, currentYear);
});

// Konfirmasi tanggal
confirmDate.addEventListener('click', function () {
  if (selectedDate) {
    hideModalWithAnimation(datePickerModal);
    // Update waktu yang tersedia berdasarkan tanggal yang dipilih
    if (typeof fillTimeOptions === 'function') {
      fillTimeOptions();
    }
    if (typeof updateTableStatus === 'function') {
      updateTableStatus();
    }
  } else {
    alert('Silakan pilih tanggal terlebih dahulu.');
  }
});

// Keyboard support untuk modal kalender
document.addEventListener('keydown', function (e) {
  if (datePickerModal.classList.contains('flex')) {
    if (e.key === 'Escape') {
      hideModalWithAnimation(datePickerModal);
    } else if (e.key === 'ArrowLeft') {
      e.preventDefault();
      prevMonth.click();
    } else if (e.key === 'ArrowRight') {
      e.preventDefault();
      nextMonth.click();
    } else if (e.key === 'Enter') {
      e.preventDefault();
      confirmDate.click();
    }
  }
});

// Tambahkan ripple effect ke tombol kalender
[prevMonth, nextMonth, confirmDate].forEach(button => {
  button.addEventListener('click', createRipple);
});

// Inisialisasi kalender dengan tanggal hari ini
selectDate(new Date());

// Tambahkan CSS untuk kalender
const calendarStyle = document.createElement('style');
calendarStyle.textContent = `
  #calendarGrid > div {
    position: relative;
    overflow: hidden;
  }
  
  #calendarGrid > div:hover {
    transform: scale(1.1);
  }
  
  #calendarGrid > div.selected {
    animation: pulse 0.3s ease-in-out;
  }
  
  @keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
  }
  
  #datePickerBtn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  }
  
  #prevMonth, #nextMonth {
    position: relative;
    overflow: hidden;
  }
`;
document.head.appendChild(calendarStyle);

// Update fungsi selectDate untuk menambahkan animasi
const originalSelectDate = selectDate;
selectDate = function (date) {
  originalSelectDate(date);

  // Tambahkan animasi pada tanggal yang dipilih
  const selectedElement = calendarGrid.querySelector(`[data-date="${formatDateForInput(date)}"]`);
  if (selectedElement) {
    selectedElement.classList.add('selected');
    setTimeout(() => {
      selectedElement.classList.remove('selected');
    }, 300);
  }
}; 