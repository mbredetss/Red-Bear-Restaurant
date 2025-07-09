    <?php
    require_once 'database.php';
    $koneksi = koneksiDatabase("red bear");

    $post = null;
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $stmt = $koneksi->prepare("SELECT bp.id, bp.title, bp.content, bp.image_path, bp.created_at, u.name as author_name FROM blog_posts bp JOIN users u ON bp.user_id = u.id WHERE bp.id = ? AND bp.status = 'published'");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $post = $result->fetch_assoc();
        $stmt->close();
    }

    if (!$post) {
        echo "<script>alert('Postingan tidak ditemukan atau belum dipublikasikan.'); window.location.href='blog.php';</script>";
        exit;
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($post['title']); ?></title>
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    </head>
    <body class="bg-gray-50 p-8">
        <div class="max-w-3xl mx-auto bg-white p-8 rounded-lg shadow-md">
            <h1 class="text-3xl font-bold mb-4 text-gray-800"><?php echo htmlspecialchars($post['title']); ?></h1>
            <p class="text-gray-600 text-sm mb-6">Oleh: <?php echo htmlspecialchars($post['author_name']); ?> pada <?php echo date('d M Y', strtotime($post['created_at'])); ?></p>
            <?php if ($post['image_path']): ?>
                <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="w-full h-64 object-cover rounded-lg mb-6">
            <?php endif; ?>
            <div class="prose max-w-none text-gray-800">
                <?php echo nl2br(htmlspecialchars($post['content'])); ?>
            </div>
            <div class="mt-8">
                <a href="home.php" class="text-red-600 hover:underline font-medium">&larr; Kembali ke Blog</a>
            </div>
        </div>
    </body>
    </html>
    