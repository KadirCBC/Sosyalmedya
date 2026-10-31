<x-app-layout
    :title="$pageTitle"
    :ogTitle="$pageTitle"
    :ogDescription="$pageDescription"
    :ogUrl="$canonicalUrl">

    <!-- Schema.org JSON-LD Yapılandırılmış Verisi (Google Arama Motoru İçin) -->
    <script type="application/ld+json">
        {!! $jsonLd !!}
    </script>

    <!-- İlçe Açılış Başlığı ve Hava Durumu Barı -->
    <header class="p-5 border-b border-slate-200 dark:border-neutral-800 bg-gradient-to-b from-blue-50/50 to-transparent dark:from-blue-950/20">
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-neutral-400 mb-3">
            <a href="{{ route('home') }}" class="hover:underline">Ana Sayfa</a>
            <span>›</span>
            <span class="text-slate-700 dark:text-neutral-300">{{ $location->city }}</span>
            <span>›</span>
            <span class="text-blue-600 dark:text-blue-400 font-semibold">{{ $location->district }}</span>
        </nav>

        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                    <span>📍 {{ $location->district }}</span>
                    <span class="text-sm font-medium text-slate-500 dark:text-neutral-400">({{ $location->city }})</span>
                </h1>
                <p class="text-xs text-slate-600 dark:text-neutral-400 mt-1 max-w-md">
                    {{ $location->district }} mahalle sakinlerinin paylaşımları, belediye duyuruları ve yerel tartışmalar.
                </p>
            </div>

            <!-- Yerel Hava Durumu Widget'ı -->
            <div class="shrink-0 p-3 rounded-2xl bg-white dark:bg-neutral-900 border border-slate-200/80 dark:border-neutral-800 shadow-sm flex items-center gap-3">
                <span class="text-2xl">{{ $weather['icon'] }}</span>
                <div>
                    <div class="text-base font-black text-slate-900 dark:text-white leading-none">
                        {{ $weather['temp'] }}°C
                    </div>
                    <div class="text-[10px] font-medium text-slate-500 dark:text-neutral-400 mt-0.5">
                        {{ $weather['desc'] }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Katılım Çağrısı (CTA) -->
        <div class="mt-4 pt-3 border-t border-slate-200/60 dark:border-neutral-800/60 flex items-center justify-between gap-2">
            <span class="text-xs text-slate-500 dark:text-neutral-400">Sen de {{ $location->district }}'de sesini duyur!</span>
            <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-sm transition-colors">
                <span>Gönderi Paylaş</span>
                <span>→</span>
            </a>
        </div>
    </header>

    <!-- İlçe Akış Gönderileri -->
    <section class="divide-y divide-slate-100 dark:divide-neutral-900">
        @forelse($posts as $post)
            <x-post-card :post="$post" />
        @empty
            <div class="p-12 text-center text-slate-400 dark:text-neutral-600">
                <div class="text-3xl mb-2">📍</div>
                <p class="text-sm font-medium text-slate-700 dark:text-neutral-300">{{ $location->district }} için henüz bir gönderi bulunmuyor.</p>
                <p class="text-xs text-slate-500 mt-1">İlk paylaşımı yaparak mahallenin gündemini sen başlat!</p>
                <a href="{{ route('home') }}" class="mt-4 inline-block px-4 py-2 rounded-full bg-blue-600 text-white font-bold text-xs shadow-sm">
                    İlk Gönderiyi Paylaş
                </a>
            </div>
        @endforelse
    </section>

    <!-- Alt SEO Açıklama Bloğu (Google Bot İçin Zengin İçerik) -->
    <footer class="p-6 bg-slate-100/50 dark:bg-neutral-950/60 border-t border-slate-200 dark:border-neutral-900 text-xs text-slate-500 dark:text-neutral-500 space-y-2">
        <h2 class="font-bold text-slate-700 dark:text-neutral-400">{{ $location->district }} Yerel Sosyal Ağı Hakkında</h2>
        <p class="leading-relaxed">
            Meydan, {{ $location->city }} ili {{ $location->district }} sakinlerini doğrudan mahalle gündeminde bir araya getirir. Su ve elektrik kesintileri, belediye duyuruları, yerel esnaf fırsatları ve mahalle sohbetlerini canlı olarak takip edebilirsiniz.
        </p>
    </footer>
</x-app-layout>

