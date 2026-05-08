# 🏦 KasKita — Grup Keuangan Keluarga Pintar

[![Laravel Version](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel)](https://laravel.com)
[![Vue Version](https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=for-the-badge&logo=vue.js)](https://vuejs.org)
[![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-v4.0-38B2AC?style=for-the-badge&logo=tailwind-css)](https://tailwindcss.com)
[![TypeScript](https://img.shields.io/badge/TypeScript-5.x-3178C6?style=for-the-badge&logo=typescript)](https://www.typescriptlang.org)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge)](https://opensource.org/licenses/MIT)

**KasKita** adalah aplikasi asisten dan pelacak keuangan keluarga kolaboratif berbasis web yang didesain dengan pendekatan *mobile-first*. Dibuat menggunakan teknologi termutakhir untuk membantu keluarga mencatat, merencanakan anggaran, memantau dompet bersama secara real-time, serta mendapatkan analisis cerdas keuangan berbasis aturan emas finansial.

---

## ✨ Fitur Utama

### 1. 🤖 KasKita AI — Asisten Finansial Pintar
*   **Analisis Kesehatan Finansial**: Secara otomatis menghitung rasio pengeluaran bulanan dibanding pendapatan keluarga.
*   **Audit Keuangan Cerdas**: Memberikan label kesehatan finansial (*Sangat Sehat*, *Cukup Sehat*, atau *Waspada Overspending*) beserta saran taktis berdasarkan prinsip alokasi emas **50/30/20** (Kebutuhan/Keinginan/Tabungan).
*   **Deteksi Hari Boros**: Mengidentifikasi hari dengan pengeluaran puncak secara visual serta kategori pengeluaran tertinggi.

### 2. 👥 Kolaborasi & Manajemen Keluarga
*   **Setup Keluarga Mudah**: Pengguna baru dapat langsung mendirikan grup keluarga baru (*Create Family*) atau bergabung (*Join Family*) menggunakan kode undangan instan.
*   **Berbagi Cepat via WhatsApp**: Kirim kode atau link bergabung secara praktis dengan sekali ketuk.
*   **Manajemen Peran (Role)**: Bedakan akses anggota sebagai **Admin Keluarga** (dapat mengelola anggota, mengubah nama keluarga, mengundang anggota baru) atau **Anggota Biasa** (mencatat transaksi & melihat dashboard).

### 3. 📊 Tren Arus Kas & Alokasi Visual
*   **Grafik Arus Kas Mingguan**: Visualisasi perbandingan pemasukan (*cash-in*) dan pengeluaran (*cash-out*) harian interaktif dilengkapi dengan *tooltip hover* premium.
*   **Donut Chart Alokasi**: Diagram lingkaran interaktif untuk memetakan distribusi pengeluaran berdasarkan kategori anggaran secara presisi.

### 4. 💰 Rencana Anggaran (Budgeting) Presisi
*   **Budgeting Bulanan & Mingguan**: Alokasikan batas belanja per kategori secara detail.
*   **Indikator Status Cerdas**: Pemantauan visual sisa anggaran dengan status otomatis: `Aman` (Safe), `Hati-hati` (Warning), `Hampir Habis` (Danger), hingga `Melebihi Budget` (Over).
*   **Kelola Kategori Kustom**: Buat kategori anggaran khusus sesuai karakteristik keluarga Anda secara dinamis.

### 5. 💳 Multi-Dompet Terintegrasi
*   **Jenis Dompet Fleksibel**: Dukungan penuh untuk berbagai tipe dompet, mulai dari *Rekening Bank*, *Uang Tunai (Cash)*, *E-Wallet*, hingga pos *Investasi*.
*   **Pemindahan Saldo (Transfer)**: Lakukan pencatatan mutasi saldo antar dompet keluarga secara instan dan akurat.
*   **Sembunyikan Saldo (Privacy Mode)**: Fitur privasi sekali ketuk untuk menyembunyikan nominal saldo di tempat umum.

### 6. 📄 Riwayat Aktivitas & Download PDF Premium
*   **Penyaringan (Filtering) Granular**: Cari dan urutkan transaksi berdasarkan Jenis, Anggota Keluarga, Kategori Anggaran, serta Bulan & Tahun tertentu.
*   **Laporan PDF Cetak Instan**: Unduh rekapitulasi laporan bulanan keluarga dalam format PDF dengan desain grafis premium dan ramah cetak.

---

## 🛠️ Stack Teknologi

Aplikasi ini dibangun menggunakan arsitektur modern berkinerja tinggi:

*   **Backend Core**: [Laravel 12.x](https://laravel.com) — Framework PHP modern terbaik dengan fitur keamanan tinggi.
*   **Frontend Bridge**: [Inertia.js v3](https://inertiajs.com) — Menghubungkan Laravel & Vue secara seamless tanpa membuat REST API terpisah (*Single Page Application*).
*   **User Interface**: [Vue 3](https://vuejs.org) (Composition API, `<script setup>`, TypeScript) — Interaksi UI yang reaktif dan super cepat.
*   **CSS Engine**: [Tailwind CSS v4.0](https://tailwindcss.com) — Utility-first CSS generasi terbaru dengan kecepatan kompilasi luar biasa menggunakan `@tailwindcss/vite`.
*   **UI Components**: [shadcn-vue](https://www.shadcn-vue.com) (Radix Vue / Reka UI) — Komponen UI yang aksesibel, konsisten, dan sangat kustomisasibel.
*   **Autentikasi Sosial**: [Laravel Fortify](https://laravel.com/docs/fortify) & Integrasi **Google OAuth** untuk masuk dengan satu klik secara aman.
*   **Ikonografi**: [Lucide Vue Next](https://lucide.dev) — Koleksi ikon vektor yang indah dan ringan.

---

## ⚙️ Persyaratan Sistem

Pastikan server atau mesin lokal Anda memenuhi spesifikasi berikut:

*   **PHP** >= 8.2 (dengan ekstensi `pdo`, `mbstring`, `openssl`, `xml`, `gd`)
*   **Composer** >= 2.x
*   **Node.js** >= 20.x & **npm** >= 10.x
*   **Database**: MySQL >= 8.0, PostgreSQL, atau SQLite.

---

## 🚀 Panduan Instalasi Lokal

Ikuti langkah-langkah di bawah ini untuk menjalankan KasKita di lingkungan lokal Anda:

### 1. Kloning Repositori
```bash
git clone https://github.com/Agus27111/kaskita.git
cd kaskita
```

### 2. Instal Dependensi PHP & Frontend
```bash
# Instal paket PHP via Composer
composer install

# Instal paket Node.js
npm install
```

### 3. Konfigurasi Lingkungan (`.env`)
Salin file konfigurasi contoh dan sesuaikan pengaturan database Anda:
```bash
cp .env.example .env
```
Buka file `.env` lalu sesuaikan kredensial database Anda:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kaskita
DB_USERNAME=root
DB_PASSWORD=
```

*(Opsional)* Jika menggunakan masuk lewat Google, tambahkan kredensial Google OAuth:
```env
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
```

### 4. Buat Kunci Aplikasi & Jalankan Migrasi
```bash
# Membuat key enkripsi aplikasi
php artisan key:generate

# Menjalankan migrasi database beserta data awal (seeders)
php artisan migrate --seed
```

### 5. Jalankan Server Aplikasi
Jalankan perintah berikut di dua terminal terpisah (atau gunakan concurrently):

**Terminal 1 (Laravel Server):**
```bash
php artisan serve
```

**Terminal 2 (Vite Hot-Reload):**
```bash
npm run dev
```

Aplikasi kini dapat diakses di browser Anda melalui alamat **`http://127.0.0.1:8000`**!

---

## 🧑‍💻 Panduan Pengembangan & Kualitas Kode

KasKita menggunakan standar pemformatan ketat untuk menjaga kualitas kode di tingkat produksi:

*   **Pemeriksaan Linter (ESLint)**:
    ```bash
    npm run lint:check
    ```
*   **Perbaikan Linter Otomatis**:
    ```bash
    npm run lint
    ```
*   **Pemformatan Kode (Prettier)**:
    ```bash
    npm run format
    ```
*   **Pengecekan Tipe TypeScript**:
    ```bash
    npm run types:check
    ```

---

## 📄 Lisensi

Proyek ini dilisensikan di bawah lisensi **MIT License** - lihat file [LICENSE](LICENSE) untuk detail selengkapnya.

---

<p align="center">
  Dibuat dengan ❤️ untuk pengelolaan finansial keluarga yang lebih sehat dan berkah.
</p>
