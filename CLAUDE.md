# CLAUDE.md — IGLO CMS (cmsalt2)

CMS halaman **Tentang Kami** situs IGLO (frontend: `marketingbrandingiglo/iglowebsitealt2`).
Laravel 13 + Filament 5, PHP ≥ 8.4 (dibutuhkan `composer.lock`). Aplikasi ada di **root repo**.
Detail fitur & struktur: `README.md`.

## Aturan keras

- Jangan pernah meng-expose secret (APP_KEY, DB_URL, R2_*, ADMIN_PASSWORD) ke frontend,
  ke respons API, ke log, atau ke git. `.env` tidak di-commit.
- Validasi semua input (form Filament: `required`, `maxLength`, `acceptedFileTypes`, `maxSize`).
- Minta izin pemilik sebelum mengubah autentikasi / hak akses (login panel, `User`,
  `canAccessPanel`, CORS, akun admin).
- Push hanya ke branch kerja; jangan ke `main` tanpa izin.
- Komunikasi dengan pemilik repo dalam Bahasa Indonesia (bukan developer).

## Konvensi

- Media: selalu pakai disk dari `config('cms.media_disk')` (env `MEDIA_DISK` / `CMS_MEDIA_DISK`),
  jangan hard-code `disk('public')`. Upload lewat `App\Filament\Support\MediaUpload`,
  URL lewat `App\Support\Media::url()`, cek keberadaan file lewat `Media::exists()`
  (di R2 cukup satu request LIST per request HTTP).
- Disk `r2` (Cloudflare R2): tanpa ACL per objek (`Media::visibility()` = null,
  `retain_visibility` false, tanpa `visibility` di config) — R2 menolak ACL public-read.
- Path media hasil seed (mis. `img/...`) boleh menunjuk ke aset frontend yang tidak ada di
  disk media; `MediaUpload` mempertahankannya saat form disimpan.
- Seeder/start-up harus idempoten: data awal tidak boleh muncul lagi setelah editor
  menghapusnya. `AboutSeeder` mengosongkan tabel About — hanya untuk database kosong.
- Konfigurasi dibaca saat runtime (Vercel): jangan `config:cache` di image Vercel.

## Deploy

- **Vercel + Neon + R2** (aktif): `docs/DEPLOY-VERCEL.md`. `vercel.json` (container,
  region `sin1`), `Dockerfile.vercel`, `docker/Caddyfile.vercel`, `docker/vercel-start.sh`.
  Server langsung listen di `$PORT`; `php artisan cms:bootstrap` jalan di background
  (migrate → seed sekali dengan penanda cache `cms:initial-seed-done` → `AdminUserSeeder`
  → impor `CMS_ASSETS_SOURCE`), dikunci advisory lock Postgres.
  Livewire temporary upload memakai disk media bila driver-nya s3 (`AppServiceProvider`).
- **Railway** (lama): `docs/DEPLOY-RAILWAY.md`, `Dockerfile`, `docker/start.sh` → `cms:install`.
- Frontend membaca env `CMS_URL` (alamat dasar CMS, tanpa `/api`).

## Cek sebelum commit

```bash
php artisan test
vendor/bin/pint --dirty
```
