<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../database.php'; // Sesuaikan path ini berdasarkan struktur direktori
session_start();

// Cek login (opsional, sesuaikan dengan kebutuhan)
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php"); // Redirect ke halaman login jika belum login
    exit;
}

$koneksi = koneksiDatabase('red bear');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $table_code = $_POST['table_code'] ?? '';
    if (!empty($table_code)) {
        $stmt = $koneksi->prepare("
            SELECT 
                tb.id AS booking_id,
                tb.table_code,
                u.name AS pelanggan,
                u.email,
                u.phone,
                tb.booking_date,
                tb.booking_time,
                tb.status,
                tb.jumlah_tamu,
                tb.catatan
            FROM 
                table_bookings tb
            INNER JOIN 
                users u ON tb.user_id = u.id
            WHERE 
                tb.table_code = ?
        ");
        $stmt->bind_param("s", $table_code);
        $stmt->execute();
        $result = $stmt->get_result();
        $booking = $result->fetch_assoc();
        $stmt->close();
    } else {
        $booking = false; // Jika input kosong
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cek Kode Meja</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Google Font */
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&display=swap');

        body {
            margin: 0;
            font-family: 'Playfair Display', serif;
            background: url('image1.png') no-repeat center center fixed;
            background-size: cover;
            color: white;
            overflow-x: hidden;
        }

        .overlay {
            background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.7));
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            position: relative;
            z-index: 1;
        }

        h2 {
            font-size: 40px;
            text-align: center;
            margin-bottom: 50px;
            text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.8);
        }

        .button-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-template-rows: auto auto;
            gap: 20px;
            max-width: 900px;
            width: 100%;
            padding: 0 20px;
            margin-top: 30px;
        }

        .button {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
            font-size: 16px;
            font-weight: bold;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.1));
            color: white;
            text-decoration: none;
            border-radius: 30px;
            border: 2px solid rgba(255, 255, 255, 0.5);
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .button i {
            margin-right: 10px;
        }

        .button:hover {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.4), rgba(255, 255, 255, 0.2));
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
        }

        .button:active {
            transform: translateY(0);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        /* Gaya untuk form cek kode meja */
        .check-form {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .input-field {
            padding: 12px 20px;
            font-size: 16px;
            border-radius: 30px;
            border: none;
            outline: none;
            width: 250px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.15), rgba(255, 255, 255, 0.05));
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.5);
            transition: border-color 0.3s ease, background 0.3s ease;
        }

        .input-field::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .input-field:focus {
            border-color: rgba(255, 255, 255, 0.8);
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.1));
        }

        /* Gaya untuk tabel */
        .booking-table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.05), rgba(255, 255, 255, 0.02));
            border-radius: 10px;
            overflow: hidden;
            animation: fadeIn 1s ease-in-out;
        }

        .booking-table th,
        .booking-table td {
            padding: 12px 15px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .booking-table th {
            background: linear-gradient(135deg, rgba(237, 76, 76, 0.8), rgba(237, 76, 76, 0.6));
            color: white;
        }

        .booking-table tr:hover {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.3), rgba(255, 255, 255, 0.1));
        }

        /* Gaya untuk pesan error */
        .error-message {
            color: #ff4d4d;
            font-size: 18px;
            margin-top: 20px;
            text-align: center;
            padding: 10px 20px;
            background: rgba(255, 77, 77, 0.1);
            border-radius: 10px;
            animation: fadeIn 1s ease-in-out;
        }

        @media (max-width: 768px) {
            .button-container {
                grid-template-columns: 1fr 1fr;
                grid-template-rows: auto auto auto;
            }
            .check-form {
                flex-direction: column;
                align-items: center;
            }
            .input-field {
                width: 100%;
                max-width: 300px;
            }
            .booking-table {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .button-container {
                grid-template-columns: 1fr;
                grid-template-rows: repeat(6, auto);
            }
            h2 {
                font-size: 28px;
            }
            .input-field {
                padding: 10px;
                font-size: 14px;
            }
            .booking-table th,
            .booking-table td {
                padding: 10px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="overlay">
        <h2>Cek Kode Meja</h2>
        <form method="POST" action="" class="check-form">
            <input type="text" name="table_code" placeholder="Masukkan Kode Meja" class="input-field" required>
            <button type="submit" class="button"><i class="fas fa-search"></i> Cek</button>
        </form>

        <?php if (isset($booking) && $booking): ?>
            <table class="booking-table">
                <tr>
                    <th>Kode Meja</th>
                    <th>Pelanggan</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Tanggal</th>
                    <th>Waktu</th>
                    <th>Status</th>
                    <th>Jumlah Tamu</th>
                    <th>Catatan</th>
                </tr>
                <tr>
                    <td><?= htmlspecialchars($booking['table_code']) ?></td>
                    <td><?= htmlspecialchars($booking['pelanggan']) ?></td>
                    <td><?= htmlspecialchars($booking['email']) ?></td>
                    <td><?= htmlspecialchars($booking['phone'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($booking['booking_date']) ?></td>
                    <td><?= htmlspecialchars($booking['booking_time']) ?></td>
                    <td><?= htmlspecialchars($booking['status']) ?></td>
                    <td><?= htmlspecialchars($booking['jumlah_tamu']) ?></td>
                    <td><?= htmlspecialchars($booking['catatan'] ?? '-') ?></td>
                </tr>
            </table>
        <?php elseif (isset($booking) && !$booking): ?>
            <p class="error-message">Kode meja tidak valid atau input kosong.</p>
        <?php endif; ?>

        <div class="button-container">
            <a href="beranda.php" class="button"><i class="fas fa-arrow-left"></i> Kembali ke Beranda</a>
        </div>
    </div>
</body>
</html>