@props(['reputation' => null, 'showPoints' => true])

@php
    $level = $reputation['level'] ?? ($reputation->level ?? 'Yeni Komşu');
    $points = (int) ($reputation['points'] ?? ($reputation->points ?? 0));
    $badgeIcon = $reputation['badge_icon'] ?? match($level) {
        'Gönüllü Muhtar' => '🎖️',
        'Mahalle Esnafı' => '🏪',
        'Mahalle Sakini' => '🏘️',
        default => '🌱'
    };

    $colors = match($level) {
        'Gönüllü Muhtar' => 'bg-amber-100 dark:bg-amber-950/70 text-amber-800 dark:text-amber-300 border-amber-300/60 dark:border-amber-800/60',
        'Mahalle Esnafı' => 'bg-emerald-100 dark:bg-emerald-950/70 text-emerald-800 dark:text-emerald-300 border-emerald-300/60 dark:border-emerald-800/60',
        'Mahalle Sakini' => 'bg-blue-100 dark:bg-blue-950/70 text-blue-800 dark:text-blue-300 border-blue-300/60 dark:border-blue-800/60',
        default => 'bg-slate-100 dark:bg-neutral-800 text-slate-700 dark:text-neutral-300 border-slate-300/60 dark:border-neutral-700/60',
    };
@endphp

<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $colors }} shrink-0 tracking-tight select-none"
      title="İtibar Puanı: {{ number_format($points, 0, ',', '.') }}">
    <span class="text-[11px] leading-none">{{ $badgeIcon }}</span>
    <span>{{ $level }}</span>
    @if($showPoints)
        <span class="opacity-60 font-semibold font-mono">({{ $points }}p)</span>
    @endif
</span>

