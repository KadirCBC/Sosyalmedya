@extends('layouts.app', ['title' => 'Mahalle Pazarı - Komisyonsuz İkinci El & Askıda Eşya', 'maxWidth' => 'max-w-4xl'])

@section('content')
<div class="px-4 py-5" x-data="marketplaceIndex()">
    
    <!-- Üst Başlık & Eylem Çubuğu -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 dark:border-neutral-800 pb-5 mb-5">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 text-xs font-semibold mb-1">
                <span>🛍️ Sıfır Komisyon &bull; Sıfır Kargo Derdi</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                <span>Mahalle Pazarı</span>
                <span class="text-xs font-medium px-2 py-0.5 rounded-md bg-slate-100 dark:bg-neutral-800 text-slate-600 dark:text-neutral-400">
                    📍 {{ $location->district ?? 'Bölgeniz' }}
                </span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-neutral-400 mt-1">
                Mahalle sakinleriyle elden güvenle alışveriş yapın veya ihtiyacınız olmayan eşyaları askıya bırakın.
            </p>
        </div>

        <!-- İlan Ver Butonu -->
        <div class="flex items-center gap-2.5">
            <a href="{{ route('marketplace.create') }}"
               class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-xs shadow-md transition flex items-center gap-1.5 shrink-0">
                <span>➕</span>
                <span>Ücretsiz İlan Ver</span>
            </a>
        </div>
    </div>

    <!-- Kategori Filtre Çubuğu -->
    <div class="space-y-3 mb-6">
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-1">
            <template x-for="cat in categories" :key="cat.id">
                <button type="button" @click="setCategory(cat.id)"
                        class="px-3.5 py-1.5 rounded-full text-xs font-bold transition shrink-0 flex items-center gap-1.5 border"
                        :class="selectedCategory === cat.id 
                            ? 'bg-blue-600 text-white border-blue-600 shadow-xs' 
                            : 'bg-white dark:bg-neutral-900 text-slate-600 dark:text-neutral-300 border-slate-200 dark:border-neutral-800 hover:border-slate-300'">
                    <span x-text="cat.icon"></span>
                    <span x-text="cat.name"></span>
                </button>
            </template>
        </div>

        <!-- Askıda Eşya Toggle'ı -->
        <div class="flex items-center justify-between p-3 rounded-xl bg-emerald-50/60 dark:bg-emerald-950/20 border border-emerald-200/60 dark:border-emerald-900/40">
            <div class="flex items-center gap-2">
                <span class="text-lg">🎁</span>
                <div>
                    <div class="text-xs font-bold text-emerald-900 dark:text-emerald-300">Askıda Eşya / Dayanışma İlanları</div>
                    <div class="text-[11px] text-emerald-700/80 dark:text-emerald-400/80">Mahallelinin ücretsiz paylaştığı eşyaları göster</div>
                </div>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" x-model="onlyFree" @change="fetchItems(true)" class="sr-only peer">
                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-neutral-800 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
            </label>
        </div>
    </div>

    <!-- İlan Grid Listesi -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3.5 sm:gap-4">
        <template x-for="item in items" :key="item.id">
            <a :href="`/marketplace/${item.id}`"
               class="bg-white dark:bg-neutral-900 rounded-2xl border border-slate-200 dark:border-neutral-800 overflow-hidden shadow-xs hover:border-blue-400 dark:hover:border-blue-600 transition flex flex-col group">
                
                <!-- İlan Görseli -->
                <div class="aspect-[4/3] bg-slate-100 dark:bg-neutral-800 relative overflow-hidden flex items-center justify-center">
                    <template x-if="item.thumbnail_url">
                        <img :src="item.thumbnail_url" :alt="item.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    </template>
                    <template x-if="!item.thumbnail_url">
                        <span class="text-3xl opacity-30">📦</span>
                    </template>

                    <!-- Fiyat Rozeti -->
                    <div class="absolute bottom-2 left-2">
                        <span class="px-2.5 py-1 rounded-lg text-xs font-extrabold text-white shadow-md backdrop-blur-md"
                              :class="item.is_free ? 'bg-emerald-600' : 'bg-slate-900/90 dark:bg-blue-600'">
                            <span x-text="item.formatted_price"></span>
                        </span>
                    </div>

                    <!-- Kategori Rozeti -->
                    <div class="absolute top-2 right-2">
                        <span class="px-2 py-0.5 rounded-md text-[9px] font-bold uppercase bg-black/60 text-white backdrop-blur-sm"
                              x-text="item.category_label"></span>
                    </div>
                </div>

                <!-- İlan Bilgileri -->
                <div class="p-3 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors"
                            x-text="item.title"></h3>
                    </div>

                    <div class="mt-3 pt-2 border-t border-slate-100 dark:border-neutral-800/80 flex items-center justify-between text-[11px] text-slate-500 dark:text-neutral-400">
                        <!-- Satıcı ve İtibar Rozeti -->
                        <div class="flex items-center gap-1 truncate max-w-[120px]">
                            <span class="truncate" x-text="item.seller.name"></span>
                            <span x-text="item.seller.reputation.badge_icon || '🌱'"></span>
                        </div>
                        <span class="text-[10px] shrink-0" x-text="item.created_at_human"></span>
                    </div>
                </div>
            </a>
        </template>
    </div>

    <!-- Yükleniyor / Boş Durum -->
    <div x-show="loading" class="py-16 text-center text-slate-400">
        <span class="animate-spin inline-block w-6 h-6 border-2 border-blue-500 border-t-transparent rounded-full mb-2"></span>
        <div class="text-xs">İlanlar yükleniyor...</div>
    </div>

    <div x-show="!loading && items.length === 0" class="py-16 text-center text-slate-400 text-xs" x-cloak>
        <span class="text-3xl block mb-2">📦</span>
        <span>Bu kategoride henüz ilan bulunmuyor. İlk ilanı sen ver!</span>
    </div>

    <!-- Sonsuz Kaydırma -->
    <div x-show="hasMore && !loading" x-intersect="fetchItems(false)" class="py-6 text-center" x-cloak></div>

</div>

<script>
function marketplaceIndex() {
    return {
        locationId: {{ $location->id ?? 1 }},
        items: [],
        nextCursor: null,
        loading: false,
        hasMore: true,
        selectedCategory: 'all',
        onlyFree: false,

        categories: [
            { id: 'all', name: 'Tümü', icon: '✨' },
            { id: 'elektronik', name: 'Elektronik', icon: '📱' },
            { id: 'ev_esyasi', name: 'Ev Eşyası', icon: '🛋️' },
            { id: 'giyim', name: 'Giyim', icon: '👕' },
            { id: 'kitap_hobi', name: 'Kitap & Hobi', icon: '📚' },
            { id: 'diger', name: 'Diğer', icon: '📦' }
        ],

        init() {
            this.fetchItems(true);
        },

        setCategory(catId) {
            this.selectedCategory = catId;
            this.fetchItems(true);
        },

        async fetchItems(isReset = false) {
            if (this.loading || (!isReset && !this.hasMore)) return;
            this.loading = true;

            if (isReset) {
                this.items = [];
                this.nextCursor = null;
                this.hasMore = true;
            }

            let url = `/api/v1/marketplace?location_id=${this.locationId}&category=${this.selectedCategory}&only_free=${this.onlyFree ? 1 : 0}`;
            if (this.nextCursor) {
                url += `&cursor=${encodeURIComponent(this.nextCursor)}`;
            }

            try {
                const res = await fetch(url);
                const data = await res.json();
                if (data.success) {
                    this.items = isReset ? data.items : [...this.items, ...data.items];
                    this.nextCursor = data.next_cursor;
                    this.hasMore = data.has_more;
                }
            } catch (err) {
                console.error('İlan çekme hatası:', err);
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endsection

