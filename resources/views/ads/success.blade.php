@extends('layouts.app', ['title' => 'Reklamınız Yayında! - Meydan'])

@section('content')
<div class="max-w-xl mx-auto px-4 py-12 text-center">
    
    <!-- Kutlama İkonu -->
    <div class="w-20 h-20 mx-auto rounded-full bg-emerald-100 dark:bg-emerald-950/60 border border-emerald-300 dark:border-emerald-800 flex items-center justify-center text-3xl shadow-lg mb-6">
        🎉
    </div>

    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 text-xs font-bold uppercase tracking-wider mb-2">
        Ödeme Başarılı &bull; İlanınız Akışta
    </span>

    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mb-3">
        Tebrikler! Reklamınız Yayına Alındı
    </h1>

    <p class="text-sm text-slate-600 dark:text-neutral-400 mb-8 max-w-md mx-auto leading-relaxed">
        İlanınız <span class="font-bold text-slate-900 dark:text-white">{{ $ad->location->district ?? 'Bölgeniz' }}</span> yerel akışındaki kullanıcıların akışında gösterilmeye başladı.
    </p>

    <!-- Kampanya Özet Kartı -->
    <div class="bg-white dark:bg-neutral-900 rounded-2xl border border-slate-200 dark:border-neutral-800 p-6 text-left shadow-sm mb-6 space-y-3.5 text-xs">
        <div class="flex justify-between items-center pb-3 border-b border-slate-100 dark:border-neutral-800">
            <span class="text-slate-500 dark:text-neutral-400">İlan Başlığı:</span>
            <span class="font-bold text-slate-900 dark:text-white truncate max-w-[220px]">{{ $ad->title }}</span>
        </div>

        <div class="flex justify-between items-center pb-3 border-b border-slate-100 dark:border-neutral-800">
            <span class="text-slate-500 dark:text-neutral-400">Hedef Bölge:</span>
            <span class="font-bold text-slate-900 dark:text-white">📍 {{ $ad->location->city ?? '' }} / {{ $ad->location->district ?? '' }}</span>
        </div>

        <div class="flex justify-between items-center pb-3 border-b border-slate-100 dark:border-neutral-800">
            <span class="text-slate-500 dark:text-neutral-400">Yayın Periyodu:</span>
            <span class="font-bold text-slate-900 dark:text-white">{{ $ad->duration_days }} Gün ({{ $ad->starts_at?->format('d.m.Y') }} - {{ $ad->ends_at?->format('d.m.Y') }})</span>
        </div>

        <div class="flex justify-between items-center pb-3 border-b border-slate-100 dark:border-neutral-800">
            <span class="text-slate-500 dark:text-neutral-400">Ödenen Tutar:</span>
            <span class="font-extrabold text-emerald-600 dark:text-emerald-400 text-sm">{{ number_format($ad->paid_amount, 2, ',', '.') }} TL</span>
        </div>

        <div class="flex justify-between items-center">
            <span class="text-slate-500 dark:text-neutral-400">Sipariş No (Referans):</span>
            <span class="font-mono text-[11px] text-slate-600 dark:text-neutral-400">{{ $ad->merchant_oid }}</span>
        </div>
    </div>

    <!-- Rapor ve Bilgilendirme Notu -->
    <div class="p-4 rounded-xl bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-900/40 text-xs text-blue-900 dark:text-blue-200 text-left mb-8 flex items-start gap-3">
        <span class="text-lg shrink-0">📲</span>
        <div>
            <div class="font-bold mb-0.5">Kampanya Bitiş Raporu</div>
            <p class="leading-relaxed">
                Yayın süreniz tamamlandığında telefonunuza ve e-postanıza reklamınızın kaç kişiye ulaştığını ve kaç kişinin tıkladığını bildiren performans özeti gönderilecektir.
            </p>
        </div>
    </div>

    <!-- Aksiyon Butonları -->
    <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
        <a href="/" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-md transition text-center">
            Meydan Akışına Dön
        </a>
        <a href="{{ route('ads.create') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl border border-slate-300 dark:border-neutral-700 hover:bg-slate-50 dark:hover:bg-neutral-800 text-slate-700 dark:text-neutral-200 font-semibold text-sm transition text-center">
            Yeni Reklam Oluştur
        </a>
    </div>

</div>
@endsection

