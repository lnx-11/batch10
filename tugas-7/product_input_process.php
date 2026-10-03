<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: product_input_form.php');
    exit;
}

$categories = ['Teknologi', 'Rumah', 'Gaya hidup', 'Aksesori'];
$fields = ['nama_produk', 'harga', 'deskripsi', 'kategori', 'stok'];
$old = [];
$errors = [];

foreach ($fields as $field) {
    $old[$field] = trim((string) ($_POST[$field] ?? ''));
    if ($old[$field] === '') {
        $errors[$field] = 'Field ini wajib diisi.';
    }
}

if (strlen($old['nama_produk']) > 255) {
    $errors['nama_produk'] = 'Nama produk maksimal 255 karakter.';
}

$price = filter_var($old['harga'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($old['harga'] !== '' && $price === false) {
    $errors['harga'] = 'Harga harus berupa bilangan bulat lebih dari 0.';
}

$stock = filter_var($old['stok'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
if ($old['stok'] !== '' && $stock === false) {
    $errors['stok'] = 'Stok harus berupa bilangan bulat 0 atau lebih.';
}

if ($old['deskripsi'] !== '' && strlen($old['deskripsi']) > 2000) {
    $errors['deskripsi'] = 'Deskripsi maksimal 2000 karakter.';
}

if ($old['kategori'] !== '' && !in_array($old['kategori'], $categories, true)) {
    $errors['kategori'] = 'Pilih kategori yang tersedia.';
}

$image = $_FILES['gambar'] ?? null;
$imageData = '';
$imageMime = '';
$allowedImageTypes = [
    'image/jpeg' => 'JPG',
    'image/png' => 'PNG',
    'image/webp' => 'WebP',
    'image/gif' => 'GIF',
];

if (!$image || $image['error'] === UPLOAD_ERR_NO_FILE) {
    $errors['gambar'] = 'Gambar produk wajib dipilih.';
} elseif ($image['error'] !== UPLOAD_ERR_OK) {
    $errors['gambar'] = 'Gambar gagal diunggah. Silakan coba lagi.';
} elseif ($image['size'] > 2 * 1024 * 1024) {
    $errors['gambar'] = 'Ukuran gambar maksimal 2 MB.';
} elseif (!is_uploaded_file($image['tmp_name'])) {
    $errors['gambar'] = 'File gambar tidak valid.';
} else {
    $imageInfo = getimagesize($image['tmp_name']);
    $imageMime = $imageInfo['mime'] ?? '';

    if ($imageInfo === false || !isset($allowedImageTypes[$imageMime])) {
        $errors['gambar'] = 'Gunakan gambar JPG, PNG, WebP, atau GIF yang valid.';
    }
}

if ($errors) {
    http_response_code(422);
    require __DIR__ . '/product_input_form.php';
    exit;
}

$imageContents = file_get_contents($image['tmp_name']);
if ($imageContents === false) {
    http_response_code(422);
    $errors['gambar'] = 'Gambar tidak dapat diproses. Silakan pilih file lain.';
    require __DIR__ . '/product_input_form.php';
    exit;
}
$imageData = base64_encode($imageContents);
$escape = static fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#f6f5f0">
  <title>Hasil Input Produk | LNX.goods</title>
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
    <span class="task-label">TUGAS 7 <span aria-hidden="true">/</span> PHP DASAR</span>
  </header>

  <main class="page-shell result-shell">
    <section class="form-intro" aria-labelledby="page-title">
      <p class="eyebrow">DATA BERHASIL DIPROSES</p>
      <h1 id="page-title">Produk diterima.</h1>
      <p>Berikut data yang dikirim. Informasi ini hanya ditampilkan sementara dan tidak disimpan ke database.</p>
    </section>

    <section class="result-panel" aria-label="Ringkasan produk">
      <img class="result-image" src="data:<?= $escape($imageMime) ?>;base64,<?= $imageData ?>" alt="Gambar <?= $escape($old['nama_produk']) ?>">
      <dl class="product-details">
        <div><dt>Nama produk</dt><dd><?= $escape($old['nama_produk']) ?></dd></div>
        <div><dt>Harga</dt><dd>Rp<?= number_format($price, 0, ',', '.') ?></dd></div>
        <div><dt>Deskripsi</dt><dd><?= nl2br($escape($old['deskripsi'])) ?></dd></div>
        <div><dt>Kategori</dt><dd><?= $escape($old['kategori']) ?></dd></div>
        <div><dt>Stok</dt><dd><?= number_format($stock, 0, ',', '.') ?> unit</dd></div>
        <div><dt>Gambar</dt><dd><?= $escape($image['name']) ?> · <?= $escape($allowedImageTypes[$imageMime]) ?></dd></div>
      </dl>
    </section>

    <a class="back-link" href="product_input_form.php">← Input produk lain</a>
    <p class="page-note">LNX.goods <span>·</span> Form input produk</p>
  </main>
</body>
</html>