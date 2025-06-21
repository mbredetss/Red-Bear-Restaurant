<?php
require_once '../../../database.php';
session_start();

$koneksi = koneksiDatabase("red bear");

$email = $_POST['email'];
$password = $_POST['password'];

// Cek user berdasarkan email
$sql = "SELECT id, name, password, role FROM users WHERE email = ?";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {
        // Simpan data sesi
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        // Redirect berdasarkan role
        if ($user['role'] === 'admin') {
            header("Location: ../../../admin/beranda.php");
        } else {
            header("Location: ../../../home.php");
        }
        exit();
    } else {
        // Password salah
        header("Location: ../../login/login.html?error=wrong_password");
        exit();
    }
} else {
    // Email tidak ditemukan
    header("Location: ../../login/login.html?error=email_not_found");
    exit();
}
