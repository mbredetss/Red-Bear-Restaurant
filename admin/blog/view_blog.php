    <?php
    session_start();
    require_once '../../database.php';

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header('Location: ../../login_register/login.php');
        exit;
    }

    $koneksi = koneksiDatabase('red bear');
    $post = null;

    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $stmt = $koneksi->prepare("SELECT bp.*, u.name as author_name FROM blog_posts bp JOIN users u ON bp.user_id = u.id WHERE bp.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $post = $result->fetch_assoc();
        $stmt->close();
    }

    if (!$post) {
        echo "<script>alert('Postingan tidak ditemukan.'); window.location.href='manage_blog.php';</script>";
        exit;
    }

    // Handle update jika form disubmit
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = $_POST['title'] ?? $post['title'];
        $content = $_POST['content'] ?? $post['content'];
        $status = $_POST['status'] ?? $post['status'];
        $current_image_path = $post['image_path'];

        // Handle new image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $target_dir = "../../img/blog/"; // Path relatif dari admin/blog/
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $image_name = uniqid() . '_' . basename($_FILES['image']['name']);
            $new_image_path = $target_dir . $image_name;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $new_image_path)) {
                // Delete old image if exists
                if ($current_image_path && file_exists($current_image_path)) {
                    unlink($current_image_path);
                }
                $current_image_path = $new_image_path;
            } else {
                echo "<script>alert('Gagal mengupload gambar baru.');</script>";
            }
        }

        $update_stmt = $koneksi->prepare("UPDATE blog_posts SET title = ?, content = ?, image_path = ?, status = ? WHERE id = ?");
        $update_stmt->bind_param("ssssi", $title, $content, $current_image_path, $status, $id);

        if ($update_stmt->execute()) {
            echo "<script>alert('Postingan berhasil diperbarui.'); window.location.href='manage_blog.php';</script>";
        } else {
            echo "<script>alert('Gagal memperbarui postingan: " . $update_stmt->error . "');</script>";
        }
        $update_stmt->close();
        // Refresh post data after update
        $stmt = $koneksi->prepare("SELECT bp.*, u.name as author_name FROM blog_posts bp JOIN users u ON bp.user_id = u.id WHERE bp.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $post = $result->fetch_assoc();
        $stmt->close();
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin - Lihat & Edit Postingan Blog</title>
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    </head>
    <body class="bg-gray-50 p-8">
        <div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Lihat & Edit Postingan Blog</h2>
            <form action="view_blog.php?id=<?php echo $post['id']; ?>" method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Penulis:</label>
                    <p class="text-gray-900"><?php echo htmlspecialchars($post['author_name']); ?></p>
                </div>
                <div class="mb-4">
                    <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Judul Postingan:</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label for="content" class="block text-gray-700 text-sm font-bold mb-2">Isi Postingan:</label>
                    <textarea id="content" name="content" rows="15" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required><?php echo htmlspecialchars($post['content']); ?></textarea>
                </div>
                <div class="mb-4">
                    <label for="image" class="block text-gray-700 text-sm font-bold mb-2">Gambar Saat Ini:</label>
                    <?php if ($post['image_path']): ?>
                        <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="Current Image" class="w-64 h-auto object-cover mb-2 rounded-lg">
                    <?php else: ?>
                        <p class="text-gray-500">Tidak ada gambar.</p>
                    <?php endif; ?>
                    <label for="image" class="block text-gray-700 text-sm font-bold mb-2 mt-4">Ganti Gambar (Opsional):</label>
                    <input type="file" id="image" name="image" accept="image/*" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-6">
                    <label for="status" class="block text-gray-700 text-sm font-bold mb-2">Status:</label>
                    <select id="status" name="status" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="pending" <?php echo ($post['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="published" <?php echo ($post['status'] == 'published') ? 'selected' : ''; ?>>Published</option>
                        <option value="rejected" <?php echo ($post['status'] == 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>
                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        <i class="fas fa-save mr-1"></i> Simpan Perubahan
                    </button>
                    <a href="manage_blog.php" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </body>
    </html>
    