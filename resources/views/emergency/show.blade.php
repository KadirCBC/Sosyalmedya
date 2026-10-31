@extends('layouts.app', ['title' => 'ACİL: ' . $alert->title, 'maxWidth' => 'max-w-2xl'])

@section('content')
<div class="px-4 py-6" x-data="emergencyShow({{ $alert->id }})">
    
    <!-- Üst Gezinti -->
    <div class="flex items-center justify-between border-b border-slate-200 dark:border-neutral-800 pb-3 mb-5">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:hover:text-white transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Meydan Akışına Dön</span>
        </a>

        <!-- Durum Rozeti -->
        @if($alert->status === 'active')
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-rose-100 text-rose-700 dark:bg-rose-950/70 dark:text-rose-300 animate-pulse">
                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                <span>AKTİF ACİL DURUM</span>
            </span>
        @else
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-200 text-slate-700 dark:bg-neutral-800 dark:text-neutral-400">
                <span>✅ ÇÖZÜLDÜ / KAPATILDI</span>
            </span>
        @endif
    </div>

    <!-- Acil Durum Başlık Kartı -->
    <div class="rounded-2xl p-5 mb-6 text-white shadow-xl"
         class="{{ $alert->alert_type === 'kan_bagisi' ? 'bg-gradient-to-r from-red-600 to-rose-700' : 'bg-gradient-to-r from-rose-700 to-red-800' }}">
        
        <div class="flex items-center gap-2 mb-2">
            <span class="text-2xl">{{ $alert->icon }}</span>
            <span class="px-2.5 py-0.5 rounded-full bg-black/30 backdrop-blur-md text-[10px] font-black uppercase tracking-wider border border-white/20">
                {{ $alert->type_label }}
            </span>
            <span class="text-xs opacity-80">📍 {{ $alert->location->district ?? '' }} / {{ $alert->location->city ?? '' }}</span>
        </div>

        <h1 class="text-xl sm:text-2xl font-black tracking-tight leading-snug mt-1">
            {{ $alert->title }}
        </h1>

        <div class="mt-4 pt-3 border-t border-white/20 flex items-center justify-between text-xs opacity-90">
            <span>Yayınlanma: {{ $alert->created_at->diffForHumans() }}</span>
            @if($alert->is_verified)
                <span class="flex items-center gap-1 font-bold text-emerald-200">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    Doğrulanmış Çağrı
                </span>
            @else
                <span class="text-amber-200 font-semibold">Moderatör İncelemesinde</span>
            @endif
        </div>
    </div>

    <!-- Hızlı İletişim Butonu (Büyük Kırmızı Tel Butonu) -->
    @if($alert->contact_phone && $alert->status === 'active')
        <div class="mb-6">
            <a href="tel:{{ $alert->contact_phone }}"
               class="w-full py-4 px-6 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-base shadow-xl transition flex items-center justify-center gap-3">
                <span class="text-2xl animate-bounce">📞</span>
                <span>İrtibat Numarasını Ara: {{ $alert->contact_phone }}</span>
            </a>
        </div>
    @endif

    <!-- Detaylı Açıklama -->
    <div class="bg-white dark:bg-neutral-900 rounded-2xl border border-slate-200 dark:border-neutral-800 p-5 shadow-sm mb-6">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-neutral-500 mb-2">Acil Durum Detayları</h3>
        <p class="text-sm leading-relaxed text-slate-800 dark:text-neutral-200 whitespace-pre-line font-medium">
            {{ $alert->description }}
        </p>
    </div>

    <!-- Moderatör / Sahip Aksiyon Paneli -->
    @auth
        @if(auth()->user()->isSuperAdmin() || auth()->user()->isLocalModerator() || auth()->id() === $alert->user_id)
            <div class="p-4 rounded-xl bg-slate-50 dark:bg-neutral-800/60 border border-slate-200 dark:border-neutral-800 space-y-3">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-neutral-300">
                    ⚙️ Yetkili Moderasyon Paneli
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    @if(!$alert->is_verified && (auth()->user()->isSuperAdmin() || auth()->user()->isLocalModerator()))
                        <button @click="verifyAlert()"
                                class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow transition">
                            ✅ İlanı Doğrula & Tüm İlçeye Push Fırlat
                        </button>
                    @endif

                    @if($alert->status === 'active')
                        <button @click="resolveAlert()"
                                class="px-4 py-2 rounded-xl bg-slate-800 dark:bg-neutral-700 hover:bg-black text-white text-xs font-bold transition">
                            🏁 Acil Durumu 'Çözüldü' Olarak Kapat
                        </button>
                    @endif
                </div>
            </div>
        @endif
    @endauth

</div>

<script>
function emergencyShow(alertId) {
    return {
        alertId: alertId,

        async verifyAlert() {
            if (!confirm('Bu acil durum ilanını onaylayıp ilçedeki tüm kullanıcılara anında bildirim göndermek istediğinize emin misiniz?')) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            try {
                const res = await fetch(`/api/v1/emergency/alerts/${this.alertId}/verify`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                }
            } catch (e) {
                console.error(e);
            }
        },

        async resolveAlert() {
            if (!confirm('Acil durum ilanını kapatmak istediğinize emin misiniz? Kırmızı akış uyarısı kaldırılacaktır.')) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            try {
                const res = await fetch(`/api/v1/emergency/alerts/${this.alertId}/resolve`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    alert('Acil durum ilanı başarıyla kapatıldı.');
                    window.location.reload();
                }
            } catch (e) {
                console.error(e);
            }
        }
    }
}
</script>
@endsection

