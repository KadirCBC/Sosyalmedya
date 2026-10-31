@extends('layouts.app', ['title' => 'Doğrulanmış Esnaf Paketi - Meydan', 'maxWidth' => 'max-w-xl'])

@section('content')
<div class="px-4 py-8 text-center" x-data="businessSubscription({{ $profile->id }})">
    
    <!-- Başlık & İkon -->
    <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 text-white flex items-center justify-center text-3xl shadow-lg mb-4">
        🏪
    </div>

    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 text-xs font-bold uppercase tracking-wider mb-2">
        Aylık Sabit Esnaf Paketi
    </span>

    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
        {{ $profile->business_name }}
    </h1>
    <p class="text-xs text-slate-500 dark:text-neutral-400 mt-1 max-w-sm mx-auto">
        Mahallenizde güven inşa edin, menünüzü sergileyin ve müşterilerinizden doğrudan WhatsApp siparişi alın.
    </p>

    <!-- Fiyatlandırma Kartı -->
    <div class="mt-6 mb-6 p-6 rounded-3xl bg-white dark:bg-neutral-900 border-2 border-emerald-500 shadow-xl text-left relative overflow-hidden">
        <div class="absolute -top-3 right-6 bg-emerald-500 text-white px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider shadow">
            En Popüler
        </div>

        <div class="flex items-baseline justify-between mb-4">
            <div>
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Doğrulanmış Esnaf</h3>
                <span class="text-xs text-slate-400">Aylık Sabit Üyelik</span>
            </div>
            <div class="text-right">
                <span class="text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ number_format($monthlyPrice, 0) }} TL</span>
                <span class="text-xs text-slate-400">/ ay</span>
            </div>
        </div>

        <!-- Paket Özellikleri -->
        <ul class="space-y-3 text-xs text-slate-700 dark:text-neutral-200 border-t border-slate-100 dark:border-neutral-800 pt-4 mb-6">
            <li class="flex items-center gap-2.5">
                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950/80 flex items-center justify-center text-xs font-bold shrink-0">✓</span>
                <span><strong>Doğrulanmış Esnaf Rozeti:</strong> Paylaşımlarında adının yanında yeşil onay tiki görünür.</span>
            </li>
            <li class="flex items-center gap-2.5">
                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950/80 flex items-center justify-center text-xs font-bold shrink-0">✓</span>
                <span><strong>Menü & Katalog Vitrini:</strong> Ürün ve fiyatlarınızı sergileyip WhatsApp siparişi toplayın.</span>
            </li>
            <li class="flex items-center gap-2.5">
                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950/80 flex items-center justify-center text-xs font-bold shrink-0">✓</span>
                <span><strong>Haftalık 2 Ücretsiz Boost:</strong> İlanlarınızı ve duyurularınızı ilçe akışında öne çıkarın.</span>
            </li>
            <li class="flex items-center gap-2.5">
                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950/80 flex items-center justify-center text-xs font-bold shrink-0">✓</span>
                <span><strong>Müşteri Değerlendirmeleri:</strong> Mahallelinin puan ve yorumlarını toplayarak itibarınızı artırın.</span>
            </li>
        </ul>

        <!-- Abone Ol Butonu -->
        <div x-show="!paymentIframeUrl">
            <button type="button" @click="startPayment()" :disabled="loading"
                    class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-black text-sm shadow-lg transition flex items-center justify-center gap-2">
                <span x-show="!loading">🔒 PayTR ile Güvenli Abone Ol ({{ number_format($monthlyPrice, 0) }} TL)</span>
                <span x-show="loading">Ödeme Yükleniyor...</span>
            </button>
            <div class="text-[11px] text-slate-400 text-center mt-2">
                İstediğiniz an tek tıkla iptal edebilirsiniz. Taahhüt yoktur.
            </div>
        </div>

        <!-- PayTR Iframe Container -->
        <div x-show="paymentIframeUrl" class="mt-4 border border-slate-200 dark:border-neutral-800 rounded-xl overflow-hidden">
            <iframe :src="paymentIframeUrl" class="w-full h-[540px] border-0"></iframe>
        </div>
    </div>

</div>

<script>
function businessSubscription(profileId) {
    return {
        profileId: profileId,
        loading: false,
        paymentIframeUrl: '',

        async startPayment() {
            this.loading = true;
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const res = await fetch(`/api/v1/business/${this.profileId}/subscribe`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const data = await res.json();
                if (data.success && data.iframe_url) {
                    this.paymentIframeUrl = data.iframe_url;
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endsection

