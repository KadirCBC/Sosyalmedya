@extends('layouts.app', ['title' => 'Esnaf Reklam Sihirbazı - Mahallenize 2 Dakikada Reklam Verin', 'maxWidth' => 'max-w-5xl'])

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6 sm:py-8" x-data="adWizard()">
    
    <!-- Üst Başlık & Motivasyon Barı -->
    <div class="mb-6 sm:mb-8 text-center sm:text-left flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 dark:border-neutral-800 pb-5">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 text-xs font-semibold mb-2">
                <span>⚡ 2 Dakikada Mahallenize Ulaşın</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                Self-Service Esnaf Reklam Sihirbazı
            </h1>
            <p class="text-sm text-slate-500 dark:text-neutral-400 mt-1">
                Yönetici onayı beklemeden ilçenizdeki binlerce mahalle sakininin akışına anında düşün.
            </p>
        </div>

        <!-- Adım Göstergesi (Stepper Pills) -->
        <div class="flex items-center justify-center gap-2">
            <template x-for="s in [1, 2, 3, 4]" :key="s">
                <div class="flex items-center">
                    <button type="button" @click="goToStep(s)" :disabled="s > maxStepReached"
                            class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all"
                            :class="{
                                'bg-blue-600 text-white shadow-md ring-2 ring-blue-400/50': currentStep === s,
                                'bg-emerald-600 text-white': currentStep > s,
                                'bg-slate-200 dark:bg-neutral-800 text-slate-500 dark:text-neutral-400': currentStep < s
                            }">
                        <span x-show="currentStep <= s" x-text="s"></span>
                        <svg x-show="currentStep > s" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                    <div x-show="s < 4" class="w-4 sm:w-6 h-0.5 bg-slate-200 dark:bg-neutral-800"
                         :class="{ 'bg-emerald-500': currentStep > s }"></div>
                </div>
            </template>
        </div>
    </div>

    <!-- Hata Bildirim Kutusu -->
    <div x-show="errorMessage" x-cloak class="mb-6 p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 text-rose-800 dark:text-rose-300 text-sm flex items-center justify-between">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0 text-rose-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <span x-text="errorMessage"></span>
        </div>
        <button type="button" @click="errorMessage = ''" class="text-rose-500 hover:text-rose-700 font-bold">&times;</button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- SOL SÜTUN: Form Adımları (7 Kolon) -->
        <div class="lg:col-span-7 bg-white dark:bg-neutral-900 rounded-2xl border border-slate-200 dark:border-neutral-800 p-6 sm:p-7 shadow-sm">
            
            <!-- ADIM 1: İçerik ve Görsel Yükleme -->
            <div x-show="currentStep === 1" x-transition>
                <div class="mb-5">
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Adım 1 / 4</span>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white mt-1">İçerik ve Görsel</h2>
                    <p class="text-xs text-slate-500 dark:text-neutral-400">Mahalle sakinlerinin dikkatini çekecek başlık ve kampanyayı yazın.</p>
                </div>

                <div class="space-y-4">
                    <!-- Başlık -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-neutral-300 mb-1">
                            İşletme veya Kampanya Başlığı <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" x-model="form.title" maxlength="120"
                               placeholder="Örn: Hasan Usta Çiğköfte & Dürüm Evi"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 outline-none transition">
                        <div class="flex justify-between text-[11px] text-slate-400 mt-1">
                            <span>Akışta ilk göze çarpan kalın başlık.</span>
                            <span x-text="`${form.title.length}/120`"></span>
                        </div>
                    </div>

                    <!-- Gönderi Metni -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-neutral-300 mb-1">
                            Kampanya veya Duyuru Metni <span class="text-rose-500">*</span>
                        </label>
                        <textarea x-model="form.content" rows="4" maxlength="500"
                                  placeholder="Örn: Bu haftaya özel tüm porsiyon çiğköftelerde ayran hediye! Paket servisimiz Keçiören geneli 20 dakikada kapınızda..."
                                  class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 outline-none transition"></textarea>
                        <div class="flex justify-between text-[11px] text-slate-400 mt-1">
                            <span>Kısa, net ve samimi bir çağrı yapın.</span>
                            <span x-text="`${form.content.length}/500`"></span>
                        </div>
                    </div>

                    <!-- Görsel Yükleme -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-neutral-300 mb-1">
                            Görsel Yükle (Önerilen)
                        </label>
                        <div class="border-2 border-dashed border-slate-300 dark:border-neutral-700 rounded-xl p-4 text-center hover:border-blue-500 transition relative bg-slate-50/50 dark:bg-neutral-800/40">
                            <input type="file" @change="handleImageUpload($event)" accept="image/*"
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-8 h-8 text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-xs font-semibold text-slate-700 dark:text-neutral-200">Görsel seçmek için tıklayın veya sürükleyin</span>
                                <span class="text-[11px] text-slate-400 mt-0.5">JPG, PNG veya WebP (Maksimum 5 MB)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="button" @click="validateAndNextStep(1)"
                            class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm shadow-md transition flex items-center gap-2">
                        <span>Hedef ve Bütçe Seçimine Geç</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            <!-- ADIM 2: Hedef İlçe, Süre ve Bütçe -->
            <div x-show="currentStep === 2" x-transition>
                <div class="mb-5">
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Adım 2 / 4</span>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white mt-1">Hedef Bölge ve Bütçe</h2>
                    <p class="text-xs text-slate-500 dark:text-neutral-400">Reklamınızın hangi ilçede kaç gün yayınlanacağını belirleyin.</p>
                </div>

                <div class="space-y-5">
                    <!-- Hedef İlçe Seçimi -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-neutral-300 mb-1">
                            Hedef İlçe <span class="text-rose-500">*</span>
                        </label>
                        <select x-model="form.location_id" @change="onLocationChange()"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 outline-none transition">
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" {{ ($defaultLocation && $defaultLocation->id == $loc->id) ? 'selected' : '' }}>
                                    {{ $loc->city }} - {{ $loc->district }}
                                </option>
                            @endforeach
                        </select>
                        <span class="text-[11px] text-slate-400 mt-1 block">İlanınız yalnızca bu ilçenin yerel akışında mahalle sakinlerine gösterilir.</span>
                    </div>

                    <!-- Hızlı Paket Seçimi Kartları -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-neutral-300 mb-2">
                            Önerilen Paketler
                        </label>
                        <div class="grid grid-cols-3 gap-2.5">
                            <button type="button" @click="applyPackage(3, 100)"
                                    :class="{'border-blue-600 bg-blue-50 dark:bg-blue-950/40 text-blue-900 dark:text-blue-200 ring-2 ring-blue-500/30': form.duration_days == 3 && form.daily_budget == 100}"
                                    class="p-3 rounded-xl border border-slate-200 dark:border-neutral-800 text-left transition hover:border-blue-400">
                                <div class="text-xs font-bold">3 Günlük</div>
                                <div class="text-[11px] text-slate-500 dark:text-neutral-400">100 TL / gün</div>
                                <div class="text-sm font-extrabold text-blue-600 dark:text-blue-400 mt-1">300 TL</div>
                            </button>
                            <button type="button" @click="applyPackage(7, 90)"
                                    :class="{'border-blue-600 bg-blue-50 dark:bg-blue-950/40 text-blue-900 dark:text-blue-200 ring-2 ring-blue-500/30': form.duration_days == 7 && form.daily_budget == 90}"
                                    class="p-3 rounded-xl border border-slate-200 dark:border-neutral-800 text-left transition hover:border-blue-400 relative">
                                <span class="absolute -top-2 right-2 bg-emerald-500 text-white text-[9px] px-1.5 py-0.5 rounded-full font-bold uppercase">%10 İndirim</span>
                                <div class="text-xs font-bold">7 Günlük</div>
                                <div class="text-[11px] text-slate-500 dark:text-neutral-400">90 TL / gün</div>
                                <div class="text-sm font-extrabold text-blue-600 dark:text-blue-400 mt-1">630 TL</div>
                            </button>
                            <button type="button" @click="applyPackage(14, 80)"
                                    :class="{'border-blue-600 bg-blue-50 dark:bg-blue-950/40 text-blue-900 dark:text-blue-200 ring-2 ring-blue-500/30': form.duration_days == 14 && form.daily_budget == 80}"
                                    class="p-3 rounded-xl border border-slate-200 dark:border-neutral-800 text-left transition hover:border-blue-400 relative">
                                <span class="absolute -top-2 right-2 bg-amber-500 text-white text-[9px] px-1.5 py-0.5 rounded-full font-bold uppercase">Popüler</span>
                                <div class="text-xs font-bold">14 Günlük</div>
                                <div class="text-[11px] text-slate-500 dark:text-neutral-400">80 TL / gün</div>
                                <div class="text-sm font-extrabold text-blue-600 dark:text-blue-400 mt-1">1.120 TL</div>
                            </button>
                        </div>
                    </div>

                    <!-- Özel Bütçe ve Süre Ayarları -->
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-neutral-800/60 border border-slate-200 dark:border-neutral-800 space-y-4">
                        <div>
                            <div class="flex justify-between items-center text-xs font-semibold mb-1">
                                <span class="text-slate-700 dark:text-neutral-200">Günlük Bütçe:</span>
                                <span class="text-blue-600 dark:text-blue-400 font-bold text-sm" x-text="`${form.daily_budget} TL / gün`"></span>
                            </div>
                            <input type="range" min="30" max="1000" step="10" x-model.number="form.daily_budget" @input="updateReach()"
                                   class="w-full accent-blue-600">
                        </div>

                        <div>
                            <div class="flex justify-between items-center text-xs font-semibold mb-1">
                                <span class="text-slate-700 dark:text-neutral-200">Yayın Süresi:</span>
                                <span class="text-blue-600 dark:text-blue-400 font-bold text-sm" x-text="`${form.duration_days} Gün`"></span>
                            </div>
                            <input type="range" min="1" max="30" step="1" x-model.number="form.duration_days" @input="updateReach()"
                                   class="w-full accent-blue-600">
                        </div>
                    </div>

                    <!-- Tahmini Erişim Göstergesi -->
                    <div class="p-4 rounded-xl bg-gradient-to-r from-blue-500/10 via-indigo-500/10 to-transparent border border-blue-200 dark:border-blue-900/50 flex items-center justify-between">
                        <div>
                            <div class="text-xs text-blue-700 dark:text-blue-300 font-semibold">Tahmini Mahalle Erişimi</div>
                            <div class="text-lg font-black text-slate-900 dark:text-white mt-0.5">
                                <span x-text="reachData.formatted">7.500 - 12.000</span>
                                <span class="text-xs font-normal text-slate-500 dark:text-neutral-400">gösterim</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-slate-500 dark:text-neutral-400">Toplam Tutar</div>
                            <div class="text-xl font-black text-emerald-600 dark:text-emerald-400" x-text="`${totalPriceFormatted} TL`"></div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-between">
                    <button type="button" @click="currentStep = 1"
                            class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-neutral-400 hover:text-slate-900 dark:hover:text-white transition">
                        &larr; Geri Dön
                    </button>
                    <button type="button" @click="validateAndNextStep(2)"
                            class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm shadow-md transition flex items-center gap-2">
                        <span>İletişim Butonu Seç</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            <!-- ADIM 3: Eyleme Çağrı Butonu (CTA) -->
            <div x-show="currentStep === 3" x-transition>
                <div class="mb-5">
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Adım 3 / 4</span>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white mt-1">İletişim & Aksiyon Butonu</h2>
                    <p class="text-xs text-slate-500 dark:text-neutral-400">Müşterilerinizin tek tıkla size nasıl ulaşacağını seçin.</p>
                </div>

                <div class="space-y-4">
                    <!-- CTA Türleri -->
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer p-3.5 rounded-xl border transition flex items-center gap-3"
                               :class="form.cta_type === 'whatsapp' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/30 ring-2 ring-emerald-500/30' : 'border-slate-200 dark:border-neutral-800'">
                            <input type="radio" value="whatsapp" x-model="form.cta_type" class="sr-only">
                            <span class="text-xl">💬</span>
                            <div>
                                <div class="text-xs font-bold text-slate-900 dark:text-white">WhatsApp'tan Yaz</div>
                                <div class="text-[10px] text-slate-500">Doğrudan sohbete yönlendirir</div>
                            </div>
                        </label>

                        <label class="cursor-pointer p-3.5 rounded-xl border transition flex items-center gap-3"
                               :class="form.cta_type === 'call' ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-950/30 ring-2 ring-blue-500/30' : 'border-slate-200 dark:border-neutral-800'">
                            <input type="radio" value="call" x-model="form.cta_type" class="sr-only">
                            <span class="text-xl">📞</span>
                            <div>
                                <div class="text-xs font-bold text-slate-900 dark:text-white">Telefonla Ara</div>
                                <div class="text-[10px] text-slate-500">Müşterinin tuş takımını açar</div>
                            </div>
                        </label>

                        <label class="cursor-pointer p-3.5 rounded-xl border transition flex items-center gap-3"
                               :class="form.cta_type === 'maps' ? 'border-amber-500 bg-amber-50/50 dark:bg-amber-950/30 ring-2 ring-amber-500/30' : 'border-slate-200 dark:border-neutral-800'">
                            <input type="radio" value="maps" x-model="form.cta_type" class="sr-only">
                            <span class="text-xl">📍</span>
                            <div>
                                <div class="text-xs font-bold text-slate-900 dark:text-white">Adrese Git / Harita</div>
                                <div class="text-[10px] text-slate-500">Google Haritalar navigasyonu</div>
                            </div>
                        </label>

                        <label class="cursor-pointer p-3.5 rounded-xl border transition flex items-center gap-3"
                               :class="form.cta_type === 'link' ? 'border-purple-500 bg-purple-50/50 dark:bg-purple-950/30 ring-2 ring-purple-500/30' : 'border-slate-200 dark:border-neutral-800'">
                            <input type="radio" value="link" x-model="form.cta_type" class="sr-only">
                            <span class="text-xl">🌐</span>
                            <div>
                                <div class="text-xs font-bold text-slate-900 dark:text-white">Web Sitesini Aç</div>
                                <div class="text-[10px] text-slate-500">Online sipariş / menü linki</div>
                            </div>
                        </label>
                    </div>

                    <!-- CTA Değer Girişi -->
                    <div class="mt-4">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-neutral-300 mb-1" x-text="ctaInputLabel"></label>
                        <input type="text" x-model="form.cta_value" :placeholder="ctaInputPlaceholder"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 outline-none transition">
                        <span class="text-[11px] text-slate-400 mt-1 block" x-text="ctaInputHelp"></span>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-between">
                    <button type="button" @click="currentStep = 2"
                            class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-neutral-400 hover:text-slate-900 dark:hover:text-white transition">
                        &larr; Geri Dön
                    </button>
                    <button type="button" @click="validateAndNextStep(3)"
                            class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm shadow-md transition flex items-center gap-2">
                        <span>Özet ve Güvenli Ödemeye Geç</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            <!-- ADIM 4: Ödeme ve Onay Ekranı (PayTR Entegrasyonu) -->
            <div x-show="currentStep === 4" x-transition>
                <div class="mb-5">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Adım 4 / 4 - Son Adım</span>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white mt-1">Ödeme ve Kampanyayı Başlat</h2>
                    <p class="text-xs text-slate-500 dark:text-neutral-400">Ödemeniz tamamlandığı anda ilanınız yerel akışta otomatik olarak yayına alınacaktır.</p>
                </div>

                <!-- Sipariş Özeti Kutusu -->
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-neutral-800/60 border border-slate-200 dark:border-neutral-800 mb-5 space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-neutral-400">Kampanya Başlığı:</span>
                        <span class="font-bold text-slate-900 dark:text-white text-right truncate max-w-[200px]" x-text="form.title"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-neutral-400">Hedef İlçe:</span>
                        <span class="font-bold text-slate-900 dark:text-white" x-text="selectedLocationName"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-neutral-400">Yayın Süresi:</span>
                        <span class="font-bold text-slate-900 dark:text-white" x-text="`${form.duration_days} Gün`"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-neutral-400">Günlük Bütçe:</span>
                        <span class="font-bold text-slate-900 dark:text-white" x-text="`${form.daily_budget} TL`"></span>
                    </div>
                    <div class="border-t border-slate-200 dark:border-neutral-700 pt-2 flex justify-between items-center text-sm">
                        <span class="font-extrabold text-slate-900 dark:text-white">Toplam Ödenecek Tutar:</span>
                        <span class="text-lg font-black text-emerald-600 dark:text-emerald-400" x-text="`${totalPriceFormatted} TL`"></span>
                    </div>
                </div>

                <!-- PayTR Iframe veya Ödeme Başlatma Butonu -->
                <div x-show="!paymentIframeUrl" class="text-center py-4">
                    <button type="button" @click="submitAndStartPayment()" :disabled="isLoading"
                            class="w-full py-3.5 px-6 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-extrabold text-base shadow-lg transition flex items-center justify-center gap-2">
                        <span x-show="!isLoading">🔒 PayTR ile Güvenli Öde (<span x-text="`${totalPriceFormatted} TL`"></span>)</span>
                        <span x-show="isLoading" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            Ödeme Oturumu Başlatılıyor...
                        </span>
                    </button>

                    <div class="flex items-center justify-center gap-4 mt-4 text-[11px] text-slate-400">
                        <span class="flex items-center gap-1">🛡️ 256-Bit SSL Şifreleme</span>
                        <span class="flex items-center gap-1">⚡ Otomatik Anında Aktivasyon</span>
                        <span class="flex items-center gap-1">💳 Kredi / Banka Kartı</span>
                    </div>
                </div>

                <!-- Yüklenen PayTR Iframe Penceresi -->
                <div x-show="paymentIframeUrl" class="border border-slate-200 dark:border-neutral-800 rounded-xl overflow-hidden mt-4">
                    <iframe :src="paymentIframeUrl" class="w-full h-[580px] border-0" id="paytriframe"></iframe>
                </div>

                <div class="mt-4 flex items-center justify-start">
                    <button type="button" @click="currentStep = 3" :disabled="paymentIframeUrl !== ''"
                            class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-neutral-400 hover:text-slate-900 dark:hover:text-white transition">
                        &larr; Bilgileri Düzenle
                    </button>
                </div>
            </div>
        </div>

        <!-- SAĞ SÜTUN: Canlı Mobil Kart Önizlemesi (Sticky - 5 Kolon) -->
        <div class="lg:col-span-5 sticky top-20">
            <div class="bg-white dark:bg-neutral-900 rounded-2xl border border-slate-200 dark:border-neutral-800 p-4 shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-neutral-800 pb-3 mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-neutral-400 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Canlı Akış Önizlemesi
                    </span>
                    <span class="text-[11px] text-slate-400">Bölge Sakinlerinin Gözünden</span>
                </div>

                <!-- Simüle Edilmiş Sponsorlu Gönderi Kartı -->
                <article class="p-4 rounded-xl border border-amber-200/70 dark:border-amber-900/40 bg-gradient-to-r from-amber-500/[0.04] via-transparent to-transparent">
                    <div class="flex items-start gap-3">
                        <!-- İşletme Avatarı -->
                        <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-amber-500 to-orange-400 text-white font-bold flex items-center justify-center text-sm shrink-0 shadow-sm">
                            🏪
                        </div>

                        <div class="flex-1 min-w-0">
                            <!-- Üst Bar -->
                            <div class="flex items-center justify-between gap-1 mb-1">
                                <div class="flex items-center gap-1.5 truncate">
                                    <span class="font-bold text-[14px] text-slate-900 dark:text-white truncate"
                                          x-text="form.title || 'İşletme Adınız'"></span>
                                    <span class="text-xs text-amber-600 dark:text-amber-400 font-semibold flex items-center">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    </span>
                                </div>
                                <span class="shrink-0 px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-300/50">
                                    Sponsorlu
                                </span>
                            </div>

                            <!-- Konum Rozeti -->
                            <div class="text-[11px] text-slate-500 dark:text-neutral-400 mb-2 flex items-center gap-1">
                                <span>📍 <span x-text="selectedLocationName">Keçiören, Ankara</span></span>
                            </div>

                            <!-- Metin -->
                            <p class="text-[13px] leading-relaxed text-slate-800 dark:text-neutral-200 mb-3 whitespace-pre-line"
                               x-text="form.content || 'Kampanya veya duyuru metniniz burada bu şekilde görüntülenecektir...'"></p>

                            <!-- Görsel ve Canlı CTA -->
                            <div class="relative rounded-xl overflow-hidden border border-amber-200/60 dark:border-amber-900/40 mb-2 group bg-slate-100 dark:bg-neutral-800 min-h-[160px] flex items-center justify-center">
                                <template x-if="imagePreview">
                                    <img :src="imagePreview" alt="Önizleme" class="w-full max-h-[220px] object-cover">
                                </template>
                                <template x-if="!imagePreview">
                                    <div class="text-slate-400 text-xs flex flex-col items-center p-6 text-center">
                                        <svg class="w-8 h-8 mb-1 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span>Görsel yüklendiğinde burada belirecektir</span>
                                    </div>
                                </template>

                                <!-- Canlı CTA Butonu -->
                                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/85 via-black/40 to-transparent p-2.5 flex items-center justify-between">
                                    <span class="text-[10px] font-medium text-white/90">Özel Esnaf İlanı</span>
                                    <div class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-amber-500 text-slate-950 font-bold text-xs shadow-md">
                                        <span x-text="ctaButtonLabel">WhatsApp'tan Yaz</span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>

                <!-- Şeffaflık & İstatistik Notu -->
                <div class="mt-4 p-3 rounded-xl bg-slate-50 dark:bg-neutral-800/50 border border-slate-100 dark:border-neutral-800 text-[11px] text-slate-500 dark:text-neutral-400 space-y-1">
                    <div class="flex items-center gap-1 font-semibold text-slate-700 dark:text-neutral-300">
                        <span>📊 Kampanya Sonrası Rapor</span>
                    </div>
                    <p>Kampanyanız bittiğinde telefonunuza kaç kişinin gördüğünü ve tıkladığını bildiren özet SMS gönderilir.</p>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function adWizard() {
    return {
        currentStep: 1,
        maxStepReached: 1,
        isLoading: false,
        errorMessage: '',
        paymentIframeUrl: '',
        imageFile: null,
        imagePreview: null,
        
        form: {
            title: '',
            content: '',
            location_id: '{{ $defaultLocation ? $defaultLocation->id : 1 }}',
            duration_days: 3,
            daily_budget: 100,
            cta_type: 'whatsapp',
            cta_value: '',
        },

        reachData: {
            formatted: '7.500 - 12.000',
            min: 7500,
            max: 12000
        },

        locationsMap: {
            @foreach($locations as $loc)
                '{{ $loc->id }}': '{{ $loc->district }}, {{ $loc->city }}',
            @endforeach
        },

        get selectedLocationName() {
            return this.locationsMap[this.form.location_id] || 'Bölgeniz';
        },

        get totalPrice() {
            return this.form.duration_days * this.form.daily_budget;
        },

        get totalPriceFormatted() {
            return new Intl.NumberFormat('tr-TR').format(this.totalPrice);
        },

        get ctaButtonLabel() {
            switch(this.form.cta_type) {
                case 'whatsapp': return 'WhatsApp\'tan Yaz';
                case 'call': return 'Hemen Ara';
                case 'maps': return 'Yol Tarifi Al';
                case 'link': return 'Menü / Siteyi Gör';
                default: return 'Detayları İncele';
            }
        },

        get ctaInputLabel() {
            switch(this.form.cta_type) {
                case 'whatsapp': return 'WhatsApp Telefon Numarası';
                case 'call': return 'Aranacak Telefon Numarası';
                case 'maps': return 'İşletme Adresi / Google Maps Linki';
                case 'link': return 'Web Sitesi veya Menü URL';
                default: return 'İletişim Değeri';
            }
        },

        get ctaInputPlaceholder() {
            switch(this.form.cta_type) {
                case 'whatsapp': return '0555 123 45 67';
                case 'call': return '0312 456 78 90';
                case 'maps': return 'Örn: Kuşcağız Mah. Sanatoryum Cad. No:14 Keçiören';
                case 'link': return 'https://instagram.com/isletmeniz veya web adresiniz';
                default: return '';
            }
        },

        get ctaInputHelp() {
            switch(this.form.cta_type) {
                case 'whatsapp': return 'Kullanıcı butona bastığında doğrudan bu numarayla WhatsApp sohbeti açılır.';
                case 'call': return 'Kullanıcı butona bastığında telefonun arama ekranı bu numarayla açılır.';
                case 'maps': return 'Kullanıcı butona bastığında Google Haritalar navigasyonu açılır.';
                case 'link': return 'Kullanıcı butona bastığında belirttiğiniz internet adresi açılır.';
                default: return '';
            }
        },

        goToStep(s) {
            if (s <= this.maxStepReached) {
                this.currentStep = s;
            }
        },

        applyPackage(days, dailyBudget) {
            this.form.duration_days = days;
            this.form.daily_budget = dailyBudget;
            this.updateReach();
        },

        handleImageUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            if (file.size > 5 * 1024 * 1024) {
                this.errorMessage = 'Görsel boyutu 5 MB\'tan büyük olamaz.';
                return;
            }

            this.imageFile = file;
            const reader = new FileReader();
            reader.onload = (e) => {
                this.imagePreview = e.target.result;
            };
            reader.readAsDataURL(file);
        },

        onLocationChange() {
            this.updateReach();
        },

        updateReach() {
            const totalBudget = this.form.duration_days * this.form.daily_budget;
            const minReach = Math.round(totalBudget * 25);
            const maxReach = Math.round(totalBudget * 40);
            this.reachData.formatted = `${new Intl.NumberFormat('tr-TR').format(minReach)} - ${new Intl.NumberFormat('tr-TR').format(maxReach)}`;
        },

        validateAndNextStep(step) {
            this.errorMessage = '';

            if (step === 1) {
                if (!this.form.title.trim()) {
                    this.errorMessage = 'Lütfen kampanya veya işletme başlığı girin.';
                    return;
                }
                if (!this.form.content.trim()) {
                    this.errorMessage = 'Lütfen duyuru / kampanya metnini girin.';
                    return;
                }
                this.currentStep = 2;
                this.maxStepReached = Math.max(this.maxStepReached, 2);
            } else if (step === 2) {
                if (!this.form.location_id) {
                    this.errorMessage = 'Lütfen bir hedef ilçe seçin.';
                    return;
                }
                if (this.form.daily_budget < 20) {
                    this.errorMessage = 'Günlük bütçe en az 20 TL olmalıdır.';
                    return;
                }
                this.currentStep = 3;
                this.maxStepReached = Math.max(this.maxStepReached, 3);
            } else if (step === 3) {
                if (!this.form.cta_value.trim()) {
                    this.errorMessage = 'Lütfen seçtiğiniz iletişim yöntemi için telefon, adres veya link bilgisini girin.';
                    return;
                }
                this.currentStep = 4;
                this.maxStepReached = Math.max(this.maxStepReached, 4);
            }
        },

        async submitAndStartPayment() {
            this.isLoading = true;
            this.errorMessage = '';

            try {
                const formData = new FormData();
                formData.append('title', this.form.title);
                formData.append('content', this.form.content);
                formData.append('location_id', this.form.location_id);
                formData.append('duration_days', this.form.duration_days);
                formData.append('daily_budget', this.form.daily_budget);
                formData.append('cta_type', this.form.cta_type);
                formData.append('cta_value', this.form.cta_value);

                if (this.imageFile) {
                    formData.append('image', this.imageFile);
                }

                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                const response = await fetch('{{ route('ads.store') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Ödeme oturumu oluşturulurken bir hata oluştu.');
                }

                // If iframe URL received, show it
                if (data.iframe_url) {
                    this.paymentIframeUrl = data.iframe_url;
                } else {
                    window.location.href = `/ads/success/${data.ad_id}`;
                }

            } catch (err) {
                this.errorMessage = err.message || 'Bir bağlantı hatası meydana geldi.';
            } finally {
                this.isLoading = false;
            }
        }
    }
}
</script>
@endsection
