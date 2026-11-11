# Panduan Belajar Project LИX.goods

Panduan ini menjelaskan susunan dan alur utama kode aplikasi toko online
`ecommerce10`. Tujuannya sebagai catatan belajar dan persiapan ujian. Panduan
berfokus pada kode aplikasi yang dibuat untuk project ini; folder dependency,
cache, file hasil build, gambar, dan nilai rahasia di `.env` tidak dijelaskan
baris demi baris.

## 1. Gambaran umum

Project ini menggunakan Laravel 12 dan PHP 8.2. Fitur aplikasi yang sudah
terlihat di source meliputi:

- Halaman beranda dan katalog produk.
- Login, registrasi, pengelolaan profil, dan autentikasi Laravel Breeze.
- Keranjang belanja yang tersimpan per pengguna di database.
- Pengelolaan produk dan kategori khusus admin.
- Dashboard berisi ringkasan toko dan grafik.
- Pengujian otomatis dengan Pest.

### Alur dasar Laravel<?php
public function store(Request $request): RedirectResponse|JsonResponse
{
    dd($request->except('_token'));

    $validated = $request->validate([
        // aturan validasi yang sudah ada
    ]);

    // proses penyimpanan
}

```text
Browser
  -> route di routes/
  -> middleware (misalnya auth dan admin)
  -> controller
  -> model / database
  -> Blade view atau JSON
  -> response ke browser
```

Contoh: ketika admin mengirim form produk, route `product.store` menerima
request `POST`, Laravel memastikan pengguna adalah admin, `ProductController`
memvalidasi data dan menyimpannya melalui model `Product`, lalu mengembalikan
redirect atau JSON.

## 2. Istilah penting

| Istilah | Penjelasan sederhana |
|---|---|
| Route | Aturan yang memasangkan URL dan method HTTP dengan aksi aplikasi. |
| Controller | Tempat menangani request dan mengatur alur fitur. |
| Model | Representasi tabel database dan relasinya di Eloquent. |
| View / Blade | Template HTML yang menampilkan data kepada pengguna. |
| Migration | Catatan perubahan struktur database yang bisa dijalankan atau dibatalkan. |
| Seeder | Pengisi data awal/contoh ke database. |
| Middleware | Pemeriksaan yang dijalankan sebelum request masuk ke controller. |
| Validation | Pemeriksaan agar data yang dikirim memenuhi aturan. |
| Session | Penyimpanan data sementara untuk satu pengguna, misalnya keranjang. |
| Named route | Nama untuk route agar link/form tidak perlu menulis URL secara langsung. |

## 3. Struktur folder

### `app/`

Kode utama aplikasi:

- `Http/Controllers/`: logika request untuk halaman dan fitur.
- `Http/Middleware/`: pemeriksaan akses sebelum request diteruskan.
- `Http/Requests/`: aturan validasi request yang digunakan ulang.
- `Models/`: model dan relasi database.
- `Providers/`: konfigurasi service provider aplikasi.
- `View/Components/`: komponen Blade yang memiliki class PHP.

### `routes/`

- [`routes/web.php`](routes/web.php): route halaman toko, dashboard, katalog, keranjang, riwayat pembelian, dan profil.
- [`routes/auth.php`](routes/auth.php): route login, registrasi, logout, reset password, dan verifikasi email.
- `routes/console.php`: tempat mendefinisikan perintah Artisan berbasis closure.
- `routes/web.old.php`: salinan/versi lama; tidak didaftarkan sebagai route aktif pada bootstrap saat ini.

### `resources/`

- `resources/views/`: halaman dan komponen Blade.
- `resources/js/`: JavaScript aplikasi, inisialisasi Alpine, grafik, dan pemotongan gambar.
- `resources/css/`: entry CSS Tailwind yang dibangun oleh Vite.

### `database/`

- `migrations/`: struktur tabel.
- `seeders/`: data contoh dan data awal.
- `factories/`: pembuat data uji, misalnya `UserFactory`.
- `database.sqlite`: file database SQLite project; bukan source code.

### `tests/`

Berisi pengujian feature dan unit. Tes feature menguji alur melalui request
Laravel, sedangkan tes unit menguji bagian kecil secara terisolasi.

### Folder/file pendukung

- `bootstrap/app.php`: mendaftarkan route, health-check, dan alias middleware `admin`.
- `config/`: pengaturan aplikasi, database, autentikasi, cache, email, dan layanan lain.
- `public/`: entry point web dan aset yang dapat diakses browser, termasuk gambar.
- `vite.config.js`, `package.json`: pengaturan dan perintah asset frontend.
- `composer.json`: versi PHP/package Laravel dan perintah Composer.
- `.env`: konfigurasi lokal/rahasia; jangan dibagikan atau dimasukkan ke laporan.

Folder seperti `vendor/` berisi library yang dipasang Composer. `node_modules/`
berisi package frontend. Keduanya bukan kode fitur toko dan umumnya tidak diedit
untuk tugas aplikasi.

## 4. Routing dan akses

File utama route web adalah [`routes/web.php`](routes/web.php). Method HTTP yang
sering ditemui:

- `GET`: meminta/menampilkan halaman atau form.
- `POST`: mengirim data baru atau menjalankan aksi.
- `PUT` / `PATCH`: memperbarui data.
- `DELETE`: menghapus data.

`name('...')` memberi nama route. Di Blade, nama tersebut dipanggil dengan
`route('nama.route')`; ini lebih aman dirawat daripada menulis URL berulang kali.

### Route produk dan kategori tugas 21

- `GET /product/create`, nama `product.create`: menampilkan form produk.
- `POST /product`, nama `product.store`: memproses form produk.
- `GET /product-category/create`, nama `product-category.create`: menampilkan form kategori.
- `POST /product-category`, nama `product-category.store`: memproses form kategori.

Keempat route tersebut memakai middleware `auth` dan `admin`. Project juga
memiliki route resource dashboard dengan nama seperti
`dashboard.products.store` dan `dashboard.product-categories.store`. Route
bernama pendek dan route dashboard mengarah ke controller yang sama.

`Route::resource(...)` membuat sekumpulan route CRUD Laravel secara otomatis.
`except('show')` menghilangkan route halaman detail satu data. Prefix
`dashboard` menambahkan bagian `/dashboard` pada URL dalam group tersebut.

Route toko umum seperti beranda/katalog tidak berada di group admin. Fitur
keranjang, checkout, dan profil memerlukan `auth`. Route autentikasi dimuat dari
[`routes/auth.php`](routes/auth.php).

## 5. Controller dan alur fitur

### Produk — `ProductController`

File: [`app/Http/Controllers/ProductController.php`](app/Http/Controllers/ProductController.php)

- `home()`: mengambil beberapa produk yang stoknya tersedia untuk beranda.
- `catalog()`: memvalidasi kata pencarian dan menampilkan katalog.
- `index()`: menampilkan daftar produk admin dengan pencarian, filter kategori,
  pengurutan, dan pagination.
- `create()`: menampilkan form tambah produk; mendukung form modal admin.
- `store()`: memvalidasi data produk, membuat slug unik, menyimpan gambar, lalu
  menyimpan produk ke database.
- `edit()` dan `update()`: menampilkan serta memperbarui data produk.
- `destroy()`: menghapus produk jika belum digunakan oleh pesanan.
- `trackClick()`: menambah penghitung klik produk.
- `uniqueSlug()`, `storeImage()`, `deleteManagedImage()`: helper internal untuk
  slug dan pengelolaan file gambar.

Validasi produk mencakup kategori yang harus ada, nama, deskripsi, stok, harga,
dan gambar. Gambar harus berformat yang diizinkan, tidak lebih dari 2 MB, dan
memenuhi ukuran dimensi yang ditentukan controller.

### Kategori — `ProductCategoryController`

File: [`app/Http/Controllers/ProductCategoryController.php`](app/Http/Controllers/ProductCategoryController.php)

- `index()`: menampilkan kategori, jumlah produk, dan pencarian.
- `create()`: menampilkan form tambah kategori, termasuk mode modal.
- `store()`: memvalidasi nama, memastikan nama unik, membuat slug, lalu
  menyimpan kategori.
- `edit()` dan `update()`: mengubah nama/slug kategori.
- `destroy()`: menolak penghapusan jika kategori masih memiliki produk.
- `uniqueSlug()`: membuat slug kategori yang tidak bentrok.

### Keranjang — `CartController`

File: [`app/Http/Controllers/CartController.php`](app/Http/Controllers/CartController.php)

Keranjang disimpan pada tabel `carts` per pengguna yang login. Controller:

- `index()`: mengambil item milik pengguna login beserta data produknya.
- `store()`: menambahkan satu produk jika stok tersedia dan batas jumlah belum tercapai.
- `updateQuantity()`: mengubah jumlah setelah memvalidasi perubahan dan stok.
- `remove()`: menghapus produk dari keranjang pengguna yang sedang login.

Respons bisa berupa redirect untuk form biasa atau JSON untuk request AJAX.

### Checkout — `CheckoutController`

File: [`app/Http/Controllers/CheckoutController.php`](app/Http/Controllers/CheckoutController.php)

- `index()`: menampilkan rincian dan total barang milik pengguna yang login.
- `store()`: memeriksa stok terbaru, membuat baris pesanan, mengurangi stok,
  mengosongkan keranjang, lalu membuka WhatsApp dengan pesan konfirmasi.
- Seluruh perubahan database checkout dilakukan dalam satu transaksi agar
  pesanan, stok, dan keranjang tidak tertinggal dalam keadaan parsial.

Nomor tujuan WhatsApp dibaca dari `WHATSAPP_PHONE`. Isi dengan kode negara dan
angka saja, misalnya `6281234567890`. Jika masih kosong, tautan WhatsApp tetap
membuka dengan teks pesanan yang telah disiapkan dan pengguna dapat memilih
tujuan sendiri.

### Dashboard — `DashboardController`

File: [`app/Http/Controllers/DashboardController.php`](app/Http/Controllers/DashboardController.php)

Mengambil jumlah produk, kategori, pengguna, klik, pendapatan, data pesanan, dan
data grafik lalu mengirimkannya ke `admin/dashboard.blade.php`. Jika belum ada
pesanan/klik, sebagian grafik menampilkan data contoh dan view memberi label
bahwa data tersebut adalah contoh.

### Riwayat pembelian — `OrderHistoryController`

File: [`app/Http/Controllers/OrderHistoryController.php`](app/Http/Controllers/OrderHistoryController.php)

Mengambil pesanan melalui relasi milik pengguna yang sedang login, memuat data
produk, mengelompokkan semua item berdasarkan nomor invoice, dan menampilkan
riwayat terbaru dengan pagination. Halaman detail invoice juga menampilkan item
pesanan dan tautan WhatsApp. Method `cancel()` membatalkan hanya invoice milik
pengguna yang masih berstatus `pending`, mengubah seluruh item invoice menjadi
`cancelled`, dan mengembalikan stok dalam satu transaksi. Pengguna tidak dapat
melihat atau membatalkan pesanan akun lain.

### Controller lain

- `ProfileController`: menampilkan dan memperbarui profil serta menghapus akun.
- `Controller`: base controller untuk controller aplikasi.
- `Auth/*Controller`: alur Breeze untuk login, registrasi, logout, reset password,
  konfirmasi password, dan verifikasi email.
- `OrderController` dan `OrderItemController`: saat ini masih berupa kerangka
  controller resource dengan method kosong; jangan menganggap CRUD pesanan sudah
  diterapkan hanya karena file controllernya ada.

## 6. Model dan hubungan data

- [`Product`](app/Models/Product.php): data produk; relasi `category()` menuju
  `ProductCategory`. Scope `available()` memilih produk dengan stok lebih dari 0.
  `fillable` menentukan field yang boleh diisi mass assignment, sedangkan
  `casts()` memastikan beberapa nilai dibaca sebagai integer.
- [`ProductCategory`](app/Models/ProductCategory.php): kategori; `products()`
  menyatakan satu kategori memiliki banyak produk.
- [`User`](app/Models/User.php): akun pengguna Laravel; `isAdmin()` memeriksa
  apakah role pengguna adalah `admin`. Password di-hash lewat cast `hashed`.
  Relasi `cartItems()` mengambil keranjang dan `orders()` mengambil pesanan milik
  akun tersebut.
- [`Order`](app/Models/Order.php): memiliki relasi ke pengguna dan produk.
  `invoice_number` menghubungkan beberapa baris produk dari checkout yang sama;
  `status` mencatat `pending`, `processing`, `completed`, atau `cancelled`.
- [`Cart`](app/Models/Cart.php): satu baris untuk satu produk di keranjang seorang
  pengguna; memiliki relasi ke `User` dan `Product`.
- `OrderItem`: model masih kosong/minimal pada kondisi source sekarang.

Relasi kategori-produk dapat dibaca sebagai:

```text
ProductCategory (satu) ---- (banyak) Product
```

Model tidak otomatis membuat tabel. Struktur tabel dibuat oleh migration.

## 7. Middleware dan validasi akses

- [`AdminMiddleware`](app/Http/Middleware/AdminMiddleware.php): meneruskan
  request hanya jika pengguna login memiliki role admin; selain itu mengembalikan
  status 403.
- [`bootstrap/app.php`](bootstrap/app.php): mendaftarkan nama alias `admin`
  untuk class middleware tersebut.
- Middleware `auth` bawaan Laravel menolak pengguna yang belum login.
- `LoginRequest` memvalidasi kredensial dan membatasi percobaan login.
- `ProfileUpdateRequest` menampung aturan validasi pembaruan profil.

## 8. View dan frontend

### Halaman Blade

- `home/index.blade.php`: beranda dan produk pilihan.
- `product/description.blade.php`: katalog dan pencarian produk.
- `cart/index.blade.php`: isi keranjang, ubah jumlah, hapus produk, dan ringkasan.
- `checkout/index.blade.php`: rincian sebelum checkout dan tombol WhatsApp.
- `purchases/index.blade.php` dan `purchases/show.blade.php`: daftar invoice
  serta detail produk dan total tiap invoice.
- `admin/dashboard.blade.php`: kartu ringkasan dan grafik dashboard.
- `admin/products/`: tabel, form tambah/edit, dan form modal produk.
- `admin/product_categories/`: tabel, form tambah/edit, dan form modal kategori.
- `auth/`: halaman login, registrasi, verifikasi, dan reset password.
- `profile/`: halaman profil serta form bagian profil.
- `components/`: elemen UI yang digunakan ulang, seperti navbar, tombol, input,
  footer, dan product card.
- `layouts/` dan `components/layout.blade.php`: kerangka halaman.

Blade dapat menampilkan variabel seperti `{{ $product->name }}`, membuat form
CSRF dengan `@csrf`, mengulang data dengan `@foreach`, dan memanggil route
bernama dengan `route(...)`.

### JavaScript dan CSS

- [`resources/js/app.js`](resources/js/app.js): menjalankan Alpine, grafik
  Chart.js, dan fitur crop gambar produk dengan Cropper.js.
- `resources/js/bootstrap.js`: konfigurasi awal kebutuhan JavaScript/Axios.
- `resources/css/app.css`: entry Tailwind untuk asset yang dibangun.
- `resources/views/components/layout.blade.php`: kerangka storefront, termasuk
  styling dan interaksi AJAX storefront.
- `resources/views/layouts/app.blade.php`: layout halaman admin/auth berbasis
  Vite dan komponen Blade.
- `vite.config.js`: menghubungkan Vite dengan entry CSS dan JavaScript Laravel.

## 9. Database, migration, dan seed

Migration utama membangun tabel pengguna, kategori, produk, keranjang, pesanan,
dan item pesanan. Migration tambahan menambahkan penghitung klik produk, role
pengguna, dan relasi pemilik/produk/jumlah pada tabel keranjang. Foreign key
menjaga hubungan data; misalnya kategori produk tidak bisa dihapus jika masih
dipakai produk.

Seeder:

- `DatabaseSeeder`: membuat/memperbarui akun contoh admin dan menjalankan seeder
  kategori serta produk.
- `ProductCategorySeeder`: mengisi kategori awal.
- `ProductSeeder`: mengisi delapan produk contoh dan menghubungkannya ke kategori.
- `UserFactory`: membuat pengguna untuk test.

### Catatan penting tentang bagian yang belum lengkap

- Tabel `carts` menyimpan pengguna, produk, dan jumlah. Keranjang setiap akun
  terpisah dan tetap ada setelah pengguna berpindah halaman atau login kembali.
- Tabel `order_items` saat ini juga hanya memiliki ID dan timestamps.
- Checkout mencatat satu baris di tabel `orders` untuk setiap produk; semua
  baris dalam satu checkout berbagi `invoice_number`. Stok dikurangi dan pesan
  konfirmasi WhatsApp disiapkan. Pengguna bisa melihat invoice di riwayat;
  pembayaran online dan status konfirmasi dari toko belum diproses aplikasi.
- Pengguna boleh membatalkan invoice hanya saat statusnya `pending`. Pembatalan
  mengubah status semua produk di invoice dan mengembalikan stok. Invoice
  berstatus `processing` atau `completed` tidak dapat dibatalkan oleh pengguna.
- Aplikasi belum terhubung ke payment gateway atau WhatsApp API, sehingga
  aplikasi tidak bisa mengetahui apakah pelanggan benar-benar membayar atau
  mengirim pesan WhatsApp. Checkout baru dan pesanan lama yang belum diverifikasi
  berstatus `pending`.
- Di dashboard bagian Latest Orders, admin memilih **Terima pesanan** setelah
    memeriksa bukti transfer/mutasi atau konfirmasi pelanggan di WhatsApp. Aksi
    ini menyimpan admin dan waktu verifikasi, mengubah pembayaran menjadi `paid`,
    serta status pesanan menjadi `processing`. Admin juga dapat memilih
    **Batalkan** berwarna kuning selama status masih `pending`; seluruh item
    invoice menjadi `cancelled` dan stok dikembalikan satu kali. Pesanan yang
    sudah dibatalkan ditampilkan sebagai teks merah **Pesanan Dibatalkan**, bukan
    tombol aksi. Setelah admin memverifikasi pembayaran, pelanggan dapat melihat
    pesanan berstatus `processing`. Jika barang sudah diterima, pelanggan sendiri
    menekan **Konfirmasi pesanan diterima** di detail invoice. Status semua item
    berubah menjadi `completed`; tombol hanya tersedia untuk invoice yang semua
    itemnya berstatus `processing` dan pembayarannya sudah diverifikasi. Jika
    pelanggan belum mengonfirmasi, command `orders:complete-expired` mengubah
    invoice menjadi `completed` setelah lebih dari 5 hari sejak admin memulai
    pemrosesan (`payment_verified_at`). Pemeriksaan otomatis dijalankan setiap
    jam, sehingga perubahan dapat terjadi pada pemeriksaan berikutnya. Jalankan
    `php artisan schedule:work` selama pengembangan lokal; server produksi harus
    memanggil `php artisan schedule:run` secara berkala melalui cron atau Windows
    Task Scheduler. Aksi penerimaan dan pembatalan admin memerlukan pemeriksaan
    manual; aplikasi tidak terhubung ke bank/payment gateway. Warna status
    pesanan dibuat konsisten: Pending kuning, Processing biru, Completed hijau
    kebiruan, dan Cancelled merah.
- Controller `OrderController` dan `OrderItemController` masih berupa kerangka.

Catatan tersebut menjelaskan batas fitur pesanan yang belum tersedia, bukan
berarti pencatatan checkout atau riwayat pembelian tidak berfungsi.

## 10. Pengujian

Project memakai Pest dan PHPUnit. Pengujian feature penting antara lain:

- `AdminCatalogTest`: daftar, pencarian, filter, CRUD produk/kategori, validasi,
  upload gambar, modal, dan proteksi penghapusan.
- `ShoppingCartTest`: keranjang per pengguna, autentikasi, katalog, perubahan
  jumlah, penghapusan item, checkout, dan konfirmasi WhatsApp.
- `DashboardTest`: ringkasan dashboard dan pencatatan klik.
- `ProfileTest`: proteksi route dashboard dan alur profil.
- `PurchaseHistoryTest`: riwayat pesanan per akun, tampilan kosong, informasi
  profil, dan tautan navigasi.
- `Auth/*Test`: login, registrasi, reset password, konfirmasi password, dan
  verifikasi email.
- `DatabaseSeederTest`: data awal kategori dan produk.

Jalankan dari folder `ecommerce10`:

```powershell
php artisan test
```

Untuk test katalog/admin saja:

```powershell
php artisan test --filter=AdminCatalogTest
```

Untuk test keranjang dan checkout:

```powershell
php artisan test --filter=ShoppingCartTest
```

Test feature biasanya memakai database yang di-refresh agar data uji tidak
tercampur dengan data aplikasi.

## 11. Tugas 21: alur tambah kategori dan produk

1. Browser membuka route `GET` `product.create` atau `product-category.create`.
2. Method `create()` menampilkan form Blade.
3. Form memakai method `POST` dan mengirim data ke `product.store` atau
   `product-category.store`.
4. Middleware memastikan pengguna sudah login dan berstatus admin.
5. Method `store()` memvalidasi input.
6. Jika input tidak valid, Laravel mengembalikan error validasi ke form.
7. Jika valid, model Eloquent menyimpan data ke database.
8. Aplikasi mengembalikan redirect sukses atau JSON untuk request AJAX.

Jawaban singkat untuk ujian:

> `create()` digunakan untuk menampilkan form, sedangkan `store()` menerima
> request form, memvalidasi input, lalu menyimpannya melalui model ke database.
> Route menghubungkan URL dan method HTTP ke controller. Middleware `auth` dan
> `admin` membatasi fitur tersebut kepada admin yang sudah login.

## 12. Tugas 22: edit dan hapus produk serta kategori

Tugas ini menambahkan/menggunakan operasi edit dan hapus untuk produk dan
kategori. Pada project ini, fitur edit dan hapus sudah terhubung melalui route
resource di `routes/web.php`; aksesnya hanya untuk pengguna yang login sebagai
admin.

### Route yang terlibat

| Aksi | Method HTTP | Nama route produk | Nama route kategori |
|---|---|---|---|
| Menampilkan form edit | `GET` | `dashboard.products.edit` | `dashboard.product-categories.edit` |
| Memperbarui data | `PUT` / `PATCH` | `dashboard.products.update` | `dashboard.product-categories.update` |
| Menghapus data | `DELETE` | `dashboard.products.destroy` | `dashboard.product-categories.destroy` |

Parameter `{product}` atau `{product_category}` pada URL berisi ID record.
Laravel melakukan **route model binding**: ID tersebut dicari dan object model
diserahkan ke method controller. Jika record tidak ditemukan, Laravel
mengembalikan 404.

### Alur edit

1. Tombol **Edit** di tabel membuka route `...edit` untuk record yang dipilih.
2. Method `edit()` mengambil data record dan menampilkan form dengan nilai lama.
   Form tersedia sebagai halaman penuh dan modal.
3. Form menggunakan `POST` HTML dengan `@csrf` dan `@method('PUT')`. Browser
   form HTML tidak mengirim `PUT` secara langsung; directive Laravel tersebut
   menambahkan method spoofing agar request diproses sebagai `PUT`.
4. Method `update()` memvalidasi input sebelum mengubah model.
5. Jika valid, perubahan disimpan dan pengguna mendapat pesan sukses. Jika tidak
   valid, Laravel mengembalikan error validasi dan data tidak diperbarui.

Untuk kategori, nama wajib, maksimal 255 karakter, dan harus unik. Pengecekan
unik mengabaikan kategori yang sedang diedit sehingga pengguna boleh menyimpan
nama yang sama seperti semula. Slug dibuat ulang dan dijaga agar tidak bentrok.

Untuk produk, kategori harus ada, nama dan deskripsi wajib, stok serta harga
tidak boleh negatif. Gambar baru bersifat opsional ketika edit. Jika gambar
diganti, gambar lama yang dikelola aplikasi dihapus setelah gambar baru
disimpan. Jika gambar tidak diganti, gambar lama tetap digunakan.

### Alur hapus dan perlindungan data

1. Tombol **Hapus** mengirim form `POST` dengan `@csrf` dan `@method('DELETE')`.
2. Route `...destroy` mengarahkan request ke method `destroy()` controller.
3. Controller menerima model berdasarkan ID, memeriksa aturan relasi, lalu
   menghapus record jika diperbolehkan.

Kategori tidak boleh dihapus selama masih memiliki produk. Produk tidak boleh
dihapus jika sudah tercatat pada pesanan. Perlindungan ini menjaga agar data
terkait tidak rusak atau kehilangan referensi. Jika penghapusan ditolak, aplikasi
kembali ke halaman sebelumnya dengan pesan error.

### File yang perlu dibaca

- `app/Http/Controllers/ProductController.php`: `edit()`, `update()`, dan `destroy()`.
- `app/Http/Controllers/ProductCategoryController.php`: `edit()`, `update()`, dan `destroy()`.
- `resources/views/admin/products/index.blade.php` dan
  `resources/views/admin/product_categories/index.blade.php`: tombol Edit/Hapus.
- `resources/views/admin/products/form.blade.php` dan
  `resources/views/admin/product_categories/form.blade.php`: form halaman penuh.
- `resources/views/admin/products/modal-form.blade.php` dan
  `resources/views/admin/product_categories/modal-form.blade.php`: form modal.
- `tests/Feature/AdminCatalogTest.php`: test update, validasi, dan penghapusan.

### Jawaban singkat untuk ujian

> Tombol edit membawa ID data ke route edit. Controller mencari data dengan
> route model binding lalu menampilkan form berisi nilai saat ini. Form mengirim
> perubahan ke method `update()`, yang memvalidasi sebelum menyimpan. Hapus
> memakai method `DELETE` dan method `destroy()`. Sebelum menghapus, controller
> memeriksa apakah produk/kategori masih dipakai data lain.

Verifikasi test yang terkait:

```powershell
php artisan test --filter=AdminCatalogTest
```

## 13. Tugas 23: keranjang, checkout, WhatsApp, riwayat, dan dashboard

### Instruksi keranjang (gambar 1–5)

1. **Keranjang berdasarkan pengguna login:** data disimpan di `carts` dengan
   `user_id`, `product_id`, dan `quantity`. Relasi `User::cartItems()` memastikan
   setiap akun hanya membaca keranjangnya sendiri.
2. **Ubah jumlah dan hapus:** tombol plus/minus mengirim perubahan ke route
   `cart.quantity`; tombol hapus ke `cart.remove`. Controller membatasi jumlah
   dengan stok produk, dan query selalu dibatasi pada pengguna aktif.
3. **Checkout:** `GET /checkout` menampilkan rincian dan total. `POST /checkout`
   memeriksa stok terbaru, membuat record pesanan, mengurangi stok, lalu
   mengosongkan keranjang dalam transaksi database.
4. **Konfirmasi WhatsApp:** setelah checkout, aplikasi membangun pesan berisi
   nama, nomor pesanan, daftar barang, jumlah, dan total, lalu membuka tautan
   `wa.me`. Atur `WHATSAPP_PHONE` pada `.env` dengan kode negara tanpa tanda plus
   atau spasi, contohnya `6281234567890`. Jangan memasukkan nomor rahasia ke
   source control. Jika belum diisi, WhatsApp terbuka dengan pesan dan pengguna
   dapat memilih penerimanya.
5. **Tampilan daftar produk:** instruksi menyebut `show.blade.php`, tetapi file
   tersebut tidak ada pada project. Halaman katalog aktif adalah
   `resources/views/product/description.blade.php`, yang sudah menampilkan
   produk dari database sebagai daftar kartu. Perubahan tugas ini memakai
   katalog aktif tersebut dan tidak membuat view `show` duplikat.
6. **Invoice per checkout:** seluruh produk dalam satu checkout mendapat nomor
   invoice yang sama dengan format `INV-YYYYMMDD-########`. Halaman riwayat
   mengelompokkan semua baris produk berdasarkan invoice dan menyediakan
   halaman detail beserta tombol konfirmasi WhatsApp.
7. **Status dan pembatalan pesanan:** checkout berstatus `pending` karena aplikasi
   belum bisa memverifikasi pembayaran atau pesan WhatsApp. User bisa membatalkan
   invoice yang masih `pending`; seluruh item berubah menjadi `cancelled` dan stok
   dikembalikan di dalam transaksi. Status `processing`/`completed` membutuhkan
   verifikasi admin.
8. **Riwayat pembelian:** halaman `GET /riwayat-pembelian` menampilkan invoice
   yang tercatat setelah checkout. Query diambil dari relasi pengguna login,
   sehingga pengguna hanya melihat invoice miliknya.
9. **Profil pengguna:** halaman `/profile` menampilkan nama, email, tipe akun,
   tanggal bergabung, dan jumlah item pesanan dalam layout toko. Pengguna tetap
   dapat memperbarui nama/email, password, atau menghapus akun. Admin tetap
   memakai layout admin pada halaman profil yang sama.
10. **Navigasi akun:** pengguna login memiliki tautan Riwayat dan Profil.
   Dashboard hanya muncul untuk admin.

### Instruksi dashboard/klik (gambar keenam)

Bagian ini sudah tersedia sebelum pengerjaan keranjang tugas 23 dan diverifikasi
agar tidak dibuat dua kali:

- Migration `2026_11_10_100000_add_click_count_to_products_table.php`
  menambahkan kolom klik produk.
- `ProductController::trackClick()` mencatat klik dan
  `DashboardController::index()` mengambil jumlah produk, jumlah kategori, serta
  total klik.
- `resources/views/admin/dashboard.blade.php` menampilkan data tersebut;
  `resources/js/app.js` menggambar grafik.
- `tests/Feature/DashboardTest.php` memeriksa angka dashboard dan pencatatan klik.
- Admin dapat membuka **Orders** dari navbar atau **View All Orders** pada
  dashboard untuk melihat riwayat invoice secara lengkap, tidak hanya delapan
  pesanan terbaru. Halaman riwayat dibatasi untuk admin, memakai pagination,
  dan dapat difilter berdasarkan tanggal, bulan, tahun, serta status pesanan.

## 14. Tugas 24: klik produk, deployment cPanel, dan pemeriksaan akhir

- Kartu produk pada halaman utama dan katalog menggunakan
  `data-product-click`; klik dikirim ke route `products.click`, lalu
  `ProductController::trackClick()` menambah `click_count`. Statistiknya tampil
  di dashboard dan grafik klik per kategori. `DashboardTest` memastikan kartu
  produk di halaman utama terhubung ke route klik dan endpoint menambah hitungan.
- Sebelum deployment, jalankan `php artisan test`, `npm run build`, dan
  `php artisan migrate:status`. Pastikan semua migration sudah dijalankan pada
  database target.
- Deployment cPanel belum dilakukan dari lingkungan lokal ini. Siapkan domain
  dan document root yang menunjuk ke folder `public` Laravel, database MySQL,
  versi PHP yang memenuhi `composer.json`, serta konfigurasi `.env` produksi.
  Jangan unggah `.env`, `vendor`, atau kredensial ke repository publik.
- Setelah unggah, jalankan dependency/build/migration pada server sesuai akses
  yang disediakan hosting, atur permission `storage` dan `bootstrap/cache`,
  buat symbolic link storage bila diperlukan, lalu uji halaman utama, klik
  produk, checkout, riwayat, dashboard admin, dan halaman Orders melalui domain.

### File yang berubah untuk alur keranjang dan checkout

- Migration `database/migrations/2026_11_11_164612_add_user_and_product_to_carts_table.php`.
- Migration `database/migrations/2026_11_11_173000_add_invoice_number_to_orders_table.php`
  menambahkan nomor invoice dan memberi nomor invoice pada pesanan lama.
- Migration `database/migrations/2026_11_11_174000_add_status_to_orders_table.php`
  menambahkan status invoice.
- Migration `database/migrations/2026_11_11_174500_set_unverified_orders_to_pending.php`
  mengoreksi status lama ke `pending`, karena aplikasi tidak memiliki bukti
  pembayaran atau konfirmasi untuk menyatakan pesanan telah diproses.
- Migration `database/migrations/2026_11_11_174600_add_payment_verification_to_orders_table.php`
  menambahkan status pembayaran, waktu verifikasi, dan admin yang memverifikasi.
- Dashboard admin menyediakan aksi verifikasi manual pembayaran. Aksi ini
  mengubah status pembayaran menjadi `paid` dan status pesanan menjadi
  `processing` untuk seluruh item invoice. Admin juga dapat membatalkan invoice
  Pending dari dashboard. Riwayat dan detail invoice user menampilkan status
  pembayaran, waktu, serta nama admin yang memverifikasi.
- `app/Models/Cart.php` dan relasi keranjang di `app/Models/User.php`.
- `app/Http/Controllers/CartController.php` serta
  `app/Http/Controllers/CheckoutController.php`.
- `app/Http/Controllers/OrderHistoryController.php` menyediakan riwayat yang
  hanya dapat dibaca pemilik akun, halaman detail per invoice, dan pembatalan
  pesanan pending beserta pemulihan stok.
- `app/Models/Order.php` menyimpan nomor invoice dan status pada setiap baris
  produk pesanan.
- `app/Models/User.php` memiliki relasi `orders()` dan `cartItems()`.
- `routes/web.php`, `resources/views/cart/index.blade.php`, dan
  `resources/views/checkout/index.blade.php`.
- `resources/views/purchases/index.blade.php` menampilkan riwayat pembelian;
- `resources/views/purchases/show.blade.php` menampilkan seluruh item dan total
  untuk satu invoice serta tombol pembatalan bila masih pending.
- `resources/views/profile/edit.blade.php` memilih tampilan toko untuk pengguna
  dan tampilan admin untuk admin.
- `resources/views/components/navbar.blade.php` menampilkan jumlah item keranjang
  pengguna yang sedang login.
- `config/services.php` membaca pengaturan `WHATSAPP_PHONE`.
- `tests/Feature/ShoppingCartTest.php` menguji pemisahan data antarpengguna,
  perubahan keranjang, checkout, stok, dan tautan WhatsApp.
- `tests/Feature/PurchaseHistoryTest.php` menguji riwayat, privasi pesanan,
  profil, dan navigasi akun.

### Jawaban singkat untuk ujian

> Keranjang disimpan di database dan dihubungkan dengan user serta produk.
> Controller mengambil item melalui relasi milik pengguna login, sehingga
> keranjang pengguna lain tidak ikut terbaca. Saat checkout, aplikasi mengecek
> stok di dalam transaksi, membuat satu nomor invoice untuk seluruh produk,
> mengurangi stok, dan mengosongkan keranjang. Invoice berstatus Pending dapat
> dibatalkan oleh pemiliknya; pembatalan mengembalikan stok dan mengubah status
> seluruh item invoice menjadi Cancelled. Invoice tetap tersimpan dan bisa
> dilihat kembali pada menu Riwayat.
> Profil menampilkan informasi akun dan jumlah pesanan. Setelah checkout,
> WhatsApp terbuka dengan rincian pesanan untuk dikonfirmasi.

Verifikasi:

```powershell
php artisan migrate
php artisan test --filter=ShoppingCartTest
php artisan test --filter=PurchaseHistoryTest
php artisan test --filter=DashboardTest
```

Jalankan migration satu kali setelah menarik perubahan agar tabel keranjang
memiliki kolom pemilik, produk, jumlah, dan unique constraint per pengguna/produk.

## 14. Pertanyaan latihan ujian

**Apa bedanya route dan controller?**  
Route menentukan request masuk ke aksi mana; controller menjalankan logika aksi.

**Apa bedanya `create()` dan `store()`?**  
`create()` menampilkan form. `store()` memproses data yang dikirim oleh form.

**Mengapa form memakai `@csrf`?**  
Untuk menyertakan token perlindungan CSRF Laravel pada request yang mengubah data.

**Mengapa validasi dilakukan di controller?**  
Supaya data yang masuk memenuhi aturan sebelum disimpan. Laravel akan mengembalikan
error validasi jika aturan tidak terpenuhi.

**Apa fungsi migration dan seeder?**  
Migration mengatur struktur tabel; seeder mengisi data awal/contoh.

**Mengapa route admin memakai middleware?**  
Supaya fitur administrasi tidak dapat dipakai pengunjung umum atau pengguna biasa.

**Apakah keranjang disimpan di tabel `carts`?**  
Ya. Setiap baris menyimpan pemilik, produk, dan jumlah. Query keranjang dibatasi
ke akun yang sedang login.

## 15. Cara membaca fitur baru

Untuk memahami atau menjelaskan fitur, ikuti urutan ini:

1. Cari URL dan nama route di `routes/web.php` atau `routes/auth.php`.
2. Periksa middleware yang membatasi route.
3. Buka method controller yang dipanggil route.
4. Ikuti query model/relasi ke tabel yang dipakai.
5. Lihat view yang dikembalikan controller.
6. Jika ada form, periksa `action`, `method`, `@csrf`, dan nama input.
7. Cari test terkait di `tests/Feature/`.

Dengan urutan ini, alur sebuah fitur bisa dijelaskan dari request sampai
response tanpa harus menghafal setiap baris kode.

## 16. Peta file aplikasi lebih lengkap

Bagian ini membantu mencari file saat belajar. File yang sudah dijelaskan
sebelumnya tidak diulang dengan uraian panjang.

### Controller autentikasi dan request

Controller autentikasi berada di `app/Http/Controllers/Auth/`:

- `AuthenticatedSessionController.php`: menampilkan form login, memproses login,
  dan logout.
- `RegisteredUserController.php`: menampilkan form registrasi dan membuat akun.
- `PasswordResetLinkController.php`: meminta link reset password.
- `NewPasswordController.php`: menerima token reset dan menyimpan password baru.
- `PasswordController.php`: mengganti password pengguna yang sudah login.
- `ConfirmablePasswordController.php`: meminta dan memeriksa konfirmasi password.
- `EmailVerificationPromptController.php`: meminta pengguna memverifikasi email.
- `VerifyEmailController.php`: memproses link verifikasi email.
- `EmailVerificationNotificationController.php`: mengirim ulang notifikasi verifikasi.

Request khusus:

- `app/Http/Requests/Auth/LoginRequest.php`: aturan dan proses autentikasi login,
  termasuk pembatasan percobaan login.
- `app/Http/Requests/ProfileUpdateRequest.php`: validasi data profil.

### Model dan class pendukung

- `app/Providers/AppServiceProvider.php`: tempat pendaftaran/konfigurasi layanan
  aplikasi.
- `app/View/Components/AppLayout.php` dan `GuestLayout.php`: class untuk layout
  pengguna yang sudah login dan pengunjung.
- `app/View/Components/ProductCard.php`: menerima data produk dan menampilkan
  komponen kartu produk.
- `app/Models/Cart.php`: model item keranjang dengan relasi ke pengguna dan produk.
- `app/Models/OrderItem.php`: masih model minimal; checkout saat ini menyimpan
  satu baris per produk langsung di tabel `orders`.

### View yang sering dicari

- `resources/views/home/index.blade.php`: beranda.
- `resources/views/product/description.blade.php`: katalog produk aktif; project
  tidak memiliki `show.blade.php`.
- `resources/views/cart/index.blade.php`: keranjang belanja.
- `resources/views/checkout/index.blade.php`: ringkasan checkout dan tombol
  konfirmasi pesanan melalui WhatsApp.
- `resources/views/purchases/index.blade.php`: daftar riwayat pembelian milik
  pengguna yang sedang login.
- `resources/views/admin/dashboard.blade.php`: dashboard admin.
- `resources/views/admin/products/index.blade.php`: tabel/daftar produk admin.
- `resources/views/admin/products/form.blade.php`: halaman tambah/edit produk.
- `resources/views/admin/products/modal-form.blade.php`: versi modal form produk.
- `resources/views/admin/product_categories/index.blade.php`: daftar kategori admin.
- `resources/views/admin/product_categories/form.blade.php`: halaman form kategori.
- `resources/views/admin/product_categories/modal-form.blade.php`: versi modal form kategori.
- `resources/views/profile/edit.blade.php`: halaman profil dengan tampilan
  berbeda untuk pengguna toko dan admin.
- `resources/views/profile/partials/update-profile-information-form.blade.php`:
  form nama dan email.
- `resources/views/profile/partials/update-password-form.blade.php`: form ganti password.
- `resources/views/profile/partials/delete-user-form.blade.php`: form hapus akun.
- `resources/views/layouts/app.blade.php` dan `guest.blade.php`: layout Breeze
  untuk area aplikasi dan pengunjung.
- `resources/views/components/layout.blade.php`: layout storefront.
- `resources/views/components/navbar.blade.php` dan `footer.blade.php`:
  navigasi dan bagian bawah storefront.
- `resources/views/components/product-card.blade.php`: markup kartu produk.
- Komponen lain di `resources/views/components/` seperti `input-label`,
  `input-error`, `text-input`, `primary-button`, `dropdown`, dan `modal` adalah
  elemen UI yang digunakan ulang.
- `resources/views/auth/`: halaman Blade untuk login, registrasi, verifikasi,
  konfirmasi password, lupa password, dan reset password.
- `resources/views/welcome.blade.php`: halaman welcome bawaan yang tersedia
  dalam project; route beranda aktif saat ini memakai controller produk.

### Migration dan data awal

File di `database/migrations/` berfungsi sebagai riwayat struktur database:

- `0001_01_01_000000_create_users_table.php`: tabel akun pengguna.
- `0001_01_01_000001_create_cache_table.php`: tabel cache database.
- `0001_01_01_000002_create_jobs_table.php`: tabel job/queue.
- `2026_10_19_122841_create_product_categories_table.php`: tabel kategori.
- `2026_10_19_122958_create_products_table.php`: tabel produk dan foreign key kategori.
- `2026_10_19_123043_create_carts_table.php`: tabel keranjang awal.
- `2026_10_19_123108_create_orders_table.php`: tabel pesanan dengan pengguna dan produk.
- `2026_10_19_123147_create_order_items_table.php`: tabel item pesanan awal.
- `2026_11_10_100000_add_click_count_to_products_table.php`: penghitung klik produk.
- `2026_11_10_180000_add_role_to_users_table.php`: role pengguna.
- `2026_11_11_164612_add_user_and_product_to_carts_table.php`: pemilik, produk,
  jumlah, foreign key, dan unique constraint untuk item keranjang.
- `2026_11_11_173000_add_invoice_number_to_orders_table.php`: nomor invoice
  bersama untuk produk dalam checkout yang sama.
- `2026_11_11_174000_add_status_to_orders_table.php`: status pesanan untuk
  pembatalan dan grafik status dashboard.
- `2026_11_11_174500_set_unverified_orders_to_pending.php`: memastikan pesanan
  yang belum diverifikasi tidak keliru ditandai `processing`.
- `2026_11_11_174600_add_payment_verification_to_orders_table.php`: status
  pembayaran dan catatan waktu/admin verifikasi.

`database/factories/UserFactory.php` membuat data pengguna untuk test. File
`database/seeders/DatabaseSeeder.php`, `ProductCategorySeeder.php`, dan
`ProductSeeder.php` mengisi akun contoh, kategori, dan produk.

### Test yang tersedia

- `tests/Feature/AdminCatalogTest.php`: pengelolaan produk dan kategori admin.
- `tests/Feature/DashboardTest.php`: angka ringkasan, klik produk, dan data pesanan dashboard.
- `tests/Feature/ShoppingCartTest.php`: katalog, keranjang per pengguna, checkout,
  stok, dan tautan WhatsApp.
- `tests/Feature/PurchaseHistoryTest.php`: privasi riwayat, halaman profil akun,
  tampilan kosong, navigasi pengguna, pembatalan invoice, dan pemulihan stok.
- `tests/Feature/ProfileTest.php`: akses dashboard dan pengelolaan profil.
- `tests/Feature/DatabaseSeederTest.php`: hasil data seeder.
- `tests/Feature/Auth/AuthenticationTest.php`: login dan logout.
- `tests/Feature/Auth/RegistrationTest.php`: registrasi.
- `tests/Feature/Auth/EmailVerificationTest.php`: verifikasi email.
- `tests/Feature/Auth/PasswordResetTest.php`: permintaan dan proses reset password.
- `tests/Feature/Auth/PasswordConfirmationTest.php`: konfirmasi password.
- `tests/Feature/Auth/PasswordUpdateTest.php`: pembaruan password.
- `tests/Feature/ExampleTest.php` dan `tests/Unit/ExampleTest.php`: contoh test bawaan.
- `tests/Pest.php` dan `tests/TestCase.php`: konfigurasi dasar Pest/PHPUnit.

## 17. Catatan belajar file konfigurasi

- `composer.json`: kebutuhan PHP/package dan script seperti `setup`, `dev`, dan `test`.
- `package.json`: package frontend dan script `npm run dev` / `npm run build`.
- `phpunit.xml`: konfigurasi PHPUnit/Pest untuk menjalankan test.
- `config/app.php`: identitas dan pengaturan umum aplikasi.
- `config/auth.php`: guard dan provider autentikasi.
- `config/database.php`: koneksi database.
- `config/cache.php`, `config/session.php`, `config/queue.php`: penyimpanan cache,
  session, dan antrean.
- `config/filesystems.php`: disk penyimpanan file.
- `config/logging.php`: tujuan dan level log.
- `config/mail.php` dan `config/services.php`: email serta integrasi layanan.
- `config/services.php` memuat `services.whatsapp.phone` dari `WHATSAPP_PHONE`.
- Tambahkan `WHATSAPP_PHONE=6281234567890` ke `.env` lokal untuk mengarahkan
  konfirmasi ke nomor toko; gunakan nomor toko yang benar dan jangan commit `.env`.
- `postcss.config.js` dan `tailwind.config.js`: konfigurasi pipeline CSS.
- `public/index.php` adalah pintu masuk request web; `artisan` adalah pintu masuk
  perintah terminal Laravel.

Nilai konfigurasi lokal berasal dari environment, terutama `.env`. Untuk belajar
struktur pengaturannya gunakan `.env.example`; jangan menyalin atau membagikan
password, token, atau key dari `.env`.
