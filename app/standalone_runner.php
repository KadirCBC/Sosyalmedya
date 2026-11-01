<?php

declare(strict_types=1);

/**
 * Meydan - Ultra-Professional Next-Gen Local Social Platform
 * Luxury Editorial Tech Aesthetic (Apple + Linear + Airbnb caliber)
 * Full-Width Responsive Widescreen Layout with True Visual Richness
 */

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

// API Uç Noktaları (JSON Desteği)
if (str_starts_with($uri, '/api/')) {
    header('Content-Type: application/json; charset=utf-8');

    if ($uri === '/api/v1/events/weekend') {
        echo json_encode([
            'success' => true,
            'events' => [
                [
                    'id' => 1,
                    'title' => 'Botanikte Sabah Koşusu & Komşu Kahvesi',
                    'category' => 'spor',
                    'venue_name' => 'Şelale Park Kafe',
                    'is_sponsored_venue' => true,
                    'image' => 'https://images.unsplash.com/photo-1517649763962-0c623266ddc0?auto=format&fit=crop&w=600&q=80',
                    'event_date' => date('Y-m-d 09:30:00', strtotime('next Saturday')),
                    'participants_count' => 18,
                ],
                [
                    'id' => 2,
                    'title' => 'Mahalle Kitap Kulübü & Söyleşi',
                    'category' => 'kultur_sanat',
                    'venue_name' => 'Keçiören Gençlik Kütüphanesi',
                    'is_sponsored_venue' => false,
                    'image' => 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=600&q=80',
                    'event_date' => date('Y-m-d 15:00:00', strtotime('next Sunday')),
                    'participants_count' => 24,
                ],
            ],
        ]);
        exit;
    }

    echo json_encode(['success' => true, 'timestamp' => time()]);
    exit;
}

// İnfografik Anket Kartı
if (preg_match('#^/polls/(\d+)/card#', $uri, $matches)) {
    header('Content-Type: image/png');
    $img = imagecreatetruecolor(1200, 630);
    $bg = imagecolorallocate($img, 15, 23, 42);
    $card = imagecolorallocate($img, 30, 41, 59);
    $white = imagecolorallocate($img, 255, 255, 255);
    $blue = imagecolorallocate($img, 59, 130, 246);
    $emerald = imagecolorallocate($img, 16, 185, 129);
    imagefilledrectangle($img, 0, 0, 1200, 630, $bg);
    imagefilledrectangle($img, 50, 40, 1150, 590, $card);
    imagestring($img, 5, 80, 70, "M E Y D A N   Y E R E L   A N K E T", $blue);
    imagestring($img, 5, 80, 140, "Kecioren'de bu hafta sonu en cok hangi etkinligi gormek istersiniz?", $white);
    imagefilledrectangle($img, 80, 220, 1120, 280, imagecolorallocate($img, 51, 65, 85));
    imagefilledrectangle($img, 80, 220, 690, 280, $emerald);
    imagestring($img, 5, 100, 242, "Acik Hava Sinemasi - %56.8 (58 Oy) [KAZANAN]", $white);
    imagestring($img, 4, 80, 550, "Toplam 102 mahalle oyu kullanildi  *  meydan.com'da oyla ->", $white);
    imagepng($img);
    imagedestroy($img);
    exit;
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="tr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Meydan - Yerel Mahalle ve Genel Akış Platformu</title>
    
    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'Inter', 'system-ui', 'sans-serif'],
                        mono: ['ui-monospace', 'SFMono-Regular', 'Menlo', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#f0f6fe',
                            100: '#e0edfd',
                            500: '#2563eb',
                            600: '#1d4ed8',
                            700: '#1e40af',
                        }
                    },
                    boxShadow: {
                        'subtle-card': '0 1px 3px 0 rgba(0, 0, 0, 0.04), 0 8px 24px -4px rgba(15, 23, 42, 0.05)',
                        'subtle-card-hover': '0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 16px 32px -6px rgba(15, 23, 42, 0.1)',
                        'glow-brand': '0 4px 20px -2px rgba(37, 99, 235, 0.3)',
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }

        /* ========================================================= */
        /* AYDINLIK MOD (Apple Studio Pearl / Clean Luxury)          */
        /* ========================================================= */
        body {
            background-color: #f8fafc;
            color: #0f172a;
            transition: background-color 0.25s ease, color 0.25s ease;
        }

        .luxury-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.03), 0 10px 25px -5px rgba(15, 23, 42, 0.04);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .luxury-card-hover:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 12px 0 rgba(0, 0, 0, 0.05), 0 16px 32px -4px rgba(15, 23, 42, 0.08);
            transform: translateY(-1.5px);
        }

        /* ========================================================= */
        /* KOYU MOD (Titanium Obsidian Noir)                         */
        /* ========================================================= */
        html.dark body {
            background-color: #0b0f19;
            color: #f8fafc;
        }

        html.dark .luxury-card {
            background: #111827;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.4), 0 12px 30px -6px rgba(0, 0, 0, 0.6);
        }

        html.dark .luxury-card-hover:hover {
            border-color: rgba(255, 255, 255, 0.16);
            box-shadow: 0 4px 16px 0 rgba(0, 0, 0, 0.6), 0 20px 40px -6px rgba(0, 0, 0, 0.8);
            transform: translateY(-1.5px);
        }

        html { scroll-behavior: smooth; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="min-h-screen flex flex-col font-sans"
      x-data="{
          darkMode: false,
          activeTab: 'local',
          composeTab: 'post',
          postText: '',
          district: 'Keçiören',
          city: 'Ankara',
          voted: false,
          selectedOpt: null,
          liked: false,
          likesCount: 52,
          saved: false,
          attendingEvent1: false,
          attendeesCount1: 18,
          attendingEvent2: false,
          attendeesCount2: 24,
          init() {
              const saved = localStorage.getItem('meydan_theme');
              if (saved) {
                  this.darkMode = saved === 'dark';
              } else {
                  this.darkMode = false;
              }
              this.applyTheme();
          },
          setTheme(dark) {
              this.darkMode = dark;
              localStorage.setItem('meydan_theme', this.darkMode ? 'dark' : 'light');
              this.applyTheme();
          },
          applyTheme() {
              if (this.darkMode) {
                  document.documentElement.classList.add('dark');
              } else {
                  document.documentElement.classList.remove('dark');
              }
          }
      }">

    <!-- ================================================================= -->
    <!-- 1. ÜST NAVİGASYON (Clean Apple Glass Top Bar)                     -->
    <!-- ================================================================= -->
    <header class="sticky top-0 z-50 w-full backdrop-blur-xl bg-white/90 dark:bg-[#0b0f19]/90 border-b border-slate-200/80 dark:border-white/[0.08] transition-colors">
        <div class="max-w-[1560px] mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
            
            <!-- Sol: Logo & İlçe Rozeti -->
            <div class="flex items-center gap-4 shrink-0">
                <a href="/" class="flex items-center gap-2.5 group">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-black shadow-glow-brand group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-xl font-black tracking-tight text-slate-900 dark:text-white">Meydan</span>
                            <span class="text-[10px] font-black px-1.5 py-0.5 rounded bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400">PRO</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-[11px] font-semibold text-slate-500 dark:text-slate-400 -mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span>Keçiören / Kalaba</span>
                        </div>
                    </div>
                </a>

                <!-- Lokasyon Seçici Dropdown Butonu -->
                <button class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-white/10 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-white/[0.04] transition-colors">
                    <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                    <span x-text="`${city} / ${district}`"></span>
                    <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
            </div>

            <!-- Orta: Arama Çubuğu -->
            <div class="hidden md:flex flex-1 max-w-xl mx-4">
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text"
                           placeholder="Mahallede esnaf, etkinlik, satılık/ücretsiz eşya veya konu ara..."
                           class="w-full pl-9 pr-12 py-2 rounded-xl text-xs bg-slate-100/80 dark:bg-slate-800/60 border border-transparent focus:border-blue-500 focus:bg-white dark:focus:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none transition-all">
                    <span class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-[10px] font-mono text-slate-400">⌘K</span>
                </div>
            </div>

            <!-- Sağ: Aydınlık/Koyu Switcher & Profil -->
            <div class="flex items-center gap-3">
                
                <!-- ŞIK TEMA SEÇİCİ (Segmented Switcher) -->
                <div class="p-1 rounded-xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-white/10 flex items-center gap-1">
                    <button @click="setTheme(false)"
                            type="button"
                            :class="!darkMode ? 'bg-white text-blue-600 shadow-sm font-bold' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400'"
                            class="px-2.5 py-1 rounded-lg text-xs flex items-center gap-1.5 transition-all">
                        <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span class="hidden sm:inline">Aydınlık</span>
                    </button>
                    <button @click="setTheme(true)"
                            type="button"
                            :class="darkMode ? 'bg-slate-700 text-blue-400 shadow-sm font-bold' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400'"
                            class="px-2.5 py-1 rounded-lg text-xs flex items-center gap-1.5 transition-all">
                        <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        <span class="hidden sm:inline">Koyu</span>
                    </button>
                </div>

                <!-- Yeni Gönderi Butonu -->
                <button @click="window.scrollTo({top: 0, behavior: 'smooth'});"
                        class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-sm transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Paylaş</span>
                </button>

                <!-- Gerçekçi Kullanıcı Avatarı -->
                <div class="relative cursor-pointer">
                    <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=120&q=80"
                         alt="Profil"
                         class="w-9 h-9 rounded-xl object-cover ring-2 ring-slate-200 dark:ring-white/10">
                    <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-emerald-500 rounded-full ring-2 ring-white dark:ring-slate-900"></span>
                </div>
            </div>
        </div>
    </header>

    <!-- ================================================================= -->
    <!-- 2. ANA İÇERİK: 3 KOLONLU GENİŞ VE DENGELİ GRID (max-w-[1560px])   -->
    <!-- ================================================================= -->
    <div class="max-w-[1560px] mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 flex-1">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- ========================================================= -->
            <!-- 2.1 SOL KOLON: Navigasyon & Komşu Kartı (lg:col-span-3)   -->
            <!-- ========================================================= -->
            <aside class="hidden lg:block lg:col-span-3 space-y-5 sticky top-22">
                
                <!-- Navigasyon Menüsü -->
                <div class="luxury-card rounded-2xl p-2.5 space-y-1">
                    <div class="px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                        Mahalle Hayatı
                    </div>

                    <a href="/"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all"
                       :class="activeTab === 'local' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Bölgem (Keçiören)</span>
                    </a>

                    <a href="/"
                       @click.prevent="activeTab = 'general'"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all"
                       :class="activeTab === 'general' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Genel Meydan</span>
                    </a>

                    <a href="/events"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                        <svg class="w-4 h-4 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Etkinlik Takvimi</span>
                        <span class="ml-auto text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400">Bu Hafta Sonu</span>
                    </a>

                    <a href="/marketplace"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                        <svg class="w-4 h-4 shrink-0 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <span>Mahalle Pazarı</span>
                        <span class="ml-auto text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400">Ücretsiz & İkinci El</span>
                    </a>

                    <a href="/business"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                        <svg class="w-4 h-4 shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span>Esnaf Rehberi</span>
                    </a>

                    <a href="/analytics/pulse"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                        <svg class="w-4 h-4 shrink-0 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span>Yerel Nabız & Analitik</span>
                    </a>

                    <a href="/emergency"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-all">
                        <svg class="w-4 h-4 shrink-0 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Acil Durum Ağı</span>
                    </a>
                </div>

                <!-- Komşu Pasaportu (Kullanıcı Profil Kartı) -->
                <div class="luxury-card rounded-2xl p-4 space-y-3.5">
                    <div class="flex items-center gap-3">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=150&q=80"
                             alt="Kadir CBC"
                             class="w-12 h-12 rounded-xl object-cover ring-2 ring-blue-500/20">
                        <div>
                            <div class="flex items-center gap-1.5">
                                <h4 class="text-sm font-bold text-slate-900 dark:text-white">Kadir Çelebi</h4>
                                <svg class="w-3.5 h-3.5 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            </div>
                            <span class="text-xs text-slate-500 dark:text-slate-400">@kadir.cbc • Kalaba Mah.</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 text-xs border border-slate-100 dark:border-white/5">
                        <div>
                            <span class="text-[10px] text-slate-400 block font-semibold uppercase">Yerel Unvan</span>
                            <span class="font-bold text-blue-600 dark:text-blue-400">Aktif Komşu</span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] text-slate-400 block font-semibold uppercase">İtibar Puanı</span>
                            <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">120 Puan</span>
                        </div>
                    </div>
                </div>

            </aside>

            <!-- ========================================================= -->
            <!-- 2.2 ORTA KOLON: Feed, Composer & Gönderiler (lg:col-span-6)-->
            <!-- ========================================================= -->
            <main class="col-span-12 lg:col-span-6 space-y-5">
                
                <!-- Akış Segment Kontrolü -->
                <div class="luxury-card rounded-2xl p-1 flex items-center gap-1">
                    <button @click="activeTab = 'local'"
                            class="flex-1 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all flex items-center justify-center gap-1.5"
                            :class="activeTab === 'local' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                        <span>Bölgem (Keçiören)</span>
                    </button>
                    <button @click="activeTab = 'general'"
                            class="flex-1 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all flex items-center justify-center gap-1.5"
                            :class="activeTab === 'general' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Genel Akış</span>
                    </button>
                </div>

                <!-- Zarif Paylaşım Kutusu (Clean Composer) -->
                <div class="luxury-card rounded-2xl p-4 sm:p-5 space-y-3.5">
                    <div class="flex items-start gap-3.5">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=120&q=80"
                             alt="Avatar"
                             class="w-10 h-10 rounded-xl object-cover ring-1 ring-slate-200 dark:ring-white/10 shrink-0">
                        <div class="flex-1 min-w-0">
                            <textarea x-model="postText"
                                      rows="3"
                                      placeholder="Keçiören'de ve mahallede neler oluyor? Bir soru sor, etkinlik duyur veya yerel gelişme paylaş..."
                                      class="w-full bg-transparent border-0 resize-none text-[15px] placeholder-slate-400 text-slate-900 dark:text-white focus:ring-0 p-0 outline-none leading-relaxed"></textarea>
                        </div>
                    </div>

                    <!-- Dinamik Anket Alanı -->
                    <div x-show="composeTab === 'poll'" x-cloak class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-white/10 space-y-2.5">
                        <div class="flex items-center justify-between text-xs font-bold text-blue-600 dark:text-blue-400">
                            <span>📊 Yerel Anket Seçenekleri</span>
                            <button @click="composeTab = 'post'" class="text-slate-400 hover:text-rose-500">İptal ✕</button>
                        </div>
                        <input type="text" placeholder="Seçenek 1 (Örn: Botanik Parkı Açık Hava Sineması)" class="w-full text-xs px-3 py-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 text-slate-900 dark:text-white focus:ring-1 focus:ring-blue-500 outline-none">
                        <input type="text" placeholder="Seçenek 2 (Örn: Şelale Parkı Kermesi)" class="w-full text-xs px-3 py-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 text-slate-900 dark:text-white focus:ring-1 focus:ring-blue-500 outline-none">
                    </div>

                    <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-white/5">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <button type="button"
                                    @click="composeTab = (composeTab === 'poll' ? 'post' : 'poll')"
                                    :class="composeTab === 'poll' ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 border-blue-200' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 border-slate-200 dark:border-white/10'"
                                    class="px-2.5 py-1.5 rounded-lg border text-xs font-semibold transition-all flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                <span>Anket</span>
                            </button>

                            <button type="button"
                                    onclick="alert('Görsel seçici hazır.');"
                                    class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/10 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold transition-all flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>Fotoğraf</span>
                            </button>

                            <a href="/events"
                               class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/10 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold transition-all flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>Etkinlik</span>
                            </a>
                        </div>

                        <button @click="if(postText.trim()){ alert('Gönderiniz başarıyla paylaşıldı! 🚀'); postText=''; } else { alert('Lütfen paylaşmak için bir şeyler yazın.'); }"
                                class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm transition-colors">
                            Yayınla
                        </button>
                    </div>
                </div>

                <!-- 🌟 GÖRSEL ZENGİNLİKTE ETKİNLİK KARTLARI (Weekend Events Spotlight) -->
                <div class="luxury-card rounded-2xl p-4 sm:p-5 space-y-3.5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                            <h3 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                                Bu Hafta Sonu Mahallede Ne Var?
                            </h3>
                        </div>
                        <a href="/events" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">
                            Tümünü Gör →
                        </a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <!-- Etkinlik 1 (Görselli Kart) -->
                        <div class="rounded-xl overflow-hidden border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-800/40 hover:border-amber-500/50 transition-all flex flex-col group">
                            <div class="h-28 w-full relative overflow-hidden">
                                <img src="https://images.unsplash.com/photo-1517649763962-0c623266ddc0?auto=format&fit=crop&w=600&q=80"
                                     alt="Koşu"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                                <span class="absolute top-2 left-2 text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-500 text-white">
                                    Spor & Koşu
                                </span>
                                <span class="absolute bottom-2 left-2 text-[11px] font-bold text-white flex items-center gap-1">
                                    <span>📅 Cumartesi • 09:30</span>
                                </span>
                            </div>
                            <div class="p-3 flex-1 flex flex-col justify-between space-y-2">
                                <div>
                                    <h4 class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-blue-600 transition-colors">
                                        Botanikte Sabah Koşusu & Kahve
                                    </h4>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">📍 Şelale Park Kafe</p>
                                </div>
                                <div class="pt-2 border-t border-slate-200/80 dark:border-white/5 flex items-center justify-between text-xs">
                                    <span class="text-slate-500 text-[11px]" x-text="`${attendeesCount1} komşu katılıyor`"></span>
                                    <button @click="attendingEvent1 = !attendingEvent1; attendeesCount1 += attendingEvent1 ? 1 : -1"
                                            :class="attendingEvent1 ? 'bg-emerald-600 text-white' : 'text-blue-600 dark:text-blue-400 font-bold hover:underline'"
                                            class="text-xs">
                                        <span x-text="attendingEvent1 ? '✓ Katılıyorsun' : 'Katıl →'"></span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Etkinlik 2 (Görselli Kart) -->
                        <div class="rounded-xl overflow-hidden border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-800/40 hover:border-purple-500/50 transition-all flex flex-col group">
                            <div class="h-28 w-full relative overflow-hidden">
                                <img src="https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=600&q=80"
                                     alt="Kitap"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                                <span class="absolute top-2 left-2 text-[10px] font-bold px-2 py-0.5 rounded bg-purple-600 text-white">
                                    Kültür & Kitap
                                </span>
                                <span class="absolute bottom-2 left-2 text-[11px] font-bold text-white flex items-center gap-1">
                                    <span>📅 Pazar • 15:00</span>
                                </span>
                            </div>
                            <div class="p-3 flex-1 flex flex-col justify-between space-y-2">
                                <div>
                                    <h4 class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-blue-600 transition-colors">
                                        Mahalle Kitap Kulübü Söyleşisi
                                    </h4>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">📍 Keçiören Kütüphanesi</p>
                                </div>
                                <div class="pt-2 border-t border-slate-200/80 dark:border-white/5 flex items-center justify-between text-xs">
                                    <span class="text-slate-500 text-[11px]" x-text="`${attendeesCount2} komşu katılıyor`"></span>
                                    <button @click="attendingEvent2 = !attendingEvent2; attendeesCount2 += attendingEvent2 ? 1 : -1"
                                            :class="attendingEvent2 ? 'bg-emerald-600 text-white' : 'text-blue-600 dark:text-blue-400 font-bold hover:underline'"
                                            class="text-xs">
                                        <span x-text="attendingEvent2 ? '✓ Katılıyorsun' : 'Katıl →'"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- GÖNDERİ 1: YEREL ANKETLİ GÖNDERİ (Interactive Poll) -->
                <article class="luxury-card luxury-card-hover rounded-2xl p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=120&q=80"
                                 alt="Ahmet Yılmaz"
                                 class="w-10 h-10 rounded-xl object-cover ring-1 ring-slate-200 dark:ring-white/10">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-sm text-slate-900 dark:text-white">Ahmet Yılmaz</span>
                                    <span class="text-xs text-slate-400">@ahmetyilmaz</span>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 font-bold">
                                        Mahalle Sakini
                                    </span>
                                </div>
                                <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1.5">
                                    <span>15 dakika önce</span>
                                    <span>•</span>
                                    <span class="text-blue-600 dark:text-blue-400 font-medium">📍 Keçiören / Kalaba</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="text-sm leading-relaxed text-slate-700 dark:text-slate-200">
                        Komşular günaydın! Bu hafta sonu Keçiören'de hep birlikte güzel bir açık hava etkinliği planlamak istiyoruz. Aşağıdaki ankette oyunuzu kullanarak mahalle kararına doğrudan katılalım!
                    </p>

                    <!-- İnteraktif Anket Kutusu -->
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-white/10 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold px-2.5 py-0.5 rounded bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300">
                                📊 Yerel Anket
                            </span>
                            <a href="/polls/1/card" target="_blank"
                               class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                                <span>İnfografik Kartı</span> ↗
                            </a>
                        </div>

                        <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">
                            Keçiören'de bu hafta sonu en çok hangi etkinliği görmek istersiniz?
                        </h4>

                        <!-- Seçenekler -->
                        <div class="space-y-2 text-xs">
                            <template x-if="!voted">
                                <div class="space-y-2">
                                    <button @click="voted = true; selectedOpt = 1"
                                            class="w-full text-left p-3 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 hover:border-blue-500 transition-all flex items-center justify-between font-medium text-slate-800 dark:text-slate-200 shadow-2xs">
                                        <span>🎬 Açık Hava Sineması & Çay İkramı</span>
                                        <span class="text-blue-600 dark:text-blue-400 font-bold">Oy Ver →</span>
                                    </button>
                                    <button @click="voted = true; selectedOpt = 2"
                                            class="w-full text-left p-3 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 hover:border-blue-500 transition-all flex items-center justify-between font-medium text-slate-800 dark:text-slate-200 shadow-2xs">
                                        <span>🌿 Botanik Parkı Doğa Yürüyüşü</span>
                                        <span class="text-blue-600 dark:text-blue-400 font-bold">Oy Ver →</span>
                                    </button>
                                    <button @click="voted = true; selectedOpt = 3"
                                            class="w-full text-left p-3 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 hover:border-blue-500 transition-all flex items-center justify-between font-medium text-slate-800 dark:text-slate-200 shadow-2xs">
                                        <span>☕ Yerel Esnaf Kermesi & Sohbet</span>
                                        <span class="text-blue-600 dark:text-blue-400 font-bold">Oy Ver →</span>
                                    </button>
                                </div>
                            </template>

                            <template x-if="voted">
                                <div class="space-y-2">
                                    <div class="relative overflow-hidden rounded-lg border border-blue-500 p-3 bg-blue-50/60 dark:bg-blue-950/40">
                                        <div class="absolute inset-y-0 left-0 bg-blue-500/20 dark:bg-blue-500/30 rounded-l-lg transition-all duration-500" style="width: 56.8%"></div>
                                        <div class="relative z-10 flex items-center justify-between font-bold text-slate-900 dark:text-white">
                                            <span class="flex items-center gap-1.5">
                                                <span>🎬 Açık Hava Sineması & Çay İkramı</span>
                                                <span class="text-[9px] bg-blue-600 px-1.5 py-0.2 rounded text-white font-bold">✓ Oyunuz</span>
                                            </span>
                                            <span class="font-mono text-blue-600 dark:text-blue-300">%56.8 (58 Oy)</span>
                                        </div>
                                    </div>
                                    <div class="relative overflow-hidden rounded-lg border border-slate-200 dark:border-white/10 p-3 bg-white dark:bg-slate-900">
                                        <div class="absolute inset-y-0 left-0 bg-slate-200/50 dark:bg-white/10 rounded-l-lg transition-all duration-500" style="width: 27.5%"></div>
                                        <div class="relative z-10 flex items-center justify-between font-bold text-slate-700 dark:text-slate-300">
                                            <span>🌿 Botanik Parkı Doğa Yürüyüşü</span>
                                            <span class="font-mono text-slate-400">%27.5 (28 Oy)</span>
                                        </div>
                                    </div>
                                    <div class="relative overflow-hidden rounded-lg border border-slate-200 dark:border-white/10 p-3 bg-white dark:bg-slate-900">
                                        <div class="absolute inset-y-0 left-0 bg-slate-200/50 dark:bg-white/10 rounded-l-lg transition-all duration-500" style="width: 15.7%"></div>
                                        <div class="relative z-10 flex items-center justify-between font-bold text-slate-700 dark:text-slate-300">
                                            <span>☕ Yerel Esnaf Kermesi & Sohbet</span>
                                            <span class="font-mono text-slate-400">%15.7 (16 Oy)</span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="pt-2 border-t border-slate-200 dark:border-white/5 flex items-center justify-between text-[11px] text-slate-400">
                            <span>102 mahalle oyu kullanıldı • 2 gün kaldı</span>
                            <div class="flex items-center gap-3 font-semibold text-blue-600 dark:text-blue-400">
                                <a href="https://twitter.com/intent/tweet?text=Kecioren+Anketi" target="_blank" class="hover:underline">X'te Paylaş</a>
                                <a href="https://api.whatsapp.com/send?text=Kecioren+Anketi" target="_blank" class="hover:underline text-emerald-600 dark:text-emerald-400">WhatsApp</a>
                            </div>
                        </div>
                    </div>

                    <!-- Aksiyon Barı -->
                    <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-white/5 text-xs text-slate-500">
                        <button @click="liked = !liked; likesCount += liked ? 1 : -1"
                                class="flex items-center gap-1.5 transition-colors"
                                :class="liked ? 'text-rose-600 font-bold' : 'hover:text-rose-600'">
                            <svg class="w-4 h-4" :class="liked ? 'fill-current text-rose-600' : 'text-slate-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            <span x-text="likesCount"></span> Beğeni
                        </button>
                        <button class="flex items-center gap-1.5 hover:text-blue-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            <span>14 Yorum</span>
                        </button>
                        <button onclick="navigator.clipboard.writeText(window.location.origin); alert('Bağlantı kopyalandı! 🔗');"
                                class="flex items-center gap-1.5 hover:text-emerald-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                            <span>Paylaş</span>
                        </button>
                    </div>
                </article>

                <!-- GÖNDERİ 2: DOĞRULANMIŞ ESNAF VİTRİNİ & ÜRÜN (Visual Showcase) -->
                <article class="luxury-card luxury-card-hover rounded-2xl p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white font-bold flex items-center justify-center text-sm shadow-sm">
                                D
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-sm text-slate-900 dark:text-white">Demir Botanik & Çiçek</span>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        Doğrulanmış Esnaf
                                    </span>
                                </div>
                                <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1.5">
                                    <span>45 dakika önce</span>
                                    <span>•</span>
                                    <span>Tepebaşı Mah.</span>
                                </div>
                            </div>
                        </div>

                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300">
                            %30 Mahalle İndirimi
                        </span>
                    </div>

                    <p class="text-sm leading-relaxed text-slate-700 dark:text-slate-200">
                        Komşularımıza özel askıda çiçek ve iç mekan saksı bitkilerinde bu hafta sonuna özel %30 mahalle indirimi başladı! Mahallemizin yeşilini hep birlikte çoğaltalım. 🪴
                    </p>

                    <!-- Ürün Görsel Kartı -->
                    <div class="rounded-xl overflow-hidden border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-800/40">
                        <div class="h-44 sm:h-52 w-full relative">
                            <img src="https://images.unsplash.com/photo-1614594975525-e45190c55d0b?auto=format&fit=crop&w=800&q=80"
                                 alt="Bitki"
                                 class="w-full h-full object-cover">
                            <div class="absolute bottom-3 left-3 bg-slate-900/80 backdrop-blur-md text-white text-xs font-bold px-3 py-1 rounded-lg">
                                ₺120 <span class="text-slate-300 line-through text-[10px] font-normal ml-1">₺180</span>
                            </div>
                        </div>
                        <div class="p-3.5 flex items-center justify-between">
                            <div>
                                <h4 class="text-xs font-bold text-slate-900 dark:text-white">İç Mekan Paşa Kılıcı & Monstera</h4>
                                <p class="text-[11px] text-slate-500">Askıda Paylaşım & Mahalleliye Teslimat</p>
                            </div>
                            <a href="https://api.whatsapp.com/send?phone=905550000000&text=Demir+Botanik+Siparis" target="_blank"
                               class="px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold flex items-center gap-1.5 shadow-sm transition-colors">
                                <span>WhatsApp Sipariş</span> ↗
                            </a>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-white/5 text-xs text-slate-500">
                        <button class="flex items-center gap-1.5 hover:text-rose-600 transition-colors">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            <span>28 Beğeni</span>
                        </button>
                        <button class="flex items-center gap-1.5 hover:text-blue-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            <span>6 Yorum</span>
                        </button>
                        <a href="/marketplace" class="flex items-center gap-1.5 hover:text-emerald-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            <span>Pazarda Gör</span>
                        </a>
                    </div>
                </article>

            </main>

            <!-- ========================================================= -->
            <!-- 2.3 SAĞ KOLON: Nabız, Liderlik & Esnaf (lg:col-span-3)    -->
            <!-- ========================================================= -->
            <aside class="hidden lg:block lg:col-span-3 space-y-5 sticky top-22">

                <!-- Keçiören Gündem Nabzı -->
                <div class="luxury-card rounded-2xl p-4 sm:p-5 space-y-3.5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                                Keçiören Gündem Nabzı
                            </h3>
                        </div>
                        <a href="/analytics/pulse" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">Detay ↗</a>
                    </div>

                    <!-- Memnuniyet Barı -->
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-white/5 space-y-2">
                        <div class="flex items-center justify-between text-xs font-bold">
                            <span class="text-slate-500">Genel Memnuniyet</span>
                            <span class="text-emerald-600 dark:text-emerald-400">%68.4 Olumlu</span>
                        </div>
                        <div class="w-full bg-slate-200 dark:bg-white/10 h-2 rounded-full overflow-hidden flex">
                            <div class="bg-emerald-500 h-full" style="width: 68.4%"></div>
                            <div class="bg-rose-500 h-full" style="width: 31.6%"></div>
                        </div>
                        <div class="flex justify-between text-[10px] text-slate-400 font-medium">
                            <span>%68.4 Memnun</span>
                            <span>%31.6 Şikayet</span>
                        </div>
                    </div>

                    <!-- Trend Etiketler -->
                    <div class="space-y-1.5 pt-1">
                        <span class="text-[11px] font-bold text-slate-400 block uppercase">En Çok Konuşulanlar</span>
                        <div class="flex flex-wrap gap-1.5">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 cursor-pointer transition-colors">
                                #BotanikParkı
                            </span>
                            <span class="px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 cursor-pointer transition-colors">
                                #SuKesintisi
                            </span>
                            <span class="px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 cursor-pointer transition-colors">
                                #KeçiörenPazarı
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Haftanın Komşuları (Gerçekçi Avatarlar) -->
                <div class="luxury-card rounded-2xl p-4 sm:p-5 space-y-3.5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 1.738 5.42a1 1 0 01-.285 1.05A3.989 3.989 0 0115 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.715-5.349L11 6.477V16h2a1 1 0 110 2H7a1 1 0 110-2h2V6.477L6.237 7.582l1.715 5.349a1 1 0 01-.285 1.05A3.989 3.989 0 015 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.738-5.42-1.233-.617a1 1 0 01.894-1.788l1.599.799L9 4.323V3a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                                Haftanın Komşuları
                            </h3>
                        </div>
                        <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">Top 3</span>
                    </div>

                    <div class="space-y-2">
                        <!-- 1. Dr. Mehmet Kaya -->
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-white/5 flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2.5">
                                <span class="font-bold text-amber-500 font-mono text-sm">1.</span>
                                <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=100&q=80"
                                     alt="Dr. Mehmet"
                                     class="w-8 h-8 rounded-lg object-cover ring-1 ring-amber-500/30">
                                <div>
                                    <span class="font-bold text-slate-900 dark:text-white block">Dr. Mehmet Kaya</span>
                                    <span class="text-[10px] text-slate-400">Gönüllü Muhtar</span>
                                </div>
                            </div>
                            <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">+580p</span>
                        </div>

                        <!-- 2. Esnaf Serkan Usta -->
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-white/5 flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2.5">
                                <span class="font-bold text-slate-400 font-mono text-sm">2.</span>
                                <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=100&q=80"
                                     alt="Serkan Usta"
                                     class="w-8 h-8 rounded-lg object-cover">
                                <div>
                                    <span class="font-bold text-slate-900 dark:text-white block">Serkan Usta</span>
                                    <span class="text-[10px] text-slate-400">Mahalle Esnafı</span>
                                </div>
                            </div>
                            <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">+420p</span>
                        </div>

                        <!-- 3. Ayşe Öğretmen -->
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-white/5 flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2.5">
                                <span class="font-bold text-amber-700 dark:text-amber-500 font-mono text-sm">3.</span>
                                <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=100&q=80"
                                     alt="Ayşe Öğretmen"
                                     class="w-8 h-8 rounded-lg object-cover">
                                <div>
                                    <span class="font-bold text-slate-900 dark:text-white block">Ayşe Öğretmen</span>
                                    <span class="text-[10px] text-slate-400">Mahalle Sakini</span>
                                </div>
                            </div>
                            <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">+310p</span>
                        </div>
                    </div>
                </div>

                <!-- Öne Çıkan Esnaf (Gerçek Görselli) -->
                <div class="luxury-card rounded-2xl p-4 sm:p-5 space-y-3.5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                                Öne Çıkan Esnaf
                            </h3>
                        </div>
                        <a href="/business" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">Tümü ↗</a>
                    </div>

                    <div class="rounded-xl overflow-hidden border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-800/40">
                        <div class="h-28 w-full relative">
                            <img src="https://images.unsplash.com/photo-1554118811-1e0d58224f24?auto=format&fit=crop&w=600&q=80"
                                 alt="Kafe"
                                 class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent"></div>
                            <span class="absolute bottom-2 left-2 text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-500 text-white">
                                🟢 Şu An Açık
                            </span>
                        </div>
                        <div class="p-3 space-y-2">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold text-slate-900 dark:text-white">Şelale Park Kafe & Bistro</h4>
                                <span class="text-xs font-bold text-amber-500">★ 4.9</span>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Hafta sonu komşu kahvesi buluşmalarının resmi sponsoru.</p>
                            <a href="https://api.whatsapp.com/send?phone=905550000000&text=Selale+Kafe" target="_blank"
                               class="block w-full text-center py-2 rounded-lg bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-xs font-bold transition-opacity hover:opacity-90">
                                WhatsApp ile İletişim ↗
                            </a>
                        </div>
                    </div>
                </div>

            </aside>

        </div>
    </div>

    <!-- Mobil Gezinme Dock'u -->
    <nav class="lg:hidden fixed bottom-0 inset-x-0 z-50 backdrop-blur-xl bg-white/95 dark:bg-[#0b0f19]/95 border-t border-slate-200 dark:border-white/10 px-4 py-2.5 flex items-center justify-around shadow-lg">
        <a href="/" class="flex flex-col items-center gap-1 text-xs font-bold text-blue-600 dark:text-blue-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span class="text-[10px]">Akış</span>
        </a>
        <a href="/events" class="flex flex-col items-center gap-1 text-xs font-medium text-slate-400 hover:text-slate-900 dark:hover:text-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span class="text-[10px]">Etkinlik</span>
        </a>
        <button @click="window.scrollTo({top: 0, behavior: 'smooth'})"
                class="w-11 h-11 -mt-4 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-xl shadow-glow-brand">
            +
        </button>
        <a href="/marketplace" class="flex flex-col items-center gap-1 text-xs font-medium text-slate-400 hover:text-slate-900 dark:hover:text-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            <span class="text-[10px]">Pazar</span>
        </a>
        <a href="/analytics/pulse" class="flex flex-col items-center gap-1 text-xs font-medium text-slate-400 hover:text-slate-900 dark:hover:text-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <span class="text-[10px]">Nabız</span>
        </a>
    </nav>

</body>
</html>
