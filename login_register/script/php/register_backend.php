<?php
session_start(); // mulai session
require_once '../../../database.php';

$koneksi = koneksiDatabase("red bear");

// Tangkap data dari form
$name = $_POST['name'];
$email = $_POST['email'];
$password_plain = $_POST['password'];
$password = password_hash($password_plain, PASSWORD_DEFAULT); // Enkripsi password

// Cek apakah email sudah terdaftar
$cekQuery = "SELECT id FROM users WHERE email = ?";
$cekStmt = $koneksi->prepare($cekQuery);
$cekStmt->bind_param("s", $email);
$cekStmt->execute();
$cekStmt->store_result();

if ($cekStmt->num_rows > 0) {
    // Email sudah terdaftar, arahkan kembali ke register dengan pesan error
    header("Location: ../../register.php?error=email_exists");
    exit();
}
$cekStmt->close();

// Lanjutkan proses registrasi jika email belum terdaftar
$sql = "INSERT INTO users (name, email, password, role, saldo)
        VALUES (?, ?, ?, 'user', 0.00)";

$stmt = $koneksi->prepare($sql);
$stmt->bind_param("sss", $name, $email, $password);

if ($stmt->execute()) {
    // Registrasi berhasil, ambil data user untuk session dan redirect sesuai role
    $lastId = $stmt->insert_id;

    $getUser = $koneksi->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
    $getUser->bind_param("i", $lastId);
    $getUser->execute();
    $result = $getUser->get_result();
    $user = $result->fetch_assoc();

    // Simpan data ke session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];

    // Redirect berdasarkan role
    if ($user['role'] == 'admin') {
        header("Location: ../../../admin/beranda.php");
    } else {
        header("Location: ../../home.php");
    }
    exit();
} else {
    echo "Registrasi gagal: " . $stmt->error;
}

$stmt->close();
$koneksi->close();
?>