<?php
session_start();
require dirname(__DIR__) . '/koneksi_db.php';
require dirname(__DIR__) . '/functions.php';

$products = $koneksi->query(
    'SELECT id, nama_produk, kategori, harga, stok, image FROM products ORDER BY id DESC'
)->fetch_all(MYSQLI_ASSOC);
$cartCount = array_sum($_SESSION['cart'] ?? []);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Produk | LNX.goods</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css?v=4">
</head>
<body>
  <header class="site-header"><a class="brand" href="../index.php"><img class="brand-logo" src="../assets/icon/logo.jpg" alt=""><span>LИX<span class="brand-light">.goods</span></span></a><nav class="main-nav"><a href="../index.php">Etalase</a><a href="../keranjang.php">Keranjang (<?= (int) $cartCount ?>)</a></nav></header>
  <main class="admin-shell">
    <div class="admin-heading">
      <div><p class="eyebrow">TUGAS 9 · CRUD</p><h1>Kelola produk.</h1><p>Produk tersimpan di database ecommerce_db.</p></div>
      <div class="admin-heading-actions">
        <a class="submit-button admin-add" href="form/create_form.php">Tambah produk <span aria-hidden="true">+</span></a>
        <a class="action-button action-button-secondary" href="../index.php"><span aria-hidden="true">←</span> Kembali ke etalase</a>
      </div>
    </div>
    <?php if (!empty($_SESSION['admin_message'])): ?><p class="notice" role="status"><?= t9_escape($_SESSION['admin_message']) ?></p><?php unset($_SESSION['admin_message']); endif; ?>
    <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Aksi</th></tr></thead><tbody>
      <?php foreach ($products as $product): ?>
        <tr><td><div class="table-product"><img src="../<?= t9_escape(t9_image_url($product['image'], '')) ?>" alt=""><span><?= t9_escape($product['nama_produk']) ?></span></div></td><td><?= t9_escape($product['kategori']) ?></td><td>Rp<?= number_format((int) $product['harga'], 0, ',', '.') ?></td><td><?= number_format((int) $product['stok'], 0, ',', '.') ?></td><td class="table-actions"><a class="action-button action-button-small action-button-secondary" href="form/edit_form.php?id=<?= (int) $product['id'] ?>">Edit</a><form action="crud_process/delete.php" method="post" onsubmit="return confirm('Hapus produk ini?')"><input type="hidden" name="csrf_token" value="<?= t9_escape(t9_csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int) $product['id'] ?>"><button class="action-button action-button-small action-button-danger" type="submit">Hapus</button></form></td></tr>
      <?php endforeach; ?>
      <?php if (!$products): ?><tr><td colspan="5" class="empty-table">Belum ada produk.</td></tr><?php endif; ?>
    </tbody></table></div>
  </main>
</body>
</html>