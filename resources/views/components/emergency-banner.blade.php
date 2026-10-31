@props(['locationId' => 1])

<div x-data="{
         alerts: [],
         loading: true,
         async init() {
             try {
                 const res = await fetch(`/api/v1/emergency/alerts?location_id={{ $locationId }}`);
                 const data = await res.json();
                 if (data.success && data.alerts) {
                     this.alerts = data.alerts;
                 }
             } catch (e) {
                 console.error('Acil durum kontrol hatası:', e);
             } finally {
                 this.loading = false;
             }
         }
     }"
     x-show="alerts.length > 0"
     x-cloak
     class="sticky top-12 z-40 w-full mb-3">

    <template x-for="alert in alerts" :key="alert.id">
        <div class="relative overflow-hidden shadow-lg border-y"
             :class="{
                 'bg-gradient-to-r from-red-600 via-rose-600 to-red-700 text-white border-red-800': alert.alert_type === 'afet_yardim' || alert.alert_type === 'kan_bagisi',
                 'bg-gradient-to-r from-amber-600 via-orange-600 to-amber-700 text-white border-amber-800': alert.alert_type === 'kayip_ilan' || alert.alert_type === 'guvenlik_uyari'
             }">
            
            <!-- Arka Plan Yanıp Sönen Uyarı Efekti (Pulsing Glow) -->
            <div class="absolute inset-0 bg-white/10 animate-pulse pointer-events-none"></div>

            <div class="p-3 sm:p-3.5 relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5">
                
                <!-- Sol Kısım: İkon, Başlık, Tür ve Açıklama Özeti -->
                <div class="flex items-start gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center text-lg shrink-0 shadow-xs">
                        <span x-text="alert.icon"></span>
                    </div>

                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-black/30 backdrop-blur-md text-white border border-white/20">
                                🚨 <span x-text="alert.type_label"></span>
                            </span>
                            <span class="text-[11px] opacity-80" x-text="alert.created_at_human"></span>
                        </div>
                        <h3 class="text-xs sm:text-sm font-extrabold tracking-tight truncate mt-0.5" x-text="alert.title"></h3>
                        <p class="text-[11px] opacity-90 line-clamp-1 mt-0.5" x-text="alert.description"></p>
                    </div>
                </div>

                <!-- Sağ Kısım: Hızlı Arama Butonu ve Detay Linki -->
                <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
                    <template x-if="alert.contact_phone">
                        <a :href="`tel:${alert.contact_phone}`"
                           class="px-3.5 py-1.5 rounded-xl bg-white text-rose-700 hover:bg-slate-100 font-extrabold text-xs shadow-md transition flex items-center gap-1 shrink-0">
                            <span>📞</span>
                            <span x-text="alert.contact_phone"></span>
                        </a>
                    </template>

                    <a :href="`/emergency/${alert.id}`"
                       class="px-3 py-1.5 rounded-xl bg-black/40 hover:bg-black/60 text-white font-bold text-xs border border-white/25 transition">
                        Detay
                    </a>
                </div>

            </div>
        </div>
    </template>
</div>

