    <?php
    session_start();
    require_once 'database.php';

    if (!isset($_SESSION['user_id'])) {
        header('Location: login_register/login.php');
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $image_path = null;

    $koneksi = koneksiDatabase("red bear");

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "img/blog/"; // Pastikan folder ini ada dan writable
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $image_name = uniqid() . '_' . basename($_FILES['image']['name']);
        $target_file = $target_dir . $image_name;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = $target_file;
        } else {
            echo "Gagal mengupload gambar.";
            exit;
        }
    }

    // Insert into database
    $stmt = $koneksi->prepare("INSERT INTO blog_posts (user_id, title, content, image_path, status) VALUES (?, ?, ?, ?, 'pending')");
    $stmt->bind_param("isss", $user_id, $title, $content, $image_path);

    if ($stmt->execute()) {
        echo "<script>alert('Postingan Anda berhasil dikirim dan menunggu konfirmasi admin.'); window.location.href='home.php';</script>";
    } else {
        echo "<script>alert('Gagal mengirim postingan: " . $stmt->error . "'); window.location.href='blog_form.php';</script>";
    }

    $stmt->close();
    $koneksi->close();
    ?>
    