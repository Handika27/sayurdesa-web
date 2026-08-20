# SayurDesa - Aplikasi Pemesanan Sayur Online

Aplikasi web pemesanan sayur online berbasis PHP Native, MySQL, dan Bootstrap 5.

## Fitur Utama

### Untuk Pelanggan
- Registrasi dan Login
- Lihat katalog produk
- Cari produk
- Lihat detail produk (informasi gizi, manfaat kesehatan, dll)
- Keranjang belanja
- Checkout dengan metode pembayaran COD dan Transfer Bank
- Lihat riwayat pesanan
- Pesan via WhatsApp

### Untuk Admin
- Dashboard dengan statistik dan grafik penjualan
- Manajemen produk (tambah, edit, hapus)
- Manajemen kategori
- Manajemen pesanan (ubah status)
- Laporan penjualan

## Struktur Folder

```
sayurdesa/
├── admin/              # Halaman admin
├── assets/             # Asset (css, js, images)
│   ├── css/
│   ├── js/
│   └── images/
├── config/             # Konfigurasi database
├── database/           # File SQL database
├── pelanggan/          # Halaman pelanggan
└── *.php               # Halaman utama
```

## Panduan Instalasi

### 1. Persyaratan Sistem
- XAMPP (PHP 7.4 atau lebih baru, MySQL)
- Web browser

### 2. Instalasi

1. **Pindahkan Folder Proyek**
   - Salin folder `sayurdesa` ke direktori `C:\xampp\htdocs\` (Windows) atau `/Applications/XAMPP/htdocs/` (macOS)

2. **Jalankan XAMPP**
   - Buka XAMPP Control Panel
   - Jalankan Apache dan MySQL

3. **Buat Database**
   - Buka phpMyAdmin di browser: `http://localhost/phpmyadmin`
   - Klik tab "Import"
   - Pilih file `sayurdesa/database/database.sql`
   - Klik "Go"

4. **Konfigurasi Database**
   - Buka file `sayurdesa/config/config.php`
   - Sesuaikan konfigurasi database jika diperlukan (default: user `root`, password kosong)

5. **Akses Aplikasi**
   - Buka browser dan akses: `http://localhost/sayurdesa`

### 3. Akun Default

#### Admin
- Email: `admin@sayurdesa.com`
- Password: `password`

#### Pelanggan
- Daftar akun baru melalui halaman registrasi

## Teknologi yang Digunakan
- PHP Native
- MySQL
- Bootstrap 5
- Font Awesome
- Chart.js
