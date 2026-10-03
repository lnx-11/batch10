<?php
$old = $old ?? [];
$errors = $errors ?? [];
$categories = ['Teknologi', 'Rumah', 'Gaya hidup', 'Aksesori'];
$escape = static fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#f6f5f0">
  <title>Input Produk | LNX.goods</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <header class="site-header">
    <a class="brand" href="index.php" aria-label="LNX.goods">
      <img class="brand-logo" src="assets/icon/logo.jpg" alt="">
      <span>LИX<span>.goods</span></span>
    </a>
  </header>

  <main class="page-shell">
    <section class="form-intro" aria-labelledby="page-title">
      <p class="eyebrow">KATALOG LИX.GOODS</p>
      <h1 id="page-title">Tambah produk.</h1>
      <p>Isi informasi produk untuk melihat hasilnya. Data hanya diproses sementara dan tidak disimpan ke database.</p>
    </section>

    <?php if ($errors): ?>
      <div class="alert" role="alert">
        <strong>Periksa kembali isian berikut.</strong>
        <p>Pastikan semua field terisi dan gambar menggunakan format yang didukung.</p>
      </div>
    <?php endif; ?>

    <form class="product-form" action="product_input_process.php" method="post" enctype="multipart/form-data" novalidate>
      <div class="field">
        <label for="nama_produk">Nama produk</label>
        <input id="nama_produk" name="nama_produk" type="text" maxlength="255" value="<?= $escape($old['nama_produk'] ?? '') ?>" placeholder="Contoh: Lampu Meja Arc" required>
        <?php if (isset($errors['nama_produk'])): ?><p class="field-error"><?= $escape($errors['nama_produk']) ?></p><?php endif; ?>
      </div>

      <div class="field-row">
        <div class="field">
          <label for="harga">Harga</label>
          <div class="input-prefix"><span>Rp</span><input id="harga" name="harga" type="number" min="1" step="1" value="<?= $escape($old['harga'] ?? '') ?>" placeholder="429000" required></div>
          <?php if (isset($errors['harga'])): ?><p class="field-error"><?= $escape($errors['harga']) ?></p><?php endif; ?>
        </div>

        <div class="field">
          <label for="stok">Stok</label>
          <input id="stok" name="stok" type="number" min="0" step="1" value="<?= $escape($old['stok'] ?? '') ?>" placeholder="10" required>
          <?php if (isset($errors['stok'])): ?><p class="field-error"><?= $escape($errors['stok']) ?></p><?php endif; ?>
        </div>
      </div>

      <div class="field">
        <label for="deskripsi">Deskripsi</label>
        <textarea id="deskripsi" name="deskripsi" rows="4" maxlength="2000" placeholder="Ceritakan produk secara singkat" required><?= $escape($old['deskripsi'] ?? '') ?></textarea>
        <?php if (isset($errors['deskripsi'])): ?><p class="field-error"><?= $escape($errors['deskripsi']) ?></p><?php endif; ?>
      </div>

      <div class="field">
        <label for="kategori">Kategori</label>
        <select id="kategori" name="kategori" required>
          <option value="">Pilih kategori</option>
          <?php foreach ($categories as $category): ?>
            <option value="<?= $escape($category) ?>" <?= ($old['kategori'] ?? '') === $category ? 'selected' : '' ?>><?= $escape($category) ?></option>
          <?php endforeach; ?>
        </select>
        <?php if (isset($errors['kategori'])): ?><p class="field-error"><?= $escape($errors['kategori']) ?></p><?php endif; ?>
      </div>

      <div class="field">
        <label for="gambar">Gambar produk</label>
        <input id="gambar" name="gambar" type="file" accept="image/jpeg,image/png,image/webp,image/gif" required>
        <p class="field-hint">JPG, PNG, WebP, atau GIF. Maksimal 2 MB.</p>
        <?php if (isset($errors['gambar'])): ?><p class="field-error"><?= $escape($errors['gambar']) ?></p><?php endif; ?>
      </div>

      <button class="submit-button" type="submit">Proses data <span aria-hidden="true">↗</span></button>
    </form>

    <p class="page-note">LNX.goods <span>·</span> Form input produk</p>
  </main>
</body>
</html>