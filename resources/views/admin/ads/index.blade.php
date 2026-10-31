<x-admin-layout>
    <div class="space-y-6">
        <!-- Başlık -->
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                    <span>📢 Sponsorlu İlan ve Reklam Yönetimi</span>
                </h2>
                <p class="text-sm text-slate-400 mt-1">Yerel esnaf ve işletmelerin mahalle reklam onayları ve performans metrikleri.</p>
            </div>
        </div>

        <!-- 4'lü Metrik Kartları -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-4 rounded-xl bg-slate-900 border border-slate-800">
                <span class="text-xs text-slate-400 font-semibold uppercase">Toplam Gösterim</span>
                <div class="text-2xl font-black text-white mt-1">{{ number_format($totalImpressions) }}</div>
            </div>
            <div class="p-4 rounded-xl bg-slate-900 border border-slate-800">
                <span class="text-xs text-slate-400 font-semibold uppercase">Toplam Tıklanma</span>
                <div class="text-2xl font-black text-blue-400 mt-1">{{ number_format($totalClicks) }}</div>
            </div>
            <div class="p-4 rounded-xl bg-slate-900 border border-slate-800">
                <span class="text-xs text-slate-400 font-semibold uppercase">Ortalama CTR (Tıklama %)</span>
                <div class="text-2xl font-black text-emerald-400 mt-1">%{{ $averageCtr }}</div>
            </div>
            <div class="p-4 rounded-xl bg-slate-900 border border-slate-800">
                <span class="text-xs text-slate-400 font-semibold uppercase">Aktif İlan Bütçesi</span>
                <div class="text-2xl font-black text-amber-400 mt-1">₺{{ number_format((float) $totalBudget, 2) }}</div>
            </div>
        </div>

        <!-- İlanlar Tablosu -->
        <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-800 bg-slate-950/60 text-slate-400 uppercase tracking-wider font-semibold">
                            <th class="py-3.5 px-4">İlan & İşletme</th>
                            <th class="py-3.5 px-4">Hedef Konum</th>
                            <th class="py-3.5 px-4">Bütçe</th>
                            <th class="py-3.5 px-4">Gösterim / Tık</th>
                            <th class="py-3.5 px-4">CTR %</th>
                            <th class="py-3.5 px-4">Durum</th>
                            <th class="py-3.5 px-4 text-right">İşlem</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/70 text-slate-300">
                        @forelse($ads as $ad)
                            <tr class="hover:bg-slate-800/30 transition-colors">
                                <!-- Başlık ve İşletme -->
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-white text-sm">{{ $ad->title }}</div>
                                    <div class="text-slate-400 text-[11px] mt-0.5">
                                        {{ $ad->user->name ?? 'İşletme' }} ({{ '@' . ($ad->user->username ?? '') }})
                                    </div>
                                    <div class="text-slate-500 text-[11px] mt-1 line-clamp-1 max-w-xs">
                                        {{ $ad->content }}
                                    </div>
                                </td>

                                <!-- Hedef Konum -->
                                <td class="py-3.5 px-4">
                                    @if($ad->location)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-950/80 text-emerald-300 border border-emerald-800/50">
                                            📍 {{ $ad->location->city }} / {{ $ad->location->district }}
                                        </span>
                                    @else
                                        <span class="text-slate-500">🌍 Platform Geneli</span>
                                    @endif
                                </td>

                                <!-- Bütçe -->
                                <td class="py-3.5 px-4 font-mono font-semibold text-white">
                                    ₺{{ number_format((float) $ad->budget, 2) }}
                                </td>

                                <!-- Gösterim & Tıklanma -->
                                <td class="py-3.5 px-4 font-mono">
                                    <div class="text-slate-200">{{ number_format($ad->impressions_count) }} gör.</div>
                                    <div class="text-blue-400">{{ number_format($ad->clicks_count) }} tık</div>
                                </td>

                                <!-- CTR -->
                                <td class="py-3.5 px-4 font-mono font-semibold text-emerald-400">
                                    %{{ $ad->ctr }}
                                </td>

                                <!-- Durum Rozeti -->
                                <td class="py-3.5 px-4">
                                    @php
                                        $badgeStyles = [
                                            'pending' => 'bg-amber-950/80 text-amber-300 border-amber-800/50',
                                            'active' => 'bg-emerald-950/80 text-emerald-300 border-emerald-800/50',
                                            'rejected' => 'bg-rose-950/80 text-rose-300 border-rose-800/50',
                                            'expired' => 'bg-slate-800 text-slate-400 border-slate-700',
                                        ];
                                    @endphp
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $badgeStyles[$ad->status->value] ?? '' }}">
                                        {{ $ad->status->label() }}
                                    </span>
                                </td>

                                <!-- İşlemler -->
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if($ad->status->value === 'pending')
                                            <!-- Onayla -->
                                            <form action="{{ route('admin.ads.approve', $ad->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs shadow-sm transition-colors">
                                                    ✓ Onayla
                                                </button>
                                            </form>

                                            <!-- Reddet -->
                                            <form action="{{ route('admin.ads.reject', $ad->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-600/30 hover:bg-rose-600 text-rose-300 hover:text-white border border-rose-500/40 font-semibold text-xs transition-colors">
                                                    ✕ Reddet
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-slate-500 text-[11px]">İşlem Tamam</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-500">
                                    Kayıtlı sponsorlu ilan bulunmuyor.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sayfalama -->
        <div>
            {{ $ads->links() }}
        </div>
    </div>
</x-admin-layout>

