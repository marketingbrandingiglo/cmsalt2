# Integrasi ke frontend Next.js (iglowebsitealt2)

> **Status: SUDAH TERPASANG** di `iglowebsitealt2` (branch `main`). Implementasi akhirnya
> sedikit berbeda dari langkah di bawah: browser memanggil **`/api/cms/about` milik website**
> (`app/api/cms/about/route.js`), yang meneruskan ke `GET /api/about` CMS di server dan
> tidak di-cache (selalu data terbaru) — jadi tidak bergantung pada CORS, dan perubahan di CMS
> tampil di website begitu halaman di-refresh. URL CMS default `https://cmsalt2-production.up.railway.app`,
> bisa diganti dengan env `CMS_URL` di Vercel. Hook ada di `components/useAboutContent.js`.

CMS ini menyajikan konten halaman `/about` lewat REST API dengan **bentuk JSON yang
sama persis** seperti `content[lang].about` di `lib/content.js`. Jadi integrasi cukup
mengganti sumber data `t.about` → data CMS, tanpa merombak komponen.

## 1. Endpoint

| Method | URL | Hasil |
|---|---|---|
| GET | `/api/about` | `{ "id": { ...about }, "en": { ...about } }` |
| GET | `/api/about/id` | konten Bahasa Indonesia |
| GET | `/api/about/en` | konten English |

Respons CMS tidak di-cache secara default (`CMS_CACHE_TTL=0`), jadi perubahan di admin
langsung terbaca.

### Kunci tambahan dibanding `lib/content.js`

| Kunci | Isi | Pengganti hardcode di frontend |
|---|---|---|
| `hero.image` | URL banner hero | `"/img/Banner/banner_aboutus.png"` |
| `i5Logo` | URL logo i5 | `"/logo/i5-fixed.png"` |
| `stats[].icon` | `client` / `developer` | `STATS_ICON_KEYS` |
| `values[].icon` | URL ikon nilai | `VALUE_ICONS` |
| `mascotX.sceneImage`, `mascotX.profileImage` | URL gambar maskot | path di `MascotScene.js` |
| `mascots[]` | daftar maskot berurutan | — |
| `video.src`, `video.poster` | URL video perusahaan | `"/video/iglo_compro_sd.mp4"` |
| `partner.logos[].url` | website mitra (opsional) | — |

URL media: file yang diunggah lewat CMS → URL absolut CMS (`https://cms.../storage/...`);
media hasil seed yang belum diimpor → `FRONTEND_URL + path` (atau `/img/...` relatif
bila `FRONTEND_URL` kosong) sehingga tetap memakai aset di `public/` Next.js.

## 2. Langkah integrasi

1. Tambah env di Vercel / `.env.local` frontend:
   ```
   NEXT_PUBLIC_CMS_URL=https://cms.indocyber.co.id
   ```
   dan pastikan domain frontend ada di `CORS_ALLOWED_ORIGINS` milik CMS.

2. Salin [`docs/nextjs/useAboutContent.js`](nextjs/useAboutContent.js) ke
   `components/useAboutContent.js`.

3. `app/about/page.js`:
   ```diff
   +import { useAboutContent } from "@/components/useAboutContent";
    ...
    export default function AboutPage() {
      const { t } = useLanguage();
   -  const a = t.about;
   +  const a = useAboutContent();
   ```
   Opsional — agar gambar juga dikelola CMS (semua punya fallback ke nilai lama):
   ```diff
   -<PageHeroBanner image="/img/Banner/banner_aboutus.png" ... />
   +<PageHeroBanner image={a.hero.image || "/img/Banner/banner_aboutus.png"} ... />

   -src="/logo/i5-fixed.png"
   +src={a.i5Logo || "/logo/i5-fixed.png"}

   -{STAT_ICONS[STATS_ICON_KEYS[i]]}
   +{STAT_ICONS[s.icon || STATS_ICON_KEYS[i]]}

   -<img src={VALUE_ICONS[layout.title]} alt="" />
   +<img src={v.icon || VALUE_ICONS[layout.title]} alt="" />

   -videoSrc="/video/iglo_compro_sd.mp4"
   +videoSrc={a.video.src || "/video/iglo_compro_sd.mp4"}
   ```

4. `components/ClientTabs.js` membaca `t.about.client` langsung — ganti ke hook yang sama:
   ```diff
   -import { useLanguage } from "./LanguageContext";
   +import { useAboutContent } from "./useAboutContent";
    ...
   -  const { t } = useLanguage();
   -  const tabs = t.about.client.tabs;
   +  const about = useAboutContent();
   +  const tabs = about.client.tabs;
    ...
   -  const logos = t.about.client.logos[active] || [];
   +  const logos = about.client.logos[active] || [];
   ```

5. (Opsional) `MascotScene.js`: gunakan `zenith.profileImage`, `elio.profileImage`, dst.
   sebagai pengganti path `/img/Mascot/*_show.png` bila ingin gambar maskot dikelola dari CMS.

6. `npm run build` di frontend, lalu deploy seperti biasa.

> Catatan: posisi 5 ikon nilai i5 di sekitar logo (`VALUE_LAYOUT`) dipetakan dari
> **judul** nilai (Integrity, Innovative, Integrated, Impressive, Involved). Judul bisa
> diganti di CMS, tetapi nilai dengan judul baru perlu ditambahkan juga ke `VALUE_LAYOUT`.
