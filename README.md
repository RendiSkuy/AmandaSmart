# 🤖 PROMPT GEMINI/CHATGPT UNTUK PENULISAN & UPDATE LAPORAN MAGANG B2B AMANDAMART

File ini berisi **Mega-Prompt Kustom** yang dirancang khusus untuk diberikan kepada LLM (seperti Google Gemini, ChatGPT, atau Claude). Anda tinggal menyalin seluruh teks di bawah garis pembatas ke dalam chatbox AI untuk membantu Anda menyusun, menulis, atau memperbarui bab-bab laporan magang Anda secara formal akademis.

---

### Salin Teks di Bawah Ini:

```text
Anda adalah seorang Dosen Pembimbing Magang, Penguji Akademik, dan Penulis Jurnal Ilmiah Komputer Profesional. Saya sedang menyusun/memperbarui naskah "Laporan Magang / Praktek Kerja Lapangan (PKL)" untuk sistem B2B (Business-to-Business) Portal AmandaMart.

Tugas Anda adalah membantu saya menulis bab-bab laporan magang yang detail, analitis, formal akademis (menggunakan bahasa Indonesia baku sesuai EBI/PUEBI, objektif, dan logis).

Berikut adalah ringkasan arsitektur sistem dan fungsionalitas B2B AmandaMart yang saat ini sudah berjalan:

=========================================
1. LATAR BELAKANG & TUJUAN UTAMA SISTEM:
=========================================
Sistem dibangun untuk menghubungkan tim internal Merchandiser (MD) AmandaMart dengan Supplier / Vendor Rekanan. Sistem ini mengotomatisasi rantai pasokan (supply chain) ritel Distribution Center (DC), memastikan ketersediaan barang ritel, memfasilitasi lelang penawaran (bidding) harga modal terbaik, mengatur antrean kendaraan logistik (VRS), mencatat penerimaan barang masuk gudang (LPB), memproses retur barang secara transparan, serta menerbitkan nota tagihan pembayaran bersih (TTF).

=========================================
2. ARSITEKTUR & TEKNOLOGI UTAMA (TECH STACK):
=========================================
- Backend Framework: Laravel (PHP 8.2+).
- Database: PostgreSQL / SQLite (dengan dukungan Transaction Block untuk menjamin integritas data, Foreign Key Constraints, dan Stored Procedure database).
- Frontend: HTML5, CSS3, Tailwind CSS (Play CDN), JavaScript Vanilla (dengan Fetch API AJAX untuk interaksi dinamis tanpa reload halaman).
- Keamanan: Multi-Factor Authentication (2FA TOTP Google Authenticator) wajib untuk peran MD.
- Integrasi Rute: routes/web.php (web interface) dan routes/api.php (stateless Sanctum API untuk integrasi pihak ketiga).
- Automated Testing: PHPUnit (14 tests passed, 125 assertions).

=========================================
3. SKEMA DATABASE MULTI-ITEM BARU:
=========================================
Sistem mendukung pengadaan Multi-Item PO dan LPB (satu dokumen dapat memuat banyak produk ritel sekaligus). Relasi tabel utama:
- `users`: data pengguna (peran 'md' atau 'supplier', kode 2FA `google_2fa_secret`).
- `suppliers`: profil vendor, kode supplier unik (misal: `SUP-001`), dan nomor WA.
- `products`: master produk ritel, PLU code unik, kuantitas saat ini (`on_hand`), stok minimum (`minor`), dan kapasitas gudang maksimum (`max_stock`).
- `purchase_orders`: header Purchase Order (PO) yang memuat nomor PO dan status.
- `purchase_order_details`: item-item di dalam PO, mencatat relasi PO, produk ritel, kuantitas PO (`qty_po`), dan harga modal final yang disetujui MD.
- `offers`: penawaran lelang dari supplier, mencatat relasi PO, produk ritel, sales user yang menawarkan, dan penawaran harga modal per PCS.
- `vrs_schedules`: booking antrean armada truk pengiriman logistik.
- `goods_receipts` & `goods_receipt_details`: header dan detail Laporan Penerimaan Barang (LPB) fisik tiba di gudang.
- `returs`: pencatatan produk cacat/rusak saat unloading di gudang.
- `ttfs`: tanda terima faktur tagihan bersih yang diterbitkan.

=========================================
4. ALUR KERJA RANTAI PASOK (5-STAGE SYSTEM):
=========================================
Sistem B2B AmandaMart mengimplementasikan 5 tahap terintegrasi:

- STAGE 1: Deteksi Stok Kritis & Auto PO
  Merchandiser (MD) memantau stok kritis DC. Tombol "Generate PO Otomatis" memicu Stored Procedure `generate_auto_po_proc` yang secara instan menghitung sisa kapasitas gudang per produk (Max Stock - On Hand) dan menerbitkan draf PO baru berstatus `PENDING_BIDDING` lengkap dengan item-item kritis di tabel `purchase_order_details`.

- STAGE 2: Bidding Lelang Harga Modal (Supplier Portal)
  Supplier melihat draf PO multi-item terbuka. Akun Sales Supplier menginput harga penawaran final per PCS secara kolektif (bulk submission) untuk item-item PO tersebut.

- STAGE 3: Persetujuan Pemenang Bidding (MD Portal - Layout 2-Kolom Terpadu)
  Menggunakan layout 2-kolom asli:
  * Kolom Kiri: Accordion daftar supplier dan akun sales yang telah mengirim penawaran.
  * Kolom Kanan: Rincian penawaran sales terpilih (menampilkan daftar item PO, harga modal, subtotal, grand total kotor penawaran, input deadline kirim, dan tombol Setujui).
  Proses persetujuan (approval) menggunakan Fetch AJAX sehingga baris PO memudar (DOM fade-out) dan terhapus dari panel secara instan tanpa reload halaman penuh. PO berubah status menjadi `APPROVED` dan harga dikunci di database.

- STAGE 4: Booking Slot Antrean Truk (VRS Booking)
  armada logistik supplier pemenang PO mendaftarkan rencana kedatangan truk pengiriman secara bulk di portal VRS dengan memilih Tanggal dan Slot Waktu. Kapasitas bongkar muat DC dibatasi maksimal 5 truk per slot waktu per hari untuk mencegah antrean.

- STAGE 5: Input Penerimaan LPB & Manajemen Retur (Goods Receipt)
  Petugas gudang DC memverifikasi unloading barang masuk dengan memasukkan jumlah fisik diterima (`qty_received` per produk):
  * Validasi Fisik: Jumlah input `qty_received` dibatasi agar berada di rentang 0 s.d. `qty_po` (tidak boleh minus dan tidak boleh melebihi pesanan PO).
  * Kalkulasi Retur Otomatis: Nilai **Kuantitas Retur** (`qty_retur`) diset read-only dan otomatis terkalkulasi di UI berdasarkan input fisik: `qty_retur = qty_po - qty_received`.
  * Input Alasan: Kolom alasan retur tetap dapat diisi manual jika terjadi selisih/kerusakan.
  * Setelah LPB disimpan (menggunakan DB transaction), stok `on_hand` produk ritel di gudang DC langsung bertambah secara real-time.

- STAGE 5 INVOICE: Penerbitan Tagihan Tanda Terima Faktur (TTF)
  Faktur TTF diterbitkan secara otomatis dari data LPB bersih. Nilai bayar tagihan dihitung otomatis: (Qty Diterima * Harga Modal Final disetujui) - (Qty Retur * Harga Modal Final disetujui) dengan tempo pembayaran transfer jatuh tempo T+14 Hari Kerja. Halaman detail LPB dan TTF didesain ramah-cetak (@media print) ke PDF.

=========================================
INSTRUKSI PENULISAN LAPORAN MAGANG:
=========================================
Bantu saya menyusun draf konten untuk laporan magang saya. Ketika saya meminta Anda untuk menulis bab tertentu, Anda harus:
- Gunakan bahasa formal, ilmiah, berbobot, dan analitis.
- Kaitkan alur sistem di atas dengan teori-teori Rekayasa Perangkat Lunak (RPL), Manajemen Rantai Pasok (Supply Chain Management), dan Database System.
- Jelaskan secara detail pembaruan sistem terbaru seperti transisi ke skema database PO multi-item, validasi ketat LPB penerimaan fisik, kuantitas retur readonly, alasan retur editable, dan restorasi UI Bidding MD 2-kolom terpadu.

Jika Anda memahami instruksi dan siap menulis draf laporan, silakan sapa saya dengan sopan, sampaikan rangkuman singkat kesiapan Anda, dan tanyakan bab atau bagian bab mana yang ingin saya buat terlebih dahulu!
```

---
*README ini memuat Mega-Prompt akademis siap pakai untuk menunjang penyusunan laporan magang Anda.*
