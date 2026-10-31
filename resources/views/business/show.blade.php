@extends('layouts.app', ['title' => $profile->business_name . ' - Doğrulanmış Mahalle Esnafı', 'maxWidth' => 'max-w-3xl'])

@section('content')
<div class="pb-12" x-data="businessPage({{ $profile->id }})">
    
    <!-- Kapak Görseli -->
    <div class="h-44 sm:h-56 w-full bg-gradient-to-r from-blue-700 via-indigo-600 to-purple-700 relative overflow-hidden">
        @if($profile->cover_url)
            <img src="{{ $mediaService->getUrl($profile->cover_url) }}" alt="{{ $profile->business_name }}" class="w-full h-full object-cover">
        @else
            <div class="absolute inset-0 bg-black/20 flex items-center justify-center">
                <span class="text-white/40 text-4xl font-black">{{ $profile->business_name }}</span>
            </div>
        @endif

        <a href="{{ route('home') }}" class="absolute top-4 left-4 p-2 rounded-full bg-black/50 text-white hover:bg-black/75 transition backdrop-blur-md">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
    </div>

    <!-- Başlık ve Profil Bilgisi -->
    <div class="px-4 relative -mt-12 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            
            <div class="flex items-end gap-3.5">
                <!-- İşletme Logosu -->
                <div class="w-24 h-24 rounded-2xl bg-white dark:bg-neutral-900 border-4 border-white dark:border-black shadow-xl overflow-hidden flex items-center justify-center shrink-0">
                    @if($profile->logo_url)
                        <img src="{{ $mediaService->getUrl($profile->logo_url) }}" alt="{{ $profile->business_name }}" class="w-full h-full object-cover">
                    @else
                        <span class="text-3xl font-black text-blue-600">{{ mb_substr($profile->business_name, 0, 1) }}</span>
                    @endif
                </div>

                <!-- İsim ve Rozet -->
                <div>
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white leading-tight">
                            {{ $profile->business_name }}
                        </h1>

                        <!-- Doğrulanmış Esnaf Rozeti -->
                        @if($profile->is_verified)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300/60 dark:border-emerald-800">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                <span>Doğrulanmış Esnaf</span>
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-neutral-400 mt-1">
                        <span>{{ $profile->category_label }}</span>
                        <span>&bull;</span>
                        <span>📍 {{ $profile->location->district ?? '' }}</span>
                        <span>&bull;</span>
                        <span class="flex items-center text-amber-500 font-bold">
                            ⭐ {{ number_format($profile->rating_avg, 1) }}
                            <span class="text-slate-400 font-normal ml-0.5">({{ $profile->reviews_count }})</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- İşletme Sahibi Yönetim Linki -->
            @if($currentUser && $currentUser->id === $profile->user_id)
                <div class="flex items-center gap-2">
                    <a href="{{ route('business.register') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-neutral-800 text-xs font-bold hover:bg-slate-50 dark:hover:bg-neutral-800 transition">
                        ⚙️ Profili Düzenle
                    </a>
                    @if(!$profile->isSubscriptionActive())
                        <a href="{{ route('business.subscription', $profile->id) }}" class="px-3.5 py-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 text-white text-xs font-black shadow-md transition">
                            ⭐ Doğrulanmış Rozeti Al
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Hızlı Eylem Butonları: WhatsApp Sipariş, Yol Tarifi, Ara -->
    <div class="px-4 mb-8">
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
            @if($profile->whatsapp_order_url)
                <a href="{{ $profile->whatsapp_order_url }}" target="_blank" rel="noopener noreferrer"
                   class="p-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center justify-center gap-2 col-span-2 sm:col-span-1">
                    <span class="text-lg">💬</span>
                    <span>WhatsApp ile Sipariş Ver</span>
                </a>
            @endif

            @if($profile->google_maps_url)
                <a href="{{ $profile->google_maps_url }}" target="_blank" rel="noopener noreferrer"
                   class="p-3 rounded-2xl bg-slate-900 dark:bg-neutral-800 hover:bg-slate-800 text-white font-bold text-xs shadow-sm transition flex items-center justify-center gap-2">
                    <span class="text-lg">📍</span>
                    <span>Yol Tarifi Al</span>
                </a>
            @endif

            <a href="tel:{{ $profile->phone }}"
               class="p-3 rounded-2xl border border-slate-300 dark:border-neutral-700 hover:bg-slate-50 dark:hover:bg-neutral-800 text-slate-800 dark:text-neutral-200 font-bold text-xs transition flex items-center justify-center gap-2">
                <span class="text-lg">📞</span>
                <span>Telefonla Ara</span>
            </a>
        </div>

        <!-- Adres ve Çalışma Bilgisi -->
        <div class="mt-4 p-3.5 rounded-xl bg-slate-50 dark:bg-neutral-900 border border-slate-200/80 dark:border-neutral-800 text-xs text-slate-600 dark:text-neutral-300 space-y-1">
            <div class="flex items-start gap-2">
                <span class="font-bold shrink-0">Adres:</span>
                <span>{{ $profile->address }}</span>
            </div>
            @if(!empty($profile->opening_hours))
                <div class="flex items-start gap-2">
                    <span class="font-bold shrink-0">Çalışma Saatleri:</span>
                    <span>{{ is_array($profile->opening_hours) ? implode(', ', $profile->opening_hours) : $profile->opening_hours }}</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Menü ve Katalog Vitrini -->
    <div class="px-4 mb-10">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 dark:text-white">Menü ve Ürünler</h2>
                <p class="text-xs text-slate-400">İşletmenin güncel ürün ve fiyat listesi</p>
            </div>
            @if($currentUser && $currentUser->id === $profile->user_id)
                <button @click="showAddProductModal = true" class="px-3 py-1.5 rounded-xl bg-blue-600 text-white text-xs font-bold">
                    + Ürün Ekle
                </button>
            @endif
        </div>

        @if($profile->catalogItems->isNotEmpty())
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @foreach($profile->catalogItems as $item)
                    <div class="p-3 rounded-2xl bg-white dark:bg-neutral-900 border border-slate-200 dark:border-neutral-800 shadow-xs flex flex-col justify-between">
                        @if($item->image_url)
                            <div class="aspect-square rounded-xl overflow-hidden bg-slate-100 dark:bg-neutral-800 mb-2">
                                <img src="{{ $mediaService->getUrl($item->image_url) }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
                            </div>
                        @endif
                        <div>
                            <h4 class="font-bold text-xs text-slate-900 dark:text-white line-clamp-1">{{ $item->name }}</h4>
                            @if($item->description)
                                <p class="text-[11px] text-slate-400 line-clamp-2 mt-0.5">{{ $item->description }}</p>
                            @endif
                        </div>
                        <div class="mt-2.5 pt-2 border-t border-slate-100 dark:border-neutral-800 flex items-center justify-between">
                            <span class="font-black text-xs text-emerald-600 dark:text-emerald-400">{{ $item->formatted_price }}</span>
                            @if($profile->whatsapp_order_url)
                                <a href="{{ $profile->whatsapp_order_url }}" target="_blank"
                                   class="text-[10px] font-bold text-blue-600 dark:text-blue-400 hover:underline">Sipariş &rarr;</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 bg-slate-50 dark:bg-neutral-900 rounded-2xl border border-dashed border-slate-200 dark:border-neutral-800 text-xs text-slate-400">
                📦 Henüz menü veya ürün kataloğu eklenmemiş.
            </div>
        @endif
    </div>

    <!-- Müşteri Değerlendirmeleri ve Yorumlar -->
    <div class="px-4">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 dark:text-white">Mahalle Yorumları</h2>
                <p class="text-xs text-slate-400">İlçedeki komşuların tarafsız değerlendirmeleri</p>
            </div>
            <div class="text-right">
                <span class="text-xl font-black text-amber-500">⭐ {{ number_format($profile->rating_avg, 1) }}</span>
                <span class="text-xs text-slate-400 block">{{ $profile->reviews_count }} Değerlendirme</span>
            </div>
        </div>

        <!-- Değerlendirme Bırakma Formu -->
        @auth
            @if(auth()->id() !== $profile->user_id)
                <div class="mb-6 p-4 rounded-2xl bg-white dark:bg-neutral-900 border border-slate-200 dark:border-neutral-800 shadow-sm">
                    <h4 class="text-xs font-bold text-slate-900 dark:text-white mb-2">Bu Esnafa Puan ve Yorum Bırak</h4>
                    
                    <div class="flex items-center gap-2 mb-3">
                        <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                            <button type="button" @click="rating = star" class="text-xl transition-transform hover:scale-125"
                                    :class="star <= rating ? 'text-amber-500' : 'text-slate-300 dark:text-neutral-700'">
                                ★
                            </button>
                        </template>
                        <span class="text-xs text-slate-500 dark:text-neutral-400 font-bold ml-1" x-text="`${rating} / 5 Yıldız`"></span>
                    </div>

                    <textarea x-model="comment" rows="2" placeholder="Hizmet, lezzet veya müşteri ilgisi hakkında düşüncelerini yaz..."
                              class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-xs outline-none focus:ring-2 focus:ring-blue-500 mb-2"></textarea>
                    
                    <div class="flex justify-end">
                        <button type="button" @click="submitReview()" :disabled="!comment.trim() || submitting"
                                class="px-4 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow transition disabled:opacity-50">
                            Değerlendir (+2 İtibar Puanı)
                        </button>
                    </div>
                </div>
            @endif
        @else
            <div class="p-3.5 mb-6 rounded-xl bg-slate-50 dark:bg-neutral-900 border border-slate-200 dark:border-neutral-800 text-xs text-slate-500 text-center">
                Değerlendirme bırakmak için <a href="/login" class="text-blue-600 font-bold hover:underline">giriş yapın</a>.
            </div>
        @endauth

        <!-- Yorumlar Listesi -->
        <div class="space-y-3">
            @forelse($profile->reviews as $rev)
                <div class="p-4 rounded-xl bg-white dark:bg-neutral-900 border border-slate-100 dark:border-neutral-800">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full bg-blue-600/20 text-blue-600 font-bold flex items-center justify-center text-xs">
                                {{ mb_substr($rev->user->name ?? 'K', 0, 1) }}
                            </div>
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold text-xs text-slate-900 dark:text-white">{{ $rev->user->name }}</span>
                                    <x-reputation-badge :reputation="$rev->user->reputation" :showPoints="false" />
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center text-amber-500 text-xs">
                            @for($i = 1; $i <= 5; $i++)
                                <span>{{ $i <= $rev->rating ? '★' : '☆' }}</span>
                            @endfor
                        </div>
                    </div>

                    <p class="text-xs text-slate-700 dark:text-neutral-300 leading-relaxed">{{ $rev->comment }}</p>
                    <span class="text-[10px] text-slate-400 block mt-1.5 text-right">{{ $rev->created_at->diffForHumans() }}</span>
                </div>
            @empty
                <div class="text-center py-6 text-xs text-slate-400">
                    Henüz yorum yapılmamış. İlk değerlendiren siz olun!
                </div>
            @endforelse
        </div>
    </div>

    <!-- Ürün Ekleme Modalı (Sadece İşletme Sahibi İçin) -->
    <div x-show="showAddProductModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
        <div @click.away="showAddProductModal = false"
             class="w-full max-w-md bg-white dark:bg-neutral-900 rounded-2xl border border-slate-200 dark:border-neutral-800 p-5 shadow-2xl">
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">Menüye Yeni Ürün Ekle</h3>
            
            <form @submit.prevent="submitProduct" class="space-y-3.5 text-xs">
                <div>
                    <label class="block font-bold mb-1">Ürün Adı *</label>
                    <input type="text" x-model="productForm.name" required placeholder="Örn: Karışık Izgara Tabağı"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800">
                </div>
                <div>
                    <label class="block font-bold mb-1">Fiyat (TL) *</label>
                    <input type="number" step="0.5" x-model="productForm.price" required placeholder="240"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800">
                </div>
                <div>
                    <label class="block font-bold mb-1">Açıklama (İsteğe bağlı)</label>
                    <textarea x-model="productForm.description" rows="2" placeholder="Porsiyon içeriği, gramaj vb."
                              class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showAddProductModal = false" class="px-4 py-2 rounded-xl border">İptal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 text-white font-bold">Kaydet</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function businessPage(profileId) {
    return {
        profileId: profileId,
        rating: 5,
        comment: '',
        submitting: false,
        showAddProductModal: false,
        productForm: { name: '', price: '', description: '' },

        async submitReview() {
            if (!this.comment.trim() || this.submitting) return;
            this.submitting = true;

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            try {
                const res = await fetch(`/api/v1/business/${this.profileId}/reviews`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ rating: this.rating, comment: this.comment })
                });

                const data = await res.json();
                if (data.success) {
                    $dispatch('toast', data.message);
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    alert(data.message || 'Hata oluştu');
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.submitting = false;
            }
        },

        async submitProduct() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            try {
                const res = await fetch(`/api/v1/business/${this.profileId}/catalog`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.productForm)
                });

                const data = await res.json();
                if (data.success) {
                    window.location.reload();
                }
            } catch (e) {
                console.error(e);
            }
        }
    }
}
</script>
@endsection

