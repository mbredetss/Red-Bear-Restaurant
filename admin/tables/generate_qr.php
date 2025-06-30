<?php
require_once '../../database.php';
session_start();

// Cek login dan peran admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../home.php');
    exit;
}

$koneksi = koneksiDatabase('red bear');
$query = "SELECT id, table_number FROM tables ORDER BY table_number ASC";
$result = $koneksi->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Generate QR Code Meja</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-center flex-grow">QR Code untuk Setiap Meja</h1>
            <a href="../beranda.php" class="text-gray-600 hover:text-gray-900 bg-white px-4 py-2 rounded-lg shadow border transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Dashboard
            </a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-8">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="bg-white p-6 rounded-lg shadow-lg flex flex-col items-center text-center">
                    <h2 class="text-xl font-bold mb-4">Meja <?php echo $row['table_number']; ?></h2>
                    <div id="qrcode-<?php echo $row['id']; ?>" class="p-2 border rounded-md"></div>
                    <p class="text-xs text-gray-500 mt-2 break-all" id="url-<?php echo $row['id']; ?>"></p>
                </div>
                <script>
                    (function() {
                        // Menggunakan alamat IP lokal, ganti jika perlu
                        const ipAddress = "192.168.1.11"; 
                        const url = `http://${ipAddress}:8080/sistem%20enterprise/restaurant%20web/home.php?table_id=<?php echo $row['id']; ?>#menu`;
                        
                        document.getElementById('url-<?php echo $row['id']; ?>').textContent = `ID Meja: <?php echo $row['id']; ?>`;
                        
                        new QRCode(document.getElementById("qrcode-<?php echo $row['id']; ?>"), {
                            text: url,
                            width: 128,
                            height: 128,
                            colorDark : "#000000",
                            colorLight : "#ffffff",
                            correctLevel : QRCode.CorrectLevel.H
                        });
                    })();
                </script>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html> 