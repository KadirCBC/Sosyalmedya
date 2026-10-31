@extends('layouts.app', ['title' => 'Meydan - Yerel Etkinlik ve Buluşma Takvimi'])

@section('content')
<div class="p-4 sm:p-6 space-y-6">

    <!-- Üst Başlık & Eylem Butonu -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-neutral-800">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                    Mahalle Takvimi
                </span>
                <span class="text-xs text-slate-500 dark:text-neutral-400">
                    📍 {{ $location->city }} / {{ $location->district }}
                </span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white mt-1">
                📅 Mahalle Etkinlikleri & Buluşmalar
            </h1>
            <p class="text-xs text-slate-500 dark:text-neutral-400 mt-0.5">
                Komşularınla yüz yüze tanış, spora çık, kahve iç veya dayanışmaya katıl.
            </p>
        </div>

        <a href="{{ route('events.create') }}"
           class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs shadow-md transition-colors flex items-center gap-1.5 shrink-0">
            <span>+ Buluşma Başlat</span>
        </a>
    </div>

    <!-- Bu Hafta Sonu Mahallede Ne Var? Öne Çıkan Kartı -->
    @if($weekendEvents->isNotEmpty())
        <div class="p-4 rounded-2xl bg-gradient-to-r from-amber-500/10 via-orange-500/5 to-transparent border border-amber-500/20 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-extrabold text-amber-800 dark:text-amber-300 flex items-center gap-1.5">
                    <span>🌟</span>
                    <span>Bu Hafta Sonu Mahallede Ne Var?</span>
                </h3>
                <span class="text-[11px] font-semibold text-amber-700 dark:text-amber-400">
                    {{ $weekendEvents->count() }} Etkinlik
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($weekendEvents->take(4) as $wEvent)
                    <a href="{{ route('events.show', $wEvent->id) }}"
                       class="p-3 bg-white dark:bg-neutral-900 rounded-xl border border-slate-200 dark:border-neutral-800 hover:border-amber-400 dark:hover:border-amber-600 transition flex flex-col justify-between group shadow-2xs">
                        <div>
                            <div class="flex items-center justify-between gap-1 mb-1">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $wEvent->getCategoryMeta()['bg'] }} {{ $wEvent->getCategoryMeta()['text'] }}">
                                    {{ $wEvent->getCategoryMeta()['icon'] }} {{ $wEvent->getCategoryMeta()['name'] }}
                                </span>
                                @if($wEvent->is_sponsored_venue)
                                    <span class="text-[10px] font-black px-1.5 py-0.5 rounded-md bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                                        ⭐ Sponsorlu
                                    </span>
                                @endif
                            </div>
                            <h4 class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors line-clamp-1">
                                {{ $wEvent->title }}
                            </h4>
                            <p class="text-[11px] text-slate-500 dark:text-neutral-400 mt-1 flex items-center gap-1">
                                <span>📍 {{ $wEvent->venue_name }}</span>
                                <span>•</span>
                                <span>⏰ {{ $wEvent->event_date->translatedFormat('d M, H:i') }}</span>
                            </p>
                        </div>
                        <div class="mt-2.5 pt-2 border-t border-slate-100 dark:border-neutral-800 flex items-center justify-between text-[11px]">
                            <span class="font-semibold text-slate-600 dark:text-slate-300">{{ $wEvent->participants_count }} komşu katılıyor</span>
                            <span class="font-bold text-amber-600 dark:text-amber-400">Katıl →</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Kategori Filtre Butonları -->
    <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-1 text-xs font-semibold">
        <a href="{{ route('events.index') }}"
           class="px-3 py-1.5 rounded-full border transition-colors whitespace-nowrap {{ empty($activeCategory) ? 'bg-slate-900 text-white dark:bg-white dark:text-black border-transparent' : 'bg-slate-100 dark:bg-neutral-800 text-slate-600 dark:text-neutral-400 border-slate-200 dark:border-neutral-700' }}">
            Tümü
        </a>
        @foreach(['spor' => '⚽ Spor & Doğa', 'hobi_oyun' => '☕ Hobi & Oyun', 'kultur_sanat' => '🎨 Kültür & Sanat', 'yardimlasma' => '🤝 Dayanışma', 'belediye_duyuru' => '🏛️ Resmi'] as $catKey => $catLabel)
            <a href="{{ route('events.index', ['category' => $catKey]) }}"
               class="px-3 py-1.5 rounded-full border transition-colors whitespace-nowrap {{ $activeCategory === $catKey ? 'bg-amber-600 text-white border-amber-600' : 'bg-slate-100 dark:bg-neutral-800 text-slate-600 dark:text-neutral-400 border-slate-200 dark:border-neutral-700 hover:border-slate-300' }}">
                {{ $catLabel }}
            </a>
        @endforeach
    </div>

    <!-- Yaklaşan Etkinlikler Listesi -->
    <div class="space-y-3">
        <h2 class="text-sm font-extrabold text-slate-900 dark:text-white">
            Yaklaşan Tüm Mahalle Buluşmaları
        </h2>

        @forelse($upcomingEvents as $event)
            <div class="p-4 rounded-2xl bg-white dark:bg-neutral-900 border border-slate-200 dark:border-neutral-800 shadow-2xs hover:border-slate-300 dark:hover:border-neutral-700 transition flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1.5 flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full {{ $event->getCategoryMeta()['bg'] }} {{ $event->getCategoryMeta()['text'] }}">
                            {{ $event->getCategoryMeta()['icon'] }} {{ $event->getCategoryMeta()['name'] }}
                        </span>

                        @if($event->is_sponsored_venue)
                            <span class="text-[10px] font-black px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-300 dark:border-amber-800 flex items-center gap-1">
                                <span>⭐ Sponsorlu Buluşma Noktası</span>
                            </span>
                        @endif

                        <span class="text-xs text-slate-400 dark:text-neutral-500">•</span>
                        <span class="text-xs text-slate-500 dark:text-neutral-400 font-medium">
                            Düzenleyen: {{ $event->user->name ?? 'Mahalle Sakini' }}
                        </span>
                    </div>

                    <a href="{{ route('events.show', $event->id) }}" class="block">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white hover:text-amber-600 dark:hover:text-amber-400 transition-colors">
                            {{ $event->title }}
                        </h3>
                    </a>

                    <p class="text-xs text-slate-600 dark:text-neutral-300 line-clamp-2">
                        {{ $event->description }}
                    </p>

                    <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-neutral-400 flex-wrap pt-1">
                        <span class="flex items-center gap-1 font-semibold text-slate-700 dark:text-slate-200">
                            <span>📍</span>
                            <span>{{ $event->venue_name }} ({{ $event->address }})</span>
                        </span>
                        <span>•</span>
                        <span class="flex items-center gap-1 font-semibold text-amber-600 dark:text-amber-400">
                            <span>⏰</span>
                            <span>{{ $event->event_date->translatedFormat('d F Y, l - H:i') }}</span>
                        </span>
                    </div>
                </div>

                <!-- Sağ Taraf: Katılımcı Sayısı & İncele Butonu -->
                <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-center gap-2 shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100 dark:border-neutral-800">
                    <div class="text-xs text-slate-500 dark:text-neutral-400 text-right">
                        <span class="font-bold text-slate-900 dark:text-white text-sm">{{ $event->participants_count }}</span>
                        @if($event->max_participants)
                            <span class="text-[11px] text-slate-400">/ {{ $event->max_participants }} kişi</span>
                        @else
                            <span class="text-[11px] text-slate-400">katılımcı</span>
                        @endif
                    </div>

                    <a href="{{ route('events.show', $event->id) }}"
                       class="px-4 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-slate-100 text-white dark:text-black font-bold text-xs transition-colors">
                        Detay & Katıl →
                    </a>
                </div>
            </div>
        @empty
            <div class="py-12 text-center rounded-2xl bg-slate-50 dark:bg-neutral-900 border border-slate-200 dark:border-neutral-800 space-y-3">
                <span class="text-4xl">☕</span>
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300">Henüz planlanmış bir etkinlik bulunmuyor.</h3>
                <p class="text-xs text-slate-500 dark:text-neutral-400 max-w-sm mx-auto">
                    Mahallede yürüyüş, satranç, kahve sohbeti veya kitap kulübü başlatarak ilk adımı sen at!
                </p>
                <a href="{{ route('events.create') }}" class="inline-block px-4 py-2 rounded-xl bg-amber-600 text-white font-bold text-xs shadow-md">
                    + İlk Buluşmayı Sen Başlat
                </a>
            </div>
        @endforelse

        <!-- Sayfalama -->
        <div class="pt-4">
            {{ $upcomingEvents->links() }}
        </div>
    </div>

</div>
@endsection

