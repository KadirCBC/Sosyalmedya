@props(['locationId' => 1, 'districtName' => 'Mahalleniz'])

<div class="py-3 px-4 border-b border-amber-200/60 dark:border-amber-950/40 bg-gradient-to-r from-amber-500/[0.06] via-orange-500/[0.02] to-transparent"
     x-data="{
         events: [],
         loading: true,
         async init() {
             try {
                 const res = await fetch(`/api/v1/events/weekend?location_id={{ $locationId }}`);
                 const data = await res.json();
                 if (data.success) {
                     this.events = data.events;
                 }
             } catch (e) {
                 console.error('Hafta sonu etkinlikleri yüklenemedi:', e);
             } finally {
                 this.loading = false;
             }
         }
     }"
     x-show="events.length > 0"
     x-cloak>

    <!-- Widget Başlığı -->
    <div class="flex items-center justify-between mb-2.5">
        <div class="flex items-center gap-1.5">
            <span class="text-base">📅</span>
            <span class="text-xs font-black uppercase tracking-wider text-amber-700 dark:text-amber-400">
                Bu Hafta Sonu Mahallede Ne Var?
            </span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('events.create') }}" class="text-[11px] font-bold text-amber-600 dark:text-amber-400 hover:underline">
                + Buluşma Başlat
            </a>
            <span class="text-slate-300 dark:text-neutral-700">•</span>
            <a href="{{ route('events.index') }}" class="text-[11px] font-bold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-0.5">
                <span>Tümü</span>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>

    <!-- Yatay Kaydırılabilir Etkinlik Kartları -->
    <div class="flex gap-3 overflow-x-auto no-scrollbar pb-1 snap-x snap-mandatory">
        <template x-for="event in events" :key="event.id">
            <a :href="`/events/${event.id}`"
               class="snap-start shrink-0 w-56 sm:w-64 bg-white dark:bg-neutral-900 rounded-2xl border border-slate-200 dark:border-neutral-800 p-3 shadow-xs hover:border-amber-400 dark:hover:border-amber-600 transition flex flex-col justify-between group">
                
                <div>
                    <!-- Kategori & Sponsor Rozeti -->
                    <div class="flex items-center justify-between gap-1 mb-1.5">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-neutral-800 text-slate-700 dark:text-slate-300">
                            <span x-text="
                                event.category === 'spor' ? '⚽ Spor' :
                                event.category === 'kultur_sanat' ? '🎨 Kültür' :
                                event.category === 'yardimlasma' ? '🤝 Dayanışma' :
                                event.category === 'belediye_duyuru' ? '🏛️ Resmi' : '☕ Buluşma'
                            "></span>
                        </span>
                        
                        <!-- Sponsorlu Buluşma Noktası Rozeti -->
                        <template x-if="event.is_sponsored_venue">
                            <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-300 dark:border-amber-800" title="Yerel Esnaf Buluşma Noktası">
                                <span>⭐ Sponsorlu</span>
                            </span>
                        </template>
                    </div>

                    <!-- Etkinlik Başlığı -->
                    <h4 class="text-xs font-bold text-slate-900 dark:text-white line-clamp-2 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors" x-text="event.title"></h4>
                    
                    <!-- Mekan & Zaman -->
                    <div class="mt-2 space-y-0.5 text-[11px] text-slate-500 dark:text-neutral-400">
                        <div class="flex items-center gap-1 truncate">
                            <span>📍</span>
                            <span class="font-medium truncate" x-text="event.venue_name"></span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span>⏰</span>
                            <span x-text="new Date(event.event_date).toLocaleString('tr-TR', { weekday: 'short', day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })"></span>
                        </div>
                    </div>
                </div>

                <!-- Alt Kısım: Katılımcı Sayısı & Katıl Butonu -->
                <div class="mt-3 pt-2 border-t border-slate-100 dark:border-neutral-800/80 flex items-center justify-between">
                    <span class="text-[11px] font-semibold text-slate-600 dark:text-slate-300" x-text="`${event.participants_count} komşu katılıyor`"></span>
                    <span class="text-xs font-bold text-blue-600 dark:text-blue-400 group-hover:translate-x-0.5 transition-transform">İncele →</span>
                </div>
            </a>
        </template>
    </div>
</div>

