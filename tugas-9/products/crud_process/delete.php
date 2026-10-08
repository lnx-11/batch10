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
    exit('Token keamanan tidak valid. Muat ulang halaman dan coba kembali.');
}

$productId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$productId) {
    http_response_code(400);
    exit('ID produk tidak valid.');
}

$find = $koneksi->prepare('SELECT image FROM products WHERE id = ?');
$find->bind_param('i', $productId);
$find->execute();
$product = $find->get_result()->fetch_assoc();
if (!$product) {
    $_SESSION['admin_message'] = 'Produk sudah tidak tersedia.';
    header('Location: ../index.php');
    exit;
}

$usage = $koneksi->prepare(
    'SELECT (SELECT COUNT(*) FROM orders WHERE product_id = ?) + '
    . '(SELECT COUNT(*) FROM customer_order_items WHERE product_id = ?) AS total'
);
$usage->bind_param('ii', $productId, $productId);
$usage->execute();
if ((int) $usage->get_result()->fetch_assoc()['total'] > 0) {
    $_SESSION['admin_message'] = 'Produk tidak dapat dihapus karena sudah tercatat pada transaksi.';
    header('Location: ../index.php');
    exit;
}

$delete = $koneksi->prepare('DELETE FROM products WHERE id = ?');
$delete->bind_param('i', $productId);
$delete->execute();
if (str_starts_with((string) $product['image'], 'uploads/')) {
    $imagePath = dirname(__DIR__, 2) . '/uploads/' . basename($product['image']);
    if (is_file($imagePath)) unlink($imagePath);
}
$_SESSION['admin_message'] = 'Produk berhasil dihapus.';
header('Location: ../index.php');
exit;