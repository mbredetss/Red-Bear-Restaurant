    <?php
    session_start();
    require_once '../../../database.php';

    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $id = isset($input['id']) ? intval($input['id']) : 0;
    $status = isset($input['status']) ? $input['status'] : '';

    if (!$id || !in_array($status, ['published', 'rejected'])) {
        echo json_encode(['success' => false, 'message' => 'Data tidak valid.']);
        exit;
    }

    $koneksi = koneksiDatabase('red bear');

    $stmt = $koneksi->prepare("UPDATE blog_posts SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Status postingan berhasil diperbarui.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Postingan tidak ditemukan atau status tidak berubah.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui status: ' . $stmt->error]);
    }

    $stmt->close();
    $koneksi->close();
    ?>
    