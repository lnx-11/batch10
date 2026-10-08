# Tugas 9 - CRUD dan Keranjang

## Menjalankan aplikasi

1. Jalankan Apache dan MySQL dari XAMPP.
2. Pastikan database `ecommerce_db` dari Tugas 6 sudah tersedia dan berisi tabel `products`.
3. Jalankan `orders.sql` sekali untuk membuat tabel `customer_orders` dan `customer_order_items`.
4. Buka `http://localhost/batch10/tugas-9/` untuk etalase.
5. Buka `http://localhost/batch10/tugas-9/products/` untuk mengelola produk.

Jangan impor ulang `tugas-6/ecommerce_db.sql` jika database sudah berisi data karena file tersebut menghapus lalu membuat ulang database. `orders.sql` Tugas 9 hanya menambah tabel pesanan dan tidak mengubah tabel `orders` dari Tugas 6.

Koneksi default memakai user MySQL `root` tanpa password. Sesuaikan `koneksi_db.php` jika konfigurasi XAMPP berbeda.