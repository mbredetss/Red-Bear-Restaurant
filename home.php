<?php
require_once 'script/session_check.php'; // Mengimpor file session_check.php
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Red Bear - Korean BBQ Test</title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link href="style/styles.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  
  <!-- Custom Styles -->
  <style>
    body { font-family: 'Poppins', sans-serif; color: #333; }
    h1, h2, h3, h4, h5, h6 { font-family: 'Montserrat', sans-serif; }
    .nav-scrolled { background-color: rgba(26, 26, 26, 0.8) !important; backdrop-filter: blur(10px); }
    .hero-gradient { background: linear-gradient(to top, rgba(0,0,0,0.7), transparent 40%); }
    .group:hover .group-hover-scale-105 { transform: scale(1.05); }
  </style>
</head>

<body class="bg-gray-50">
  <!-- Navbar -->
  <header id="navbar" class="fixed w-full z-50 flex items-center justify-between px-6 py-4">
    <a href="home.php">
      <img src="img/red-bear-logo.png" alt="Logo" class="h-16" style="border-radius: 50%;">
    </a>

    <nav class="flex gap-1 md:gap-4 items-center">
      <a href="#" class="hover:bg-white/10 text-white px-3 py-2 rounded-md font-semibold text-sm transition-colors">HOME</a>
      <a href="#menu" class="hover:bg-white/10 text-white px-3 py-2 rounded-md font-semibold text-sm transition-colors">MENU</a>
      
      <!-- More Dropdown -->
      <div class="relative" id="more-dropdown">
        <button id="more-btn" class="hover:bg-white/10 text-white px-3 py-2 rounded-md font-semibold text-sm transition-colors flex items-center gap-1">
          MORE <i class="fas fa-chevron-down text-xs"></i>
        </button>
        <div id="more-menu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl py-1 z-50 text-gray-800 dropdown-menu">
          <a href="#about" class="block px-4 py-2 text-sm hover:bg-gray-100">ABOUT</a>
          <a href="#location" class="block px-4 py-2 text-sm hover:bg-gray-100">LOCATION</a>
          <a href="#blog" class="block px-4 py-2 text-sm hover:bg-gray-100">BLOG</a>
        </div>
      </div>
      
<a id="bookTable" href="#" class="bg-red-600 text-white px-5 py-2 rounded-full font-bold text-sm hover:bg-red-700 transition-all duration-300 shadow-lg">BOOK A TABLE</a>
      
      <a id="orderStatusBtn" href="#" class="relative text-white hover:bg-white/10 p-2 rounded-full">
        <i class="fas fa-shopping-cart text-xl"></i>
        <span id="orderBadge" class="absolute -top-0.5 -right-0.5 flex h-3 w-3">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
          <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
        </span>
      </a>

      <?php if ($user): ?>
        <!-- Tampilkan Avatar & Dropdown jika login -->
        <div class="relative ml-4">
          <button id="userAvatar"
            class="flex items-center justify-center h-10 w-10 rounded-full bg-white text-gray-800 border border-gray-300 shadow hover:shadow-lg transition duration-200 focus:outline-none">
            <i class="fas fa-user"></i>
          </button>

          <div id="profileDropdown"
            class="hidden absolute right-0 mt-3 w-56 bg-white rounded-xl shadow-lg z-50 text-sm text-gray-700 overflow-hidden ring-1 ring-black/5 transition-all duration-300 dropdown-menu">
            <div class="px-4 py-3 border-b">
              <p class="font-semibold">Halo, <?= htmlspecialchars($user['name']) ?></p>
              <p class="text-xs text-gray-500 mt-1">Saldo: <span
                  class="font-medium text-green-600">Rp<?= number_format($saldo, 0, ',', '.') ?></span></p>
            </div>
            <a href="logout.php" class="block px-4 py-2 hover:bg-gray-100">🚪 Logout</a>
          </div>
        </div>
      <?php else: ?>
        <!-- Tampilkan tombol login jika belum login -->
        <a href="login_register/login.php"
          class="bg-white text-gray-800 border border-gray-300 px-4 py-2 rounded-full hover:bg-gray-100 hover:shadow-md font-bold text-sm transition-all">Login</a>
      <?php endif; ?>

    </nav>
  </header>

  <!-- Hero Section -->
  <section class="relative h-screen bg-cover bg-center carousel-slide overflow-hidden" style="background-image: url('img/image1.png');">
    <div class="carousel-bg absolute inset-0 transition-opacity duration-700 opacity-0 pointer-events-none"></div>
    <div class="absolute inset-0 hero-gradient flex flex-col items-center justify-center text-center text-white px-4">
      <h1 class="text-6xl md:text-7xl font-extrabold" style="font-family: 'Montserrat', sans-serif;">RED BEAR</h1>
      <p class="text-xl md:text-2xl mt-4 font-light tracking-wider">PREMIUM KOREAN BARBEQUE</p>

      <!-- Carousel indicators -->
      <div class="absolute bottom-10 flex gap-3 mt-8">
        <span class="carousel-indicator active w-3 h-3 bg-white rounded-full cursor-pointer" data-slide="0"></span>
        <span class="carousel-indicator w-3 h-3 bg-white/60 rounded-full cursor-pointer" data-slide="1"></span>
        <span class="carousel-indicator w-3 h-3 bg-white/60 rounded-full cursor-pointer" data-slide="2"></span>
      </div>

      <!-- Navigation buttons -->
      <button class="carousel-btn prev-btn absolute left-4 md:left-10 top-1/2 transform -translate-y-1/2 p-3 text-white bg-black/30 rounded-full hover:bg-black/50 transition-colors">&#10094;</button>
      <button class="carousel-btn next-btn absolute right-4 md:right-10 top-1/2 transform -translate-y-1/2 p-3 text-white bg-black/30 rounded-full hover:bg-black/50 transition-colors">&#10095;</button>
    </div>
  </section>

  <!-- Public Display + Menu Grid -->
  <section id="menu" class="py-20 bg-gray-50">
    <div class="text-center mb-12">
      <h2 class="text-4xl font-bold text-gray-800">TASTY BITES</h2>
      <p class="text-gray-500 mt-2">Discover our signature dishes and popular drinks.</p>
    </div>

    <!-- Menu Grid -->
    <div id="menu-container" class="container mx-auto px-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8"></div>
  </section>

  <div id="pesan-modal" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50 px-4">
    <div id="pesan-modal-content" class="bg-white p-6 rounded-xl shadow-2xl w-full max-w-sm space-y-4 relative transform transition-all opacity-0 scale-95">
      <button id="pesan-close" class="absolute top-3 right-4 text-gray-400 hover:text-gray-800 text-2xl font-bold">&times;</button>
      <h3 id="pesan-menu-nama" class="text-xl font-bold text-gray-800">Order Menu</h3>
      <div class="flex items-center gap-4">
        <label for="pesan-jumlah" class="font-semibold text-gray-600">Quantity:</label>
        <input type="number" id="pesan-jumlah" min="1" value="1" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500" />
      </div>
      <button id="pesan-kirim" class="w-full bg-red-600 text-white py-3 rounded-lg font-bold hover:bg-red-700 transition-colors shadow-lg hover:shadow-xl">
        <i class="fas fa-paper-plane mr-2"></i>Place Order
      </button>
    </div>
  </div>


  <!-- Modal Tampilan Menu Detail -->
  <div id="menu-modal" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 hidden transition-opacity duration-300 px-4">
    <div
      class="relative bg-white rounded-xl overflow-hidden max-w-md w-full max-h-[90vh] scale-95 opacity-0 transform transition-all duration-300 flex flex-col"
      id="modal-content">

      <!-- Tombol Tutup -->
      <button id="modal-close" class="absolute top-3 right-3 text-white text-3xl z-20 bg-black/40 w-10 h-10 rounded-full hover:bg-black/60 transition-colors">&times;</button>

      <!-- Navigasi Kiri -->
      <button id="modal-prev" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-white text-4xl z-20 bg-black/40 p-2 rounded-full hover:bg-black/60 transition-colors w-12 h-12 flex items-center justify-center">&lt;</button>

      <!-- Navigasi Kanan -->
      <button id="modal-next" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-white text-4xl z-20 bg-black/40 p-2 rounded-full hover:bg-black/60 transition-colors w-12 h-12 flex items-center justify-center">&gt;</button>

      <!-- Gambar Menu -->
      <div class="relative w-full h-80">
        <img id="modal-image" src="" alt="Menu Detail" class="w-full h-full object-cover" />
      </div>

      <!-- Nama Menu -->
      <div class="bg-white text-gray-800 text-center text-xl font-bold py-4 border-t border-gray-200" id="modal-name"></div>
    </div>
  </div>

  <!-- Modal Status Pesanan (Disederhanakan, digabung ke Detail) -->
  <div id="orderModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg shadow-lg text-center w-80">
      <h2 class="text-xl font-bold mb-4">Status Pesanan</h2>
      <p id="orderStatusText" class="text-gray-700 text-lg mb-6">
        <span id="orderStatusSpan">...</span>
      </p>
      <button id="closeOrderModal" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">Tutup</button>
    </div>
  </div>

  <!-- Modal Detail Pesanan dengan Tab -->
  <div id="orderDetailsModal" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50 px-4">
    <div id="orderDetailsModal-content" class="bg-white rounded-xl shadow-2xl w-full max-w-lg max-h-[85vh] flex flex-col transform transition-all opacity-0 scale-95">
      <div class="p-6 border-b border-gray-200 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">Your Orders</h2>
        <button id="closeOrderDetails" class="text-gray-400 hover:text-gray-800 text-2xl">&times;</button>
      </div>
      
      <!-- Tab Navigation -->
      <div class="flex border-b border-gray-200 px-6">
        <button id="tabAktif" class="flex-1 py-3 text-center font-semibold border-b-2 border-red-600 text-red-600 transition-colors">Active</button>
        <button id="tabSelesai" class="flex-1 py-3 text-center font-semibold text-gray-500 hover:text-red-600 border-b-2 border-transparent transition-colors">History</button>
      </div>

      <!-- Tab Content -->
      <div class="p-6 space-y-4 overflow-y-auto">
        <ul id="orderListAktif" class="space-y-4 text-gray-700">
          <!-- Pesanan Aktif akan diisi via JS -->
        </ul>
        <ul id="orderListSelesai" class="space-y-4 text-gray-700 hidden">
          <!-- Pesanan Selesai akan diisi via JS -->
        </ul>
      </div>
    </div>
  </div>

  <!-- WhatsApp Button -->
  <a href="https://api.whatsapp.com/send" target="_blank" class="fixed bottom-6 right-6 z-50 bg-green-500 text-white w-14 h-14 rounded-full flex items-center justify-center shadow-lg hover:bg-green-600 transition-all transform hover:scale-110">
    <i class="fab fa-whatsapp text-3xl"></i>
  </a>
  

  <!-- Book a Table Modal -->
  <div id="bookTableModal" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50 px-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-8 relative">
      <button id="closeBookTableModal" class="absolute top-3 right-4 text-gray-400 hover:text-gray-800 text-2xl font-bold">&times;</button>
      <h3 class="text-2xl font-bold text-gray-800 mb-6 text-center">Book a Table</h3>
      <form id="bookTableForm" class="space-y-4">
        <div>
          <label for="bookName" class="block font-semibold mb-1">Name</label>
          <input type="text" id="bookName" name="name" required class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500" />
        </div>
        <div>
          <label for="bookPhone" class="block font-semibold mb-1">Phone</label>
          <input type="tel" id="bookPhone" name="phone" required class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500" />
        </div>
        <div>
          <label for="bookDate" class="block font-semibold mb-1">Date</label>
          <input type="date" id="bookDate" name="date" required class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500" />
        </div>
        <div>
          <label for="bookTime" class="block font-semibold mb-1">Time</label>
          <input type="time" id="bookTime" name="time" required class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500" />
        </div>
        <div>
          <label for="bookPeople" class="block font-semibold mb-1">Number of People</label>
          <input type="number" id="bookPeople" name="people" min="1" max="20" required class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500" />
        </div>
        <button type="submit" class="w-full bg-red-600 text-white py-3 rounded-lg font-bold hover:bg-red-700 transition-colors shadow-lg hover:shadow-xl">
          <i class="fas fa-calendar-check mr-2"></i>Book Now
        </button>
        <div id="bookTableMsg" class="text-center text-sm mt-2"></div>
      </form>
    </div>
  </div>

  <script>
  // Book a Table Modal Logic
  document.addEventListener('DOMContentLoaded', function() {
    const bookBtn = document.getElementById('bookTable');
    const modal = document.getElementById('bookTableModal');
    const closeBtn = document.getElementById('closeBookTableModal');
    const form = document.getElementById('bookTableForm');
    const msg = document.getElementById('bookTableMsg');

    bookBtn.addEventListener('click', function(e) {
      e.preventDefault();
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      msg.textContent = '';
      form.reset();
    });

    closeBtn.addEventListener('click', function() {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    });

    modal.addEventListener('click', function(e) {
      if (e.target === modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
      }
    });

    form.addEventListener('submit', function(e) {
      e.preventDefault();
      msg.textContent = 'Booking...';
      msg.className = 'text-center text-sm mt-2 text-gray-500';
      // Simulasi AJAX (ganti dengan AJAX ke server jika ada backend)
      setTimeout(function() {
        msg.textContent = 'Thank you! Your table has been booked.';
        msg.className = 'text-center text-green-600 font-semibold mt-2';
        form.reset();
      }, 1200);
    });
  });
  </script>

<!-- About Section -->
<section id="about" class="py-20 bg-white">
  <div class="container mx-auto px-4 max-w-4xl">
    <div class="text-center mb-10">
      <h2 class="text-4xl font-bold text-gray-800 mb-4">About Red Bear</h2>
      <p class="text-gray-600 text-lg">
        Red Bear is your destination for authentic Korean Barbeque, offering premium meats, fresh ingredients, and a cozy atmosphere. Our mission is to bring the best of Korean cuisine to your table, ensuring every visit is a memorable dining experience.
      </p>
    </div>
    <div class="flex flex-col md:flex-row items-center gap-8">
      <img src="img/about-red-bear.png" alt="About Red Bear" class="w-full md:w-1/2 rounded-xl shadow-lg object-cover">
      <div class="flex-1">
        <h3 class="text-2xl font-semibold text-gray-700 mb-3">Why Choose Us?</h3>
        <ul class="list-disc list-inside text-gray-600 space-y-2">
          <li>Premium quality meats and ingredients</li>
          <li>Traditional Korean recipes with a modern twist</li>
          <li>Comfortable and stylish dining environment</li>
          <li>Friendly and attentive service</li>
          <li>Perfect for family gatherings, friends, and celebrations</li>
        </ul>
      </div>
    </div>
  </div>
</section>


<!-- Location Section -->
<section id="location" class="py-20 bg-gray-100">
  <div class="container mx-auto px-4 max-w-4xl">
    <div class="text-center mb-10">
      <h2 class="text-4xl font-bold text-gray-800 mb-4">Our Location</h2>
      <p class="text-gray-600 text-lg">
        Visit Red Bear at our convenient location. We look forward to serving you!
      </p>
    </div>
    <div class="flex flex-col md:flex-row items-center gap-8">
      <div class="w-full md:w-1/2 rounded-xl overflow-hidden shadow-lg">
        <iframe
          src="https://www.google.com/maps?q=Jakarta+Indonesia&output=embed"
          width="100%" height="320" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
      </div>
      <div class="flex-1">
        <h3 class="text-2xl font-semibold text-gray-700 mb-3">Red Bear Restaurant</h3>
        <ul class="text-gray-600 space-y-2">
          <li><i class="fas fa-map-marker-alt mr-2 text-red-600"></i>Jl. Contoh Alamat No. 123, Jakarta, Indonesia</li>
          <li><i class="fas fa-phone-alt mr-2 text-red-600"></i>(021) 1234-5678</li>
          <li><i class="fas fa-clock mr-2 text-red-600"></i>Open: 10:00 - 22:00 (Everyday)</li>
          <li><i class="fas fa-envelope mr-2 text-red-600"></i>info@redbear.com</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- Blog Section -->
<section id="blog" class="py-20 bg-white">
  <div class="container mx-auto px-4 max-w-5xl">
    <div class="text-center mb-12">
      <h2 class="text-4xl font-bold text-gray-800 mb-4">Red Bear Blog</h2>
      <p class="text-gray-600 text-lg">
        Get the latest updates, tips, and stories from Red Bear Restaurant.
      </p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      <!-- Blog Post 1 -->
      <article class="bg-gray-50 rounded-xl shadow hover:shadow-lg transition p-6 flex flex-col">
        <img src="img/blog1.jpg" alt="Blog 1" class="rounded-lg mb-4 object-cover h-40 w-full">
        <h3 class="text-xl font-semibold text-gray-800 mb-2">5 Tips for the Perfect Korean BBQ Experience</h3>
        <p class="text-gray-600 mb-4 flex-1">
          Discover how to make the most of your Korean BBQ meal at Red Bear, from grilling techniques to sauce pairings!
        </p>
        <a href="#" class="text-red-600 font-bold hover:underline mt-auto">Read More</a>
      </article>
      <!-- Blog Post 2 -->
      <article class="bg-gray-50 rounded-xl shadow hover:shadow-lg transition p-6 flex flex-col">
        <img src="img/blog2.jpg" alt="Blog 2" class="rounded-lg mb-4 object-cover h-40 w-full">
        <h3 class="text-xl font-semibold text-gray-800 mb-2">Behind the Scenes: Our Signature Dishes</h3>
        <p class="text-gray-600 mb-4 flex-1">
          Go behind the scenes with our chefs and learn what makes our signature dishes so special and delicious.
        </p>
        <a href="#" class="text-red-600 font-bold hover:underline mt-auto">Read More</a>
      </article>
      <!-- Blog Post 3 -->
      <article class="bg-gray-50 rounded-xl shadow hover:shadow-lg transition p-6 flex flex-col">
        <img src="img/blog3.jpg" alt="Blog 3" class="rounded-lg mb-4 object-cover h-40 w-full">
        <h3 class="text-xl font-semibold text-gray-800 mb-2">Celebrating Special Moments at Red Bear</h3>
        <p class="text-gray-600 mb-4 flex-1">
          See how our guests celebrate birthdays, anniversaries, and more with us. Your special moments, our honor!
        </p>
        <a href="#" class="text-red-600 font-bold hover:underline mt-auto">Read More</a>
      </article>
    </div>
  </div>
</section>

<script>
// Blog "Read More" functionality
document.addEventListener('DOMContentLoaded', function() {
  // Blog data (replace with dynamic data if needed)
  const blogPosts = [
    {
      title: "5 Tips for the Perfect Korean BBQ Experience",
      image: "img/blog1.jpg",
      content: "Discover how to make the most of your Korean BBQ meal at Red Bear, from grilling techniques to sauce pairings!<br><br>1. Preheat the grill properly.<br>2. Use the right cuts of meat.<br>3. Don’t overcrowd the grill.<br>4. Try all the sauces.<br>5. Enjoy with friends and family for the best experience.",
    },
    {
      title: "Behind the Scenes: Our Signature Dishes",
      image: "img/blog2.jpg",
      content: "Go behind the scenes with our chefs and learn what makes our signature dishes so special and delicious.<br><br>Our chefs use only the freshest ingredients and traditional Korean techniques. Each dish is crafted with care and passion, ensuring authentic flavors in every bite.",
    },
    {
      title: "Celebrating Special Moments at Red Bear",
      image: "img/blog3.jpg",
      content: "See how our guests celebrate birthdays, anniversaries, and more with us. Your special moments, our honor!<br><br>We offer special packages and decorations for your celebrations. Let us know your occasion, and we’ll make it memorable.",
    }
  ];

  // Create modal
  const blogModal = document.createElement('div');
  blogModal.id = 'blogModal';
  blogModal.className = 'fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50 px-4';
  blogModal.innerHTML = `
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg p-8 relative">
      <button id="closeBlogModal" class="absolute top-3 right-4 text-gray-400 hover:text-gray-800 text-2xl font-bold">&times;</button>
      <img id="blogModalImg" src="" alt="" class="rounded-lg mb-4 object-cover w-full h-56">
      <h3 id="blogModalTitle" class="text-2xl font-bold mb-4"></h3>
      <div id="blogModalContent" class="text-gray-700 text-base"></div>
    </div>
  `;
  document.body.appendChild(blogModal);

  // Show modal function
  function showBlogModal(idx) {
    document.getElementById('blogModalImg').src = blogPosts[idx].image;
    document.getElementById('blogModalTitle').textContent = blogPosts[idx].title;
    document.getElementById('blogModalContent').innerHTML = blogPosts[idx].content;
    blogModal.classList.remove('hidden');
    blogModal.classList.add('flex');
  }

  // Hide modal function
  function hideBlogModal() {
    blogModal.classList.add('hidden');
    blogModal.classList.remove('flex');
  }

  // Attach event listeners to "Read More" links
  document.querySelectorAll('section#blog a.text-red-600').forEach(function(link, idx) {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      showBlogModal(idx);
    });
  });

  // Close modal on button click or background click
  blogModal.addEventListener('click', function(e) {
    if (e.target === blogModal || e.target.id === 'closeBlogModal') {
      hideBlogModal();
    }
  });
});
</script>


  <!-- Footer -->
  <footer class="bg-gray-800 text-white pt-12 pb-8">
    <div class="container mx-auto px-6 md:px-12">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- About -->
        <div>
          <h4 class="text-lg font-bold mb-3">RED BEAR</h4>
          <p class="text-gray-400 text-sm">
            Experience the finest Korean Barbeque with premium ingredients and an unforgettable ambiance. Join us for a sizzling good time.
          </p>
        </div>
        <!-- Links -->
        <div>
          <h4 class="text-lg font-bold mb-3">Quick Links</h4>
          <ul class="text-sm space-y-2">
            <li><a href="#" class="text-gray-400 hover:text-white">Home</a></li>
            <li><a href="#menu" class="text-gray-400 hover:text-white">Menu</a></li>
            <li><a href="#" class="text-gray-400 hover:text-white">Book a Table</a></li>
          </ul>
        </div>
        <!-- Social -->
        <div>
          <h4 class="text-lg font-bold mb-3">Follow Us</h4>
          <div class="flex gap-4">
            <a href="#" class="text-gray-400 hover:text-white text-2xl"><i class="fab fa-instagram"></i></a>
            <a href="#" class="text-gray-400 hover:text-white text-2xl"><i class="fab fa-facebook-square"></i></a>
            <a href="#" class="text-gray-400 hover:text-white text-2xl"><i class="fab fa-twitter-square"></i></a>
          </div>
        </div>
      </div>
      <div class="border-t border-gray-700 mt-8 pt-6 text-center text-gray-500 text-sm">
        <p>&copy; 2024 Red Bear. All Rights Reserved.</p>
      </div>
    </div>
  </footer>

  
  <script src="script/script.js"></script>
  <script src="script/menuLoad.js"></script>
</body>

</html>