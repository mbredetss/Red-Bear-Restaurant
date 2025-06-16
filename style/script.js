const navbar = document.getElementById('navbar');
const bookTable = document.getElementById('bookTable');

window.addEventListener('scroll', () => {
  if (window.scrollY > 10) {
    navbar.classList.add('nav-scrolled', 'navbar-scrolled');
    bookTable.classList.remove('bg-black');
    bookTable.classList.add('hover:bg-white', 'hover:text-black');
  } else {
    navbar.classList.remove('nav-scrolled', 'navbar-scrolled');
    bookTable.classList.add('bg-black');
    bookTable.classList.remove('hover:bg-white', 'hover:text-black');
  }
});

const images = [
  'img/image3.png',
  'img/image1.png',
  'img/image2.png'
];

let currentSlide = 0;
const slideElement = document.querySelector('.carousel-slide');
const indicators = document.querySelectorAll('.carousel-indicator');
const prevBtn = document.querySelector('.prev-btn');
const nextBtn = document.querySelector('.next-btn');

function updateSlide() {
  const bg = document.querySelector('.carousel-bg');
  // Set gambar baru di layer transisi
  bg.style.backgroundImage = `url('${images[currentSlide]}')`;
  bg.style.opacity = 1;

  setTimeout(() => {
    slideElement.style.backgroundImage = `url('${images[currentSlide]}')`;
    bg.style.opacity = 0;
  }, 700);

  // Update indicators
  indicators.forEach((indicator, index) => {
    if (index === currentSlide) {
      indicator.classList.add('active');
      indicator.classList.remove('opacity-60');
    } else {
      indicator.classList.remove('active');
      indicator.classList.add('opacity-60');
    }
  });
}

function nextSlide() {
  currentSlide = (currentSlide + 1) % images.length;
  updateSlide();
}

function prevSlide() {
  currentSlide = (currentSlide - 1 + images.length) % images.length;
  updateSlide();
}

// Event listeners
nextBtn.addEventListener('click', nextSlide);
prevBtn.addEventListener('click', prevSlide);

// Indicator click events
indicators.forEach((indicator, index) => {
  indicator.addEventListener('click', () => {
    currentSlide = index;
    updateSlide();
  });
});

// Auto slide every 5 seconds
setInterval(nextSlide, 5000);

// Initialize first slide
updateSlide(); 