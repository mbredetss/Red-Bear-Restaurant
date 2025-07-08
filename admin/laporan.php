<?php require_once 'vendor/autoload.php';
use PhpOffice\PhpWord\PhpWord;

$data = [
    ['produk' => 'Nasi Goreng', 'jumlah' => 2],
    ['produk' => 'Es Teh', 'jumlah' => 1],
];

// Buat file Word
$word = new PhpWord();
$section = $word->addSection();
$section->addText('Laporan Mingguan Penjualan');

foreach ($data as $item) {
    $section->addText("Produk: {$item['produk']}, Jumlah: {$item['jumlah']}");
}


$filename = 'laporan-mingguan.docx';
header("Content-Description: File Transfer");
header("Content-Disposition: attachment; filename=$filename");
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
$writer = \PhpOffice\PhpWord\IOFactory::createWriter($word, 'Word2007');
$writer->save("php://output");
exit;
?>