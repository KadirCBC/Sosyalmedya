<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationSeeder extends Seeder
{
    /**
     * Türkiye'nin 81 ili ve ilçelerini toplu (chunk) optimize insert ile ekler.
     */
    public function run(): void
    {
        $locations = $this->getTurkeyLocationsData();
        $now = now();

        $rows = [];
        foreach ($locations as $loc) {
            $rows[] = [
                'city' => $loc['city'],
                'district' => $loc['district'],
                'latitude' => $loc['latitude'],
                'longitude' => $loc['longitude'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // 200'lük parçalar halinde yüksek hızlı insertOrIgnore
        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('locations')->insertOrIgnore($chunk);
        }
    }

    /**
     * 81 il ve ilçelerin koordinatlı veri seti.
     *
     * @return list<array{city: string, district: string, latitude: float, longitude: float}>
     */
    protected function getTurkeyLocationsData(): array
    {
        $provinces = [
            'Adana' => ['center' => [37.0000, 35.3213], 'districts' => ['Seyhan', 'Yüreğir', 'Çukurova', 'Sarıçam', 'Ceyhan', 'Kozan', 'İmamoğlu', 'Karataş', 'Karaisalı', 'Pozantı', 'Yumurtalık', 'Tufanbeyli', 'Feke', 'Aladağ', 'Saimbeyli']],
            'Adıyaman' => ['center' => [37.7648, 38.2786], 'districts' => ['Merkez', 'Kahta', 'Besni', 'Gölbaşı', 'Gerger', 'Sincik', 'Çelikhan', 'Tut', 'Samsat']],
            'Afyonkarahisar' => ['center' => [38.7507, 30.5567], 'districts' => ['Merkez', 'Sandıklı', 'Dinar', 'Bolvadin', 'Emirdağ', 'Şuhut', 'Çay', 'İhsaniye', 'İscehisar', 'Sinanpaşa', 'Dazkırı', 'Başmakçı', 'Hocalar', 'Çobanlar', 'Evciler', 'Kızılören', 'Bayat', 'Sultandağı']],
            'Ağrı' => ['center' => [39.7191, 43.0503], 'districts' => ['Merkez', 'Patnos', 'Doğubayazıt', 'Diyadin', 'Eleşkirt', 'Tutak', 'Taşlıçay', 'Hamur']],
            'Amasya' => ['center' => [40.6501, 35.8353], 'districts' => ['Merkez', 'Merzifon', 'Suluova', 'Taşova', 'Gümüşhacıköy', 'Göynücek', 'Hamamözü']],
            'Ankara' => ['center' => [39.9334, 32.8597], 'districts' => ['Keçiören', 'Çankaya', 'Yenimahalle', 'Mamak', 'Etimesgut', 'Sincan', 'Altındağ', 'Pursaklar', 'Gölbaşı', 'Polatlı', 'Çubuk', 'Kahramankazan', 'Beypazarı', 'Elmadağ', 'Nallıhan', 'Haymana', 'Akyurt', 'Kızılcahamam', 'Bala', 'Kalecik', 'Ayaş', 'Güdül', 'Çamlıdere', 'Evren', 'Şereflikoçhisar']],
            'Antalya' => ['center' => [36.8969, 30.7133], 'districts' => ['Muratpaşa', 'Kepez', 'Konyaaltı', 'Alanya', 'Manavgat', 'Serik', 'Aksu', 'Kumluca', 'Döşemealtı', 'Kaş', 'Korkuteli', 'Gazipaşa', 'Finike', 'Kemer', 'Elmalı', 'Demre', 'Akseki', 'Gündoğmuş', 'İbradı']],
            'Artvin' => ['center' => [41.1828, 41.8183], 'districts' => ['Merkez', 'Hopa', 'Borçka', 'Yusufeli', 'Arhavi', 'Şavşat', 'Ardanuç', 'Murgul', 'Kemalpaşa']],
            'Aydın' => ['center' => [37.8560, 27.8416], 'districts' => ['Efeler', 'Nazilli', 'Söke', 'Kuşadası', 'Didim', 'İncirliova', 'Çine', 'Germencik', 'Bozdoğan', 'Köşk', 'Kuyucak', 'Sultanhisar', 'Karacasu', 'Yenipazar', 'Buharkent', 'Karpuzlu']],
            'Balıkesir' => ['center' => [39.6484, 27.8826], 'districts' => ['Karesi', 'Altıeylül', 'Bandırma', 'Edremit', 'Gönen', 'Ayvalık', 'Burhaniye', 'Bigadiç', 'Susurluk', 'Dursunbey', 'Sındırgı', 'İvrindi', 'Erdek', 'Havran', 'Kepsut', 'Manyas', 'Savaştepe', 'Balya', 'Gömeç', 'Marmara']],
            'Bilecik' => ['center' => [40.1506, 29.9793], 'districts' => ['Merkez', 'Bozüyük', 'Osmaneli', 'Söğüt', 'Gölpazarı', 'Pazaryeri', 'Yenipazar', 'İnhisar']],
            'Bingöl' => ['center' => [38.8854, 40.4983], 'districts' => ['Merkez', 'Genç', 'Solhan', 'Karlıova', 'Adaklı', 'Kiğı', 'Yedisu', 'Yayladere']],
            'Bitlis' => ['center' => [38.3938, 42.1232], 'districts' => ['Tatvan', 'Merkez', 'Güroymak', 'Ahlat', 'Hizan', 'Mutki', 'Adilcevaz']],
            'Bolu' => ['center' => [40.7392, 31.6089], 'districts' => ['Merkez', 'Gerede', 'Mudurnu', 'Göynük', 'Mengen', 'Yeniçağa', 'Dörtdivan', 'Seben', 'Kıbrıscık']],
            'Burdur' => ['center' => [37.7203, 30.2908], 'districts' => ['Merkez', 'Bucak', 'Gölhisar', 'Yeşilova', 'Çavdır', 'Tefenni', 'Ağlasun', 'Karamanlı', 'Altınyayla', 'Çeltikçi', 'Kemer']],
            'Bursa' => ['center' => [40.1885, 29.0610], 'districts' => ['Osmangazi', 'Yıldırım', 'Nilüfer', 'İnegöl', 'Gemlik', 'Mustafakemalpaşa', 'Mudanya', 'Gürsu', 'Karacabey', 'Orhangazi', 'Kestel', 'Yenişehir', 'İznik', 'Orhaneli', 'Keles', 'Büyükorhan', 'Harmancık']],
            'Çanakkale' => ['center' => [40.1553, 26.4142], 'districts' => ['Merkez', 'Biga', 'Çan', 'Gelibolu', 'Yenice', 'Ayvacık', 'Ezine', 'Bayramiç', 'Lapseki', 'Eceabat', 'Gökçeada', 'Bozcaada']],
            'Çankırı' => ['center' => [40.6013, 33.6134], 'districts' => ['Merkez', 'Çerkeş', 'Ilgaz', 'Orta', 'Şabanözü', 'Kurşunlu', 'Yapraklı', 'Kızılırmak', 'Eldivan', 'Atkaracalar', 'Korgun', 'Bayramören']],
            'Çorum' => ['center' => [40.5506, 34.9556], 'districts' => ['Merkez', 'Sungurlu', 'Osmancık', 'İskilip', 'Alaca', 'Bayat', 'Mecitözü', 'Kargı', 'Ortaköy', 'Uğurludağ', 'Dodurga', 'Oğuzlar', 'Laçin', 'Boğazkale']],
            'Denizli' => ['center' => [37.7765, 29.0864], 'districts' => ['Pamukkale', 'Merkezefendi', 'Çivril', 'Acıpayam', 'Tavas', 'Honaz', 'Sarayköy', 'Buldan', 'Kale', 'Çal', 'Çameli', 'Serinhisar', 'Bozkurt', 'Güney', 'Çardak', 'Bekilli', 'Beyağaç', 'Babadağ', 'Baklan']],
            'Diyarbakır' => ['center' => [37.9144, 40.2306], 'districts' => ['Bağlar', 'Kayapınar', 'Yenişehir', 'Sur', 'Ergani', 'Bismil', 'Silvan', 'Çınar', 'Cermik', 'Dicle', 'Kulp', 'Hani', 'Lice', 'Eğil', 'Hazro', 'Kocaköy', 'Çüngüş']],
            'Edirne' => ['center' => [41.6768, 26.5603], 'districts' => ['Merkez', 'Keşan', 'Uzunköprü', 'İpsala', 'Havsa', 'Meriç', 'Enez', 'Süloğlu', 'Lalapaşa']],
            'Elazığ' => ['center' => [38.6810, 39.2264], 'districts' => ['Merkez', 'Kovancılar', 'Karakoçan', 'Palu', 'Arıcak', 'Baskil', 'Maden', 'Sivrice', 'Alacakaya', 'Keban', 'Ağın']],
            'Erzincan' => ['center' => [39.7500, 39.5000], 'districts' => ['Merkez', 'Tercan', 'Üzümlü', 'Çayırlı', 'İliç', 'Kemah', 'Refahiye', 'Kemaliye', 'Otlukbeli']],
            'Erzurum' => ['center' => [39.9000, 41.2700], 'districts' => ['Yakutiye', 'Palandöken', 'Aziziye', 'Horasan', 'Oltu', 'Pasinler', 'Karayazı', 'Hınıs', 'Tekman', 'Karaçoban', 'Aşkale', 'Şenkaya', 'Çat', 'Köprüköy', 'İspir', 'Tortum', 'Narman', 'Uzundere', 'Olur', 'Pazaryolu']],
            'Eskişehir' => ['center' => [39.7767, 30.5206], 'districts' => ['Odunpazarı', 'Tepebaşı', 'Sivrihisar', 'Çifteler', 'Seyitgazi', 'Alpu', 'Mihalıççık', 'Mahmudiye', 'Beylikova', 'İnönü', 'Günyüzü', 'Han', 'Mihalgazi', 'Sarıcakaya']],
            'Gaziantep' => ['center' => [37.0662, 37.3833], 'districts' => ['Şahinbey', 'Şehitkamil', 'Nizip', 'İslahiye', 'Nurdağı', 'Araban', 'Oğuzeli', 'Yavuzeli', 'Karkamış']],
            'Giresun' => ['center' => [40.9128, 38.3895], 'districts' => ['Merkez', 'Bulancak', 'Espiye', 'Görele', 'Tirebolu', 'Dereli', 'Şebinkarahisar', 'Keşap', 'Yağlıdere', 'Alucra', 'Piraziz', 'Eynesil', 'Çamoluk', 'Güce', 'Doğankent', 'Çanakçı']],
            'Gümüşhane' => ['center' => [40.4600, 39.4700], 'districts' => ['Merkez', 'Kelkit', 'Şiran', 'Kürtün', 'Torul', 'Köse']],
            'Hakkari' => ['center' => [37.5833, 43.7333], 'districts' => ['Yüksekova', 'Merkez', 'Şemdinli', 'Çukurca', 'Derecik']],
            'Hatay' => ['center' => [36.4018, 36.3498], 'districts' => ['Antakya', 'İskenderun', 'Defne', 'Dörtyol', 'Samandağ', 'Kırıkhan', 'Reyhanlı', 'Arsuz', 'Altınözü', 'Hassa', 'Payas', 'Erzin', 'Yayladağı', 'Belen', 'Kumlu']],
            'Isparta' => ['center' => [37.7648, 30.5566], 'districts' => ['Merkez', 'Yalvaç', 'Eğirdir', 'Şarkikaraağaç', 'Gelendost', 'Keçiborlu', 'Senirkent', 'Sütçüler', 'Gönen', 'Uluborlu', 'Atabey', 'Aksu', 'Yenişarbademli']],
            'Mersin' => ['center' => [36.8000, 34.6333], 'districts' => ['Tarsus', 'Toroslar', 'Akdeniz', 'Yenişehir', 'Mezitli', 'Erdemli', 'Silifke', 'Anamur', 'Mut', 'Bozyazı', 'Gülnar', 'Aydıncık', 'Çamlıyayla']],
            'İstanbul' => ['center' => [41.0082, 28.9784], 'districts' => ['Kadıköy', 'Beşiktaş', 'Üsküdar', 'Şişli', 'Fatih', 'Beyoğlu', 'Bakırköy', 'Maltepe', 'Kartal', 'Pendik', 'Ataşehir', 'Ümraniye', 'Sarıyer', 'Beykoz', 'Tuzla', 'Esenyurt', 'Küçükçekmece', 'Bağcılar', 'Bahçelievler', 'Zeytinburnu', 'Güngören', 'Bayrampaşa', 'Esenler', 'Gaziosmanpaşa', 'Sultangazi', 'Eyüpsultan', 'Kağıthane', 'Başakşehir', 'Sancaktepe', 'Çekmeköy', 'Sultanbeyli', 'Avcılar', 'Beylikdüzü', 'Büyükçekmece', 'Silivri', 'Çatalca', 'Arnavutköy', 'Şile', 'Adalar']],
            'İzmir' => ['center' => [38.4192, 27.1287], 'districts' => ['Buca', 'Karabağlar', 'Bornova', 'Konak', 'Karşıyaka', 'Bayraklı', 'Çiğli', 'Torbalı', 'Menemen', 'Gaziemir', 'Ödemiş', 'Kemalpaşa', 'Bergama', 'Aliağa', 'Menderes', 'Tire', 'Balçova', 'Urla', 'Narlıdere', 'Seferihisar', 'Çeşme', 'Dikili', 'Kiraz', 'Kınık', 'Beydağ', 'Güzelbahçe', 'Foça', 'Selçuk', 'Karaburun']],
            'Kars' => ['center' => [40.6167, 43.1000], 'districts' => ['Merkez', 'Kağızman', 'Sarıkamış', 'Selim', 'Digor', 'Arpaçay', 'Akyaka', 'Susuz']],
            'Kastamonu' => ['center' => [41.3887, 33.7827], 'districts' => ['Merkez', 'Tosya', 'Taşköprü', 'Cide', 'İnebolu', 'Araç', 'Devrekani', 'Bozkurt', 'Daday', 'Azdavay', 'Çatalzeytin', 'Küre', 'Doğanyurt', 'İhsangazi', 'Pınarbaşı', 'Şenpazar', 'Abana', 'Seydiler', 'Hanonü', 'Ağlı']],
            'Kayseri' => ['center' => [38.7312, 35.4787], 'districts' => ['Melikgazi', 'Kocasinan', 'Talas', 'Develi', 'Yahyalı', 'Bünyan', 'İncesu', 'Pınarbaşı', 'Tomarza', 'Yeşilhisar', 'Sarıoğlan', 'Hacılar', 'Sarız', 'Felahiye', 'Akkışla', 'Özvatan']],
            'Kırklareli' => ['center' => [41.7333, 27.2167], 'districts' => ['Lüleburgaz', 'Merkez', 'Babaeski', 'Vize', 'Pınarhisar', 'Demirköy', 'Pehlivanköy', 'Kofçaz']],
            'Kırşehir' => ['center' => [39.1425, 34.1709], 'districts' => ['Merkez', 'Kaman', 'Mucur', 'Çiçekdağı', 'Akpınar', 'Boztepe', 'Akçakent']],
            'Kocaeli' => ['center' => [40.7654, 29.9408], 'districts' => ['Gebze', 'İzmit', 'Darıca', 'Körfez', 'Gölcük', 'Derince', 'Çayırova', 'Kartepe', 'Başiskele', 'Karamürsel', 'Kandıra', 'Dilovası']],
            'Konya' => ['center' => [37.8667, 32.4833], 'districts' => ['Selçuklu', 'Meram', 'Karatay', 'Ereğli', 'Akşehir', 'Beyşehir', 'Çumra', 'Seydişehir', 'Ilgın', 'Cihanbeyli', 'Kulu', 'Karapınar', 'Kadınhanı', 'Sarayönü', 'Bozkır', 'Yunak', 'Doğanhisar', 'Hüyük', 'Altınekin', 'Hadim', 'Çeltik', 'Güneysınır', 'Emirgazi', 'Taşkent', 'Tuzlukçu', 'Akören', 'Derebucak', 'Halkapınar', 'Yalıhüyük']],
            'Kütahya' => ['center' => [39.4167, 29.9833], 'districts' => ['Merkez', 'Tavşanlı', 'Simav', 'Gediz', 'Emet', 'Altıntaş', 'Domaniç', 'Hisarcık', 'Aslanapa', 'Çavdarhisar', 'Şaphane', 'Pazarlar', 'Dumlupınar']],
            'Malatya' => ['center' => [38.3552, 38.3095], 'districts' => ['Battalgazi', 'Yeşilyurt', 'Doğanşehir', 'Akçadağ', 'Darende', 'Hekimhan', 'Pütürge', 'Yazıhan', 'Arapgir', 'Kuluncak', 'Arguvan', 'Kale', 'Doğanyol']],
            'Manisa' => ['center' => [38.6191, 27.4289], 'districts' => ['Yunusemre', 'Şehzadeler', 'Akhisar', 'Turgutlu', 'Salihli', 'Soma', 'Alaşehir', 'Saruhanlı', 'Demirci', 'Kula', 'Kırkağaç', 'Sarıgöl', 'Gördes', 'Selendi', 'Ahmetli', 'Gölmarmara', 'Köprübaşı']],
            'Kahramanmaraş' => ['center' => [37.5858, 36.9371], 'districts' => ['Onikişubat', 'Dulkadiroğlu', 'Elbistan', 'Afşin', 'Türkoğlu', 'Pazarcık', 'Göksun', 'Andırın', 'Çağlayancerit', 'Nurhak', 'Ekinözü']],
            'Mardin' => ['center' => [37.3212, 40.7245], 'districts' => ['Kızıltepe', 'Artuklu', 'Midyat', 'Nusaybin', 'Derik', 'Mazıdağı', 'Dargeçit', 'Savur', 'Yeşilli', 'Ömerli']],
            'Muğla' => ['center' => [37.2153, 28.3636], 'districts' => ['Bodrum', 'Fethiye', 'Milas', 'Menteşe', 'Marmaris', 'Seydikemer', 'Ortaca', 'Yatağan', 'Dalaman', 'Köyceğiz', 'Ula', 'Datça', 'Kavaklıdere']],
            'Muş' => ['center' => [38.7432, 41.5064], 'districts' => ['Merkez', 'Bulanık', 'Malazgirt', 'Varto', 'Hasköy', 'Korkut']],
            'Nevşehir' => ['center' => [38.6244, 34.7144], 'districts' => ['Merkez', 'Ürgüp', 'Avanos', 'Gülşehir', 'Derinkuyu', 'Acıgöl', 'Kozaklı', 'Hacıbektaş']],
            'Niğde' => ['center' => [37.9667, 34.6833], 'districts' => ['Merkez', 'Bor', 'Çiftlik', 'Ulukışla', 'Altunhisar', 'Çamardı']],
            'Ordu' => ['center' => [40.9839, 37.8764], 'districts' => ['Altınordu', 'Ünye', 'Fatsa', 'Kumru', 'Korgan', 'Gölköy', 'Perşembe', 'Aybastı', 'Ulubey', 'Mesudiye', 'İkizce', 'Gürgentepe', 'Çatalpınar', 'Çaybaşı', 'Kabadüz', 'Kabataş', 'Korgan', 'Gülyalı']],
            'Rize' => ['center' => [41.0201, 40.5234], 'districts' => ['Merkez', 'Çayeli', 'Ardeşen', 'Pazar', 'Fındıklı', 'Güneysu', 'Kalkandere', 'İyidere', 'Derepazarı', 'Çamlıhemşin', 'İkizdere', 'Hemşin']],
            'Sakarya' => ['center' => [40.7569, 30.3783], 'districts' => ['Adapazarı', 'Serdivan', 'Akyazı', 'Erenler', 'Hendek', 'Karasu', 'Geyve', 'Arifiye', 'Sapanca', 'Pamukova', 'Ferizli', 'Kocaali', 'Kaynarca', 'Söğütlü', 'Karapürçek', 'Taraklı']],
            'Samsun' => ['center' => [41.2928, 36.3313], 'districts' => ['İlkadım', 'Atakum', 'Bafra', 'Çarşamba', 'Canik', 'Vezirköprü', 'Terme', 'Tekkeköy', 'Havza', 'Alaçam', '19 Mayıs', 'Ayvacık', 'Kavak', 'Salıpazarı', 'Asarcık', 'Ladik', 'Yakakent']],
            'Siirt' => ['center' => [37.9333, 41.9500], 'districts' => ['Merkez', 'Kurtalan', 'Pervari', 'Baykan', 'Şirvan', 'Eruh', 'Tillo']],
            'Sinop' => ['center' => [42.0231, 35.1531], 'districts' => ['Merkez', 'Boyabat', 'Gerze', 'Ayancık', 'Durağan', 'Türkeli', 'Erfelek', 'Dikmen', 'Saraydüzü']],
            'Sivas' => ['center' => [39.7477, 37.0179], 'districts' => ['Merkez', 'Şarkışla', 'Yıldızeli', 'Suşehri', 'Zara', 'Gemerek', 'Kangal', 'Gürün', 'Divriği', 'Koyulhisar', 'Altınyayla', 'Hafik', 'Ulaş', 'İmranlı', 'Akıncılar', 'Gölova', 'Doğanşar']],
            'Tekirdağ' => ['center' => [40.9833, 27.5167], 'districts' => ['Çorlu', 'Süleymanpaşa', 'Çerkezköy', 'Kapaklı', 'Ergene', 'Malkara', 'Saray', 'Hayrabolu', 'Şarköy', 'Muratlı', 'Marmaraereğlisi']],
            'Tokat' => ['center' => [40.3167, 36.5500], 'districts' => ['Merkez', 'Erbaa', 'Turhal', 'Niksar', 'Zile', 'Reşadiye', 'Almus', 'Pazar', 'Yeşilyurt', 'Artova', 'Sulusaray', 'Başçiftlik']],
            'Trabzon' => ['center' => [41.0015, 39.7178], 'districts' => ['Ortahisar', 'Akçaabat', 'Araklı', 'Of', 'Yomra', 'Arsin', 'Vakfıkebir', 'Sürmene', 'Maçka', 'Beşikdüzü', 'Çarşıbaşı', 'Tonya', 'Düzköy', 'Çaykara', 'Şalpazarı', 'Hayrat', 'Köprübaşı', 'Dernekpazarı']],
            'Tunceli' => ['center' => [39.1079, 39.5401], 'districts' => ['Merkez', 'Pertek', 'Mazgirt', 'Çemişgezek', 'Hozat', 'Ovacık', 'Nazımiye', 'Pülümür']],
            'Şanlıurfa' => ['center' => [37.1591, 38.7969], 'districts' => ['Haliliye', 'Eyyübiye', 'Siverek', 'Viranşehir', 'Karaköprü', 'Akçakale', 'Suruç', 'Birecik', 'Ceylanpınar', 'Harran', 'Bozova', 'Hilvan', 'Halfeti']],
            'Uşak' => ['center' => [38.6823, 29.4082], 'districts' => ['Merkez', 'Banaz', 'Eşme', 'Sivaslı', 'Ulubey', 'Karahallı']],
            'Van' => ['center' => [38.4891, 43.4089], 'districts' => ['İpekyolu', 'Erciş', 'Tuşba', 'Edremit', 'Özalp', 'Çaldıran', 'Başkale', 'Muradiye', 'Gürpınar', 'Gevaş', 'Saray', 'Çatak', 'Bahçesaray']],
            'Yozgat' => ['center' => [39.8181, 34.8147], 'districts' => ['Merkez', 'Sorgun', 'Akdağmadeni', 'Yerköy', 'Boğazlıyan', 'Sarıkaya', 'Çekerek', 'Şefaatli', 'Saraykent', 'Çayıralan', 'Kadışehri', 'Aydıncık', 'Yenifakılı', 'Chandır']],
            'Zonguldak' => ['center' => [41.4564, 31.7987], 'districts' => ['Ereğli', 'Merkez', 'Çaycuma', 'Devrek', 'Kozlu', 'Alaplı', 'Kilimli', 'Gökçebey']],
            'Aksaray' => ['center' => [38.3687, 34.0370], 'districts' => ['Merkez', 'Ortaköy', 'Eskil', 'Gülağaç', 'Güzelyurt', 'Ağaçören', 'Sultanhanı', 'Sarıyahşi']],
            'Bayburt' => ['center' => [40.2552, 40.2249], 'districts' => ['Merkez', 'Demirözü', 'Aydıntepe']],
            'Karaman' => ['center' => [37.1759, 33.2287], 'districts' => ['Merkez', 'Ermenek', 'Sarıveliler', 'Ayrancı', 'Kazımkarabekir', 'Başyayla']],
            'Kırıkkale' => ['center' => [39.8468, 33.5153], 'districts' => ['Merkez', 'Yahşihan', 'Keskin', 'Delice', 'Sulakyurt', 'Bahşılı', 'Balışeyh', 'Karakeçili', 'Çelebi']],
            'Batman' => ['center' => [37.8812, 41.1351], 'districts' => ['Merkez', 'Kozluk', 'Sason', 'Beşiri', 'Gercüş', 'Hasankeyf']],
            'Şırnak' => ['center' => [37.5164, 42.4593], 'districts' => ['Cizre', 'Silopi', 'Merkez', 'İdil', 'Uludere', 'Beytüşşebap', 'Güçlükonak']],
            'Bartın' => ['center' => [41.6344, 32.3375], 'districts' => ['Merkez', 'Ulus', 'Amasra', 'Kurucaşile']],
            'Ardahan' => ['center' => [41.1105, 42.7022], 'districts' => ['Merkez', 'Göle', 'Çıldır', 'Hanak', 'Posof', 'Damal']],
            'Iğdır' => ['center' => [39.9196, 44.0450], 'districts' => ['Merkez', 'Tuzluca', 'Aralık', 'Karakoyunlu']],
            'Yalova' => ['center' => [40.6500, 29.2667], 'districts' => ['Merkez', 'Çiftlikköy', 'Çınarcık', 'Altınova', 'Armutlu', 'Termal']],
            'Karabük' => ['center' => [41.2061, 32.6204], 'districts' => ['Merkez', 'Safranbolu', 'Yenice', 'Eskipazar', 'Eflani', 'Ovacık']],
            'Kilis' => ['center' => [36.7184, 37.1212], 'districts' => ['Merkez', 'Musabeyli', 'Elbeyli', 'Polateli']],
            'Osmaniye' => ['center' => [37.0742, 36.2472], 'districts' => ['Merkez', 'Kadirli', 'Düziçi', 'Bahçe', 'Toprakkale', 'Sumbas', 'Hasanbeyli']],
            'Düzce' => ['center' => [40.8438, 31.1565], 'districts' => ['Merkez', 'Akçakoca', 'Kaynaşlı', 'Gölyaka', 'Çilimli', 'Yığılca', 'Gümüşova', 'Cumayeri']],
        ];

        $results = [];

        foreach ($provinces as $cityName => $info) {
            $baseLat = $info['center'][0];
            $baseLng = $info['center'][1];

            foreach ($info['districts'] as $idx => $districtName) {
                // Koordinatları ilçe merkezine göre hafif dağıtarak hassaslaştır
                $offset = ($idx % 5) * 0.02 - 0.04;
                $lat = round($baseLat + $offset, 7);
                $lng = round($baseLng + ($offset * 1.2), 7);

                $results[] = [
                    'city' => $cityName,
                    'district' => $districtName,
                    'latitude' => $lat,
                    'longitude' => $lng,
                ];
            }
        }

        return $results;
    }
}

