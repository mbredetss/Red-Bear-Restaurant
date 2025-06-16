<?php
function koneksiDatabase($namaDatabase = "red bear", $host = "localhost", $username = "root", $password = "")
{
    $koneksi = new mysqli($host, $username, $password, $namaDatabase);

    if ($koneksi->connect_error) {
        http_response_code(500);
        echo json_encode(["error" => "Koneksi ke database '$namaDatabase' gagal: " . $koneksi->connect_error]);
        exit;
    }

    return $koneksi;
}
