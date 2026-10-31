@extends('layouts.app', ['title' => 'İlan Ver - Mahalle Pazarı', 'maxWidth' => 'max-w-2xl'])

@section('content')
<div class="px-4 py-6" x-data="marketplaceCreate()">
    
    <!-- Geri Butonu & Başlık -->
    <div class="flex items-center gap-3 border-b border-slate-200 dark:border-neutral-800 pb-4 mb-6">
        <a href="{{ route('marketplace.index') }}" class="p-2 -ml-2 rounded-full hover:bg-slate-100 dark:hover:bg-neutral-800 text-slate-500 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Mahalle Pazarına İlan Ver</h1>
            <p class="text-xs text-slate-500 dark:text-neutral-400">Komisyon ödemeden, kargo beklemeden elden teslim edin.</p>
        </div>
    </div>

    <!-- Hata Bildirimi -->
    <div x-show="errorMessage" x-cloak class="mb-5 p-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 text-rose-800 dark:text-rose-300 text-xs">
        <span x-text="errorMessage"></span>
    </div>

    <form @submit.prevent="submitForm" class="space-y-5">
        <!-- 1. İlan Başlığı -->
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300 mb-1">
                İlan Başlığı <span class="text-rose-500">*</span>
            </label>
            <input type="text" x-model="form.title" maxlength="100" required
                   placeholder="Örn: Az kullanılmış Philips Filtre Kahve Makinesi"
                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 outline-none transition">
        </div>

        <!-- 2. Kategori ve Konum -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300 mb-1">
                    Kategori <span class="text-rose-500">*</span>
                </label>
                <select x-model="form.category" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 outline-none transition">
                    <option value="elektronik">📱 Elektronik</option>
                    <option value="ev_esyasi">🛋️ Ev Eşyası</option>
                    <option value="giyim">👕 Giyim & Moda</option>
                    <option value="kitap_hobi">📚 Kitap & Hobi</option>
                    <option value="diger">📦 Diğer</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300 mb-1">
                    İlçe / Mahalle <span class="text-rose-500">*</span>
                </label>
                <select x-model="form.location_id" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 outline-none transition">
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ ($defaultLocation && $defaultLocation->id == $loc->id) ? 'selected' : '' }}>
                            {{ $loc->city }} - {{ $loc->district }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- 3. Fiyat ve Askıda Eşya Seçimi -->
        <div class="p-4 rounded-xl border border-slate-200 dark:border-neutral-800 bg-slate-50/50 dark:bg-neutral-800/40 space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs font-bold text-slate-800 dark:text-neutral-200">🎁 Askıda Eşya / Ücretsiz Dayanışma</div>
                    <div class="text-[11px] text-slate-500 dark:text-neutral-400">Ücret talep etmiyorsanız işaretleyin (+5 İtibar Puanı)</div>
                </div>
                <input type="checkbox" x-model="form.is_free" @change="if(form.is_free) form.price = 0"
                       class="w-5 h-5 rounded text-emerald-600 focus:ring-emerald-500">
            </div>

            <div x-show="!form.is_free" x-transition>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300 mb-1">
                    Fiyat (TL)
                </label>
                <div class="relative">
                    <input type="number" step="1" min="0" x-model="form.price"
                           placeholder="350"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 outline-none transition pr-10">
                    <span class="absolute right-3.5 top-2.5 text-xs font-bold text-slate-400">TL</span>
                </div>
            </div>
        </div>

        <!-- 4. Fotoğraf Yükleme (Maks 4 Görsel) -->
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300 mb-1">
                Fotoğraflar (En fazla 4 adet)
            </label>
            <div class="grid grid-cols-4 gap-2 mb-2" x-show="previewUrls.length > 0">
                <template x-for="(url, idx) in previewUrls" :key="idx">
                    <div class="aspect-square rounded-xl overflow-hidden relative border border-slate-200 dark:border-neutral-700">
                        <img :src="url" class="w-full h-full object-cover">
                        <button type="button" @click="removeImage(idx)"
                                class="absolute top-1 right-1 w-5 h-5 rounded-full bg-black/70 text-white flex items-center justify-center text-[10px]">✕</button>
                    </div>
                </template>
            </div>

            <label x-show="selectedFiles.length < 4"
                   class="border-2 border-dashed border-slate-300 dark:border-neutral-700 rounded-xl p-4 text-center hover:border-blue-500 transition cursor-pointer block bg-slate-50/50 dark:bg-neutral-800/30">
                <input type="file" multiple accept="image/*" @change="handleFiles($event)" class="hidden">
                <div class="flex flex-col items-center">
                    <svg class="w-7 h-7 text-slate-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="text-xs font-semibold text-slate-700 dark:text-neutral-200">Fotoğraf seçmek için tıklayın</span>
                </div>
            </label>
        </div>

        <!-- 5. Açıklama -->
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300 mb-1">
                Açıklama <span class="text-rose-500">*</span>
            </label>
            <textarea x-model="form.description" rows="4" maxlength="2000" required
                      placeholder="Eşyanın durumu, varsa deformesi, teslim alma saatleri vb. bilgileri yazın..."
                      class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 outline-none transition"></textarea>
        </div>

        <!-- Gönder Butonu -->
        <button type="submit" :disabled="loading"
                class="w-full py-3 rounded-xl bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-bold text-sm shadow-md transition flex items-center justify-center gap-2">
            <span x-show="!loading">🚀 İlanı Mahallede Yayınla</span>
            <span x-show="loading" class="flex items-center gap-2">
                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                Yayınlanıyor...
            </span>
        </button>
    </form>
</div>

<script>
function marketplaceCreate() {
    return {
        loading: false,
        errorMessage: '',
        selectedFiles: [],
        previewUrls: [],

        form: {
            title: '',
            category: 'diger',
            location_id: '{{ $defaultLocation ? $defaultLocation->id : 1 }}',
            price: '',
            is_free: false,
            description: ''
        },

        handleFiles(e) {
            const files = Array.from(e.target.files);
            const remaining = 4 - this.selectedFiles.length;
            const toAdd = files.slice(0, remaining);

            for (const file of toAdd) {
                this.selectedFiles.push(file);
                this.previewUrls.push(URL.createObjectURL(file));
            }
        },

        removeImage(idx) {
            this.selectedFiles.splice(idx, 1);
            this.previewUrls.splice(idx, 1);
        },

        async submitForm() {
            if (!this.form.title.trim()) {
                this.errorMessage = 'Lütfen ilan başlığını girin.';
                return;
            }
            if (!this.form.description.trim()) {
                this.errorMessage = 'Lütfen açıklama girin.';
                return;
            }

            this.loading = true;
            this.errorMessage = '';

            try {
                const formData = new FormData();
                formData.append('title', this.form.title);
                formData.append('category', this.form.category);
                formData.append('location_id', this.form.location_id);
                formData.append('price', this.form.is_free ? 0 : (this.form.price || 0));
                formData.append('is_free', this.form.is_free ? 1 : 0);
                formData.append('description', this.form.description);

                this.selectedFiles.forEach((file, index) => {
                    formData.append(`images[${index}]`, file);
                });

                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                const res = await fetch('/api/v1/marketplace', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await res.json();
                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'İlan kaydedilirken hata oluştu.');
                }

                window.location.href = `/marketplace/${data.item_id}`;
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

