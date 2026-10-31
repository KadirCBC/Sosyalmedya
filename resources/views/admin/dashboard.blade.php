<x-admin-layout>
    <div class="space-y-8">
        <!-- Başlık -->
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-white">Platform İstatistikleri</h2>
            <p class="text-sm text-slate-400 mt-1">Canlı kullanıcı aktivitesi, ilçe yoğunlukları ve denetim durumu.</p>
        </div>

        <!-- 4'lü Temel Metrik Kartları -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- DAU (Günlük Aktif Kullanıcı) -->
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm">
                <div class="flex items-center justify-between text-slate-400 mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider">Günlük Aktif (DAU)</span>
                    <span class="p-2 rounded-lg bg-blue-500/10 text-blue-400">👥</span>
                </div>
                <div class="text-3xl font-black text-white">{{ number_format($dauCount) }}</div>
                <div class="text-xs text-emerald-400 mt-2 flex items-center gap-1">
                    <span>⚡ 5651 oturum kayıtları</span>
                </div>
            </div>

            <!-- Bekleyen Şikayetler -->
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm">
                <div class="flex items-center justify-between text-slate-400 mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider">Bekleyen Şikayet</span>
                    <span class="p-2 rounded-lg bg-rose-500/10 text-rose-400">⚠️</span>
                </div>
                <div class="text-3xl font-black {{ $pendingReportsCount > 0 ? 'text-rose-400' : 'text-white' }}">
                    {{ number_format($pendingReportsCount) }}
                </div>
                <div class="text-xs text-slate-400 mt-2">
                    <a href="{{ route('admin.moderation.index') }}" class="text-blue-400 hover:underline">Kuyruğu İncele →</a>
                </div>
            </div>

            <!-- Toplam Gönderi -->
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm">
                <div class="flex items-center justify-between text-slate-400 mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider">Toplam Gönderi</span>
                    <span class="p-2 rounded-lg bg-indigo-500/10 text-indigo-400">📝</span>
                </div>
                <div class="text-3xl font-black text-white">{{ number_format($totalPostsCount) }}</div>
                <div class="text-xs text-slate-400 mt-2">
                    Platform geneli içerik havuzu
                </div>
            </div>

            <!-- Aktif Sponsorlu İlanlar -->
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm">
                <div class="flex items-center justify-between text-slate-400 mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider">Aktif Esnaf İlanı</span>
                    <span class="p-2 rounded-lg bg-amber-500/10 text-amber-400">📢</span>
                </div>
                <div class="text-3xl font-black text-amber-400">{{ number_format($activeAdsCount) }}</div>
                <div class="text-xs text-slate-400 mt-2">
                    Yayındaki mahalle sponsorlukları
                </div>
            </div>
        </div>

        <!-- 2 Kolonlu Analitik Alanı -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Sol: İlçelere Göre Gönderi Yoğunluğu Tablosu (2 Kolon) -->
            <div class="lg:col-span-2 rounded-2xl bg-slate-900 border border-slate-800 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3 class="font-bold text-base text-white">İlçelere Göre Gönderi Yoğunluğu</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Son 7 günde en çok paylaşım yapılan aktif merkezler.</p>
                    </div>
                    <span class="text-xs font-medium text-slate-400 bg-slate-800 px-3 py-1 rounded-full">Haftalık Trend</span>
                </div>

                <div class="space-y-3.5">
                    @forelse($districtDensity as $location)
                        <div>
                            <div class="flex justify-between items-center text-xs mb-1">
                                <span class="font-semibold text-slate-200">
                                    📍 {{ $location->city }} / {{ $location->district }}
                                </span>
                                <span class="font-mono text-slate-400">
                                    {{ $location->posts_count }} gönderi
                                </span>
                            </div>
                            <div class="w-full bg-slate-800 rounded-full h-2 overflow-hidden">
                                @php
                                    $maxPosts = max(1, $districtDensity->first()->posts_count ?? 1);
                                    $percent = min(100, round(($location->posts_count / $maxPosts) * 100));
                                @endphp
                                <div class="bg-gradient-to-r from-blue-600 to-indigo-500 h-2 rounded-full transition-all duration-500" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500 py-6 text-center">Henüz konum bazlı gönderi bulunmuyor.</p>
                    @endforelse
                </div>
            </div>

            <!-- Sağ: Sunucu & cPanel Durumu (1 Kolon) -->
            <div class="rounded-2xl bg-slate-900 border border-slate-800 p-6 shadow-sm flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-base text-white mb-1">Sunucu & Kaynak Durumu</h3>
                    <p class="text-xs text-slate-400 mb-5">cPanel / Plesk ortamı PHP & RAM performansı.</p>

                    <dl class="space-y-3 text-xs">
                        <div class="flex items-center justify-between py-2 border-b border-slate-800">
                            <dt class="text-slate-400">PHP Sürümü</dt>
                            <dd class="font-mono font-semibold text-emerald-400">{{ $serverStats['php_version'] }}</dd>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-slate-800">
                            <dt class="text-slate-400">Anlık Bellek (RAM)</dt>
                            <dd class="font-mono font-semibold text-white">{{ $serverStats['memory_usage'] }}</dd>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-slate-800">
                            <dt class="text-slate-400">Zirve Bellek</dt>
                            <dd class="font-mono font-semibold text-slate-300">{{ $serverStats['memory_peak'] }}</dd>
                        </div>
                        <div class="flex items-center justify-between py-2">
                            <dt class="text-slate-400">OPcache Durumu</dt>
                            <dd class="font-semibold {{ $serverStats['opcache_enabled'] ? 'text-emerald-400' : 'text-amber-400' }}">
                                {{ $serverStats['opcache_enabled'] ? '✓ Aktif' : 'Pasif' }}
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-800">
                    <a href="{{ route('admin.moderation.index') }}"
                       class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-sm transition-colors">
                        <span>🛡️ Moderasyon Kuyruğuna Git</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>

