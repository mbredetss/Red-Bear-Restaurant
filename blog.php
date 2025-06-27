    <?php
    require_once 'database.php';
    $koneksi = koneksiDatabase("red bear");

    $posts = [];
    $result = $koneksi->query("SELECT bp.id, bp.title, bp.content, bp.image_path, bp.created_at, u.name as author_name FROM blog_posts bp JOIN users u ON bp.user_id = u.id WHERE bp.status = 'published' ORDER BY bp.created_at DESC");
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Blog Red Bear</title>
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    </head>
    <body class="bg-gray-50 p-8">
        <div class="max-w-5xl mx-auto">
            <h1 class="text-3xl font-bold text-center mb-8">Red Bear Blog</h1>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (empty($posts)): ?>
                    <p class="col-span-full text-center text-gray-500">Belum ada postingan blog yang dipublikasikan.</p>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <?php if ($post['image_path']): ?>
                                <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="w-full h-48 object-cover">
                            <?php endif; ?>
                            <div class="p-6">
                                <h2 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($post['title']); ?></h2>
                                <p class="text-gray-600 text-sm mb-4">Oleh: <?php echo htmlspecialchars($post['author_name']); ?> pada <?php echo date('d M Y', strtotime($post['created_at'])); ?></p>
                                <p class="text-gray-700 mb-4"><?php echo nl2br(substr(strip_tags($post['content']), 0, 150)); ?>...</p>
                                <a href="blog_detail.php?id=<?php echo $post['id']; ?>" class="text-red-600 hover:underline font-medium">Baca Selengkapnya</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </body>
    </html>
    