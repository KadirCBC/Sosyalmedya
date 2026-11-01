<!DOCTYPE html>
<html lang="tr" class="h-full bg-slate-50 dark:bg-black text-slate-900 dark:text-slate-100 antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Meydan - Yerel ve Genel Çift Akışlı Sosyal Ağ' }}</title>

    <!-- Dinamik OpenGraph (WhatsApp, Facebook, Telegram) Meta Etiketleri -->
    <meta property="og:site_name" content="Meydan">
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:title" content="{{ $ogTitle ?? ($title ?? 'Meydan - Yerel ve Genel Çift Akışlı Sosyal Ağ') }}">
    <meta property="og:description" content="{{ $ogDescription ?? 'Mahallende ve genel akışta neler olup bittiğini anında öğren ve paylaş.' }}">
    <meta property="og:image" content="{{ $ogImage ?? 'https://pub-your-id.r2.dev/assets/meydan-og-banner.jpg' }}">
    <meta property="og:url" content="{{ $ogUrl ?? url()->current() }}">

    <!-- PWA & Mobil Kurulum Meta Etiketleri -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#2563eb">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Meydan">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.svg">

    <!-- Dinamik Twitter Card (X Paylaşım Önizlemesi) -->
    <meta name="twitter:card" content="{{ $twitterCard ?? 'summary_large_image' }}">
    <meta name="twitter:title" content="{{ $ogTitle ?? ($title ?? 'Meydan') }}">
    <meta name="twitter:description" content="{{ $ogDescription ?? 'Mahallende ve genel akışta neler olup bittiğini anında öğren.' }}">
    <meta name="twitter:image" content="{{ $ogImage ?? 'https://pub-your-id.r2.dev/assets/meydan-og-banner.jpg' }}">

    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (cPanel CDN - Derleme/Build gerektirmez) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'Inter', 'sans-serif'],
                    },
                    boxShadow: {
                        'glow-primary': '0 4px 24px -2px rgba(37, 99, 235, 0.35)',
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js + Intersect Plugin (Sonsuz Kaydırma / Infinite Scroll için) -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }

        body {
            background-color: #f8fafc;
            background-image: 
                radial-gradient(circle at 15% 15%, rgba(59, 130, 246, 0.06) 0%, transparent 40%),
                radial-gradient(circle at 85% 20%, rgba(99, 102, 241, 0.05) 0%, transparent 45%),
                radial-gradient(circle at 50% 90%, rgba(16, 185, 129, 0.04) 0%, transparent 50%);
            background-attachment: fixed;
            color: #0f172a;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        html.dark body {
            background-color: #080c14;
            background-image: 
                radial-gradient(circle at 10% 15%, rgba(37, 99, 235, 0.12) 0%, transparent 45%),
                radial-gradient(circle at 90% 25%, rgba(99, 102, 241, 0.10) 0%, transparent 50%),
                radial-gradient(circle at 50% 85%, rgba(16, 185, 129, 0.06) 0%, transparent 55%);
            background-attachment: fixed;
            color: #f8fafc;
        }

        .elite-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05), inset 0 1px 0 0 rgba(255, 255, 255, 0.9);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        html.dark .elite-card {
            background: rgba(15, 22, 36, 0.75);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.6), inset 0 1px 0 0 rgba(255, 255, 255, 0.1);
        }

        .elite-card-hover:hover {
            transform: translateY(-2px);
            border-color: rgba(59, 130, 246, 0.35);
            box-shadow: 0 14px 35px -8px rgba(37, 99, 235, 0.12);
        }

        html.dark .elite-card-hover:hover {
            border-color: rgba(255, 255, 255, 0.18);
            box-shadow: 0 24px 50px -15px rgba(0, 0, 0, 0.8), 0 0 35px -5px rgba(37, 99, 235, 0.2);
        }

        /* Mobil yumuşak kaydırma ve no-scrollbar */
        html { scroll-behavior: smooth; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        /* Kalp tıklama animasyonu */
        @keyframes heartPop {
            0% { transform: scale(1); }
            50% { transform: scale(1.35); }
            100% { transform: scale(1); }
        }
        .animate-heart { animation: heartPop 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
    </style>
</head>
<body class="min-h-screen flex flex-col font-sans"
      x-data="{
          darkMode: false,
          init() {
              const saved = localStorage.getItem('meydan_theme');
              if (saved) {
                  this.darkMode = saved === 'dark';
              } else {
                  this.darkMode = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
              }
              this.applyTheme();
          },
          setTheme(dark) {
              this.darkMode = dark;
              localStorage.setItem('meydan_theme', this.darkMode ? 'dark' : 'light');
              this.applyTheme();
          },
          toggleTheme() {
              this.setTheme(!this.darkMode);
          },
          applyTheme() {
              if (this.darkMode) {
                  document.documentElement.classList.add('dark');
              } else {
                  document.documentElement.classList.remove('dark');
              }
          }
      }">

    <!-- Üst İnce Parlak Ambient Çizgi -->
    <div class="h-1 bg-gradient-to-r from-blue-600 via-indigo-600 to-emerald-500 w-full sticky top-0 z-50"></div>

    <!-- Ana Çerçeve (Full-Width Responsive Container) -->
    <div class="min-h-screen flex-1 flex flex-col w-full">
        <main class="w-full max-w-[1640px] mx-auto px-4 sm:px-6 lg:px-10 flex-1 flex flex-col pb-24 lg:pb-8">
            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </div>

    <!-- Bildirim / Toast Bileşeni -->
    <div x-data="{ show: false, message: '' }"
         @toast.window="message = $event.detail; show = true; setTimeout(() => show = false, 3000)"
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         x-cloak
         class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 px-4 py-2.5 rounded-full bg-slate-900/95 dark:bg-blue-600 text-white text-sm font-medium shadow-xl flex items-center gap-2 backdrop-blur-md">
        <svg class="w-4 h-4 text-emerald-400 dark:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span x-text="message"></span>
    </div>

    <!-- PWA Tek Tıkla Yükleme Barı (Install Prompt) -->
    <div x-data="{
             deferredPrompt: null,
             showInstallBanner: false,
             init() {
                 window.addEventListener('beforeinstallprompt', (e) => {
                     e.preventDefault();
                     this.deferredPrompt = e;
                     // Daha önce kapatılmadıysa göster
                     if (!localStorage.getItem('pwa_prompt_dismissed')) {
                         this.showInstallBanner = true;
                     }
                 });
             },
             async installPwa() {
                 if (!this.deferredPrompt) return;
                 this.deferredPrompt.prompt();
                 const { outcome } = await this.deferredPrompt.userChoice;
                 if (outcome === 'accepted') {
                     this.showInstallBanner = false;
                 }
                 this.deferredPrompt = null;
             },
             dismissBanner() {
                 this.showInstallBanner = false;
                 localStorage.setItem('pwa_prompt_dismissed', 'true');
             }
         }"
         x-show="showInstallBanner"
         x-cloak
         class="fixed bottom-0 inset-x-0 z-50 p-3 bg-slate-900/95 dark:bg-neutral-900/95 border-t border-slate-800 text-white shadow-2xl backdrop-blur-md">
        <div class="max-w-md mx-auto flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <img src="/icons/icon-192x192.svg" class="w-10 h-10 rounded-xl shadow-md">
                <div>
                    <h4 class="text-xs font-bold leading-tight">Meydan'ı Telefona Yükle</h4>
                    <p class="text-[11px] text-slate-400">Uygulama mağazası olmadan tek tıkla ana ekrana ekle.</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button @click="dismissBanner()" class="text-slate-400 hover:text-white text-xs px-2 py-1">Sonra</button>
                <button @click="installPwa()" class="px-3.5 py-1.5 rounded-full bg-blue-600 hover:bg-blue-500 font-bold text-xs shadow-md transition-colors">
                    Yükle
                </button>
            </div>
        </div>
    </div>

    <!-- PWA Service Worker & WebPush Kayıt Betiği -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', async () => {
                try {
                    const registration = await navigator.serviceWorker.register('/sw.js');
                    console.log('Meydan PWA Service Worker aktif:', registration.scope);

                    // WebPush Abonelik Kontrolü
                    if ('PushManager' in window && 'Notification' in window && Notification.permission === 'granted') {
                        syncPushSubscription(registration);
                    }
                } catch (err) {
                    console.warn('Service Worker kaydı yapılamadı:', err);
                }
            });
        }

        // VAPID WebPush Aboneliğini Backend'e Eşitle
        async function syncPushSubscription(registration) {
            try {
                const res = await fetch('/api/v1/push/vapid-public-key');
                if (!res.ok) return;
                const { public_key } = await res.json();
                if (!public_key) return;

                const subscription = await registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array(public_key)
                });

                await fetch('/api/v1/push/subscribe', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify(subscription)
                });
            } catch (e) {
                console.log('WebPush eşitleme:', e);
            }
        }

        function urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);
            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        }
    </script>
</body>
</html>

