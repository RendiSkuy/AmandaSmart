# 🛒 Portal B2B AmandaMart (Business-to-Business Supply Chain Portal)

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-15%2B-336791?style=for-the-badge&logo=postgresql&logoColor=white)](https://www.postgresql.org)
[![TailwindCSS](https://img.shields.io/badge/TailwindCSS-v3-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)

Portal B2B (Business-to-Business) AmandaMart adalah sistem informasi manajemen rantai pasok (*supply chain management*) terintegrasi yang menghubungkan tim internal **Merchandiser (MD)** AmandaMart dengan pihak **Supplier / Vendor Rekanan (Sales)** secara langsung. 

Sistem ini didesain untuk mendeteksi stok kritis gudang DC, memproses pemesanan barang otomatis (*Auto PO*), memfasilitasi lelang penawaran harga (*bidding*), memanajemen slot antrean kedatangan logistik (*VRS Schedule*), memverifikasi penerimaan barang fisik (*LPB*), serta memproses pemotongan retur barang cacat hingga menerbitkan nota penagihan bersih (*Tanda Terima Faktur / TTF*).

---

## 🚀 Fitur Utama & Alur Sistem (5-Stage Workflow)

Sistem B2B AmandaMart menerapkan **5 Tahap Alur Logistik Hulu-ke-Hilir** terintegrasi:

### 1. Deteksi Stok Kritis & Auto PO (Stage 1)
* Sistem mendeteksi stok kritis produk di Distribution Center (DC).
* Tim MD dapat memicu Stored Procedure `generate_auto_po_proc` yang secara instan menghitung selisih kapasitas gudang ($M_{\text{stock}} - \text{on\_hand}$) berdasarkan kelipatan pemesanan (*minor*), lalu menerbitkan draf Purchase Order (PO) berstatus `PENDING_BIDDING`.

### 2. Lelang Harga Bidding Supplier (Stage 2)
* Akun sales dari pihak supplier rekanan melihat draf PO multi-item terbuka.
* Supplier dapat mengajukan penawaran harga modal per PCS secara kolektif (*bulk submission*) untuk setiap item barang dalam PO tersebut.

### 3. Panel Penyetujuan & Penentuan Pemenang (Stage 3)
* Tim MD mengelola penawaran yang masuk melalui panel dashboard terpadu dengan desain layout **2-Kolom**:
  * **Kolom Kiri**: Accordion daftar supplier dan akun sales pengirim penawaran.
  * **Kolom Kanan**: Rincian penawaran harga terpilih, batas deadline kirim, kalkulasi grand total kotor, dan tombol persetujuan.
* Proses penyetujuan memanfaatkan Fetch API AJAX (DOM *fade-out*) sehingga data terproses secara instan tanpa reload halaman penuh. Status PO akan berganti menjadi `APPROVED` dan harga modal disepakati langsung dikunci.

### 4. Booking Slot Truk VRS (Stage 4)
* Supplier pemenang melakukan reservasi slot logistik kedatangan truk pengiriman (*VRS Booking*) secara bulk.
* Kapasitas gerbang bongkar muat DC dibatasi maksimal 5 truk per slot waktu per hari untuk mencegah kemacetan antrean armada di gerbang DC.

### 5. Penerimaan LPB & Otomatisasi Retur (Stage 5)
* Petugas gudang memverifikasi pembongkaran muatan fisik dengan memasukkan kuantitas diterima (`qty_received`):
  * **Validasi Ketat**: Backend dan frontend membatasi kuantitas masuk agar berada di rentang $0 \le \text{qty\_received} \le \text{qty\_po}$ (tidak boleh minus dan tidak boleh melebihi plafon PO).
  * **Hitung Retur Otomatis**: Kuantitas retur (`qty_retur`) terkunci secara *Read-Only* dan terisi otomatis berdasarkan selisih formula: $\text{qty\_retur} = \text{qty\_po} - \text{qty\_received}$.
  * **Catatan Alasan**: Kolom alasan retur dapat diisi manual jika ditemukan selisih barang rusak/cacat.
* Setelah LPB disimpan dalam blok *Database Transaction*, saldo stok fisik (`on_hand`) master produk DC langsung bertambah secara *real-time*.

### 6. Nota Tagihan Keuangan TTF (Stage 5 Finance)
* Nota Tanda Terima Faktur (TTF) diterbitkan otomatis setelah LPB tersimpan.
* Nilai tagihan bersih dihitung otomatis: $(\text{Qty Diterima} \times \text{Harga Final}) - (\text{Qty Retur} \times \text{Harga Final})$ dengan tempo pembayaran jatuh tempo transfer T+14 Hari Kerja. Halaman LPB dan TTF didesain ramah-cetak (*print-friendly*).

---

## 🔒 Fitur Keamanan (Google 2FA)
* Pengguna internal MD diwajibkan mengaktifkan keamanan **2FA (Two-Factor Authentication)** menggunakan aplikasi *Google Authenticator* (TOTP) demi melindungi data transaksi internal perusahaan dari akses ilegal.
* Desain halaman setup 2FA dioptimalkan tanpa scroll otomatis (*autofocus disabled*), menjaga petunjuk visual dan QR Code bagian atas agar tidak terpotong saat halaman pertama kali dimuat.

---

## 🛠️ Tech Stack & Spesifikasi Teknis

* **Backend**: Laravel 11.x, PHP 8.2+
* **Database**: PostgreSQL / SQLite (Dukungan database transaction, relational integrity constraints, dan database PL/pgSQL Stored Procedure).
* **Frontend**: Vanilla JS (Fetch API AJAX), Tailwind CSS (Play CDN), Blade Templates.
* **Keamanan**: Laravel Sanctum (API Tokens), Google 2FA.

---

## 💻 Panduan Instalasi & Pengoperasian

### 1. Prasyarat (*Prerequisites*)
Pastikan server lokal Anda telah terinstal:
* PHP >= 8.2 (dilengkapi ekstensi pdo, pgsql, dll.)
* Composer
* Node.js & NPM
* Database PostgreSQL / SQLite

### 2. Kloning Repositori
```bash
git clone https://github.com/RendiSkuy/AmandaSmart.git
cd AmandaSmart
```

### 3. Pasang Dependensi
```bash
composer install
npm install
```

### 4. Konfigurasi Environment File
Salin file `.env.example` menjadi `.env`, lalu sesuaikan kredensial koneksi database Anda:
```bash
cp .env.example .env
```

### 5. Migrasi Database & Seeding
Jalankan perintah berikut untuk membuat seluruh 11 tabel relasional dan memicu skrip stored procedure:
```bash
php artisan migrate
```

### 6. Jalankan Server Lokal
```bash
php artisan serve
npm run dev
```
Akses portal melalui peramban di `http://127.0.0.1:8000`.

---

## 🤖 Simulasi Transaksi Otomatis (Hulu ke Hilir)

Untuk menguji seluruh alur transaksi dari deteksi stok kritis, otomatisasi PO, penawaran bidding, VRS booking, LPB, retur, hingga invoice TTF secara praktis tanpa menggunakan Tinker, jalankan perintah simulasi kustom berikut di terminal Anda:
```bash
php artisan amandamart:simulasi
```
Perintah ini akan mencetak laporan log kalkulasi performa operasional, skor *service level* vendor, dan nominal keuangan bersih secara instan di CLI.

---

## 🧪 Pengujian Otomatis (*Automated Testing*)
Sistem ini dilengkapi 14 unit & feature test komprehensif (125 assertions) untuk memastikan tidak adanya kebocoran validasi. Jalankan pengujian menggunakan PHPUnit:
```bash
php artisan test
```
