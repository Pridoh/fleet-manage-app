# Fleet Management System

Aplikasi manajemen armada kendaraan untuk perusahaan tambang nikel. Aplikasi ini digunakan untuk memantau dan mengelola kendaraan perusahaan, termasuk pemesanan kendaraan, persetujuan, monitoring penggunaan BBM, dan maintenance.

## Spesifikasi Teknis

-   PHP Version: 8.2
-   Laravel Version: 11.0
-   Database: MySQL 8.0
-   Frontend: Bootstrap 5.3 & Chart.js

## Fitur

1. **Manajemen Pengguna**

    - Multi-level user (Admin dan Approver)
    - Autentikasi dan Otorisasi

2. **Pemesanan Kendaraan**

    - Form pemesanan dengan detail lengkap
    - Sistem persetujuan berjenjang (minimal 2 level)
    - Histori pemesanan

3. **Persetujuan**

    - Sistem approval berjenjang
    - Notifikasi persetujuan
    - Alasan penolakan

4. **Dashboard**

    - Grafik penggunaan kendaraan
    - Statistik pemesanan
    - Monitoring BBM

5. **Laporan**
    - Laporan pemesanan kendaraan
    - Laporan konsumsi BBM
    - Laporan maintenance
    - Export ke Excel

## Informasi Akun

### Admin

-   Email: admin@example.com
-   Password: password

### Approver 1

-   Email: approver1@example.com
-   Password: password

### Approver 2

-   Email: approver2@example.com
-   Password: password

## Instalasi

1. **Clone repositori**

    ```
    git clone https://github.com/username/fleet-management-app.git
    cd fleet-management-app
    ```

2. **Install dependensi**

    ```
    composer install
    npm install
    ```

3. **Setup konfigurasi lingkungan**

    ```
    cp .env.example .env
    php artisan key:generate
    ```

4. **Konfigurasi database di file .env**

    ```
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=db_fms
    DB_USERNAME=root
    DB_PASSWORD=
    ```

5. **Migrasi dan seed database**

    ```
    php artisan migrate --seed
    ```

6. **Compile aset frontend**

    ```
    npm run dev
    ```

7. **Jalankan aplikasi**

    ```
    php artisan serve
    ```

8. **Buka aplikasi di browser**
    ```
    http://localhost:8000
    ```

## Panduan Pengguna

### Membuat Pemesanan Kendaraan

1. Login ke sistem
2. Klik menu "Pemesanan Kendaraan"
3. Klik tombol "Buat Pemesanan"
4. Isi form pemesanan dengan detail yang diperlukan
5. Pilih atasan/pihak yang menyetujui
6. Klik "Simpan Pemesanan"

### Menyetujui Pemesanan

1. Login sebagai approver
2. Klik menu "Persetujuan"
3. Pilih permintaan yang akan disetujui
4. Klik tombol "Detail" untuk melihat rincian
5. Klik "Setujui" atau "Tolak" sesuai keputusan

### Melihat Laporan

1. Login ke sistem
2. Klik menu "Laporan"
3. Pilih jenis laporan (Pemesanan, BBM, atau Maintenance)
4. Atur filter tanggal dan parameter lainnya
5. Klik "Filter" untuk menampilkan data
6. Klik "Export Excel" untuk mengunduh laporan

## Alur Proses Pemesanan Kendaraan

1. User mengajukan pemesanan kendaraan
2. Permintaan dikirim ke atasan/manager untuk persetujuan (Level 1) & (Level 2)
3. Admin pool menyetujui dan menentukan kendaraan & driver
4. User menerima notifikasi persetujuan
5. Kendaraan digunakan sesuai jadwal
6. Setelah selesai, user menyelesaikan pemesanan

## Catatan Teknis

-   Gunakan PHP 8.2 atau yang lebih tinggi
-   Pastikan folder storage dan bootstrap/cache dapat ditulis oleh web server
-   Gunakan MySQL 8.0 atau versi yang kompatibel

## Pengembangan Selanjutnya

-   Integrasi GPS tracking
-   Mobile app untuk driver
-   QR Code untuk pemeriksaan kendaraan
-   Sistem reminder maintenance

---

Dibuat dengan ❤️ untuk kebutuhan manajemen armada kendaraan perusahaan tambang nikel.
