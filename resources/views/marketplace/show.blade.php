@extends('layouts.app', ['title' => $item->title . ' - Mahalle Pazarı', 'maxWidth' => 'max-w-2xl'])

@section('content')
<div class="px-4 py-5" x-data="itemDetail({{ $item->id }}, {{ $item->user_id }})">
    
    <!-- Üst Gezinti -->
    <div class="flex items-center justify-between border-b border-slate-200 dark:border-neutral-800 pb-3 mb-4">
        <a href="{{ route('marketplace.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:hover:text-white transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Mahalle Pazarına Dön</span>
        </a>

        <!-- Durum Rozeti -->
        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold"
              :class="{
                  'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300': currentStatus === 'active',
                  'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300': currentStatus === 'reserved',
                  'bg-slate-200 text-slate-700 dark:bg-neutral-800 dark:text-neutral-400': currentStatus === 'sold'
              }"
              x-text="statusLabel">
            {{ $item->status_label }}
        </span>
    </div>

    <!-- Fotoğraf Galerisi -->
    <div class="rounded-2xl overflow-hidden bg-slate-100 dark:bg-neutral-800 border border-slate-200 dark:border-neutral-800 mb-5 relative">
        @if(!empty($imageUrls))
            <div class="aspect-[16/10] relative">
                <img :src="activeImage" alt="{{ $item->title }}" class="w-full h-full object-cover">
                
                @if(count($imageUrls) > 1)
                    <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex items-center gap-1.5 p-1 rounded-full bg-black/50 backdrop-blur-md">
                        @foreach($imageUrls as $idx => $url)
                            <button @click="activeImage = '{{ $url }}'" type="button"
                                    class="w-2.5 h-2.5 rounded-full transition-all"
                                    :class="activeImage === '{{ $url }}' ? 'bg-white scale-125' : 'bg-white/40'"></button>
                        @endforeach
                    </div>
                @endif
            </div>
        @else
            <div class="aspect-[16/10] flex items-center justify-center text-slate-400 text-sm">
                <span>Fotoğraf eklenmemiş</span>
            </div>
        @endif
    </div>

    <!-- Başlık ve Fiyat -->
    <div class="flex items-start justify-between gap-4 mb-4">
        <div>
            <span class="text-xs font-semibold text-slate-500 dark:text-neutral-400 block mb-1">
                {{ $item->category_label }} &bull; 📍 {{ $item->location->district ?? '' }}
            </span>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white leading-tight">
                {{ $item->title }}
            </h1>
        </div>

        <div class="text-right shrink-0">
            <span class="px-3.5 py-1.5 rounded-xl text-base font-black text-white shadow-md inline-block"
                  class="{{ $item->is_free ? 'bg-emerald-600' : 'bg-blue-600' }}">
                {{ $item->formatted_price }}
            </span>
        </div>
    </div>

    <!-- Satıcı Bilgi Kartı -->
    <div class="p-4 rounded-xl bg-slate-50 dark:bg-neutral-800/50 border border-slate-200 dark:border-neutral-800 flex items-center justify-between mb-5">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-500 text-white font-bold flex items-center justify-center text-sm shadow-sm shrink-0">
                {{ mb_substr($item->user->name ?? 'U', 0, 1) }}
            </div>
            <div>
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="font-bold text-sm text-slate-900 dark:text-white">{{ $item->user->name }}</span>
                    <x-reputation-badge :reputation="$item->user->reputation" />
                </div>
                <div class="text-[11px] text-slate-400 mt-0.5">
                    Mahalle Sakini &bull; {{ $item->created_at->diffForHumans() }}
                </div>
            </div>
        </div>

        <!-- Eğer İlan Sahibi İse Durum Değiştirici -->
        @if($currentUser && $currentUser->id === $item->user_id)
            <div class="relative" x-data="{ openMenu: false }">
                <button @click="openMenu = !openMenu" type="button"
                        class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-neutral-700 text-xs font-semibold hover:bg-white dark:hover:bg-neutral-800 transition">
                    Durumu Değiştir &dtrif;
                </button>
                <div x-show="openMenu" @click.away="openMenu = false" x-cloak
                     class="absolute right-0 mt-1 w-36 bg-white dark:bg-neutral-900 border border-slate-200 dark:border-neutral-800 rounded-xl shadow-xl py-1 z-10 text-xs font-medium">
                    <button @click="changeStatus('active'); openMenu = false" class="w-full text-left px-3 py-2 hover:bg-slate-50 dark:hover:bg-neutral-800">✅ Yayında</button>
                    <button @click="changeStatus('reserved'); openMenu = false" class="w-full text-left px-3 py-2 hover:bg-slate-50 dark:hover:bg-neutral-800">⏳ Rezerve Et</button>
                    <button @click="changeStatus('sold'); openMenu = false" class="w-full text-left px-3 py-2 hover:bg-slate-50 dark:hover:bg-neutral-800 text-emerald-600">🎉 Satıldı</button>
                </div>
            </div>
        @endif
    </div>

    <!-- Açıklama Metni -->
    <div class="mb-8">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-neutral-500 mb-2">Eşya Detayları</h3>
        <p class="text-sm leading-relaxed text-slate-800 dark:text-neutral-200 whitespace-pre-line bg-white dark:bg-neutral-900 p-4 rounded-xl border border-slate-100 dark:border-neutral-800">
            {{ $item->description }}
        </p>
    </div>

    <!-- Alt İletişim Butonu (Telefon paylaşmadan şifreli platform sohbeti) -->
    @if(!$currentUser || $currentUser->id !== $item->user_id)
        <div class="sticky bottom-4">
            <button @click="openChat()"
                    class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-extrabold text-sm shadow-xl transition flex items-center justify-center gap-2">
                <span>💬 Satıcıya Güvenli Mesaj Gönder</span>
            </button>
            <div class="text-center text-[10px] text-slate-400 mt-2">
                🛡️ Telefon numaranız gizli tutulur. İletişim platform içi güvenli sohbetle sağlanır.
            </div>
        </div>
    @endif

    <!-- Sohbet Çekmecesi / Modalı -->
    <div x-show="isChatOpen" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
        
        <div @click.away="isChatOpen = false"
             class="w-full max-w-lg bg-white dark:bg-neutral-900 rounded-2xl border border-slate-200 dark:border-neutral-800 shadow-2xl flex flex-col h-[560px] overflow-hidden">
            
            <!-- Sohbet Üst Barı -->
            <div class="p-3.5 border-b border-slate-100 dark:border-neutral-800 flex items-center justify-between bg-slate-50 dark:bg-neutral-800/50">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center text-xs">
                        {{ mb_substr($item->user->name ?? 'U', 0, 1) }}
                    </div>
                    <div>
                        <div class="text-xs font-bold text-slate-900 dark:text-white">{{ $item->user->name }}</div>
                        <div class="text-[10px] text-slate-400 truncate max-w-[200px]">{{ $item->title }}</div>
                    </div>
                </div>
                <button @click="isChatOpen = false" class="p-1 text-slate-400 hover:text-slate-600 font-bold">&times;</button>
            </div>

            <!-- Mesaj Listesi -->
            <div class="flex-1 p-4 overflow-y-auto space-y-3" id="messageContainer">
                <template x-for="msg in chatMessages" :key="msg.id">
                    <div class="flex" :class="msg.is_mine ? 'justify-end' : 'justify-start'">
                        <div class="max-w-[75%] px-3.5 py-2 rounded-2xl text-xs"
                             :class="msg.is_mine 
                                 ? 'bg-blue-600 text-white rounded-br-xs' 
                                 : 'bg-slate-100 dark:bg-neutral-800 text-slate-800 dark:text-neutral-100 rounded-bl-xs'">
                            <p class="whitespace-pre-line" x-text="msg.content"></p>
                            <span class="text-[9px] opacity-70 block text-right mt-1" x-text="msg.created_at_human"></span>
                        </div>
                    </div>
                </template>
                <div x-show="chatMessages.length === 0" class="text-center text-xs text-slate-400 py-12">
                    👋 Eşya hakkında soru sormak veya teslimat saatini belirlemek için ilk mesajı yazın.
                </div>
            </div>

            <!-- Mesaj Gönderme Formu -->
            <div class="p-3 border-t border-slate-100 dark:border-neutral-800 flex items-center gap-2">
                <input type="text" x-model="newMessage" @keydown.enter.prevent="sendMessage()"
                       placeholder="Mesajınızı yazın..."
                       class="flex-1 px-3.5 py-2 rounded-xl border border-slate-300 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-900 dark:text-white text-xs outline-none focus:ring-2 focus:ring-blue-500">
                <button @click="sendMessage()" :disabled="!newMessage.trim() || sending"
                        class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-bold text-xs shadow transition">
                    Gönder
                </button>
            </div>
        </div>
    </div>

</div>

<script>
function itemDetail(itemId, sellerId) {
    return {
        itemId: itemId,
        activeImage: '{{ $imageUrls[0] ?? '' }}',
        currentStatus: '{{ $item->status }}',
        statusLabel: '{{ $item->status_label }}',
        isChatOpen: false,
        conversationId: null,
        chatMessages: [],
        newMessage: '',
        sending: false,

        async changeStatus(newStat) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            try {
                const res = await fetch(`/api/v1/marketplace/${this.itemId}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status: newStat })
                });
                const data = await res.json();
                if (data.success) {
                    this.currentStatus = data.status;
                    this.statusLabel = newStat === 'active' ? 'Yayında' : (newStat === 'reserved' ? 'Rezerve Edildi' : 'Satıldı');
                    $dispatch('toast', 'İlan durumu güncellendi.');
                }
            } catch (e) {
                console.error(e);
            }
        },

        async openChat() {
            @guest
                window.location.href = '/login';
                return;
            @endguest

            this.isChatOpen = true;

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                // 1. Sohbet Başlat veya Mevcut Olanı Al
                const res = await fetch(`/api/v1/chat/start/${this.itemId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    this.conversationId = data.conversation_id;
                    await this.loadMessages();
                }
            } catch (e) {
                console.error('Sohbet başlatma hatası:', e);
            }
        },

        async loadMessages() {
            if (!this.conversationId) return;
            try {
                const res = await fetch(`/api/v1/chat/conversations/${this.conversationId}/messages`);
                const data = await res.json();
                if (data.success) {
                    this.chatMessages = data.messages;
                    this.$nextTick(() => {
                        const el = document.getElementById('messageContainer');
                        if (el) el.scrollTop = el.scrollHeight;
                    });
                }
            } catch (e) {
                console.error(e);
            }
        },

        async sendMessage() {
            if (!this.newMessage.trim() || !this.conversationId || this.sending) return;
            this.sending = true;

            const content = this.newMessage.trim();
            this.newMessage = '';

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const res = await fetch(`/api/v1/chat/conversations/${this.conversationId}/messages`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ content: content })
                });

                const data = await res.json();
                if (data.success) {
                    this.chatMessages.push(data.message);
                    this.$nextTick(() => {
                        const el = document.getElementById('messageContainer');
                        if (el) el.scrollTop = el.scrollHeight;
                    });
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.sending = false;
            }
        }
    }
}
</script>
@endsection

