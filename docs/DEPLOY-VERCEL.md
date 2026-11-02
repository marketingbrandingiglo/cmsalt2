# Deploy IGLO CMS ke Vercel (gratis) + Neon + Cloudflare R2

CMS (Laravel + Filament) jalan sebagai **container service** di Vercel (fitur Beta
"container runtime"), region Singapore (`sin1`), dekat database Neon
(AWS ap-southeast-1). Tidak perlu kartu kredit.

| Bagian | Layanan | Variabel |
|---|---|---|
| Aplikasi | Vercel (container, `sin1`) | — |
| Database | Neon Postgres (Singapore) | `DB_CONNECTION=pgsql`, `DB_URL` |
| Gambar/video upload | Cloudflare R2 | `MEDIA_DISK=r2`, `R2_*` |

File terkait (semua di **root repo**, tidak ada folder `backend/`):

- `vercel.json` — container service + region `sin1`
- `Dockerfile.vercel` — FrankenPHP (PHP 8.4 + Caddy) yang listen di `$PORT`
- `docker/Caddyfile.vercel`, `docker/vercel-start.sh` — web server + start-up
- `php artisan cms:bootstrap` — dijalankan di background setiap container start:
  migrasi, isi konten About awal **sekali saja** (data yang dihapus editor tidak
  muncul lagi), buat akun admin dari `ADMIN_EMAIL`/`ADMIN_PASSWORD` bila belum ada (password ikut
  diganti bila nilai `ADMIN_PASSWORD` diubah lalu Redeploy),
  dan (opsional) impor media dari `CMS_ASSETS_SOURCE`.

`Dockerfile`, `railway.json`, `docker/start.sh` (Railway) tetap ada dan tidak dipakai Vercel.

---

## 1. Neon — database

1. https://console.neon.tech → **New Project** → region **AWS Asia Pacific (Singapore)**.
2. Pilih **Just Postgres**. Abaikan tawaran "install Neon skill".
3. **Connect** → matikan **Connection pooling** → salin connection string.
   Pastikan host-nya **tidak** mengandung `-pooler`. Ini nilai `DB_URL`
   (`postgresql://...neon.tech/neondb?sslmode=require...`).

## 2. Cloudflare R2 — penyimpanan gambar

1. R2 → **Create bucket** (mis. `iglo-cms-media`). Nama ini = `R2_BUCKET`.
2. Bucket → **Settings** → **Public Development URL** → **Enable**.
   URL `https://pub-....r2.dev` = `R2_PUBLIC_URL`.
3. Bucket → **Settings** → **S3 API**: salin endpoint **tanpa** `/nama-bucket`
   di belakang: `https://<account-id>.r2.cloudflarestorage.com` = `R2_ENDPOINT`.
4. R2 → **Manage API tokens** → **Create API token** → izin **Object Read & Write**,
   khusus bucket tadi → simpan **Access Key ID** (`R2_ACCESS_KEY_ID`) dan
   **Secret Access Key** (`R2_SECRET_ACCESS_KEY`). Secret hanya tampil sekali.
5. Bucket → **Settings** → **CORS Policy** → **Add CORS policy** → tab JSON:

   ```json
   [
     {
       "AllowedOrigins": ["*"],
       "AllowedMethods": ["GET", "PUT", "HEAD"],
       "AllowedHeaders": ["*"],
       "ExposeHeaders": ["ETag"],
       "MaxAgeSeconds": 3600
     }
   ]
   ```

   Ini perlu karena file upload dari admin dikirim browser **langsung ke R2**
   (body request ke Vercel dibatasi ±4,5 MB). Tetap aman: upload butuh URL
   bertanda tangan dari server yang hanya berlaku sebentar.
6. (Opsional) **Object lifecycle rules** → hapus objek berawalan `livewire-tmp/`
   setelah 1 hari (sisa upload sementara yang dibatalkan).

## 3. APP_KEY

Buka tab browser apa saja → **F12** → **Console**, tempel lalu Enter:

```js
'base64:'+btoa(String.fromCharCode(...crypto.getRandomValues(new Uint8Array(32))))
```

Salin hasilnya **tanpa tanda kutip**. Jangan pakai tombol "Generate" di Vercel
(formatnya tidak cocok untuk Laravel).

## 4. Vercel — project CMS

1. https://vercel.com → **Add New… → Project** → ketik `cmsalt2` di kotak **Search**
   (daftar hanya menampilkan 5 repo terbaru).
   Kalau tidak muncul: **Adjust GitHub App Permissions** → tambahkan `cmsalt2`
   **tanpa menghapus repo lain** (atau pilih *All repositories*).
2. **Framework Preset:** Other · **Root Directory:** biarkan kosong (`./`).
3. **Environment Variables** (centang **Production**, tanpa tanda kutip):

   ```env
   APP_KEY=base64:...
   APP_URL=https://<project>.vercel.app
   DB_CONNECTION=pgsql
   DB_URL=postgresql://...
   SESSION_SECURE_COOKIE=true
   MEDIA_DISK=r2
   R2_BUCKET=iglo-cms-media
   R2_ENDPOINT=https://<account-id>.r2.cloudflarestorage.com
   R2_PUBLIC_URL=https://pub-....r2.dev
   R2_ACCESS_KEY_ID=...
   R2_SECRET_ACCESS_KEY=...
   ADMIN_EMAIL=design@indocyber.id
   ADMIN_PASSWORD=...
   ```

   Opsional (disarankan): `CMS_ASSETS_SOURCE=https://iglowebsitealt2.vercel.app`
   — saat start pertama, ±135 gambar + video halaman About disalin dari situs
   frontend ke R2, sehingga thumbnail di admin tampil. Tanpa ini, website tetap
   memakai gambar milik frontend (path `/img/...`).
4. **Deploy.** Deploy pertama memakai branch `main` dan **wajar gagal**
   (mis. "No Output Directory named dist").
5. Ubah branch produksi ke `claude/deploy-cms-vercel-neon-r2-5fjikb`:
   **Settings → Environments → Production → Branch Tracking** (atau **Settings → Git**).
6. **Deployments → Create Deployment** → branch `claude/deploy-cms-vercel-neon-r2-5fjikb`
   → **Create**. Build pertama ±5–10 menit.
7. Kalau domain akhirnya bukan yang diisi di `APP_URL`, ubah `APP_URL` lalu **Redeploy**.

## 5. Cek

- `https://<domain>/up` → halaman hijau "Application up".
- `https://<domain>/api/about` → JSON (`{"id":{...},"en":{...}}`). Pada start pertama
  konten diisi di background; kalau masih error, tunggu ±1 menit lalu refresh.
- `https://<domain>/admin` → login dengan `ADMIN_EMAIL` / `ADMIN_PASSWORD`.
  Coba unggah satu gambar untuk memastikan R2 + CORS benar.

## 6. Frontend (iglowebsitealt2)

Website memanggil CMS lewat `app/api/cms/about/route.js`, yang membaca env
**`CMS_URL`** (alamat dasar CMS, **tanpa** `/api`). Di project Vercel frontend:
**Settings → Environment Variables** → `CMS_URL=https://<domain-cms>` (Production)
→ **Redeploy** frontend.

---

## Mengatasi masalah

Vercel → project CMS → **Logs** → centang level **Info** → cari `production.ERROR`
atau baris dari `cms:bootstrap`.

| Gejala | Penyebab & solusi |
|---|---|
| `connection to server at "127.0.0.1"` | `DB_URL` tidak terbaca: belum ada, tidak dicentang **Production**, atau ada tanda kutip. Perbaiki lalu **Redeploy** (perubahan variabel baru berlaku setelah redeploy). |
| `Menunggu database siap (30/30)` | Connection string salah, atau memakai host `-pooler`. |
| `FUNCTION_INVOCATION_FAILED` | Container gagal start/listen. Lihat Logs build & runtime. |
| `ADMIN_EMAIL / ADMIN_PASSWORD belum di-set` | Isi kedua variabel lalu Redeploy. |
| Ganti password admin | Ubah nilai `ADMIN_PASSWORD` → Save → Redeploy. Log: `Password admin ... diganti sesuai ADMIN_PASSWORD.` Password baru berlaku ±1 menit setelah Ready. |
| Upload gambar macet / gagal di admin | CORS bucket R2 belum diatur (langkah 2.5) atau token R2 bukan *Read & Write*. |
| Gambar upload tidak tampil di website | Public Development URL (r2.dev) belum aktif, atau `R2_PUBLIC_URL` salah. |
| Login admin langsung keluar lagi | `APP_URL` tidak sama dengan domain yang dibuka, atau `APP_KEY` berubah. |

## Catatan

- Push ke branch produksi → Vercel deploy otomatis. Data (Neon) dan upload (R2) aman.
- Container runtime Vercel masih **Beta**; paket Hobby ditujukan untuk pemakaian
  non-komersial. Instance bisa "tidur" saat sepi, jadi request pertama setelahnya lebih lambat.
- Frontend menunggu CMS maksimal 8 detik; bila CMS sedang "bangun", website memakai
  konten statis bawaannya dan data CMS tampil lagi saat di-refresh.
- Panel belum punya menu kelola user, dan Vercel tidak punya shell. Admin pertama
  dibuat dari `ADMIN_EMAIL`/`ADMIN_PASSWORD`; untuk akun lain perlu fitur tambahan.
