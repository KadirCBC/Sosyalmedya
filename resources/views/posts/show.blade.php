<x-app-layout
    :title="$ogTitle"
    :ogTitle="$ogTitle"
    :ogDescription="$ogDescription"
    :ogImage="route('posts.og-image', $post['id'])"
    :ogUrl="$ogUrl"
    ogType="article">

    <!-- Üst Gezinti / Geri Butonu -->
    <header class="sticky top-0 z-40 backdrop-blur-md bg-white/85 dark:bg-black/85 border-b border-slate-200 dark:border-neutral-800 px-4 py-3 flex items-center gap-3">
        <a href="{{ route('home') }}" class="p-2 -ml-2 rounded-full hover:bg-slate-100 dark:hover:bg-neutral-800 text-slate-700 dark:text-neutral-300 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <h1 class="text-base font-bold text-slate-900 dark:text-white">Gönderi</h1>
    </header>

    <!-- Post Kartı -->
    <x-post-card :post="$post" />

    <!-- Yorumlar Bölümü -->
    <section class="p-4 bg-slate-50/50 dark:bg-neutral-950/40">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-neutral-500 mb-3">
            Yorumlar ({{ $post['comments_count'] ?? 0 }})
        </h3>

        <div class="space-y-3">
            @forelse($comments as $comment)
                <div class="p-3.5 rounded-xl bg-white dark:bg-neutral-900 border border-slate-100 dark:border-neutral-800 flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-blue-600/20 text-blue-500 font-bold flex items-center justify-center text-xs shrink-0">
                        {{ mb_substr($comment['user']['name'] ?? 'U', 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="font-bold text-xs text-slate-900 dark:text-white">{{ $comment['user']['name'] ?? 'Kullanıcı' }}</span>
                            <span class="text-[11px] text-slate-400">{{ '@' . ($comment['user']['username'] ?? 'user') }}</span>
                            <x-reputation-badge :reputation="$comment['user']['reputation'] ?? null" />
                            <span class="text-[11px] text-slate-400">·</span>
                            <span class="text-[11px] text-slate-400">{{ $comment['created_at_human'] ?? '' }}</span>
                        </div>
                        <p class="text-xs text-slate-700 dark:text-neutral-200 mt-1 whitespace-pre-line">{{ $comment['content'] ?? '' }}</p>
                    </div>
                </div>
            @empty
                <p class="text-xs text-slate-400 text-center py-6">İlk yorumu sen yap!</p>
            @endforelse
        </div>
    </section>
</x-app-layout>

