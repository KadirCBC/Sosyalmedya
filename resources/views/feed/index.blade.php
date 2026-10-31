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

        // Yerel Anket State
        showPollBox: false,
        pollQuestion: '',
        pollOptions: ['', ''],
        pollDuration: 24,

        addPollOption() {
            if (this.pollOptions.length < 4) {
                this.pollOptions.push('');
            }
        },

        removePollOption(index) {
            if (this.pollOptions.length > 2) {
                this.pollOptions.splice(index, 1);
            }
        },

        // Liderlik Tablosu (Haftanın Komşuları) State
        showLeaderboardModal: false,
        leaderboard: [],
        loadingLeaderboard: false,

        async openLeaderboard() {
            this.showLeaderboardModal = true;
            this.loadingLeaderboard = true;
            try {
                const res = await fetch(`/api/v1/locations/${this.locationId}/leaderboard`);
                const data = await res.json();
                if (data.success) {
                    this.leaderboard = data.leaderboard;
                }
            } catch (err) {
                console.error('Liderlik tablosu hatası:', err);
            } finally {
                this.loadingLeaderboard = false;
            }
        },

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

            // Anket Eklendiyse Doğrula ve Ekle
            if (this.showPollBox) {
                const validOptions = this.pollOptions.map(o => o.trim()).filter(o => o.length > 0);
                if (validOptions.length < 2) {
                    $dispatch('toast', 'Anket için en az 2 seçenek doldurmalısınız.');
                    this.posting = false;
                    return;
                }
                formData.append('poll[question]', this.pollQuestion.trim() || this.content.trim());
                formData.append('poll[duration_hours]', this.pollDuration);
                validOptions.forEach((opt, idx) => {
                    formData.append(`poll[options][${idx}]`, opt);
                });
            }

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
                    this.showPollBox = false;
                    this.pollQuestion = '';
                    this.pollOptions = ['', ''];
                    this.pollDuration = 24;
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
                <div class="flex items-center gap-2">
                    <!-- Haftanın Komşuları Liderlik Butonu -->
                    <button @click="openLeaderboard()"
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 hover:bg-indigo-500/20 transition-colors">
                        <span>🏆 Komşular</span>
                    </button>
                    <!-- Yerel Nabız & Trend Analitiği Butonu -->
                    <a href="{{ route('analytics.pulse') }}"
                       class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20 hover:bg-purple-500/20 transition-colors"
                       title="İlçe Gündemi ve Memnuniyet Analitiği">
                        <span>📊 Nabız</span>
                    </a>
                    <!-- Esnaf Reklam Sihirbazı Butonu -->
                    <a href="{{ route('ads.create') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 hover:bg-amber-500/20 transition-colors">
                        <span>⚡ Reklam</span>
                    </a>
                    <!-- Acil Durum Bildir Butonu -->
                    <a href="{{ route('emergency.create') }}"
                       class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 hover:bg-rose-500/20 transition-colors"
                       title="Afet, kan veya kayıp ilanı bildir">
                        <span>🚨 Acil</span>
                    </a>
                    <!-- Gece / Gündüz Modu Butonu -->
                    <button @click="darkMode = !darkMode" class="p-2 rounded-full hover:bg-slate-100 dark:hover:bg-neutral-800 text-slate-500 dark:text-neutral-400 transition-colors">
                        <span x-show="darkMode">☀️</span>
                        <span x-show="!darkMode">🌙</span>
                    </button>
                </div>
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

                <!-- 3. Sekme: Mahalle Pazarı -->
                <a href="{{ route('marketplace.index') }}"
                   class="flex-1 py-3 text-sm font-bold text-slate-400 dark:text-neutral-500 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-neutral-900/40 transition-colors flex items-center justify-center gap-1.5">
                    <span>🛍️</span>
                    <span>Pazar</span>
                </a>

                <!-- 4. Sekme: Esnaf Rehberi -->
                <a href="{{ route('business.index') }}"
                   class="flex-1 py-3 text-sm font-bold text-slate-400 dark:text-neutral-500 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-neutral-900/40 transition-colors flex items-center justify-center gap-1.5">
                    <span>🏪</span>
                    <span>Esnaf</span>
                </a>

                <!-- 5. Sekme: Mahalle Etkinlikleri -->
                <a href="{{ route('events.index') }}"
                   class="flex-1 py-3 text-sm font-bold text-slate-400 dark:text-neutral-500 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-neutral-900/40 transition-colors flex items-center justify-center gap-1.5">
                    <span>📅</span>
                    <span>Etkinlik</span>
                </a>
            </nav>
        </header>

        <!-- 🚨 Kırmızı Akış: Sticky İlçe Acil Durum Uyarısı (Reklam ve Postlarca Bastırılamaz) -->
        <x-emergency-banner :locationId="$locationId ?? 1" />

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

                    <!-- Anket Formu (Expandable) -->
                    <div x-show="showPollBox" x-cloak class="my-3 p-3.5 rounded-2xl bg-slate-50 dark:bg-neutral-900 border border-slate-200 dark:border-neutral-800 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-blue-600 dark:text-blue-400 flex items-center gap-1">
                                📊 Yerel Anket Oluştur
                            </span>
                            <button @click="showPollBox = false" type="button" class="text-xs text-slate-400 hover:text-rose-500 transition-colors">
                                İptal ✕
                            </button>
                        </div>
                        <input x-model="pollQuestion"
                               type="text"
                               placeholder="Anket sorusu (boş bırakılırsa gönderi metni kullanılır)"
                               class="w-full text-xs px-3 py-2 rounded-xl bg-white dark:bg-neutral-800 border border-slate-200 dark:border-neutral-700 text-slate-900 dark:text-white focus:ring-1 focus:ring-blue-500 outline-none">
                        <div class="space-y-1.5">
                            <template x-for="(opt, idx) in pollOptions" :key="idx">
                                <div class="flex items-center gap-1.5">
                                    <input x-model="pollOptions[idx]"
                                           type="text"
                                           :placeholder="`Seçenek ${idx + 1}`"
                                           class="flex-1 text-xs px-3 py-1.5 rounded-xl bg-white dark:bg-neutral-800 border border-slate-200 dark:border-neutral-700 text-slate-900 dark:text-white focus:ring-1 focus:ring-blue-500 outline-none">
                                    <button x-show="pollOptions.length > 2"
                                            @click="removePollOption(idx)"
                                            type="button"
                                            class="p-1 text-slate-400 hover:text-rose-500 text-xs">✕</button>
                                </div>
                            </template>
                        </div>
                        <div class="flex items-center justify-between pt-1">
                            <button x-show="pollOptions.length < 4"
                                    @click="addPollOption()"
                                    type="button"
                                    class="text-[11px] font-bold text-blue-600 dark:text-blue-400 hover:underline">
                                + Seçenek Ekle (Maks 4)
                            </button>
                            <div class="flex items-center gap-1.5 text-[11px] text-slate-500">
                                <span>Süre:</span>
                                <select x-model="pollDuration" class="bg-white dark:bg-neutral-800 border border-slate-200 dark:border-neutral-700 rounded-lg px-2 py-0.5 text-[11px] text-slate-700 dark:text-slate-300">
                                    <option value="24">24 Saat</option>
                                    <option value="72">3 Gün</option>
                                    <option value="168">7 Gün</option>
                                </select>
                            </div>
                        </div>
                    </div>

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

                            <!-- Anket Açma Butonu -->
                            <button type="button"
                                    @click="showPollBox = !showPollBox"
                                    :class="showPollBox ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300' : 'text-slate-500 hover:text-blue-600 dark:text-neutral-400 hover:bg-slate-100 dark:hover:bg-neutral-800'"
                                    class="p-2 rounded-full transition-colors"
                                    title="Yerel anket ekle">
                                <span class="text-sm">📊</span>
                            </button>

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

        <!-- 🌟 Bu Hafta Sonu Mahallede Ne Var? Etkinlik Widget'ı -->
        <x-weekend-events-widget :locationId="$locationId ?? 1" :districtName="$districtName ?? 'Mahalleniz'" />

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
                                        <!-- Dinamik İtibar Rozeti -->
                                        <template x-if="post.user && post.user.reputation">
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[10px] font-bold border shrink-0 tracking-tight select-none"
                                                  :class="{
                                                      'bg-amber-100 dark:bg-amber-950/70 text-amber-800 dark:text-amber-300 border-amber-300/60 dark:border-amber-800/60': post.user.reputation.level === 'Gönüllü Muhtar',
                                                      'bg-emerald-100 dark:bg-emerald-950/70 text-emerald-800 dark:text-emerald-300 border-emerald-300/60 dark:border-emerald-800/60': post.user.reputation.level === 'Mahalle Esnafı',
                                                      'bg-blue-100 dark:bg-blue-950/70 text-blue-800 dark:text-blue-300 border-blue-300/60 dark:border-blue-800/60': post.user.reputation.level === 'Mahalle Sakini',
                                                      'bg-slate-100 dark:bg-neutral-800 text-slate-700 dark:text-neutral-300 border-slate-300/60 dark:border-neutral-700/60': post.user.reputation.level === 'Yeni Komşu'
                                                  }">
                                                <span x-text="post.user.reputation.badge_icon || '🌱'"></span>
                                                <span x-text="post.user.reputation.level"></span>
                                                <span class="opacity-60 font-mono" x-text="'(' + post.user.reputation.points + 'p)'"></span>
                                            </span>
                                        </template>
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

                                <!-- Varsa Yerel Anket & Oylama (Dinamik) -->
                                <template x-if="post.poll">
                                    <div class="my-3 p-3.5 rounded-2xl bg-slate-50 dark:bg-neutral-900/90 border border-slate-200 dark:border-neutral-800 shadow-sm">
                                        <div class="flex items-center justify-between mb-2.5">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300">
                                                📊 Yerel Anket
                                            </span>
                                            <div class="flex items-center gap-1.5">
                                                <button @click="window.open(`/polls/${post.poll.id}/card`, '_blank')"
                                                        type="button"
                                                        class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-0.5">
                                                    <span>İnfografik</span> ↗
                                                </button>
                                            </div>
                                        </div>
                                        <h4 class="text-sm font-bold text-slate-900 dark:text-white mb-2.5" x-text="post.poll.question"></h4>
                                        <div class="space-y-1.5">
                                            <template x-for="opt in post.poll.options" :key="opt.id">
                                                <div>
                                                    <template x-if="post.poll.show_results">
                                                        <div class="relative overflow-hidden rounded-xl border p-2 text-xs"
                                                             :class="opt.is_user_vote ? 'border-blue-500 bg-blue-50/60 dark:bg-blue-950/30' : 'border-slate-200 dark:border-neutral-800 bg-white dark:bg-neutral-800/40'">
                                                            <div class="absolute inset-y-0 left-0 bg-blue-500 opacity-20 transition-all duration-500 rounded-l-xl"
                                                                 :style="`width: ${opt.percentage}%`"></div>
                                                            <div class="relative z-10 flex items-center justify-between gap-2">
                                                                <div class="flex items-center gap-1.5 font-medium text-slate-800 dark:text-slate-200">
                                                                    <span x-text="opt.option_text"></span>
                                                                    <span x-show="opt.is_user_vote" class="text-[10px] px-1 bg-blue-600 text-white rounded font-bold">✓ Oyunuz</span>
                                                                </div>
                                                                <span class="font-mono font-bold" x-text="`%${opt.percentage} (${opt.votes_count} oy)`"></span>
                                                            </div>
                                                        </div>
                                                    </template>
                                                    <template x-if="!post.poll.show_results">
                                                        <button @click="
                                                            fetch(`/api/v1/polls/${post.poll.id}/vote`, {
                                                                method: 'POST',
                                                                headers: {
                                                                    'Content-Type': 'application/json',
                                                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                                                },
                                                                body: JSON.stringify({ option_id: opt.id })
                                                            }).then(r => r.json()).then(d => {
                                                                if (d.success) {
                                                                    post.poll = d.poll;
                                                                    $dispatch('toast', 'Oyunuz kaydedildi! 🗳️');
                                                                } else {
                                                                    $dispatch('toast', d.message || 'Hata oluştu.');
                                                                }
                                                            });
                                                        "
                                                        type="button"
                                                        class="w-full text-left py-2 px-3 rounded-xl border border-blue-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-xs font-semibold text-slate-800 dark:text-slate-200 hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-950/30 transition-colors flex items-center justify-between">
                                                            <span x-text="opt.option_text"></span>
                                                            <span class="text-blue-500 text-[11px] font-bold">Oy Ver →</span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                        <div class="mt-2.5 pt-2 border-t border-slate-200/60 dark:border-neutral-800/60 flex items-center justify-between text-[11px] text-slate-400">
                                            <span x-text="`${post.poll.total_votes} oy • ${post.poll.is_expired ? 'Oylama kapandı' : post.poll.expires_at_human + ' sona eriyor'}`"></span>
                                            <div class="flex items-center gap-2">
                                                <button @click="window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(post.poll.question)}&url=${encodeURIComponent(window.location.origin + '/polls/' + post.poll.id + '/card')}`, '_blank')"
                                                        class="text-sky-500 hover:underline">X'te Paylaş</button>
                                                <button @click="window.open(`https://api.whatsapp.com/send?text=${encodeURIComponent(post.poll.question + ' ' + window.location.origin + '/polls/' + post.poll.id + '/card')}`, '_blank')"
                                                        class="text-emerald-500 hover:underline">WhatsApp</button>
                                            </div>
                                        </div>
                                    </div>
                                </template>

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

                    <!-- Her 6 gönderide bir Mahalle Pazarı Yakınındaki İlanlar Karuseli yerleştir -->
                    <template x-if="(index + 1) === 2 || (index + 1) % 6 === 0">
                        <x-marketplace-carousel :locationId="$locationId ?? 1" :districtName="$districtName ?? 'Bölgeniz'" />
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

        <!-- 5. Haftanın Komşuları (Liderlik Tablosu) Modal Penceresi -->
        <div x-show="showLeaderboardModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            <div @click.away="showLeaderboardModal = false"
                 class="w-full max-w-md bg-white dark:bg-neutral-900 rounded-2xl border border-slate-200 dark:border-neutral-800 shadow-2xl overflow-hidden flex flex-col max-h-[85vh]">
                
                <!-- Modal Başlığı -->
                <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-neutral-800 flex items-center justify-between bg-gradient-to-r from-indigo-500/10 via-transparent to-amber-500/10">
                    <div class="flex items-center gap-2.5">
                        <span class="text-2xl">🏆</span>
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Haftanın Komşuları</h3>
                            <p class="text-xs text-slate-500 dark:text-neutral-400" x-text="`${districtName} Liderlik Tablosu`"></p>
                        </div>
                    </div>
                    <button @click="showLeaderboardModal = false" class="p-1.5 rounded-full hover:bg-slate-100 dark:hover:bg-neutral-800 text-slate-400 hover:text-slate-600 dark:hover:text-neutral-200 transition">
                        ✕
                    </button>
                </div>

                <!-- Liderlik Listesi -->
                <div class="p-4 overflow-y-auto space-y-2 flex-1">
                    <template x-if="loadingLeaderboard">
                        <div class="py-12 text-center text-slate-400">
                            <span class="animate-spin inline-block w-6 h-6 border-2 border-indigo-500 border-t-transparent rounded-full mb-2"></span>
                            <div class="text-xs">Komşular yükleniyor...</div>
                        </div>
                    </template>

                    <template x-if="!loadingLeaderboard && leaderboard.length === 0">
                        <div class="py-12 text-center text-slate-400 text-xs">
                            🌱 Henüz bu ilçede itibar puanı alan komşu bulunmuyor. İlk sen ol!
                        </div>
                    </template>

                    <template x-for="item in leaderboard" :key="item.user_id">
                        <div class="p-3 rounded-xl border border-slate-100 dark:border-neutral-800/80 bg-slate-50/50 dark:bg-neutral-950/40 flex items-center justify-between gap-3 transition hover:border-indigo-200 dark:hover:border-indigo-900/60">
                            <div class="flex items-center gap-3 min-w-0">
                                <!-- Sıralama Rozeti -->
                                <div class="w-6 text-center font-black text-sm shrink-0"
                                     :class="{
                                         'text-amber-500 text-base': item.rank === 1,
                                         'text-slate-400': item.rank === 2,
                                         'text-amber-700 dark:text-amber-600': item.rank === 3,
                                         'text-slate-400 dark:text-neutral-600 font-mono text-xs': item.rank > 3
                                     }">
                                    <span x-show="item.rank === 1">🥇</span>
                                    <span x-show="item.rank === 2">🥈</span>
                                    <span x-show="item.rank === 3">🥉</span>
                                    <span x-show="item.rank > 3" x-text="'#' + item.rank"></span>
                                </div>

                                <!-- Kullanıcı Bilgisi -->
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5 truncate">
                                        <span class="font-bold text-sm text-slate-900 dark:text-white truncate" x-text="item.name"></span>
                                        <span class="text-[11px] text-slate-400" x-text="'@' + item.username"></span>
                                    </div>
                                    <div class="mt-0.5 flex items-center gap-1">
                                        <span class="text-[10px] font-semibold text-slate-500 dark:text-neutral-400 flex items-center gap-1">
                                            <span x-text="item.badge_icon"></span>
                                            <span x-text="item.level"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Puan Rozeti -->
                            <div class="text-right shrink-0">
                                <span class="px-2.5 py-1 rounded-full bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 font-black text-xs font-mono border border-indigo-200 dark:border-indigo-900/60"
                                      x-text="item.points + ' Puan'"></span>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Bilgilendirme Altlığı -->
                <div class="p-3 bg-slate-50 dark:bg-neutral-800/50 border-t border-slate-100 dark:border-neutral-800 text-[11px] text-slate-500 dark:text-neutral-400 text-center">
                    💡 Mahallenizde beğeni alıp faydalı içerikler üreterek puan kazanabilir, <strong>Gönüllü Muhtar</strong> unvanına yükselebilirsiniz.
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

