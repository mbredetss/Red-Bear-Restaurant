    <?php
    session_start();
    require_once '../../database.php';

    // Cek login admin
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header('Location: ../../login_register/login.php');
        exit;
    }

    $koneksi = koneksiDatabase('red bear');

    $posts = [];
    $query = "SELECT bp.id, bp.title, bp.status, bp.created_at, u.name as author_name FROM blog_posts bp JOIN users u ON bp.user_id = u.id ORDER BY bp.created_at DESC";
    $result = $koneksi->query($query);
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin - Kelola Postingan Blog</title>
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    </head>
    <body class="bg-gray-50">
        <div class="min-h-screen">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center py-6">
                        <h1 class="text-2xl font-bold text-gray-900">Kelola Postingan Blog</h1>
                        <a href="../beranda.php" class="text-gray-600 hover:text-gray-900">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
                <div class="bg-white shadow overflow-hidden sm:rounded-md">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Daftar Postingan</h3>
                    </div>
                    <?php if (empty($posts)): ?>
                        <p class="text-center text-gray-500 py-6">Tidak ada postingan blog.</p>
                    <?php else: ?>
                        <ul class="divide-y divide-gray-200">
                            <?php foreach ($posts as $post): ?>
                                <li class="px-4 py-4 sm:px-6 flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($post['title']); ?></p>
                                        <p class="text-sm text-gray-500">Oleh: <?php echo htmlspecialchars($post['author_name']); ?> pada <?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?></p>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php 
                                                if ($post['status'] == 'pending') echo 'bg-yellow-100 text-yellow-800';
                                                else if ($post['status'] == 'published') echo 'bg-green-100 text-green-800';
                                                else echo 'bg-red-100 text-red-800';
                                            ?>">
                                            <?php echo ucfirst($post['status']); ?>
                                        </span>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="view_blog.php?id=<?php echo $post['id']; ?>" class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200">
                                            <i class="fas fa-eye mr-1"></i> Lihat
                                        </a>
                                        <?php if ($post['status'] == 'pending'): ?>
                                            <button onclick="updateBlogStatus(<?php echo $post['id']; ?>, 'published')" class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                                <i class="fas fa-check mr-1"></i> Publikasikan
                                            </button>
                                            <button onclick="updateBlogStatus(<?php echo $post['id']; ?>, 'rejected')" class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                                                <i class="fas fa-times mr-1"></i> Tolak
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </main>
        </div>

        <script>
            function updateBlogStatus(id, status) {
                if (confirm(`Anda yakin ingin ${status === 'published' ? 'mempublikasikan' : 'menolak'} postingan ini?`)) {
                    fetch('api/update_blog_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ id: id, status: status })
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        if (data.success) {
                            location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat memproses permintaan.');
                    });
                }
            }
        </script>
    </body>
    </html>
    