<x-app-layout>
    <div x-data="{
        activeTab: 'local', // 'local' veya 'general'
        districtName: '{{ $districtName ?? 'Kadıköy' }}',
        locationId: {{ $locationId ?? 1 }},
        posts: [],
        nextCursor: null,
        loading: false,
        hasMore: true,
        
        // Hızlı Paylaşım Kutusu State
        content: '',
        scope: 'local', // 'local' veya 'general'
        selectedFiles: [],
        previewUrls: [],
        posting: false,
        maxChars: 500,

        get remainingChars() {
            return this.maxChars - this.content.length;
        },

        init() {
            this.fetchFeed(true);
        },

        switchTab(tab) {
            if (this.activeTab === tab) return;
            this.activeTab = tab;
            this.scope = tab === 'local' ? 'local' : 'general';
            this.posts = [];
            this.nextCursor = null;
            this.hasMore = true;
            this.fetchFeed(true);
        },

        async fetchFeed(isFirst = false) {
            if (this.loading || (!isFirst && !this.hasMore)) return;
            this.loading = true;

            const endpoint = this.activeTab === 'local' 
                ? `/api/v1/feed/local?location_id=${this.locationId}` 
                : '/api/v1/feed/general';
            
            const url = this.nextCursor ? `${endpoint}&cursor=${encodeURIComponent(this.nextCursor)}` : endpoint;

            try {
                const res = await fetch(url, { credentials: 'same-origin' });
                const json = await res.json();
                
                const newPosts = json.data || [];
                this.posts = isFirst ? newPosts : [...this.posts, ...newPosts];
                this.nextCursor = json.next_cursor || null;
                this.hasMore = Boolean(this.nextCursor);
            } catch (err) {
                console.error('Akış yükleme hatası:', err);
            } finally {
                this.loading = false;
            }
        },

        handleFileSelect(e) {
            const files = Array.from(e.target.files);
            if (this.selectedFiles.length + files.length > 4) {
                $dispatch('toast', 'En fazla 4 adet görsel seçebilirsiniz.');
                return;
            }

            for (const file of files) {
                this.selectedFiles.push(file);
                this.previewUrls.push(URL.createObjectURL(file));
            }
        },

        removeFile(index) {
            this.selectedFiles.splice(index, 1);
            this.previewUrls.splice(index, 1);
        },

        async submitPost() {
            if (!this.content.trim() && this.selectedFiles.length === 0) return;
            if (this.content.length > this.maxChars) return;
            this.posting = true;

            const formData = new FormData();
            formData.append('content', this.content);
            formData.append('scope', this.scope);
            if (this.scope === 'local') {
                formData.append('location_id', this.locationId);
            }
            this.selectedFiles.forEach((file, index) => {
                formData.append(`images[${index}]`, file);
            });

            try {
                const res = await fetch('/api/v1/posts', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await res.json();
                if (res.ok) {
                    $dispatch('toast', 'Gönderiniz paylaşıldı! 🚀');
                    this.content = '';
                    this.selectedFiles = [];
                    this.previewUrls = [];
                    // Yeni gönderiyi listenin başına ekle
                    if (data.post) {
                        this.posts.unshift(data.post);
                    }
                } else {
                    $dispatch('toast', data.message || 'Gönderi paylaşılamadı.');
                }
            } catch (err) {
                $dispatch('toast', 'Ağ hatası meydana geldi.');
            } finally {
                this.posting = false;
            }
        }
    }">

        <!-- 1. Üst Bar: Sticky Tab Switcher -->
        <header class="sticky top-0 z-40 backdrop-blur-md bg-white/85 dark:bg-black/85 border-b border-slate-200 dark:border-neutral-800">
            <div class="flex items-center justify-between px-4 pt-3 pb-1">
                <div class="flex items-center gap-2">
                    <span class="text-xl font-black tracking-tight text-blue-600 dark:text-blue-500">Meydan</span>
                </div>
                <!-- Gece / Gündüz Modu Butonu -->
                <button @click="darkMode = !darkMode" class="p-2 rounded-full hover:bg-slate-100 dark:hover:bg-neutral-800 text-slate-500 dark:text-neutral-400 transition-colors">
                    <span x-show="darkMode">☀️</span>
                    <span x-show="!darkMode">🌙</span>
                </button>
            </div>

            <!-- Sekme Seçici: [Bölgem] - [Genel Akış] -->
            <nav class="flex text-center border-t border-slate-100 dark:border-neutral-900">
                <button @click="switchTab('local')"
                        class="flex-1 py-3 text-sm font-bold relative transition-colors focus:outline-none"
                        :class="activeTab === 'local' ? 'text-slate-900 dark:text-white' : 'text-slate-400 dark:text-neutral-500 hover:bg-slate-50 dark:hover:bg-neutral-900/40'">
                    <span class="flex items-center justify-center gap-1.5">
                        <span>📍</span>
                        <span x-text="'Bölgem (' + districtName + ')'"></span>
                    </span>
                    <div x-show="activeTab === 'local'" class="absolute bottom-0 inset-x-12 h-1 bg-blue-600 dark:bg-blue-500 rounded-full"></div>
                </button>

                <button @click="switchTab('general')"
                        class="flex-1 py-3 text-sm font-bold relative transition-colors focus:outline-none"
                        :class="activeTab === 'general' ? 'text-slate-900 dark:text-white' : 'text-slate-400 dark:text-neutral-500 hover:bg-slate-50 dark:hover:bg-neutral-900/40'">
                    <span class="flex items-center justify-center gap-1.5">
                        <span>🌍</span>
                        <span>Genel Akış</span>
                    </span>
                    <div x-show="activeTab === 'general'" class="absolute bottom-0 inset-x-12 h-1 bg-blue-600 dark:bg-blue-500 rounded-full"></div>
                </button>
            </nav>
        </header>

        <!-- 2. Hızlı Gönderi Paylaşım Kutusu (Compose Box) -->
        <section class="p-4 border-b border-slate-200 dark:border-neutral-800 bg-white dark:bg-black">
            <div class="flex items-start gap-3">
                <!-- Kullanıcı Avatarı -->
                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-bold flex items-center justify-center text-sm shadow-sm shrink-0">
                    {{ mb_substr(auth()->user()->name ?? 'B', 0, 1) }}
                </div>

                <div class="flex-1 min-w-0">
                    <!-- Metin Alanı -->
                    <textarea x-model="content"
                              rows="3"
                              placeholder="Mahallende veya gündemde neler oluyor?"
                              class="w-full bg-transparent border-0 resize-none text-[15px] placeholder-slate-400 dark:placeholder-neutral-500 text-slate-900 dark:text-white focus:ring-0 p-0 leading-relaxed outline-none"></textarea>

                    <!-- Görsel Önizleme Galerisi -->
                    <template x-if="previewUrls.length > 0">
                        <div class="grid grid-cols-2 gap-2 my-2.5">
                            <template x-for="(url, idx) in previewUrls" :key="idx">
                                <div class="relative rounded-xl overflow-hidden aspect-video border border-slate-200 dark:border-neutral-800">
                                    <img :src="url" class="w-full h-full object-cover">
                                    <button @click="removeFile(idx)" type="button" class="absolute top-1.5 right-1.5 w-6 h-6 rounded-full bg-black/75 hover:bg-black text-white flex items-center justify-center text-xs backdrop-blur-sm transition-colors">
                                        ✕
                                    </button>
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- Alt Kontroller: Kapsam Seçici, Görsel Butonu, Sayaç ve Paylaş Butonu -->
                    <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-neutral-800/60 mt-2">
                        <div class="flex items-center gap-2">
                            <!-- Görsel Ekleme İkonu -->
                            <label class="p-2 rounded-full hover:bg-blue-50 dark:hover:bg-blue-950/40 text-blue-600 dark:text-blue-400 cursor-pointer transition-colors" title="Görsel ekle (Maks 4)">
                                <input type="file" multiple accept="image/*" class="hidden" @change="handleFileSelect">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </label>

                            <!-- Kapsam Seçici Toggle: Mahallem / Genel -->
                            <div class="inline-flex items-center p-0.5 rounded-full bg-slate-100 dark:bg-neutral-900 border border-slate-200 dark:border-neutral-800 text-xs">
                                <button type="button" 
                                        @click="scope = 'local'"
                                        :class="scope === 'local' ? 'bg-white dark:bg-neutral-800 text-slate-900 dark:text-white font-semibold shadow-sm' : 'text-slate-500 dark:text-neutral-400'"
                                        class="px-2.5 py-1 rounded-full transition-all flex items-center gap-1">
                                    <span>📍</span>
                                    <span>Mahallem</span>
                                </button>
                                <button type="button" 
                                        @click="scope = 'general'"
                                        :class="scope === 'general' ? 'bg-white dark:bg-neutral-800 text-slate-900 dark:text-white font-semibold shadow-sm' : 'text-slate-500 dark:text-neutral-400'"
                                        class="px-2.5 py-1 rounded-full transition-all flex items-center gap-1">
                                    <span>🌍</span>
                                    <span>Genel</span>
                                </button>
                            </div>
                        </div>

                        <!-- Karakter Sayacı ve Paylaş Butonu -->
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-mono font-medium"
                                  :class="{
                                      'text-slate-400 dark:text-neutral-500': remainingChars > 50,
                                      'text-amber-500': remainingChars <= 50 && remainingChars >= 0,
                                      'text-rose-500 font-bold': remainingChars < 0
                                  }"
                                  x-text="remainingChars"></span>

                            <button @click="submitPost"
                                    type="button"
                                    :disabled="posting || remainingChars < 0 || (!content.trim() && selectedFiles.length === 0)"
                                    class="px-4 py-1.5 rounded-full bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-sm shadow-sm transition-all flex items-center gap-1.5">
                                <span x-show="!posting">Paylaş</span>
                                <span x-show="posting" class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full" x-cloak></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 3. Feed Alanı: Dinamik Post Kartları + Sponsorlu Kartlar -->
        <section class="divide-y divide-slate-100 dark:divide-neutral-900">
            <template x-for="(post, index) in posts" :key="post.id">
                <div>
                    <!-- Standart Post Kartı (Alpine ile Dinamik) -->
                    <div class="p-4 hover:bg-slate-50/50 dark:hover:bg-neutral-900/30 transition-colors">
                        <div class="flex items-start gap-3">
                            <!-- Avatar -->
                            <div class="shrink-0">
                                <template x-if="post.user && post.user.avatar_url">
                                    <img :src="post.user.avatar_url" class="w-10 h-10 rounded-full object-cover ring-1 ring-slate-200 dark:ring-neutral-700">
                                </template>
                                <template x-if="!post.user || !post.user.avatar_url">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-500 text-white font-semibold flex items-center justify-center text-sm shadow-sm"
                                         x-text="(post.user ? post.user.name : 'U').substring(0, 1)"></div>
                                </template>
                            </div>

                            <div class="flex-1 min-w-0">
                                <!-- Başlık & Konum Rozeti -->
                                <div class="flex items-center justify-between gap-2 mb-1">
                                    <div class="flex items-center gap-1.5 flex-wrap leading-tight">
                                        <span class="font-bold text-[15px] text-slate-900 dark:text-white truncate" x-text="post.user ? post.user.name : 'Anonim'"></span>
                                        <span class="text-xs text-slate-500 dark:text-neutral-400" x-text="'@' + (post.user ? post.user.username : 'user')"></span>
                                        <span class="text-xs text-slate-400 dark:text-neutral-500">·</span>
                                        <time class="text-xs text-slate-500 dark:text-neutral-400" x-text="post.created_at_human || 'Az önce'"></time>
                                    </div>

                                    <template x-if="post.scope === 'local' && post.location">
                                        <span class="shrink-0 inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60">
                                            <span>📍</span>
                                            <span x-text="post.location.district || post.location.city"></span>
                                        </span>
                                    </template>
                                </div>

                                <!-- İçerik -->
                                <p class="text-[15px] leading-relaxed text-slate-800 dark:text-neutral-200 whitespace-pre-line break-words mb-3" x-text="post.content"></p>

                                <!-- Medya Galerisi -->
                                <template x-if="post.media_urls && post.media_urls.length > 0">
                                    <div class="mb-3 rounded-2xl overflow-hidden border border-slate-200/80 dark:border-neutral-800 bg-slate-100 dark:bg-neutral-900">
                                        <template x-if="post.media_urls.length === 1">
                                            <img :src="post.media_urls[0]" class="w-full max-h-[420px] object-cover cursor-pointer">
                                        </template>
                                        <template x-if="post.media_urls.length > 1">
                                            <div class="grid grid-cols-2 gap-0.5 aspect-[16/9]">
                                                <template x-for="img in post.media_urls.slice(0, 4)" :key="img">
                                                    <img :src="img" class="w-full h-full object-cover cursor-pointer">
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                <!-- Etkileşim Butonları -->
                                <div class="flex items-center justify-between max-w-md pt-1 text-slate-500 dark:text-neutral-400">
                                    <!-- Beğeni -->
                                    <button @click="
                                        post.is_liked = !post.is_liked;
                                        post.likes_count += post.is_liked ? 1 : -1;
                                        fetch(`/api/v1/posts/${post.id}/like`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } });
                                    "
                                    type="button"
                                    class="group flex items-center gap-1.5 text-xs transition-colors hover:text-rose-600 focus:outline-none"
                                    :class="{ 'text-rose-600 font-semibold': post.is_liked }">
                                        <div class="p-2 rounded-full group-hover:bg-rose-50 dark:group-hover:bg-rose-950/30 transition-colors">
                                            <svg class="w-4 h-4 transition-transform group-hover:scale-110" :fill="post.is_liked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                            </svg>
                                        </div>
                                        <span x-text="post.likes_count || 0"></span>
                                    </button>

                                    <!-- Yorum -->
                                    <div class="group flex items-center gap-1.5 text-xs transition-colors hover:text-blue-500 cursor-pointer">
                                        <div class="p-2 rounded-full group-hover:bg-blue-50 dark:group-hover:bg-blue-950/30 transition-colors">
                                            <svg class="w-4 h-4 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                            </svg>
                                        </div>
                                        <span x-text="post.comments_count || 0"></span>
                                    </div>

                                    <!-- Paylaş -->
                                    <button @click="
                                        navigator.clipboard.writeText(window.location.origin + '/posts/' + post.id);
                                        $dispatch('toast', 'Gönderi bağlantısı kopyalandı! 🔗');
                                    "
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
                    </div>

                    <!-- Her 4 gönderide bir Sponsorlu Mahalle İlanı Kartı yerleştir -->
                    <template x-if="(index + 1) % 4 === 0">
                        <x-sponsored-card
                            title="Kadıköy Gurme Kahve & Fırın"
                            location="Moda Caddesi No:42"
                            content="Mahallemize özel: Hafta içi saat 10:00'a kadar tüm sıcak filtre kahve ve kruvasan çeşitlerinde %20 indirim!"
                            image="https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?auto=format&fit=crop&w=800&q=80"
                            ctaText="Konuma Git"
                            ctaUrl="https://maps.google.com" />
                    </template>
                </div>
            </template>
        </section>

        <!-- 4. Sonsuz Kaydırma Tetikleyicisi (Alpine.js x-intersect) -->
        <div x-show="hasMore" x-intersect="fetchFeed()" class="py-8 text-center" x-cloak>
            <div class="inline-flex items-center gap-2 text-sm text-slate-400 dark:text-neutral-500">
                <span class="animate-spin inline-block w-4 h-4 border-2 border-blue-500 border-t-transparent rounded-full"></span>
                <span>Yeni gönderiler yükleniyor...</span>
            </div>
        </div>

        <div x-show="!hasMore && posts.length > 0" class="py-10 text-center text-xs text-slate-400 dark:text-neutral-600 border-t border-slate-100 dark:border-neutral-900" x-cloak>
            🎉 Şimdilik tüm akışı gördünüz.
        </div>
    </div>
</x-app-layout>

