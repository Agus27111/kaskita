# CLAUDE.md — KasKita (Aplikasi Keuangan Keluarga)

> Dokumentasi ini adalah panduan pengembangan aplikasi **KasKita** menggunakan Laravel 13 + Filament 4 + Vue 3.
> Dibuat sebagai referensi bersama agar development tetap konsisten dan terdokumentasi dengan baik.

---

## 🎯 Tentang Aplikasi

**KasKita** adalah aplikasi pencatat keuangan keluarga berbasis web (mobile-first) yang memungkinkan seluruh anggota keluarga mencatat pemasukan dan pengeluaran secara bersama-sama, dilengkapi notifikasi Telegram real-time dan saran keuangan berbasis AI.

### Tujuan Utama
- Keluarga bisa mencatat keuangan bersama-sama (suami & istri)
- Notifikasi Telegram otomatis saat ada transaksi baru
- Upload foto struk → AI baca → transaksi tercatat otomatis
- Laporan keuangan yang mudah dipahami
- Saran hemat dari AI berdasarkan histori keuangan

---

## 🏗️ Tech Stack

| Layer | Teknologi |
|---|---|
| Backend | Laravel 13 |
| Admin Panel | Filament 4 (Livewire — khusus /admin) |
| Permission | Filament Shield + Spatie Permission |
| Auth | Laravel Socialite (Google OAuth) |
| Frontend User | Vue 3 + Inertia.js (khusus halaman user) |
| Styling | Tailwind CSS | Pinia |
| Chart | Apexcharts (Vue component) |
| Queue & Cache | Redis + Laravel Horizon |
| Notifikasi | Telegram Bot API (gratis, resmi) |
| Email | Laravel Mail + SMTP |
| AI — Struk OCR | Laravel AI + Gemini Vision (Google) | https://github.com/thiagoalessio/tesseract-ocr-for-php
| AI — Advisor | Laravel AI + Gemini Pro |
| Database | MySQL / PostgreSQL |

---

## 🧩 Pemisahan Frontend — Konsep Penting!

> ⚠️ **Livewire (Filament) dan Vue TIDAK dicampur.** Keduanya punya tujuan sama
> (reaktivitas UI) sehingga harus dipisah berdasarkan area/fungsi.

```
FILAMENT 4 (Livewire)          VUE 3 + INERTIA JS
──────────────────────         ──────────────────────
URL: /admin/*                  URL: /* (semua selain /admin)
Untuk: developer/internal      Untuk: user (keluarga)
CRUD data master               Dashboard keuangan
Manage users & roles           Form input transaksi
Laporan admin                  Laporan visual interaktif
Monitor sistem                 Halaman AI Advisor
```

### Apa itu Inertia.js?

Inertia adalah "jembatan" antara Laravel dan Vue — tidak butuh REST API terpisah.

```
TANPA Inertia (Vue SPA biasa):          DENGAN Inertia:
───────────────────────────────         ──────────────────────────────
Laravel → buat API endpoint             Laravel controller → return
Vue → fetch API → render                Inertia::render('Dashboard', $data)
                                                │
Masalah:                                        ▼
- Harus buat API dulu                   Vue component terima $data
- Auth token terpisah                   sebagai props langsung!
- Dua codebase terasa                   Auth & session Laravel tetap jalan
- Lebih kompleks                        Tidak perlu buat API endpoint ✅
```

```php
// Controller Laravel — seperti biasa, tapi return Inertia
class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard/Index', [
            // Data ini langsung jadi props di Vue component!
            'summary'      => $this->getSummary(),
            'recentTrx'    => Transaction::with('category', 'wallet')
                                ->latest()
                                ->take(5)
                                ->get(),
            'budgetProgress' => $this->getBudgetProgress(),
        ]);
    }
}
```

```vue
<!-- resources/js/Pages/Dashboard/Index.vue -->
<script setup>
// Props langsung dari Laravel controller — tidak perlu fetch/axios!
const props = defineProps({
  summary: Object,
  recentTrx: Array,
  budgetProgress: Array,
})
</script>

<template>
  <AppLayout>
    <SummaryCards :data="summary" />
    <TransactionList :items="recentTrx" />
    <BudgetProgress :budgets="budgetProgress" />
  </AppLayout>
</template>
```

### Struktur Folder Vue (resources/js)

```
resources/js/
├── app.js                    ← entry point Inertia
├── Components/               ← komponen reusable
│   ├── UI/
│   │   ├── AppButton.vue
│   │   ├── AppModal.vue
│   │   ├── CurrencyInput.vue
│   │   └── ProgressBar.vue
│   ├── Transaction/
│   │   ├── TransactionList.vue
│   │   ├── TransactionItem.vue
│   │   └── TransactionForm.vue
│   ├── Wallet/
│   │   └── WalletCard.vue
│   └── Charts/
│       ├── IncomeExpenseChart.vue  ← Apexcharts
│       └── CategoryPieChart.vue
│
├── Layouts/
│   ├── AppLayout.vue         ← layout utama (navbar, bottom nav mobile)
│   └── GuestLayout.vue       ← layout untuk halaman login/register
│
└── Pages/                    ← 1 file = 1 halaman (mirip blade view)
    ├── Auth/
    │   └── Login.vue
    ├── Dashboard/
    │   └── Index.vue
    ├── Transaction/
    │   ├── Index.vue
    │   └── Create.vue
    ├── Wallet/
    │   └── Index.vue
    ├── Budget/
    │   └── Index.vue
    ├── Report/
    │   └── Index.vue
    ├── AiAdvisor/
    │   └── Index.vue
    └── Family/
        ├── Setup.vue          ← buat keluarga baru
        └── Settings.vue       ← kelola anggota
```

### Instalasi Frontend

```bash
# Install Inertia server-side (Laravel)
composer require inertiajs/inertia-laravel

# Install Inertia client-side + Vue 3
npm install @inertiajs/vue3 vue @vitejs/plugin-vue

# Install dependencies lain
npm install apexcharts vue3-apexcharts
npm install @vueuse/core          # Vue composables yang useful
npm install @headlessui/vue       # komponen accessible (modal, dropdown)
```

```js
// vite.config.js
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
})
```

```js
// resources/js/app.js
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import AppLayout from './Layouts/AppLayout.vue'

createInertiaApp({
    // Resolve Pages otomatis dari folder Pages/
    resolve: (name) => {
        const page = resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue')
        )
        // Set default layout ke AppLayout
        page.then((module) => {
            module.default.layout = module.default.layout || AppLayout
        })
        return page
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el)
    },
    progress: {
        color: '#10b981', // warna loading bar (hijau KasKita)
    },
})
```

---

## 📐 Arsitektur Tenancy

> ⚠️ **PENTING** — Aplikasi ini menggunakan **Global Scope + Traits**, BUKAN Filament Built-in Tenancy.

### Alasan:
- 1 user = 1 keluarga saja (bukan many-to-many)
- Tidak butuh tenant switcher
- Lebih simpel dan sesuai kebutuhan

### Implementasi:

```php
// app/Models/Scopes/FamilyScope.php
class FamilyScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (app()->runningInConsole()) return;

        $familyId = auth()->user()?->family_id;

        if ($familyId) {
            $builder->where($model->getTable() . '.family_id', $familyId);
        }
    }
}
```

```php
// app/Traits/BelongsToFamily.php
trait BelongsToFamily
{
    protected static function bootBelongsToFamily(): void
    {
        static::addGlobalScope(new FamilyScope());

        static::creating(function ($model) {
            $model->family_id ??= auth()->user()?->family_id;
        });
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }
}
```

### Model yang Menggunakan Trait:
```php
class Transaction extends Model { use BelongsToFamily; }
class Wallet extends Model      { use BelongsToFamily; }
class Budget extends Model      { use BelongsToFamily; }
class Category extends Model    { use BelongsToFamily; }
```

---

## 🗄️ Database Schema

### Tabel Utama

```
families
├── id
├── name
├── avatar (nullable)
└── timestamps

users
├── id
├── name
├── email (unique)
├── google_id (nullable)
├── avatar (nullable)
├── phone (untuk WA, nullable)
├── family_id (FK → families.id, nullable)
├── role (enum: admin_keluarga, anggota)
└── timestamps

wallets
├── id
├── family_id (FK)
├── name
├── type (enum: cash, bank, ewallet)
├── balance (decimal 15,2 default 0)
├── color (hex color)
├── icon (nullable)
├── is_active (boolean default true)
└── timestamps

categories
├── id
├── family_id (nullable → null = kategori default sistem)
├── name
├── type (enum: income, expense)
├── icon
├── color
└── timestamps

transactions
├── id
├── family_id (FK)
├── wallet_id (FK)
├── category_id (FK)
├── user_id (FK - siapa yang input)
├── type (enum: income, expense, transfer)
├── amount (decimal 15,2)
├── note (nullable)
├── date (date)
├── receipt_photo (nullable)
├── transfer_to_wallet_id (FK nullable - khusus transfer)
└── timestamps

budgets
├── id
├── family_id (FK)
├── category_id (FK)
├── amount (decimal 15,2)
├── month (tinyint 1-12)
├── year (smallint)
└── timestamps

notification_logs
├── id
├── family_id (FK)
├── user_id (FK - siapa yang trigger)
├── channel (enum: whatsapp, email)
├── recipient (phone/email tujuan)
├── message (text)
├── status (enum: sent, failed, throttled)
├── sent_at (nullable)
└── timestamps

family_invitations
├── id
├── family_id (FK)
├── email
├── token (unique)
├── role
├── accepted_at (nullable)
├── expires_at
└── timestamps
```

---

## 📦 Struktur Folder

```
app/
├── Events/
│   └── TransactionCreated.php
│
├── Filament/                     ← KHUSUS area /admin (Livewire)
│   ├── Pages/
│   │   └── AdminDashboard.php
│   └── Resources/
│       ├── UserResource.php
│       ├── FamilyResource.php
│       └── TransactionResource.php
│
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   │   └── GoogleController.php
│   │   ├── DashboardController.php      ← return Inertia::render()
│   │   ├── TransactionController.php
│   │   ├── WalletController.php
│   │   ├── BudgetController.php
│   │   ├── ReportController.php
│   │   ├── AiAdvisorController.php
│   │   └── FamilyController.php
│   └── Requests/
│       ├── StoreTransactionRequest.php
│       └── StoreFamilyRequest.php
│
├── Jobs/
│   ├── SendWhatsAppJob.php
│   └── SendEmailReportJob.php
│
├── Listeners/
│   └── NotifyFamilyOnTransaction.php
│
├── Models/
│   ├── Family.php
│   ├── User.php
│   ├── Wallet.php
│   ├── Category.php
│   ├── Transaction.php
│   ├── Budget.php
│   └── NotificationLog.php
│
├── Models/Scopes/
│   └── FamilyScope.php
│
├── Observers/
│   ├── TransactionObserver.php
│   └── WalletObserver.php
│
├── Services/
│   ├── WhatsAppService.php
│   ├── AiAdvisorService.php
│   ├── ReportService.php
│   └── TransactionService.php
│
└── Traits/
    └── BelongsToFamily.php

resources/js/                     ← KHUSUS area user (Vue 3)
├── app.js
├── Components/
│   ├── UI/
│   ├── Transaction/
│   ├── Wallet/
│   └── Charts/
├── Layouts/
│   ├── AppLayout.vue
│   └── GuestLayout.vue
└── Pages/
    ├── Auth/Login.vue
    ├── Dashboard/Index.vue
    ├── Transaction/
    ├── Wallet/
    ├── Budget/
    ├── Report/
    ├── AiAdvisor/
    └── Family/
```

---

## 🔀 Layer Architecture

```
HTTP Request
    │
    ▼
Controller / Filament Resource   → terima & kembalikan response saja
    │
    ▼
Form Request                     → validasi input
    │
    ▼
Service Class                    → business logic utama
    │
    ▼
Model + Trait                    → query otomatis terfilter by family_id
    │
    ▼
Observer                         → side effects (update balance wallet)
    │
    ▼
Event → Listener → Job (Queue)   → notif WA & email (async)
```

---

## 🤖 Telegram Bot

> Dipilih karena: gratis, API resmi (tidak kena ban), reliable di Android & iOS,
> bisa 2 arah (user bisa balas/query), dan bisa terima foto struk.
> Tidak butuh server tambahan — cukup dari shared hosting via Webhook.

### Setup Bot

```
1. Buka @BotFather di Telegram
2. Ketik /newbot → ikuti instruksi
3. Dapat TOKEN → simpan di .env
4. Set webhook → Laravel otomatis terima update dari Telegram
```

```env
TELEGRAM_BOT_TOKEN=123456789:ABCdefGHI...
TELEGRAM_WEBHOOK_URL=${APP_URL}/telegram/webhook
```

### Cara Kerja Webhook (Tidak Butuh Polling!)

```
User kirim pesan ke bot
        │
        ▼
Telegram kirim HTTP POST ke:
/telegram/webhook
        │
        ▼
Laravel proses pesan
        │
        ▼
Laravel balas via Telegram API
```

```php
// routes/web.php
Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle']);

// app/Http/Controllers/TelegramWebhookController.php
class TelegramWebhookController extends Controller
{
    public function handle(Request $request, TelegramBotService $bot): Response
    {
        $update = $request->all();

        // Foto struk → proses OCR AI
        if (isset($update['message']['photo'])) {
            $bot->handleReceiptPhoto($update);
            return response('ok');
        }

        // Text command
        $text   = $update['message']['text'] ?? '';
        $chatId = $update['message']['chat']['id'];

        match(true) {
            str_starts_with($text, '/start')   => $bot->handleStart($chatId, $text),
            str_starts_with($text, '/saldo')   => $bot->handleSaldo($chatId),
            str_starts_with($text, '/bulan')   => $bot->handleBulan($chatId),
            str_starts_with($text, '/tambah')  => $bot->handleTambah($chatId),
            str_starts_with($text, '/bantuan') => $bot->handleBantuan($chatId),
            default                            => $bot->handleUnknown($chatId),
        };

        return response('ok');
    }
}
```

### Alur Setup User (Sekali Saja)

```
1. User buka @KasKitaBot di Telegram
2. Ketik /start → bot balas dengan kode verifikasi unik
3. User masukkan kode di halaman Setting KasKita
4. Laravel simpan telegram_chat_id di tabel users
5. Selesai! Notif masuk ke Telegram ✅
```

```php
// Simpan chat_id saat user verifikasi
$user->update(['telegram_chat_id' => $chatId]);
```

### Commands Bot

#### /start — Verifikasi & Sambut User

```
Bot balas:
👋 Halo! Selamat datang di KasKita Bot.

Untuk menghubungkan akun, masukkan kode ini
di halaman Pengaturan > Notifikasi:

🔑 Kode verifikasi: KK-847291

Kode berlaku selama 10 menit.
```

#### /saldo — Cek Saldo Semua Dompet

```php
public function handleSaldo(string $chatId): void
{
    $user    = User::where('telegram_chat_id', $chatId)->firstOrFail();
    $wallets = Wallet::where('family_id', $user->family_id)
                     ->where('is_active', true)
                     ->get();

    $total = $wallets->sum('balance');
    $lines = $wallets->map(fn($w) =>
        "👛 {$w->name}: *" . format_rupiah($w->balance) . "*"
    )->join("\n");

    $message = "💰 *Saldo Dompet Keluarga*\n\n{$lines}\n\n"
             . "━━━━━━━━━━━━━━\n"
             . "💵 *Total: " . format_rupiah($total) . "*";

    $this->sendMessage($chatId, $message);
}
```

```
Bot balas:
💰 *Saldo Dompet Keluarga*

👛 BCA Tabungan: *Rp 4.250.000*
👛 Dompet Tunai: *Rp 350.000*
👛 GoPay: *Rp 125.000*

━━━━━━━━━━━━━━
💵 *Total: Rp 4.725.000*
```

#### /bulan — Ringkasan Bulan Ini

```
Bot balas:
📊 *Ringkasan April 2026*

✅ Pemasukan:   Rp 8.500.000
❌ Pengeluaran: Rp 3.240.000
💾 Selisih:     Rp 5.260.000

📂 *Top Pengeluaran:*
1. Makan & Minum  Rp 1.200.000 (37%)
2. Transport      Rp   650.000 (20%)
3. Belanja        Rp   540.000 (17%)

📈 Budget terpakai: 64% dari Rp 5.000.000
```

#### /tambah — Link ke Form Transaksi

```
Bot balas:
➕ *Tambah Transaksi*

Klik link di bawah untuk membuka form:
👉 https://kaskita.app/transactions/create

Atau kirim *foto struk* langsung ke sini,
AI akan membaca dan mencatat otomatis! 🤖
```

#### /bantuan — Daftar Command

```
Bot balas:
🤖 *KasKita Bot — Panduan*

/saldo   → Cek saldo semua dompet
/bulan   → Ringkasan bulan ini
/tambah  → Link form tambah transaksi
/bantuan → Tampilkan pesan ini

📸 *Kirim foto struk* → AI catat otomatis!
```

### Notifikasi Otomatis Saat Ada Transaksi Baru

```
Format pesan yang diterima anggota keluarga lain:

💸 *Pengeluaran Baru!*
👤 Budi mengeluarkan *Rp 85.000*
📂 Makan & Minum
📝 Makan siang bareng anak
👛 BCA Tabungan
📊 Budget makan tersisa: *Rp 915.000* (91%)
📅 26 Apr 2026 · 14:30
```

```php
// app/Services/TelegramBotService.php

public function notifyFamily(Transaction $trx): void
{
    // Kirim ke semua anggota keluarga KECUALI yang input
    $members = User::where('family_id', $trx->family_id)
                   ->where('id', '!=', $trx->user_id)
                   ->whereNotNull('telegram_chat_id')
                   ->get();

    foreach ($members as $member) {
        SendTelegramNotificationJob::dispatch($member->telegram_chat_id, $trx)
            ->onQueue('notifications');
    }
}

public function sendMessage(string $chatId, string $text): void
{
    Http::post("https://api.telegram.org/bot{$this->token}/sendMessage", [
        'chat_id'    => $chatId,
        'text'       => $text,
        'parse_mode' => 'Markdown',
    ]);
}
```

---

## 📸 Fitur Kirim Struk via Telegram + AI Extraction

> User foto struk → kirim ke bot → AI baca → transaksi tercatat otomatis!

### Biaya

```
Proses 1 foto struk:
├── Telegram download foto  → GRATIS
├── Vision AI (Claude)      → ~$0.003 (Rp 50)
└── Total per foto          → ~Rp 50

Estimasi pemakaian keluarga:
├── 60 foto struk/bulan     → Rp 3.000/bulan
└── Sangat terjangkau! ✅
```

### Alur Teknis

```
User kirim foto struk ke bot
        │
        ▼
Telegram webhook terima update (type: photo)
        │
        ▼
Laravel download foto dari Telegram server
(pakai file_id → getFile API)
        │
        ▼
Foto di-encode base64
        │
        ▼
Kirim ke Vision AI (Claude claude-sonnet / GPT-4o)
dengan prompt ekstraksi terstruktur
        │
        ▼
AI kembalikan JSON:
{
  "merchant": "Warteg Bu Sari",
  "amount": 85000,
  "date": "2026-04-26",
  "category_suggestion": "Makan & Minum",
  "items": ["Nasi + ayam + sayur"]
}
        │
        ▼
Laravel buat transaksi otomatis
        │
        ▼
Bot kirim konfirmasi ke user
```

### Implementasi

```php
// app/Services/TelegramBotService.php

public function handleReceiptPhoto(array $update): void
{
    $chatId  = $update['message']['chat']['id'];
    $user    = User::where('telegram_chat_id', $chatId)->first();

    if (!$user) {
        $this->sendMessage($chatId, '❌ Akun belum terhubung. Ketik /start');
        return;
    }

    // Kasih tau user sedang diproses
    $this->sendMessage($chatId, '🔄 Sedang membaca struk... tunggu sebentar ya!');

    // Dispatch ke queue agar tidak timeout
    ProcessReceiptPhotoJob::dispatch($user, $update['message']['photo'], $chatId)
        ->onQueue('receipts');
}
```

```php
// app/Jobs/ProcessReceiptPhotoJob.php

class ProcessReceiptPhotoJob implements ShouldQueue
{
    public int $tries   = 2;
    public int $timeout = 60;

    public function __construct(
        private User   $user,
        private array  $photos,
        private string $chatId,
    ) {}

    public function handle(TelegramBotService $bot, ReceiptExtractorService $extractor): void
    {
        // Ambil foto kualitas terbaik (array terakhir = resolusi tertinggi)
        $fileId = end($this->photos)['file_id'];

        // 1. Download foto dari Telegram → base64
        $imageBase64 = $bot->downloadPhotoAsBase64($fileId);

        // 2. Kirim ke Gemini Vision untuk ekstraksi
        $extracted = $extractor->extract($imageBase64);

        if (!$extracted) {
            $bot->sendMessage($this->chatId,
                "❌ Maaf, struk kurang jelas terbaca.\n" .
                "Coba foto ulang dengan pencahayaan lebih baik,\n" .
                "atau tambah manual:\n" .
                "👉 " . route('transactions.create')
            );
            return;
        }

        // 3. Match kategori
        $category = Category::where('family_id', $this->user->family_id)
            ->where('type', 'expense')
            ->where('name', 'like', "%{$extracted['category_suggestion']}%")
            ->orWhere('family_id', null)
            ->first();

        // 4. Simpan transaksi + update saldo (dalam DB transaction)
        $trx = DB::transaction(function () use ($extracted, $category) {
            $trx = Transaction::create([
                'family_id'   => $this->user->family_id,
                'user_id'     => $this->user->id,
                'wallet_id'   => $this->user->default_wallet_id,
                'category_id' => $category?->id,
                'type'        => 'expense',
                'amount'      => $extracted['amount'],
                'date'        => $extracted['date'],
                'note'        => $extracted['merchant'],
            ]);

            Wallet::where('id', $this->user->default_wallet_id)
                  ->lockForUpdate()
                  ->decrement('balance', $extracted['amount']);

            return $trx;
        });

        // 5. Konfirmasi ke user
        $items = collect($extracted['items'])->join(', ');
        $bot->sendMessage($this->chatId,
            "✅ *Struk berhasil dicatat!*\n\n" .
            "🏪 {$extracted['merchant']}\n" .
            "💰 *" . format_rupiah($extracted['amount']) . "*\n" .
            "📂 {$category?->name ?? 'Belum dikategorikan'}\n" .
            "🗓 {$extracted['date']}\n" .
            ($items ? "📦 Items: {$items}\n" : "") .
            "\n👉 [Lihat & edit transaksi](" . route('transactions.show', $trx) . ")"
        );
    }
}
```

```php
// app/Services/AiAdvisorService.php

public function extractReceiptData(string $imageBase64): ?array
{
    $response = Prism::vision()
        ->using('anthropic', 'claude-sonnet-4-5')
        ->withImage($imageBase64, 'image/jpeg')
        ->withPrompt('
            Ekstrak data dari struk belanja ini.
            Kembalikan HANYA JSON valid, tanpa teks lain:
            {
              "merchant": "nama toko/restoran",
              "amount": 85000,
              "date": "2026-04-26",
              "category_suggestion": "Makan & Minum",
              "items": ["item 1", "item 2"]
            }
            Jika tidak bisa membaca struk, kembalikan: null
            Format date: YYYY-MM-DD
            Amount: angka saja tanpa titik/koma
        ')
        ->generate();

    try {
        return json_decode($response->text, true);
    } catch (\Exception $e) {
        return null;
    }
}
```

### Update Schema Database

```
users
├── ... (kolom sebelumnya)
├── telegram_chat_id (string, nullable, unique)  ← tambah ini
└── default_wallet_id (FK nullable)              ← untuk struk otomatis
```

---

## 🤖 AI — Laravel AI + Gemini

> Laravel 13 hadir dengan package **Laravel AI** bawaan yang bisa dihubungkan
> ke berbagai provider AI. Kita pakai **Google Gemini** karena:
> - Free tier sangat generous (1500 request/hari gratis!)
> - Gemini Vision sangat bagus untuk baca foto struk
> - Tidak butuh kartu kredit untuk mulai
> - Satu API key untuk semua fitur (Vision + Text)

### Kenapa Gemini?

```
GEMINI FREE TIER (per hari):
├── gemini-2.0-flash  → 1.500 request/hari GRATIS ✅
├── Input gambar      → bisa kirim foto struk ✅
├── Output JSON       → structured response ✅
└── Bahasa Indonesia  → dipahami dengan baik ✅

Estimasi pemakaian 1 keluarga:
├── Scan struk      : ~5 request/hari
├── AI Advisor chat : ~3 request/hari
└── Total           : ~8 request/hari → jauh di bawah limit ✅

Kalau sudah banyak user (> 150 keluarga aktif):
└── Upgrade ke Gemini berbayar → sangat murah ($0.075/1M token)
```

### Setup Laravel AI + Gemini

```bash
# Laravel AI sudah built-in di Laravel 13
# Tinggal install driver Gemini:
composer require google/gemini-api-php

# Atau pakai Prism PHP yang support multi-provider:
composer require echolabsdev/prism-php
```

```env
# .env
GEMINI_API_KEY=AIzaSy...  # ambil dari Google AI Studio (gratis)

# Kalau pakai Prism:
PRISM_GEMINI_API_KEY=AIzaSy...
```

```php
// config/ai.php (Laravel AI bawaan)
return [
    'default' => 'gemini',

    'drivers' => [
        'gemini' => [
            'driver' => 'gemini',
            'api_key' => env('GEMINI_API_KEY'),
            'model'   => 'gemini-2.0-flash',  // model default
        ],
    ],
];
```

### Fitur 1: Ekstrak Struk → JSON (Vision)

```php
// app/Services/ReceiptExtractorService.php

class ReceiptExtractorService
{
    /**
     * Kirim foto struk ke Gemini Vision,
     * dapat balik JSON data transaksi
     */
    public function extract(string $imageBase64, string $mimeType = 'image/jpeg'): ?array
    {
        $prompt = <<<PROMPT
        Kamu adalah asisten pencatat keuangan keluarga Indonesia.
        Baca struk belanja pada gambar ini dan ekstrak informasinya.

        Kembalikan HANYA JSON valid tanpa markdown, tanpa teks tambahan:
        {
          "merchant": "nama toko atau restoran",
          "amount": 85000,
          "date": "2026-04-26",
          "category_suggestion": "Makan & Minum",
          "items": ["nama item 1", "nama item 2"],
          "confidence": "high"
        }

        Aturan:
        - amount: angka bulat tanpa titik/koma/Rp
        - date: format YYYY-MM-DD, kalau tidak ada pakai hari ini
        - category_suggestion pilih dari:
          [Makan & Minum, Belanja, Transport, Kesehatan,
           Pendidikan, Hiburan, Tagihan, Lainnya]
        - confidence: "high" kalau jelas terbaca, "low" kalau tidak yakin
        - Kalau gambar bukan struk belanja, kembalikan: {"error": "bukan struk"}
        PROMPT;

        try {
            // Pakai Prism PHP dengan Gemini Vision
            $response = Prism::vision()
                ->using('gemini', 'gemini-2.0-flash')
                ->withPrompt($prompt)
                ->withImage($imageBase64, $mimeType)
                ->generate();

            $json = json_decode($response->text, true);

            // Validasi hasil
            if (isset($json['error'])) return null;
            if (!isset($json['amount']) || $json['amount'] <= 0) return null;

            return $json;

        } catch (\Exception $e) {
            Log::error('Receipt extraction failed', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
```

### Fitur 2: AI Advisor (Text)

```php
// app/Services/AiAdvisorService.php

class AiAdvisorService
{
    /**
     * Generate saran keuangan berdasarkan histori transaksi
     */
    public function getMonthlyInsight(Family $family): string
    {
        // Ambil data ringkasan 3 bulan terakhir
        $summary = $this->buildFinancialSummary($family);

        $prompt = <<<PROMPT
        Kamu adalah konsultan keuangan keluarga yang ramah dan berbahasa Indonesia.
        Berikan analisa dan saran singkat berdasarkan data keuangan berikut:

        {$summary}

        Format respons:
        - Maksimal 150 kata
        - Gunakan bahasa yang mudah dipahami ibu rumah tangga
        - Sertakan 2-3 saran praktis yang spesifik
        - Gunakan emoji yang relevan
        - Bersifat positif dan memotivasi
        PROMPT;

        $response = Prism::text()
            ->using('gemini', 'gemini-2.0-flash')
            ->withPrompt($prompt)
            ->generate();

        return $response->text;
    }

    /**
     * Chat interaktif dengan AI Advisor
     */
    public function chat(Family $family, string $userMessage, array $history = []): string
    {
        $context = $this->buildFinancialSummary($family);

        $systemPrompt = <<<PROMPT
        Kamu adalah KasKita AI, asisten keuangan keluarga yang ramah.
        Gunakan bahasa Indonesia yang santai dan mudah dipahami.
        
        Data keuangan keluarga ini bulan ini:
        {$context}
        
        Jawab pertanyaan berdasarkan data di atas.
        Kalau tidak ada datanya, minta user untuk cek langsung di aplikasi.
        PROMPT;

        // Bangun history percakapan
        $messages = collect($history)->map(fn($h) => [
            'role'    => $h['role'],
            'content' => $h['content'],
        ])->toArray();

        $messages[] = ['role' => 'user', 'content' => $userMessage];

        $response = Prism::text()
            ->using('gemini', 'gemini-2.0-flash')
            ->withSystemPrompt($systemPrompt)
            ->withMessages($messages)
            ->generate();

        return $response->text;
    }

    private function buildFinancialSummary(Family $family): string
    {
        $now = now();

        // Ringkasan 3 bulan terakhir
        $transactions = Transaction::where('family_id', $family->id)
            ->where('date', '>=', $now->copy()->subMonths(3)->startOfMonth())
            ->with('category')
            ->get();

        $thisMonth = $transactions->filter(
            fn($t) => Carbon::parse($t->date)->isCurrentMonth()
        );

        $income  = $thisMonth->where('type', 'income')->sum('amount');
        $expense = $thisMonth->where('type', 'expense')->sum('amount');

        $topCategories = $thisMonth
            ->where('type', 'expense')
            ->groupBy('category.name')
            ->map(fn($g) => $g->sum('amount'))
            ->sortDesc()
            ->take(5);

        $categoriesText = $topCategories
            ->map(fn($amt, $cat) => "- {$cat}: Rp " . number_format($amt, 0, ',', '.'))
            ->join("\n");

        return <<<DATA
        Bulan ini ({$now->translatedFormat('F Y')}):
        - Pemasukan: Rp {$income}
        - Pengeluaran: Rp {$expense}
        - Selisih: Rp {$income - $expense}

        Top pengeluaran per kategori:
        {$categoriesText}
        DATA;
    }
}
```

### Alur Lengkap Struk via Telegram

```
📱 User foto struk → kirim ke @KasKitaBot
        │
        ▼
Telegram Webhook → TelegramWebhookController
        │
        ▼ (masuk queue 'receipts')
ProcessReceiptPhotoJob
        │
        ├── Download foto dari Telegram server
        │   (via getFile API → file URL → Http::get())
        │
        ├── Convert ke base64
        │
        ├── Kirim ke Gemini Vision
        │   ReceiptExtractorService::extract()
        │
        ├── Terima JSON hasil ekstraksi:
        │   {merchant, amount, date, category, items}
        │
        ├── Match kategori dengan database keluarga
        │
        ├── DB::transaction:
        │   ├── Transaction::create()
        │   └── Wallet->decrement('balance', amount)
        │
        └── Kirim konfirmasi ke Telegram:
            ✅ Struk berhasil dicatat!
            🏪 Warteg Bu Sari
            💰 Rp 85.000
            📂 Makan & Minum
            👉 [Lihat & edit](https://kaskita.app/...)
```

### Contoh Response Konfirmasi ke User

```
Struk terbaca:
──────────────
✅ *Struk berhasil dicatat!*

🏪 Indomaret Jl. Sudirman
💰 *Rp 127.500*
📂 Belanja
🗓 26 April 2026
📦 Items: Susu, Roti, Sabun mandi

👉 [Lihat & edit transaksi](https://kaskita.app/transactions/123)

Struk tidak terbaca / blur:
────────────────────────────
❌ Maaf, struk kurang jelas terbaca.
Coba foto ulang dengan pencahayaan lebih baik,
atau tambah transaksi manual:
👉 [Tambah manual](https://kaskita.app/transactions/create)
```

---

## 🔐 Auth & Role

### Google OAuth
```bash
composer require laravel/socialite
```

### Role (via Filament Shield)
| Role | Akses |
|---|---|
| `admin_keluarga` | Semua fitur + kelola anggota & setting |
| `anggota` | Input transaksi + lihat laporan |

### Catatan Role:
- Saat buat keluarga baru → otomatis jadi `admin_keluarga`
- Saat diundang → default jadi `anggota`
- Role bersifat **per keluarga** (scope by family_id)

---

## 🎨 UI / UX Guidelines

- **Mobile-first** — desain prioritas layar HP
- **Vue 3 + Tailwind CSS** untuk semua halaman user
- Warna utama: hijau (pemasukan), merah (pengeluaran), biru (transfer)
- Gunakan **Bottom Navigation** di mobile (bukan sidebar)
- Dashboard harus tampilkan:
  1. Total saldo semua dompet
  2. Ringkasan bulan ini (pemasukan vs pengeluaran)
  3. Progress budget per kategori
  4. 5 transaksi terbaru
  5. Shortcut FAB (Floating Action Button): + Transaksi

### Vue Component Conventions

```vue
<!-- Selalu pakai <script setup> (Composition API) -->
<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'   // form handling
import { usePage } from '@inertiajs/vue3'   // akses shared data

// Props selalu definisikan dengan defineProps
const props = defineProps({
  transactions: Array,
  summary: Object,
})

// Form pakai useForm dari Inertia (handle error otomatis)
const form = useForm({
  amount: '',
  type: 'expense',
  wallet_id: '',
  category_id: '',
  date: new Date().toISOString().split('T')[0],
  note: '',
})

const submit = () => {
  form.post(route('transactions.store'), {
    onSuccess: () => form.reset(),
  })
}
</script>
```

### Shared Data (Flash Message, Auth User)

```php
// app/Http/Middleware/HandleInertiaRequests.php
// Data ini tersedia di SEMUA halaman Vue tanpa perlu pass manual

public function share(Request $request): array
{
    return [
        ...parent::share($request),
        'auth' => [
            'user'   => $request->user()?->only('id', 'name', 'avatar', 'family_id'),
            'family' => $request->user()?->family?->only('id', 'name'),
        ],
        'flash' => [
            'success' => $request->session()->get('success'),
            'error'   => $request->session()->get('error'),
        ],
    ];
}
```

```vue
<!-- Akses shared data di mana saja tanpa props -->
<script setup>
import { usePage } from '@inertiajs/vue3'
const { auth, flash } = usePage().props
</script>

<template>
  <p>Halo, {{ auth.user.name }}!</p>
  <p v-if="flash.success" class="text-green-500">{{ flash.success }}</p>
</template>
```

---

## 🚀 Development Phases

### Phase 1 — Foundation
- [x] Setup Laravel 13 + Filament 4 (4.x-dev branch)
- [x] Setup Vue 3 + Inertia.js + Vite (starter kit bawaan)
- [x] Install & konfigurasi Shield + Socialite
- [x] Migration semua tabel (families, wallets, categories, transactions, budgets, notification_logs, family_invitations, permissions)
- [x] Model + Trait `BelongsToFamily` + `FamilyScope`
- [x] Google Auth flow (GoogleController + routes + services config)
- [ ] Family: buat keluarga baru + sistem undangan
- [x] Route: pisah `/admin/*` (Filament) dan `/*` (Inertia+Vue)
- [x] Seeder kategori default (15 kategori income/expense)
- [x] TransactionObserver (auto update saldo wallet)

### Phase 2 — Core UI (Vue)
- [ ] AppLayout.vue (mobile-first, bottom navigation)
- [ ] Dashboard/Index.vue (saldo, ringkasan, progress)
- [ ] Transaction/Create.vue (form cepat, fokus UX)
- [ ] Wallet/Index.vue
- [ ] Budget/Index.vue (progress bar visual)
- [ ] Report/Index.vue (grafik Apexcharts)
- [ ] Upload foto struk

### Phase 3 — Notification
- [ ] Setup Redis + Queue worker + Horizon
- [ ] Deploy wa-gateway (Docker)
- [ ] WhatsAppService + throttle logic
- [ ] TransactionObserver → fire event
- [ ] Job: SendWhatsAppJob + SendEmailReportJob
- [ ] Laporan email mingguan

### Phase 4 — AI Features
- [ ] Install Prism PHP
- [ ] AiAdvisorService
- [ ] AiAdvisor/Index.vue (chat interaktif dengan streaming)
- [ ] Insight otomatis di dashboard

### Phase 5 — Polish & Production
- [ ] PWA (vite-plugin-pwa)
- [ ] Optimasi query (Eager Loading, Index)
- [ ] Activity Log (Spatie)
- [ ] Testing (Feature + Unit)
- [ ] Deploy ke production

---

## 🗂️ Database Indexing

> Index = cara MySQL "menghafalkan" lokasi data agar pencarian lebih cepat.
> Tanpa index → MySQL scan seluruh tabel (full table scan). Dengan index → langsung loncat ke data.

### Aturan Index di Project Ini

```php
// Kolom yang WAJIB di-index:
// 1. Foreign Key (sudah otomatis di Laravel jika pakai constrained())
// 2. Kolom yang sering di-WHERE / di-filter
// 3. Kolom yang sering di-ORDER BY
// 4. Kolom yang sering di-GROUP BY

Schema::create('transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('family_id')->constrained()->cascadeOnDelete(); // FK = auto index
    $table->foreignId('wallet_id')->constrained()->cascadeOnDelete(); // FK = auto index
    $table->foreignId('category_id')->constrained();                  // FK = auto index
    $table->foreignId('user_id')->constrained();                      // FK = auto index

    $table->enum('type', ['income', 'expense', 'transfer']);
    $table->decimal('amount', 15, 2);
    $table->date('date');
    $table->string('note')->nullable();
    $table->timestamps();

    // Index tambahan untuk query yang sering:
    $table->index('date');                        // filter by bulan/tahun
    $table->index('type');                        // filter by jenis transaksi
    $table->index(['family_id', 'date']);         // ← COMPOSITE INDEX!
    $table->index(['family_id', 'type', 'date']); // ← query dashboard bulanan
});
```

### Composite Index — Kapan Dipakai?

```php
// Query ini sangat sering dijalankan di dashboard:
Transaction::where('family_id', 1)
           ->where('type', 'expense')
           ->whereMonth('date', now()->month)
           ->sum('amount');

// MySQL akan pakai index: ['family_id', 'type', 'date']
// Urutan kolom di composite index HARUS sama dengan urutan WHERE!
// family_id dulu → type → date ✅

// Index lain yang dibutuhkan per tabel:
Schema::create('budgets', function (Blueprint $table) {
    // ...
    $table->unique(['family_id', 'category_id', 'month', 'year']); // 1 budget per kategori per bulan
});

Schema::create('wallets', function (Blueprint $table) {
    // ...
    $table->index(['family_id', 'is_active']); // ambil dompet aktif per keluarga
});

Schema::create('categories', function (Blueprint $table) {
    // ...
    $table->index(['family_id', 'type']); // filter kategori income/expense
    // family_id nullable (null = default sistem) → tetap perlu index
});
```

### Cek Apakah Index Dipakai (EXPLAIN)

```php
// Jalankan di tinker atau query log:
DB::statement('EXPLAIN SELECT * FROM transactions
               WHERE family_id = 1
               AND type = "expense"
               AND MONTH(date) = 4');

// Perhatikan kolom "key" di hasil EXPLAIN:
// key = NULL          → index TIDAK dipakai ⚠️
// key = index_name    → index dipakai ✅
// rows = angka besar  → mungkin perlu composite index
```

---

## ⚡ Query Optimization

### Wajib: Eager Loading (Cegah N+1)

```php
// ❌ N+1 — jangan pernah ini di production!
$transactions = Transaction::all();
foreach ($transactions as $trx) {
    echo $trx->category->name; // query baru tiap baris!
    echo $trx->wallet->name;   // query baru lagi!
    echo $trx->user->name;     // dan lagi!
}
// Kalau ada 100 transaksi = 1 + 100 + 100 + 100 = 301 queries!

// ✅ Eager Loading — wajib!
$transactions = Transaction::with(['category', 'wallet', 'user'])->get();
// Hanya 4 queries total, berapapun jumlah transaksi ✅

// ✅ Pilih kolom yang dibutuhkan saja (hemat memory)
$transactions = Transaction::with([
    'category:id,name,icon,color',
    'wallet:id,name,type',
    'user:id,name,avatar',
])->select('id', 'category_id', 'wallet_id', 'user_id', 'type', 'amount', 'date', 'note')
  ->get();
```

### Chunking untuk Data Besar

```php
// ❌ Jangan load semua sekaligus untuk laporan tahunan!
$transactions = Transaction::whereYear('date', 2026)->get(); // bisa ribuan record!

// ✅ Chunk — proses per 200 baris
Transaction::whereYear('date', 2026)
    ->chunk(200, function ($transactions) {
        foreach ($transactions as $trx) {
            // proses laporan...
        }
    });

// ✅ Lazy Collection — lebih hemat memory (streaming)
Transaction::whereYear('date', 2026)
    ->lazy()
    ->each(function ($trx) {
        // proses satu per satu tanpa load semua ke memory
    });
```

### Query Aggregation di Database, Bukan PHP

```php
// ❌ Jangan hitung di PHP!
$total = Transaction::where('type', 'expense')->get()->sum('amount');

// ✅ Hitung di database!
$total = Transaction::where('type', 'expense')->sum('amount');

// ✅ Ambil ringkasan sekaligus (1 query)
$summary = Transaction::selectRaw('
        type,
        SUM(amount) as total,
        COUNT(*) as count
    ')
    ->whereMonth('date', now()->month)
    ->groupBy('type')
    ->get()
    ->keyBy('type');

$pemasukanTotal  = $summary['income']->total ?? 0;
$pengeluaranTotal = $summary['expense']->total ?? 0;
```

---

## 🗃️ Caching Strategy

### Apa yang Perlu Di-cache di KasKita?

```
CACHE ✅                          JANGAN CACHE ❌
──────────────────────────        ─────────────────────────
Kategori default sistem           Saldo dompet (berubah tiap transaksi)
Daftar dompet per keluarga        Daftar transaksi terbaru
Ringkasan bulanan (report)        Data real-time apapun
Setting notifikasi keluarga
```

### Implementasi Cache

```php
// app/Services/ReportService.php

class ReportService
{
    public function getMonthlySummary(int $familyId, int $month, int $year): array
    {
        $cacheKey = "report_summary_{$familyId}_{$year}_{$month}";

        return Cache::remember($cacheKey, now()->addHours(1), function () use ($familyId, $month, $year) {
            return Transaction::where('family_id', $familyId)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->selectRaw('type, SUM(amount) as total, COUNT(*) as count')
                ->groupBy('type')
                ->get()
                ->toArray();
        });
    }

    // WAJIB: Hapus cache saat ada transaksi baru/edit/hapus!
    public function clearMonthlyCache(int $familyId, string $date): void
    {
        $month = Carbon::parse($date)->month;
        $year  = Carbon::parse($date)->year;
        Cache::forget("report_summary_{$familyId}_{$year}_{$month}");
    }
}
```

```php
// app/Observers/TransactionObserver.php
// Cache di-invalidate otomatis lewat Observer

class TransactionObserver
{
    public function __construct(private ReportService $report) {}

    public function created(Transaction $trx): void
    {
        $this->report->clearMonthlyCache($trx->family_id, $trx->date);
    }

    public function updated(Transaction $trx): void
    {
        $this->report->clearMonthlyCache($trx->family_id, $trx->date);
        // Kalau tanggal diubah, hapus cache bulan lama juga
        if ($trx->wasChanged('date')) {
            $this->report->clearMonthlyCache($trx->family_id, $trx->getOriginal('date'));
        }
    }

    public function deleted(Transaction $trx): void
    {
        $this->report->clearMonthlyCache($trx->family_id, $trx->date);
    }
}
```

### Cache untuk Kategori Default (Jarang Berubah)

```php
// Cache lama — 24 jam karena kategori default tidak berubah
public function getDefaultCategories(): Collection
{
    return Cache::remember('default_categories', now()->addHours(24), function () {
        return Category::whereNull('family_id')->orderBy('type')->get();
    });
}
```

---

## 🔒 Security

### 1. Authorization — Policy (Wajib!)

```php
// app/Policies/TransactionPolicy.php
// Pastikan user hanya bisa CRUD transaksi milik keluarganya sendiri

class TransactionPolicy
{
    // Cek sebelum method lain — kalau false, langsung tolak
    public function before(User $user): ?bool
    {
        // Super admin bisa bypass (kalau ada)
        return null;
    }

    public function view(User $user, Transaction $transaction): bool
    {
        return $user->family_id === $transaction->family_id;
    }

    public function update(User $user, Transaction $transaction): bool
    {
        // Hanya yang buat, atau admin keluarga
        return $user->family_id === $transaction->family_id
            && ($user->id === $transaction->user_id || $user->hasRole('admin_keluarga'));
    }

    public function delete(User $user, Transaction $transaction): bool
    {
        return $user->family_id === $transaction->family_id
            && $user->hasRole('admin_keluarga');
    }
}
```

```php
// Daftarkan di AuthServiceProvider
protected $policies = [
    Transaction::class => TransactionPolicy::class,
    Wallet::class      => WalletPolicy::class,
    Budget::class      => BudgetPolicy::class,
];
```

### 2. Form Request Validation

```php
// app/Http/Requests/StoreTransactionRequest.php

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check(); // harus login
    }

    public function rules(): array
    {
        return [
            'wallet_id'   => [
                'required',
                'exists:wallets,id',
                // Pastikan wallet milik keluarga user ini!
                Rule::exists('wallets', 'id')->where('family_id', auth()->user()->family_id),
            ],
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where(function ($query) {
                    $query->where('family_id', auth()->user()->family_id)
                          ->orWhereNull('family_id'); // boleh kategori default
                }),
            ],
            'type'   => ['required', Rule::in(['income', 'expense', 'transfer'])],
            'amount' => ['required', 'numeric', 'min:1', 'max:999999999'],
            'date'   => ['required', 'date', 'before_or_equal:today'],
            'note'   => ['nullable', 'string', 'max:500'],
            'receipt_photo' => ['nullable', 'image', 'max:2048'], // max 2MB
            'transfer_to_wallet_id' => [
                Rule::requiredIf(fn() => request('type') === 'transfer'),
                'nullable',
                'different:wallet_id', // tidak boleh transfer ke dompet yang sama
                Rule::exists('wallets', 'id')->where('family_id', auth()->user()->family_id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.max'    => 'Jumlah transaksi maksimal Rp 999.999.999',
            'date.before_or_equal' => 'Tanggal tidak boleh lebih dari hari ini',
        ];
    }
}
```

### 3. Rate Limiting

```php
// app/Providers/AppServiceProvider.php

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

public function boot(): void
{
    // Max 60 transaksi per menit per keluarga (anti spam)
    RateLimiter::for('transactions', function (Request $request) {
        return Limit::perMinute(60)->by($request->user()?->family_id);
    });

    // Max 5 undangan per jam per keluarga
    RateLimiter::for('invitations', function (Request $request) {
        return Limit::perHour(5)->by($request->user()?->family_id);
    });
}
```

### 4. Soft Delete — Data Penting Tidak Dihapus Permanen

```php
// Model yang pakai Soft Delete:
class Transaction extends Model
{
    use SoftDeletes; // hapus = set deleted_at, data aman & bisa restore
}

class Wallet extends Model
{
    use SoftDeletes; // dompet dihapus tapi histori transaksi tetap ada
}

// Query otomatis exclude soft-deleted:
Transaction::all();                    // hanya yang belum dihapus
Transaction::withTrashed()->get();     // termasuk yang sudah dihapus
Transaction::onlyTrashed()->get();     // hanya yang sudah dihapus
$trx->restore();                       // kembalikan yang terhapus
```

---

## 🔄 Database Transaction (DB::transaction)

> Gunakan saat ada **lebih dari 1 operasi database yang saling bergantung**.
> Kalau salah satu gagal → semua di-rollback, tidak ada data setengah-setengah.

```php
// app/Services/TransactionService.php

class TransactionService
{
    public function create(array $data, User $user): Transaction
    {
        return DB::transaction(function () use ($data, $user) {

            // 1. Buat record transaksi
            $transaction = Transaction::create([
                ...$data,
                'user_id'   => $user->id,
                'family_id' => $user->family_id,
            ]);

            // 2. Update saldo dompet
            $wallet = Wallet::lockForUpdate()->find($data['wallet_id']);
            // ↑ lockForUpdate() penting! Cegah race condition
            // kalau suami & istri input transaksi bersamaan

            if ($data['type'] === 'expense') {
                $wallet->decrement('balance', $data['amount']);
            } elseif ($data['type'] === 'income') {
                $wallet->increment('balance', $data['amount']);
            } elseif ($data['type'] === 'transfer') {
                $wallet->decrement('balance', $data['amount']);

                $targetWallet = Wallet::lockForUpdate()->find($data['transfer_to_wallet_id']);
                $targetWallet->increment('balance', $data['amount']);
            }

            // Kalau salah satu di atas throw exception → semua rollback!
            return $transaction;
        });
    }
}
```

---

## 📝 Activity Log

```bash
composer require spatie/laravel-activitylog
```

```php
// Model yang perlu audit trail:
class Transaction extends Model
{
    use LogsActivity;

    protected static $logAttributes      = ['amount', 'type', 'category_id', 'wallet_id', 'date', 'note'];
    protected static $logName            = 'transaction';
    protected static $logOnlyDirty       = true; // hanya catat yang berubah
    protected static $recordEvents       = ['created', 'updated', 'deleted'];

    public function getDescriptionForEvent(string $eventName): string
    {
        return match($eventName) {
            'created' => 'Transaksi baru ditambahkan',
            'updated' => 'Transaksi diubah',
            'deleted' => 'Transaksi dihapus',
        };
    }
}

// Akses log:
activity()->forSubject($transaction)->get();   // log untuk 1 transaksi
activity()->causedBy($user)->get();            // semua aksi oleh 1 user
```

---

## 🧪 Testing

### Struktur Test

```
tests/
├── Feature/
│   ├── Auth/
│   │   └── GoogleAuthTest.php
│   ├── Transaction/
│   │   ├── CreateTransactionTest.php
│   │   ├── WalletBalanceTest.php
│   │   └── FamilyScopeTest.php       ← test isolasi antar keluarga!
│   ├── Budget/
│   │   └── BudgetAlertTest.php
│   └── Notification/
│       └── WhatsAppNotificationTest.php
└── Unit/
    ├── Services/
    │   ├── TransactionServiceTest.php
    │   └── WhatsAppServiceTest.php
    └── Models/
        └── WalletBalanceTest.php
```

### Contoh Test Penting — Family Scope Isolation

```php
// tests/Feature/Transaction/FamilyScopeTest.php

class FamilyScopeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_cannot_see_other_family_transactions(): void
    {
        // Buat 2 keluarga berbeda
        $familyA = Family::factory()->create();
        $familyB = Family::factory()->create();

        $userA = User::factory()->create(['family_id' => $familyA->id]);
        $userB = User::factory()->create(['family_id' => $familyB->id]);

        // Buat transaksi untuk masing-masing
        $trxA = Transaction::factory()->create(['family_id' => $familyA->id]);
        $trxB = Transaction::factory()->create(['family_id' => $familyB->id]);

        // Login sebagai user A
        $this->actingAs($userA);

        // User A hanya boleh lihat transaksi keluarga A
        $result = Transaction::all();
        $this->assertCount(1, $result);
        $this->assertEquals($trxA->id, $result->first()->id);

        // User A tidak boleh lihat transaksi keluarga B
        $this->assertDatabaseMissing('transactions', [
            'id'        => $trxB->id,
            'family_id' => $familyA->id, // bukan punyanya
        ]);
    }

    /** @test */
    public function wallet_balance_updated_correctly_on_expense(): void
    {
        $user   = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'family_id' => $user->family_id,
            'balance'   => 1_000_000,
        ]);

        $this->actingAs($user);

        $service = app(TransactionService::class);
        $service->create([
            'wallet_id'   => $wallet->id,
            'category_id' => Category::factory()->create()->id,
            'type'        => 'expense',
            'amount'      => 150_000,
            'date'        => now()->toDateString(),
        ], $user);

        // Saldo harus berkurang
        $this->assertEquals(850_000, $wallet->fresh()->balance);
    }
}
```

### Contoh Test — WA Notification (Mock, tidak kirim WA sungguhan)

```php
// tests/Feature/Notification/WhatsAppNotificationTest.php

class WhatsAppNotificationTest extends TestCase
{
    /** @test */
    public function wa_notification_queued_when_transaction_created(): void
    {
        Queue::fake(); // intercept queue, tidak benar-benar kirim

        $user = User::factory()->create(['phone' => '08123456789']);
        $this->actingAs($user);

        // Buat transaksi
        app(TransactionService::class)->create([...], $user);

        // Pastikan job masuk queue
        Queue::assertPushed(SendWhatsAppJob::class);
    }

    /** @test */
    public function wa_not_sent_when_throttled(): void
    {
        // Simulasi throttle aktif
        Cache::put("wa_throttle_628123456789", true, 60);

        $result = app(WhatsAppService::class)->send('08123456789', 'test');

        $this->assertFalse($result);
    }
}
```

---

## 📊 Queue & Horizon

```bash
composer require laravel/horizon
php artisan horizon:install
```

```php
// config/horizon.php — konfigurasi queue worker

'environments' => [
    'production' => [
        'supervisor-default' => [
            'connection' => 'redis',
            'queue'      => ['notifications', 'reports', 'default'],
            'balance'    => 'auto',
            'processes'  => 3,
            'tries'      => 3,
        ],
    ],
],
```

```php
// Queue per prioritas:
// Notifikasi WA masuk queue 'notifications' (prioritas tinggi)
SendWhatsAppJob::dispatch($phone, $message)->onQueue('notifications');

// Generate laporan PDF masuk queue 'reports' (prioritas rendah)
GenerateMonthlyReportJob::dispatch($family)->onQueue('reports');
```

Monitor queue di: `http://your-app.com/horizon`

---

## 📋 Konvensi Kode

### Naming
- Model: `PascalCase` singular → `Transaction`, `Wallet`
- Controller: `PascalCase` + Controller → `TransactionController`
- Service: `PascalCase` + Service → `TransactionService`
- Job: verb + noun → `SendWhatsAppJob`, `GenerateReportJob`
- Event: noun + past tense → `TransactionCreated`
- Listener: verb + noun → `NotifyFamilyOnTransaction`

### Rules
- Selalu gunakan **Database Transaction** (`DB::transaction`) untuk operasi yang melibatkan update balance wallet
- Selalu gunakan **Queue** untuk notifikasi WA dan email
- Selalu gunakan **Policy** untuk authorization
- Semua query yang bisa N+1 harus pakai **Eager Loading**
- Kolom yang sering di-filter harus punya **Index** di migration

---

## 🔧 Environment Variables Penting

```env
# App
APP_NAME=KasKita
APP_URL=http://localhost

# Database
DB_CONNECTION=mysql

# Redis (Queue & Cache)
REDIS_HOST=127.0.0.1
QUEUE_CONNECTION=redis
CACHE_STORE=redis

# Google OAuth (Socialite)
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=${APP_URL}/auth/google/callback

# Telegram Bot
TELEGRAM_BOT_TOKEN=123456789:ABCdef...  # dari @BotFather
TELEGRAM_WEBHOOK_URL=${APP_URL}/telegram/webhook

# AI — Google Gemini
# Daftar gratis di: https://aistudio.google.com/apikey
GEMINI_API_KEY=AIzaSy...
PRISM_GEMINI_API_KEY=AIzaSy...

# Mail
MAIL_MAILER=smtp
MAIL_FROM_ADDRESS=noreply@kaskita.app

# Vite
VITE_APP_NAME="${APP_NAME}"
```

---

## 📌 Catatan Penting

> **Gemini Free Tier** — 1.500 request/hari gratis (gemini-2.0-flash). Cukup untuk ratusan keluarga. Daftar API key di https://aistudio.google.com/apikey, tidak perlu kartu kredit.

> **Telegram Bot** — API resmi Google, tidak kena ban, gratis selamanya. Setup via @BotFather. Setiap anggota keluarga perlu `/start` sekali untuk verifikasi.

> **Struk OCR** — Foto struk dikirim ke Gemini Vision, dikembalikan sebagai JSON terstruktur. Kalau confidence "low" atau gagal parse, user diarahkan input manual.

> **Global Scope** — otomatis bypass saat `php artisan` (seeder, queue). Aman untuk background jobs.

> **Observer vs Traits** — Observer untuk logic spesifik per model (update balance wallet, notif Telegram). Traits untuk logic umum lintas model (set family_id, global scope).

> **DB::transaction + lockForUpdate()** — wajib dipakai saat update saldo wallet. Cegah race condition kalau suami & istri input transaksi bersamaan.

---

*Dokumen ini diupdate seiring development. Setiap perubahan arsitektur besar harus dicatat di sini.*
