<x-admin-layout>
    <div class="space-y-6">
        <!-- Başlık Barı -->
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                    <span>🛡️ Moderasyon Kuyruğu</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                        {{ $reportedPosts->total() }} İnceleme Bekliyor
                    </span>
                </h2>
                <p class="text-sm text-slate-400 mt-1">
                    @if(auth()->user()->isLocalModerator() && auth()->user()->moderatedLocation)
                        Yetkili olduğunuz <strong>{{ auth()->user()->moderatedLocation->district }}</strong> bölgesine ait şikayetler listeleniyor.
                    @else
                        Platform geneli 5651 ve topluluk kuralları ihlal bildirimleri.
                    @endif
                </p>
            </div>
        </div>

        <!-- Şikayet Edilen Gönderi Kartları -->
        <div class="space-y-4">
            @forelse($reportedPosts as $post)
                <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-4" x-data="{ showReports: false }">
                    <div class="flex items-start justify-between gap-4 flex-wrap">
                        <!-- Yazar Bilgisi -->
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-slate-800 text-slate-300 font-bold flex items-center justify-center text-sm">
                                {{ mb_substr($post->user->name ?? 'U', 0, 1) }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-sm text-white">{{ $post->user->name ?? 'Silinmiş Kullanıcı' }}</span>
                                    <span class="text-xs text-slate-400">{{ '@' . ($post->user->username ?? 'user') }}</span>
                                    @if($post->user && $post->user->isSuspended())
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-950 text-rose-300 border border-rose-800/50">Askıda</span>
                                    @endif
                                </div>
                                <div class="text-xs text-slate-500 mt-0.5 flex items-center gap-2">
                                    <span>{{ $post->created_at->diffForHumans() }}</span>
                                    @if($post->location)
                                        <span>·</span>
                                        <span class="text-emerald-400 font-medium">📍 {{ $post->location->city }} / {{ $post->location->district }}</span>
                                    @endif
                                    @if($post->is_hidden)
                                        <span>·</span>
                                        <span class="text-amber-400 font-medium">🔒 Otomatik Gizlenmiş</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Şikayet Sayacı & Neden Rozetleri -->
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                ⚠️ {{ $post->pending_reports_count }} Şikayet
                            </span>
                        </div>
                    </div>

                    <!-- Gönderi Metni -->
                    <div class="p-3.5 rounded-xl bg-slate-950/70 border border-slate-800/70 text-sm text-slate-200 leading-relaxed whitespace-pre-line">
                        {{ $post->content }}
                    </div>

                    <!-- Varsa Medya Görselleri -->
                    @if(!empty($post->media_paths) && is_array($post->media_paths))
                        <div class="flex items-center gap-2 overflow-x-auto py-1">
                            @foreach($post->media_paths as $path)
                                <img src="{{ app(\App\Services\MediaService::class)->getUrl($path) }}" class="w-20 h-20 rounded-lg object-cover ring-1 ring-slate-800">
                            @endforeach
                        </div>
                    @endif

                    <!-- Şikayet Edenlerin Notları (Akordeon) -->
                    <div class="pt-2 border-t border-slate-800/60">
                        <button @click="showReports = !showReports" class="text-xs text-blue-400 hover:text-blue-300 font-medium flex items-center gap-1 focus:outline-none">
                            <span x-text="showReports ? '▲ Şikayet Detaylarını Kapat' : '▼ Şikayet Gerekçelerini ve Notları Göster (' + {{ $post->reports->count() }} + ')'"></span>
                        </button>

                        <div x-show="showReports" class="mt-3 space-y-2" x-cloak>
                            @foreach($post->reports as $report)
                                <div class="p-2.5 rounded-lg bg-slate-800/40 border border-slate-800 text-xs flex items-start justify-between gap-3">
                                    <div>
                                        <span class="font-semibold text-rose-300 uppercase tracking-wide text-[10px] bg-rose-950/60 px-1.5 py-0.5 rounded border border-rose-800/40">
                                            {{ $report->reason->value }}
                                        </span>
                                        @if($report->description)
                                            <p class="text-slate-300 mt-1 italic">"{{ $report->description }}"</p>
                                        @endif
                                    </div>
                                    <span class="text-slate-500 shrink-0">{{ '@' . ($report->reporter->username ?? 'anonim') }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- 4 Temel Aksiyon Butonu -->
                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-800 flex-wrap">
                        <!-- 1. Onayla (Yayına Al) -->
                        <form action="{{ route('admin.moderation.approve', $post->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 rounded-xl bg-emerald-600/20 hover:bg-emerald-600 text-emerald-300 hover:text-white border border-emerald-500/30 text-xs font-semibold transition-colors">
                                ✓ Onayla (Yayına Al)
                            </button>
                        </form>

                        <!-- 2. Gizle -->
                        <form action="{{ route('admin.moderation.hide', $post->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 rounded-xl bg-amber-600/20 hover:bg-amber-600 text-amber-300 hover:text-white border border-amber-500/30 text-xs font-semibold transition-colors">
                                👁️ Gizle
                            </button>
                        </form>

                        <!-- 3. İçeriği Sil -->
                        <form action="{{ route('admin.moderation.delete', $post->id) }}" method="POST" onsubmit="return confirm('Bu gönderiyi kalıcı olarak silmek istediğinize emin misiniz?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-600/20 hover:bg-rose-600 text-rose-300 hover:text-white border border-rose-500/30 text-xs font-semibold transition-colors">
                                🗑️ İçeriği Sil
                            </button>
                        </form>

                        <!-- 4. Kullanıcıyı 24 Saat Askıya Al -->
                        @if($post->user)
                            <form action="{{ route('admin.moderation.suspend-user', $post->user_id) }}" method="POST" onsubmit="return confirm('Kullanıcı 24 saat süreyle askıya alınacaktır. Emin misiniz?')">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 rounded-xl bg-purple-600/20 hover:bg-purple-600 text-purple-300 hover:text-white border border-purple-500/30 text-xs font-semibold transition-colors">
                                    ⛔ 24 Saat Askıya Al
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-12 rounded-2xl bg-slate-900 border border-slate-800 text-center">
                    <div class="text-4xl mb-3">🎉</div>
                    <h3 class="text-base font-bold text-white">İnceleme Bekleyen Şikayet Bulunmuyor</h3>
                    <p class="text-xs text-slate-400 mt-1">Yetkili olduğunuz alandaki tüm içerikler denetlendi veya şikayet yapılmadı.</p>
                </div>
            @endforelse
        </div>

        <!-- Sayfalama -->
        <div>
            {{ $reportedPosts->links() }}
        </div>
    </div>
</x-admin-layout>

