<?php
session_start();
require __DIR__ . '/koneksi_db.php';
require __DIR__ . '/functions.php';

$items = t9_cart_items($koneksi);
if (!$items) {
    $_SESSION['cart_message'] = 'Tambahkan produk ke keranjang sebelum checkout.';
    header('Location: keranjang.php');
    exit;
}
$total = array_sum(array_column($items, 'subtotal'));
$errors = $_SESSION['checkout_errors'] ?? [];
$old = $_SESSION['checkout_old'] ?? ['recipient_name' => '', 'address' => '', 'phone' => ''];
unset($_SESSION['checkout_errors'], $_SESSION['checkout_old']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Checkout | LNX.goods</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet"><link rel="stylesheet" href="css/style.css">
</head>
<body>
  <header class="site-header"><a class="brand" href="index.php"><img class="brand-logo" src="assets/icon/logo.jpg" alt=""><span>LИX<span class="brand-light">.goods</span></span></a><nav class="main-nav"><a href="keranjang.php">Kembali ke keranjang</a></nav></header>
  <main class="checkout-shell"><div class="admin-heading"><div><p class="eyebrow">LANGKAH TERAKHIR</p><h1>Checkout.</h1><p>Pastikan data pengiriman sudah benar.</p></div></div>
    <?php if ($errors): ?><div class="alert" role="alert"><strong>Pesanan belum dapat diproses.</strong><ul><?php foreach ($errors as $error): ?><li><?= t9_escape($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <div class="checkout-layout"><form class="admin-form" action="process_order.php" method="post"><input type="hidden" name="csrf_token" value="<?= t9_escape(t9_csrf_token()) ?>"><h2>Data penerima</h2>
      <div class="field"><label for="recipient_name">Nama penerima</label><input id="recipient_name" name="recipient_name" maxlength="150" required value="<?= t9_escape($old['recipient_name'] ?? '') ?>"></div>
      <div class="field"><label for="address">Alamat lengkap</label><textarea id="address" name="address" rows="4" maxlength="2000" required><?= t9_escape($old['address'] ?? '') ?></textarea></div>
      <div class="field"><label for="phone">Nomor telepon</label><input id="phone" name="phone" type="tel" maxlength="30" required value="<?= t9_escape($old['phone'] ?? '') ?>"></div>
      <button class="submit-button" type="submit">Buat pesanan <span aria-hidden="true">↗</span></button>
    </form><aside class="cart-summary"><h2>Ringkasan pesanan</h2>
      <?php foreach ($items as $item): ?><div class="checkout-line"><span><?= t9_escape($item['product']['nama_produk']) ?> × <?= (int) $item['quantity'] ?></span><strong>Rp<?= number_format((int) $item['subtotal'], 0, ',', '.') ?></strong></div><?php endforeach; ?>
      <div class="summary-total"><span>Total</span><strong>Rp<?= number_format((int) $total, 0, ',', '.') ?></strong></div><a class="back-link" href="keranjang.php">← Ubah keranjang</a>
    </aside></div>
  </main>
</body>
</html>