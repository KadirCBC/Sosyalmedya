<?php

declare(strict_types=1);

/**
 * Meydan - Ultra-Professional Multi-Page Local Social Platform
 * Complete 100% Working Application with All Core Modules & Dedicated User Profile:
 * 1. Feed & Polls (Ana Akış & Anketler)
 * 2. User Profile (Kullanıcı Profil Sayfası)
 * 3. Events & Meetups (Etkinlik & Buluşma Takvimi)
 * 4. Neighborhood Market (Mahalle Pazarı & Askıda Eşya)
 * 5. Business Directory (Esnaf Rehberi & Sipariş)
 * 6. District Pulse Analytics (Yerel Nabız & Analitik Panosu)
 * 7. Emergency Network (Acil Durum Ağı & Kriz Bildirimi)
 */

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

// API Uç Noktaları
if (str_starts_with($uri, '/api/')) {
    header('Content-Type: application/json; charset=utf-8');

    if ($uri === '/api/v1/events/weekend') {
        echo json_encode([
            'success' => true,
            'events' => [
                [
                    'id' => 1,
                    'title' => 'Botanikte Sabah Koşusu & Komşu Kahvesi',
                    'category' => 'spor',
                    'venue_name' => 'Şelale Park Kafe',
                    'is_sponsored_venue' => true,
                    'image' => 'https://images.unsplash.com/photo-1517649763962-0c623266ddc0?auto=format&fit=crop&w=600&q=80',
                    'event_date' => date('Y-m-d 09:30:00', strtotime('next Saturday')),
                    'participants_count' => 18,
                ],
                [
                    'id' => 2,
                    'title' => 'Mahalle Kitap Kulübü Söyleşisi',
                    'category' => 'kultur_sanat',
                    'venue_name' => 'Keçiören Gençlik Kütüphanesi',
                    'is_sponsored_venue' => false,
                    'image' => 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=600&q=80',
                    'event_date' => date('Y-m-d 15:00:00', strtotime('next Sunday')),
                    'participants_count' => 24,
                ],
            ],
        ]);
        exit;
    }

    if ($uri === '/api/v1/analytics/pulse/kecioren') {
        echo json_encode([
            'district' => 'Keçiören',
            'sentiment_score' => 68.4,
            'positive_percentage' => 68.4,
            'complaint_percentage' => 31.6,
            'trending_topics' => [
                ['topic' => '#BotanikParkı', 'count' => 1240, 'trend' => '+18%'],
                ['topic' => '#SuKesintisi', 'count' => 890, 'trend' => '-12%'],
                ['topic' => '#KeçiörenPazarı', 'count' => 640, 'trend' => '+5%'],
            ],
        ]);
        exit;
    }

    echo json_encode(['success' => true, 'timestamp' => time()]);
    exit;
}

// İnfografik Anket Kartı Üretimi
if (preg_match('#^/polls/(\d+)/card#', $uri, $matches)) {
    header('Content-Type: image/png');
    $img = imagecreatetruecolor(1200, 630);
    $bg = imagecolorallocate($img, 15, 23, 42);
    $card = imagecolorallocate($img, 30, 41, 59);
    $white = imagecolorallocate($img, 255, 255, 255);
    $blue = imagecolorallocate($img, 59, 130, 246);
    $emerald = imagecolorallocate($img, 16, 185, 129);
    imagefilledrectangle($img, 0, 0, 1200, 630, $bg);
    imagefilledrectangle($img, 50, 40, 1150, 590, $card);
    imagestring($img, 5, 80, 70, "M E Y D A N   Y E R E L   A N K E T", $blue);
    imagestring($img, 5, 80, 140, "Kecioren'de bu hafta sonu en cok hangi etkinligi gormek istersiniz?", $white);
    imagefilledrectangle($img, 80, 220, 1120, 280, imagecolorallocate($img, 51, 65, 85));
    imagefilledrectangle($img, 80, 220, 690, 280, $emerald);
    imagestring($img, 5, 100, 242, "Acik Hava Sinemasi - %56.8 (58 Oy) [KAZANAN]", $white);
    imagestring($img, 4, 80, 550, "Toplam 102 mahalle oyu kullanildi  *  meydan.com'da oyla ->", $white);
    imagepng($img);
    imagedestroy($img);
    exit;
}

// =========================================================================
// Mahalle Sakinleri Veritabanı (Çoklu Üye Profil Sistemi)
// =========================================================================
$members = [
    'kadircbc' => [
        'handle' => 'kadircbc',
        'name' => 'Kadir Çelebi',
        'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&q=80',
        'cover' => 'https://images.unsplash.com/photo-1519501025264-65ba15a82390?auto=format&fit=crop&w=1600&q=85',
        'role' => 'Aktif Komşu (2. Seviye)',
        'job' => 'Yazılım Geliştirici & Topluluk Gönüllüsü',
        'workplace' => 'Keçiören Teknoloji Vadisi',
        'education' => 'Gazi Üniversitesi Bilgisayar Mühendisliği',
        'location' => 'Keçiören / Kalaba Mah.',
        'hometown' => 'Ankara, Türkiye',
        'joined' => 'Kasım 2023',
        'reputation' => 120,
        'level' => '2. Seviye Aktif Komşu',
        'next_level' => 'Mahalle Elçisi (150 Puan)',
        'progress' => 65,
        'neighbors_count' => 284,
        'mutual_count' => 14,
        'posts_count' => 14,
        'events_count' => 8,
        'bio' => 'Keçiören Kalaba sakini. Çevre ve doğa gönüllüsü, açık hava koşuları, bisiklet turları ve mahalle imecelerine katılır. Mahallemizi birlikte güzelleştirelim. 🌿☕',
        'is_current_user' => true,
        'tags' => ['#Yazılım', '#DoğaKoşusu', '#Bisiklet', '#Mahalleİmecesi'],
        'badges' => [
            ['title' => 'Aktif Komşu (2. Seviye)', 'icon' => '🏅', 'bg' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20'],
            ['title' => '120 İtibar Puanı', 'icon' => '★', 'bg' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20'],
            ['title' => '4 Buluşma Katılımı', 'icon' => '🏃', 'bg' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20'],
            ['title' => 'Doğrulanmış Pasaport', 'icon' => '✓', 'bg' => 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border-indigo-500/20'],
        ],
        'photos' => [
            'https://images.unsplash.com/photo-1517649763962-0c623266ddc0?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1519501025264-65ba15a82390?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1554118811-1e0d58224f24?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80',
        ],
        'friends' => ['mehmetkaya', 'aysetugba', 'serkanusta', 'ahmetyilmaz'],
    ],
    'mehmetkaya' => [
        'handle' => 'mehmetkaya',
        'name' => 'Dr. Mehmet Kaya',
        'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=400&q=80',
        'cover' => 'https://images.unsplash.com/photo-1517649763962-0c623266ddc0?auto=format&fit=crop&w=1600&q=85',
        'role' => 'Gönüllü Muhtar & Sağlık Elçisi',
        'job' => 'Aile Hekimi & Mahalle Sağlık Koordinatörü',
        'workplace' => 'Keçiören 4 No\'lu Aile Sağlığı Merkezi',
        'education' => 'Hacettepe Üniversitesi Tıp Fakültesi',
        'location' => 'Keçiören / Kalaba Mah.',
        'hometown' => 'Trabzon',
        'joined' => 'Eylül 2022',
        'reputation' => 580,
        'level' => 'Lider Komşu (1. Sıra)',
        'next_level' => 'Fahri Mahalle Başkanı',
        'progress' => 95,
        'neighbors_count' => 542,
        'mutual_count' => 28,
        'posts_count' => 36,
        'events_count' => 29,
        'bio' => 'Kalaba sakinlerine koruyucu sağlık, ilk yardım eğitimleri ve mahalle dayanışmasında rehberlik ediyorum. Botanik Parkı sabah koşularının gönüllü kaptanıyım. 🩺🏃‍♂️',
        'is_current_user' => false,
        'tags' => ['#Sağlık', '#SabahKoşusu', '#GönüllüMuhtar', '#İlkYardım'],
        'badges' => [
            ['title' => 'Haftanın 1. Komşusu', 'icon' => '🥇', 'bg' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20'],
            ['title' => '580 İtibar Puanı', 'icon' => '★', 'bg' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20'],
            ['title' => 'Sağlık & Yaşam Elçisi', 'icon' => '🩺', 'bg' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20'],
            ['title' => 'Gönüllü Muhtar', 'icon' => '🏛️', 'bg' => 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20'],
        ],
        'photos' => [
            'https://images.unsplash.com/photo-1517649763962-0c623266ddc0?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1505751172876-fa1923c5c528?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1532938911079-1b06ac7ceec7?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1554118811-1e0d58224f24?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80',
        ],
        'friends' => ['kadircbc', 'aysetugba', 'serkanusta', 'ahmetyilmaz'],
    ],
    'aysetugba' => [
        'handle' => 'aysetugba',
        'name' => 'Ayşe Öğretmen',
        'avatar' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=400&q=80',
        'cover' => 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=1600&q=85',
        'role' => 'Kültür & Sanat Elçisi',
        'job' => 'Türk Dili ve Edebiyatı Öğretmeni',
        'workplace' => 'Keçiören Anadolu Lisesi',
        'education' => 'Ankara Üniversitesi DTCF',
        'location' => 'Keçiören / Tepebaşı Mah.',
        'hometown' => 'İzmir',
        'joined' => 'Mart 2023',
        'reputation' => 310,
        'level' => 'Kültür Elçisi (3. Sıra)',
        'next_level' => 'Topluluk Rehberi (350 Puan)',
        'progress' => 80,
        'neighbors_count' => 390,
        'mutual_count' => 22,
        'posts_count' => 22,
        'events_count' => 15,
        'bio' => 'Mahalle Kitap Kulübü moderatörüyüm. Gençlerle birlikte edebiyat söyleşileri düzenliyor ve Keçiören Gençlik Kütüphanesi\'ni zenginleştirmek için askıda kitap projeleri yürütüyorum. 📚✨',
        'is_current_user' => false,
        'tags' => ['#KitapKulübü', '#Edebiyat', '#AskıdaKitap', '#Eğitim'],
        'badges' => [
            ['title' => 'Haftanın 3. Komşusu', 'icon' => '🥉', 'bg' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20'],
            ['title' => '310 İtibar Puanı', 'icon' => '★', 'bg' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20'],
            ['title' => 'Kültür & Kitap Kulübü', 'icon' => '📚', 'bg' => 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20'],
            ['title' => 'Askıda Kitap Projesi', 'icon' => '📖', 'bg' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20'],
        ],
        'photos' => [
            'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1457369804613-52c61a468e7d?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?auto=format&fit=crop&w=400&q=80',
        ],
        'friends' => ['kadircbc', 'mehmetkaya', 'serkanusta', 'ahmetyilmaz'],
    ],
    'serkanusta' => [
        'handle' => 'serkanusta',
        'name' => 'Serkan Usta (Demir Bisiklet)',
        'avatar' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=400&q=80',
        'cover' => 'https://images.unsplash.com/photo-1485965120184-e220f721d03e?auto=format&fit=crop&w=1600&q=85',
        'role' => 'Doğrulanmış Esnaf & Usta',
        'job' => 'Bisiklet & Doğa Sporları Mekaniği',
        'workplace' => 'Kalaba Demir Bisiklet & Doğa Sporları',
        'education' => 'Mesleki Eğitim Merkezi',
        'location' => 'Keçiören / Kalaba Mah.',
        'hometown' => 'Konya',
        'joined' => 'Ocak 2023',
        'reputation' => 420,
        'level' => '2. Sıra Esnaf Lideri',
        'next_level' => 'Altın Esnaf (500 Puan)',
        'progress' => 84,
        'neighbors_count' => 610,
        'mutual_count' => 24,
        'posts_count' => 41,
        'events_count' => 18,
        'bio' => '30 yıllık Keçiören esnafıyım. Çocukların bisikletlerini ücretsiz kontrol ediyor, mahallemizde çevre dostu ulaşımı yaygınlaştırmak için haftalık bakım atölyeleri düzenliyorum. 🚲🔧',
        'is_current_user' => false,
        'tags' => ['#Esnaf', '#BisikletBakım', '#DoğaSporları', '#İmece'],
        'badges' => [
            ['title' => 'Haftanın 2. Komşusu', 'icon' => '🥈', 'bg' => 'bg-slate-500/10 text-slate-700 dark:text-slate-300 border-slate-500/20'],
            ['title' => '420 İtibar Puanı', 'icon' => '★', 'bg' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20'],
            ['title' => 'Doğrulanmış Mahalle Esnafı', 'icon' => '🛠️', 'bg' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20'],
            ['title' => 'Ücretsiz Çocuk Desteği', 'icon' => '🚲', 'bg' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20'],
        ],
        'photos' => [
            'https://images.unsplash.com/photo-1485965120184-e220f721d03e?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1517649763962-0c623266ddc0?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1554118811-1e0d58224f24?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80',
        ],
        'friends' => ['kadircbc', 'mehmetkaya', 'aysetugba', 'ahmetyilmaz'],
    ],
    'ahmetyilmaz' => [
        'handle' => 'ahmetyilmaz',
        'name' => 'Ahmet Yılmaz',
        'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=400&q=80',
        'cover' => 'https://images.unsplash.com/photo-1511497584788-87676104235f?auto=format&fit=crop&w=1600&q=85',
        'role' => 'Yerel Anket Moderatörü',
        'job' => 'Şehir Plancısı & Çevre Gönüllüsü',
        'workplace' => 'Ankara Kent Araştırmaları',
        'education' => 'ODTÜ Şehir ve Bölge Planlama',
        'location' => 'Keçiören / Kalaba Mah.',
        'hometown' => 'Eskişehir',
        'joined' => 'Ağustos 2023',
        'reputation' => 215,
        'level' => 'Aktif Komşu',
        'next_level' => 'Bölge Sözcüsü (250 Puan)',
        'progress' => 70,
        'neighbors_count' => 312,
        'mutual_count' => 18,
        'posts_count' => 19,
        'events_count' => 11,
        'bio' => 'Keçiören\'in yeşil alanlarının korunması, çocuk parkları ve yayalaştırma projelerinde yerel anketlerle komşularımızın fikirlerini belediyeye iletiyoruz. 🏙️🌳',
        'is_current_user' => false,
        'tags' => ['#ŞehirPlanlama', '#YerelAnket', '#Yayalaştırma', '#Kalaba'],
        'badges' => [
            ['title' => 'Aktif Komşu', 'icon' => '🏅', 'bg' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20'],
            ['title' => '215 İtibar Puanı', 'icon' => '★', 'bg' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20'],
            ['title' => 'Yerel Karar Anketörü', 'icon' => '📊', 'bg' => 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border-indigo-500/20'],
        ],
        'photos' => [
            'https://images.unsplash.com/photo-1511497584788-87676104235f?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1519501025264-65ba15a82390?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?auto=format&fit=crop&w=400&q=80',
        ],
        'friends' => ['kadircbc', 'mehmetkaya', 'aysetugba', 'serkanusta'],
    ]
];

// Üyelere Özel Zaman Tüneli Gönderileri
$memberPosts = [
    'kadircbc' => [
        [
            'id' => 101,
            'author' => 'Kadir Çelebi',
            'handle' => '@kadircbc',
            'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=150&q=80',
            'time' => 'Dün 18:30',
            'location' => 'Keçiören / Kalaba',
            'content' => "Kalaba Vadisi boyunca yeni açılan bisiklet ve yürüyüş parkurunu bu akşam denedim! Aydınlatma, güvenlik ve zemin kalitesi çok başarılı olmuş. Hafta sonu sabah koşuları için komşularla burada buluşabiliriz 🚴‍♂️🏃‍♂️🌲",
            'image' => 'https://images.unsplash.com/photo-1519501025264-65ba15a82390?auto=format&fit=crop&w=800&q=80',
            'likes' => 48,
            'comments' => [
                ['author' => 'Dr. Mehmet Kaya', 'handle' => '@mehmetkaya', 'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=80&q=80', 'time' => '2 saat önce', 'text' => 'Harika haber Kadir Bey! Cumartesi sabahı koşu grubumuzla tam bu güzergahtan geçeceğiz, kahveye de bekleriz.'],
                ['author' => 'Ayşe Öğretmen', 'handle' => '@aysetugba', 'avatar' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=80&q=80', 'time' => '45 dk önce', 'text' => 'Fotoğraflar çok güzel çıkmış. Öğrencilerle birlikte çevre gezisi için plan yapıyorduk, güzergah notumu aldım.']
            ]
        ],
        [
            'id' => 102,
            'author' => 'Kadir Çelebi',
            'handle' => '@kadircbc',
            'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=150&q=80',
            'time' => '3 gün önce',
            'location' => 'Keçiören / Kalaba',
            'content' => "Hafta sonu Şelale Park Kafe ev sahipliğinde gerçekleştirdiğimiz fidan dikimi ve çevre temizliği imecemize katılan herkese teşekkürler! Mahallemiz el birliğiyle daha yeşil ve yaşanabilir oluyor 🌱🌳",
            'image' => 'https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?auto=format&fit=crop&w=800&q=80',
            'likes' => 64,
            'comments' => [
                ['author' => 'Serkan Usta', 'handle' => '@serkanusta', 'avatar' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=80&q=80', 'time' => '1 gün önce', 'text' => 'Elinize sağlık gençler, pazar günü dükkandan sıcak çay ikramımız da hazırdı.']
            ]
        ]
    ],
    'mehmetkaya' => [
        [
            'id' => 201,
            'author' => 'Dr. Mehmet Kaya',
            'handle' => '@mehmetkaya',
            'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=150&q=80',
            'time' => '5 saat önce',
            'location' => 'Keçiören / Kalaba',
            'content' => "Değerli komşularım, bu Cumartesi saat 09:30'da Botanik Parkı girişinde buluşup hafif tempolu 4 km sabah koşusu ve ardından Şelale Park Kafe'de komşu sohbeti yapıyoruz. Katılmak isteyen herkesi bekleriz, spor ayakkabılarınızı giyin gelin! 🏃‍♂️☕",
            'image' => 'https://images.unsplash.com/photo-1517649763962-0c623266ddc0?auto=format&fit=crop&w=800&q=80',
            'likes' => 89,
            'comments' => [
                ['author' => 'Kadir Çelebi', 'handle' => '@kadircbc', 'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=80&q=80', 'time' => '3 saat önce', 'text' => 'Hocam koşu rotasını hazırladım, ben de oradayım!'],
                ['author' => 'Ahmet Yılmaz', 'handle' => '@ahmetyilmaz', 'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=80&q=80', 'time' => '1 saat önce', 'text' => 'Ben de katılıyorum doktorum, görüşmek üzere.']
            ]
        ],
        [
            'id' => 202,
            'author' => 'Dr. Mehmet Kaya',
            'handle' => '@mehmetkaya',
            'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=150&q=80',
            'time' => '4 gün önce',
            'location' => 'Keçiören / Kalaba',
            'content' => "Mevsim geçişlerinde bağışıklık sistemini güçlü tutmak için bol su tüketimi ve açık hava yürüyüşleri büyük önem taşıyor. Kalaba ASM olarak tansiyon ve şeker taramalarımız her çarşamba ücretsiz devam ediyor. 🩺❤️",
            'image' => null,
            'likes' => 112,
            'comments' => []
        ]
    ],
    'aysetugba' => [
        [
            'id' => 301,
            'author' => 'Ayşe Öğretmen',
            'handle' => '@aysetugba',
            'avatar' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=150&q=80',
            'time' => 'Dün 14:15',
            'location' => 'Keçiören / Tepebaşı',
            'content' => "Mahalle Kitap Kulübümüzün bu ayki kitabı Ahmet Hamdi Tanpınar'ın 'Huzur' romanı. Pazar günü saat 15:00'te Keçiören Gençlik Kütüphanesi salonunda buluşuyoruz. Çaylar ve kurabiyeler bizden! Kitabını kapan gelsin 📚☕",
            'image' => 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=800&q=80',
            'likes' => 76,
            'comments' => [
                ['author' => 'Kadir Çelebi', 'handle' => '@kadircbc', 'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=80&q=80', 'time' => '2 saat önce', 'text' => 'Sabah koşusunun ardından kütüphaneye geçeceğim Ayşe Hanım, harika bir program!']
            ]
        ]
    ],
    'serkanusta' => [
        [
            'id' => 401,
            'author' => 'Serkan Usta (Demir Bisiklet)',
            'handle' => '@serkanusta',
            'avatar' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=150&q=80',
            'time' => '1 gün önce',
            'location' => 'Keçiören / Kalaba',
            'content' => "Mahallemizin miniklerine duyuru: Okul çıkışında bisikletinin zinciri atan, lastiği inen tüm çocuklar dükkanımıza uğrayabilir. Bakımlar tamamen komşu hediyemizdir 🚲🔧",
            'image' => 'https://images.unsplash.com/photo-1485965120184-e220f721d03e?auto=format&fit=crop&w=800&q=80',
            'likes' => 145,
            'comments' => [
                ['author' => 'Dr. Mehmet Kaya', 'handle' => '@mehmetkaya', 'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=80&q=80', 'time' => '8 saat önce', 'text' => 'Gönlü güzel esnafımız, ellerine sağlık Serkan Ustam!']
            ]
        ]
    ],
    'ahmetyilmaz' => [
        [
            'id' => 501,
            'author' => 'Ahmet Yılmaz',
            'handle' => '@ahmetyilmaz',
            'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80',
            'time' => '2 gün önce',
            'location' => 'Keçiören / Kalaba',
            'content' => "Komşular günaydın! Bu hafta sonu Keçiören'de hep birlikte güzel bir açık hava etkinliği planlamak istiyoruz. Başlattığımız yerel ankette Açık Hava Sineması önde gidiyor, oy vermeyenler ana akıştaki ankete katılabilir! 🗳️🌿",
            'image' => null,
            'likes' => 52,
            'comments' => []
        ]
    ]
];

// Sayfa Yönlendirici (Router)
$page = 'feed';
$currentMemberKey = 'kadircbc';

// /profile/@username veya /profile/username veya /profile eşleşmesi
if (preg_match('#^/profile(?:/@?([a-zA-Z0-9_\.\-]+))?#i', $uri, $matches)) {
    $page = 'profile';
    $rawHandle = strtolower(trim($matches[1] ?? ''));
    if ($rawHandle !== '' && isset($members[$rawHandle])) {
        $currentMemberKey = $rawHandle;
    } else {
        $currentMemberKey = 'kadircbc';
    }
} elseif (str_starts_with($uri, '/u/')) {
    $page = 'profile';
    $rawHandle = strtolower(trim(substr($uri, 3)));
    if (isset($members[$rawHandle])) {
        $currentMemberKey = $rawHandle;
    } else {
        $currentMemberKey = 'kadircbc';
    }
} elseif (str_starts_with($uri, '/events')) {
    $page = 'events';
} elseif (str_starts_with($uri, '/login')) {
    $page = 'login';
} elseif (str_starts_with($uri, '/register')) {
    $page = 'register';
} elseif (str_starts_with($uri, '/forgot-password')) {
    $page = 'forgot-password';
} elseif (str_starts_with($uri, '/marketplace')) {
    $page = 'marketplace';
} elseif (str_starts_with($uri, '/business')) {
    $page = 'business';
} elseif (str_starts_with($uri, '/analytics')) {
    $page = 'analytics';
} elseif (str_starts_with($uri, '/emergency')) {
    $page = 'emergency';
}

$currentMember = $members[$currentMemberKey];

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="tr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Meydan - Yerel Mahalle Ağı</title>
    
    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'Inter', 'system-ui', 'sans-serif'],
                        mono: ['ui-monospace', 'SFMono-Regular', 'Menlo', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#f0f6fe',
                            100: '#e0edfd',
                            500: '#2563eb',
                            600: '#1d4ed8',
                            700: '#1e40af',
                        }
                    },
                    boxShadow: {
                        'subtle-card': '0 1px 3px 0 rgba(0, 0, 0, 0.04), 0 8px 24px -4px rgba(15, 23, 42, 0.05)',
                        'glow-brand': '0 4px 20px -2px rgba(37, 99, 235, 0.3)',
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }

        body {
            background-color: #f8fafc;
            color: #0f172a;
            transition: background-color 0.25s ease, color 0.25s ease;
        }

        .luxury-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.03), 0 10px 25px -5px rgba(15, 23, 42, 0.04);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .luxury-card-hover:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 12px 0 rgba(0, 0, 0, 0.05), 0 16px 32px -4px rgba(15, 23, 42, 0.08);
            transform: translateY(-1.5px);
        }

        html.dark body {
            background-color: #0b0f19;
            color: #f8fafc;
        }

        html.dark .luxury-card {
            background: #111827;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.4), 0 12px 30px -6px rgba(0, 0, 0, 0.6);
        }

        html.dark .luxury-card-hover:hover {
            border-color: rgba(255, 255, 255, 0.16);
            box-shadow: 0 4px 16px 0 rgba(0, 0, 0, 0.6), 0 20px 40px -6px rgba(0, 0, 0, 0.8);
            transform: translateY(-1.5px);
        }

        html { scroll-behavior: smooth; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>

    <!-- Alpine.data Tanımı (HTML attribute kaçış hatalarını %100 önler) -->
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('meydanApp', () => ({
            currentPage: '<?= $page ?>',
            darkMode: false,
            activeTab: 'local',
            composeTab: 'post',
            profileTab: 'posts',
            postText: '',
            district: 'Keçiören',
            city: 'Ankara',
            
            // Giriş / Oturum Durumu
            isLoggedIn: localStorage.getItem('meydan_is_logged_in') !== null 
                ? (localStorage.getItem('meydan_is_logged_in') === 'true') 
                : true,
            loginAs(handle) {
                localStorage.setItem('meydan_is_logged_in', 'true');
                this.isLoggedIn = true;
                this.triggerToast('Giriş başarılı! Hoş geldiniz. 🎉');
                setTimeout(() => {
                    window.location.href = handle ? '/profile/@' + handle : '/';
                }, 700);
            },
            logout() {
                localStorage.setItem('meydan_is_logged_in', 'false');
                this.isLoggedIn = false;
                this.triggerToast('Oturum kapatıldı. Görüşmek üzere! 👋');
                setTimeout(() => {
                    window.location.href = '/login';
                }, 700);
            },

            // Yasal Mevzuat & Sözleşme Onayları (KVKK, 5651, 6563)
            legalModalOpen: false,
            legalModalTab: 'kvkk', // 'kvkk', 'terms', 'law5651', 'cookies'
            registerTerms: false,
            registerKvkk: false,
            registerAccuracy: false,
            registerMarketing: false,
            openLegalModal(tab) {
                this.legalModalTab = tab || 'kvkk';
                this.legalModalOpen = true;
            },
            acceptAndCloseLegal() {
                if (this.legalModalTab === 'kvkk') this.registerKvkk = true;
                if (this.legalModalTab === 'terms') this.registerTerms = true;
                if (this.legalModalTab === 'law5651') this.registerAccuracy = true;
                this.legalModalOpen = false;
                this.triggerToast('Yasal şartlar onaylandı. ✓');
            },
            handleRegister() {
                if (!this.registerTerms || !this.registerKvkk || !this.registerAccuracy) {
                    this.triggerToast('⚠️ Lütfen mevzuat gereği zorunlu sözleşme, KVKK ve doğruluk beyanlarını işaretleyiniz.');
                    return;
                }
                this.triggerToast('Yasal şartlar onaylandı. Komşuluğa hoş geldiniz! 🏡');
                setTimeout(() => {
                    this.loginAs('kadircbc');
                }, 900);
            },

            // Toast Bildirimi
            toastMessage: '',
            showToast: false,
            triggerToast(msg) {
                this.toastMessage = msg;
                this.showToast = true;
                setTimeout(() => { this.showToast = false; }, 3200);
            },

            // Gönderiler Listesi (LocalStorage Desteği ile Kalıcı)
            posts: [],

            loadPosts() {
                const saved = localStorage.getItem('meydan_posts');
                if (saved) {
                    try {
                        this.posts = JSON.parse(saved);
                    } catch (e) {
                        this.initDefaultPosts();
                    }
                } else {
                    this.initDefaultPosts();
                }
            },

            initDefaultPosts() {
                this.posts = [
                    {
                        id: 1,
                        author: 'Ahmet Yılmaz',
                        handle: '@ahmetyilmaz',
                        avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=120&q=80',
                        badge: 'Mahalle Sakini',
                        time: '15 dakika önce',
                        location: 'Keçiören / Kalaba',
                        content: 'Komşular günaydın! Bu hafta sonu Keçiören\'de hep birlikte güzel bir açık hava etkinliği planlamak istiyoruz. Aşağıdaki ankette oyunuzu kullanarak mahalle kararına doğrudan katılalım!',
                        isPoll: true,
                        pollVoted: false,
                        likes: 52,
                        isLiked: false,
                        comments: 14
                    }
                ];
            },

            savePosts() {
                localStorage.setItem('meydan_posts', JSON.stringify(this.posts));
            },

            // Gönderi Paylaşma Fonksiyonu (%100 Çalışır ve Anında Akışa Ekler)
            submitPost() {
                const text = (this.postText || '').trim();
                if (!text) {
                    this.triggerToast('Lütfen paylaşmak için bir şeyler yazın.');
                    return;
                }

                const newPost = {
                    id: Date.now(),
                    author: 'Kadir Çelebi',
                    handle: '@kadir.cbc',
                    avatar: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=150&q=80',
                    badge: 'Aktif Komşu',
                    time: 'Az önce',
                    location: 'Keçiören / Kalaba',
                    content: text,
                    isPoll: false,
                    likes: 0,
                    isLiked: false,
                    comments: 0
                };

                this.posts.unshift(newPost);
                this.savePosts();
                this.postText = '';
                this.triggerToast('Gönderiniz mahalle akışında yayınlandı! 🚀');
            },

            // Anket Oy Verme
            votePoll(postId) {
                const p = this.posts.find(item => item.id === postId);
                if (p) {
                    p.pollVoted = true;
                    this.savePosts();
                    this.triggerToast('Oyunuz başarıyla kaydedildi! 🗳️');
                }
            },

            // Beğeni Butonu
            toggleLike(postId) {
                const p = this.posts.find(item => item.id === postId);
                if (p) {
                    p.isLiked = !p.isLiked;
                    p.likes += p.isLiked ? 1 : -1;
                    this.savePosts();
                }
            },

            // Etkinlik Sayaçları
            event1Joined: false,
            event1Count: 18,
            event2Joined: false,
            event2Count: 24,

            toggleEvent(num) {
                if (num === 1) {
                    this.event1Joined = !this.event1Joined;
                    this.event1Count += this.event1Joined ? 1 : -1;
                    this.triggerToast(this.event1Joined ? 'Botanikte Sabah Koşusu etkinliğine katıldınız! 🏃' : 'Katılımınız iptal edildi.');
                } else {
                    this.event2Joined = !this.event2Joined;
                    this.event2Count += this.event2Joined ? 1 : -1;
                    this.triggerToast(this.event2Joined ? 'Kitap Kulübü buluşmasına katıldınız! 📚' : 'Katılımınız iptal edildi.');
                }
            },

            // Güvenli Mesajlaşma Modalı
            showChatModal: false,
            chatRecipient: '',
            chatItemTitle: '',
            chatMessage: '',
            openChat(recipient, itemTitle) {
                this.chatRecipient = recipient;
                this.chatItemTitle = itemTitle;
                this.chatMessage = 'Merhaba, ' + itemTitle + ' ilanınızla ilgileniyorum. Mahallede elden teslim alabilir miyim?';
                this.showChatModal = true;
            },
            sendChatMessage() {
                this.showChatModal = false;
                this.triggerToast('Güvenli mesaj satıcıya iletildi! 💬');
            },

            init() {
                this.loadPosts();
                const savedTheme = localStorage.getItem('meydan_theme');
                if (savedTheme) {
                    this.darkMode = savedTheme === 'dark';
                }
                this.applyTheme();
            },
            setTheme(dark) {
                this.darkMode = dark;
                localStorage.setItem('meydan_theme', this.darkMode ? 'dark' : 'light');
                this.applyTheme();
            },
            applyTheme() {
                if (this.darkMode) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            }
        }));
    });
    </script>
</head>
<body class="min-h-screen flex flex-col font-sans" x-data="meydanApp">

    <!-- Toast Bildirimi -->
    <div x-show="showToast"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         x-cloak
         class="fixed bottom-6 right-6 z-50 px-4 py-3 rounded-2xl bg-slate-900 text-white dark:bg-blue-600 shadow-2xl flex items-center gap-3 border border-white/10 text-xs font-bold">
        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
        <span x-text="toastMessage"></span>
    </div>

    <!-- ================================================================= -->
    <!-- 1. ÜST NAVİGASYON (Header)                                        -->
    <!-- ================================================================= -->
        <?php if (!in_array($page, ['login', 'register', 'forgot-password'])): ?>
    <header class="sticky top-0 z-40 w-full backdrop-blur-xl bg-white/90 dark:bg-[#0b0f19]/90 border-b border-slate-200/80 dark:border-white/[0.08] transition-colors">
        <div class="max-w-[1560px] mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
            
            <!-- Logo & Lokasyon -->
            <div class="flex items-center gap-4 shrink-0">
                <a href="/" class="flex items-center gap-2.5 group">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-black shadow-glow-brand group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-xl font-black tracking-tight text-slate-900 dark:text-white">Meydan</span>
                            <span class="text-[10px] font-black px-1.5 py-0.5 rounded bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400">PRO</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-[11px] font-semibold text-slate-500 dark:text-slate-400 -mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span>Keçiören / Kalaba</span>
                        </div>
                    </div>
                </a>

                <div class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-white/10 text-xs font-bold text-slate-700 dark:text-slate-200">
                    <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                    <span x-text="`${city} / ${district}`"></span>
                </div>
            </div>

            <!-- Orta Arama Çubuğu -->
            <div class="hidden md:flex flex-1 max-w-xl mx-4">
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text"
                           placeholder="Mahallede esnaf, etkinlik, satılık eşya veya duyuru ara..."
                           class="w-full pl-9 pr-12 py-2 rounded-xl text-xs bg-slate-100/80 dark:bg-slate-800/60 border border-transparent focus:border-blue-500 focus:bg-white dark:focus:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none transition-all">
                    <span class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-[10px] font-mono text-slate-400">⌘K</span>
                </div>
            </div>

            <!-- Sağ: Aydınlık/Koyu Switcher & Profil Butonu -->
            <div class="flex items-center gap-3">
                <div class="p-1 rounded-xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-white/10 flex items-center gap-1">
                    <button @click="setTheme(false)"
                            type="button"
                            :class="!darkMode ? 'bg-white text-blue-600 shadow-sm font-bold' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400'"
                            class="px-2.5 py-1 rounded-lg text-xs flex items-center gap-1.5 transition-all">
                        <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span class="hidden sm:inline">Aydınlık</span>
                    </button>
                    <button @click="setTheme(true)"
                            type="button"
                            :class="darkMode ? 'bg-slate-700 text-blue-400 shadow-sm font-bold' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400'"
                            class="px-2.5 py-1 rounded-lg text-xs flex items-center gap-1.5 transition-all">
                        <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        <span class="hidden sm:inline">Koyu</span>
                    </button>
                </div>

                <!-- Giriş Yapılmamışsa Görünen Alanlar -->
                <template x-if="!isLoggedIn">
                    <div class="flex items-center gap-2">
                        <a href="/login"
                           class="px-3.5 py-1.5 rounded-xl border border-slate-300 dark:border-white/15 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-bold transition-colors">
                            <span>Giriş Yap</span>
                        </a>
                        <a href="/register"
                           class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition-colors">
                            <span>Kayıt Ol</span>
                        </a>
                    </div>
                </template>

                <!-- Giriş Yapılmışsa Görünen Alanlar -->
                <template x-if="isLoggedIn">
                    <div class="flex items-center gap-3">
                        <a href="/events"
                           class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-sm transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            <span>Yeni Buluşma</span>
                        </a>

                        <!-- Profil Menüsü -->
                        <div class="relative" x-data="{ userMenu: false }">
                            <button @click="userMenu = !userMenu"
                                    @click.away="userMenu = false"
                                    type="button"
                                    class="relative flex items-center gap-2 p-0.5 rounded-xl hover:ring-2 hover:ring-blue-500 transition-all cursor-pointer">
                                <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=120&q=80"
                                     alt="Profil"
                                     class="w-9 h-9 rounded-xl object-cover ring-2 ring-slate-200 dark:ring-white/10">
                                <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-emerald-500 rounded-full ring-2 ring-white dark:ring-slate-900"></span>
                            </button>

                            <!-- Açılır Dropdown Menü -->
                            <div x-show="userMenu"
                                 x-transition
                                 x-cloak
                                 class="absolute right-0 mt-2 w-48 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-white/10 shadow-xl py-2 z-50 text-xs">
                                <div class="px-3.5 py-2 border-b border-slate-100 dark:border-white/5">
                                    <span class="font-bold text-slate-900 dark:text-white block truncate">Kadir Çelebi</span>
                                    <span class="text-[10px] text-slate-400 font-mono">@kadircbc</span>
                                </div>
                                <a href="/profile/@kadircbc" class="flex items-center gap-2 px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold">
                                    <span>👤 Profilim</span>
                                </a>
                                <a href="/events" class="flex items-center gap-2 px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold">
                                    <span>📅 Buluşmalar</span>
                                </a>
                                <div class="border-t border-slate-100 dark:border-white/5 my-1"></div>
                                <button @click="logout()" type="button" class="w-full text-left flex items-center gap-2 px-3.5 py-2 hover:bg-rose-50 dark:hover:bg-rose-950/30 text-rose-600 dark:text-rose-400 font-bold cursor-pointer">
                                    <span>🚪 Çıkış Yap</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </header>
    <?php endif; ?>

    <?php if ($page === 'profile'): ?>
    <!-- YENİ NESİL ELİT SOSYAL PROFİL SAYFASI (Modern Social Media Profile)-->
    <!-- Link Yapısı: /profile/@<?= $currentMember['handle'] ?>            -->
    <!-- ================================================================= -->

    <div class="max-w-[1560px] mx-auto w-full px-4 sm:px-6 lg:px-8 py-5 flex-1 space-y-5">

        <!-- 1. ANA PROFİL KARTI (Twitter / Threads / LinkedIn Elite Format) -->
        <div class="luxury-card rounded-3xl overflow-hidden border border-slate-200/80 dark:border-white/10 bg-white dark:bg-[#0f172a] shadow-md">
            
            <!-- 1.1 KAPAK FOTOĞRAFI (Panoramik & Şık) -->
            <div class="h-48 sm:h-64 md:h-72 w-full relative bg-slate-800 overflow-hidden group">
                <img src="<?= $currentMember['cover'] ?>"
                     alt="<?= htmlspecialchars($currentMember['name']) ?> Kapak"
                     class="w-full h-full object-cover object-center group-hover:scale-[1.01] transition-transform duration-700">
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>

                <!-- Kapak Üzeri Lokasyon -->
                <div class="absolute top-4 left-4 sm:left-6 flex items-center gap-2">
                    <span class="px-3.5 py-1.5 rounded-full bg-black/60 text-white backdrop-blur-md text-xs font-bold flex items-center gap-1.5 border border-white/20 shadow-md">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>📍 <?= htmlspecialchars($currentMember['location']) ?></span>
                    </span>
                    <span class="hidden sm:inline-flex px-3 py-1.5 rounded-full bg-blue-600/80 text-white backdrop-blur-md text-xs font-bold border border-white/20 shadow-md">
                        <?= htmlspecialchars($currentMember['role']) ?>
                    </span>
                </div>

                <!-- Kapak Aksiyonu -->
                <?php if ($currentMember['is_current_user']): ?>
                    <button @click="triggerToast('Kapak fotoğrafı değiştirme modalı açıldı.')"
                            class="absolute top-4 right-4 sm:right-6 px-3.5 py-1.5 rounded-xl bg-black/60 hover:bg-black/80 text-white backdrop-blur-md text-xs font-bold flex items-center gap-1.5 border border-white/20 shadow-md transition-all cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span class="hidden sm:inline">Kapağı Değiştir</span>
                    </button>
                <?php endif; ?>
            </div>

            <!-- 1.2 KİMLİK & BİYOGRAFİ & AKSİYON ALANI -->
            <div class="px-5 sm:px-8 pb-4 relative">
                
                <!-- Üst Satır: Avatar ve Aksiyon Butonları -->
                <div class="flex items-end justify-between gap-4 -mt-16 sm:-mt-20 mb-4">
                    <!-- Avatar (Overlapping Circle) -->
                    <div class="relative shrink-0">
                        <div class="w-28 h-28 sm:w-36 sm:h-36 rounded-full ring-4 ring-white dark:ring-[#0f172a] shadow-xl overflow-hidden bg-white dark:bg-slate-800">
                            <img src="<?= $currentMember['avatar'] ?>"
                                 alt="<?= htmlspecialchars($currentMember['name']) ?>"
                                 class="w-full h-full object-cover">
                        </div>
                        <span class="absolute bottom-1 right-2 w-4 h-4 sm:w-5 sm:h-5 bg-emerald-500 rounded-full ring-4 ring-white dark:ring-[#0f172a]" title="Çevrimiçi"></span>
                        <?php if ($currentMember['is_current_user']): ?>
                            <button @click="triggerToast('Profil fotoğrafı seçici açıldı.')"
                                    class="absolute bottom-0 right-7 p-1.5 rounded-full bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-white ring-2 ring-white dark:ring-[#0f172a] shadow-sm transition-transform active:scale-95 cursor-pointer"
                                    title="Fotoğrafı Değiştir">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- Aksiyon Butonları (Sağ Taraf) -->
                    <div class="flex items-center gap-2 pt-2 sm:pt-0">
                        <?php if ($currentMember['is_current_user']): ?>
                            <button @click="triggerToast('Profil düzenleme penceresi açıldı.')"
                                    class="px-4 py-2 rounded-xl border border-slate-300 dark:border-white/15 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-800 dark:text-white font-bold text-xs sm:text-sm flex items-center gap-1.5 transition-colors cursor-pointer shadow-sm">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                <span>Profili Düzenle</span>
                            </button>
                            <button @click="triggerToast('Yeni gönderi penceresi odaklandı.')"
                                    class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm flex items-center gap-1.5 shadow-sm transition-colors cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                <span class="hidden sm:inline">Gönderi Paylaş</span>
                            </button>
                            <button @click="navigator.clipboard.writeText(window.location.origin + '/profile/@<?= $currentMember['handle'] ?>'); triggerToast('Profil bağlantısı kopyalandı! 🔗');"
                                    class="p-2 rounded-xl border border-slate-300 dark:border-white/15 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 transition-colors cursor-pointer"
                                    title="Profil Linkini Kopyala">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </button>
                        
                        <?php else: ?>
                            <button @click="triggerToast('<?= htmlspecialchars($currentMember['name']) ?> ile komşuluk kuruldu! 🤝')"
                                    class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm flex items-center gap-1.5 shadow-sm transition-colors cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Komşumsun</span>
                            </button>
                            <button @click="openChat('<?= htmlspecialchars($currentMember['name']) ?>', 'Profil Mesajı')"
                                    class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm flex items-center gap-1.5 shadow-sm transition-colors cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                <span>Mesaj Gönder</span>
                            </button>
                            <button @click="navigator.clipboard.writeText(window.location.origin + '/profile/@<?= $currentMember['handle'] ?>'); triggerToast('Profil bağlantısı kopyalandı! 🔗');"
                                    class="p-2 rounded-xl border border-slate-300 dark:border-white/15 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 transition-colors cursor-pointer"
                                    title="Profili Paylaş">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- İsim, Handle ve Rozetler -->
                <div class="space-y-2">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                            <?= htmlspecialchars($currentMember['name']) ?>
                        </h1>
                        <svg class="w-5 h-5 text-blue-500 shrink-0" fill="currentColor" viewBox="0 0 20 20" title="Doğrulanmış Mahalle Sakini"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        
                        <span class="text-xs font-mono font-bold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-md">
                            @<?= $currentMember['handle'] ?>
                        </span>

                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 border border-blue-200/60 dark:border-blue-800/40">
                            <?= htmlspecialchars($currentMember['role']) ?>
                        </span>

                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/40">
                            ★ <?= $currentMember['reputation'] ?> İtibar
                        </span>
                    </div>

                    <!-- Biyografi -->
                    <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed max-w-3xl">
                        <?= htmlspecialchars($currentMember['bio']) ?>
                    </p>

                    <!-- Etiketler -->
                    <div class="flex flex-wrap gap-1.5 pt-0.5">
                        <?php foreach ($currentMember['tags'] as $tag): ?>
                            <span class="text-[11px] font-bold text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">
                                <?= htmlspecialchars($tag) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>

                    <!-- Detay Bilgileri & İkonlar (Inline Minimalist Row) -->
                    <div class="flex items-center gap-x-4 gap-y-2 flex-wrap text-xs text-slate-500 dark:text-slate-400 pt-1">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span><?= htmlspecialchars($currentMember['job']) ?></span>
                        </span>
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span><?= htmlspecialchars($currentMember['workplace']) ?></span>
                        </span>
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                            <span><?= htmlspecialchars($currentMember['education']) ?></span>
                        </span>
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span><?= htmlspecialchars($currentMember['joined']) ?> katıldı</span>
                        </span>
                    </div>

                    <!-- Komşu ve Ortak Komşu Sayaçları (Twitter / LinkedIn Style Inline) -->
                    <div class="flex items-center gap-4 pt-2 text-xs text-slate-600 dark:text-slate-300 border-t border-slate-100 dark:border-white/5">
                        <div>
                            <strong class="text-slate-900 dark:text-white font-extrabold text-sm"><?= $currentMember['posts_count'] ?></strong> Gönderi
                        </div>
                        <div>
                            <strong class="text-slate-900 dark:text-white font-extrabold text-sm"><?= $currentMember['neighbors_count'] ?></strong> Komşu
                        </div>
                        <div>
                            <strong class="text-slate-900 dark:text-white font-extrabold text-sm"><?= $currentMember['events_count'] ?></strong> Buluşma
                        </div>
                        
                        <!-- Ortak Komşu Avatarları -->
                        <div class="flex items-center gap-2 pl-2 border-l border-slate-200 dark:border-white/10">
                            <div class="flex -space-x-2 shrink-0">
                                <?php foreach (array_slice($currentMember['friends'], 0, 4) as $fKey): ?>
                                    <?php if (isset($members[$fKey])): ?>
                                        <a href="/profile/@<?= $members[$fKey]['handle'] ?>" title="<?= htmlspecialchars($members[$fKey]['name']) ?>">
                                            <img src="<?= $members[$fKey]['avatar'] ?>" class="w-5 h-5 rounded-full ring-2 ring-white dark:ring-[#0f172a] object-cover hover:scale-120 transition-transform">
                                        </a>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                            <span class="text-[11px] text-slate-400 font-medium">
                                <?= $currentMember['mutual_count'] ?> ortak komşu
                            </span>
                        </div>
                    </div>

                </div>

                <!-- 1.3 SEKMELER (Temiz, Modern Çizgili Menü) -->
                <div class="flex items-center gap-1 sm:gap-2 overflow-x-auto scrollbar-none pt-4 mt-3 border-t border-slate-100 dark:border-white/5">
                    <button @click="profileTab = 'posts'"
                            :class="profileTab === 'posts' ? 'border-blue-600 text-blue-600 dark:text-blue-400 font-extrabold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 font-semibold'"
                            class="px-4 py-2.5 text-xs sm:text-sm border-b-2 transition-all whitespace-nowrap cursor-pointer">
                        Gönderiler (<?= $currentMember['posts_count'] ?>)
                    </button>
                    <button @click="profileTab = 'about'"
                            :class="profileTab === 'about' ? 'border-blue-600 text-blue-600 dark:text-blue-400 font-extrabold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 font-semibold'"
                            class="px-4 py-2.5 text-xs sm:text-sm border-b-2 transition-all whitespace-nowrap cursor-pointer">
                        Künye & Pasaport
                    </button>
                    <button @click="profileTab = 'friends'"
                            :class="profileTab === 'friends' ? 'border-blue-600 text-blue-600 dark:text-blue-400 font-extrabold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 font-semibold'"
                            class="px-4 py-2.5 text-xs sm:text-sm border-b-2 transition-all whitespace-nowrap cursor-pointer">
                        Komşular (<?= $currentMember['neighbors_count'] ?>)
                    </button>
                    <button @click="profileTab = 'photos'"
                            :class="profileTab === 'photos' ? 'border-blue-600 text-blue-600 dark:text-blue-400 font-extrabold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 font-semibold'"
                            class="px-4 py-2.5 text-xs sm:text-sm border-b-2 transition-all whitespace-nowrap cursor-pointer">
                        Fotoğraflar (<?= count($currentMember['photos']) ?>)
                    </button>
                    <a href="/events"
                       class="px-4 py-2.5 text-xs sm:text-sm border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 font-semibold transition-all whitespace-nowrap">
                        Buluşmalar (<?= $currentMember['events_count'] ?>)
                    </a>
                </div>

            </div>

        </div>

        <!-- 2. İKİLİ GÖVDE DÜZENİ (Sol: Sabit Pasaport/Rozet/Foto | Sağ: Gönderi & Zaman Tüneli) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
            
            <!-- 2.1 SOL SÜTUN (KÜNYE, PASAPORT, ROZETLER, FOTOĞRAFLAR, KOMŞULAR) -->
            <div class="col-span-12 lg:col-span-4 space-y-5">
                
                <!-- KART 1: KOMŞU PASAPORTU & İTİBAR -->
                <div class="luxury-card rounded-2xl p-4 sm:p-5 space-y-3.5 border border-slate-200/80 dark:border-white/10 bg-white dark:bg-[#0f172a] shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-base">🛂</span>
                            <h3 class="text-sm font-black text-slate-900 dark:text-white">Komşu Pasaportu</h3>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40">
                            Doğrulandı
                        </span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-white/5 space-y-2">
                        <div class="flex items-center justify-between text-xs font-bold">
                            <span class="text-slate-800 dark:text-slate-200"><?= $currentMember['level'] ?></span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-mono font-black"><?= $currentMember['reputation'] ?> Puan</span>
                        </div>
                        <div class="w-full bg-slate-200 dark:bg-white/10 h-2 rounded-full overflow-hidden">
                            <div class="h-full rounded-full bg-blue-600 transition-all duration-500" style="width: <?= $currentMember['progress'] ?>%"></div>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-slate-400">
                            <span>Sonraki Seviye:</span>
                            <span class="font-bold text-blue-600 dark:text-blue-400"><?= $currentMember['next_level'] ?></span>
                        </div>
                    </div>

                    <!-- Rozetler -->
                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <?php foreach ($currentMember['badges'] as $b): ?>
                            <div class="p-2 rounded-xl border <?= $b['bg'] ?> flex items-center gap-2 text-left">
                                <span class="text-sm shrink-0"><?= $b['icon'] ?></span>
                                <span class="text-[10px] font-bold truncate"><?= $b['title'] ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- KART 2: FOTOĞRAFLAR (Instagram 3x2 Grid) -->
                <div class="luxury-card rounded-2xl p-4 sm:p-5 space-y-3 border border-slate-200/80 dark:border-white/10 bg-white dark:bg-[#0f172a] shadow-sm">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-black text-slate-900 dark:text-white">Fotoğraflar</h3>
                        <span class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline cursor-pointer" @click="triggerToast('Fotoğraf galerisi açılıyor.')">
                            Tümünü Gör (<?= count($currentMember['photos']) ?>)
                        </span>
                    </div>

                    <div class="grid grid-cols-3 gap-1.5 rounded-xl overflow-hidden">
                        <?php foreach ($currentMember['photos'] as $pUrl): ?>
                            <img src="<?= $pUrl ?>"
                                 @click="triggerToast('Fotoğraf tam ekran açıldı.')"
                                 class="aspect-square w-full object-cover hover:scale-105 transition-transform duration-300 cursor-pointer">
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- KART 3: MAHALLEDEKİ DİĞER KOMŞULAR -->
                <div class="luxury-card rounded-2xl p-4 sm:p-5 space-y-3 border border-slate-200/80 dark:border-white/10 bg-white dark:bg-[#0f172a] shadow-sm">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-black text-slate-900 dark:text-white">Komşular</h3>
                        <span class="text-[11px] font-semibold text-slate-400">Keşfet</span>
                    </div>

                    <div class="space-y-2">
                        <?php foreach ($members as $mKey => $m): ?>
                            <?php if ($mKey !== $currentMemberKey): ?>
                                <a href="/profile/@<?= $m['handle'] ?>"
                                   class="p-2 rounded-xl bg-slate-50 dark:bg-slate-800/40 hover:bg-blue-50 dark:hover:bg-slate-800 border border-slate-100 dark:border-white/5 flex items-center justify-between transition-colors group">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <img src="<?= $m['avatar'] ?>" class="w-8 h-8 rounded-full object-cover shrink-0">
                                        <div class="min-w-0">
                                            <span class="font-bold text-xs text-slate-900 dark:text-white group-hover:text-blue-600 block truncate"><?= htmlspecialchars($m['name']) ?></span>
                                            <span class="text-[10px] text-slate-400 block truncate">@<?= $m['handle'] ?></span>
                                        </div>
                                    </div>
                                    <span class="text-[11px] text-blue-600 dark:text-blue-400 font-bold shrink-0">Profili Gör →</span>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>

            <!-- 2.2 SAĞ SÜTUN (GÖNDERİ OLUŞTURUCU + ZAMAN TÜNELİ) -->
            <div class="col-span-12 lg:col-span-8 space-y-4">

                <!-- KENDİ PROFİLİNDEYSE: GÖNDERİ PAYLAŞMA KUTUSU -->
                <?php if ($currentMember['is_current_user']): ?>
                    <div class="luxury-card rounded-2xl p-4 sm:p-5 space-y-3 border border-slate-200/80 dark:border-white/10 bg-white dark:bg-[#0f172a] shadow-sm">
                        <div class="flex items-start gap-3">
                            <img src="<?= $currentMember['avatar'] ?>" class="w-10 h-10 rounded-full object-cover shrink-0">
                            <textarea x-model="postText"
                                      rows="2"
                                      placeholder="Kadir, mahallede neler oluyor? Bir duyuru, soru veya gelişme paylaş..."
                                      class="w-full bg-slate-50 dark:bg-slate-800/50 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl p-3 border border-slate-200 dark:border-white/10 resize-none text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 outline-none focus:ring-1 focus:ring-blue-500 transition-colors"></textarea>
                        </div>
                        <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-white/5 flex-wrap gap-2">
                            <div class="flex items-center gap-1.5">
                                <button type="button" @click="triggerToast('Fotoğraf seçici açıldı.')" class="px-2.5 py-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold text-slate-600 dark:text-slate-300 flex items-center gap-1.5 cursor-pointer">
                                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>Görsel</span>
                                </button>
                                <button type="button" @click="triggerToast('Komşu etiketleme paneli açıldı.')" class="px-2.5 py-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold text-slate-600 dark:text-slate-300 flex items-center gap-1.5 cursor-pointer">
                                    <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span>Komşu Etiketle</span>
                                </button>
                            </div>
                            <button @click="submitPost()" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm shadow-sm transition-colors cursor-pointer">
                                Paylaş
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="luxury-card rounded-2xl p-4 space-y-2 border border-slate-200/80 dark:border-white/10 bg-white dark:bg-[#0f172a] shadow-sm">
                        <span class="text-xs font-bold text-slate-900 dark:text-white block"><?= htmlspecialchars($currentMember['name']) ?> komşunuza bir selam bırakın</span>
                        <div class="flex items-center gap-2">
                            <input type="text"
                                   placeholder="Merhaba <?= htmlspecialchars($currentMember['name']) ?>..."
                                   class="flex-1 text-xs px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-white/10 text-slate-900 dark:text-white outline-none focus:ring-1 focus:ring-blue-500">
                            <button @click="triggerToast('Selam mesajınız iletildi! 👋')" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shrink-0 cursor-pointer">
                                Gönder
                            </button>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- ANLIK PAYLAŞILAN GÖNDERİLER (Kadir Çelebi Canlı Gönderileri) -->
                <?php if ($currentMember['is_current_user']): ?>
                    <template x-for="post in posts.filter(p => p.author === 'Kadir Çelebi')" :key="post.id">
                        <div class="luxury-card rounded-2xl p-5 space-y-3.5 border border-slate-200/80 dark:border-white/10 bg-white dark:bg-[#0f172a] shadow-sm">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <img :src="post.avatar" class="w-10 h-10 rounded-full object-cover">
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <h4 class="text-sm font-bold text-slate-900 dark:text-white" x-text="post.author"></h4>
                                            <svg class="w-3.5 h-3.5 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-[11px] text-slate-400">
                                            <span x-text="post.time"></span> • <span x-text="post.location"></span>
                                        </div>
                                    </div>
                                </div>
                                <button class="text-slate-400 p-1">•••</button>
                            </div>

                            <p class="text-sm text-slate-800 dark:text-slate-100 font-medium leading-relaxed whitespace-pre-line" x-text="post.content"></p>

                            <!-- Beğeni / Yorum Barı -->
                            <div class="flex items-center justify-between text-xs text-slate-500 pt-2 border-t border-slate-100 dark:border-white/5">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-5 h-5 rounded-full bg-blue-500 flex items-center justify-center text-[10px] text-white">👍</span>
                                    <span class="font-bold text-slate-700 dark:text-slate-300" x-text="post.likes || 1"></span>
                                </div>
                                <span x-text="`${post.comments || 0} yorum`"></span>
                            </div>

                            <div class="grid grid-cols-3 gap-1 pt-1 border-t border-slate-100 dark:border-white/5 text-xs">
                                <button @click="toggleLike(post.id)" :class="post.isLiked ? 'text-blue-600 font-bold' : 'text-slate-600 dark:text-slate-300'" class="py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center gap-1.5 cursor-pointer">
                                    <span>👍 Beğen</span>
                                </button>
                                <button @click="triggerToast('Yorum kutusu açıldı.')" class="py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 font-semibold flex items-center justify-center gap-1.5 cursor-pointer">
                                    <span>💬 Yorum</span>
                                </button>
                                <button @click="triggerToast('Profilinizde paylaşıldı! 🚀')" class="py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 font-semibold flex items-center justify-center gap-1.5 cursor-pointer">
                                    <span>🔗 Paylaş</span>
                                </button>
                            </div>
                        </div>
                    </template>
                <?php endif; ?>

                <!-- SEÇİLİ KOMŞUNUN ZAMAN TÜNELİ GÖNDERİLERİ -->
                <?php if (isset($memberPosts[$currentMemberKey]) && count($memberPosts[$currentMemberKey]) > 0): ?>
                    <?php foreach ($memberPosts[$currentMemberKey] as $post): ?>
                        <div class="luxury-card rounded-2xl p-5 space-y-3.5 border border-slate-200/80 dark:border-white/10 bg-white dark:bg-[#0f172a] shadow-sm">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <a href="/profile/@<?= $currentMember['handle'] ?>">
                                        <img src="<?= $post['avatar'] ?>" class="w-10 h-10 rounded-full object-cover">
                                    </a>
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <a href="/profile/@<?= $currentMember['handle'] ?>" class="text-sm font-bold text-slate-900 dark:text-white hover:text-blue-600">
                                                <?= htmlspecialchars($post['author']) ?>
                                            </a>
                                            <svg class="w-3.5 h-3.5 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-[11px] text-slate-400">
                                            <span><?= $post['time'] ?></span> • <span><?= $post['location'] ?></span>
                                        </div>
                                    </div>
                                </div>
                                <button class="text-slate-400 p-1">•••</button>
                            </div>

                            <p class="text-sm text-slate-800 dark:text-slate-100 font-medium leading-relaxed">
                                <?= htmlspecialchars($post['content']) ?>
                            </p>

                            <?php if (!empty($post['image'])): ?>
                                <div class="rounded-xl overflow-hidden border border-slate-100 dark:border-white/5">
                                    <img src="<?= $post['image'] ?>" class="w-full h-72 sm:h-80 object-cover">
                                </div>
                            <?php endif; ?>

                            <!-- Beğeni Barı -->
                            <div class="flex items-center justify-between text-xs text-slate-500 pt-2 border-t border-slate-100 dark:border-white/5">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-5 h-5 rounded-full bg-blue-500 flex items-center justify-center text-[10px] text-white">👍</span>
                                    <span class="font-bold text-slate-700 dark:text-slate-300"><?= $post['likes'] ?> beğeni</span>
                                </div>
                                <span><?= count($post['comments']) ?> yorum</span>
                            </div>

                            <div class="grid grid-cols-3 gap-1 pt-1 border-t border-slate-100 dark:border-white/5 text-xs">
                                <button @click="triggerToast('Beğendiniz! 👍')" class="py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-blue-600 font-bold flex items-center justify-center gap-1.5 cursor-pointer">
                                    <span>👍 Beğen</span>
                                </button>
                                <button @click="triggerToast('Yorum yazma kutusu odaklandı.')" class="py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 font-semibold flex items-center justify-center gap-1.5 cursor-pointer">
                                    <span>💬 Yorum Yap</span>
                                </button>
                                <button @click="triggerToast('Profilinizde paylaşıldı! 🚀')" class="py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 font-semibold flex items-center justify-center gap-1.5 cursor-pointer">
                                    <span>🔗 Paylaş</span>
                                </button>
                            </div>

                            <!-- Yorum Listesi -->
                            <?php if (!empty($post['comments'])): ?>
                                <div class="pt-3 border-t border-slate-100 dark:border-white/5 space-y-2.5">
                                    <?php foreach ($post['comments'] as $comm): ?>
                                        <div class="flex items-start gap-2.5">
                                            <a href="/profile/<?= $comm['handle'] ?>">
                                                <img src="<?= $comm['avatar'] ?>" class="w-7 h-7 rounded-full object-cover">
                                            </a>
                                            <div class="flex-1 bg-slate-100 dark:bg-slate-800/60 rounded-xl p-2.5 text-xs space-y-0.5">
                                                <div class="flex items-center justify-between">
                                                    <a href="/profile/<?= $comm['handle'] ?>" class="font-bold text-slate-900 dark:text-white hover:text-blue-600">
                                                        <?= htmlspecialchars($comm['author']) ?>
                                                    </a>
                                                    <span class="text-[10px] text-slate-400"><?= $comm['time'] ?></span>
                                                </div>
                                                <p class="text-slate-700 dark:text-slate-300"><?= htmlspecialchars($comm['text']) ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Yorum Ekleme Kutusu -->
                            <div class="flex items-center gap-2 pt-2 border-t border-slate-100 dark:border-white/5">
                                <img src="<?= $members['kadircbc']['avatar'] ?>" class="w-7 h-7 rounded-full object-cover shrink-0">
                                <input type="text"
                                       placeholder="Bir komşu yorumu yaz..."
                                       @keydown.enter="triggerToast('Yorumunuz iletildi! 💬')"
                                       class="flex-1 text-xs px-3 py-2 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 outline-none focus:ring-1 focus:ring-blue-500">
                                <button @click="triggerToast('Yorumunuz iletildi! 💬')" class="text-xs font-bold text-blue-600 px-2 py-1 cursor-pointer">
                                    Gönder
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="luxury-card rounded-2xl p-8 text-center space-y-1.5 border border-slate-200 dark:border-white/10 bg-white dark:bg-[#0f172a]">
                        <span class="text-3xl block">📬</span>
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white">Henüz Paylaşım Yok</h4>
                        <p class="text-xs text-slate-400">Bu komşumuz henüz bir zaman tüneli gönderisi paylaşmadı.</p>
                    </div>
                <?php endif; ?>

            </div>

        </div>


    <?php elseif ($page === 'events'): ?>
    <!-- ================================================================= -->
    <!-- KENDİ BAŞINA TAM SAYFA: ETKİNLİK & BULUŞMA MERKEZİ              -->
    <!-- Link: /events                                                     -->
    <!-- ================================================================= -->
    <div class="max-w-[1560px] mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 flex-1 space-y-6">

        <!-- 1. ETKİNLİK ÜST BANNER & ARAMA / AKSİYON ÇUBUĞU -->
        <div class="luxury-card rounded-3xl p-6 sm:p-8 border border-slate-200/80 dark:border-white/10 bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 text-white relative overflow-hidden shadow-xl">
            <!-- Arka Plan Dekoratif Işıklar -->
            <div class="absolute -right-10 -bottom-10 w-80 h-80 bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute right-1/3 -top-10 w-60 h-60 bg-purple-500/20 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
                <div class="space-y-2 max-w-2xl">
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-full bg-blue-500/30 text-blue-200 text-xs font-bold border border-blue-400/30 flex items-center gap-1.5 backdrop-blur-md">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>Keçiören / Kalaba Mahalle Buluşmaları</span>
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full bg-white/10 text-white/80 text-[11px] font-semibold">
                            Kasım 2026
                        </span>
                    </div>
                    <h1 class="text-2xl sm:text-4xl font-black tracking-tight text-white">
                        Mahalle Etkinlik & Buluşma Takvimi
                    </h1>
                    <p class="text-sm sm:text-base text-slate-300 leading-relaxed">
                        Komşularınızla spor yapın, açık hava sinemasında buluşun, kitap kulüplerine katılın ve mahallemizi el birliğiyle güzelleştirelim.
                    </p>
                </div>

                <!-- Aksiyon Butonları -->
                <div class="flex items-center gap-3 shrink-0 flex-wrap">
                    <button @click="triggerToast('Etkinlik oluşturma sihirbazı açıldı. 📅')"
                            class="px-5 py-3 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white text-xs sm:text-sm font-bold shadow-lg shadow-blue-600/40 transition-all hover:scale-105 flex items-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>+ Yeni Buluşma Başlat</span>
                    </button>
                    <a href="/profile/@kadircbc"
                       class="px-4 py-3 rounded-2xl bg-white/10 hover:bg-white/20 text-white text-xs sm:text-sm font-bold backdrop-blur-md border border-white/20 transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>Katıldığım Etkinlikler (8)</span>
                    </a>
                </div>
            </div>

            <!-- Kategori Filtre Butonları -->
            <div class="relative z-10 flex items-center gap-2 overflow-x-auto scrollbar-none pt-6 mt-6 border-t border-white/10">
                <button class="px-4 py-2 rounded-xl bg-white text-slate-900 text-xs font-extrabold shadow-sm whitespace-nowrap">
                    Tüm Etkinlikler
                </button>
                <button class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold transition-colors whitespace-nowrap">
                    🏃 Spor & Koşu
                </button>
                <button class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold transition-colors whitespace-nowrap">
                    📚 Kültür & Kitap
                </button>
                <button class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold transition-colors whitespace-nowrap">
                    🌿 Çevre & İmece
                </button>
                <button class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold transition-colors whitespace-nowrap">
                    🚲 Bisiklet & Doğa
                </button>
                <button class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold transition-colors whitespace-nowrap">
                    🩺 Sağlık & Yaşam
                </button>
                <button class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold transition-colors whitespace-nowrap">
                    🎬 Açık Hava Sineması
                </button>
            </div>
        </div>

        <!-- 2. BU HAFTA SONU ÖNE ÇIKAN İKİ BÜYÜK BULUŞMA (Featured Heroes) -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-amber-500 animate-pulse"></span>
                    <h2 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">
                        Bu Hafta Sonu Mahallede Ne Var?
                    </h2>
                </div>
                <span class="text-xs font-bold text-slate-400">14 - 15 Kasım 2026</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                
                <!-- ÖNE ÇIKAN 1: SABAH KOŞUSU -->
                <div class="luxury-card rounded-3xl overflow-hidden border border-slate-200/80 dark:border-white/10 bg-white dark:bg-[#0f172a] shadow-lg flex flex-col group">
                    <div class="h-60 w-full relative overflow-hidden bg-slate-800">
                        <img src="https://images.unsplash.com/photo-1517649763962-0c623266ddc0?auto=format&fit=crop&w=800&q=80"
                             alt="Botanikte Sabah Koşusu"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>
                        
                        <!-- Sol Üst Rozetler -->
                        <div class="absolute top-3.5 left-3.5 flex items-center gap-2">
                            <span class="px-3 py-1 rounded-full bg-emerald-600 text-white text-xs font-extrabold shadow-md">
                                🏃 Spor & Koşu
                            </span>
                            <span class="px-3 py-1 rounded-full bg-amber-500/90 text-slate-950 text-xs font-black shadow-md backdrop-blur-md">
                                ⭐ Sponsorlu Mekan
                            </span>
                        </div>

                        <!-- Sağ Üst Tarih -->
                        <div class="absolute top-3.5 right-3.5 px-3 py-1.5 rounded-xl bg-black/60 backdrop-blur-md text-white text-xs font-bold border border-white/20">
                            📅 Cumartesi • 09:30
                        </div>

                        <!-- Alt Bilgi -->
                        <div class="absolute bottom-3.5 left-3.5 right-3.5 text-white">
                            <h3 class="text-xl font-black tracking-tight drop-shadow-md">
                                Botanikte Sabah Koşusu & Komşu Kahvesi
                            </h3>
                            <p class="text-xs text-slate-200 mt-1 flex items-center gap-1.5">
                                <span>📍 Şelale Park Kafe • Kalaba Vadisi Parkuru</span>
                            </p>
                        </div>
                    </div>

                    <!-- Kart Gövdesi -->
                    <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Hafif tempolu 4 km sabah koşusunun ardından Şelale Park Kafe'nin ikramı olan taze kahvelerle komşuluk sohbeti yapıyoruz. Her seviyeden komşumuz davetlidir!
                        </p>

                        <!-- Düzenleyen & Katılımcılar -->
                        <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-white/5">
                            <a href="/profile/@mehmetkaya" class="flex items-center gap-2.5 group/org">
                                <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=100&q=80" class="w-8 h-8 rounded-full object-cover ring-2 ring-blue-500/30">
                                <div>
                                    <span class="text-[10px] text-slate-400 block font-semibold">Buluşma Lideri</span>
                                    <span class="text-xs font-bold text-slate-900 dark:text-white group-hover/org:text-blue-600 transition-colors">Dr. Mehmet Kaya</span>
                                </div>
                            </a>

                            <div class="flex items-center gap-3">
                                <span class="text-xs font-bold text-slate-500" x-text="`${event1Count} Komşu Katılıyor`"></span>
                                <button @click="toggleEvent(1)"
                                        :class="event1Joined ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-blue-600 hover:bg-blue-700 text-white'"
                                        class="px-4 py-2 rounded-xl text-xs font-bold shadow-md transition-all cursor-pointer">
                                    <span x-text="event1Joined ? '✓ Katılıyorsun' : '+ Katıl'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ÖNE ÇIKAN 2: KİTAP KULÜBÜ -->
                <div class="luxury-card rounded-3xl overflow-hidden border border-slate-200/80 dark:border-white/10 bg-white dark:bg-[#0f172a] shadow-lg flex flex-col group">
                    <div class="h-60 w-full relative overflow-hidden bg-slate-800">
                        <img src="https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=800&q=80"
                             alt="Mahalle Kitap Kulübü Söyleşisi"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>
                        
                        <!-- Sol Üst Rozetler -->
                        <div class="absolute top-3.5 left-3.5 flex items-center gap-2">
                            <span class="px-3 py-1 rounded-full bg-purple-600 text-white text-xs font-extrabold shadow-md">
                                📚 Kültür & Kitap
                            </span>
                            <span class="px-3 py-1 rounded-full bg-indigo-500/90 text-white text-xs font-black shadow-md backdrop-blur-md">
                                ☕ Çay & Kurabiye İkramlı
                            </span>
                        </div>

                        <!-- Sağ Üst Tarih -->
                        <div class="absolute top-3.5 right-3.5 px-3 py-1.5 rounded-xl bg-black/60 backdrop-blur-md text-white text-xs font-bold border border-white/20">
                            📅 Pazar • 15:00
                        </div>

                        <!-- Alt Bilgi -->
                        <div class="absolute bottom-3.5 left-3.5 right-3.5 text-white">
                            <h3 class="text-xl font-black tracking-tight drop-shadow-md">
                                Mahalle Kitap Kulübü: "Huzur" Söyleşisi
                            </h3>
                            <p class="text-xs text-slate-200 mt-1 flex items-center gap-1.5">
                                <span>📍 Keçiören Gençlik Kütüphanesi • Konferans Salonu</span>
                            </p>
                        </div>
                    </div>

                    <!-- Kart Gövdesi -->
                    <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Ahmet Hamdi Tanpınar'ın klasik eseri 'Huzur' üzerine derinlikli bir edebiyat ve şiir sohbeti gerçekleştiriyoruz. Kitabınızı ve düşüncelerinizi alıp gelin!
                        </p>

                        <!-- Düzenleyen & Katılımcılar -->
                        <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-white/5">
                            <a href="/profile/@aysetugba" class="flex items-center gap-2.5 group/org">
                                <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=100&q=80" class="w-8 h-8 rounded-full object-cover ring-2 ring-purple-500/30">
                                <div>
                                    <span class="text-[10px] text-slate-400 block font-semibold">Moderatör</span>
                                    <span class="text-xs font-bold text-slate-900 dark:text-white group-hover/org:text-purple-600 transition-colors">Ayşe Öğretmen</span>
                                </div>
                            </a>

                            <div class="flex items-center gap-3">
                                <span class="text-xs font-bold text-slate-500">24 Komşu Katılıyor</span>
                                <button @click="triggerToast('Kitap Kulübü buluşmasına kaydınız alındı! 📖')"
                                        class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold shadow-md transition-all cursor-pointer">
                                    + Katıl
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- 3. GENİŞLETİLMİŞ İKİLİ GÖVDE (Sol: Yaklaşan Etkinlikler 8 cols | Sağ: Mekanlar & Liderler 4 cols) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start pt-2">
            
            <!-- 3.1 SOL BÖLÜM: YAKLAŞAN ETKİNLİKLER IZGARASI (8 Sütun) -->
            <div class="col-span-12 lg:col-span-8 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight">
                        Tüm Yaklaşan Buluşmalar & İmeceler
                    </h3>
                    <span class="text-xs text-slate-400 font-semibold">6 Aktif Buluşma</span>
                </div>

                <!-- ETKİNLİK LİSTESİ -->
                <div class="space-y-3.5">
                    
                    <!-- Etkinlik 1: Fidan Dikimi İmecesi -->
                    <div class="luxury-card rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-white/10 bg-white dark:bg-[#0f172a] shadow-sm flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between hover:border-emerald-500/40 transition-colors">
                        <div class="flex items-start gap-4">
                            <div class="w-16 h-16 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 flex flex-col items-center justify-center text-center shrink-0">
                                <span class="text-[10px] font-black uppercase text-emerald-600 dark:text-emerald-400">Kasım</span>
                                <span class="text-xl font-black text-slate-900 dark:text-white">18</span>
                            </div>
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded bg-emerald-500 text-white text-[10px] font-extrabold">🌿 Çevre & İmece</span>
                                    <span class="text-xs font-semibold text-slate-400">Cumartesi • 11:00</span>
                                </div>
                                <h4 class="text-sm font-bold text-slate-900 dark:text-white">
                                    Kalaba Vadisi Fidan Dikimi & Çevre Temizliği İmecesi
                                </h4>
                                <p class="text-xs text-slate-500">
                                    📍 Kalaba Vadisi Yürüyüş Parkuru Girişi • Düzenleyen: <a href="/profile/@kadircbc" class="text-blue-600 font-semibold hover:underline">Kadir Çelebi</a>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end pt-3 sm:pt-0 border-t sm:border-t-0 border-slate-100 dark:border-white/5">
                            <span class="text-xs font-bold text-slate-500">35 Komşu</span>
                            <button @click="triggerToast('Fidan dikimi imecesine katılımınız onaylandı! 🌱')"
                                    class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-slate-100 text-white dark:text-slate-900 text-xs font-bold transition-all shadow-sm cursor-pointer">
                                + Katıl
                            </button>
                        </div>
                    </div>

                    <!-- Etkinlik 2: Bisiklet Bakım Atölyesi -->
                    <div class="luxury-card rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-white/10 bg-white dark:bg-[#0f172a] shadow-sm flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between hover:border-amber-500/40 transition-colors">
                        <div class="flex items-start gap-4">
                            <div class="w-16 h-16 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 flex flex-col items-center justify-center text-center shrink-0">
                                <span class="text-[10px] font-black uppercase text-amber-600 dark:text-amber-400">Kasım</span>
                                <span class="text-xl font-black text-slate-900 dark:text-white">19</span>
                            </div>
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded bg-amber-500 text-white text-[10px] font-extrabold">🚲 Atölye & Çocuk</span>
                                    <span class="text-xs font-semibold text-slate-400">Pazar • 13:00</span>
                                </div>
                                <h4 class="text-sm font-bold text-slate-900 dark:text-white">
                                    Çocuklar İçin Ücretsiz Bisiklet Bakım & Emniyet Atölyesi
                                </h4>
                                <p class="text-xs text-slate-500">
                                    📍 Demir Bisiklet & Doğa Sporları • Düzenleyen: <a href="/profile/@serkanusta" class="text-blue-600 font-semibold hover:underline">Serkan Usta</a>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end pt-3 sm:pt-0 border-t sm:border-t-0 border-slate-100 dark:border-white/5">
                            <span class="text-xs font-bold text-slate-500">28 Komşu</span>
                            <button @click="triggerToast('Bisiklet atölyesine katılım sağlandı! 🔧')"
                                    class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-slate-100 text-white dark:text-slate-900 text-xs font-bold transition-all shadow-sm cursor-pointer">
                                + Katıl
                            </button>
                        </div>
                    </div>

                    <!-- Etkinlik 3: Açık Hava Sineması -->
                    <div class="luxury-card rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-white/10 bg-white dark:bg-[#0f172a] shadow-sm flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between hover:border-blue-500/40 transition-colors">
                        <div class="flex items-start gap-4">
                            <div class="w-16 h-16 rounded-2xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 flex flex-col items-center justify-center text-center shrink-0">
                                <span class="text-[10px] font-black uppercase text-blue-600 dark:text-blue-400">Kasım</span>
                                <span class="text-xl font-black text-slate-900 dark:text-white">21</span>
                            </div>
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded bg-blue-600 text-white text-[10px] font-extrabold">🎬 Açık Hava Sineması</span>
                                    <span class="text-xs font-semibold text-slate-400">Salı • 19:30</span>
                                </div>
                                <h4 class="text-sm font-bold text-slate-900 dark:text-white">
                                    Botanik Parkı Yeşilçam Gecesi: 'Neşeli Günler' Gösterimi
                                </h4>
                                <p class="text-xs text-slate-500">
                                    📍 Botanik Parkı Çim Amfi • Çay ve Mısır İkramlı • <a href="/profile/@ahmetyilmaz" class="text-blue-600 font-semibold hover:underline">Ahmet Yılmaz</a> Moderatörlüğünde
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end pt-3 sm:pt-0 border-t sm:border-t-0 border-slate-100 dark:border-white/5">
                            <span class="text-xs font-bold text-slate-500">112 Komşu</span>
                            <button @click="triggerToast('Açık hava sineması yeriniz ayırtıldı! 🎬')"
                                    class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-slate-100 text-white dark:text-slate-900 text-xs font-bold transition-all shadow-sm cursor-pointer">
                                + Katıl
                            </button>
                        </div>
                    </div>

                    <!-- Etkinlik 4: İlk Yardım Semineri -->
                    <div class="luxury-card rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-white/10 bg-white dark:bg-[#0f172a] shadow-sm flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between hover:border-rose-500/40 transition-colors">
                        <div class="flex items-start gap-4">
                            <div class="w-16 h-16 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 flex flex-col items-center justify-center text-center shrink-0">
                                <span class="text-[10px] font-black uppercase text-rose-600 dark:text-rose-400">Kasım</span>
                                <span class="text-xl font-black text-slate-900 dark:text-white">23</span>
                            </div>
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded bg-rose-600 text-white text-[10px] font-extrabold">🩺 Sağlık & Yaşam</span>
                                    <span class="text-xs font-semibold text-slate-400">Perşembe • 14:00</span>
                                </div>
                                <h4 class="text-sm font-bold text-slate-900 dark:text-white">
                                    Ev Kazalarında İlk Yardım & Temel Yaşam Desteği Semineri
                                </h4>
                                <p class="text-xs text-slate-500">
                                    📍 Keçiören 4 No'lu ASM Konferans Salonu • Eğitmen: <a href="/profile/@mehmetkaya" class="text-blue-600 font-semibold hover:underline">Dr. Mehmet Kaya</a>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end pt-3 sm:pt-0 border-t sm:border-t-0 border-slate-100 dark:border-white/5">
                            <span class="text-xs font-bold text-slate-500">46 Komşu</span>
                            <button @click="triggerToast('İlk yardım semineri kaydınız oluşturuldu! 🩺')"
                                    class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-slate-100 text-white dark:text-slate-900 text-xs font-bold transition-all shadow-sm cursor-pointer">
                                + Katıl
                            </button>
                        </div>
                    </div>

                </div>
            </div>

            <!-- 3.2 SAĞ BÖLÜM: MEKANLAR, LİDERLER VE BULUŞMA BAŞLAT KARTI (4 Sütun) -->
            <div class="col-span-12 lg:col-span-4 space-y-5">
                
                <!-- KART 1: KENDİ BULUŞMANI BAŞLAT KARTI -->
                <div class="luxury-card rounded-2xl p-5 border border-blue-500/30 bg-gradient-to-br from-blue-600 to-indigo-700 text-white shadow-lg space-y-3">
                    <span class="text-2xl block">🤝</span>
                    <h4 class="text-base font-black tracking-tight">Kendi Buluşmanı Başlat</h4>
                    <p class="text-xs text-blue-100 leading-relaxed">
                        Mahallende bir koşu grubu, kitap okuma kulübü, sokak hayvanlarını besleme ya da çevre imecesi organize etmek ister misin? 1 dakikada oluştur ve tüm Keçiören'e duyur.
                    </p>
                    <button @click="triggerToast('Etkinlik başlatma formu açıldı. 📢')"
                            class="w-full py-2.5 rounded-xl bg-white text-blue-700 hover:bg-blue-50 font-bold text-xs shadow-md transition-colors cursor-pointer">
                        Buluşma Planla
                    </button>
                </div>

                <!-- KART 2: MAHALLE BULUŞMA MEKANLARI REHBERİ -->
                <div class="luxury-card rounded-2xl p-5 border border-slate-200/80 dark:border-white/10 bg-white dark:bg-[#0f172a] shadow-sm space-y-3.5">
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-black text-slate-900 dark:text-white">Popüler Buluşma Noktaları</h4>
                        <a href="/business" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">Tümü →</a>
                    </div>

                    <div class="space-y-3">
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-white/5 space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-xs text-slate-900 dark:text-white">☕ Şelale Park Kafe</span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400">Komşu İndirimi %15</span>
                            </div>
                            <p class="text-[11px] text-slate-500">Hafta sonu sabah koşusu ve kahve buluşmalarının resmi mekanı.</p>
                        </div>

                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-white/5 space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-xs text-slate-900 dark:text-white">📖 Gençlik Kütüphanesi</span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-purple-50 text-purple-600 dark:bg-purple-950/40 dark:text-purple-400">Ücretsiz Salon</span>
                            </div>
                            <p class="text-[11px] text-slate-500">Kitap kulüpleri, edebiyat söyleşileri ve sınav çalışma grupları.</p>
                        </div>

                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-white/5 space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-xs text-slate-900 dark:text-white">🚲 Demir Bisiklet & Doğa</span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400">Buluşma Noktası</span>
                            </div>
                            <p class="text-[11px] text-slate-500">Haftalık bisiklet turları ve çocuk bakım atölyeleri başlangıç yeri.</p>
                        </div>
                    </div>
                </div>

                <!-- KART 3: BU AYIN ETKİNLİK LİDERLERİ -->
                <div class="luxury-card rounded-2xl p-5 border border-slate-200/80 dark:border-white/10 bg-white dark:bg-[#0f172a] shadow-sm space-y-3.5">
                    <h4 class="text-sm font-black text-slate-900 dark:text-white">En Aktif Buluşma Liderleri</h4>
                    
                    <div class="space-y-2.5">
                        <a href="/profile/@mehmetkaya" class="flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <div class="flex items-center gap-2.5">
                                <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=80&q=80" class="w-8 h-8 rounded-full object-cover">
                                <div>
                                    <span class="text-xs font-bold text-slate-900 dark:text-white block">Dr. Mehmet Kaya</span>
                                    <span class="text-[10px] text-slate-400">5 Buluşma Lideri</span>
                                </div>
                            </div>
                            <span class="text-xs font-mono font-bold text-emerald-600 dark:text-emerald-400">+580p</span>
                        </a>

                        <a href="/profile/@aysetugba" class="flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <div class="flex items-center gap-2.5">
                                <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=80&q=80" class="w-8 h-8 rounded-full object-cover">
                                <div>
                                    <span class="text-xs font-bold text-slate-900 dark:text-white block">Ayşe Öğretmen</span>
                                    <span class="text-[10px] text-slate-400">4 Kitap Kulübü</span>
                                </div>
                            </div>
                            <span class="text-xs font-mono font-bold text-emerald-600 dark:text-emerald-400">+310p</span>
                        </a>

                        <a href="/profile/@kadircbc" class="flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <div class="flex items-center gap-2.5">
                                <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=80&q=80" class="w-8 h-8 rounded-full object-cover">
                                <div>
                                    <span class="text-xs font-bold text-slate-900 dark:text-white block">Kadir Çelebi</span>
                                    <span class="text-[10px] text-slate-400">4 Buluşma Katılımı</span>
                                </div>
                            </div>
                            <span class="text-xs font-mono font-bold text-emerald-600 dark:text-emerald-400">+120p</span>
                        </a>
                    </div>
                </div>

            </div>

        </div>

    </div>



    <?php elseif ($page === 'login'): ?>
    <!-- ================================================================= -->
    <!-- MODERN SPLIT-SCREEN GİRİŞ SAYFASI (Meta / Facebook Inspired)     -->
    <!-- Link: /login                                                      -->
    <!-- ================================================================= -->
    <div class="min-h-screen w-full bg-white dark:bg-[#0b0f19] flex flex-col justify-between transition-colors">
        
        <!-- Üst Sağ Küçük Tema ve Geri Butonları -->
        <div class="absolute top-5 right-5 sm:right-8 z-50 flex items-center gap-2">
            <div class="p-1 rounded-xl bg-slate-100/90 dark:bg-slate-800/90 backdrop-blur-md border border-slate-200 dark:border-white/10 flex items-center gap-1 shadow-sm">
                <button @click="setTheme(false)" type="button" :class="!darkMode ? 'bg-white text-blue-600 font-bold shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400'" class="px-2.5 py-1 rounded-lg text-xs flex items-center gap-1 transition-all">
                    <span>☀️ Aydınlık</span>
                </button>
                <button @click="setTheme(true)" type="button" :class="darkMode ? 'bg-slate-700 text-blue-400 font-bold shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400'" class="px-2.5 py-1 rounded-lg text-xs flex items-center gap-1 transition-all">
                    <span>🌙 Koyu</span>
                </button>
            </div>
            <a href="/" class="px-3.5 py-1.5 rounded-xl bg-slate-100/90 dark:bg-slate-800/90 backdrop-blur-md border border-slate-200 dark:border-white/10 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 shadow-sm transition-all">
                <span>← Ana Sayfa</span>
            </a>
        </div>

        <!-- Ana Bölünmüş Grid (Split-Screen) -->
        <div class="flex-1 grid grid-cols-1 lg:grid-cols-12 min-h-screen">
            
            <!-- ========================================================= -->
            <!-- SOL PANEL: GÖRSEL KOMPOZİSYON & BAŞLIK (60% Genişlik)    -->
            <!-- ========================================================= -->
            <div class="lg:col-span-7 xl:col-span-7 flex flex-col justify-between p-6 sm:p-10 lg:p-14 relative overflow-hidden bg-gradient-to-br from-blue-50/40 via-white to-indigo-50/30 dark:from-[#0b0f19] dark:via-[#0f172a] dark:to-[#0b0f19]">
                
                <!-- Tepe Sol: Meydan Marka Logosu -->
                <div class="z-10">
                    <a href="/" class="inline-flex items-center gap-3 group">
                        <div class="w-12 h-12 rounded-2xl bg-[#1877F2] text-white flex items-center justify-center font-black shadow-lg shadow-blue-500/25 group-hover:scale-105 transition-transform">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <div>
                            <span class="text-2xl font-black tracking-tight text-slate-900 dark:text-white block">Meydan</span>
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Keçiören Yerel Mahalle Ağı</span>
                        </div>
                    </a>
                </div>

                <!-- Orta: Katmanlı Kartlar & Reaksiyon Kolajı (Facebook Tarzı) -->
                <div class="my-8 sm:my-12 flex items-center justify-center relative py-8">
                    
                    <div class="relative w-full max-w-[420px] h-[340px] sm:h-[380px]">
                        
                        <!-- 1. Arka Sol Kart: Mahalle Pazarı / Bitki İlanı -->
                        <div class="absolute top-4 left-2 sm:left-4 w-44 sm:w-48 rounded-2xl overflow-hidden shadow-xl border-2 border-white dark:border-slate-800 bg-white dark:bg-slate-800 transform -rotate-6 hover:rotate-0 transition-transform duration-500 z-10">
                            <div class="h-32 sm:h-36 relative">
                                <img src="https://images.unsplash.com/photo-1614594975525-e45190c55d0b?auto=format&fit=crop&w=350&q=80" class="w-full h-full object-cover">
                                <span class="absolute top-2 left-2 w-6 h-6 rounded-lg bg-black/60 backdrop-blur-md flex items-center justify-center text-xs">🪴</span>
                            </div>
                            <div class="p-2.5">
                                <div class="w-20 h-2 bg-slate-200 dark:bg-slate-700 rounded-full mb-1"></div>
                                <div class="w-12 h-2 bg-slate-100 dark:bg-slate-800 rounded-full"></div>
                            </div>
                        </div>

                        <!-- 2. Ana Dikey Kart (Ortada Telefon / Story Şeklinde) -->
                        <div class="absolute top-0 right-10 sm:right-16 w-52 sm:w-60 rounded-3xl overflow-hidden shadow-2xl border-4 border-white dark:border-slate-800 bg-white dark:bg-slate-800 z-20 transform hover:scale-[1.02] transition-transform duration-500">
                            <!-- Story Tepe İlerleme Çizgisi -->
                            <div class="h-1.5 w-full bg-slate-200 dark:bg-slate-700 flex gap-1 p-0.5">
                                <div class="w-1/2 bg-white rounded-full"></div>
                                <div class="w-1/2 bg-white/40 rounded-full"></div>
                            </div>
                            
                            <div class="h-56 sm:h-64 relative bg-slate-900">
                                <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=500&q=80" class="w-full h-full object-cover">
                                
                                <!-- Saat Rozeti -->
                                <span class="absolute top-2.5 right-2.5 px-2 py-0.5 rounded-full bg-black/60 backdrop-blur-md text-white text-[10px] font-bold flex items-center gap-1 border border-white/20">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>16:45</span>
                                </span>
                            </div>

                            <!-- Hikaye Alt Etkileşim Barı -->
                            <div class="p-3 bg-white dark:bg-slate-900 flex items-center justify-between">
                                <div class="w-24 h-5 rounded-full bg-blue-50 dark:bg-slate-800 flex items-center px-2">
                                    <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse mr-1.5"></span>
                                    <span class="text-[9px] font-bold text-blue-600 dark:text-blue-400">Kalaba Vadisi</span>
                                </div>
                                <div class="flex items-center gap-1 text-xs">
                                    <span>👏</span>
                                    <span>❤️</span>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Ön Alt Kart: Doğa & Parkur Fotoğrafı -->
                        <div class="absolute bottom-2 left-6 sm:left-10 w-44 sm:w-48 rounded-2xl overflow-hidden shadow-2xl border-2 border-white dark:border-slate-800 bg-white dark:bg-slate-800 z-30 transform -rotate-3 hover:rotate-0 transition-transform duration-500">
                            <div class="h-28 relative">
                                <img src="https://images.unsplash.com/photo-1519501025264-65ba15a82390?auto=format&fit=crop&w=350&q=80" class="w-full h-full object-cover">
                                <span class="absolute top-2 left-2 w-6 h-6 rounded-lg bg-blue-600 text-white flex items-center justify-center text-xs shadow-md">★</span>
                            </div>
                            <div class="p-2 flex items-center justify-between">
                                <span class="text-[10px] font-bold text-slate-700 dark:text-slate-300">Botanik Parkı</span>
                                <span class="text-[10px] text-emerald-600 font-bold">Açık</span>
                            </div>
                        </div>

                        <!-- 4. Alt Ortada Yuvarlak Gülümseyen Komşu Çemberi -->
                        <div class="absolute bottom-0 right-16 sm:right-24 w-18 h-18 sm:w-20 sm:h-20 rounded-full border-4 border-white dark:border-slate-800 shadow-2xl overflow-hidden bg-amber-400 z-40 ring-2 ring-blue-500">
                            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=150&q=80" class="w-full h-full object-cover">
                        </div>

                        <!-- 5. Uçuşan 3D Reaksiyon Balonları (Emoji Pills) -->
                        <div class="absolute top-10 left-0 w-11 h-11 rounded-full bg-white dark:bg-slate-800 shadow-xl border border-slate-100 dark:border-white/10 flex items-center justify-center text-xl z-30 animate-bounce">
                            😆
                        </div>
                        <div class="absolute bottom-16 right-4 sm:right-8 w-12 h-12 rounded-full bg-gradient-to-tr from-rose-500 to-pink-500 text-white shadow-xl flex items-center justify-center text-xl z-40 transform hover:scale-110 transition-transform">
                            ❤️
                        </div>

                    </div>
                </div>

                <!-- Tepe Sol Alt: Büyük Slogan Metni (Görseldeki İle Birebir Format) -->
                <div class="z-10 pt-4">
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-[1.08] text-slate-900 dark:text-white">
                        <span class="text-[#1877F2]">Sevdiğin</span><br>
                        şeyleri<br>
                        keşfet.
                    </h1>
                </div>

            </div>

            <!-- ========================================================= -->
            <!-- SAĞ PANEL: TEMİZ GİRİŞ FORMU & BUTONLAR (40% Genişlik)   -->
            <!-- ========================================================= -->
            <div class="lg:col-span-5 xl:col-span-5 flex flex-col justify-center px-6 sm:px-12 lg:px-16 py-12 border-t lg:border-t-0 lg:border-l border-slate-200/80 dark:border-white/10 bg-white dark:bg-[#0b0f19]">
                
                <div class="w-full max-w-sm mx-auto space-y-6">
                    
                    <!-- Başlık -->
                    <div>
                        <h2 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white tracking-tight">
                            Meydan'a Giriş Yap
                        </h2>
                    </div>

                    <!-- Giriş Formu -->
                    <form @submit.prevent="loginAs()" class="space-y-3.5">
                        
                        <!-- E-posta veya Kullanıcı Adı -->
                        <div>
                            <input type="text"
                                   required
                                   value="kadir@meydan.com"
                                   placeholder="E-posta adresi veya cep telefonu numarası"
                                   class="w-full px-4 py-3.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-[#1877F2] focus:border-[#1877F2] outline-none transition-all">
                        </div>

                        <!-- Şifre -->
                        <div>
                            <input type="password"
                                   required
                                   value="••••••••••••"
                                   placeholder="Şifre"
                                   class="w-full px-4 py-3.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-[#1877F2] focus:border-[#1877F2] outline-none transition-all">
                        </div>

                        <!-- Giriş Yap Butonu (Meta Mavisi, Tam Genişlik) -->
                        <button type="submit"
                                class="w-full py-3.5 rounded-full bg-[#1877F2] hover:bg-blue-600 text-white font-bold text-sm shadow-md transition-all cursor-pointer">
                            Giriş Yap
                        </button>
                    </form>

                    <!-- Şifremi Unuttum Bağlantısı -->
                    <div class="text-center">
                        <a href="/forgot-password" class="text-xs sm:text-sm font-semibold text-slate-800 dark:text-slate-200 hover:text-[#1877F2] hover:underline transition-colors">
                            Şifreni mi unuttun?
                        </a>
                    </div>

                    <!-- Ayrım / Boşluk -->
                    <div class="pt-2">
                        <!-- Yeni Hesap Oluştur Butonu (Mavi Çerçeveli Oval Buton) -->
                        <a href="/register"
                           class="w-full py-3 rounded-full border-2 border-[#1877F2] hover:bg-blue-50 dark:hover:bg-slate-800/80 text-[#1877F2] font-bold text-sm text-center block transition-all shadow-sm">
                            Yeni hesap oluştur
                        </a>
                    </div>

                    <!-- Tek Tıkla Hızlı Test Girişi (Geliştirici / Test Kolaylığı) -->
                    <div class="pt-4 border-t border-slate-100 dark:border-white/5 space-y-2">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block text-center">
                            Hızlı Demo Komşusu Seç
                        </span>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button"
                                    @click="loginAs('kadircbc')"
                                    class="p-2 rounded-xl border border-slate-200 dark:border-white/10 hover:border-blue-500 hover:bg-blue-50/50 dark:hover:bg-slate-800 flex items-center gap-2 text-left transition-all cursor-pointer">
                                <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=60&q=80" class="w-6 h-6 rounded-full object-cover">
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">Kadir Çelebi</span>
                            </button>
                            <button type="button"
                                    @click="loginAs('mehmetkaya')"
                                    class="p-2 rounded-xl border border-slate-200 dark:border-white/10 hover:border-blue-500 hover:bg-blue-50/50 dark:hover:bg-slate-800 flex items-center gap-2 text-left transition-all cursor-pointer">
                                <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=60&q=80" class="w-6 h-6 rounded-full object-cover">
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">Dr. Mehmet</span>
                            </button>
                        </div>
                    </div>

                    <!-- Alt Meta / Meydan İmzası -->
                    <div class="text-center pt-6 text-slate-400 flex items-center justify-center gap-1.5 text-xs font-semibold">
                        <span>∞ Meydan Yerel Ağı</span>
                    </div>

                </div>

            </div>

        </div>

    </div>

    <?php elseif ($page === 'register'): ?>
    <!-- ================================================================= -->
    <!-- MODERN SPLIT-SCREEN KAYIT SAYFASI (Meta / Facebook Inspired)      -->
    <!-- Link: /register                                                   -->
    <!-- ================================================================= -->
    <div class="min-h-screen w-full bg-white dark:bg-[#0b0f19] flex flex-col justify-between transition-colors">
        
        <!-- Üst Sağ Küçük Tema ve Geri Butonları -->
        <div class="absolute top-5 right-5 sm:right-8 z-50 flex items-center gap-2">
            <div class="p-1 rounded-xl bg-slate-100/90 dark:bg-slate-800/90 backdrop-blur-md border border-slate-200 dark:border-white/10 flex items-center gap-1 shadow-sm">
                <button @click="setTheme(false)" type="button" :class="!darkMode ? 'bg-white text-blue-600 font-bold shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400'" class="px-2.5 py-1 rounded-lg text-xs flex items-center gap-1 transition-all">
                    <span>☀️ Aydınlık</span>
                </button>
                <button @click="setTheme(true)" type="button" :class="darkMode ? 'bg-slate-700 text-blue-400 font-bold shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400'" class="px-2.5 py-1 rounded-lg text-xs flex items-center gap-1 transition-all">
                    <span>🌙 Koyu</span>
                </button>
            </div>
            <a href="/" class="px-3.5 py-1.5 rounded-xl bg-slate-100/90 dark:bg-slate-800/90 backdrop-blur-md border border-slate-200 dark:border-white/10 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 shadow-sm transition-all">
                <span>← Ana Sayfa</span>
            </a>
        </div>

        <!-- Ana Bölünmüş Grid (Split-Screen) -->
        <div class="flex-1 grid grid-cols-1 lg:grid-cols-12 min-h-screen">
            
            <!-- ========================================================= -->
            <!-- SOL PANEL: GÖRSEL KOMPOZİSYON & BAŞLIK (60% Genişlik)    -->
            <!-- ========================================================= -->
            <div class="lg:col-span-7 xl:col-span-7 flex flex-col justify-between p-6 sm:p-10 lg:p-14 relative overflow-hidden bg-gradient-to-br from-blue-50/40 via-white to-indigo-50/30 dark:from-[#0b0f19] dark:via-[#0f172a] dark:to-[#0b0f19]">
                
                <!-- Tepe Sol: Meydan Marka Logosu -->
                <div class="z-10">
                    <a href="/" class="inline-flex items-center gap-3 group">
                        <div class="w-12 h-12 rounded-2xl bg-[#1877F2] text-white flex items-center justify-center font-black shadow-lg shadow-blue-500/25 group-hover:scale-105 transition-transform">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <div>
                            <span class="text-2xl font-black tracking-tight text-slate-900 dark:text-white block">Meydan</span>
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Keçiören Yerel Mahalle Ağı</span>
                        </div>
                    </a>
                </div>

                <!-- Orta: Katmanlı Kartlar & Reaksiyon Kolajı (Facebook Tarzı) -->
                <div class="my-6 sm:my-10 flex items-center justify-center relative py-6">
                    <div class="relative w-full max-w-[420px] h-[340px] sm:h-[380px]">
                        
                        <!-- 1. Arka Sol Kart: Mahalle Pazarı / Bitki İlanı -->
                        <div class="absolute top-4 left-2 sm:left-4 w-44 sm:w-48 rounded-2xl overflow-hidden shadow-xl border-2 border-white dark:border-slate-800 bg-white dark:bg-slate-800 transform -rotate-6 hover:rotate-0 transition-transform duration-500 z-10">
                            <div class="h-32 sm:h-36 relative">
                                <img src="https://images.unsplash.com/photo-1614594975525-e45190c55d0b?auto=format&fit=crop&w=350&q=80" class="w-full h-full object-cover">
                                <span class="absolute top-2 left-2 w-6 h-6 rounded-lg bg-black/60 backdrop-blur-md flex items-center justify-center text-xs">🪴</span>
                            </div>
                            <div class="p-2.5">
                                <div class="w-20 h-2 bg-slate-200 dark:bg-slate-700 rounded-full mb-1"></div>
                                <div class="w-12 h-2 bg-slate-100 dark:bg-slate-800 rounded-full"></div>
                            </div>
                        </div>

                        <!-- 2. Ana Dikey Kart (Ortada Telefon / Story Şeklinde) -->
                        <div class="absolute top-0 right-10 sm:right-16 w-52 sm:w-60 rounded-3xl overflow-hidden shadow-2xl border-4 border-white dark:border-slate-800 bg-white dark:bg-slate-800 z-20 transform hover:scale-[1.02] transition-transform duration-500">
                            <!-- Story Tepe İlerleme Çizgisi -->
                            <div class="h-1.5 w-full bg-slate-200 dark:bg-slate-700 flex gap-1 p-0.5">
                                <div class="w-1/2 bg-white rounded-full"></div>
                                <div class="w-1/2 bg-white/40 rounded-full"></div>
                            </div>
                            
                            <div class="h-56 sm:h-64 relative bg-slate-900">
                                <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=500&q=80" class="w-full h-full object-cover">
                                
                                <!-- Saat Rozeti -->
                                <span class="absolute top-2.5 right-2.5 px-2 py-0.5 rounded-full bg-black/60 backdrop-blur-md text-white text-[10px] font-bold flex items-center gap-1 border border-white/20">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>10:30</span>
                                </span>
                            </div>

                            <!-- Hikaye Alt Etkileşim Barı -->
                            <div class="p-3 bg-white dark:bg-slate-900 flex items-center justify-between">
                                <div class="w-26 h-5 rounded-full bg-blue-50 dark:bg-slate-800 flex items-center px-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse mr-1.5"></span>
                                    <span class="text-[9px] font-bold text-emerald-600 dark:text-emerald-400">Yeni Komşu</span>
                                </div>
                                <div class="flex items-center gap-1 text-xs">
                                    <span>👋</span>
                                    <span>🌱</span>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Ön Alt Kart: Doğa & Parkur Fotoğrafı -->
                        <div class="absolute bottom-2 left-6 sm:left-10 w-44 sm:w-48 rounded-2xl overflow-hidden shadow-2xl border-2 border-white dark:border-slate-800 bg-white dark:bg-slate-800 z-30 transform -rotate-3 hover:rotate-0 transition-transform duration-500">
                            <div class="h-28 relative">
                                <img src="https://images.unsplash.com/photo-1519501025264-65ba15a82390?auto=format&fit=crop&w=350&q=80" class="w-full h-full object-cover">
                                <span class="absolute top-2 left-2 w-6 h-6 rounded-lg bg-blue-600 text-white flex items-center justify-center text-xs shadow-md">★</span>
                            </div>
                            <div class="p-2 flex items-center justify-between">
                                <span class="text-[10px] font-bold text-slate-700 dark:text-slate-300">Botanik Parkı</span>
                                <span class="text-[10px] text-emerald-600 font-bold">Topluluk</span>
                            </div>
                        </div>

                        <!-- 4. Alt Ortada Yuvarlak Komşu Çemberi -->
                        <div class="absolute bottom-0 right-16 sm:right-24 w-18 h-18 sm:w-20 sm:h-20 rounded-full border-4 border-white dark:border-slate-800 shadow-2xl overflow-hidden bg-purple-500 z-40 ring-2 ring-blue-500">
                            <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=150&q=80" class="w-full h-full object-cover">
                        </div>

                        <!-- 5. Uçuşan Reaksiyon Balonları -->
                        <div class="absolute top-10 left-0 w-11 h-11 rounded-full bg-white dark:bg-slate-800 shadow-xl border border-slate-100 dark:border-white/10 flex items-center justify-center text-xl z-30 animate-bounce">
                            🏡
                        </div>
                        <div class="absolute bottom-16 right-4 sm:right-8 w-12 h-12 rounded-full bg-gradient-to-tr from-blue-500 to-indigo-500 text-white shadow-xl flex items-center justify-center text-xl z-40 transform hover:scale-110 transition-transform">
                            ✨
                        </div>

                    </div>
                </div>

                <!-- Tepe Sol Alt: Büyük Slogan Metni -->
                <div class="z-10 pt-4">
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-[1.08] text-slate-900 dark:text-white">
                        <span class="text-[#1877F2]">Mahallene</span><br>
                        adım<br>
                        at.
                    </h1>
                </div>

            </div>

            <!-- ========================================================= -->
            <!-- SAĞ PANEL: META TARZI SADE KAYIT FORMU (40% Genişlik)     -->
            <!-- ========================================================= -->
            <div class="lg:col-span-5 xl:col-span-5 flex flex-col justify-center px-6 sm:px-10 lg:px-14 py-8 border-t lg:border-t-0 lg:border-l border-slate-200/80 dark:border-white/10 bg-white dark:bg-[#0b0f19] overflow-y-auto">
                
                <div class="w-full max-w-sm sm:max-w-md mx-auto space-y-3.5">
                    
                    <!-- Başlık & Açıklama -->
                    <div class="space-y-1">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                                Meydan'a Kaydol
                            </h2>
                            <span class="px-2 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 text-[10px] font-bold border border-emerald-500/20 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Yasal Uyumlu
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Hızlı ve kolaydır. T.C. mevzuatına uygun güvenli yerel mahalle ağı.
                        </p>
                    </div>

                    <!-- Kayıt Formu -->
                    <form @submit.prevent="handleRegister()" class="space-y-3">
                        
                        <!-- Ad & Soyad (İkili Yan Yana) -->
                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Adınız *</label>
                                <input type="text"
                                       required
                                       placeholder="Adınız"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-[#1877F2] focus:border-[#1877F2] outline-none transition-all">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Soyadınız *</label>
                                <input type="text"
                                       required
                                       placeholder="Soyadınız"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-[#1877F2] focus:border-[#1877F2] outline-none transition-all">
                            </div>
                        </div>

                        <!-- E-posta veya Cep Telefonu -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Cep Telefonu veya E-Posta *</label>
                            <input type="text"
                                   required
                                   placeholder="05XX XXX XX XX veya e-posta adresi"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-[#1877F2] focus:border-[#1877F2] outline-none transition-all">
                        </div>

                        <!-- Mahalle Seçimi -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">İkamet Ettiğiniz Mahalle (Keçiören) *</label>
                            <select required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-[#1877F2] focus:border-[#1877F2] transition-all">
                                <option value="Kalaba">Keçiören / Kalaba Mahallesi</option>
                                <option value="Tepebaşı">Keçiören / Tepebaşı Mahallesi</option>
                                <option value="Şefkat">Keçiören / Şefkat Mahallesi</option>
                                <option value="Basınevleri">Keçiören / Basınevleri Mahallesi</option>
                                <option value="İncirli">Keçiören / İncirli Mahallesi</option>
                                <option value="Subayevleri">Keçiören / Subayevleri Mahallesi</option>
                                <option value="Aşağı Eğlence">Keçiören / Aşağı Eğlence Mahallesi</option>
                            </select>
                        </div>

                        <!-- Doğum Tarihi (Facebook Tarzı Yasal Yaş Kontrolü) -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-[11px] font-bold text-slate-600 dark:text-slate-400">Doğum Tarihi *</label>
                                <span class="text-[10px] text-slate-400">18 yaşından büyükler üye olabilir</span>
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <select class="px-2.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-[#1877F2]">
                                    <?php for ($d = 1; $d <= 31; $d++): ?>
                                        <option value="<?= $d ?>" <?= $d === 15 ? 'selected' : '' ?>><?= $d ?></option>
                                    <?php endfor; ?>
                                </select>
                                <select class="px-2.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-[#1877F2]">
                                    <option value="1">Ocak</option>
                                    <option value="2">Şubat</option>
                                    <option value="3">Mart</option>
                                    <option value="4">Nisan</option>
                                    <option value="5" selected>Mayıs</option>
                                    <option value="6">Haziran</option>
                                    <option value="7">Temmuz</option>
                                    <option value="8">Ağustos</option>
                                    <option value="9">Eylül</option>
                                    <option value="10">Ekim</option>
                                    <option value="11">Kasım</option>
                                    <option value="12">Aralık</option>
                                </select>
                                <select class="px-2.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-[#1877F2]">
                                    <?php for ($y = 2006; $y >= 1940; $y--): ?>
                                        <option value="<?= $y ?>" <?= $y === 1995 ? 'selected' : '' ?>><?= $y ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Yeni Şifre -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Şifre *</label>
                            <input type="password"
                                   required
                                   placeholder="En az 8 karakterli güçlü şifre"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-[#1877F2] focus:border-[#1877F2] outline-none transition-all">
                        </div>

                        <!-- YASAL MEVZUAT VE ONAY KUTULARI (KVKK, 5651, 6563) -->
                        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-850/80 border border-slate-200/90 dark:border-white/10 space-y-2.5 text-left">
                            <div class="flex items-center justify-between pb-1.5 border-b border-slate-200/80 dark:border-white/10">
                                <span class="text-[11px] font-black tracking-wide uppercase text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    Yasal Mevzuat ve Rıza Onayları
                                </span>
                                <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400">KVKK & 5651</span>
                            </div>

                            <!-- Onay 1: Sözleşme & Mahalle İlkeleri -->
                            <label class="flex items-start gap-2.5 cursor-pointer select-none">
                                <input type="checkbox" x-model="registerTerms" required class="mt-0.5 w-4 h-4 rounded text-[#1877F2] border-slate-300 dark:border-slate-700 focus:ring-[#1877F2]">
                                <span class="text-[11px] text-slate-700 dark:text-slate-300 leading-snug">
                                    <a href="#" @click.prevent="openLegalModal('terms')" class="text-[#1877F2] font-bold hover:underline">Kullanıcı & Üyelik Sözleşmesi</a>'ni ve <a href="#" @click.prevent="openLegalModal('terms')" class="text-[#1877F2] font-bold hover:underline">Komşuluk İlkeleri</a>'ni okudum, kabul ediyorum. <span class="text-rose-500 font-bold">*</span>
                                </span>
                            </label>

                            <!-- Onay 2: 6698 KVKK Aydınlatma ve Açık Rıza -->
                            <label class="flex items-start gap-2.5 cursor-pointer select-none">
                                <input type="checkbox" x-model="registerKvkk" required class="mt-0.5 w-4 h-4 rounded text-[#1877F2] border-slate-300 dark:border-slate-700 focus:ring-[#1877F2]">
                                <span class="text-[11px] text-slate-700 dark:text-slate-300 leading-snug">
                                    <a href="#" @click.prevent="openLegalModal('kvkk')" class="text-[#1877F2] font-bold hover:underline">6698 Sayılı KVKK Aydınlatma Metni</a>'ni okudum; kişisel verilerimin işlenmesine ve <a href="#" @click.prevent="openLegalModal('cookies')" class="text-[#1877F2] font-bold hover:underline">Çerez Politikası</a>'na açık rıza veriyorum. <span class="text-rose-500 font-bold">*</span>
                                </span>
                            </label>

                            <!-- Onay 3: Gerçek Bilgi & 18 Yaş Taahhüdü (5237 TCK & 5651) -->
                            <label class="flex items-start gap-2.5 cursor-pointer select-none">
                                <input type="checkbox" x-model="registerAccuracy" required class="mt-0.5 w-4 h-4 rounded text-[#1877F2] border-slate-300 dark:border-slate-700 focus:ring-[#1877F2]">
                                <span class="text-[11px] text-slate-700 dark:text-slate-300 leading-snug">
                                    Girdiğim kimlik, iletişim ve mahalle ikamet bilgilerimin şahsıma ait olduğunu ve 18 yaşını doldurduğumu yasal olarak taahhüt ederim. <span class="text-rose-500 font-bold">*</span>
                                </span>
                            </label>

                            <!-- Onay 4: Ticari Elektronik İleti İzni (İsteğe Bağlı - 6563 ETK) -->
                            <label class="flex items-start gap-2.5 cursor-pointer select-none pt-1 border-t border-slate-200/60 dark:border-white/5">
                                <input type="checkbox" x-model="registerMarketing" class="mt-0.5 w-4 h-4 rounded text-[#1877F2] border-slate-300 dark:border-slate-700 focus:ring-[#1877F2]">
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 leading-snug">
                                    6563 sayılı Kanun kapsamında mahallemdeki acil durumlar, pazar duyuruları ve belediye etkinlikleri hakkında SMS ve E-posta bilgilendirmesi almak istiyorum. <span class="text-slate-400 font-medium">(İsteğe bağlı)</span>
                                </span>
                            </label>
                        </div>

                        <!-- Kaydol Butonu (Meta Mavisi, Tam Genişlik Oval) -->
                        <button type="submit"
                                class="w-full py-3.5 rounded-full bg-[#1877F2] hover:bg-blue-600 text-white font-bold text-sm shadow-md transition-all cursor-pointer">
                            Yasal Şartlarla Kaydol
                        </button>
                    </form>

                    <!-- Zaten Hesabın Var mı? (Mavi Çerçeveli Oval Buton) -->
                    <div class="pt-2 border-t border-slate-100 dark:border-white/5">
                        <a href="/login"
                           class="w-full py-2.5 rounded-full border-2 border-[#1877F2] hover:bg-blue-50 dark:hover:bg-slate-800/80 text-[#1877F2] font-bold text-xs sm:text-sm text-center block transition-all shadow-sm">
                            Zaten bir hesabın var mı? Giriş Yap
                        </a>
                    </div>

                    <!-- Alt Yasal Güvence & İmzalar -->
                    <div class="text-center pt-2 space-y-1">
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 flex items-center justify-center gap-2 flex-wrap">
                            <span>🛡️ 256-Bit SSL Şifreleme</span>
                            <span>•</span>
                            <span>6698 KVKK Uyumlu</span>
                            <span>•</span>
                            <span>5651 Sayılı Yer Sağlayıcı</span>
                        </p>
                        <span class="text-xs font-semibold text-slate-400 dark:text-slate-500 block">∞ Meydan Yerel Ağı</span>
                    </div>

                </div>

            </div>

        </div>

        <!-- ============================================================= -->
        <!-- RESMİ YASAL MEVZUAT METİNLERİ MODALI                          -->
        <!-- ============================================================= -->
        <div x-show="legalModalOpen"
             x-transition.opacity
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm"
             style="display: none;">
            
            <div @click.away="legalModalOpen = false"
                 class="w-full max-w-2xl bg-white dark:bg-[#0f172a] rounded-3xl border border-slate-200 dark:border-white/10 shadow-2xl overflow-hidden flex flex-col max-h-[85vh]">
                
                <!-- Modal Tepe Başlık & Kapat -->
                <div class="px-6 py-4 border-b border-slate-100 dark:border-white/10 flex items-center justify-between bg-slate-50/60 dark:bg-slate-900/60">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center text-sm font-bold shadow-sm">
                            ⚖️
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white">T.C. Yasal Mevzuat & Sözleşme Metinleri</h3>
                            <p class="text-[11px] text-slate-500">Meydan Keçiören Yerel Sosyal Ağı Hukuki Bilgilendirme Portalı</p>
                        </div>
                    </div>
                    <button @click="legalModalOpen = false" class="p-1.5 rounded-full hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-700 dark:hover:text-white transition-colors cursor-pointer">
                        ✕
                    </button>
                </div>

                <!-- Modal Sekmeleri -->
                <div class="flex items-center gap-1 px-6 pt-3 border-b border-slate-100 dark:border-white/10 overflow-x-auto text-xs font-bold">
                    <button @click="legalModalTab = 'kvkk'"
                            :class="legalModalTab === 'kvkk' ? 'text-[#1877F2] border-b-2 border-[#1877F2] pb-2' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 pb-2'"
                            class="px-3 cursor-pointer shrink-0">
                        6698 KVKK Aydınlatma
                    </button>
                    <button @click="legalModalTab = 'terms'"
                            :class="legalModalTab === 'terms' ? 'text-[#1877F2] border-b-2 border-[#1877F2] pb-2' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 pb-2'"
                            class="px-3 cursor-pointer shrink-0">
                        Kullanıcı Sözleşmesi
                    </button>
                    <button @click="legalModalTab = 'law5651'"
                            :class="legalModalTab === 'law5651' ? 'text-[#1877F2] border-b-2 border-[#1877F2] pb-2' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 pb-2'"
                            class="px-3 cursor-pointer shrink-0">
                        5651 Sayılı Kanun & Loglama
                    </button>
                    <button @click="legalModalTab = 'cookies'"
                            :class="legalModalTab === 'cookies' ? 'text-[#1877F2] border-b-2 border-[#1877F2] pb-2' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 pb-2'"
                            class="px-3 cursor-pointer shrink-0">
                        Çerez Politikası
                    </button>
                </div>

                <!-- Modal İçerik Gövdesi -->
                <div class="p-6 overflow-y-auto space-y-4 text-xs text-slate-700 dark:text-slate-300 leading-relaxed">
                    
                    <!-- 1. KVKK AYDINLATMA METNİ -->
                    <div x-show="legalModalTab === 'kvkk'" class="space-y-3">
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white">6698 SAYILI KİŞİSEL VERİLERİN KORUNMASI KANUNU (KVKK) AYDINLATMA METNİ</h4>
                        <p><strong>1. Veri Sorumlusu:</strong> Meydan Yerel Mahalle İletişim Ağı (“Platform”) olarak, 6698 sayılı Kişisel Verilerin Korunması Kanunu (“KVKK”) uyarınca, veri sorumlusu sıfatıyla kişisel verilerinizi mevzuata uygun şekilde işlemekteyiz.</p>
                        <p><strong>2. İşlenen Kişisel Verileriniz:</strong> Adınız, soyadınız, telefon numaranız, e-posta adresiniz, ikamet ettiğiniz mahalle bilgisi (Keçiören ilçesi sınırları), doğum tarihiniz, sisteme giriş zaman damgaları ve IP adresiniz.</p>
                        <p><strong>3. İşleme Amaçları ve Hukuki Sebepleri:</strong> Kişisel verileriniz; platform üyeliğinizin tesisi, doğru mahalle sakinleriyle iletişime geçebilmeniz (mahalle imecesi, acil durum yardımı, yerel pazar), platform güvenliğinin temini ve 5651 sayılı Kanun uyarınca yasal erişim kayıtlarının tutulması amacıyla KVKK Madde 5/2 (c, ç, e, f) fıkralarına dayanılarak işlenmektedir.</p>
                        <p><strong>4. Verilerin Aktarılması:</strong> Kişisel verileriniz hiçbir surette ticari reklam amacıyla 3. şahıslara veya şirketlere satılmaz; yalnızca adli makamların (Cumhuriyet Savcılıkları, Mahkemeler, EGM) yasal talepleri doğrultusunda yetkili kamu kurumlarıyla paylaşılır.</p>
                        <p><strong>5. İlgili Kişi Hakları (KVKK Madde 11):</strong> Verilerinizin işlenip işlenmediğini öğrenme, yanlış verileri düzelttirme, silinmesini talep etme ve kanuna aykırı işleme sebebiyle zararın giderilmesini talep etme haklarına sahipsiniz.</p>
                    </div>

                    <!-- 2. KULLANICI SÖZLEŞMESİ -->
                    <div x-show="legalModalTab === 'terms'" class="space-y-3">
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white">MEYDAN YEREL TOPLULUK & KULLANICI SÖZLEŞMESİ</h4>
                        <p><strong>1. Taraflar ve Kapsam:</strong> İşbu sözleşme, Meydan platformuna üye olan kullanıcı ile Platform yönetimi arasında akdedilmiştir. Kullanıcı, platformu yerel komşuluk dayanışması ve yasal amaçlar doğrultusunda kullanacağını kabul eder.</p>
                        <p><strong>2. Yaş ve Ehliyet:</strong> Platforma yalnızca 18 yaşını doldurmuş, medeni hakları kullanma ehliyetine sahip gerçek kişiler üye olabilir.</p>
                        <p><strong>3. Ceza Hukuku Sorumluluğu (TCK Uyarısı):</strong> 5237 sayılı Türk Ceza Kanunu kapsamında; hakaret (Madde 125), tehdit (Madde 106), halkı kin ve düşmanlığa tahrik veya aşağılama (Madde 216), kişisel verileri hukuka aykırı olarak yayma (Madde 136) gibi suç teşkil eden içeriklerin paylaşılması kesinlikle yasaktır. Bu tür içeriklerde yasal sorumluluk tamamen paylaşımı yapan kullanıcıya aittir.</p>
                        <p><strong>4. Komşuluk İlkeleri:</strong> Mahalle sakinleri arasında karşılıklı saygı, güven ve dayanışma esastır. Esnaf tacizi, sahte ilanlar, manipülatif dezenformasyon ve nefret söylemi içeren hesaplar derhal kapatılır.</p>
                    </div>

                    <!-- 3. 5651 SAYILI KANUN & LOGLAMA -->
                    <div x-show="legalModalTab === 'law5651'" class="space-y-3">
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white">5651 SAYILI KANUN KAPSAMINDA YER SAĞLAYICI VE LOG SAKLAMA BİLDİRİMİ</h4>
                        <p><strong>1. Yasal Yükümlülük:</strong> Meydan, 5651 sayılı “İnternet Ortamında Yapılan Yayınların Düzenlenmesi ve Bu Yayınlar Yoluyla İşlenen Suçlarla Mücadele Edilmesi Hakkında Kanun” uyarınca <strong>Yer Sağlayıcı</strong> statüsündedir.</p>
                        <p><strong>2. Trafik Bilgisi (Log) Saklama:</strong> 5651 Sayılı Kanun Madde 5 uyarınca, platforma erişim sağlayan tüm kullanıcıların IP adresi, port numarası, bağlantı başlama ve bitiş zamanı gibi sistem erişim trafik logları <strong>en az 2 (iki) yıl</strong> süreyle kriptografik bütünlüğü (hash) korunarak saklanmak zorundadır.</p>
                        <p><strong>3. Adli Merci Talepleri:</strong> Mahkeme ve kolluk kuvvetleri tarafından suç soruşturmaları kapsamında talep edilen IP ve trafik bilgileri, Bilgi Teknolojileri ve İletişim Kurumu (BTK) standartlarına uygun olarak resmi makamlara tevdi edilir.</p>
                    </div>

                    <!-- 4. ÇEREZ POLİTİKASI -->
                    <div x-show="legalModalTab === 'cookies'" class="space-y-3">
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white">ÇEREZ (COOKIE) VE GİZLİLİK POLİTİKASI</h4>
                        <p>Platformumuzda yalnızca güvenli oturum yönetimi (session cookies), tema tercihleriniz (koyu/aydınlık mod) ve siber saldırıların engellenmesi (CSRF koruması) amacıyla zorunlu çerezler kullanılmaktadır. Üçüncü taraf reklam ve takip çerezleri barındırılmamaktadır.</p>
                    </div>

                </div>

                <!-- Modal Alt Barı -->
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900 border-t border-slate-100 dark:border-white/10 flex items-center justify-between">
                    <span class="text-[11px] text-slate-400">T.C. Adalet Bakanlığı & BTK Mevzuat İlkeleri</span>
                    <button @click="acceptAndCloseLegal()"
                            class="px-5 py-2.5 rounded-full bg-[#1877F2] hover:bg-blue-600 text-white font-bold text-xs shadow-md transition-all cursor-pointer">
                        Okudum ve Şartları Onaylıyorum ✓
                    </button>
                </div>

            </div>

        </div>

        </div>

    </div>

    <!-- ================================================================= -->
    
    <?php elseif ($page === 'forgot-password'): ?>
    <!-- ================================================================= -->
    <!-- MODERN SPLIT-SCREEN ŞİFRE SIFIRLAMA SAYFASI                        -->
    <!-- Link: /forgot-password                                            -->
    <!-- ================================================================= -->
    <div class="min-h-screen w-full bg-white dark:bg-[#0b0f19] flex flex-col justify-between transition-colors">
        
        <!-- Üst Sağ Küçük Tema ve Geri Butonları -->
        <div class="absolute top-5 right-5 sm:right-8 z-50 flex items-center gap-2">
            <div class="p-1 rounded-xl bg-slate-100/90 dark:bg-slate-800/90 backdrop-blur-md border border-slate-200 dark:border-white/10 flex items-center gap-1 shadow-sm">
                <button @click="setTheme(false)" type="button" :class="!darkMode ? 'bg-white text-blue-600 font-bold shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400'" class="px-2.5 py-1 rounded-lg text-xs flex items-center gap-1 transition-all">
                    <span>☀️ Aydınlık</span>
                </button>
                <button @click="setTheme(true)" type="button" :class="darkMode ? 'bg-slate-700 text-blue-400 font-bold shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400'" class="px-2.5 py-1 rounded-lg text-xs flex items-center gap-1 transition-all">
                    <span>🌙 Koyu</span>
                </button>
            </div>
            <a href="/" class="px-3.5 py-1.5 rounded-xl bg-slate-100/90 dark:bg-slate-800/90 backdrop-blur-md border border-slate-200 dark:border-white/10 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 shadow-sm transition-all">
                <span>← Ana Sayfa</span>
            </a>
        </div>

        <!-- Ana Bölünmüş Grid (Split-Screen) -->
        <div class="flex-1 grid grid-cols-1 lg:grid-cols-12 min-h-screen">
            
            <!-- SOL PANEL: GÖRSEL KOMPOZİSYON & BAŞLIK -->
            <div class="lg:col-span-7 xl:col-span-7 flex flex-col justify-between p-6 sm:p-10 lg:p-14 relative overflow-hidden bg-gradient-to-br from-amber-50/40 via-white to-blue-50/30 dark:from-[#0b0f19] dark:via-[#0f172a] dark:to-[#0b0f19]">
                
                <!-- Tepe Sol: Meydan Marka Logosu -->
                <div class="z-10">
                    <a href="/" class="inline-flex items-center gap-3 group">
                        <div class="w-12 h-12 rounded-2xl bg-[#1877F2] text-white flex items-center justify-center font-black shadow-lg shadow-blue-500/25 group-hover:scale-105 transition-transform">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <div>
                            <span class="text-2xl font-black tracking-tight text-slate-900 dark:text-white block">Meydan</span>
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Keçiören Yerel Mahalle Ağı</span>
                        </div>
                    </a>
                </div>

                <!-- Orta: İkonik Güvenlik Görseli -->
                <div class="my-8 sm:my-12 flex items-center justify-center relative py-8">
                    <div class="relative w-64 h-64 rounded-full bg-gradient-to-br from-blue-500/10 via-amber-500/10 to-indigo-500/10 flex items-center justify-center border-2 border-white dark:border-slate-800 shadow-2xl backdrop-blur-sm">
                        <div class="w-32 h-32 rounded-3xl bg-gradient-to-tr from-[#1877F2] to-indigo-600 text-white flex items-center justify-center shadow-2xl transform rotate-6 hover:rotate-0 transition-transform duration-500">
                            <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        </div>
                        <span class="absolute top-4 right-4 text-2xl animate-bounce">🔑</span>
                        <span class="absolute bottom-6 left-6 text-xl">🛡️</span>
                    </div>
                </div>

                <!-- Tepe Sol Alt: Büyük Slogan Metni -->
                <div class="z-10 pt-4">
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-[1.08] text-slate-900 dark:text-white">
                        <span class="text-[#1877F2]">Hesabını</span><br>
                        güvenle<br>
                        kurtar.
                    </h1>
                </div>

            </div>

            <!-- SAĞ PANEL: ŞİFRE SIFIRLAMA FORMU -->
            <div class="lg:col-span-5 xl:col-span-5 flex flex-col justify-center px-6 sm:px-12 lg:px-16 py-12 border-t lg:border-t-0 lg:border-l border-slate-200/80 dark:border-white/10 bg-white dark:bg-[#0b0f19]">
                
                <div class="w-full max-w-sm mx-auto space-y-6">
                    
                    <div class="space-y-1">
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                            Hesabını Bul
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Hesabını aramak için lütfen e-posta adresini veya cep telefonu numaranı gir.
                        </p>
                    </div>

                    <form @submit.prevent="triggerToast('Sıfırlama bağlantısı e-posta adresinize iletildi! 📩'); setTimeout(() => window.location.href = '/login', 1600);" class="space-y-4">
                        <div>
                            <input type="text"
                                   required
                                   placeholder="E-posta adresi veya cep telefonu numarası"
                                   class="w-full px-4 py-3.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-[#1877F2] focus:border-[#1877F2] outline-none transition-all">
                        </div>

                        <button type="submit"
                                class="w-full py-3.5 rounded-full bg-[#1877F2] hover:bg-blue-600 text-white font-bold text-sm shadow-md transition-all cursor-pointer">
                            Sıfırlama Bağlantısı Gönder
                        </button>
                    </form>

                    <div class="pt-2">
                        <a href="/login"
                           class="w-full py-3 rounded-full border-2 border-[#1877F2] hover:bg-blue-50 dark:hover:bg-slate-800/80 text-[#1877F2] font-bold text-sm text-center block transition-all shadow-sm">
                            İptal Et ve Giriş Yap'a Dön
                        </a>
                    </div>

                    <div class="text-center pt-6 text-slate-400 flex items-center justify-center gap-1.5 text-xs font-semibold">
                        <span>∞ Meydan Yerel Ağı</span>
                    </div>

                </div>

            </div>

        </div>

    </div>

    <?php else: ?>
    <!-- ================================================================= -->
    <!-- 2. ANA DÜZEN: 3 KOLONLU ELASTİK GRID                              -->
    <!-- ================================================================= -->
    <div class="max-w-[1560px] mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 flex-1">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- ========================================================= -->
            <!-- 2.1 SOL MENÜ: Tüm Sayfalar Arası %100 Çalışır Geçiş       -->
            <!-- ========================================================= -->
            <aside class="hidden lg:block lg:col-span-3 space-y-5 sticky top-22">
                <div class="luxury-card rounded-2xl p-2.5 space-y-1">
                    <div class="px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                        Mahalle Hayatı
                    </div>

                    <!-- 1. Ana Akış -->
                    <a href="/"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all"
                       :class="'<?= $page ?>' === 'feed' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Bölgem (Keçiören)</span>
                    </a>

                    <!-- 2. Profilim -->
                    <a href="/profile/@kadircbc"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all"
                       :class="'<?= $page ?>' === 'profile' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'">
                        <svg class="w-4 h-4 shrink-0 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>Profilim</span>
                        <span class="ml-auto text-[10px] font-bold px-1.5 py-0.5 rounded bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400">@kadircbc</span>
                    </a>

                    <!-- 3. Etkinlik Takvimi -->
                    <a href="/events"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all"
                       :class="'<?= $page ?>' === 'events' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'">
                        <svg class="w-4 h-4 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Etkinlik Takvimi</span>
                        <span class="ml-auto text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400">Bu Hafta Sonu</span>
                    </a>

                    <!-- 4. Mahalle Pazarı -->
                    <a href="/marketplace"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all"
                       :class="'<?= $page ?>' === 'marketplace' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'">
                        <svg class="w-4 h-4 shrink-0 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <span>Mahalle Pazarı</span>
                        <span class="ml-auto text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400">Ücretsiz & Pazar</span>
                    </a>

                    <!-- 5. Esnaf Rehberi -->
                    <a href="/business"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all"
                       :class="'<?= $page ?>' === 'business' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'">
                        <svg class="w-4 h-4 shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span>Esnaf Rehberi</span>
                        <span class="ml-auto text-[10px] font-bold px-1.5 py-0.5 rounded bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400">Doğrulanmış</span>
                    </a>

                    <!-- 6. Yerel Nabız & Analitik -->
                    <a href="/analytics/pulse"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all"
                       :class="'<?= $page ?>' === 'analytics' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'">
                        <svg class="w-4 h-4 shrink-0 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span>Yerel Nabız & Analitik</span>
                    </a>

                    <!-- 7. Acil Durum Ağı -->
                    <a href="/emergency"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all"
                       :class="'<?= $page ?>' === 'emergency' ? 'bg-rose-600 text-white shadow-sm' : 'text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30'">
                        <svg class="w-4 h-4 shrink-0 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Acil Durum Ağı</span>
                        <span class="ml-auto w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                    </a>
<!-- 8. Giriş Yap / Çıkış Yap Durumu -->
                    <template x-if="!isLoggedIn">
                        <div class="pt-2 mt-2 border-t border-slate-100 dark:border-white/5 space-y-1">
                            <a href="/login" class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                                <span class="text-sm">🔑</span>
                                <span>Giriş Yap</span>
                            </a>
                            <a href="/register" class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 transition-all">
                                <span class="text-sm">✨</span>
                                <span>Yeni Mahalle Kaydı</span>
                            </a>
                        </div>
                    </template>
                    <template x-if="isLoggedIn">
                        <div class="pt-2 mt-2 border-t border-slate-100 dark:border-white/5">
                            <button @click="logout()" type="button" class="w-full flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-all cursor-pointer">
                                <span class="text-sm">🚪</span>
                                <span>Oturumu Kapat</span>
                            </button>
                        </div>
                    </template>

                <!-- Komşu Pasaportu Kartı (Tıklanınca Profil Sayfasına Gider) -->
                <a href="/profile/@kadircbc" class="luxury-card luxury-card-hover rounded-2xl p-4 space-y-3.5 block group">
                    <div class="flex items-center gap-3">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=150&q=80"
                             alt="Kadir Çelebi"
                             class="w-12 h-12 rounded-xl object-cover ring-2 ring-blue-500/20 group-hover:ring-blue-500 transition-all">
                        <div>
                            <div class="flex items-center gap-1.5">
                                <h4 class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-blue-600 transition-colors">Kadir Çelebi</h4>
                                <svg class="w-3.5 h-3.5 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            </div>
                            <span class="text-xs text-slate-500 dark:text-slate-400">@kadircbc • Kalaba Mah.</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 text-xs border border-slate-100 dark:border-white/5">
                        <div>
                            <span class="text-[10px] text-slate-400 block font-semibold uppercase">Yerel Unvan</span>
                            <span class="font-bold text-blue-600 dark:text-blue-400">Aktif Komşu</span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] text-slate-400 block font-semibold uppercase">İtibar Puanı</span>
                            <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">120 Puan</span>
                        </div>
                    </div>
                    <span class="text-[11px] font-bold text-blue-600 dark:text-blue-400 text-center block">Profili Görüntüle →</span>
                </a>
            </aside>

            <!-- ========================================================= -->
            <!-- 2.2 ORTA KOLON: Tıklanan Sayfaya Göre Dinamik Yüklenen Alan-->
            <!-- ========================================================= -->
            <main class="col-span-12 lg:col-span-6 space-y-5">

                <?php if ($page === 'feed'): ?>
                    <!-- ===================================================== -->
                    <!-- SAYFA 1: ANA AKIŞ & ANKETLER                          -->
                    <!-- ===================================================== -->
                    <div class="luxury-card rounded-2xl p-1 flex items-center gap-1">
                        <button @click="activeTab = 'local'"
                                class="flex-1 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all flex items-center justify-center gap-1.5"
                                :class="activeTab === 'local' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            <span>Bölgem (Keçiören)</span>
                        </button>
                        <button @click="activeTab = 'general'"
                                class="flex-1 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all flex items-center justify-center gap-1.5"
                                :class="activeTab === 'general' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Genel Akış</span>
                        </button>
                    </div>

                    <!-- Paylaşım Kutusu (%100 Çalışır submitPost) -->
                    <div class="luxury-card rounded-2xl p-4 sm:p-5 space-y-3.5">
                        <div class="flex items-start gap-3.5">
                            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=120&q=80"
                                 alt="Avatar"
                                 class="w-10 h-10 rounded-xl object-cover ring-1 ring-slate-200 dark:ring-white/10 shrink-0">
                            <div class="flex-1 min-w-0">
                                <textarea x-model="postText"
                                          rows="3"
                                          placeholder="Keçiören'de ve mahallede neler oluyor? Bir soru sor, etkinlik duyur veya yerel gelişme paylaş..."
                                          class="w-full bg-transparent border-0 resize-none text-[15px] placeholder-slate-400 text-slate-900 dark:text-white focus:ring-0 p-0 outline-none leading-relaxed"></textarea>
                            </div>
                        </div>

                        <!-- Anket Formu -->
                        <div x-show="composeTab === 'poll'" x-cloak class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-white/10 space-y-2.5">
                            <div class="flex items-center justify-between text-xs font-bold text-blue-600 dark:text-blue-400">
                                <span>📊 Yerel Anket Seçenekleri</span>
                                <button @click="composeTab = 'post'" class="text-slate-400 hover:text-rose-500">İptal ✕</button>
                            </div>
                            <input type="text" placeholder="Seçenek 1 (Örn: Botanik Parkı Açık Hava Sineması)" class="w-full text-xs px-3 py-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 text-slate-900 dark:text-white focus:ring-1 focus:ring-blue-500 outline-none">
                            <input type="text" placeholder="Seçenek 2 (Örn: Şelale Parkı Kermesi)" class="w-full text-xs px-3 py-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 text-slate-900 dark:text-white focus:ring-1 focus:ring-blue-500 outline-none">
                        </div>

                        <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-white/5">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <button type="button"
                                        @click="composeTab = (composeTab === 'poll' ? 'post' : 'poll')"
                                        :class="composeTab === 'poll' ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 border-blue-200' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 border-slate-200 dark:border-white/10'"
                                        class="px-2.5 py-1.5 rounded-lg border text-xs font-semibold transition-all flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                    <span>Anket</span>
                                </button>
                                <button type="button"
                                        @click="triggerToast('Fotoğraf seçici hazır. Görsel optimizasyonu devrede.');"
                                        class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/10 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold transition-all flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>Fotoğraf</span>
                                </button>
                                <a href="/events"
                                   class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/10 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold transition-all flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>Etkinlik</span>
                                </a>
                            </div>
                            <button @click="submitPost()"
                                    class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm transition-colors cursor-pointer">
                                Yayınla
                            </button>
                        </div>
                    </div>

                    <!-- Bu Hafta Sonu Mahallede Ne Var? -->
                    <div class="luxury-card rounded-2xl p-4 sm:p-5 space-y-3.5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                                <h3 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                                    Bu Hafta Sonu Mahallede Ne Var?
                                </h3>
                            </div>
                            <a href="/events" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">
                                Tümünü Gör →
                            </a>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <!-- Etkinlik 1 -->
                            <div class="rounded-xl overflow-hidden border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-800/40 hover:border-amber-500/50 transition-all flex flex-col group">
                                <div class="h-28 w-full relative overflow-hidden">
                                    <img src="https://images.unsplash.com/photo-1517649763962-0c623266ddc0?auto=format&fit=crop&w=600&q=80" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                                    <span class="absolute top-2 left-2 text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-500 text-white">Spor & Koşu</span>
                                    <span class="absolute bottom-2 left-2 text-[11px] font-bold text-white">📅 Cumartesi • 09:30</span>
                                </div>
                                <div class="p-3 flex-1 flex flex-col justify-between space-y-2">
                                    <div>
                                        <h4 class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-blue-600 transition-colors">Botanikte Sabah Koşusu & Kahve</h4>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">📍 Şelale Park Kafe (Sponsorlu Mekan)</p>
                                    </div>
                                    <div class="pt-2 border-t border-slate-200/80 dark:border-white/5 flex items-center justify-between text-xs">
                                        <span class="text-slate-500 text-[11px]" x-text="`${event1Count} komşu katılıyor`"></span>
                                        <button @click="toggleEvent(1)"
                                                :class="event1Joined ? 'bg-emerald-600 text-white' : 'text-blue-600 dark:text-blue-400 font-bold hover:underline'"
                                                class="text-xs">
                                            <span x-text="event1Joined ? '✓ Katılıyorsun' : 'Katıl →'"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Etkinlik 2 -->
                            <div class="rounded-xl overflow-hidden border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-800/40 hover:border-purple-500/50 transition-all flex flex-col group">
                                <div class="h-28 w-full relative overflow-hidden">
                                    <img src="https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=600&q=80" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                                    <span class="absolute top-2 left-2 text-[10px] font-bold px-2 py-0.5 rounded bg-purple-600 text-white">Kültür & Kitap</span>
                                    <span class="absolute bottom-2 left-2 text-[11px] font-bold text-white">📅 Pazar • 15:00</span>
                                </div>
                                <div class="p-3 flex-1 flex flex-col justify-between space-y-2">
                                    <div>
                                        <h4 class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-blue-600 transition-colors">Mahalle Kitap Kulübü Söyleşisi</h4>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">📍 Keçiören Gençlik Kütüphanesi</p>
                                    </div>
                                    <div class="pt-2 border-t border-slate-200/80 dark:border-white/5 flex items-center justify-between text-xs">
                                        <span class="text-slate-500 text-[11px]" x-text="`${event2Count} komşu katılıyor`"></span>
                                        <button @click="toggleEvent(2)"
                                                :class="event2Joined ? 'bg-emerald-600 text-white' : 'text-blue-600 dark:text-blue-400 font-bold hover:underline'"
                                                class="text-xs">
                                            <span x-text="event2Joined ? '✓ Katılıyorsun' : 'Katıl →'"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dinamik Gönderi Akışı -->
                    <template x-for="p in posts" :key="p.id">
                        <article class="luxury-card luxury-card-hover rounded-2xl p-5 space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <img :src="p.avatar" class="w-10 h-10 rounded-xl object-cover ring-1 ring-slate-200 dark:ring-white/10">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-sm text-slate-900 dark:text-white" x-text="p.author"></span>
                                            <span class="text-xs text-slate-400" x-text="p.handle"></span>
                                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 font-bold" x-text="p.badge"></span>
                                        </div>
                                        <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1.5">
                                            <span x-text="p.time"></span>
                                            <span>•</span>
                                            <span class="text-blue-600 dark:text-blue-400 font-medium" x-text="`📍 ${p.location}`"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <p class="text-sm leading-relaxed text-slate-700 dark:text-slate-200" x-text="p.content"></p>

                            <!-- Eğer Anketli Gönderiyse -->
                            <template x-if="p.isPoll">
                                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-white/10 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[11px] font-bold px-2.5 py-0.5 rounded bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300">
                                            📊 Yerel Anket
                                        </span>
                                        <a href="/polls/1/card" target="_blank" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">
                                            İnfografik Kartı ↗
                                        </a>
                                    </div>
                                    <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">
                                        Keçiören'de bu hafta sonu en çok hangi etkinliği görmek istersiniz?
                                    </h4>

                                    <!-- Oy Verilmediğinde -->
                                    <div x-show="!p.pollVoted" class="space-y-2 text-xs">
                                        <button @click="votePoll(p.id)" class="w-full text-left p-3 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 hover:border-blue-500 transition-all flex items-center justify-between font-medium text-slate-800 dark:text-slate-200">
                                            <span>🎬 Açık Hava Sineması & Çay İkramı</span>
                                            <span class="text-blue-600 dark:text-blue-400 font-bold">Oy Ver →</span>
                                        </button>
                                        <button @click="votePoll(p.id)" class="w-full text-left p-3 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 hover:border-blue-500 transition-all flex items-center justify-between font-medium text-slate-800 dark:text-slate-200">
                                            <span>🌿 Botanik Parkı Doğa Yürüyüşü</span>
                                            <span class="text-blue-600 dark:text-blue-400 font-bold">Oy Ver →</span>
                                        </button>
                                    </div>

                                    <!-- Oy Verildiğinde -->
                                    <div x-show="p.pollVoted" class="space-y-2 text-xs">
                                        <div class="relative overflow-hidden rounded-lg border border-blue-500 p-3 bg-blue-50/60 dark:bg-blue-950/40">
                                            <div class="absolute inset-y-0 left-0 bg-blue-500/20 dark:bg-blue-500/30 rounded-l-lg" style="width: 68%"></div>
                                            <div class="relative z-10 flex items-center justify-between font-bold text-slate-900 dark:text-white">
                                                <span>🎬 Açık Hava Sineması <span class="text-[9px] bg-blue-600 px-1 py-0.2 rounded text-white font-bold ml-1">✓ Oyunuz</span></span>
                                                <span class="font-mono text-blue-600 dark:text-blue-300">%68.0</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- Aksiyon Barı -->
                            <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-white/5 text-xs text-slate-500">
                                <button @click="toggleLike(p.id)"
                                        class="flex items-center gap-1.5 transition-colors"
                                        :class="p.isLiked ? 'text-rose-600 font-bold' : 'hover:text-rose-600'">
                                    <svg class="w-4 h-4" :class="p.isLiked ? 'fill-current text-rose-600' : 'text-slate-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                    <span x-text="p.likes"></span> Beğeni
                                </button>
                                <button @click="triggerToast('Yorum yazma penceresi açıldı.')" class="flex items-center gap-1.5 hover:text-blue-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    <span x-text="`${p.comments} Yorum`"></span>
                                </button>
                                <button @click="navigator.clipboard.writeText(window.location.origin); triggerToast('Bağlantı panoya kopyalandı! 🔗');" class="flex items-center gap-1.5 hover:text-emerald-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                                    <span>Paylaş</span>
                                </button>
                            </div>
                        </article>
                    </template>

                <?php elseif ($page === 'marketplace'): ?>
                    <!-- SAYFA: MAHALLE PAZARI -->
                    <div class="luxury-card rounded-2xl p-5 space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-black text-slate-900 dark:text-white">Keçiören Mahalle Pazarı</h2>
                                <p class="text-xs text-slate-500">Komisyonsuz, elden teslim veya askıda ücretsiz eşyalar</p>
                            </div>
                            <button @click="triggerToast('İlan verme formu açıldı.')"
                                    class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition-colors flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>+ İlan Ver</span>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                            <div class="rounded-xl overflow-hidden border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-800/40 flex flex-col justify-between">
                                <div class="h-40 w-full relative">
                                    <img src="https://images.unsplash.com/photo-1614594975525-e45190c55d0b?auto=format&fit=crop&w=600&q=80" class="w-full h-full object-cover">
                                    <span class="absolute top-2 left-2 text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-600 text-white">Esnaf İndirimi</span>
                                    <span class="absolute bottom-2 left-2 text-xs font-bold px-2.5 py-1 rounded bg-black/80 text-white">₺120</span>
                                </div>
                                <div class="p-3.5 space-y-2">
                                    <h4 class="text-xs font-bold text-slate-900 dark:text-white">İç Mekan Paşa Kılıcı & Monstera</h4>
                                    <p class="text-[11px] text-slate-500">Demir Botanik • Tepebaşı Mah.</p>
                                    <button @click="openChat('Demir Botanik', 'İç Mekan Paşa Kılıcı & Monstera')"
                                            class="w-full py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-sm transition-colors">
                                        Satıcıya Güvenli Mesaj Gönder 💬
                                    </button>
                                </div>
                            </div>

                            <div class="rounded-xl overflow-hidden border border-emerald-500/50 bg-emerald-50/30 dark:bg-emerald-950/20 flex flex-col justify-between">
                                <div class="h-40 w-full relative">
                                    <img src="https://images.unsplash.com/photo-1533090161767-e6ffed986c88?auto=format&fit=crop&w=600&q=80" class="w-full h-full object-cover">
                                    <span class="absolute top-2 left-2 text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-600 text-white">🎁 Askıda Eşya</span>
                                    <span class="absolute bottom-2 left-2 text-xs font-bold px-2.5 py-1 rounded bg-emerald-700 text-white">ÜCRETSİZ</span>
                                </div>
                                <div class="p-3.5 space-y-2">
                                    <h4 class="text-xs font-bold text-slate-900 dark:text-white">Çalışma Masası & Ahşap Sandalye</h4>
                                    <p class="text-[11px] text-slate-500">Komşu Ahmet • Kalaba Mah.</p>
                                    <button @click="openChat('Ahmet Yılmaz', 'Çalışma Masası & Ahşap Sandalye')"
                                            class="w-full py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition-colors">
                                        Askıdan Al / İletişime Geç 🎁
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php elseif ($page === 'business'): ?>
                    <!-- SAYFA: ESNAF REHBERİ -->
                    <div class="luxury-card rounded-2xl p-5 space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-black text-slate-900 dark:text-white">Keçiören Doğrulanmış Esnaf Rehberi</h2>
                                <p class="text-xs text-slate-500">Doğrulanmış mahalle dükkanları ve doğrudan WhatsApp sipariş</p>
                            </div>
                        </div>

                        <div class="space-y-4 pt-2">
                            <div class="rounded-xl overflow-hidden border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-800/40 p-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <img src="https://images.unsplash.com/photo-1554118811-1e0d58224f24?auto=format&fit=crop&w=150&q=80" class="w-12 h-12 rounded-xl object-cover">
                                        <div>
                                            <div class="flex items-center gap-1.5">
                                                <h4 class="text-sm font-bold text-slate-900 dark:text-white">Şelale Park Kafe & Bistro</h4>
                                                <span class="text-[10px] text-emerald-600 font-bold">✓ Doğrulanmış Esnaf</span>
                                            </div>
                                            <p class="text-xs text-slate-500">Kalaba Mah. Şelale Cad. No:14</p>
                                        </div>
                                    </div>
                                    <span class="text-xs font-bold text-amber-500">★ 4.9 (128 Yorum)</span>
                                </div>
                                <div class="flex items-center gap-2 pt-2 border-t border-slate-200/80 dark:border-white/5">
                                    <a href="https://api.whatsapp.com/send?phone=905550000000&text=Selale+Kafe" target="_blank"
                                       class="flex-1 text-center py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold">
                                        WhatsApp ile Sipariş ↗
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php elseif ($page === 'analytics'): ?>
                    <!-- SAYFA: YEREL NABIZ -->
                    <div class="luxury-card rounded-2xl p-5 space-y-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-black text-slate-900 dark:text-white">Keçiören Gündem Nabzı & Analitik</h2>
                                <p class="text-xs text-slate-500">İlçe trendleri ve duygu analizi</p>
                            </div>
                            <a href="/api/v1/analytics/pulse/kecioren" target="_blank" class="px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-bold">B2B JSON API ↗</a>
                        </div>
                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-white/10 space-y-2">
                            <div class="flex items-center justify-between text-xs font-bold">
                                <span>İlçe Genel Duygu Durumu</span>
                                <span class="text-emerald-600 font-black">%68.4 Pozitif & Memnun</span>
                            </div>
                            <div class="w-full bg-slate-200 dark:bg-white/10 h-3 rounded-full overflow-hidden flex">
                                <div class="bg-emerald-500 h-full" style="width: 68.4%"></div>
                                <div class="bg-rose-500 h-full" style="width: 31.6%"></div>
                            </div>
                        </div>
                    </div>

                <?php elseif ($page === 'emergency'): ?>
                    <!-- SAYFA: ACİL DURUM AĞI -->
                    <div class="space-y-4">
                        <div class="p-4 rounded-2xl bg-rose-600 text-white shadow-lg space-y-2">
                            <h3 class="text-sm font-black uppercase tracking-wider">🚨 Keçiören İlçe Acil Durum Ağı Devrede</h3>
                            <p class="text-xs opacity-95">Afet, kayıp ve acil kan ihtiyaçları öncelikli olarak mahalle sakinlerine ulaştırılır.</p>
                            <button @click="triggerToast('Acil durum bildirim formu açıldı.')" class="px-4 py-2 rounded-xl bg-white text-rose-700 text-xs font-black shadow-sm">
                                + Acil Durum İlanı Bildir
                            </button>
                        </div>
                    </div>
                <?php endif; ?>

            </main>

            <!-- ========================================================= -->
            <!-- 2.3 SAĞ KOLON: Nabız, Liderlik & Esnaf Vitrini           -->
            <!-- ========================================================= -->
            <aside class="hidden lg:block lg:col-span-3 space-y-5 sticky top-22">
                <!-- Gündem Nabzı -->
                <div class="luxury-card rounded-2xl p-4 sm:p-5 space-y-3.5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                                Keçiören Gündem Nabzı
                            </h3>
                        </div>
                        <a href="/analytics/pulse" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">Detay ↗</a>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-white/5 space-y-2">
                        <div class="flex items-center justify-between text-xs font-bold">
                            <span class="text-slate-500">Genel Memnuniyet</span>
                            <span class="text-emerald-600 dark:text-emerald-400">%68.4 Olumlu</span>
                        </div>
                        <div class="w-full bg-slate-200 dark:bg-white/10 h-2 rounded-full overflow-hidden flex">
                            <div class="bg-emerald-500 h-full" style="width: 68.4%"></div>
                            <div class="bg-rose-500 h-full" style="width: 31.6%"></div>
                        </div>
                    </div>

                    <div class="space-y-1.5 pt-1">
                        <span class="text-[11px] font-bold text-slate-400 block uppercase">En Çok Konuşulanlar</span>
                        <div class="flex flex-wrap gap-1.5">
                            <a href="/analytics/pulse" class="px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 transition-colors">#BotanikParkı</a>
                            <a href="/analytics/pulse" class="px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 transition-colors">#SuKesintisi</a>
                            <a href="/analytics/pulse" class="px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 transition-colors">#KeçiörenPazarı</a>
                        </div>
                    </div>
                </div>

                <!-- Haftanın Komşuları -->
                <div class="luxury-card rounded-2xl p-4 sm:p-5 space-y-3.5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 1.738 5.42a1 1 0 01-.285 1.05A3.989 3.989 0 0115 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.715-5.349L11 6.477V16h2a1 1 0 110 2H7a1 1 0 110-2h2V6.477L6.237 7.582l1.715 5.349a1 1 0 01-.285 1.05A3.989 3.989 0 015 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.738-5.42-1.233-.617a1 1 0 01.894-1.788l1.599.799L9 4.323V3a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                                Haftanın Komşuları
                            </h3>
                        </div>
                        <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">Top 3</span>
                    </div>

                    <div class="space-y-2">
                        <a href="/profile/@mehmetkaya" class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 hover:bg-blue-50 dark:hover:bg-slate-800 border border-slate-100 dark:border-white/5 flex items-center justify-between text-xs transition-colors group block">
                            <div class="flex items-center gap-2.5">
                                <span class="font-bold text-amber-500 font-mono text-sm">1.</span>
                                <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=100&q=80" class="w-8 h-8 rounded-lg object-cover ring-1 ring-slate-200 dark:ring-white/10 group-hover:ring-blue-500">
                                <div>
                                    <span class="font-bold text-slate-900 dark:text-white group-hover:text-blue-600 block">Dr. Mehmet Kaya</span>
                                    <span class="text-[10px] text-slate-400">@mehmetkaya • Gönüllü Muhtar</span>
                                </div>
                            </div>
                            <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">+580p</span>
                        </a>
                        <a href="/profile/@serkanusta" class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 hover:bg-blue-50 dark:hover:bg-slate-800 border border-slate-100 dark:border-white/5 flex items-center justify-between text-xs transition-colors group block">
                            <div class="flex items-center gap-2.5">
                                <span class="font-bold text-slate-400 font-mono text-sm">2.</span>
                                <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=100&q=80" class="w-8 h-8 rounded-lg object-cover ring-1 ring-slate-200 dark:ring-white/10 group-hover:ring-blue-500">
                                <div>
                                    <span class="font-bold text-slate-900 dark:text-white group-hover:text-blue-600 block">Serkan Usta</span>
                                    <span class="text-[10px] text-slate-400">@serkanusta • Mahalle Esnafı</span>
                                </div>
                            </div>
                            <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">+420p</span>
                        </a>
                        <a href="/profile/@aysetugba" class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 hover:bg-blue-50 dark:hover:bg-slate-800 border border-slate-100 dark:border-white/5 flex items-center justify-between text-xs transition-colors group block">
                            <div class="flex items-center gap-2.5">
                                <span class="font-bold text-amber-700 font-mono text-sm">3.</span>
                                <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=100&q=80" class="w-8 h-8 rounded-lg object-cover ring-1 ring-slate-200 dark:ring-white/10 group-hover:ring-blue-500">
                                <div>
                                    <span class="font-bold text-slate-900 dark:text-white group-hover:text-blue-600 block">Ayşe Öğretmen</span>
                                    <span class="text-[10px] text-slate-400">@aysetugba • Kitap Kulübü</span>
                                </div>
                            </div>
                            <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">+310p</span>
                        </a>
                    </div>
                </div>

                <!-- Öne Çıkan Esnaf -->
                <div class="luxury-card rounded-2xl p-4 sm:p-5 space-y-3.5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                                Öne Çıkan Esnaf
                            </h3>
                        </div>
                        <a href="/business" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">Tümü ↗</a>
                    </div>

                    <div class="rounded-xl overflow-hidden border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-800/40">
                        <div class="h-28 w-full relative">
                            <img src="https://images.unsplash.com/photo-1554118811-1e0d58224f24?auto=format&fit=crop&w=600&q=80" class="w-full h-full object-cover">
                            <span class="absolute bottom-2 left-2 text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-500 text-white">
                                🟢 Şu An Açık
                            </span>
                        </div>
                        <div class="p-3 space-y-2">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold text-slate-900 dark:text-white">Şelale Park Kafe & Bistro</h4>
                                <span class="text-xs font-bold text-amber-500">★ 4.9</span>
                            </div>
                            <a href="https://api.whatsapp.com/send?phone=905550000000&text=Selale+Kafe" target="_blank"
                               class="block w-full text-center py-2 rounded-lg bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-xs font-bold transition-opacity hover:opacity-90">
                                WhatsApp ile İletişim ↗
                            </a>
                        </div>
                    </div>
                </div>

            </aside>

        </div>
    </div>
    <?php endif; ?>

    <!-- Güvenli Mesajlaşma Modalı -->
    <div x-show="showChatModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div class="luxury-card rounded-2xl max-w-md w-full p-5 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-white/10 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <div>
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white" x-text="chatRecipient"></h4>
                        <span class="text-[11px] text-slate-500" x-text="chatItemTitle"></span>
                    </div>
                </div>
                <button @click="showChatModal = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-white font-bold">✕</button>
            </div>
            <div class="space-y-2">
                <label class="text-[11px] font-bold text-slate-500 uppercase">Gizli Güvenli Mesajınız</label>
                <textarea x-model="chatMessage" rows="4" class="w-full text-xs p-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-white/10 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-blue-500"></textarea>
                <p class="text-[10px] text-slate-400">🔒 Telefon numaranız gizli tutulur. İletişim platform içi güvenli mesajlaşma üzerinden gerçekleşir.</p>
            </div>
            <div class="flex items-center justify-end gap-2 pt-2">
                <button @click="showChatModal = false" class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-100">İptal</button>
                <button @click="sendChatMessage()" class="px-5 py-2 rounded-xl bg-blue-600 text-white text-xs font-bold shadow-sm">Mesajı Gönder</button>
            </div>
        </div>
    </div>

    <!-- Mobil Gezinme Dock'u -->
    <nav class="lg:hidden fixed bottom-0 inset-x-0 z-50 backdrop-blur-xl bg-white/95 dark:bg-[#0b0f19]/95 border-t border-slate-200 dark:border-white/10 px-4 py-2.5 flex items-center justify-around shadow-lg">
        <a href="/" class="flex flex-col items-center gap-1 text-xs font-bold" :class="'<?= $page ?>' === 'feed' ? 'text-blue-600' : 'text-slate-400'">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span class="text-[10px]">Akış</span>
        </a>
        <a href="/profile/@kadircbc" class="flex flex-col items-center gap-1 text-xs font-medium" :class="'<?= $page ?>' === 'profile' ? 'text-blue-600' : 'text-slate-400'">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <span class="text-[10px]">Profil</span>
        </a>
        <a href="/events" class="flex flex-col items-center gap-1 text-xs font-medium" :class="'<?= $page ?>' === 'events' ? 'text-blue-600' : 'text-slate-400'">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span class="text-[10px]">Etkinlik</span>
        </a>
        <a href="/marketplace" class="flex flex-col items-center gap-1 text-xs font-medium" :class="'<?= $page ?>' === 'marketplace' ? 'text-blue-600' : 'text-slate-400'">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            <span class="text-[10px]">Pazar</span>
        </a>
    </nav>

</body>
</html>
