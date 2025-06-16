<?php
require_once '../../../database.php';

$koneksi = koneksiDatabase("red bear");

// Tangkap data dari form
$name = $_POST['name'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Enkripsi password

// Cek apakah email sudah terdaftar
$cekQuery = "SELECT id FROM users WHERE email = ?";
$cekStmt = $koneksi->prepare($cekQuery);
$cekStmt->bind_param("s", $email);
$cekStmt->execute();
$cekStmt->store_result();

if ($cekStmt->num_rows > 0) {
    // Email sudah terdaftar, arahkan kembali ke register dengan pesan error
    header("Location: ../../register/register.html?error=email_exists");
    exit();
}
$cekStmt->close();

// Lanjutkan proses registrasi jika email belum terdaftar
$sql = "INSERT INTO users (name, email, password, role, saldo)
        VALUES (?, ?, ?, 'user', 0.00)";

$stmt = $koneksi->prepare($sql);
$stmt->bind_param("sss", $name, $email, $password);

if ($stmt->execute()) {
    // Registrasi berhasil
    header("Location: ../../login/login.html?register=success");
    exit();
} else {
    echo "Registrasi gagal: " . $stmt->error;
}

$stmt->close();
$koneksi->close();
?>
