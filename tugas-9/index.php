<?php
session_start();
require __DIR__ . '/koneksi_db.php';
require __DIR__ . '/functions.php';

$categories = t9_product_categories($koneksi);
$selectedCategory = isset($_GET['kategori']) && is_string($_GET['kategori']) ? trim($_GET['kategori']) : '';
if (!in_array($selectedCategory, $categories, true)) {
    $selectedCategory = '';
}

$searchTerm = isset($_GET['q']) && is_string($_GET['q']) ? substr(trim($_GET['q']), 0, 100) : '';
$keywordAliases = ['sepatu' => ['sneaker'], 'sneaker' => ['sepatu']];
$searchVariants = $searchTerm === '' ? [] : array_values(array_unique([
    $searchTerm,
    ...($keywordAliases[mb_strtolower($searchTerm, 'UTF-8')] ?? []),
]));
$cartCount = array_sum($_SESSION['cart'] ?? []);
$filterSummary = [];
if ($searchTerm !== '') $filterSummary[] = 'Pencarian: ' . $searchTerm;
if ($selectedCategory !== '') $filterSummary[] = 'Kategori: ' . $selectedCategory;

if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    require __DIR__ . '/catalog.php';
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#f6f5f0">
  <title>LИX.goods | Toko Pilihan</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <link rel="icon" type="image/jpeg" href="assets/icon/logo.jpg">
  <script src="js/search.js" defer></script>
</head>
<body>
  <div class="announcement">Koleksi pilihan untuk hari-hari lebih baik <span aria-hidden="true">✳</span> Belanja dengan nyaman</div>
  <header class="site-header">
    <a class="brand" href="index.php" aria-label="LNX.goods, ke beranda"><img class="brand-logo" src="assets/icon/logo.jpg" alt=""><span>LИX<span class="brand-light">.goods</span></span></a>
    <nav class="main-nav" aria-label="Navigasi utama">
      <a href="#produk">Katalog</a>
      <a href="keranjang.php">Keranjang<?= $cartCount ? ' (' . (int) $cartCount . ')' : '' ?></a>
      <a href="products/index.php">Kelola produk</a>
    </nav>
  </header>

  <main id="beranda">
    <section class="hero" aria-labelledby="hero-title">
      <div class="hero-copy">
        <p class="eyebrow"><span class="eyebrow-dot"></span> OBJEK PILIHAN, HARI-HARI LEBIH BAIK</p>
        <h1 id="hero-title">Temukan hal kecil yang membuat <em>beda.</em></h1>
        <p class="hero-description">Barang fungsional dengan desain yang dipikirkan matang. Dipilih untuk menemani ritme harianmu.</p>
        <a class="hero-link" href="#produk">Jelajahi koleksi <span aria-hidden="true">↘</span></a>
      </div>
      <div class="hero-visual"><img src="assets/images/hero-workspace.jpg" alt="Ruang kerja dengan koleksi barang pilihan LNX.goods"><div class="hero-stamp"><span>DIRANCANG</span><strong>untuk<br>digunakan</strong><span>SETIAP HARI</span></div></div>
      <div class="hero-side-note">KATALOG PRODUK · 2026</div>
    </section>

    <section class="catalog" id="produk" aria-labelledby="catalog-title">
      <div class="catalog-heading"><div><p class="eyebrow">KATALOG LИX.GOODS</p><h2 id="catalog-title">Barang yang tepat.</h2></div><p class="result-count" aria-live="polite"><?= $filterSummary ? t9_escape(implode(' · ', $filterSummary)) : 'Semua kategori' ?></p></div>
      <?php if (!empty($_SESSION['cart_message'])): ?><p class="notice" role="status"><?= t9_escape($_SESSION['cart_message']) ?></p><?php unset($_SESSION['cart_message']); endif; ?>
      <div class="catalog-tools">
        <nav class="category-list" aria-label="Filter kategori">
          <?php $allParams = $searchTerm !== '' ? ['q' => $searchTerm] : []; ?>
          <a class="category-button" href="index.php<?= $allParams ? '?' . http_build_query($allParams) : '' ?>#produk" <?= $selectedCategory === '' ? 'aria-current="true"' : '' ?>>Semua</a>
          <?php foreach ($categories as $category): $filterParams = array_filter(['kategori' => $category, 'q' => $searchTerm], static fn ($value) => $value !== ''); ?>
            <a class="category-button" href="index.php?<?= t9_escape(http_build_query($filterParams)) ?>#produk" <?= $selectedCategory === $category ? 'aria-current="true"' : '' ?>><?= t9_escape($category) ?></a>
          <?php endforeach; ?>
        </nav>
        <form class="search-form" action="index.php#produk" method="get" role="search">
          <label class="visually-hidden" for="search-products">Cari produk</label>
          <input id="search-products" class="search-input" type="search" name="q" value="<?= t9_escape($searchTerm) ?>" placeholder="Cari nama atau deskripsi produk">
          <?php if ($selectedCategory !== ''): ?><input type="hidden" name="kategori" value="<?= t9_escape($selectedCategory) ?>"><?php endif; ?>
          <button class="search-button" type="submit">Cari</button>
        </form>
      </div>

      <div id="product-results" aria-live="polite"><?php require __DIR__ . '/catalog.php'; ?></div>
    </section>
  </main>
  <footer class="site-footer"><a class="brand" href="index.php"><img class="brand-logo" src="assets/icon/logo.jpg" alt=""><span>LИX<span class="brand-light">.goods</span></span></a><p>Objek yang baik. Hari yang lebih ringan.</p><span>© 2026 LИX.GOODS</span></footer>
</body>
</html>