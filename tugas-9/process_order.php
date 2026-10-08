<?php
session_start();
require __DIR__ . '/koneksi_db.php';
require __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: keranjang.php');
    exit;
}
if (!t9_verify_csrf()) {
    http_response_code(403);
    exit('Token keamanan tidak valid. Muat ulang halaman dan coba kembali.');
}

$old = [
    'recipient_name' => trim((string) ($_POST['recipient_name'] ?? '')),
    'address' => trim((string) ($_POST['address'] ?? '')),
    'phone' => trim((string) ($_POST['phone'] ?? '')),
];
$errors = [];
if ($old['recipient_name'] === '' || strlen($old['recipient_name']) > 150) $errors[] = 'Nama penerima wajib diisi dan maksimal 150 karakter.';
if ($old['address'] === '' || strlen($old['address']) > 2000) $errors[] = 'Alamat wajib diisi dan maksimal 2000 karakter.';
if (!preg_match('/^[0-9+()\s-]{8,30}$/', $old['phone'])) $errors[] = 'Nomor telepon harus berisi 8–30 karakter angka atau tanda telepon.';
if (empty($_SESSION['cart']) || !is_array($_SESSION['cart'])) $errors[] = 'Keranjang masih kosong.';

if ($errors) {
    $_SESSION['checkout_errors'] = $errors;
    $_SESSION['checkout_old'] = $old;
    header('Location: checkout.php');
    exit;
}

$cart = $_SESSION['cart'];
$ids = [];
foreach ($cart as $id => $quantity) {
    $id = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $quantity = filter_var($quantity, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id !== false && $quantity !== false) $ids[$id] = $quantity;
}
if (!$ids) {
    $_SESSION['cart'] = [];
    $_SESSION['checkout_errors'] = ['Keranjang tidak berisi produk yang valid.'];
    header('Location: keranjang.php');
    exit;
}

$transactionStarted = false;
try {
    $koneksi->begin_transaction();
    $transactionStarted = true;
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $lock = $koneksi->prepare("SELECT id, nama_produk, harga, stok FROM products WHERE id IN ($placeholders) FOR UPDATE");
    $lock->execute(array_keys($ids));
    $products = $lock->get_result()->fetch_all(MYSQLI_ASSOC);
    $productMap = [];
    foreach ($products as $product) $productMap[(int) $product['id']] = $product;

    $orderItems = [];
    $total = 0;
    foreach ($ids as $productId => $quantity) {
        if (!isset($productMap[$productId])) throw new RuntimeException('Salah satu produk sudah tidak tersedia.');
        $product = $productMap[$productId];
        if ((int) $product['stok'] < $quantity) throw new RuntimeException('Stok ' . $product['nama_produk'] . ' tidak mencukupi. Perbarui jumlah di keranjang.');
        $subtotal = (int) $product['harga'] * $quantity;
        $total += $subtotal;
        $orderItems[] = [$productId, $product['nama_produk'], (int) $product['harga'], $quantity, $subtotal];
    }

    $createOrder = $koneksi->prepare(
        'INSERT INTO customer_orders (recipient_name, address, phone, total_price) VALUES (?, ?, ?, ?)'
    );
    $totalString = (string) $total;
    $createOrder->bind_param('ssss', $old['recipient_name'], $old['address'], $old['phone'], $totalString);
    $createOrder->execute();
    $orderId = $koneksi->insert_id;

    $createItem = $koneksi->prepare(
        'INSERT INTO customer_order_items (order_id, product_id, product_name, unit_price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?)'
    );
    $reduceStock = $koneksi->prepare('UPDATE products SET stok = stok - ? WHERE id = ? AND stok >= ?');
    foreach ($orderItems as [$productId, $name, $price, $quantity, $subtotal]) {
        $orderIdString = (string) $orderId;
        $subtotalString = (string) $subtotal;
        $createItem->bind_param('sisiis', $orderIdString, $productId, $name, $price, $quantity, $subtotalString);
        $createItem->execute();
        $reduceStock->bind_param('iii', $quantity, $productId, $quantity);
        $reduceStock->execute();
        if ($reduceStock->affected_rows !== 1) throw new RuntimeException('Stok berubah saat checkout. Silakan periksa kembali keranjang.');
    }

    $koneksi->commit();
    $transactionStarted = false;
    $_SESSION['cart'] = [];
    $_SESSION['last_order_id'] = $orderId;
    header('Location: invoice.php?id=' . $orderId);
    exit;
} catch (Throwable $exception) {
    if ($transactionStarted) {
        try { $koneksi->rollback(); } catch (Throwable $rollbackException) { }
    }
    $_SESSION['checkout_errors'] = [$exception instanceof RuntimeException ? $exception->getMessage() : 'Pesanan gagal disimpan. Jalankan orders.sql dan coba kembali.'];
    $_SESSION['checkout_old'] = $old;
    header('Location: checkout.php');
    exit;
}