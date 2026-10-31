@props(['locationId' => 1, 'districtName' => 'Bölgeniz'])

<div class="py-3 px-4 border-b border-indigo-100 dark:border-indigo-950/50 bg-gradient-to-r from-indigo-500/[0.04] via-blue-500/[0.02] to-transparent"
     x-data="{
         items: [],
         loading: true,
         async init() {
             try {
                 const res = await fetch(`/api/v1/marketplace/carousel?location_id={{ $locationId }}`);
                 const data = await res.json();
                 if (data.success) {
                     this.items = data.items;
                 }
             } catch (e) {
                 console.error('Pazar karusel hatası:', e);
             } finally {
                 this.loading = false;
             }
         }
     }"
     x-show="items.length > 0"
     x-cloak>

    <!-- Karusel Başlığı -->
    <div class="flex items-center justify-between mb-2.5">
        <div class="flex items-center gap-1.5">
            <span class="text-base">🛍️</span>
            <span class="text-xs font-extrabold uppercase tracking-wider text-indigo-700 dark:text-indigo-400">
                Mahalle Pazarı &bull; {{ $districtName }}
            </span>
        </div>
        <a href="{{ route('marketplace.index') }}" class="text-[11px] font-bold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-0.5">
            <span>Tümünü Gör</span>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
        </a>
    </div>

    <!-- Yatay Kaydırılabilir Kartlar -->
    <div class="flex gap-3 overflow-x-auto no-scrollbar pb-1 snap-x snap-mandatory">
        <template x-for="item in items" :key="item.id">
            <a :href="`/marketplace/${item.id}`"
               class="snap-start shrink-0 w-36 sm:w-40 bg-white dark:bg-neutral-900 rounded-xl border border-slate-200 dark:border-neutral-800 overflow-hidden shadow-xs hover:border-indigo-400 dark:hover:border-indigo-600 transition flex flex-col group">
                
                <!-- Görsel -->
                <div class="h-24 sm:h-28 bg-slate-100 dark:bg-neutral-800 relative overflow-hidden flex items-center justify-center">
                    <template x-if="item.image_url">
                        <img :src="item.image_url" :alt="item.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    </template>
                    <template x-if="!item.image_url">
                        <span class="text-2xl opacity-40">📦</span>
                    </template>

                    <!-- Fiyat / Askıda Rozeti -->
                    <div class="absolute bottom-1.5 left-1.5">
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-black text-white shadow-md backdrop-blur-md"
                              :class="item.is_free ? 'bg-emerald-600' : 'bg-slate-900/85'">
                            <span x-text="item.price_formatted"></span>
                        </span>
                    </div>
                </div>

                <!-- Başlık ve Kategori -->
                <div class="p-2 flex-1 flex flex-col justify-between">
                    <div>
                        <span class="text-[9px] font-semibold text-slate-400 block truncate" x-text="item.category_label"></span>
                        <h4 class="text-xs font-bold text-slate-900 dark:text-white truncate mt-0.5" x-text="item.title"></h4>
                    </div>
                    <div class="text-[9px] text-slate-400 mt-1 flex items-center justify-between">
                        <span class="truncate" x-text="item.seller.name"></span>
                        <span x-text="item.created_at_human"></span>
                    </div>
                </div>
            </a>
        </template>
    </div>
</div>

