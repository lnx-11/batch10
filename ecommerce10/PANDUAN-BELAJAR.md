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
- Keranjang belanja yang disimpan di session.
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

- [`routes/web.php`](routes/web.php): route halaman toko, dashboard, katalog, keranjang, dan profil.
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
keranjang dan profil memerlukan `auth`. Route autentikasi dimuat dari
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

Keranjang saat ini disimpan di session, bukan melalui model/tabel `carts`.
Session menyimpan pasangan ID produk dan jumlahnya. Controller:

- `index()`: membaca keranjang session, mengambil produk, dan menyiapkan data view.
- `store()`: menambahkan satu produk jika stok tersedia dan batas jumlah belum tercapai.
- `updateQuantity()`: menambah/mengurangi jumlah setelah memvalidasi perubahan.
- `remove()`: menghapus produk dari session keranjang.

Respons bisa berupa redirect untuk form biasa atau JSON untuk request AJAX.

### Dashboard — `DashboardController`

File: [`app/Http/Controllers/DashboardController.php`](app/Http/Controllers/DashboardController.php)

Mengambil jumlah produk, kategori, pengguna, klik, pendapatan, data pesanan, dan
data grafik lalu mengirimkannya ke `admin/dashboard.blade.php`. Jika belum ada
pesanan/klik, sebagian grafik menampilkan data contoh dan view memberi label
bahwa data tersebut adalah contoh.

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
- [`Order`](app/Models/Order.php): memiliki relasi ke pengguna dan produk.
- `Cart` dan `OrderItem`: model masih kosong/minimal pada kondisi source sekarang.

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
dan item pesanan. Migration tambahan menambahkan penghitung klik produk dan role
pengguna. Foreign key menjaga hubungan data; misalnya kategori produk tidak
bisa dihapus jika masih dipakai produk.

Seeder:

- `DatabaseSeeder`: membuat/memperbarui akun contoh admin dan menjalankan seeder
  kategori serta produk.
- `ProductCategorySeeder`: mengisi kategori awal.
- `ProductSeeder`: mengisi delapan produk contoh dan menghubungkannya ke kategori.
- `UserFactory`: membuat pengguna untuk test.

### Catatan penting tentang bagian yang belum lengkap

- Tabel `carts` yang ada pada migration saat ini hanya memiliki ID dan timestamps;
  keranjang aplikasi aktif disimpan di session.
- Tabel `order_items` saat ini juga hanya memiliki ID dan timestamps.
- Checkout route saat ini hanya mengembalikan teks sementara, belum memproses
  pembayaran atau membuat pesanan.
- Controller `OrderController` dan `OrderItemController` masih berupa kerangka.

Catatan tersebut menjelaskan kondisi implementasi sekarang, bukan menyatakan
bahwa fitur checkout/pesanan sudah selesai.

## 10. Pengujian

Project memakai Pest dan PHPUnit. Pengujian feature penting antara lain:

- `AdminCatalogTest`: daftar, pencarian, filter, CRUD produk/kategori, validasi,
  upload gambar, modal, dan proteksi penghapusan.
- `ShoppingCartTest`: keranjang session, autentikasi, katalog, serta perubahan
  jumlah dan penghapusan item.
- `DashboardTest`: ringkasan dashboard dan pencatatan klik.
- `ProfileTest`: proteksi route dashboard dan alur profil.
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

## 12. Pertanyaan latihan ujian

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
Belum. Implementasi `CartController` menyimpan keranjang di session.

## 13. Cara membaca fitur baru

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

## 14. Peta file aplikasi lebih lengkap

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
- `app/Models/Cart.php` dan `OrderItem.php`: file model tersedia, tetapi modelnya
  masih kosong/minimal; lihat catatan implementasi yang belum lengkap.

### View yang sering dicari

- `resources/views/home/index.blade.php`: beranda.
- `resources/views/product/description.blade.php`: katalog produk.
- `resources/views/cart/index.blade.php`: keranjang belanja.
- `resources/views/admin/dashboard.blade.php`: dashboard admin.
- `resources/views/admin/products/index.blade.php`: tabel/daftar produk admin.
- `resources/views/admin/products/form.blade.php`: halaman tambah/edit produk.
- `resources/views/admin/products/modal-form.blade.php`: versi modal form produk.
- `resources/views/admin/product_categories/index.blade.php`: daftar kategori admin.
- `resources/views/admin/product_categories/form.blade.php`: halaman form kategori.
- `resources/views/admin/product_categories/modal-form.blade.php`: versi modal form kategori.
- `resources/views/profile/edit.blade.php`: halaman profil yang menggabungkan
  bagian form profil.
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

`database/factories/UserFactory.php` membuat data pengguna untuk test. File
`database/seeders/DatabaseSeeder.php`, `ProductCategorySeeder.php`, dan
`ProductSeeder.php` mengisi akun contoh, kategori, dan produk.

### Test yang tersedia

- `tests/Feature/AdminCatalogTest.php`: pengelolaan produk dan kategori admin.
- `tests/Feature/DashboardTest.php`: angka ringkasan, klik produk, dan data pesanan dashboard.
- `tests/Feature/ShoppingCartTest.php`: katalog dan operasi keranjang.
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

## 15. Catatan belajar file konfigurasi

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
- `postcss.config.js` dan `tailwind.config.js`: konfigurasi pipeline CSS.
- `public/index.php` adalah pintu masuk request web; `artisan` adalah pintu masuk
  perintah terminal Laravel.

Nilai konfigurasi lokal berasal dari environment, terutama `.env`. Untuk belajar
struktur pengaturannya gunakan `.env.example`; jangan menyalin atau membagikan
password, token, atau key dari `.env`.
