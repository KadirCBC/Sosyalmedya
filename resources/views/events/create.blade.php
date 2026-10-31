@extends('layouts.app', ['title' => 'Meydan - Yeni Mahalle Buluşması Oluştur'])

@section('content')
<div class="p-4 sm:p-6 space-y-6"
     x-data="{
         title: '',
         description: '',
         category: 'hobi_oyun',
         eventDate: '',
         eventTime: '19:00',
         venueType: 'sponsored', // 'sponsored' veya 'custom'
         selectedBusinessId: '{{ $sponsoredVenues->first()->id ?? '' }}',
         venueName: '',
         address: '',
         maxParticipants: '',
         submitting: false,

         async submitEvent() {
             if (!this.title.trim() || !this.description.trim() || !this.eventDate) {
                 $dispatch('toast', 'Lütfen başlık, açıklama ve tarih alanlarını doldurunuz.');
                 return;
             }

             this.submitting = true;
             const fullDateTime = `${this.eventDate} ${this.eventTime}:00`;

             const payload = {
                 title: this.title,
                 description: this.description,
                 category: this.category,
                 event_date: fullDateTime,
                 max_participants: this.maxParticipants ? parseInt(this.maxParticipants) : null,
                 location_id: {{ $location->id }},
             };

             if (this.venueType === 'sponsored' && this.selectedBusinessId) {
                 payload.business_profile_id = parseInt(this.selectedBusinessId);
                 payload.venue_name = 'Sponsorlu Esnaf Mekanı';
                 payload.address = 'İşletme Adresi';
             } else {
                 payload.venue_name = this.venueName || 'Mahalle Alanı';
                 payload.address = this.address || 'Belirtilen Adres';
             }

             try {
                 const res = await fetch('/api/v1/events', {
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/json',
                         'Accept': 'application/json',
                         'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                     },
                     body: JSON.stringify(payload)
                 });

                 const data = await res.json();
                 if (res.ok && data.success) {
                     $dispatch('toast', 'Etkinlik başarıyla oluşturuldu! 🎉');
                     setTimeout(() => {
                         window.location.href = `/events/${data.event.id}`;
                     }, 600);
                 } else {
                     $dispatch('toast', data.message || 'Etkinlik oluşturulamadı.');
                 }
             } catch (e) {
                 $dispatch('toast', 'Ağ hatası meydana geldi.');
             } finally {
                 this.submitting = false;
             }
         }
     }">

    <!-- Üst Başlık -->
    <div class="pb-4 border-b border-slate-200 dark:border-neutral-800">
        <a href="{{ route('events.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-neutral-400 dark:hover:text-white flex items-center gap-1 mb-2">
            ← Etkinlik Takvimine Dön
        </a>
        <h1 class="text-2xl font-black text-slate-900 dark:text-white">
            📍 Yeni Mahalle Buluşması Başlat
        </h1>
        <p class="text-xs text-slate-500 dark:text-neutral-400 mt-0.5">
            {{ $location->city }} / {{ $location->district }} sakinlerini ortak bir hobi, spor veya sohbet için bir araya getir.
        </p>
    </div>

    <!-- Form Alanı -->
    <form @submit.prevent="submitEvent()" class="space-y-5">

        <!-- 1. Başlık & Kategori -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="sm:col-span-2 space-y-1.5">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                    Buluşma Başlığı <span class="text-rose-500">*</span>
                </label>
                <input x-model="title"
                       type="text"
                       required
                       maxlength="120"
                       placeholder="Örn: Hafta Sonu Botanik Parkı Yürüyüşü & Sohbet"
                       class="w-full text-xs sm:text-sm px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 outline-none">
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                    Kategori <span class="text-rose-500">*</span>
                </label>
                <select x-model="category"
                        class="w-full text-xs sm:text-sm px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 outline-none">
                    <option value="hobi_oyun">☕ Hobi & Kahve Sohbeti</option>
                    <option value="spor">⚽ Spor, Koşu & Doğa</option>
                    <option value="kultur_sanat">🎨 Kültür, Sanat & Kitap</option>
                    <option value="yardimlasma">🤝 Mahalle İçi Dayanışma</option>
                    <option value="belediye_duyuru">🏛️ Resmi / Belediye Etkinliği</option>
                </select>
            </div>
        </div>

        <!-- 2. Açıklama -->
        <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                Açıklama & Detaylar <span class="text-rose-500">*</span>
            </label>
            <textarea x-model="description"
                      rows="3"
                      required
                      placeholder="Buluşmada neler yapılacak? Yanında bir şey getirmesi gerekiyor mu? Kimler katılabilir?"
                      class="w-full text-xs sm:text-sm px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 outline-none"></textarea>
        </div>

        <!-- 3. Tarih, Saat & Kontenjan -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                    Etkinlik Tarihi <span class="text-rose-500">*</span>
                </label>
                <input x-model="eventDate"
                       type="date"
                       min="{{ date('Y-m-d') }}"
                       required
                       class="w-full text-xs sm:text-sm px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 outline-none">
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                    Buluşma Saati <span class="text-rose-500">*</span>
                </label>
                <input x-model="eventTime"
                       type="time"
                       required
                       class="w-full text-xs sm:text-sm px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 outline-none">
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                    Maksimum Katılımcı (Opsiyonel)
                </label>
                <input x-model="maxParticipants"
                       type="number"
                       min="2"
                       max="500"
                       placeholder="Sınırsız için boş bırakın"
                       class="w-full text-xs sm:text-sm px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 outline-none">
            </div>
        </div>

        <!-- 4. Mekan & Esnaf Sponsorluk Seçimi -->
        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-neutral-900 border border-slate-200 dark:border-neutral-800 space-y-4">
            <div>
                <label class="block text-xs font-extrabold text-slate-900 dark:text-white">
                    📍 Buluşma Noktası / Mekan Belirle
                </label>
                <p class="text-[11px] text-slate-500 dark:text-neutral-400 mt-0.5">
                    Yerel esnaf kafelerimizi seçerek etkinliğinizi "Sponsorlu Buluşma Noktası" olarak öne çıkarabilir veya açık bir alan yazabilirsiniz.
                </p>
            </div>

            <!-- Seçim Sekmeleri -->
            <div class="flex items-center gap-2">
                <button type="button"
                        @click="venueType = 'sponsored'"
                        :class="venueType === 'sponsored' ? 'bg-amber-600 text-white font-bold' : 'bg-white dark:bg-neutral-800 text-slate-600 dark:text-neutral-300 border border-slate-200 dark:border-neutral-700'"
                        class="px-3 py-1.5 rounded-xl text-xs transition-colors flex items-center gap-1.5 shadow-2xs">
                    <span>⭐ Onaylı Esnaf Mekanı Seç</span>
                </button>
                <button type="button"
                        @click="venueType = 'custom'"
                        :class="venueType === 'custom' ? 'bg-amber-600 text-white font-bold' : 'bg-white dark:bg-neutral-800 text-slate-600 dark:text-neutral-300 border border-slate-200 dark:border-neutral-700'"
                        class="px-3 py-1.5 rounded-xl text-xs transition-colors flex items-center gap-1.5 shadow-2xs">
                    <span>🌳 Özel / Açık Alan Yaz</span>
                </button>
            </div>

            <!-- Seçenek A: Esnaf Listesi -->
            <div x-show="venueType === 'sponsored'" class="space-y-2">
                @if($sponsoredVenues->isNotEmpty())
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                        Mahallenizdeki Onaylı İşletmeler:
                    </label>
                    <select x-model="selectedBusinessId"
                            class="w-full text-xs sm:text-sm px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 outline-none">
                        @foreach($sponsoredVenues as $v)
                            <option value="{{ $v->id }}">
                                ⭐ {{ $v->business_name }} - {{ $v->address }}
                            </option>
                        @endforeach
                    </select>
                @else
                    <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/30 text-amber-800 dark:text-amber-300 text-xs">
                        Bu ilçede henüz doğrulanmış esnaf mekanı kayıtlı değil. Özel mekan alanını kullanabilirsiniz.
                    </div>
                @endif
            </div>

            <!-- Seçenek B: Özel Mekan -->
            <div x-show="venueType === 'custom'" class="grid grid-cols-1 sm:grid-cols-2 gap-3" x-cloak>
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                        Mekan / Buluşma Yeri Adı:
                    </label>
                    <input x-model="venueName"
                           type="text"
                           placeholder="Örn: Keçiören Şelale Parkı Girişi"
                           class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 outline-none">
                </div>
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                        Açık Adres / Tarif:
                    </label>
                    <input x-model="address"
                           type="text"
                           placeholder="Örn: Kalaba Mah. Sanatoryum Cad. No:12"
                           class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 outline-none">
                </div>
            </div>
        </div>

        <!-- Gönder Butonu -->
        <div class="pt-2 flex items-center justify-end gap-3">
            <a href="{{ route('events.index') }}" class="text-xs text-slate-500 hover:text-slate-700 dark:text-neutral-400">
                Vazgeç
            </a>
            <button type="submit"
                    :disabled="submitting"
                    class="px-6 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-sm shadow-md transition-colors flex items-center gap-2">
                <span x-show="!submitting">Buluşmayı Başlat (+5 İtibar Puanı)</span>
                <span x-show="submitting" class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full" x-cloak></span>
            </button>
        </div>

    </form>

</div>
@endsection

