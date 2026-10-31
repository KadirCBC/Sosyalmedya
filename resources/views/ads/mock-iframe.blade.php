<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PayTR Güvenli Ödeme - Test Sandbox</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 font-sans p-4 antialiased text-slate-800">
    <div class="max-w-md mx-auto bg-white rounded-xl shadow-md border border-slate-200 overflow-hidden">
        
        <!-- PayTR Header -->
        <div class="bg-[#1b2b48] text-white p-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xl font-black tracking-tight text-white">Pay<span class="text-emerald-400">TR</span></span>
                <span class="text-[10px] bg-emerald-500/30 text-emerald-300 px-2 py-0.5 rounded font-mono">SANDBOX / TEST</span>
            </div>
            <div class="text-right">
                <div class="text-[10px] text-slate-300">Ödenecek Tutar</div>
                <div class="text-base font-bold text-emerald-400">{{ number_format($ad->paid_amount, 2, ',', '.') }} TL</div>
            </div>
        </div>

        <div class="p-5 space-y-4 text-xs">
            <!-- Bilgi Notu -->
            <div class="p-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-lg flex items-start gap-2">
                <span class="text-sm">⚠️</span>
                <span>Bu bir test ödeme simülasyonudur. Gerçek kart bilgisi girmenize gerek yoktur. Aşağıdaki butona tıklayarak ödemeyi onaylayabilirsiniz.</span>
            </div>

            <!-- Kart Sahibi -->
            <div>
                <label class="block font-semibold text-slate-600 mb-1">Kart Üzerindeki İsim</label>
                <input type="text" value="MEYDAN TEST ESNAF" class="w-full px-3 py-2 border border-slate-300 rounded-lg bg-slate-50 text-slate-700 outline-none">
            </div>

            <!-- Kart Numarası -->
            <div>
                <label class="block font-semibold text-slate-600 mb-1">Kart Numarası</label>
                <input type="text" value="4543 **** **** 0001" class="w-full px-3 py-2 border border-slate-300 rounded-lg bg-slate-50 text-slate-700 font-mono outline-none">
            </div>

            <!-- SKT & CVV -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Son Kullanma (AA/YY)</label>
                    <input type="text" value="12/28" class="w-full px-3 py-2 border border-slate-300 rounded-lg bg-slate-50 text-slate-700 font-mono outline-none">
                </div>
                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Güvenlik Kodu (CVV)</label>
                    <input type="text" value="123" class="w-full px-3 py-2 border border-slate-300 rounded-lg bg-slate-50 text-slate-700 font-mono outline-none">
                </div>
            </div>

            <!-- Simülasyon Formu: Doğrudan PayTR Webhook'una POST gönderir -->
            <form id="paymentForm" method="POST" action="/api/v1/payment/paytr/callback">
                <input type="hidden" name="merchant_oid" value="{{ $ad->merchant_oid }}">
                <input type="hidden" name="status" value="success">
                <input type="hidden" name="total_amount" value="{{ (int) ($ad->paid_amount * 100) }}">
                <input type="hidden" name="hash" value="TEST_MOCK_HASH">
                <input type="hidden" name="payment_type" value="card">

                <button type="submit" id="payBtn"
                        class="w-full mt-4 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg shadow transition flex items-center justify-center gap-2 text-sm">
                    <span>✅ Ödemeyi Tamamla ve İlanı Başlat</span>
                </button>
            </form>
        </div>

        <div class="bg-slate-50 border-t border-slate-200 p-3 text-center text-[10px] text-slate-400">
            PayTR Ödeme ve Elektronik Para Kuruluşu A.Ş. &bull; 256 Bit SSL
        </div>
    </div>

    <script>
        document.getElementById('paymentForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = document.getElementById('payBtn');
            btn.disabled = true;
            btn.innerHTML = 'Ödeme İşleniyor...';

            const formData = new FormData(this);

            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                // Ödeme başarılı webhook tamamlandıktan sonra ana pencereyi yönlendir
                if (window.parent && window.parent !== window) {
                    window.parent.location.href = '/ads/success/{{ $ad->id }}';
                } else {
                    window.location.href = '/ads/success/{{ $ad->id }}';
                }
            } catch (err) {
                alert('Ödeme simülasyon hatası: ' + err.message);
                btn.disabled = false;
                btn.innerHTML = 'Tekrar Dene';
            }
        });
    </script>
</body>
</html>

