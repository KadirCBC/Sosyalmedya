<!DOCTYPE html>
<html lang="tr" class="h-full bg-slate-900 text-slate-100 antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Yönetim & Moderasyon Paneli' }} - Meydan</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (cPanel CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="h-full flex flex-col font-sans bg-slate-950 text-slate-100">

    <div class="min-h-full flex flex-col md:flex-row">
        <!-- Sol Kenar Menüsü (Sidebar) -->
        <aside class="w-full md:w-64 bg-slate-900 border-b md:border-b-0 md:border-r border-slate-800 shrink-0 flex flex-col">
            <div class="p-5 border-b border-slate-800 flex items-center justify-between">
                <div>
                    <h1 class="text-lg font-black tracking-tight text-white flex items-center gap-2">
                        <span class="text-blue-500">🛡️</span> Meydan Panel
                    </h1>
                    <p class="text-xs text-slate-400 mt-0.5">Yönetim & Denetim</p>
                </div>
            </div>

            <!-- Kullanıcı Rol Bilgisi -->
            <div class="px-5 py-4 border-b border-slate-800/80 bg-slate-900/60">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-blue-600/20 text-blue-400 font-bold flex items-center justify-center text-sm border border-blue-500/30">
                        {{ mb_substr(auth()->user()->name ?? 'Y', 0, 1) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold text-white truncate">{{ auth()->user()->name ?? 'Yönetici' }}</p>
                        <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[10px] font-semibold tracking-wide uppercase {{ (auth()->user()->role->value ?? '') === 'super_admin' ? 'bg-indigo-950 text-indigo-300 border border-indigo-800/50' : 'bg-emerald-950 text-emerald-300 border border-emerald-800/50' }}">
                            {{ auth()->user()->role->label() ?? 'Yönetici' }}
                        </span>
                    </div>
                </div>
                @if(auth()->user() && auth()->user()->isLocalModerator() && auth()->user()->moderatedLocation)
                    <p class="text-[11px] text-emerald-400 font-medium mt-2 flex items-center gap-1">
                        <span>📍 Bölge:</span>
                        <span>{{ auth()->user()->moderatedLocation->city }} / {{ auth()->user()->moderatedLocation->district }}</span>
                    </p>
                @endif
            </div>

            <!-- Menü Bağlantıları -->
            <nav class="p-3 space-y-1 flex-1 text-sm font-medium">
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>İstatistik Panosu</span>
                </a>

                <a href="{{ route('admin.moderation.index') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.moderation.*') ? 'bg-blue-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Moderasyon Kuyruğu</span>
                </a>

                @if(auth()->user() && auth()->user()->isSuperAdmin())
                    <a href="{{ route('admin.ads.index') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin.ads.*') ? 'bg-blue-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                        <span>Sponsorlu İlanlar</span>
                    </a>
                @endif

                <div class="pt-4 mt-4 border-t border-slate-800">
                    <a href="{{ route('home') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/60 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Platforma Dön</span>
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Sağ Ana İçerik Alanı -->
        <main class="flex-1 flex flex-col min-w-0 bg-slate-950">
            <!-- Bildirim Barları (Success & Error Flash Messages) -->
            @if(session('success'))
                <div class="m-5 mb-0 px-4 py-3 rounded-xl bg-emerald-950/70 border border-emerald-800/80 text-emerald-200 text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="m-5 mb-0 px-4 py-3 rounded-xl bg-rose-950/70 border border-rose-800/80 text-rose-200 text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <div class="p-6 md:p-8 flex-1">
                {{ $slot }}
            </div>
        </main>
    </div>

</body>
</html>

