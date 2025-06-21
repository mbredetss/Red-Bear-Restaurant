document.addEventListener('DOMContentLoaded', () => {
    // --- NAVBAR SCROLL EFFECT ---
    const navbar = document.getElementById('navbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            navbar.classList.toggle('nav-scrolled', window.scrollY > 50);
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
            if (!bg) return;
            bg.style.backgroundImage = `url('${images[currentSlide]}')`;
            bg.style.opacity = 1;
            setTimeout(() => {
                slideElement.style.backgroundImage = `url('${images[currentSlide]}')`;
                bg.style.opacity = 0;
            }, 700);
            indicators.forEach((indicator, index) => {
                indicator.classList.toggle('active', index === currentSlide);
            });
        };
        const nextSlide = () => {
            currentSlide = (currentSlide + 1) % images.length;
            updateSlide();
        };
        document.querySelector('.next-btn')?.addEventListener('click', nextSlide);
        document.querySelector('.prev-btn')?.addEventListener('click', () => {
            currentSlide = (currentSlide - 1 + images.length) % images.length;
            updateSlide();
        });
        indicators.forEach(indicator => {
            indicator.addEventListener('click', (e) => {
                currentSlide = parseInt(e.target.dataset.slide, 10);
                updateSlide();
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
                const isHidden = menu.classList.contains('hidden');
                document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.add('hidden'));
                if (isHidden) {
                    menu.classList.remove('hidden');
                }
            });
        }
    };
    setupDropdown('userAvatar', 'profileDropdown');
    setupDropdown('more-btn', 'more-menu');
    
    window.addEventListener('click', (e) => {
        if (!e.target.closest('.relative')) {
            document.querySelectorAll('.dropdown-menu').forEach(menu => menu.classList.add('hidden'));
        }
    });
});