<!DOCTYPE html>
<html lang="tr" class="h-full bg-slate-50 dark:bg-black text-slate-900 dark:text-slate-100 antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Sosyal Medya - Yerel ve Genel Çift Akış' }}</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (cPanel CDN - Derleme/Build gerektirmez) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        },
                        dark: {
                            base: '#000000',
                            card: '#0c0f14',
                            border: '#1e2430',
                            hover: '#141a24'
                        }
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
<body class="h-full flex flex-col font-sans" x-data="{ darkMode: true }" :class="{ 'dark': darkMode }">

    <!-- Ana Çerçeve (Mobile-First / Max-w-2xl merkezli) -->
    <div class="min-h-full flex justify-center bg-slate-100 dark:bg-black">
        <main class="w-full max-w-[620px] min-h-screen bg-white dark:bg-black border-x border-slate-200 dark:border-neutral-800 flex flex-col pb-20 sm:pb-8 shadow-sm">
            {{ $slot }}
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

</body>
</html>

