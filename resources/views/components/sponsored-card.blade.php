@props([
    'title' => 'Kadıköy Odun Ekmeği & Gurme Fırın',
    'location' => 'Moda, Kadıköy',
    'content' => 'Bu hafta sonuna özel sıcak ekşi mayalı köy ekmeklerimiz ve taze kruvasanlarımız fırından yeni çıktı! Gel-al siparişlerde %20 indirim sizleri bekliyor.',
    'image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=800&q=80',
    'ctaText' => 'Yol Tarifi Al & Menü',
    'ctaUrl' => 'https://maps.google.com'
])

<article class="p-4 border-b border-amber-200/50 dark:border-amber-900/30 bg-gradient-to-r from-amber-500/[0.04] via-transparent to-transparent transition-colors">
    <div class="flex items-start gap-3">
        <!-- İşletme Profil İkonu -->
        <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-amber-500 to-orange-400 text-white font-bold flex items-center justify-center text-sm shadow-sm shrink-0">
            🥖
        </div>

        <div class="flex-1 min-w-0">
            <!-- Sponsorlu Başlık Barı -->
            <div class="flex items-center justify-between gap-2 mb-1">
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="font-bold text-[15px] text-slate-900 dark:text-white truncate">
                        {{ $title }}
                    </span>
                    <span class="text-xs text-amber-600 dark:text-amber-400 font-semibold flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Doğrulanmış Esnaf
                    </span>
                </div>

                <!-- Sponsor Rozeti -->
                <span class="shrink-0 inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold tracking-wide uppercase bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-300/60 dark:border-amber-800/60">
                    Sponsorlu İlan
                </span>
            </div>

            <div class="text-xs text-slate-500 dark:text-neutral-400 mb-2 flex items-center gap-1">
                <span>📍 {{ $location }}</span>
            </div>

            <!-- Reklam Metni -->
            <p class="text-[14px] leading-relaxed text-slate-800 dark:text-neutral-200 mb-3">
                {{ $content }}
            </p>

            <!-- Reklam Görseli & CTA Butonu -->
            @if($image)
                <div class="relative rounded-2xl overflow-hidden border border-amber-200/60 dark:border-amber-900/40 mb-3 shadow-sm group">
                    <img src="{{ $image }}" alt="{{ $title }}" class="w-full max-h-[280px] object-cover group-hover:scale-105 transition-transform duration-500">
                    
                    <!-- Görsel Üzeri CTA Banner -->
                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent p-3 flex items-center justify-between">
                        <span class="text-xs font-medium text-white/90">Özel Mahalle Kampanyası</span>
                        <a href="{{ $ctaUrl }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs shadow-md transition-all">
                            <span>{{ $ctaText }}</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</article>

