<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

$data = json_decode(file_get_contents(__DIR__ . '/laporan.json'), true);

$phpWord = new PhpWord();
$section = $phpWord->addSection();
$section->addTitle('📄 Laporan Penjualan Mingguan', 1);
$section->addText('Dibuat pada: ' . date('d M Y H:i'));
$section->addTextBreak(1);

foreach ($data as $entry) {
    $section->addText("👤 Nama   : {$entry['nama']}");
    $section->addText("📅 Tanggal: {$entry['tanggal']}");
    $section->addText("📦 Status : {$entry['status']}");
    $section->addTextBreak(1);
}

$filename = 'Laporan-Mingguan-' . date('Ymd-His') . '.docx';
$path = __DIR__ . '/' . $filename;

$writer = IOFactory::createWriter($phpWord, 'Word2007');
$writer->save($path);

// Kirim ke browser
header("Content-Description: File Transfer");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
readfile($path);
exit;
