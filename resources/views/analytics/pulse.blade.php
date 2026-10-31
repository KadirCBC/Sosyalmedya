@extends('layouts.app', ['title' => 'Meydan - Yerel Nabız ve Gündem Analitiği: ' . ($report['location']['district'] ?? 'İlçe'), 'maxWidth' => 'max-w-4xl'])

@section('content')
<div class="p-4 sm:p-6 space-y-6">

    <!-- Üst Başlık & İlçe Değiştirici -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200 dark:border-neutral-800">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300">
                    B2B & Kurumsal Nabız
                </span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">
                    ● Canlı Veri
                </span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white mt-1">
                📍 {{ $report['location']['city'] }} / {{ $report['location']['district'] }}
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Son güncelleme: {{ \Carbon\Carbon::parse($report['generated_at'])->format('d.m.Y H:i') }} • Anonimleştirilmiş ilçe akış istatistiği
            </p>
        </div>

        <!-- İlçe Seçim Dropdown'ı -->
        <div class="flex items-center gap-2">
            <label for="districtSelect" class="text-xs font-medium text-slate-600 dark:text-slate-400 whitespace-nowrap">İlçe Seç:</label>
            <select id="districtSelect"
                    onchange="if (this.value) window.location.href = '/analytics/pulse/' + this.value"
                    class="text-xs font-semibold rounded-xl border border-slate-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-slate-800 dark:text-slate-200 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none shadow-sm">
                @foreach($districts as $d)
                    <option value="{{ $d['slug'] }}" {{ $d['slug'] === $currentSlug ? 'selected' : '' }}>
                        {{ $d['city'] }} - {{ $d['district'] }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Durum Özeti & KVKK Uyarısı -->
    <div class="rounded-2xl p-4 bg-gradient-to-r {{ ($report['pulse_summary']['positive_score'] ?? 50) >= 60 ? 'from-emerald-500/10 via-emerald-500/5 to-transparent border border-emerald-500/20' : 'from-amber-500/10 via-amber-500/5 to-transparent border border-amber-500/20' }} flex items-start gap-3">
        <div class="w-8 h-8 rounded-xl {{ ($report['pulse_summary']['positive_score'] ?? 50) >= 60 ? 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-400' : 'bg-amber-500/20 text-amber-600 dark:text-amber-400' }} flex items-center justify-center shrink-0 font-bold">
            {{ ($report['pulse_summary']['positive_score'] ?? 50) >= 60 ? '✓' : '!' }}
        </div>
        <div class="flex-1">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                Gündem Değerlendirmesi: {{ $report['pulse_summary']['status_message'] }}
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                {{ $report['disclaimer'] }}
            </p>
        </div>
    </div>

    <!-- Ana Metrik Kartları (Grid 3'lü) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- 1. Memnuniyet Endeksi -->
        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-neutral-900 border border-slate-200 dark:border-neutral-800 flex flex-col justify-between">
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Genel Memnuniyet Oranı</span>
            <div class="my-3">
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-black text-emerald-600 dark:text-emerald-400">
                        %{{ $report['pulse_summary']['positive_score'] }}
                    </span>
                    <span class="text-xs text-slate-500 dark:text-slate-400">Olumlu</span>
                </div>
                <!-- Mini Progress Bar -->
                <div class="w-full bg-slate-200 dark:bg-neutral-800 h-2 rounded-full mt-2 overflow-hidden flex">
                    <div class="bg-emerald-500 h-full" style="width: {{ $report['pulse_summary']['positive_score'] }}%"></div>
                    <div class="bg-rose-500 h-full" style="width: {{ $report['pulse_summary']['negative_score'] }}%"></div>
                </div>
                <div class="flex justify-between text-[11px] text-slate-400 mt-1">
                    <span>%{{ $report['pulse_summary']['positive_score'] }} Pozitif</span>
                    <span>%{{ $report['pulse_summary']['negative_score'] }} Şikayet</span>
                </div>
            </div>
            <span class="text-[11px] text-slate-400">Yapay zeka sözlük tabanlı duygu analizi</span>
        </div>

        <!-- 2. Gönderi & Şikayet Hacmi -->
        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-neutral-900 border border-slate-200 dark:border-neutral-800 flex flex-col justify-between">
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">İncelenen Gönderi Sayısı</span>
            <div class="my-3">
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-black text-blue-600 dark:text-blue-400">
                        {{ number_format($report['pulse_summary']['total_analyzed_posts']) }}
                    </span>
                    <span class="text-xs text-slate-500 dark:text-slate-400">Son 24 Saat</span>
                </div>
                <p class="text-xs text-rose-500 dark:text-rose-400 font-semibold mt-1">
                    ⚠️ {{ $report['pulse_summary']['total_complaints'] }} olası sorun/şikayet sinyali
                </p>
            </div>
            <span class="text-[11px] text-slate-400">Her saat başı toplanan yerel veri</span>
        </div>

        <!-- 3. Öne Çıkan Sorun Alanları -->
        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-neutral-900 border border-slate-200 dark:border-neutral-800 flex flex-col justify-between">
            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">En Çok Şikayet Alan Konular</span>
            <div class="my-3 flex flex-wrap gap-1.5">
                @forelse($report['pulse_summary']['top_complaint_issues'] as $issue)
                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/70 dark:text-rose-300 border border-rose-200 dark:border-rose-900">
                        {{ $issue }}
                    </span>
                @empty
                    <span class="text-xs text-slate-400 italic">Belirgin şikayet yoğunlaşması yok</span>
                @endforelse
            </div>
            <span class="text-[11px] text-slate-400">Belediye & altyapı birimleri için kritik</span>
        </div>
    </div>

    <!-- Haftalık Trend Tablosu (Top 10 Gündem Konusu) -->
    <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-neutral-900 border border-slate-200 dark:border-neutral-800 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">
                    🔥 Haftanın En Çok Konuşulan 10 Konusu & Etiketi
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Etkisiz Türkçe bağlaçlardan (stop-words) arındırılmış temiz frekans sıralaması
                </p>
            </div>
            <a href="/api/v1/analytics/pulse/{{ $report['location']['slug'] }}" target="_blank"
               class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                JSON API Çıktısı ↗
            </a>
        </div>

        @if(count($report['top_10_trending_topics']) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-neutral-800 text-slate-400">
                            <th class="py-2.5 font-semibold">Sıra</th>
                            <th class="py-2.5 font-semibold">Anahtar Kelime / Etiket</th>
                            <th class="py-2.5 font-semibold">Tür</th>
                            <th class="py-2.5 font-semibold text-right">Kullanım Hacmi</th>
                            <th class="py-2.5 font-semibold text-right">Şikayet/Sorun</th>
                            <th class="py-2.5 font-semibold text-right">Memnuniyet Oranı</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-neutral-800/60">
                        @foreach($report['top_10_trending_topics'] as $index => $trend)
                            <tr class="hover:bg-slate-50 dark:hover:bg-neutral-800/40 transition-colors">
                                <td class="py-3 font-bold text-slate-400">#{{ $index + 1 }}</td>
                                <td class="py-3 font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-1.5">
                                    <span class="text-sm">{{ $trend['is_hashtag'] ? '🏷️' : '💬' }}</span>
                                    <span>{{ $trend['term'] }}</span>
                                </td>
                                <td class="py-3 text-slate-500">
                                    {{ $trend['is_hashtag'] ? 'Hashtag' : 'Kelime' }}
                                </td>
                                <td class="py-3 text-right font-bold text-slate-800 dark:text-slate-200">
                                    {{ $trend['volume'] }} kez
                                </td>
                                <td class="py-3 text-right font-medium {{ $trend['complaints_volume'] > 0 ? 'text-rose-500 dark:text-rose-400' : 'text-slate-400' }}">
                                    {{ $trend['complaints_volume'] }}
                                </td>
                                <td class="py-3 text-right">
                                    <span class="inline-block px-2 py-0.5 rounded-md text-[11px] font-bold {{ $trend['sentiment_ratio'] >= 60 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' }}">
                                        %{{ $trend['sentiment_ratio'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="py-8 text-center text-slate-400 text-xs">
                Bu ilçe için henüz yeterli trend verisi birikmedi. Gönderiler paylaşıldıkça saatlik analiz motoru listeyi dolduracaktır.
            </div>
        @endif
    </div>

    <!-- B2B Kurumsal API Entegrasyon Kutusu -->
    <div class="p-4 sm:p-5 rounded-2xl bg-slate-900 text-white border border-slate-800 space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                <h3 class="text-sm font-bold">Belediyeler ve Yerel Basın için B2B Raporlama API'si</h3>
            </div>
            <span class="text-[11px] text-slate-400">Cache: 15 Dakika</span>
        </div>
        <p class="text-xs text-slate-300">
            Yerel gazeteciler, araştırma şirketleri veya belediye iletişim merkezleri bu verileri JSON formatında doğrudan sistemlerine entegre edebilir.
        </p>
        <div class="p-3 rounded-xl bg-black/60 font-mono text-[11px] text-slate-300 flex items-center justify-between gap-2 overflow-x-auto">
            <code>curl -X GET "{{ url('/api/v1/analytics/pulse/' . $report['location']['slug']) }}"</code>
            <button onclick="navigator.clipboard.writeText('{{ url('/api/v1/analytics/pulse/' . $report['location']['slug']) }}'); alert('API URL kopyalandı!')"
                    class="px-2 py-1 bg-blue-600 hover:bg-blue-500 text-white rounded text-[10px] font-bold shrink-0">
                Kopyala
            </button>
        </div>
    </div>

</div>
@endsection

