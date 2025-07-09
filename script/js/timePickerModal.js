// --- Modal Time Picker ---
const timePickerBtn = document.getElementById('timePickerBtn');
const timePickerModal = document.getElementById('timePickerModal');
const closeTimePickerModal = document.getElementById('closeTimePickerModal');
const timeGrid = document.getElementById('timeGrid');
const confirmTime = document.getElementById('confirmTime');
const selectedTimeText = document.getElementById('selectedTimeText');
const bookingTimeInput = document.getElementById('bookingTime');

let selectedTime = null;

// Generate waktu dari 08:00 sampai 22:00 dengan interval 30 menit
function generateTimeOptions() {
  timeGrid.innerHTML = '';

  const startHour = 8;
  const endHour = 22;
  const interval = 30; // menit

  for (let hour = startHour; hour <= endHour; hour++) {
    for (let minute = 0; minute < 60; minute += interval) {
      if (hour === endHour && minute > 0) break; // Stop at 22:00

      const time = new Date();
      time.setHours(hour, minute, 0, 0);

      const timeString = time.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
      });

      const timeValue = time.toTimeString().slice(0, 5); // Format HH:MM

      // Cek apakah waktu sudah lewat untuk hari ini
      const now = new Date();
      const isToday = typeof selectedDate !== 'undefined' && selectedDate && formatDateForInput(selectedDate) === formatDateForInput(now);
      const isPast = isToday && time < now;

      const timeBtn = document.createElement('button');
      timeBtn.className = `py-3 px-4 rounded-lg border border-white/30 text-white font-medium transition-colors ${isPast
          ? 'bg-gray-600 cursor-not-allowed opacity-50'
          : 'bg-red-700 hover:bg-red-600 hover:border-white/50'
        }`;
      timeBtn.textContent = timeString;
      timeBtn.setAttribute('data-time', timeValue);
      timeBtn.disabled = isPast;

      if (!isPast) {
        timeBtn.addEventListener('click', function () {
          selectTime(timeString, timeValue);
        });
      }

      timeGrid.appendChild(timeBtn);
    }
  }
}

// Pilih waktu
function selectTime(timeString, timeValue) {
  selectedTime = timeValue;
  selectedTimeText.textContent = timeString;
  bookingTimeInput.value = timeValue;

  // Update tampilan tombol yang dipilih
  timeGrid.querySelectorAll('button').forEach(btn => {
    btn.classList.remove('bg-black', 'text-yellow-400');
    btn.classList.add('bg-red-700');
  });

  const selectedBtn = timeGrid.querySelector(`[data-time="${timeValue}"]`);
  if (selectedBtn) {
    selectedBtn.classList.remove('bg-red-700');
    selectedBtn.classList.add('bg-black', 'text-yellow-400');
  }
}

// Event listeners untuk modal time picker
timePickerBtn.addEventListener('click', function () {
  showModalWithAnimation(timePickerModal);
  generateTimeOptions();
});

closeTimePickerModal.addEventListener('click', function () {
  hideModalWithAnimation(timePickerModal);
});

timePickerModal.addEventListener('click', function (e) {
  if (e.target === timePickerModal) {
    hideModalWithAnimation(timePickerModal);
  }
});

// Konfirmasi waktu
confirmTime.addEventListener('click', function () {
  if (selectedTime) {
    hideModalWithAnimation(timePickerModal);
    // Update status meja berdasarkan waktu yang dipilih
    if (typeof updateTableStatus === 'function') {
      updateTableStatus();
    }
  } else {
    alert('Silakan pilih waktu terlebih dahulu.');
  }
});

// Keyboard support untuk modal time picker
document.addEventListener('keydown', function (e) {
  if (timePickerModal.classList.contains('flex')) {
    if (e.key === 'Escape') {
      hideModalWithAnimation(timePickerModal);
    } else if (e.key === 'Enter') {
      e.preventDefault();
      confirmTime.click();
    }
  }
});

// Tambahkan ripple effect ke tombol time picker
[confirmTime].forEach(button => {
  button.addEventListener('click', createRipple);
});

// Update fungsi selectDate untuk reset waktu saat tanggal berubah
if (typeof selectDate === 'function') {
  const originalSelectDateForTime = selectDate;
  selectDate = function (date) {
    originalSelectDateForTime(date);

    // Reset waktu yang dipilih saat tanggal berubah
    selectedTime = null;
    selectedTimeText.textContent = 'Select a time';
    bookingTimeInput.value = '';

    // Tambahkan animasi pada tanggal yang dipilih
    const calendarGrid = document.getElementById('calendarGrid');
    if (calendarGrid) {
      const formatDateForInput = window.formatDateForInput || function(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
      };
      const selectedElement = calendarGrid.querySelector(`[data-date="${formatDateForInput(date)}"]`);
      if (selectedElement) {
        selectedElement.classList.add('selected');
        setTimeout(() => {
          selectedElement.classList.remove('selected');
        }, 300);
      }
    }
  };
}

// Tambahkan CSS untuk time picker
const timePickerStyle = document.createElement('style');
timePickerStyle.textContent = `
  #timeGrid button {
    position: relative;
    overflow: hidden;
  }
  
  #timeGrid button:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
  }
  
  #timePickerBtn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  }
  
  #timeGrid button.selected {
    animation: timePulse 0.3s ease-in-out;
  }
  
  @keyframes timePulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
  }
`;
document.head.appendChild(timePickerStyle); 