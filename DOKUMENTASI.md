# 📘 DOKUMENTASI STRUKTUR FILE SYSTEM & ARSITEKTUR B2B AMANDAMART
*(Berkas Pemetaan Komponen Utama Aplikasi untuk Laporan Magang)*

Dokumen ini memuat peta lengkap seluruh berkas (*file system map*) yang menyusun **Sistem Informasi Manajemen Rantai Pasok B2B AmandaMart**, penjelasan fungsi rinci masing-masing berkas, arsitektur sistem, alur integrasi data, serta panduan instalasi dan pengoperasian.

---

## 📂 1. Peta Struktur Direktori Aplikasi

Berikut adalah peta struktur berkas utama yang membangun aplikasi B2B AmandaMart:

```
amandasmart-b2b/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       └── SimulasiRetailCommand.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── LPBController.php
│   │   │   │   ├── NotificationController.php
│   │   │   │   ├── PurchaseOrderController.php
│   │   │   │   ├── ReturController.php
│   │   │   │   ├── ServiceLevelController.php
│   │   │   │   ├── TTFController.php
│   │   │   │   └── VRSController.php
│   │   │   └── WebDashboardController.php
│   │   └── Middleware/
│   │       ├── TwoFactorSession.php
│   │       └── RoleMiddleware.php
│   └── Models/
│       ├── User.php
│       ├── Supplier.php
│       ├── Product.php
│       ├── PurchaseOrder.php
│       ├── Offer.php
│       ├── VrsSchedule.php
│       ├── GoodsReceipt.php
│       ├── Retur.php
│       └── Ttf.php
├── database/
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 2026_04_13_172210_create_suppliers_table.php
│   │   ├── 2026_04_13_172211_create_products_table.php
│   │   ├── 2026_04_13_172211_create_purchase_orders_table.php
│   │   ├── 2026_04_13_172212_create_goods_receipts_table.php
│   │   ├── 2026_04_13_172215_create_ttf_table.php
│   │   ├── 2026_04_14_170958_create_returs_table.php
│   │   ├── 2026_04_16_181708_create_vrs_schedules_table.php
│   │   ├── 2026_05_25_000000_create_offers_table.php
│   │   └── 2026_05_28_000000_create_generate_auto_po_proc.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── SupplierSeeder.php
│       ├── ProductPoSeeder.php
│       ├── PurchaseOrderSeeder.php
│       ├── GoodsReceiptSeeder.php
│       ├── ReturSeeder.php
│       └── VrsScheduleSeeder.php
├── resources/
│   └── views/
│       ├── auth/
│       │   ├── login-md.blade.php
│       │   ├── login-supplier.blade.php
│       │   ├── 2fa-setup.blade.php
│       │   └── 2fa-verify.blade.php
│       ├── welcome.blade.php
│       ├── dashboard.blade.php
│       ├── lpb-detail.blade.php
│       └── ttf-detail.blade.php
├── routes/
│   ├── api.php
│   └── web.php
└── tests/
    └── Feature/
        └── ExampleTest.php
```

---

## 📄 2. Penjelasan Detail Komponen & Fungsi Berkas

### 🗃️ A. Model Eloquent (Direktori `app/Models/`)
Model digunakan untuk merepresentasikan dan berinteraksi dengan tabel-tabel di database menggunakan ORM (Object-Relational Mapping):

1. **`User.php`**: Menyimpan kredensial pengguna, peran akses (`role` = `md` atau `supplier`), dan secret key 2FA Google Authenticator. Mengatur relasi ke `Supplier`.
2. **`Supplier.php`**: Menyimpan profil resmi mitra vendor (Nama PT, Kode Vendor, nomor WA). Berelasi `hasMany` dengan `User` (untuk akun sales) dan `PurchaseOrder`.
3. **`Product.php`**: Menyimpan master data produk ritel (PLU Code, Nama, Kuantitas `on_hand`, Safety stock `minor`, dan kapasitas gudang `max_stock`).
4. **`PurchaseOrder.php`**: Menyimpan transaksi Purchase Order (PO). Mengatur data produk yang dibeli, kuantitas order, status PO, tenggat pengiriman, dan vendor terpilih.
5. **`Offer.php`**: Menyimpan data penawaran harga modal per PCS yang diajukan oleh akun sales supplier terhadap draf PO yang berstatus `PENDING_BIDDING`.
6. **`VrsSchedule.php`**: Mengelola jadwal antrean truk bongkar muat (*Vehicle Reservation System*) di gudang DC untuk PO yang telah disetujui.
7. **`GoodsReceipt.php`**: Menyimpan berkas Laporan Penerimaan Barang (LPB) fisik tiba di gudang.
8. **`Retur.php`**: Menyimpan data retur untuk barang rusak/cacat yang ditemukan saat pembongkaran fisik barang di gudang.
9. **`Ttf.php`**: Menyimpan Tanda Terima Faktur (TTF) yang mencatat penagihan keuangan bersih yang siap dibayarkan ke supplier.

---

### 🎮 B. Pengendali Logika (Direktori `app/Http/Controllers/`)

#### 💻 Web Controller (Dashboard & AJAX Handlers)
* **`WebDashboardController.php`**:
  * **`index`**: Mengumpulkan data produk, PO, VRS, LPB, TTF, dan menghitung laporan kepatuhan *Service Level* kualitatif untuk dirender pada file `dashboard.blade.php`.
  * **`generateAutoPO`**: Menjalankan stored procedure restock stok kritis via DB.
  * **`submitOffer`**: Memproses penawaran harga modal bulk dari portal supplier.
  * **`approveOffer`**: Menyetujui pemenang bidding lelang secara dinamis tanpa reload halaman.
  * **`createVrsBooking`**: Memproses pendaftaran antrean truk secara bulk untuk portal supplier.
  * **`storeLpb` & `generateTtf`**: Membuka blok transaksi database untuk menyimpan LPB, retur, serta menerbitkan faktur tagihan TTF.
  * **`showLpb` & `showTtf`**: Menyediakan tampilan ramah-cetak (print-friendly view).
  * **`updateProfile`**: Memproses perubahan nomor WA dan password supplier.

#### 📱 API Controllers (Saluran decoupled `/api/*`)
* **`Api/AuthController.php`**: Mengelola login token API via Sanctum, login web, dan keamanan Multi-Factor setup/verifikasi 2FA.
* **`Api/PurchaseOrderController.php`**: Menyediakan endpoint API bagi supplier untuk melihat PO, menginput penawaran lelang, dan membandingkan harga.
* **`Api/LPBController.php`**: Menyimpan data penerimaan barang masuk dari client API eksternal.
* **`Api/VRSController.php`**: Mengontrol booking slot antrean kendaraan pengiriman logistik.
* **`Api/TTFController.php`**: Menyusun kalkulasi nota penagihan bersih otomatis.
* **`Api/ServiceLevelController.php`**: Menghitung skor persentase kualitatif performa kedatangan supplier.
* **`Api/NotificationController.php`**: Dispatch log notifikasi pesan.

---

### 🛡️ C. Keamanan & Peran (Middleware)
* **`Http/Middleware/RoleMiddleware.php`**: Menyaring akses rute. Memastikan pengguna dengan peran `md` tidak dapat masuk ke panel supplier, begitu pula sebaliknya demi integritas sistem.
* **`Http/Middleware/TwoFactorSession.php`**: Memvalidasi sesi 2FA sementara untuk proses pendaftaran setup Google Authenticator.

---

### 💾 D. Migrasi & Skema DB (Direktori `database/migrations/`)
* **`0001_01_01_000000_create_users_table.php`**: Membuat tabel `users` dengan field 2FA (`google_2fa_secret`) dan foreign key `supplier_id`.
* **`2026_04_13_172210_create_suppliers_table.php`**: Skema tabel profil supplier.
* **`2026_04_13_172211_create_products_table.php`**: Skema tabel inventori produk DC.
* **`2026_04_13_172211_create_purchase_orders_table.php`**: Skema tabel transaksi PO.
* **`2026_05_25_000000_create_offers_table.php`**: Skema tabel pengumpulan lelang harga penawaran supplier.
* **`2026_05_28_000000_create_generate_auto_po_proc.php`**: Migrasi khusus yang mengompilasi **Stored Procedure** database (`generate_auto_po_proc`) untuk mendeteksi otomatis stok kritis di bawah kapasitas maksimum dan melakukan generate draf PO.

---

### 🎨 E. Tampilan Antarmuka (Direktori `resources/views/`)
* **`welcome.blade.php`**: Halaman landing page utama dengan tombol login portal MD dan login portal Supplier rekanan. Dilengkapi dengan toggle Light/Dark Mode.
* **`dashboard.blade.php`**: Layout dasbor modern dengan navigasi Left Sidebar responsif. Mengatur tampilan tab dinamis (`products`, `bidding`, `vrs`, `lpb`, `ttf`, `profile`, `service_level`) serta dilengkapi script AJAX Fetch interceptors untuk aksi no-refresh.
* **`lpb-detail.blade.php` & `ttf-detail.blade.php`**: Halaman detail cetak LPB dan TTF yang dioptimalkan untuk cetak fisik maupun penyimpanan file PDF (`@media print`).
* **`auth/login-md.blade.php` & `auth/login-supplier.blade.php`**: Halaman login elegan dengan validasi formulir dan desain modern yang ramah pengguna.
* **`auth/2fa-setup.blade.php` & `auth/2fa-verify.blade.php`**: Antarmuka pendaftaran QR Code Google Authenticator dan penginputan token OTP 2FA.

---

### 🧭 F. Rute Aplikasi (Direktori `routes/`)
* **`routes/web.php`**: Mengontrol rute browser web (Login, logout, visual dashboard, update profil, print LPB/TTF).
* **`routes/api.php`**: Mengontrol rute API berstatus state-less yang diproteksi token Sanctum untuk integrasi sistem decoupled.

---

## 🛠️ 3. Panduan Instalasi (Installation Guide)

### Prasyarat System:
1. **PHP** versi 8.2 atau lebih baru.
2. **Composer** (untuk dependensi Laravel).
3. **Database server** (PostgreSQL / SQLite).

### Langkah-Langkah Setup Project:

1. **Clone Repositori**:
   ```bash
   git clone <url_repositori> amandasmart-b2b
   cd amandasmart-b2b
   ```

2. **Pasang Dependensi Framework**:
   ```bash
   composer install
   ```

3. **Duplikasi Konfigurasi Environment**:
   ```bash
   cp .env.example .env
   ```
   Atur koneksi database Anda di dalam berkas `.env` tersebut. Contoh menggunakan PostgreSQL:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=amandasmart_b2b
   DB_USERNAME=postgres
   DB_PASSWORD=sandi_db_anda
   ```

4. **Generate Enkripsi Key**:
   ```bash
   php artisan key:generate
   ```

5. **Migrasi Struktur Tabel & Seeders Data**:
   Jalankan perintah ini untuk membersihkan DB, menyusun skema tabel, memicu stored procedure, dan menyemai data tiruan:
   ```bash
   php artisan migrate:fresh --seed
   ```

---

## 🚀 4. Panduan Menjalankan Sistem (Usage Guide)

### 1. Menjalankan Server Aplikasi
Jalankan perintah serve lokal Laravel:
```bash
php artisan serve
```
Akses aplikasi melalui browser Anda pada alamat: `http://127.0.0.1:8000`

### 2. Kredensial Pengguna untuk Uji Coba

* **Akun Merchandiser (MD)**:
  * URL: `http://127.0.0.1:8000/login-md`
  * Username: `rendi_md`
  * Password: `password123`
  * Keamanan: Siapkan Google Authenticator di HP untuk memindai QR Code pada login pertama kali.
* **Akun Supplier (Sales)**:
  * URL: `http://127.0.0.1:8000/login-supplier`
  * Username: `unilever_sales1` (atau `indofood_sales1`, `wings_sales1`, `mayora_sales1`, `nestle_sales1`)
  * Password: `password123`

### 3. Pengujian Otomatis (Testing)
Untuk memastikan seluruh logika kode dan integrasi berjalan 100% tanpa kegagalan:
```bash
php artisan test
```

### 4. Menjalankan Simulasi Rantai Pasok
Anda dapat menyimulasikan seluruh alur pengadaan barang ritel dari hulu ke hilir (Stage 1 s.d. Stage 5) secara otomatis menggunakan perintah command line:
```bash
php artisan amandamart:simulasi
```
Perintah ini akan secara otomatis memicu stored procedure stok kritis, membuat PO bidding, mengirim penawaran harga modal termurah, melakukan booking slot truk VRS, membuat LPB fisik bongkar muat, mencatat retur barang rusak, serta menerbitkan TTF nota keuangan tagihan.

---
*Dokumen ini disusun untuk memberikan transparansi struktur berkas dan kemudahan pengoperasian Sistem Informasi B2B AmandaMart.*
