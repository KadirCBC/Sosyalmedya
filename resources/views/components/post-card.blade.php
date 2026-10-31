@props(['post'])

<article class="p-4 border-b border-slate-100 dark:border-neutral-800/80 hover:bg-slate-50/50 dark:hover:bg-neutral-900/30 transition-colors"
         x-data="{
             liked: {{ ($post['is_liked'] ?? false) ? 'true' : 'false' }},
             likesCount: {{ (int) ($post['likes_count'] ?? 0) }},
             isAnimating: false,
             async toggleLike(postId) {
                 this.isAnimating = true;
                 setTimeout(() => this.isAnimating = false, 350);
                 
                 // İyimser UI güncellemesi
                 if (this.liked) {
                     this.liked = false;
                     this.likesCount--;
                 } else {
                     this.liked = true;
                     this.likesCount++;
                 }

                 try {
                     const res = await fetch(`/api/v1/posts/${postId}/like`, {
                         method: 'POST',
                         headers: {
                             'Accept': 'application/json',
                             'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                         }
                     });
                     if (res.ok) {
                         const data = await res.json();
                         this.liked = data.is_liked;
                         this.likesCount = data.likes_count;
                     }
                 } catch (e) {
                     // Hata durumunda geri al
                     console.error('Like toggle hatası:', e);
                 }
             },
             copyLink(postId) {
                 const url = window.location.origin + '/posts/' + postId;
                 navigator.clipboard.writeText(url);
                 $dispatch('toast', 'Gönderi bağlantısı kopyalandı! 🔗');
             }
         }">
    
    <div class="flex items-start gap-3">
        <!-- Yazar Avatarı -->
        <a href="#" class="shrink-0">
            @if(!empty($post['user']['avatar_url']))
                <img src="{{ $post['user']['avatar_url'] }}" alt="{{ $post['user']['name'] ?? 'Kullanıcı' }}" class="w-10 h-10 rounded-full object-cover ring-1 ring-slate-200 dark:ring-neutral-700">
            @else
                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-500 text-white font-semibold flex items-center justify-center text-sm shadow-sm">
                    {{ mb_substr($post['user']['name'] ?? 'U', 0, 1) }}
                </div>
            @endif
        </a>

        <!-- Gönderi Gövdesi -->
        <div class="flex-1 min-w-0">
            <!-- Üst Başlık Bilgisi: İsim, Kullanıcı Adı, Zaman, Konum -->
            <div class="flex items-center justify-between gap-2 mb-1">
                <div class="flex items-center gap-1.5 flex-wrap leading-tight">
                    <span class="font-bold text-[15px] text-slate-900 dark:text-white truncate">
                        {{ $post['user']['name'] ?? 'İsimsiz' }}
                    </span>
                    <span class="text-xs text-slate-500 dark:text-neutral-400">
                        {{ '@' . ($post['user']['username'] ?? 'kullanici') }}
                    </span>
                    <!-- Yerel İtibar ve Seviye Rozeti -->
                    <x-reputation-badge :reputation="$post['user']['reputation'] ?? null" />
                    <span class="text-xs text-slate-400 dark:text-neutral-500">·</span>
                    <time class="text-xs text-slate-500 dark:text-neutral-400">
                        {{ $post['created_at_human'] ?? 'Şimdi' }}
                    </time>
                </div>

                <!-- Konum Rozeti (Yalnızca Yerel Gönderilerde) -->
                @if(($post['scope'] ?? '') === 'local' && !empty($post['location']))
                    <span class="shrink-0 inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60">
                        <span>📍</span>
                        <span>{{ $post['location']['district'] ?? $post['location']['city'] }}</span>
                    </span>
                @endif
            </div>

            <!-- Gönderi İçeriği Metni -->
            <p class="text-[15px] leading-relaxed text-slate-800 dark:text-neutral-200 whitespace-pre-line break-words mb-3">
                {{ $post['content'] ?? '' }}
            </p>

            <!-- Medya Galerisi (Tekli, 2'li veya 4'lü Grid) -->
            @if(!empty($post['media_urls']) && is_array($post['media_urls']))
                @php $mediaCount = count($post['media_urls']); @endphp
                
                <div class="mb-3 rounded-2xl overflow-hidden border border-slate-200/80 dark:border-neutral-800 bg-slate-100 dark:bg-neutral-900">
                    @if($mediaCount === 1)
                        <!-- Tek Görsel -->
                        <img src="{{ $post['media_urls'][0] }}" alt="Medya" class="w-full max-h-[420px] object-cover hover:opacity-95 transition-opacity cursor-pointer">
                    @elseif($mediaCount === 2)
                        <!-- 2'li Grid -->
                        <div class="grid grid-cols-2 gap-0.5 aspect-[16/9]">
                            @foreach($post['media_urls'] as $img)
                                <img src="{{ $img }}" alt="Medya" class="w-full h-full object-cover hover:opacity-95 transition-opacity cursor-pointer">
                            @endforeach
                        </div>
                    @else
                        <!-- 3 veya 4'lü Grid -->
                        <div class="grid grid-cols-2 gap-0.5 aspect-[16/10]">
                            @foreach(array_slice($post['media_urls'], 0, 4) as $img)
                                <img src="{{ $img }}" alt="Medya" class="w-full h-full object-cover hover:opacity-95 transition-opacity cursor-pointer">
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            <!-- Alt Etkileşim Butonları (X / Twitter Stili) -->
            <div class="flex items-center justify-between max-w-md pt-1 text-slate-500 dark:text-neutral-400">
                <!-- Beğeni Butonu (Kalp Pop Animasyonu ile) -->
                <button @click="toggleLike({{ $post['id'] }})"
                        type="button"
                        class="group flex items-center gap-1.5 text-xs transition-colors hover:text-rose-600 focus:outline-none"
                        :class="{ 'text-rose-600 font-semibold': liked }">
                    <div class="p-2 rounded-full group-hover:bg-rose-50 dark:group-hover:bg-rose-950/30 transition-colors"
                         :class="{ 'animate-heart': isAnimating }">
                        <!-- Boş / Dolu Kalp SVG -->
                        <svg class="w-4 h-4 transition-transform group-hover:scale-110" 
                             :fill="liked ? 'currentColor' : 'none'" 
                             stroke="currentColor" 
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </div>
                    <span x-text="likesCount"></span>
                </button>

                <!-- Yorum Butonu -->
                <a href="#comments-{{ $post['id'] }}" class="group flex items-center gap-1.5 text-xs transition-colors hover:text-blue-500 focus:outline-none">
                    <div class="p-2 rounded-full group-hover:bg-blue-50 dark:group-hover:bg-blue-950/30 transition-colors">
                        <svg class="w-4 h-4 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <span>{{ (int) ($post['comments_count'] ?? 0) }}</span>
                </a>

                <!-- Paylaş / Bağlantı Kopyala -->
                <button @click="copyLink({{ $post['id'] }})"
                        type="button"
                        class="group flex items-center gap-1.5 text-xs transition-colors hover:text-emerald-500 focus:outline-none">
                    <div class="p-2 rounded-full group-hover:bg-emerald-50 dark:group-hover:bg-emerald-950/30 transition-colors">
                        <svg class="w-4 h-4 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                        </svg>
                    </div>
                    <span class="hidden sm:inline">Paylaş</span>
                </button>
            </div>
        </div>
    </div>
</article>

