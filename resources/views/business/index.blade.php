@extends('layouts.app', ['title' => 'Mahalle Esnaf Rehberi - Meydan', 'maxWidth' => 'max-w-4xl'])

@section('content')
<div class="px-4 py-6" x-data="businessDirectory()">
    
    <!-- Başlık & Esnaf Ekle Butonu -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 dark:border-neutral-800 pb-5 mb-6">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 text-xs font-bold mb-1">
                <span>⭐ Doğrulanmış Mahalle Esnafı</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                <span>Esnaf Rehberi</span>
                <span class="text-xs font-medium px-2 py-0.5 rounded-md bg-slate-100 dark:bg-neutral-800 text-slate-600 dark:text-neutral-400">
                    📍 {{ $location->district ?? 'Bölgeniz' }}
                </span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-neutral-400 mt-1">
                Mahallenizdeki güvenilir ustaları, fırınları, kafeleri ve butikleri keşfedin.
            </p>
        </div>

        <a href="{{ route('business.register') }}"
           class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-xs shadow-md transition flex items-center gap-1.5 shrink-0 self-start sm:self-auto">
            <span>➕</span>
            <span>Dükkanını Ekle / Kaydol</span>
        </a>
    </div>

    <!-- Kategori Filtre Çipleri -->
    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-2 mb-6">
        <template x-for="cat in categories" :key="cat.id">
            <button type="button" @click="setCategory(cat.id)"
                    class="px-3.5 py-1.5 rounded-full text-xs font-bold transition shrink-0 flex items-center gap-1.5 border"
                    :class="selectedCategory === cat.id 
                        ? 'bg-emerald-600 text-white border-emerald-600 shadow-xs' 
                        : 'bg-white dark:bg-neutral-900 text-slate-600 dark:text-neutral-300 border-slate-200 dark:border-neutral-800 hover:border-slate-300'">
                <span x-text="cat.icon"></span>
                <span x-text="cat.name"></span>
            </button>
        </template>
    </div>

    <!-- İşletme Kartları Izgarası -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <template x-for="biz in businesses" :key="biz.id">
            <div class="bg-white dark:bg-neutral-900 rounded-2xl border border-slate-200 dark:border-neutral-800 p-4 shadow-xs hover:border-emerald-500/60 transition flex flex-col justify-between group">
                
                <div>
                    <!-- Üst: Logo ve İsim -->
                    <div class="flex items-start gap-3 mb-3">
                        <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-neutral-800 overflow-hidden shrink-0 flex items-center justify-center font-black text-emerald-600 border border-slate-200 dark:border-neutral-700">
                            <template x-if="biz.logo_url">
                                <img :src="biz.logo_url" :alt="biz.business_name" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!biz.logo_url">
                                <span x-text="biz.business_name.substring(0, 1)"></span>
                            </template>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1 flex-wrap">
                                <a :href="`/business/${biz.id}`"
                                   class="font-bold text-sm text-slate-900 dark:text-white truncate group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition"
                                   x-text="biz.business_name"></a>
                                <template x-if="biz.is_verified">
                                    <span class="text-emerald-500 shrink-0" title="Doğrulanmış Esnaf">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    </span>
                                </template>
                            </div>
                            <span class="text-[11px] text-slate-400 block truncate" x-text="biz.category_label"></span>
                        </div>
                    </div>

                    <!-- Yıldız ve Adres -->
                    <div class="flex items-center gap-1 text-xs text-amber-500 font-bold mb-2">
                        <span>★</span>
                        <span x-text="Number(biz.rating_avg).toFixed(1)"></span>
                        <span class="text-[11px] text-slate-400 font-normal" x-text="`(${biz.reviews_count} yorum)`"></span>
                    </div>

                    <p class="text-xs text-slate-600 dark:text-neutral-400 line-clamp-2 mb-3" x-text="biz.address"></p>
                </div>

                <!-- Butonlar -->
                <div class="pt-3 border-t border-slate-100 dark:border-neutral-800 flex items-center justify-between gap-2 text-xs">
                    <a :href="`/business/${biz.id}`"
                       class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-neutral-700 hover:bg-slate-50 dark:hover:bg-neutral-800 font-semibold text-slate-700 dark:text-neutral-300">
                        Profili Gör
                    </a>
                    
                    <template x-if="biz.whatsapp_url">
                        <a :href="biz.whatsapp_url" target="_blank"
                           class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold flex items-center gap-1">
                            <span>💬</span>
                            <span>Sipariş</span>
                        </a>
                    </template>
                </div>
            </div>
        </template>
    </div>

    <!-- Boş Durum -->
    <div x-show="!loading && businesses.length === 0" class="py-16 text-center text-slate-400 text-xs" x-cloak>
        <span class="text-3xl block mb-2">🏪</span>
        <span>Bu kategoride kayıtlı esnaf bulunmuyor. İlk işletme sayfasını sen aç!</span>
    </div>

    <!-- Yükleniyor -->
    <div x-show="loading" class="py-16 text-center text-slate-400">
        <span class="animate-spin inline-block w-6 h-6 border-2 border-emerald-500 border-t-transparent rounded-full mb-2"></span>
        <div class="text-xs">Esnaflar listeleniyor...</div>
    </div>
</div>

<script>
function businessDirectory() {
    return {
        locationId: {{ $location->id ?? 1 }},
        businesses: [],
        loading: false,
        selectedCategory: 'all',

        categories: [
            { id: 'all', name: 'Tümü', icon: '✨' },
            { id: 'restoran_kafe', name: 'Restoran & Kafe', icon: '🍽️' },
            { id: 'hizmet_usta', name: 'Usta & Hizmet', icon: '🔧' },
            { id: 'perakende_butik', name: 'Butik & Mağaza', icon: '🛍️' },
            { id: 'diger', name: 'Diğer Esnaf', icon: '🏪' }
        ],

        init() {
            this.fetchBusinesses();
        },

        setCategory(cat) {
            this.selectedCategory = cat;
            this.fetchBusinesses();
        },

        async fetchBusinesses() {
            this.loading = true;
            try {
                const res = await fetch(`/api/v1/business?location_id=${this.locationId}&category=${this.selectedCategory}`);
                const data = await res.json();
                if (data.success) {
                    this.businesses = data.businesses;
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endsection

