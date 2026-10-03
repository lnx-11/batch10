<?php
$selectedCategory = $selectedCategory ?? '';
$searchTerm = $searchTerm ?? '';
$searchVariants = $searchVariants ?? [];
$escape = $escape ?? (static fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'));

$searchConditions = [];
$searchParameters = [];

foreach ($searchVariants as $variant) {
  $searchConditions[] = '(nama_produk LIKE ? OR deskripsi LIKE ? OR image LIKE ?)';
  $pattern = '%' . $variant . '%';
  array_push($searchParameters, $pattern, $pattern, $pattern);
}

$whereConditions = [];
$parameters = [];

if ($selectedCategory !== '') {
  $whereConditions[] = 'kategori = ?';
  $parameters[] = $selectedCategory;
}

if ($searchConditions) {
  $whereConditions[] = '(' . implode(' OR ', $searchConditions) . ')';
  $parameters = array_merge($parameters, $searchParameters);
}

if ($whereConditions) {
  $statement = $koneksi->prepare(
    'SELECT nama_produk, kategori, harga, deskripsi, stok, image, label, color FROM products WHERE '
    . implode(' AND ', $whereConditions)
    . ' ORDER BY id ASC'
  );
  $statement->execute($parameters);
  $productResult = $statement->get_result();
} else {
    $productResult = $koneksi->query(
        'SELECT nama_produk, kategori, harga, deskripsi, stok, image, label, color FROM products ORDER BY id ASC'
    );
}

$products = $productResult->fetch_all(MYSQLI_ASSOC);
?>
<div class="product-grid" aria-live="polite">
  <?php foreach ($products as $index => $product): ?>
    <?php
    $productColor = is_string($product['color']) && preg_match('/^#[0-9a-fA-F]{6}$/', $product['color'])
        ? $product['color']
        : '#e6e7df';
    $imageName = basename((string) ($product['image'] ?? ''));
    ?>
    <article class="product-card" style="--product-color: <?= $escape($productColor) ?>; --card-order: <?= (int) $index ?>">
      <div class="product-image-frame">
        <img src="assets/images/<?= $escape($imageName) ?>" alt="<?= $escape($product['nama_produk']) ?>" loading="lazy">
        <?php if (!empty($product['label'])): ?>
          <span class="product-label"><?= $escape($product['label']) ?></span>
        <?php endif; ?>
      </div>
      <div class="product-details">
        <p class="product-category"><?= $escape($product['kategori']) ?></p>
        <h3><?= $escape($product['nama_produk']) ?></h3>
        <p class="product-description"><?= $escape($product['deskripsi']) ?></p>
        <strong class="product-price">Rp<?= number_format((int) $product['harga'], 0, ',', '.') ?></strong>
        <p class="product-stock">Stok <?= number_format((int) $product['stok'], 0, ',', '.') ?> unit</p>
      </div>
    </article>
  <?php endforeach; ?>
</div>
<?php if (!$products): ?>
  <p class="empty-state">Tidak ada produk yang cocok dengan filter ini.</p>
<?php endif; ?>