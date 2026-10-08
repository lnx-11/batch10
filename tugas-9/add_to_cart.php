<?php
session_start();
require __DIR__ . '/koneksi_db.php';
require __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}
if (!t9_verify_csrf()) {
    http_response_code(403);
    exit('Token keamanan tidak valid. Muat ulang halaman dan coba kembali.');
}

$productId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$quantity = filter_var($_POST['quantity'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$productId || !$quantity) {
    $_SESSION['cart_message'] = 'Produk atau jumlah yang dipilih tidak valid.';
    header('Location: index.php');
    exit;
}

$statement = $koneksi->prepare('SELECT stok FROM products WHERE id = ?');
$statement->bind_param('i', $productId);
$statement->execute();
$product = $statement->get_result()->fetch_assoc();
if (!$product || (int) $product['stok'] < 1) {
    $_SESSION['cart_message'] = 'Produk tidak tersedia atau stoknya habis.';
    header('Location: index.php');
    exit;
}

$cart = $_SESSION['cart'] ?? [];
$currentQuantity = (int) ($cart[$productId] ?? 0);
$newQuantity = min($currentQuantity + $quantity, (int) $product['stok']);
$_SESSION['cart'][$productId] = $newQuantity;
$_SESSION['cart_message'] = $newQuantity < $currentQuantity + $quantity
    ? 'Jumlah ditambahkan sesuai stok yang tersedia.'
    : 'Produk berhasil ditambahkan ke keranjang.';

header('Location: keranjang.php');
exit;