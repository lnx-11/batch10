<?php
$selectedCategory = $selectedCategory ?? '';
$searchVariants = $searchVariants ?? [];
$escape = $escape ?? (static fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'));

$conditions = [];
$parameters = [];
if ($selectedCategory !== '') {
    $conditions[] = 'kategori = ?';
    $parameters[] = $selectedCategory;
}

$searchConditions = [];
foreach ($searchVariants as $variant) {
    $searchConditions[] = '(nama_produk LIKE ? OR deskripsi LIKE ? OR image LIKE ?)';
    $pattern = '%' . $variant . '%';
    array_push($parameters, $pattern, $pattern, $pattern);
}
if ($searchConditions) {
    $conditions[] = '(' . implode(' OR ', $searchConditions) . ')';
}

$query = 'SELECT id, nama_produk, kategori, harga, deskripsi, stok, image, label, color FROM products';
if ($conditions) {
    $statement = $koneksi->prepare($query . ' WHERE ' . implode(' AND ', $conditions) . ' ORDER BY id DESC');
    $statement->execute($parameters);
    $products = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $products = $koneksi->query($query . ' ORDER BY id DESC')->fetch_all(MYSQLI_ASSOC);
}
?>
<?php if ($products): ?>
  <div class="product-grid">
    <?php foreach ($products as $index => $product):
        $color = is_string($product['color']) && preg_match('/^#[0-9a-fA-F]{6}$/', $product['color']) ? $product['color'] : '#e6e7df';
        $image = t9_image_url($product['image']);
    ?>
      <article class="product-card" style="--product-color: <?= $escape($color) ?>; --card-order: <?= (int) $index ?>">
        <div class="product-image-frame"><a href="detail_product.php?id=<?= (int) $product['id'] ?>"><img src="<?= $escape($image) ?>" alt="<?= $escape($product['nama_produk']) ?>" loading="lazy"></a><?php if ($product['label']): ?><span class="product-label"><?= $escape($product['label']) ?></span><?php endif; ?></div>
        <div class="product-details"><p class="product-category"><?= $escape($product['kategori']) ?></p><h3><a href="detail_product.php?id=<?= (int) $product['id'] ?>"><?= $escape($product['nama_produk']) ?></a></h3><p class="product-description"><?= $escape($product['deskripsi']) ?></p><strong class="product-price">Rp<?= number_format((int) $product['harga'], 0, ',', '.') ?></strong><p class="product-stock"><?= (int) $product['stok'] > 0 ? 'Stok ' . number_format((int) $product['stok'], 0, ',', '.') . ' unit' : 'Stok habis' ?></p>
          <?php if ((int) $product['stok'] > 0): ?><form action="add_to_cart.php" method="post" class="card-cart-form"><input type="hidden" name="csrf_token" value="<?= $escape(t9_csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int) $product['id'] ?>"><input type="hidden" name="quantity" value="1"><button class="submit-button" type="submit">Tambah ke keranjang <span aria-hidden="true">+</span></button></form><?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <p class="empty-state">Produk tidak ditemukan. Coba kata kunci atau kategori lain.</p>
<?php endif; ?>