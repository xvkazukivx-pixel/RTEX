# RTEX — Sistem Manajemen Iuran, Kas & Layanan RT Digital (Laravel Edition)

Aplikasi manajemen operasional warga RT berbasis **Laravel 11/12 (PHP 8.3+) + MySQL / SQLite** dengan 5 peran pengguna:
1. **Super Admin**: Akses penuh seluruh modul dan pengelolaan akun.
2. **Bendahara**: Manajemen iuran, transaksi, konfirmasi pembayaran, dan laporan keuangan saja.
3. **RT**: Konfirmasi surat, kegiatan, dan validasi laporan keuangan.
4. **RW**: Konfirmasi surat, kegiatan, dan validasi laporan keuangan.
5. **Warga**: Pembayaran iuran, riwayat, transparansi kas, bank sampah, koperasi, posyandu, surat, dan kegiatan.

---

## 🚀 Cara Menjalankan di Laragon

1. Buka **Laragon** dan klik **Start All**.
2. Akses aplikasi melalui browser:
   - Menggunakan virtual host Laragon: **`http://rtex.test`**
   - Atau langsung via folder: **`http://localhost/rtex/public/`**
   - Atau via artisan serve: `php artisan serve` (akses `http://127.0.0.1:8000`)

---

## 🔑 Akun Demo (Bawaan Seeder)

| Peran | Username | Password | Keterangan |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `superadmin` | `super123` | Akses penuh seluruh fitur |
| **Bendahara** | `admin` | `admin123` | Modul keuangan saja |
| **RT** | `ketua` | `ketua123` | Konfirmasi surat, kegiatan, validasi kas |
| **RW** | `rw` | `rw123` | Konfirmasi surat, kegiatan, validasi kas |
| **Warga** | `hafis` | `warga123` | Akun warga terhubung data Bpk Hafis |

---

## 🛠️ Konfigurasi Database

Secara default, aplikasi sudah terkonfigurasi dengan database SQLite lokal siap pakai tanpa konfigurasi tambahan. 

Jika Anda ingin menghubungkan ke **MySQL Laragon**:
1. Buat database baru di HeidiSQL / phpMyAdmin bernama `rtex`.
2. Buka `.env`, ubah:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=rtex
   DB_USERNAME=root
   DB_PASSWORD=
   ```
3. Jalankan migrasi & data awal:
   ```bash
   php artisan migrate --seed
   ```
