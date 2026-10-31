@props(['poll'])

@if(!empty($poll))
<div x-data="{
        poll: @js($poll),
        voting: false,
        async submitVote(optionId) {
            if (this.voting || this.poll.has_voted || this.poll.is_expired) return;
            this.voting = true;
            try {
                const res = await fetch(`/api/v1/polls/${this.poll.id}/vote`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({ option_id: optionId })
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    this.poll = data.poll;
                    $dispatch('toast', 'Oyunuz kaydedildi! +1 Yerel Katılım Puanı 🗳️');
                } else {
                    $dispatch('toast', data.message || 'Oy kullanılamadı.');
                }
            } catch (err) {
                $dispatch('toast', 'Ağ bağlantı hatası oluştu.');
            } finally {
                this.voting = false;
            }
        },
        openCard() {
            window.open(`/polls/${this.poll.id}/card`, '_blank');
        },
        shareOnX() {
            const text = encodeURIComponent('Meydan Yerel Anketi: ' + this.poll.question);
            const cardUrl = encodeURIComponent(window.location.origin + '/polls/' + this.poll.id + '/card');
            window.open(`https://twitter.com/intent/tweet?text=${text}&url=${cardUrl}`, '_blank');
        },
        shareOnWhatsApp() {
            const msg = encodeURIComponent('Meydan Yerel Anket & Halk Oylaması: ' + this.poll.question + '\n\nSonuç İnfografiği: ' + window.location.origin + '/polls/' + this.poll.id + '/card');
            window.open(`https://api.whatsapp.com/send?text=${msg}`, '_blank');
        }
     }"
     class="my-3 p-3.5 sm:p-4 rounded-2xl bg-slate-50 dark:bg-neutral-900/90 border border-slate-200 dark:border-neutral-800 shadow-sm transition-all">

    <!-- Anket Başlığı & Rozet -->
    <div class="flex items-start justify-between gap-2 mb-3">
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                📊 Yerel Anket
            </span>
            <template x-if="poll.is_expired">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-200 text-slate-700 dark:bg-neutral-800 dark:text-neutral-400">
                    Oylama Kapandı
                </span>
            </template>
            <template x-if="!poll.is_expired">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400">
                    Aktif
                </span>
            </template>
        </div>

        <!-- İnfografik Paylaşım / İndir Menüsü -->
        <div class="flex items-center gap-1">
            <button @click="openCard()"
                    type="button"
                    class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 dark:text-neutral-400 dark:hover:text-blue-400 hover:bg-slate-200/60 dark:hover:bg-neutral-800 transition-colors"
                    title="İnfografik Kartını Aç / İndir">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            </button>
            <button @click="shareOnX()"
                    type="button"
                    class="p-1.5 rounded-lg text-slate-500 hover:text-sky-500 dark:text-neutral-400 dark:hover:text-sky-400 hover:bg-slate-200/60 dark:hover:bg-neutral-800 transition-colors"
                    title="X'te Paylaş">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
            </button>
            <button @click="shareOnWhatsApp()"
                    type="button"
                    class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-500 dark:text-neutral-400 dark:hover:text-emerald-400 hover:bg-slate-200/60 dark:hover:bg-neutral-800 transition-colors"
                    title="WhatsApp'ta Paylaş">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
            </button>
        </div>
    </div>

    <!-- Anket Sorusu -->
    <h4 class="text-sm font-bold text-slate-900 dark:text-slate-100 mb-3" x-text="poll.question"></h4>

    <!-- Seçenekler Listesi -->
    <div class="space-y-2">
        <template x-for="option in poll.options" :key="option.id">
            <div>
                <!-- Durum A: Sonuçlar Gösteriliyor (Kullanıcı oy verdi veya süre doldu) -->
                <template x-if="poll.show_results">
                    <div class="relative overflow-hidden rounded-xl border p-2.5 transition-all text-xs"
                         :class="{
                             'border-blue-500 bg-blue-50/60 dark:bg-blue-950/30 dark:border-blue-600': option.is_user_vote,
                             'border-slate-200 dark:border-neutral-800 bg-white dark:bg-neutral-800/50': !option.is_user_vote
                         }">
                        <!-- İlerleme / Yüzde Dolgu Barı -->
                        <div class="absolute inset-y-0 left-0 transition-all duration-500 rounded-l-xl opacity-20"
                             :class="{
                                 'bg-emerald-500': poll.is_expired && option.id === poll.winner_option_id,
                                 'bg-blue-600': !(poll.is_expired && option.id === poll.winner_option_id)
                             }"
                             :style="`width: ${option.percentage}%`">
                        </div>

                        <!-- Seçenek Metni ve Yüzdelik Değer -->
                        <div class="relative z-10 flex items-center justify-between gap-2">
                            <div class="flex items-center gap-1.5 font-semibold text-slate-800 dark:text-slate-200">
                                <span x-text="option.option_text"></span>
                                <template x-if="option.is_user_vote">
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-black bg-blue-600 text-white">
                                        ✓ Oyunuz
                                    </span>
                                </template>
                                <template x-if="poll.is_expired && option.id === poll.winner_option_id && poll.total_votes > 0">
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-black bg-emerald-600 text-white">
                                        🏆 Kazanan
                                    </span>
                                </template>
                            </div>
                            <div class="flex items-center gap-2 font-mono font-bold text-slate-600 dark:text-slate-300">
                                <span x-text="`%${option.percentage}`"></span>
                                <span class="text-[11px] text-slate-400 font-normal" x-text="`(${option.votes_count} oy)`"></span>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Durum B: Oylama Açık ve Henüz Oy Kullanılmamış -->
                <template x-if="!poll.show_results">
                    <button @click="submitVote(option.id)"
                            :disabled="voting"
                            type="button"
                            class="w-full text-left py-2.5 px-3.5 rounded-xl border border-blue-200 dark:border-neutral-700 bg-white dark:bg-neutral-800/80 hover:bg-blue-50 dark:hover:bg-blue-950/40 hover:border-blue-500 text-xs font-semibold text-slate-800 dark:text-slate-200 transition-colors flex items-center justify-between group">
                        <span x-text="option.option_text"></span>
                        <span class="text-blue-500 opacity-0 group-hover:opacity-100 transition-opacity font-bold">
                            Oy Ver →
                        </span>
                    </button>
                </template>
            </div>
        </template>
    </div>

    <!-- Alt Bilgi: Toplam Oy & Kalan Süre -->
    <div class="mt-3 pt-2 border-t border-slate-200/60 dark:border-neutral-800/60 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
        <div class="flex items-center gap-1.5">
            <span class="font-bold text-slate-700 dark:text-slate-300" x-text="`${poll.total_votes} oy`"></span>
            <span>•</span>
            <span x-text="poll.is_expired ? 'Oylama tamamlandı' : `${poll.expires_at_human} sona eriyor`"></span>
        </div>
        <button @click="openCard()"
                type="button"
                class="font-semibold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
            <span>İnfografik Kartı</span>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </button>
    </div>
</div>
@endif

