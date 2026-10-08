<?php
session_start();
require __DIR__ . '/koneksi_db.php';
require __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!t9_verify_csrf()) {
        http_response_code(403);
        exit('Token keamanan tidak valid. Muat ulang halaman dan coba kembali.');
    }
    $action = $_POST['action'] ?? '';
    $productId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($action === 'clear') {
        $_SESSION['cart'] = [];
        $_SESSION['cart_message'] = 'Keranjang berhasil dikosongkan.';
    } elseif ($productId && $action === 'remove') {
        unset($_SESSION['cart'][$productId]);
        $_SESSION['cart_message'] = 'Produk dihapus dari keranjang.';
    } elseif ($productId && $action === 'update') {
        $quantity = filter_var($_POST['quantity'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        if ($quantity === false || $quantity === 0) {
            unset($_SESSION['cart'][$productId]);
            $_SESSION['cart_message'] = 'Produk dihapus dari keranjang.';
        } else {
            $find = $koneksi->prepare('SELECT stok FROM products WHERE id = ?');
            $find->bind_param('i', $productId);
            $find->execute();
            $product = $find->get_result()->fetch_assoc();
            if (!$product || (int) $product['stok'] < 1) {
                unset($_SESSION['cart'][$productId]);
                $_SESSION['cart_message'] = 'Produk tidak tersedia dan dihapus dari keranjang.';
            } else {
                $_SESSION['cart'][$productId] = min($quantity, (int) $product['stok']);
                $_SESSION['cart_message'] = 'Jumlah produk diperbarui.';
            }
        }
    }
    header('Location: keranjang.php');
    exit;
}

$items = t9_cart_items($koneksi);
$total = array_sum(array_column($items, 'subtotal'));
$totalItems = array_sum(array_column($items, 'quantity'));
$message = $_SESSION['cart_message'] ?? '';
unset($_SESSION['cart_message']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Keranjang | LNX.goods</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet"><link rel="stylesheet" href="css/style.css">
</head>
<body>
  <header class="site-header"><a class="brand" href="index.php"><img class="brand-logo" src="assets/icon/logo.jpg" alt=""><span>LИX<span class="brand-light">.goods</span></span></a><nav class="main-nav"><a href="index.php#produk">Lanjut belanja</a><a href="products/index.php">Kelola produk</a></nav></header>
  <main class="cart-shell"><div class="admin-heading"><div><p class="eyebrow">RINGKASAN BELANJA</p><h1>Keranjang.</h1><p><?= (int) $totalItems ?> item</p></div></div>
    <?php if ($message !== ''): ?><p class="notice" role="status"><?= t9_escape($message) ?></p><?php endif; ?>
    <?php if ($items): ?>
      <div class="cart-layout"><section class="cart-list" aria-label="Produk dalam keranjang">
        <?php foreach ($items as $item): $product = $item['product']; ?>
          <article class="cart-item"><img src="<?= t9_escape(t9_image_url($product['image'])) ?>" alt="<?= t9_escape($product['nama_produk']) ?>"><div class="cart-item-info"><a href="detail_product.php?id=<?= (int) $product['id'] ?>"><strong><?= t9_escape($product['nama_produk']) ?></strong></a><span><?= t9_escape($product['kategori']) ?></span><span>Rp<?= number_format((int) $product['harga'], 0, ',', '.') ?></span></div><form class="quantity-form" action="keranjang.php" method="post"><input type="hidden" name="csrf_token" value="<?= t9_escape(t9_csrf_token()) ?>"><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= (int) $product['id'] ?>"><label class="visually-hidden" for="quantity-<?= (int) $product['id'] ?>">Jumlah <?= t9_escape($product['nama_produk']) ?></label><input id="quantity-<?= (int) $product['id'] ?>" name="quantity" type="number" min="0" max="<?= (int) $product['stok'] ?>" value="<?= (int) $item['quantity'] ?>"><button type="submit" title="Perbarui jumlah">Perbarui</button></form><strong class="cart-subtotal">Rp<?= number_format((int) $item['subtotal'], 0, ',', '.') ?></strong><form action="keranjang.php" method="post"><input type="hidden" name="csrf_token" value="<?= t9_escape(t9_csrf_token()) ?>"><input type="hidden" name="action" value="remove"><input type="hidden" name="id" value="<?= (int) $product['id'] ?>"><button class="text-button text-danger" type="submit">Hapus</button></form></article>
        <?php endforeach; ?>
        <form action="keranjang.php" method="post" class="clear-cart-form"><input type="hidden" name="csrf_token" value="<?= t9_escape(t9_csrf_token()) ?>"><input type="hidden" name="action" value="clear"><button class="text-button text-danger" type="submit">Kosongkan keranjang</button></form>
      </section><aside class="cart-summary"><h2>Total belanja</h2><div><span>Jumlah item</span><strong><?= (int) $totalItems ?></strong></div><div class="summary-total"><span>Total</span><strong>Rp<?= number_format((int) $total, 0, ',', '.') ?></strong></div><a class="submit-button checkout-link" href="checkout.php">Lanjut checkout <span aria-hidden="true">↗</span></a></aside></div>
    <?php else: ?><div class="empty-cart"><h2>Keranjang masih kosong.</h2><p>Temukan produk yang ingin kamu bawa pulang.</p><a class="submit-button" href="index.php#produk">Lihat katalog <span aria-hidden="true">↗</span></a></div><?php endif; ?>
  </main>
</body>
</html>