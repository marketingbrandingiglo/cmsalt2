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
| **Konten Halaman** | Hero | Banner hero + kicker/judul/subjudul |
| ↳ **Siapa Kami** | panel "Who We Are" | Nama perusahaan, label, paragraf 1 & 2 |
| ↳ **Visi & Misi** | panel Visi & Misi | Judul + isi visi dan misi |
| ↳ **Statistik & Keunggulan** | panel "company points" | Statistik ("Lebih Dari 1100" + label) & keunggulan (ikon + teks) |
| **Nilai i5** | lingkaran nilai i5 | Di atas: logo i5 + judul/subjudul seksi. Tabel: 5 nilai (ikon & penjelasan) |
| **Maskot** | Meet Our Mascot | Di atas: judul seksi. Tabel: Zenith, Elio, Aero, Nova (bio ID/EN, gambar) |
| **Milestone** | Our Milestones | Di atas: judul & narasi. Tabel: periode beserta logo |
| **Partner** | Our Partner | Di atas: judul seksi. Tabel: logo partner + nama + website |
| **Klien** | Our Client | Di atas: judul seksi. Tabel: tab kategori beserta logo klien |
| **Video** | Video Perusahaan | File video, poster, judul, deskripsi |

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

Secara default respons **tidak** di-cache di CMS (`CMS_CACHE_TTL=0`), jadi perubahan di admin
langsung terbaca API. CORS diatur lewat `CORS_ALLOWED_ORIGINS`.

Panduan memasang API ini di frontend Next.js: **[docs/INTEGRASI-FRONTEND.md](docs/INTEGRASI-FRONTEND.md)**.

## Konfigurasi `.env` khusus CMS

| Variabel | Fungsi |
|---|---|
| `CMS_ADMIN_NAME/EMAIL/PASSWORD` | Akun admin yang dibuat seeder |
| `FRONTEND_URL` | Tombol "Lihat halaman" + prefix URL media yang belum diimpor ke storage CMS. Kosong = path relatif `/img/...` |
| `CORS_ALLOWED_ORIGINS` | Domain frontend yang boleh memanggil API (pisahkan koma) |
| `CMS_ASSETS_SOURCE` | Opsional — folder `public/` frontend **atau URL situs frontend**; media diimpor saat `db:seed` / `cms:install` |
| `CMS_MEDIA_DISK` | Disk upload (default `public`; bisa `s3`) |
| `CMS_CACHE_TTL` | Lama cache API (detik), default `0` = tanpa cache (disarankan) |

Upload video dibatasi 100 MB (lihat `config/livewire.php`). Pastikan juga
`upload_max_filesize` & `post_max_size` di `php.ini` server ≥ 100M.

## Struktur kode

```
app/
├── Console/Commands/ImportAboutAssets.php   # php artisan cms:import-assets
├── Console/Commands/InstallCms.php          # php artisan cms:install (dipakai saat deploy)
├── Filament/
│   ├── Concerns/EditsAboutSection.php       # form judul seksi di atas tabel (Nilai i5, Maskot, …)
│   ├── Pages/                               # Konten Halaman (Hero), Siapa Kami, Visi & Misi, Video
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

## Deploy

**Railway (disarankan):** ikuti **[docs/DEPLOY-RAILWAY.md](docs/DEPLOY-RAILWAY.md)** — cukup
klik di dashboard; repo sudah berisi `Dockerfile`, `railway.json`, dan `docker/start.sh`.

Saat container start, `docker/start.sh` menjalankan `php artisan cms:install` (idempoten):
migrasi, isi konten awal **hanya bila database kosong**, buat admin pertama, `storage:link`,
dan impor media dari `CMS_ASSETS_SOURCE` (folder atau URL situs frontend). `APP_KEY` dibuat
sekali dan disimpan di volume bila tidak di-set.

**Hosting lain / VPS:** image Docker yang sama bisa dipakai (port `8080`, health check `/up`,
volume ke `/app/storage`). Tanpa Docker: `composer install --no-dev -o`, set `.env` produksi,
`php artisan cms:install`, `php artisan optimize`, lalu arahkan web server ke `public/`.
Ganti password admin default dan set `NEXT_PUBLIC_CMS_URL` di frontend
([docs/INTEGRASI-FRONTEND.md](docs/INTEGRASI-FRONTEND.md)).
