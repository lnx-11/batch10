<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $koneksi = new mysqli('localhost', 'root', '', 'ecommerce_db');
    $koneksi->set_charset('utf8mb4');
} catch (mysqli_sql_exception $exception) {
    http_response_code(500);
    exit('Koneksi database gagal. Pastikan MySQL XAMPP aktif dan database ecommerce_db sudah tersedia.');
}