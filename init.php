<?php
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use Cloudinary\Cloudinary;
$cloudinary = new Cloudinary();
$adminApi = $cloudinary->adminApi();