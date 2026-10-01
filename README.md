# IGLO CMS — Halaman Tentang Kami / About Us

CMS untuk mengelola seluruh konten halaman **Tentang Kami** di
[iglowebsitealt2.vercel.app/about](https://iglowebsitealt2.vercel.app/about)
(repo frontend `marketingbrandingiglo/iglowebsitealt2`).

- **Stack:** Laravel 13 + Filament 5 (panel admin) — sama dengan rencana `backend/` di repo frontend.
- **Database:** MySQL (`iglo_cms`) atau SQLite untuk uji cepat.
- **Bilingual:** setiap teks punya versi **ID** (utama) dan **EN**, diedit berdampingan.
  Bila teks EN kosong, API otomatis memakai teks ID.
- **REST API** `GET /api/about` mengembalikan JSON dengan bentuk **identik** dengan
  `content[lang].about` di `lib/content.js` frontend (sudah diverifikasi: 0 perbedaan).

## Apa saja yang bisa dikelola

Semua ada di grup menu **Tentang Kami** pada panel admin (`/admin`):

| Menu | Seksi di halaman /about | Isi |
|---|---|---|
| **Konten Halaman** | semua teks seksi | Tab: Hero (banner + kicker/judul/subjudul), Siapa Kami, Visi & Misi, Nilai i5 (logo i5 + judul), Maskot & Milestone (judul + narasi), Video (file video + poster + teks), Mitra & Klien (judul seksi) |
| **Statistik & Keunggulan** | panel "company points" | Statistik ("Lebih Dari 1100" + label) & keunggulan (ikon + teks); bisa diurutkan, disembunyikan |
| **Nilai i5** | lingkaran nilai i5 | Integrity, Involved, Integrated, Impressive, Innovative — ikon & deskripsi |
| **Maskot** | Meet Our Mascot | Zenith, Elio, Aero, Nova — nama, bio multi-paragraf (ID/EN), gambar scene & profil |
| **Milestone** | Our Milestones | Periode (2001-2005 … 2021-Present) beserta logo partner/penghargaan per periode |
| **Mitra** | Our Partner | Logo mitra + nama (alt) + website opsional |
| **Klien** | Our Client | Tab kategori (Multifinance, Asuransi, Bank, …) beserta logo klien |

Semua daftar mendukung **drag & drop urutan**, toggle **tampil/sembunyi**, dan **upload gambar**.
Seeder mengisi CMS dengan konten yang saat ini tampil di website.

## Menjalankan secara lokal

Prasyarat: PHP ≥ 8.3 (ekstensi `pdo_sqlite`/`pdo_mysql`, `intl`, `gd`, `zip`), Composer.

```bash
composer install
cp .env.example .env
php artisan key:generate

# --- pilih database ---
# a) SQLite (paling cepat)
touch database/database.sqlite
# b) MySQL: buat DB lalu ubah DB_* di .env (DB_CONNECTION=mysql, DB_DATABASE=iglo_cms)
#    mysql -u root -p -e "CREATE DATABASE iglo_cms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

php artisan migrate --seed     # tabel + konten About + user admin
php artisan storage:link       # agar file upload bisa diakses publik

# Impor gambar & video About dari repo frontend ke storage CMS
# (agar pratinjau gambar di admin tampil & media bisa diganti dari CMS)
php artisan cms:import-assets ../iglowebsitealt2/public

php artisan serve              # http://127.0.0.1:8000/admin
```

Atau sekaligus: `composer setup` (lalu jalankan `cms:import-assets`).

**Login admin (DEV — ganti sebelum produksi!)**: `design@indocyber.id` / `iglocms123`
(bisa diubah lewat `CMS_ADMIN_*` di `.env` sebelum seeding, atau buat user baru dengan
`php artisan make:filament-user`).

> ⚠️ `php artisan db:seed` / `migrate --seed` **mengosongkan lalu mengisi ulang** tabel About.
> Jangan jalankan di produksi setelah konten mulai diedit.

## REST API

| Endpoint | Hasil |
|---|---|
| `GET /api/about` | `{ "id": {...}, "en": {...} }` |
| `GET /api/about/{id\|en}` | konten satu bahasa |

Contoh potongan respons `/api/about/en`:

```json
{
  "hero": { "kicker": "ABOUT US", "title": "We Give You The Best Solution", "subtitle": "…", "image": "http://127.0.0.1:8000/storage/img/Banner/banner_aboutus.png" },
  "company": { "name": "Indocyber Global Teknologi", "p1": "…", "p2": "…" },
  "visionTitle": "Our Vision", "vision": "…", "missionTitle": "Our Mission", "mission": "…",
  "stats": [{ "value": "1100", "label": "Consultants & Developers", "icon": "developer" }],
  "values": [{ "title": "Integrity", "desc": "…", "icon": "…/ico_integrity_trn.png" }],
  "mascotZenith": { "key": "zenith", "name": "Zenith", "bio": ["…", "…"], "sceneImage": "…", "profileImage": "…" },
  "milestone": { "title": "Our Milestones", "narrative": "…", "items": [{ "period": "2021 - Present", "logos": [{ "src": "…", "alt": "Tableau" }] }] },
  "video": { "title": "Company Video", "text": "…", "src": "…/iglo_compro_sd.mp4", "poster": null },
  "partner": { "title": "Our Partner", "logos": [{ "src": "…", "alt": "SAP", "url": null }] },
  "client": { "title": "Our Client", "tabs": ["Multifinance", "Insurance", "…"], "logos": [[{ "src": "…", "alt": "ACC" }]] }
}
```

Respons di-cache (`CMS_CACHE_TTL`, default 3600 detik) dan otomatis di-refresh setiap
ada perubahan di admin. CORS diatur lewat `CORS_ALLOWED_ORIGINS`.

Panduan memasang API ini di frontend Next.js: **[docs/INTEGRASI-FRONTEND.md](docs/INTEGRASI-FRONTEND.md)**.

## Konfigurasi `.env` khusus CMS

| Variabel | Fungsi |
|---|---|
| `CMS_ADMIN_NAME/EMAIL/PASSWORD` | Akun admin yang dibuat seeder |
| `FRONTEND_URL` | Tombol "Lihat halaman" + prefix URL media yang belum diimpor ke storage CMS. Kosong = path relatif `/img/...` |
| `CORS_ALLOWED_ORIGINS` | Domain frontend yang boleh memanggil API (pisahkan koma) |
| `CMS_ASSETS_SOURCE` | Opsional — path `public/` frontend agar `db:seed` sekalian menjalankan `cms:import-assets` |
| `CMS_MEDIA_DISK` | Disk upload (default `public`; bisa `s3`) |
| `CMS_CACHE_TTL` | Lama cache API (detik), `0` = tanpa cache |

Upload video dibatasi 100 MB (lihat `config/livewire.php`). Pastikan juga
`upload_max_filesize` & `post_max_size` di `php.ini` server ≥ 100M.

## Struktur kode

```
app/
├── Console/Commands/ImportAboutAssets.php   # php artisan cms:import-assets
├── Filament/
│   ├── Pages/ManageAboutPage.php            # "Konten Halaman" (singleton, tab per seksi)
│   ├── Resources/                           # Statistik, Nilai i5, Maskot, Milestone, Mitra, Klien
│   ├── Support/Bilingual.php                # field ID | EN berdampingan
│   ├── Support/MediaUpload.php              # konfigurasi upload gambar/video
│   └── Widgets/AboutOverview.php            # ringkasan di dasbor
├── Http/Controllers/Api/AboutController.php # GET /api/about
├── Models/                                  # AboutPage, AboutHighlight, CompanyValue, Mascot,
│                                            # MilestonePeriod, MilestoneLogo, Partner, ClientCategory, Client
└── Support/
    ├── AboutContent.php                     # menyusun payload API (bentuk = lib/content.js)
    └── Media.php                            # path → URL (storage CMS / fallback frontend)
config/cms.php                               # locale, disk media, frontend URL, cache
database/
├── migrations/2026_10_01_000001_create_about_tables.php
└── seeders/AboutSeeder.php + data/about.json  # konten awal dari lib/content.js
docs/                                        # panduan integrasi Next.js + hook useAboutContent
tests/Feature/                               # test API & panel admin
```

### Skema database

| Tabel | Kolom utama |
|---|---|
| `about_pages` | `hero_image`, `i5_logo`, `video_file`, `video_poster`, `content` (JSON `{id:{…}, en:{…}}`) |
| `about_highlights` | `type` (stat/feature), `icon`, `value`, `text` (JSON id/en), `sort_order`, `is_active` |
| `company_values` | `title`, `description` (JSON id/en), `icon`, `sort_order`, `is_active` |
| `mascots` | `key` (zenith/elio/aero/nova), `name`, `bio` (JSON id/en → array paragraf), `scene_image`, `profile_image` |
| `milestone_periods` → `milestone_logos` | `period` → `name`, `logo` |
| `partners` | `name`, `logo`, `url` |
| `client_categories` → `clients` | `name` (JSON id/en) → `name`, `logo` |

## Test

```bash
php artisan test     # API (bentuk payload, fallback bahasa, cache, media) + panel admin
vendor/bin/pint      # code style
```

## Deploy (ringkas)

1. Server PHP 8.3+ dengan MySQL; `composer install --no-dev --optimize-autoloader`.
2. `.env` produksi: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://cms.domain`,
   kredensial DB, `FRONTEND_URL`, `CORS_ALLOWED_ORIGINS`.
3. `php artisan migrate --force && php artisan db:seed --force` (**sekali saja** di awal),
   `php artisan storage:link`, `php artisan cms:import-assets <path public frontend>`.
4. `php artisan optimize` dan `php artisan filament:optimize`.
5. Ganti password admin default.
6. Di frontend (Vercel) set `NEXT_PUBLIC_CMS_URL`, lalu ikuti
   [docs/INTEGRASI-FRONTEND.md](docs/INTEGRASI-FRONTEND.md).
