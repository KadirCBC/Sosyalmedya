@extends('layouts.app', ['title' => 'Meydan - ' . $event->title])

@section('content')
<div class="p-4 sm:p-6 space-y-6"
     x-data="{
         status: '{{ $currentUserAttendee?->status ?? '' }}',
         participantsCount: {{ $event->participants_count }},
         isFull: {{ $event->isFull() ? 'true' : 'false' }},
         loading: false,

         async toggleAttend(newStatus) {
             this.loading = true;
             try {
                 const res = await fetch('/api/v1/events/{{ $event->id }}/attend', {
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/json',
                         'Accept': 'application/json',
                         'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                     },
                     body: JSON.stringify({ status: newStatus })
                 });

                 const data = await res.json();
                 if (res.ok && data.success) {
                     this.status = data.attendance.status || '';
                     this.participantsCount = data.attendance.participants_count;
                     $dispatch('toast', data.message);
                 } else {
                     $dispatch('toast', data.message || 'İşlem başarısız.');
                 }
             } catch (e) {
                 $dispatch('toast', 'Ağ bağlantı hatası oluştu.');
             } finally {
                 this.loading = false;
             }
         },

         shareOnWhatsApp() {
             const text = encodeURIComponent('Mahalle Buluşması: {{ $event->title }}\nTarih: {{ $event->event_date->translatedFormat('d F Y, H:i') }}\nMekan: {{ $event->venue_name }}\n\nKatılmak için tıkla: ' + window.location.href);
             window.open(`https://api.whatsapp.com/send?text=${text}`, '_blank');
         },

         shareOnX() {
             const text = encodeURIComponent('Meydan\'da mahalle buluşması: {{ $event->title }}');
             window.open(`https://twitter.com/intent/tweet?text=${text}&url=${encodeURIComponent(window.location.href)}`, '_blank');
         }
     }">

    <!-- Üst Gezinme -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-neutral-800">
        <a href="{{ route('events.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-neutral-400 dark:hover:text-white flex items-center gap-1">
            ← Tüm Etkinlikler
        </a>

        <!-- Paylaş Butonları -->
        <div class="flex items-center gap-1.5">
            <button @click="shareOnWhatsApp()"
                    class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 hover:bg-emerald-200 transition-colors flex items-center gap-1">
                <span>WhatsApp</span>
            </button>
            <button @click="shareOnX()"
                    class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-800 dark:bg-neutral-800 dark:text-slate-200 hover:bg-slate-200 transition-colors flex items-center gap-1">
                <span>X'te Paylaş</span>
            </button>
        </div>
    </div>

    <!-- Etkinlik Ana Başlık Kartı -->
    <div class="p-5 rounded-3xl bg-white dark:bg-neutral-900 border border-slate-200 dark:border-neutral-800 shadow-sm space-y-4">
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-xs font-bold px-3 py-1 rounded-full {{ $categoryMeta['bg'] }} {{ $categoryMeta['text'] }}">
                {{ $categoryMeta['icon'] }} {{ $categoryMeta['name'] }}
            </span>

            @if($event->is_sponsored_venue)
                <span class="text-xs font-black px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-300 dark:border-amber-800 flex items-center gap-1">
                    <span>⭐ Sponsorlu Buluşma Noktası</span>
                </span>
            @endif

            <span class="text-xs text-slate-400 dark:text-neutral-500">•</span>
            <span class="text-xs text-slate-500 dark:text-neutral-400 font-medium">
                📍 {{ $event->location->city }} / {{ $event->location->district }}
            </span>
        </div>

        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white leading-tight">
            {{ $event->title }}
        </h1>

        <!-- Tarih, Saat & Mekan Bilgi Kutuları -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
            <!-- Tarih & Saat -->
            <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-neutral-800/60 border border-slate-100 dark:border-neutral-800 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg font-bold shrink-0">
                    ⏰
                </div>
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase">Tarih & Saat</span>
                    <h4 class="text-xs sm:text-sm font-extrabold text-slate-900 dark:text-white">
                        {{ $event->event_date->translatedFormat('d F Y, l') }}
                    </h4>
                    <span class="text-xs font-semibold text-amber-600 dark:text-amber-400">
                        Saat {{ $event->event_date->format('H:i') }}
                    </span>
                </div>
            </div>

            <!-- Mekan Bilgisi -->
            <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-neutral-800/60 border border-slate-100 dark:border-neutral-800 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg font-bold shrink-0">
                    📍
                </div>
                <div class="min-w-0">
                    <span class="text-[11px] font-bold text-slate-400 uppercase">Buluşma Yeri</span>
                    <h4 class="text-xs sm:text-sm font-extrabold text-slate-900 dark:text-white truncate">
                        {{ $event->venue_name }}
                    </h4>
                    <p class="text-[11px] text-slate-500 dark:text-neutral-400 truncate">
                        {{ $event->address }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Sponsorlu İşletme Bağlantısı (Varsa) -->
        @if($event->businessProfile)
            <div class="p-3.5 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/60 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    @if($event->businessProfile->logo_url)
                        <img src="{{ $event->businessProfile->logo_url }}" class="w-10 h-10 rounded-xl object-cover">
                    @else
                        <div class="w-10 h-10 rounded-xl bg-amber-200 text-amber-800 font-bold flex items-center justify-center text-sm">
                            🏪
                        </div>
                    @endif
                    <div class="min-w-0">
                        <div class="flex items-center gap-1">
                            <span class="text-xs font-black text-amber-950 dark:text-amber-200 truncate">
                                {{ $event->businessProfile->business_name }}
                            </span>
                            <span class="text-[10px] bg-emerald-100 text-emerald-800 px-1.5 py-0.2 rounded font-bold">Onaylı Esnaf</span>
                        </div>
                        <p class="text-[11px] text-amber-800 dark:text-amber-300 truncate">
                            Bu etkinlik onaylı esnaf buluşma noktamızda ağırlanmaktadır.
                        </p>
                    </div>
                </div>
                <a href="{{ route('business.show', $event->businessProfile->id) }}"
                   class="px-3 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs shrink-0 shadow-xs">
                    Mekanı İncele →
                </a>
            </div>
        @endif

        <!-- Açıklama -->
        <div class="pt-2">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">
                Buluşma Detayları
            </h3>
            <p class="text-sm leading-relaxed text-slate-700 dark:text-slate-200 whitespace-pre-line">
                {{ $event->description }}
            </p>
        </div>

        <!-- Katılım Eylemi & Otomatik Hatırlatıcı Bilgisi -->
        <div class="p-4 rounded-2xl bg-slate-100 dark:bg-neutral-800/80 border border-slate-200 dark:border-neutral-700/80 space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-black text-slate-900 dark:text-white" x-text="participantsCount"></span>
                        <span class="text-xs text-slate-500 dark:text-neutral-400">
                            @if($event->max_participants)
                                / {{ $event->max_participants }} Kişi Kontenjan
                            @else
                                Mahalle Sakini Katılıyor
                            @endif
                        </span>
                    </div>
                    <p class="text-[11px] text-emerald-600 dark:text-emerald-400 font-medium flex items-center gap-1 mt-0.5">
                        <span>🔔</span>
                        <span>"Katılıyorum" diyenlere etkinlikten 2 saat önce WebPush kilit ekranı hatırlatması basılır.</span>
                    </p>
                </div>

                <!-- Butonlar -->
                <div class="flex items-center gap-2">
                    <!-- İlgileniyorum Butonu -->
                    <button @click="toggleAttend('interested')"
                            :disabled="loading"
                            type="button"
                            class="px-3.5 py-2 rounded-xl text-xs font-bold border transition-colors flex items-center gap-1"
                            :class="status === 'interested' ? 'bg-blue-50 dark:bg-blue-950 text-blue-600 dark:text-blue-400 border-blue-300 dark:border-blue-700' : 'bg-white dark:bg-neutral-900 text-slate-700 dark:text-slate-300 border-slate-300 dark:border-neutral-700 hover:border-slate-400'">
                        <span x-text="status === 'interested' ? '👀 Takip Ediyorsun' : '👀 İlgileniyorum'"></span>
                    </button>

                    <!-- Katılıyorum Butonu -->
                    <button @click="toggleAttend('attending')"
                            :disabled="loading"
                            type="button"
                            class="px-5 py-2 rounded-xl text-xs font-extrabold shadow-md transition-colors flex items-center gap-1.5"
                            :class="status === 'attending' ? 'bg-emerald-600 hover:bg-emerald-500 text-white' : 'bg-amber-600 hover:bg-amber-500 text-white'">
                        <span x-text="status === 'attending' ? '✓ Katılıyorsun' : 'Katılıyorum (+1 Hatırlatma)'"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Katılımcılar Listesi (Avatarlar) -->
        <div class="pt-3 border-t border-slate-100 dark:border-neutral-800">
            <h4 class="text-xs font-bold text-slate-500 dark:text-neutral-400 mb-2">
                Katılan Komşular ({{ $event->attendees->where('status', 'attending')->count() }} Kişi):
            </h4>
            <div class="flex items-center gap-1.5 flex-wrap">
                @forelse($event->attendees->where('status', 'attending') as $att)
                    <div class="flex items-center gap-1.5 px-2 py-1 rounded-xl bg-slate-50 dark:bg-neutral-800 border border-slate-200 dark:border-neutral-700 text-xs">
                        <div class="w-5 h-5 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center text-[10px]">
                            {{ mb_substr($att->user->name ?? 'K', 0, 1) }}
                        </div>
                        <span class="font-medium text-slate-800 dark:text-slate-200">{{ $att->user->name ?? 'Komşu' }}</span>
                    </div>
                @empty
                    <span class="text-xs text-slate-400 italic">İlk katılan sen ol!</span>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection

