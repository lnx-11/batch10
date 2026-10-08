<?php
session_start();
require_once dirname(__DIR__, 2) . '/koneksi_db.php';
require_once dirname(__DIR__, 2) . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}
if (!t9_verify_csrf()) {
    http_response_code(403);
    exit('Token keamanan tidak valid. Muat ulang form dan coba kembali.');
}

$old = [];
foreach (['nama_produk', 'kategori', 'harga', 'stok', 'deskripsi'] as $field) {
    $old[$field] = trim((string) ($_POST[$field] ?? ''));
}
$errors = [];
if ($old['nama_produk'] === '' || strlen($old['nama_produk']) > 255) $errors[] = 'Nama produk wajib diisi dan maksimal 255 karakter.';
if (!in_array($old['kategori'], t9_product_categories($koneksi), true)) $errors[] = 'Pilih kategori yang tersedia.';
$price = filter_var($old['harga'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($price === false) $errors[] = 'Harga harus berupa bilangan bulat lebih dari 0.';
$stock = filter_var($old['stok'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
if ($stock === false) $errors[] = 'Stok harus berupa bilangan bulat 0 atau lebih.';
if ($old['deskripsi'] === '' || strlen($old['deskripsi']) > 2000) $errors[] = 'Deskripsi wajib diisi dan maksimal 2000 karakter.';

$image = null;
try {
    $image = t9_save_uploaded_image($_FILES['gambar'] ?? [], true);
} catch (InvalidArgumentException | RuntimeException $exception) {
    $errors[] = $exception->getMessage();
}

if ($errors) {
    if ($image) @unlink(dirname(__DIR__, 2) . '/' . $image);
    http_response_code(422);
    require dirname(__DIR__) . '/form/create_form.php';
    exit;
}

$statement = $koneksi->prepare(
    'INSERT INTO products (nama_produk, kategori, harga, deskripsi, stok, image) VALUES (?, ?, ?, ?, ?, ?)'
);
$statement->bind_param('ssisis', $old['nama_produk'], $old['kategori'], $price, $old['deskripsi'], $stock, $image);
$statement->execute();
$_SESSION['admin_message'] = 'Produk berhasil ditambahkan.';
header('Location: ../index.php');
exit;