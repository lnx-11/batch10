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

$productId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$productId) {
    http_response_code(400);
    exit('ID produk tidak valid.');
}
$find = $koneksi->prepare('SELECT image FROM products WHERE id = ?');
$find->bind_param('i', $productId);
$find->execute();
$existing = $find->get_result()->fetch_assoc();
if (!$existing) {
    http_response_code(404);
    exit('Produk tidak ditemukan.');
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

$newImage = null;
try {
    $newImage = t9_save_uploaded_image($_FILES['gambar'] ?? [], false);
} catch (InvalidArgumentException | RuntimeException $exception) {
    $errors[] = $exception->getMessage();
}

if ($errors) {
    if ($newImage) @unlink(dirname(__DIR__, 2) . '/' . $newImage);
    $_GET['id'] = $productId;
    http_response_code(422);
    require dirname(__DIR__) . '/form/edit_form.php';
    exit;
}

$image = $newImage ?? $existing['image'];
$update = $koneksi->prepare(
    'UPDATE products SET nama_produk = ?, kategori = ?, harga = ?, deskripsi = ?, stok = ?, image = ? WHERE id = ?'
);
$update->bind_param('ssisisi', $old['nama_produk'], $old['kategori'], $price, $old['deskripsi'], $stock, $image, $productId);
$update->execute();

if ($newImage && str_starts_with((string) $existing['image'], 'uploads/')) {
    $oldImagePath = dirname(__DIR__, 2) . '/uploads/' . basename($existing['image']);
    if (is_file($oldImagePath)) unlink($oldImagePath);
}
$_SESSION['admin_message'] = 'Produk berhasil diperbarui.';
header('Location: ../index.php');
exit;