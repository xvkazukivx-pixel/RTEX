# RTEX - Sistem Iuran, Kas & Layanan Warga RT Digital

Aplikasi manajemen administrasi RT/RW modern berbasis **Laravel 11 (Backend)** dan **Vite Tailwind CSS (Frontend)** dengan integrasi **IoT Smart Gate RFID/NFC (e-KTP)**.

---

## 🚀 Panduan Menjalankan Aplikasi

Aplikasi telah dipisahkan menjadi dua folder terpisah: `frontend` dan `backend`.

### 1. Menjalankan Backend (Laravel)
Buka terminal / bash pertama, lalu jalankan:
```bash
cd backend
php artisan serve
```
> Server backend akan aktif di: **http://127.0.0.1:8000**

---

### 2. Menjalankan Frontend (Vite Dev Server)
Buka terminal / bash kedua, lalu jalankan:
```bash
cd frontend
npm run dev
```
> Server frontend akan aktif di: **http://localhost:5173**  
> *(Seluruh request web/API otomatis di-proxy ke Laravel port 8000 dengan Hot Module Replacement)*

---

### ⚡ Jalankan Keduanya Sekaligus (Opsional dari Root)
Anda juga dapat menjalankan frontend dan backend secara bersamaan dalam satu terminal dari folder utama:
```bash
npm run dev
```

---

## 📁 Struktur Direktori

```text
rtex/
├── backend/                       # Seluruh Kode & Logika Backend Laravel
│   ├── app/                       # Model, Controller, Middleware
│   ├── bootstrap/                 # Konfigurasi Booting Framework
│   ├── config/                    # Pengaturan Database, App, Auth
│   ├── database/                  # Migrasi & Seeder Database MySQL
│   ├── firmware_esp32/            # Kode Sketch Arduino (.ino) untuk Smart Gate
│   ├── public/                    # Assets, Build, dan index.php Laravel
│   ├── resources/                 # Blade Views (Tampilan Web)
│   ├── routes/                    # Rute Web & API (web.php, api.php)
│   ├── storage/                   # File Upload & Cache
│   ├── tests/                     # Unit & Feature Test Suite
│   ├── .env                       # Konfigurasi Environment Backend
│   ├── artisan                    # CLI Artisan Laravel
│   └── composer.json              # Dependensi PHP
│
├── frontend/                      # Lingkungan Frontend & Build Assets
│   ├── src/                       # Source CSS & JavaScript
│   │   ├── css/app.css            # Tailwind CSS Styles
│   │   └── js/app.js              # Frontend JavaScript Entrypoint
│   ├── index.html                 # Halaman Dev Portal Vite
│   ├── package.json               # Dependensi NPM Frontend
│   └── vite.config.js             # Konfigurasi Vite & Proxy Backend
│
├── public/                        # Forwarder Root untuk Web Server Hosting
├── package.json                   # Root Script Launcher
└── README.md                      # Dokumentasi Proyek
```

---

## 🔑 Akun Akses Cepat Login

| Peran | Username | Password |
|---|---|---|
| **Super Admin** | `superadmin` | `super123` |
| **Ketua RT** | `ketua` | `ketua123` |
| **Bendahara RT** | `admin` | `admin123` |
| **Sekretaris RT** | `sekretaris_rt` | `sekretaris123` |
| **Ketua RW** | `rw` | `rw123` |
| **Bendahara RW** | `bendahara_rw` | `bendahararw123` |
| **Sekretaris RW** | `sekretaris_rw` | `sekretarisrw123` |
| **Warga** | `hafis` | `warga123` |

---

## 📡 Integrasi IoT Smart Gate (e-KTP)
- Firmware Arduino ESP32: `backend/firmware_esp32/rtex_portal_esp32.ino`
- Endpoint Tap Reader: `POST /api/iot/gate/tap`
