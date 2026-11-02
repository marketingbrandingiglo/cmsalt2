"use client";

// Konten halaman Beranda dari IGLO CMS (lewat /api/cms/home), digabung ke
// objek locale `t` dari lib/content.js. Kunci yang dikelola CMS: hero,
// intro, productsSection, products, news.hero, news.filters, news.articles.
// Selama CMS belum merespons — atau bila CMS tidak bisa dihubungi — hook ini
// mengembalikan `t` statis, jadi halaman tidak pernah kosong.
//
// Pemakaian: di komponen Beranda ganti `const { t } = useLanguage();`
// menjadi `const t = useHomeContent();`.

import { useEffect, useState } from "react";
import { useLanguage } from "@/components/LanguageContext";

// Komponen yang mount bersamaan (HeroSlider, IntroScene, NewsMosaic, …)
// berbagi satu request yang sedang berjalan; setiap kali Beranda dibuka lagi
// data diambil ulang dari CMS supaya perubahan terbaru selalu tampil.
let inflight = null;

function loadHome() {
  inflight ??= fetch("/api/cms/home", { cache: "no-store", headers: { Accept: "application/json" } })
    .then((res) => (res.ok ? res.json() : null))
    .then((data) => (data?.id?.intro && data?.en?.intro ? data : null))
    .catch(() => null)
    .finally(() => {
      inflight = null;
    });
  return inflight;
}

// Daftar kosong dari CMS (mis. semua slide disembunyikan) jatuh ke konten
// statis, karena HeroSlider & NewsMosaic butuh minimal satu item.
const pickList = (cmsList, fallback) => (Array.isArray(cmsList) && cmsList.length ? cmsList : fallback);

export function useHomeContent() {
  const { t, lang } = useLanguage();
  const [cms, setCms] = useState(null);

  useEffect(() => {
    let alive = true;
    loadHome().then((data) => alive && data && setCms(data));
    return () => {
      alive = false;
    };
  }, []);

  const home = cms?.[lang];
  if (!home) return t;

  return {
    ...t,
    hero: pickList(home.hero, t.hero),
    intro: { ...t.intro, ...home.intro },
    productsSection: { ...t.productsSection, ...home.productsSection },
    products: pickList(home.products, t.products),
    news: {
      ...t.news,
      hero: { ...t.news.hero, ...home.news?.hero },
      filters: { ...t.news.filters, ...home.news?.filters },
      articles: pickList(home.news?.articles, t.news.articles),
    },
  };
}
