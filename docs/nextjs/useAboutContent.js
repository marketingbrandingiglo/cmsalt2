"use client";

// Salin file ini ke frontend: components/useAboutContent.js
//
// Mengambil konten halaman About dari IGLO CMS (GET /api/about) dan
// mengembalikan objek dengan bentuk yang sama seperti `t.about` di
// lib/content.js. Selama CMS belum merespons, atau bila
// NEXT_PUBLIC_CMS_URL tidak di-set / CMS tidak bisa dihubungi, hook ini
// mengembalikan `t.about` statis — jadi halaman tidak pernah kosong.

import { useEffect, useState } from "react";
import { useLanguage } from "@/components/LanguageContext";

const CMS_URL = (process.env.NEXT_PUBLIC_CMS_URL || "").replace(/\/$/, "");

// Satu request dipakai bersama oleh semua komponen (page + ClientTabs).
let request = null;

function loadAbout() {
  if (!CMS_URL) return Promise.resolve(null);
  request ??= fetch(`${CMS_URL}/api/about`, { headers: { Accept: "application/json" } })
    .then((res) => (res.ok ? res.json() : null))
    .catch(() => null);
  return request;
}

export function useAboutContent() {
  const { t, lang } = useLanguage();
  const [cms, setCms] = useState(null);

  useEffect(() => {
    let alive = true;
    loadAbout().then((data) => alive && setCms(data));
    return () => {
      alive = false;
    };
  }, []);

  return cms?.[lang] ?? t.about;
}
