<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PayTR Güvenli Esnaf Aboneliği - Test Sandbox</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 font-sans p-4 antialiased text-slate-800">
    <div class="max-w-md mx-auto bg-white rounded-xl shadow-md border border-slate-200 overflow-hidden">
        
        <!-- Header -->
        <div class="bg-[#1b2b48] text-white p-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xl font-black text-white">Pay<span class="text-emerald-400">TR</span></span>
                <span class="text-[10px] bg-emerald-500/30 text-emerald-300 px-2 py-0.5 rounded font-mono">ABONELİK SANDBOX</span>
            </div>
            <div class="text-right">
                <div class="text-[10px] text-slate-300">Aylık Paket</div>
                <div class="text-base font-bold text-emerald-400">{{ number_format($amount, 2, ',', '.') }} TL / Ay</div>
            </div>
        </div>

        <div class="p-5 space-y-4 text-xs">
            <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg flex items-start gap-2">
                <span class="text-sm">🏪</span>
                <span><strong>{{ $profile->business_name }}</strong> için "Doğrulanmış Esnaf Paketi" aboneliği başlatılacaktır.</span>
            </div>

            <!-- Simüle Kart Girişleri -->
            <div>
                <label class="block font-semibold text-slate-600 mb-1">Kart Sahibi</label>
                <input type="text" value="{{ $profile->business_name }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg bg-slate-50 font-mono">
            </div>

            <div>
                <label class="block font-semibold text-slate-600 mb-1">Kart Numarası</label>
                <input type="text" value="5528 **** **** 0002" class="w-full px-3 py-2 border border-slate-300 rounded-lg bg-slate-50 font-mono">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-600 mb-1">SKT</label>
                    <input type="text" value="10/29" class="w-full px-3 py-2 border border-slate-300 rounded-lg bg-slate-50 font-mono">
                </div>
                <div>
                    <label class="block font-semibold text-slate-600 mb-1">CVV</label>
                    <input type="text" value="888" class="w-full px-3 py-2 border border-slate-300 rounded-lg bg-slate-50 font-mono">
                </div>
            </div>

            <!-- Simülasyon Formu -->
            <form id="subscriptionPaymentForm" method="POST" action="/api/v1/payment/paytr/callback">
                <input type="hidden" name="merchant_oid" value="BIZ_SUB_{{ $profile->id }}_{{ time() }}">
                <input type="hidden" name="status" value="success">
                <input type="hidden" name="total_amount" value="{{ (int) ($amount * 100) }}">
                <input type="hidden" name="hash" value="TEST_MOCK_HASH">

                <button type="submit" id="payBtn"
                        class="w-full mt-4 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg shadow transition flex items-center justify-center gap-2 text-sm">
                    <span>✅ Aboneliği Başlat & Rozeti Aktif Et</span>
                </button>
            </form>
        </div>

        <div class="bg-slate-50 border-t border-slate-200 p-3 text-center text-[10px] text-slate-400">
            PayTR Recurring API &bull; 256 Bit SSL Güvenlik
        </div>
    </div>

    <script>
        document.getElementById('subscriptionPaymentForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = document.getElementById('payBtn');
            btn.disabled = true;
            btn.innerHTML = 'Abonelik İşleniyor...';

            const formData = new FormData(this);
            try {
                await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json' }
                });

                if (window.parent && window.parent !== window) {
                    window.parent.location.href = '/business/{{ $profile->id }}';
                } else {
                    window.location.href = '/business/{{ $profile->id }}';
                }
            } catch (err) {
                alert('Ödeme simülasyon hatası: ' + err.message);
                btn.disabled = false;
            }
        });
    </script>
</body>
</html>

