<?php
session_start();
require __DIR__ . '/koneksi_db.php';
require __DIR__ . '/functions.php';

$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$orderId || (int) ($_SESSION['last_order_id'] ?? 0) !== $orderId) {
    http_response_code(403);
    exit('Invoice hanya tersedia pada sesi checkout ini.');
}
$statement = $koneksi->prepare('SELECT * FROM customer_orders WHERE id = ?');
$statement->bind_param('i', $orderId);
$statement->execute();
$order = $statement->get_result()->fetch_assoc();
if (!$order) {
    http_response_code(404);
    exit('Pesanan tidak ditemukan. Pastikan orders.sql sudah dijalankan.');
}
$itemsStatement = $koneksi->prepare('SELECT product_name, unit_price, quantity, subtotal FROM customer_order_items WHERE order_id = ? ORDER BY id');
$itemsStatement->bind_param('i', $orderId);
$itemsStatement->execute();
$items = $itemsStatement->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Invoice #<?= (int) $order['id'] ?> | LNX.goods</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet"><link rel="stylesheet" href="css/style.css">
</head>
<body>
  <header class="site-header"><a class="brand" href="index.php"><img class="brand-logo" src="assets/icon/logo.jpg" alt=""><span>LИX<span class="brand-light">.goods</span></span></a><nav class="main-nav"><a href="index.php">Katalog</a></nav></header>
  <main class="invoice-shell"><p class="eyebrow">PESANAN TERSIMPAN</p><h1>Terima kasih.</h1><p class="invoice-reference">Invoice #<?= (int) $order['id'] ?> · <?= t9_escape($order['created_at']) ?></p>
    <section class="invoice-panel"><div class="invoice-recipient"><div><span>Penerima</span><strong><?= t9_escape($order['recipient_name']) ?></strong></div><div><span>Nomor telepon</span><strong><?= t9_escape($order['phone']) ?></strong></div><div><span>Alamat</span><strong><?= nl2br(t9_escape($order['address'])) ?></strong></div></div>
      <div class="invoice-lines"><?php foreach ($items as $item): ?><div><span><?= t9_escape($item['product_name']) ?> × <?= (int) $item['quantity'] ?><small>Rp<?= number_format((int) $item['unit_price'], 0, ',', '.') ?> / item</small></span><strong>Rp<?= number_format((int) $item['subtotal'], 0, ',', '.') ?></strong></div><?php endforeach; ?></div>
      <div class="summary-total"><span>Total pesanan</span><strong>Rp<?= number_format((int) $order['total_price'], 0, ',', '.') ?></strong></div>
    </section>
    <a class="submit-button invoice-back" href="index.php#produk">Kembali ke katalog <span aria-hidden="true">↗</span></a>
  </main>
</body>
</html>