<?php
session_start();
require __DIR__ . '/koneksi_db.php';
require __DIR__ . '/functions.php';

$productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$productId) {
    http_response_code(400);
    exit('ID produk tidak valid.');
}
$statement = $koneksi->prepare('SELECT * FROM products WHERE id = ?');
$statement->bind_param('i', $productId);
$statement->execute();
$product = $statement->get_result()->fetch_assoc();
if (!$product) {
    http_response_code(404);
    exit('Produk tidak ditemukan.');
}
$cartCount = array_sum($_SESSION['cart'] ?? []);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?= t9_escape($product['nama_produk']) ?> | LNX.goods</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet"><link rel="stylesheet" href="css/style.css">
</head>
<body>
  <header class="site-header"><a class="brand" href="index.php"><img class="brand-logo" src="assets/icon/logo.jpg" alt=""><span>LИX<span class="brand-light">.goods</span></span></a><nav class="main-nav"><a href="index.php#produk">Katalog</a><a href="keranjang.php">Keranjang (<?= (int) $cartCount ?>)</a></nav></header>
  <main class="detail-shell"><a class="back-link" href="index.php#produk">← Kembali ke katalog</a><article class="detail-layout"><div class="detail-image"><img src="<?= t9_escape(t9_image_url($product['image'])) ?>" alt="<?= t9_escape($product['nama_produk']) ?>"></div><div class="detail-copy"><p class="product-category"><?= t9_escape($product['kategori']) ?></p><h1><?= t9_escape($product['nama_produk']) ?></h1><p class="detail-price">Rp<?= number_format((int) $product['harga'], 0, ',', '.') ?></p><p class="detail-description"><?= nl2br(t9_escape($product['deskripsi'])) ?></p><p class="product-stock"><?= (int) $product['stok'] > 0 ? 'Stok ' . number_format((int) $product['stok'], 0, ',', '.') . ' unit' : 'Stok habis' ?></p>
    <?php if ((int) $product['stok'] > 0): ?><form action="add_to_cart.php" method="post" class="detail-cart-form"><input type="hidden" name="csrf_token" value="<?= t9_escape(t9_csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int) $product['id'] ?>"><label for="quantity">Jumlah</label><input id="quantity" name="quantity" type="number" min="1" max="<?= (int) $product['stok'] ?>" value="1" required><button class="submit-button" type="submit">Tambah ke keranjang <span aria-hidden="true">+</span></button></form><?php endif; ?>
  </div></article></main>
</body>
</html>