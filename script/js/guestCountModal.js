// --- Modal Pilih Jumlah Tamu ---
const guestCountBtn = document.getElementById('guestCountBtn');
const guestCountModal = document.getElementById('guestCountModal');
const closeGuestCountModal = document.getElementById('closeGuestCountModal');
const decreaseGuest = document.getElementById('decreaseGuest');
const increaseGuest = document.getElementById('increaseGuest');
const guestDisplay = document.getElementById('guestDisplay');
const confirmGuestCount = document.getElementById('confirmGuestCount');
const guestCountText = document.getElementById('guestCountText');
const guestCountInput = document.getElementById('guestCount');

let currentGuestCount = 2;
const minGuests = 1;
const maxGuests = 8;

// Event listeners untuk modal jumlah tamu akan diupdate di bawah

// Kurangi jumlah tamu
decreaseGuest.addEventListener('click', function () {
  if (currentGuestCount > minGuests) {
    currentGuestCount--;
    guestDisplay.textContent = currentGuestCount;
    updateGuestButtons();
  }
});

// Tambah jumlah tamu
increaseGuest.addEventListener('click', function () {
  if (currentGuestCount < maxGuests) {
    currentGuestCount++;
    guestDisplay.textContent = currentGuestCount;
    updateGuestButtons();
  }
});

// Update status tombol berdasarkan jumlah tamu
function updateGuestButtons() {
  decreaseGuest.disabled = currentGuestCount <= minGuests;
  increaseGuest.disabled = currentGuestCount >= maxGuests;

  if (currentGuestCount <= minGuests) {
    decreaseGuest.classList.add('opacity-50', 'cursor-not-allowed');
    decreaseGuest.classList.remove('hover:bg-white', 'hover:text-red-800');
  } else {
    decreaseGuest.classList.remove('opacity-50', 'cursor-not-allowed');
    decreaseGuest.classList.add('hover:bg-white', 'hover:text-red-800');
  }

  if (currentGuestCount >= maxGuests) {
    increaseGuest.classList.add('opacity-50', 'cursor-not-allowed');
    increaseGuest.classList.remove('hover:bg-white', 'hover:text-red-800');
  } else {
    increaseGuest.classList.remove('opacity-50', 'cursor-not-allowed');
    increaseGuest.classList.add('hover:bg-white', 'hover:text-red-800');
  }
}

// Konfirmasi pilihan jumlah tamu akan diupdate di bawah dengan animasi

// Inisialisasi status tombol
updateGuestButtons();

// Tambahkan animasi smooth untuk modal
function showModalWithAnimation(modal) {
  modal.classList.remove('hidden');
  modal.classList.add('flex');
  // Trigger reflow untuk memastikan animasi berjalan
  modal.offsetHeight;
  modal.querySelector('div').classList.add('animate-in');
}

function hideModalWithAnimation(modal) {
  modal.querySelector('div').classList.remove('animate-in');
  setTimeout(() => {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
  }, 200);
}

// Update event listeners untuk menggunakan animasi
guestCountBtn.addEventListener('click', function () {
  showModalWithAnimation(guestCountModal);
  guestDisplay.textContent = currentGuestCount;
});

closeGuestCountModal.addEventListener('click', function () {
  hideModalWithAnimation(guestCountModal);
});

guestCountModal.addEventListener('click', function (e) {
  if (e.target === guestCountModal) {
    hideModalWithAnimation(guestCountModal);
  }
});

confirmGuestCount.addEventListener('click', function () {
  guestCountInput.value = currentGuestCount;
  guestCountText.textContent = currentGuestCount === 1 ? '1 Guest' : `${currentGuestCount} Guests`;
  hideModalWithAnimation(guestCountModal);
});

// Keyboard support untuk modal
document.addEventListener('keydown', function (e) {
  if (guestCountModal.classList.contains('flex')) {
    if (e.key === 'Escape') {
      hideModalWithAnimation(guestCountModal);
    } else if (e.key === 'ArrowLeft' || e.key === 'ArrowDown') {
      e.preventDefault();
      if (currentGuestCount > minGuests) {
        currentGuestCount--;
        guestDisplay.textContent = currentGuestCount;
        updateGuestButtons();
      }
    } else if (e.key === 'ArrowRight' || e.key === 'ArrowUp') {
      e.preventDefault();
      if (currentGuestCount < maxGuests) {
        currentGuestCount++;
        guestDisplay.textContent = currentGuestCount;
        updateGuestButtons();
      }
    } else if (e.key === 'Enter') {
      e.preventDefault();
      confirmGuestCount.click();
    }
  }
});

// Focus management untuk accessibility
guestCountBtn.addEventListener('click', function () {
  showModalWithAnimation(guestCountModal);
  guestDisplay.textContent = currentGuestCount;
  // Focus pada tombol decrease setelah modal terbuka
  setTimeout(() => {
    decreaseGuest.focus();
  }, 300);
});

// Tambahkan ripple effect untuk tombol
function createRipple(event) {
  const button = event.currentTarget;
  const circle = document.createElement('span');
  const diameter = Math.max(button.clientWidth, button.clientHeight);
  const radius = diameter / 2;

  circle.style.width = circle.style.height = `${diameter}px`;
  circle.style.left = `${event.clientX - button.offsetLeft - radius}px`;
  circle.style.top = `${event.clientY - button.offsetTop - radius}px`;
  circle.classList.add('ripple');

  const ripple = button.getElementsByClassName('ripple')[0];
  if (ripple) {
    ripple.remove();
  }

  button.appendChild(circle);
}

// Tambahkan ripple effect ke tombol-tombol
[decreaseGuest, increaseGuest, confirmGuestCount].forEach(button => {
  button.addEventListener('click', createRipple);
});

// Tambahkan CSS untuk ripple effect
const style = document.createElement('style');
style.textContent = `
  .ripple {
    position: absolute;
    border-radius: 50%;
    transform: scale(0);
    animation: ripple 0.6s linear;
    background-color: rgba(255, 255, 255, 0.3);
  }
  
  @keyframes ripple {
    to {
      transform: scale(4);
      opacity: 0;
    }
  }
  
  #decreaseGuest, #increaseGuest, #confirmGuestCount {
    position: relative;
    overflow: hidden;
  }
`;
document.head.appendChild(style); 