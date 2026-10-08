<?php
session_start();
require_once dirname(__DIR__, 2) . '/koneksi_db.php';
require_once dirname(__DIR__, 2) . '/functions.php';

$categories = t9_product_categories($koneksi);
$old = $old ?? [];
$errors = $errors ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambah Produk | LNX.goods</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet"><link rel="stylesheet" href="../../css/style.css?v=5">
</head>
<body>
  <header class="site-header"><a class="brand" href="../../index.php"><img class="brand-logo" src="../../assets/icon/logo.jpg" alt=""><span>LИX<span class="brand-light">.goods</span></span></a><nav class="main-nav"><a href="../index.php">Kelola produk</a><a href="../../index.php">Etalase</a></nav></header>
  <main class="admin-shell form-shell">
    <div class="admin-heading"><div><p class="eyebrow">TUGAS 9 · CREATE</p><h1>Tambah produk.</h1><p>Lengkapi data produk baru untuk katalog.</p></div></div>
    <?php if ($errors): ?><div class="alert" role="alert"><strong>Produk belum dapat disimpan.</strong><ul><?php foreach ($errors as $error): ?><li><?= t9_escape($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form class="admin-form" action="../crud_process/create.php" method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= t9_escape(t9_csrf_token()) ?>">
      <div class="field"><label for="nama_produk">Nama produk</label><input id="nama_produk" name="nama_produk" maxlength="255" required value="<?= t9_escape($old['nama_produk'] ?? '') ?>"></div>
      <div class="field"><label for="kategori">Kategori</label><select id="kategori" name="kategori" required><option value="">Pilih kategori</option><?php foreach ($categories as $category): ?><option value="<?= t9_escape($category) ?>" <?= ($old['kategori'] ?? '') === $category ? 'selected' : '' ?>><?= t9_escape($category) ?></option><?php endforeach; ?></select></div>
      <div class="field-row"><div class="field"><label for="harga">Harga</label><input id="harga" name="harga" type="number" min="1" step="1" required value="<?= t9_escape($old['harga'] ?? '') ?>"></div><div class="field"><label for="stok">Stok</label><input id="stok" name="stok" type="number" min="0" step="1" required value="<?= t9_escape($old['stok'] ?? '') ?>"></div></div>
      <div class="field"><label for="deskripsi">Deskripsi</label><textarea id="deskripsi" name="deskripsi" rows="4" maxlength="2000" required><?= t9_escape($old['deskripsi'] ?? '') ?></textarea></div>
      <div class="field"><label for="gambar">Gambar produk</label><input id="gambar" name="gambar" type="file" accept="image/jpeg,image/png,image/webp,image/gif" required><p class="field-hint">JPG, PNG, WebP, atau GIF. Maksimal 2 MB.</p></div>
      <div class="form-actions"><a class="action-button action-button-secondary" href="../index.php">Batal</a><button class="submit-button" type="submit">Simpan produk <span aria-hidden="true">↗</span></button></div>
    </form>
  </main>
</body>
</html>