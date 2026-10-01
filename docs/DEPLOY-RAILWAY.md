# Deploy IGLO CMS ke Railway

Hasil akhir: link publik seperti `https://iglo-cms-production.up.railway.app/admin`
yang bisa dibuka dan dibagikan ke tim, dengan database MySQL dan file upload yang
**tersimpan permanen**. Setiap push ke `main` otomatis di-deploy ulang.

Repo ini sudah berisi semua konfigurasinya (`Dockerfile`, `railway.json`,
`docker/start.sh`). Yang perlu dilakukan hanya klik di dashboard Railway (±10 menit).

> Biaya: Railway menagih berdasarkan pemakaian (ada masa trial; setelahnya paket Hobby).
> Cek harga terbaru di railway.com/pricing.

---

## 1. Buat project dari GitHub

1. Buka **https://railway.com** → **Login with GitHub** (pakai akun yang punya akses ke
   `marketingbrandingiglo/cmsalt2`).
2. **New Project** → **Deploy from GitHub repo** → pilih **`marketingbrandingiglo/cmsalt2`**.
   - Kalau repo tidak muncul: klik **Configure GitHub App** dan beri Railway akses ke repo ini.
3. Railway langsung mencoba build. **Deploy pertama boleh gagal** — variabelnya belum diisi.
   Lanjutkan langkah berikut.

## 2. Tambah database MySQL

Di kanvas project: **+ Create** (atau **New**) → **Database** → **MySQL**.

## 3. Pasang volume (penyimpanan file upload)

Klik kanan service **cmsalt2** → **Attach volume** → **Mount path**: `/app/storage`

Tanpa volume, gambar/video yang diunggah akan hilang setiap kali deploy ulang.

## 4. Buat domain publik

Service **cmsalt2** → **Settings** → **Networking** → **Generate Domain**.
Jika ditanya port, isi **8080**.

## 5. Isi variabel

Service **cmsalt2** → **Variables** → **Raw Editor**, tempel lalu sesuaikan password:

```env
APP_NAME="IGLO CMS"
APP_URL=https://${{RAILWAY_PUBLIC_DOMAIN}}
DB_CONNECTION=mysql
DB_URL=${{MySQL.MYSQL_URL}}

CMS_ADMIN_NAME="Admin IGLO"
CMS_ADMIN_EMAIL=design@indocyber.id
CMS_ADMIN_PASSWORD=GANTI-DENGAN-PASSWORD-KUAT

CMS_ASSETS_SOURCE=https://iglowebsitealt2.vercel.app
FRONTEND_URL=https://iglowebsitealt2.vercel.app
CORS_ALLOWED_ORIGINS=https://iglowebsitealt2.vercel.app,http://localhost:3000
```

- `${{MySQL.MYSQL_URL}}` dan `${{RAILWAY_PUBLIC_DOMAIN}}` ditulis persis seperti itu —
  Railway mengisinya otomatis. (Jika service database Anda bernama lain, ganti `MySQL`.)
- `APP_KEY` **tidak perlu** diisi: dibuat otomatis sekali dan disimpan di volume.
- Kalau `CMS_ADMIN_PASSWORD` lupa diisi, password acak dibuat dan dicetak di **Deploy Logs**.

Klik **Deploy** / **Apply changes**.

## 6. Tunggu deploy & buka

Pantau tab **Deployments → View logs**. Pada deploy pertama container akan otomatis:

1. membuat tabel database,
2. mengisi seluruh konten halaman Tentang Kami saat ini,
3. membuat akun admin,
4. mengunduh ±135 gambar + video dari `iglowebsitealt2.vercel.app` ke volume
   (bisa 1–3 menit) — log: `Disalin: 135, ...`.

Setelah status **Active**, buka:

- Panel admin: `https://<domain-anda>/admin` → login dengan `CMS_ADMIN_EMAIL` /
  `CMS_ADMIN_PASSWORD`.
- API: `https://<domain-anda>/api/about`

Link admin inilah yang dibagikan ke tim. Tambah akun untuk anggota tim lain lewat
service → **⋯ → Shell / SSH** lalu `php artisan make:filament-user`.

---

## Catatan

- **Deploy berikutnya aman**: data tidak di-seed ulang, hasil edit dan upload tetap ada
  (`cms:install` hanya mengisi data bila database masih kosong).
- **Log "tidak ditemukan: N"** saat impor media biasanya berarti situs Vercel diproteksi
  (Vercel Authentication). Media tetap tampil di API (memakai URL frontend), tetapi
  pratinjaunya di admin kosong. Matikan proteksi atau jalankan ulang deploy setelahnya.
- **Domain sendiri** (mis. `cms.indocyber.co.id`): Settings → Networking → Custom Domain,
  lalu ubah variabel menjadi `APP_URL=https://cms.indocyber.co.id`.
- **Hosting lain** (Render, Fly.io, VPS dengan Docker): image yang sama bisa dipakai —
  set variabel yang sama, mount volume ke `/app/storage`, expose port `8080`,
  health check `/up`.
