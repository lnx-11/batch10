<?php
require __DIR__ . '/koneksi_db.php';

$escape = static fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$categoryResult = $koneksi->query(
    "SELECT DISTINCT kategori FROM products WHERE kategori IS NOT NULL AND kategori <> '' ORDER BY kategori"
);
$categories = [];

while ($categoryRow = $categoryResult->fetch_assoc()) {
    $categories[] = $categoryRow['kategori'];
}

$selectedCategory = isset($_GET['kategori']) && is_string($_GET['kategori'])
    ? trim($_GET['kategori'])
    : '';

if ($selectedCategory !== '' && !in_array($selectedCategory, $categories, true)) {
    $selectedCategory = '';
}

$searchTerm = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
$searchTerm = substr($searchTerm, 0, 100);
$keywordAliases = [
  'sepatu' => ['sneaker'],
  'sneaker' => ['sepatu'],
];
$searchVariants = $searchTerm !== ''
  ? array_values(array_unique([
    $searchTerm,
    ...($keywordAliases[mb_strtolower($searchTerm, 'UTF-8')] ?? []),
  ]))
  : [];
$querySummary = [];

if ($searchTerm !== '') {
  $querySummary[] = 'Pencarian: ' . $searchTerm;
}
if ($selectedCategory !== '') {
  $querySummary[] = 'Kategori: ' . $selectedCategory;
}

$allProductsUrl = 'home.php'
  . ($searchTerm !== '' ? '?' . http_build_query(['q' => $searchTerm]) : '')
  . '#produk';
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
  <title>LИX.goods | Katalog Produk</title>
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
    <a class="brand" href="home.php" aria-label="LNX.goods, ke beranda">
      <img class="brand-logo" src="assets/icon/logo.jpg" alt="">
      <span>LИX<span class="brand-light">.goods</span></span>
    </a>
    <nav class="main-nav" aria-label="Navigasi utama">
      <a href="#produk" aria-current="page">Katalog</a>
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
      <div class="hero-visual">
        <img src="assets/images/hero-workspace.jpg" alt="Ruang kerja dengan koleksi barang pilihan LNX.goods">
        <div class="hero-stamp"><span>DIRANCANG</span><strong>untuk<br>digunakan</strong><span>SETIAP HARI</span></div>
      </div>
      <div class="hero-side-note">KATALOG PRODUK · 2026</div>
    </section>

    <section class="catalog" id="produk" aria-labelledby="catalog-title">
      <div class="catalog-heading">
        <div>
          <p class="eyebrow">KATALOG LИX.GOODS</p>
          <h2 id="catalog-title">Barang yang tepat.</h2>
        </div>
        <p class="result-count" aria-live="polite"><?= $querySummary ? $escape(implode(' · ', $querySummary)) : 'Semua kategori' ?></p>
      </div>

      <div class="catalog-tools">
        <nav class="category-list" aria-label="Filter kategori">
          <a class="category-button" href="<?= $escape($allProductsUrl) ?>" <?= $selectedCategory === '' ? 'aria-current="true"' : '' ?>>Semua</a>
          <?php foreach ($categories as $category): ?>
            <?php $categoryUrl = 'home.php?' . http_build_query(['kategori' => $category, 'q' => $searchTerm]) . '#produk'; ?>
            <a class="category-button" href="<?= $escape($categoryUrl) ?>" <?= $selectedCategory === $category ? 'aria-current="true"' : '' ?>><?= $escape($category) ?></a>
          <?php endforeach; ?>
        </nav>

        <form class="search-form" action="home.php#produk" method="get" role="search">
          <label class="visually-hidden" for="search-products">Cari produk</label>
          <input id="search-products" class="search-input" type="search" name="q" value="<?= $escape($searchTerm) ?>" placeholder="Cari nama atau deskripsi produk">
          <?php if ($selectedCategory !== ''): ?>
            <input type="hidden" name="kategori" value="<?= $escape($selectedCategory) ?>">
          <?php endif; ?>
          <button class="search-button" type="submit">Cari</button>
        </form>
      </div>

      <div id="product-results" aria-live="polite">
        <?php require __DIR__ . '/catalog.php'; ?>
      </div>
    </section>
  </main>

  <footer class="site-footer">
    <a class="brand" href="home.php" aria-label="LNX.goods, kembali ke atas">
      <img class="brand-logo" src="assets/icon/logo.jpg" alt="">
      <span>LИX<span class="brand-light">.goods</span></span>
    </a>
    <p>Objek yang baik. Hari yang lebih ringan.</p>
    <span>© 2026 LИX.GOODS</span>
  </footer>
</body>
</html>