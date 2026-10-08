<?php
function t9_escape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function t9_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function t9_verify_csrf(): bool
{
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && is_string($_POST['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

function t9_product_categories(mysqli $koneksi): array
{
    $result = $koneksi->query(
        "SELECT DISTINCT kategori FROM products WHERE kategori IS NOT NULL AND kategori <> '' ORDER BY kategori"
    );

    return array_column($result->fetch_all(MYSQLI_ASSOC), 'kategori');
}

function t9_cart_items(mysqli $koneksi): array
{
    $cart = $_SESSION['cart'] ?? [];
    if (!is_array($cart) || !$cart) {
        return [];
    }

    $quantities = [];
    foreach ($cart as $productId => $quantity) {
        $productId = filter_var($productId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $quantity = filter_var($quantity, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($productId !== false && $quantity !== false) {
            $quantities[$productId] = $quantity;
        }
    }

    if (!$quantities) {
        $_SESSION['cart'] = [];
        return [];
    }

    $ids = array_keys($quantities);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $statement = $koneksi->prepare(
        "SELECT id, nama_produk, kategori, harga, deskripsi, stok, image FROM products WHERE id IN ($placeholders)"
    );
    $statement->execute($ids);
    $products = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
    $items = [];

    foreach ($products as $product) {
        $id = (int) $product['id'];
        $quantity = $quantities[$id];
        $subtotal = (int) $product['harga'] * $quantity;
        $items[] = [
            'product' => $product,
            'quantity' => $quantity,
            'subtotal' => $subtotal,
        ];
    }

    return $items;
}

function t9_image_url(?string $image, string $prefix = ''): string
{
    $filename = basename((string) $image);
    if ($filename === '' || $filename === '.') {
        return $prefix . 'assets/images/hero-workspace.jpg';
    }

    return str_starts_with((string) $image, 'uploads/')
        ? $prefix . 'uploads/' . rawurlencode($filename)
        : $prefix . 'assets/images/' . rawurlencode($filename);
}

function t9_save_uploaded_image(array $file, bool $required = false): ?string
{
    $uploadError = $file['error'] ?? UPLOAD_ERR_NO_FILE;
    if ($uploadError === UPLOAD_ERR_NO_FILE) {
        if ($required) {
            throw new InvalidArgumentException('Gambar produk wajib dipilih.');
        }
        return null;
    }

    if ($uploadError !== UPLOAD_ERR_OK || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        throw new InvalidArgumentException('Gambar gagal diunggah. Silakan pilih file kembali.');
    }
    if ((int) ($file['size'] ?? 0) > 2 * 1024 * 1024) {
        throw new InvalidArgumentException('Ukuran gambar maksimal 2 MB.');
    }

    $imageInfo = getimagesize($file['tmp_name']);
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];
    $mime = $imageInfo['mime'] ?? '';
    if ($imageInfo === false || !isset($extensions[$mime])) {
        throw new InvalidArgumentException('Gunakan gambar JPG, PNG, WebP, atau GIF yang valid.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    $uploadDirectory = __DIR__ . '/uploads';
    if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0775, true) && !is_dir($uploadDirectory)) {
        throw new RuntimeException('Folder upload tidak dapat disiapkan.');
    }
    if (!move_uploaded_file($file['tmp_name'], $uploadDirectory . '/' . $filename)) {
        throw new RuntimeException('Gambar tidak dapat disimpan ke folder upload.');
    }

    return 'uploads/' . $filename;
}