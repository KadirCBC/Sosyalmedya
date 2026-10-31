@extends('layouts.app', ['title' => 'Acil Durum Bildir - Meydan Acil Ağı', 'maxWidth' => 'max-w-2xl'])

@section('content')
<div class="px-4 py-6" x-data="emergencyCreate()">
    
    <!-- Başlık & Geri Butonu -->
    <div class="flex items-center gap-3 border-b border-slate-200 dark:border-neutral-800 pb-4 mb-6">
        <a href="{{ route('home') }}" class="p-2 -ml-2 rounded-full hover:bg-slate-100 dark:hover:bg-neutral-800 text-slate-500 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 text-[11px] font-bold mb-1">
                <span>🚨 Meydan Sivil Dayanışma Ağı</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                Acil Durum Bildirimi
            </h1>
        </div>
    </div>

    <!-- Hata Bildirimi -->
    <div x-show="errorMessage" x-cloak class="mb-5 p-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 text-rose-800 dark:text-rose-300 text-xs">
        <span x-text="errorMessage"></span>
    </div>

    <form @submit.prevent="submitAlert" class="space-y-5">
        
        <!-- 1. Acil Durum Türü Seçici -->
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300 mb-2">
                Acil Durum Türü <span class="text-rose-500">*</span>
            </label>
            <div class="grid grid-cols-2 gap-2.5">
                <label class="cursor-pointer p-3 rounded-xl border transition flex items-center gap-2.5"
                       :class="form.alert_type === 'kayip_ilan' ? 'border-amber-500 bg-amber-50/50 dark:bg-amber-950/30 ring-2 ring-amber-500/30' : 'border-slate-200 dark:border-neutral-800'">
                    <input type="radio" value="kayip_ilan" x-model="form.alert_type" class="sr-only">
                    <span class="text-xl">🔍</span>
                    <div>
                        <div class="text-xs font-bold text-slate-900 dark:text-white">Kayıp İlanı</div>
                        <div class="text-[10px] text-slate-500">Çocuk, yaşlı, evcil hayvan</div>
                    </div>
                </label>

                <label class="cursor-pointer p-3 rounded-xl border transition flex items-center gap-2.5"
                       :class="form.alert_type === 'kan_bagisi' ? 'border-red-500 bg-red-50/50 dark:bg-red-950/30 ring-2 ring-red-500/30' : 'border-slate-200 dark:border-neutral-800'">
                    <input type="radio" value="kan_bagisi" x-model="form.alert_type" class="sr-only">
                    <span class="text-xl">🩸</span>
                    <div>
                        <div class="text-xs font-bold text-slate-900 dark:text-white">Kan İhtiyacı</div>
                        <div class="text-[10px] text-slate-500">Acil kan grubu çağrısı</div>
                    </div>
                </label>

                <label class="cursor-pointer p-3 rounded-xl border transition flex items-center gap-2.5"
                       :class="form.alert_type === 'afet_yardim' ? 'border-rose-500 bg-rose-50/50 dark:bg-rose-950/30 ring-2 ring-rose-500/30' : 'border-slate-200 dark:border-neutral-800'">
                    <input type="radio" value="afet_yardim" x-model="form.alert_type" class="sr-only">
                    <span class="text-xl">🚨</span>
                    <div>
                        <div class="text-xs font-bold text-slate-900 dark:text-white">Afet & Kurtarma</div>
                        <div class="text-[10px] text-slate-500">Deprem, sel, yangın, çadır</div>
                    </div>
                </label>

                <label class="cursor-pointer p-3 rounded-xl border transition flex items-center gap-2.5"
                       :class="form.alert_type === 'guvenlik_uyari' ? 'border-orange-500 bg-orange-50/50 dark:bg-orange-950/30 ring-2 ring-orange-500/30' : 'border-slate-200 dark:border-neutral-800'">
                    <input type="radio" value="guvenlik_uyari" x-model="form.alert_type" class="sr-only">
                    <span class="text-xl">⚠️</span>
                    <div>
                        <div class="text-xs font-bold text-slate-900 dark:text-white">Kriz Uyarısı</div>
                        <div class="text-[10px] text-slate-500">Gaz kaçağı, fırtına, tehlike</div>
                    </div>
                </label>
            </div>
        </div>

        <!-- 2. Başlık -->
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300 mb-1">
                Kısa ve Net Başlık <span class="text-rose-500">*</span>
            </label>
            <input type="text" x-model="form.title" maxlength="150" required
                   placeholder="Örn: ACİL: Keçiören Eğitim Araştırma Hastanesi için 0 Rh- Negatif Kan İhtiyacı"
                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-rose-500 outline-none transition font-medium">
        </div>

        <!-- 3. İlçe ve İletişim Telefonu -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300 mb-1">
                    İlgili İlçe / Bölge <span class="text-rose-500">*</span>
                </label>
                <select x-model="form.location_id" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-rose-500 outline-none transition">
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ ($defaultLocation && $defaultLocation->id == $loc->id) ? 'selected' : '' }}>
                            {{ $loc->city }} - {{ $loc->district }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300 mb-1">
                    Acil İletişim Telefonu <span class="text-rose-500">*</span>
                </label>
                <input type="tel" x-model="form.contact_phone" required
                       placeholder="0555 123 45 67"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-rose-500 outline-none transition font-mono">
            </div>
        </div>

        <!-- 4. Detaylı Açıklama -->
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300 mb-1">
                Detaylar ve Konum Tarifi <span class="text-rose-500">*</span>
            </label>
            <textarea x-model="form.description" rows="4" maxlength="2000" required
                      placeholder="Hastane adı, hasta yakını ismi, kayıp kişinin eşkali, kıyafetleri, son görüldüğü cadde vb. tüm hayati bilgileri yazın..."
                      class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-rose-500 outline-none transition"></textarea>
        </div>

        <!-- Yasal Sorumluluk Uyarısı -->
        <div class="p-3.5 rounded-xl bg-slate-100 dark:bg-neutral-800/60 border border-slate-200 dark:border-neutral-700 text-[11px] text-slate-600 dark:text-neutral-400 space-y-1">
            <div class="font-bold text-slate-800 dark:text-neutral-200 flex items-center gap-1">
                <span>⚖️ Yasal Bilgilendirme ve Güvenlik</span>
            </div>
            <p>
                Asılsız acil durum ihbarlarında bulunmak TCK 217/A (Halkı yanıltıcı bilgiyi alenen yayma) ve 5651 Sayılı Kanun kapsamında adli makamlara bildirilmektedir. İlanınız doğrulandıktan sonra tüm ilçeye bildirim gönderilecektir.
            </p>
        </div>

        <!-- Gönder Butonu -->
        <button type="submit" :disabled="loading"
                class="w-full py-3.5 rounded-xl bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-700 hover:to-red-700 disabled:opacity-50 text-white font-black text-sm shadow-lg transition flex items-center justify-center gap-2">
            <span x-show="!loading">🚨 Acil Durum Bildirimini Yayınla</span>
            <span x-show="loading" class="flex items-center gap-2">
                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                İşleniyor...
            </span>
        </button>
    </form>
</div>

<script>
function emergencyCreate() {
    return {
        loading: false,
        errorMessage: '',
        form: {
            alert_type: 'kayip_ilan',
            title: '',
            location_id: '{{ $defaultLocation ? $defaultLocation->id : 1 }}',
            contact_phone: '',
            description: '',
            latitude: null,
            longitude: null
        },

        async submitAlert() {
            if (!this.form.title.trim()) {
                this.errorMessage = 'Lütfen acil durum başlığı girin.';
                return;
            }
            if (!this.form.contact_phone.trim()) {
                this.errorMessage = 'Lütfen ulaşılabilecek bir irtibat telefonu girin.';
                return;
            }

            this.loading = true;
            this.errorMessage = '';

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const res = await fetch('/api/v1/emergency/alerts', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await res.json();
                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Bildirim kaydedilirken hata oluştu.');
                }

                alert(data.message);
                window.location.href = `/emergency/${data.alert_id}`;
            } catch (err) {
                this.errorMessage = err.message || 'Bağlantı hatası oluştu.';
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endsection

