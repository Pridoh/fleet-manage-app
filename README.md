# Fleet Management App

Aplikasi manajemen armada kendaraan untuk mengelola pemesanan, penggunaan, dan pemeliharaan kendaraan perusahaan.

## Deskripsi

Fleet Management App adalah aplikasi berbasis web yang dibangun menggunakan framework Laravel untuk memudahkan pengelolaan armada kendaraan perusahaan. Aplikasi ini memungkinkan pengguna untuk melakukan pemesanan kendaraan, persetujuan pemesanan, pencatatan penggunaan kendaraan, pelacakan maintenance, dan pembuatan laporan.

## Persyaratan Sistem

-   PHP >= 8.2
-   MySQL/MariaDB
-   Composer
-   Node.js dan NPM (untuk assets)

## Instalasi

1. Clone repositori ini

    ```
    git clone https://github.com/Pridoh/fleet-manage-app.git
    cd fleet-manage-app
    ```

2. Instal dependensi

    ```
    composer install
    npm install
    ```

3. Salin file .env.example menjadi .env dan sesuaikan konfigurasi database

    ```
    cp .env.example .env
    php artisan key:generate
    ```

4. Konfigurasi database di file .env

    ```
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=fleet_management
    DB_USERNAME=root
    DB_PASSWORD=
    ```

5. Jalankan migrasi dan seeder

    ```
    php artisan migrate --seed
    ```

6. Jalankan aplikasi
    ```
    php artisan serve
    ```

## Cara Penggunaan

### 1. Login

-   Akses aplikasi di http://localhost:8000
-   Login menggunakan kredensial yang sudah dibuat oleh seeder:
    -   **Admin**: admin@example.com / password
    -   **Approver**: approver1@example.com / password
    -   **Approver**: approver2@example.com / password

### 2. Manajemen Kendaraan

-   Lihat daftar kendaraan di menu "Kendaraan > Daftar Kendaraan"
-   Tambah kendaraan baru dengan klik tombol "Tambah Kendaraan"
-   Lihat detail, edit atau hapus kendaraan dari daftar

### 3. Pemesanan Kendaraan

-   Buat pemesanan kendaraan di menu "Pemesanan Kendaraan"
-   Isi formulir pemesanan dengan lengkap
-   Pemesanan akan dikirim untuk persetujuan

### 4. Persetujuan Pemesanan

-   Approver dapat menyetujui atau menolak pemesanan di menu "Persetujuan"
-   Lihat detail pemesanan sebelum memberikan persetujuan
-   Tambahkan catatan jika diperlukan

### 5. Penggunaan Kendaraan

-   Setelah disetujui, pemesanan akan muncul di menu "Kendaraan > Penggunaan Kendaraan"
-   Admin dapat menetapkan driver dan kendaraan
-   Catat odometer awal dan akhir penggunaan

### 6. Log Kendaraan

-   Akses "Kendaraan > Log Kendaraan" untuk melihat semua log penggunaan
-   Catat log keberangkatan, kedatangan, pengisian BBM, dan insiden
-   Lihat efisiensi penggunaan BBM dan jarak tempuh

### 7. Maintenance Kendaraan

-   Jadwalkan dan catat maintenance di menu "Kendaraan > Maintenance"
-   Pantau status maintenance kendaraan
-   Tetapkan jadwal maintenance berkala

### 8. Laporan

-   Buat laporan penggunaan kendaraan di menu "Laporan"
-   Filter laporan berdasarkan periode waktu, kendaraan, atau driver
-   Export laporan ke Excel untuk analisis lebih lanjut

## Fitur Utama

-   **Manajemen Kendaraan**: Pendaftaran, pengelolaan, dan pelacakan status kendaraan
-   **Manajemen Driver**: Data lengkap driver dengan status ketersediaan
-   **Pemesanan Kendaraan**: Sistem pemesanan kendaraan dengan workflow persetujuan
-   **Persetujuan Bertingkat**: Sistem persetujuan pemesanan kendaraan
-   **Pencatatan Penggunaan**: Detail penggunaan kendaraan termasuk odometer, BBM, dan efisiensi
-   **Log Perjalanan**: Pencatatan detail aktivitas selama perjalanan
-   **Maintenance**: Penjadwalan dan pencatatan maintenance kendaraan
-   **Laporan**: Pembuatan laporan penggunaan kendaraan dan efisiensi BBM

## Struktur Aplikasi

-   **Controllers**: app/Http/Controllers
-   **Models**: app/Models
-   **Views**: resources/views
-   **Routes**: routes/web.php
-   **Database Migrations**: database/migrations
-   **Database Seeders**: database/seeders

## Pengembangan Selanjutnya

Berikut adalah fitur yang direncanakan untuk pengembangan selanjutnya:

-   Integrasi dengan GPS untuk pelacakan lokasi kendaraan real-time
-   Aplikasi mobile untuk driver
-   Dashboard analitik lanjutan
-   Notifikasi melalui email dan SMS
-   Pengelolaan biaya dan anggaran
-   Integrasi dengan sistem akuntansi

