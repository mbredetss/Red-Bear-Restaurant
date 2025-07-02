<?php
// Fungsi untuk mengambil postingan blog yang sudah dipublikasikan (limit 3 terbaru)
function get_published_blog_posts($koneksi, $limit = 3) {
    $blog_posts = [];
    $query_blog = "SELECT bp.id, bp.title, bp.content, bp.image_path, bp.created_at, u.name as author_name FROM blog_posts bp JOIN users u ON bp.user_id = u.id WHERE bp.status = 'published' ORDER BY bp.created_at DESC LIMIT ?";
    if ($stmt = $koneksi->prepare($query_blog)) {
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $blog_posts[] = $row;
        }
        $stmt->close();
    }
    return $blog_posts;
} 