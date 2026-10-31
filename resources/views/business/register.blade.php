@extends('layouts.app', ['title' => 'Esnaf İşletme Profili Oluştur - Meydan', 'maxWidth' => 'max-w-2xl'])

@section('content')
<div class="px-4 py-6" x-data="businessRegister()">
    
    <!-- Başlık & Geri Butonu -->
    <div class="flex items-center gap-3 border-b border-slate-200 dark:border-neutral-800 pb-4 mb-6">
        <a href="{{ route('home') }}" class="p-2 -ml-2 rounded-full hover:bg-slate-100 dark:hover:bg-neutral-800 text-slate-500 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 text-[11px] font-bold mb-1">
                <span>🏪 Yerel Esnaf Vitrini</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                {{ $profile ? 'İşletme Profilini Düzenle' : 'Esnaf İşletme Sayfası Aç' }}
            </h1>
        </div>
    </div>

    <!-- Hata Bildirimi -->
    <div x-show="errorMessage" x-cloak class="mb-5 p-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 text-rose-800 dark:text-rose-300 text-xs">
        <span x-text="errorMessage"></span>
    </div>

    <form @submit.prevent="submitForm" class="space-y-4 text-xs">
        
        <!-- 1. İşletme Adı -->
        <div>
            <label class="block font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300 mb-1">
                İşletme / Dükkan Adı <span class="text-rose-500">*</span>
            </label>
            <input type="text" x-model="form.business_name" required maxlength="120"
                   placeholder="Örn: Hasan Usta Taş Fırın & Lahmacun"
                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-900 dark:text-white text-sm outline-none focus:ring-2 focus:ring-emerald-500 font-semibold">
        </div>

        <!-- 2. Kategori ve İlçe -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            <div>
                <label class="block font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300 mb-1">
                    Sektör / Kategori <span class="text-rose-500">*</span>
                </label>
                <select x-model="form.category" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-900 dark:text-white text-xs outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="restoran_kafe">🍽️ Restoran, Kafe & Fırın</option>
                    <option value="hizmet_usta">🔧 Usta, Tamir & Teknik Hizmet</option>
                    <option value="perakende_butik">🛍️ Butik, Terzi & Mağaza</option>
                    <option value="diger">🏪 Diğer Yerel Esnaf</option>
                </select>
            </div>

            <div>
                <label class="block font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300 mb-1">
                    Hizmet Verilen İlçe <span class="text-rose-500">*</span>
                </label>
                <select x-model="form.location_id" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-900 dark:text-white text-xs outline-none focus:ring-2 focus:ring-emerald-500">
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ ($profile && $profile->location_id == $loc->id) || (!$profile && $defaultLocation && $defaultLocation->id == $loc->id) ? 'selected' : '' }}>
                            {{ $loc->city }} - {{ $loc->district }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- 3. Telefon ve WhatsApp Numaraları -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            <div>
                <label class="block font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300 mb-1">
                    İşletme Telefonu <span class="text-rose-500">*</span>
                </label>
                <input type="tel" x-model="form.phone" required placeholder="0312 123 45 67"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-900 dark:text-white text-xs outline-none focus:ring-2 focus:ring-emerald-500 font-mono">
            </div>

            <div>
                <label class="block font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300 mb-1">
                    WhatsApp Sipariş Numarası
                </label>
                <input type="tel" x-model="form.whatsapp_number" placeholder="0555 123 45 67"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-900 dark:text-white text-xs outline-none focus:ring-2 focus:ring-emerald-500 font-mono">
            </div>
        </div>

        <!-- 4. Adres ve Google Haritalar Linki -->
        <div>
            <label class="block font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300 mb-1">
                Açık Dükkan Adresi <span class="text-rose-500">*</span>
            </label>
            <input type="text" x-model="form.address" required placeholder="Örn: Kuşcağız Mah. Sanatoryum Cad. No:14 Keçiören"
                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-900 dark:text-white text-xs outline-none focus:ring-2 focus:ring-emerald-500">
        </div>

        <div>
            <label class="block font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300 mb-1">
                Google Haritalar Yol Tarifi Linki (Opsiyonel)
            </label>
            <input type="url" x-model="form.google_maps_url" placeholder="https://maps.app.goo.gl/..."
                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-900 dark:text-white text-xs outline-none focus:ring-2 focus:ring-emerald-500">
        </div>

        <!-- 5. Logo ve Kapak Görseli -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            <div>
                <label class="block font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300 mb-1">
                    Dükkan Logosu / Profil Fotoğrafı
                </label>
                <input type="file" accept="image/*" @change="logoFile = $event.target.files[0]"
                       class="w-full text-xs text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 dark:file:bg-emerald-950/60 dark:file:text-emerald-300">
            </div>

            <div>
                <label class="block font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300 mb-1">
                    Dükkan Dış Mekan / Kapak Görseli
                </label>
                <input type="file" accept="image/*" @change="coverFile = $event.target.files[0]"
                       class="w-full text-xs text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 dark:file:bg-emerald-950/60 dark:file:text-emerald-300">
            </div>
        </div>

        <!-- 6. Vergi Numarası (Doğrulama ve Fatura için) -->
        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-neutral-800/50 border border-slate-200 dark:border-neutral-800 space-y-2">
            <div>
                <label class="block font-bold text-slate-700 dark:text-neutral-300">
                    Vergi Numarası / T.C. Kimlik No (Fatura & Esnaf Doğrulaması İçin)
                </label>
                <input type="text" x-model="form.tax_number" placeholder="Vergi levhası no veya TCKN"
                       class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs mt-1">
            </div>
            <p class="text-[11px] text-slate-400">Vergi bilgileri yalnızca onay ve kurumsal faturalandırma için kullanılır, sayfada gizli tutulur.</p>
        </div>

        <!-- Kaydet Butonu -->
        <button type="submit" :disabled="loading"
                class="w-full py-3.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-black text-sm shadow-md transition flex items-center justify-center gap-2">
            <span x-show="!loading">💾 İşletme Sayfasını Kaydet & Yayınla</span>
            <span x-show="loading" class="flex items-center gap-2">
                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                Kaydediliyor...
            </span>
        </button>
    </form>
</div>

<script>
function businessRegister() {
    return {
        loading: false,
        errorMessage: '',
        logoFile: null,
        coverFile: null,

        form: {
            business_name: '{{ $profile->business_name ?? '' }}',
            category: '{{ $profile->category ?? 'restoran_kafe' }}',
            location_id: '{{ $profile->location_id ?? ($defaultLocation ? $defaultLocation->id : 1) }}',
            phone: '{{ $profile->phone ?? '' }}',
            whatsapp_number: '{{ $profile->whatsapp_number ?? '' }}',
            address: '{{ $profile->address ?? '' }}',
            google_maps_url: '{{ $profile->google_maps_url ?? '' }}',
            tax_number: '{{ $profile->tax_number ?? '' }}'
        },

        async submitForm() {
            if (!this.form.business_name.trim() || !this.form.phone.trim() || !this.form.address.trim()) {
                this.errorMessage = 'Lütfen zorunlu alanları eksiksiz doldurun.';
                return;
            }

            this.loading = true;
            this.errorMessage = '';

            try {
                const formData = new FormData();
                Object.keys(this.form).forEach(key => {
                    if (this.form[key]) formData.append(key, this.form[key]);
                });

                if (this.logoFile) formData.append('logo', this.logoFile);
                if (this.coverFile) formData.append('cover', this.coverFile);

                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const res = await fetch('/api/v1/business', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await res.json();
                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Kayıt sırasında hata oluştu.');
                }

                window.location.href = `/business/${data.profile_id}`;
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

