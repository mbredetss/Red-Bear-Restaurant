<?php
require_once '../../database.php';
session_start();

// Cek login dan peran admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../home.php');
    exit;
}

$koneksi = koneksiDatabase('red bear');

// Ambil data user
$query = "SELECT id, name, email, saldo FROM users ORDER BY name ASC";
$result = $koneksi->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Kelola Saldo User</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-gray-900">Kelola Saldo User</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="../../admin/beranda.php" class="text-gray-600 hover:text-gray-900">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-users text-2xl text-blue-600"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Total User
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            <?php echo $result->num_rows; ?>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-wallet text-2xl text-green-600"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Total Saldo Sistem
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            <?php 
                                            $total_saldo = $koneksi->query("SELECT SUM(saldo) as total FROM users")->fetch_assoc()['total'];
                                            echo 'Rp' . number_format($total_saldo ? $total_saldo : 0, 0, ',', '.');
                                            ?>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-user-check text-2xl text-purple-600"></i>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            User dengan Saldo
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            <?php 
                                            $users_with_balance = $koneksi->query("SELECT COUNT(*) as total FROM users WHERE saldo > 0")->fetch_assoc()['total'];
                                            echo $users_with_balance;
                                            ?>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User List -->
                <div class="bg-white shadow overflow-hidden sm:rounded-md">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Daftar User dan Saldo
                        </h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">
                            Kelola saldo user untuk booking meja
                        </p>
                    </div>
                    
                    <!-- Search Bar -->
                    <div class="px-4 py-3 sm:px-6 border-b border-gray-200">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" id="userSearch" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Cari berdasarkan nama atau email...">
                        </div>
                    </div>

                    <?php if ($result->num_rows > 0): ?>
                        <ul id="userList" class="divide-y divide-gray-200">
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <li class="px-4 py-4 sm:px-6 user-item">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                    <i class="fas fa-user text-blue-600"></i>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="flex items-center">
                                                    <p class="text-sm font-medium text-gray-900 user-name">
                                                        <?php echo htmlspecialchars($row['name']); ?>
                                                    </p>
                                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $row['saldo'] > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                                        <?php echo $row['saldo'] > 0 ? 'Ada Saldo' : 'Saldo Kosong'; ?>
                                                    </span>
                                                </div>
                                                <div class="mt-1 flex items-center text-sm text-gray-500">
                                                    <p class="user-email">
                                                        <i class="fas fa-envelope mr-1"></i>
                                                        <?php echo htmlspecialchars($row['email']); ?>
                                                    </p>
                                                </div>
                                                <div class="mt-1 flex items-center text-sm text-gray-500">
                                                    <p>
                                                        <i class="fas fa-wallet mr-1"></i>
                                                        Saldo: <span class="font-medium text-gray-900">Rp<?php echo number_format($row['saldo'], 0, ',', '.'); ?></span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <button 
                                                onclick="showAddSaldoModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name']); ?>', <?php echo $row['saldo']; ?>)"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                                            >
                                                <i class="fas fa-plus mr-1"></i>
                                                Tambah Saldo
                                            </button>
                                        </div>
                                    </div>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                        <div id="noResults" class="text-center py-12 hidden">
                            <i class="fas fa-search text-4xl text-gray-300 mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada hasil</h3>
                            <p class="text-gray-500">Tidak ada user yang cocok dengan pencarian Anda.</p>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12">
                            <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada user</h3>
                            <p class="text-gray-500">Belum ada user yang terdaftar.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Tambah Saldo -->
    <div id="addSaldoModal" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50 px-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-8 relative">
            <button id="closeAddSaldoModal" class="absolute top-3 right-4 text-gray-400 hover:text-gray-800 text-2xl font-bold">&times;</button>
            <h3 class="text-xl font-bold text-gray-800 mb-6">Tambah Saldo User</h3>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">User</label>
                <p id="selectedUserName" class="text-gray-900 font-medium"></p>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Saldo Saat Ini</label>
                <p id="currentSaldo" class="text-gray-900 font-medium"></p>
            </div>
            
            <div class="mb-6">
                <label for="addAmount" class="block text-gray-700 font-semibold mb-2">Jumlah Saldo yang Ditambahkan</label>
                <input type="number" id="addAmount" min="1000" step="1000" placeholder="Masukkan jumlah saldo" 
                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 font-semibold mb-2">Saldo Setelah Ditambahkan</label>
                <p id="newSaldo" class="text-green-600 font-bold text-lg">Rp0</p>
            </div>
            
            <button id="submitAddSaldo" class="w-full bg-blue-600 text-white py-3 rounded-lg font-bold hover:bg-blue-700 transition-colors shadow-lg hover:shadow-xl">
                <i class="fas fa-plus mr-2"></i>Tambah Saldo
            </button>
        </div>
    </div>

    <script>
        let selectedUserId = null;
        
        function showAddSaldoModal(userId, userName, currentSaldo) {
            selectedUserId = userId;
            document.getElementById('selectedUserName').textContent = userName;
            document.getElementById('currentSaldo').textContent = 'Rp' + currentSaldo.toLocaleString('id-ID');
            document.getElementById('addAmount').value = '';
            document.getElementById('newSaldo').textContent = 'Rp' + currentSaldo.toLocaleString('id-ID');
            
            document.getElementById('addSaldoModal').classList.remove('hidden');
            document.getElementById('addSaldoModal').classList.add('flex');
        }
        
        // Close modal
        document.getElementById('closeAddSaldoModal').addEventListener('click', function() {
            document.getElementById('addSaldoModal').classList.add('hidden');
            document.getElementById('addSaldoModal').classList.remove('flex');
        });
        
        // Close modal when clicking outside
        document.getElementById('addSaldoModal').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
                this.classList.remove('flex');
            }
        });
        
        // Live search functionality
        document.getElementById('userSearch').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const userItems = document.querySelectorAll('.user-item');
            let resultsFound = false;

            userItems.forEach(item => {
                const name = item.querySelector('.user-name').textContent.toLowerCase();
                const email = item.querySelector('.user-email').textContent.toLowerCase();
                
                if (name.includes(searchTerm) || email.includes(searchTerm)) {
                    item.classList.remove('hidden');
                    resultsFound = true;
                } else {
                    item.classList.add('hidden');
                }
            });

            document.getElementById('noResults').classList.toggle('hidden', resultsFound);
        });
        
        // Calculate new saldo
        document.getElementById('addAmount').addEventListener('input', function() {
            const currentSaldo = parseInt(document.getElementById('currentSaldo').textContent.replace(/[^\d]/g, ''));
            const addAmount = parseInt(this.value) || 0;
            const newSaldo = currentSaldo + addAmount;
            document.getElementById('newSaldo').textContent = 'Rp' + newSaldo.toLocaleString('id-ID');
        });
        
        // Submit add saldo
        document.getElementById('submitAddSaldo').addEventListener('click', function() {
            const addAmount = parseInt(document.getElementById('addAmount').value);
            
            if (!addAmount || addAmount < 1000) {
                alert('Jumlah saldo minimal Rp1.000');
                return;
            }
            
            if (!selectedUserId) {
                alert('User tidak valid');
                return;
            }
            
            fetch('api/add_saldo.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: selectedUserId,
                    amount: addAmount
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Saldo berhasil ditambahkan!');
                    location.reload();
                } else {
                    alert('Gagal menambah saldo: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memproses permintaan.');
            });
        });
    </script>
</body>
</html>
