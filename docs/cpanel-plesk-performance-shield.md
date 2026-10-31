# cPanel / Plesk Yüksek Trafik Performans ve Güvenlik Kılavuzu

Bu kılavuz, Meydan sosyal medya platformunun ani trafik patlamalarında (viral gönderiler, yerel seçimler, acil durum duyuruları vb.) cPanel veya Plesk VPS sunucularının CPU/RAM kilitlenmesini önlemek için uygulanan koruma katmanlarını içerir.

---

## 1. Cloudflare Yapılandırma Kuralları (Free & Pro Plan)

### A. Cache Rules (Önbellek Kuralları - Edge Caching)
Cloudflare Dashboard > **Caching** > **Cache Rules** sekmesine gidin:

#### Kural 1: Statik Medya ve Varlıklar (Maksimum Hız, Sıfır Sunucu Yükü)
* **Kural Adı:** `Static Assets Cache Everything`
* **If incoming requests match:**
  ```text
  (http.request.uri.path starts_with "/build/") or 
  (http.request.uri.path starts_with "/images/") or 
  (http.request.uri.path starts_with "/icons/") or 
  (http.request.uri.path in {"/manifest.json" "/sw.js" "/favicon.ico"})
  ```
* **Then (Ayarlar):**
  * **Cache Eligibility:** `Eligible for cache`
  * **Edge Cache TTL:** `Ignore cache-control header and use cache` -> `1 month`
  * **Browser Cache TTL:** `Respect origin header` (veya `7 days`)

#### Kural 2: Dinamik API, Akış ve Admin Rotaları (Bypass)
* **Kural Adı:** `Bypass Cache for Dynamic API and Feeds`
* **If incoming requests match:**
  ```text
  (http.request.uri.path starts_with "/api/") or 
  (http.request.uri.path starts_with "/feed") or 
  (http.request.uri.path starts_with "/admin") or 
  (http.request.uri.path starts_with "/ads")
  ```
* **Then (Ayarlar):**
  * **Cache Eligibility:** `Bypass cache`

#### Kural 3: Programmatic SEO Açılış Sayfaları (Micro-Caching)
* **Kural Adı:** `Micro-Cache SEO Landing Pages`
* **If incoming requests match:**
  ```text
  (http.request.uri.path starts_with "/yerel/")
  ```
* **Then (Ayarlar):**
  * **Cache Eligibility:** `Eligible for cache`
  * **Edge Cache TTL:** `30 minutes`

---

### B. WAF (Web Application Firewall) Güvenlik Kuralları
Cloudflare Dashboard > **Security** > **WAF** > **Custom Rules**:

#### Kural 1: Şüpheli Bot ve Tehditlere Karşı "Managed Challenge"
* **Kural Adı:** `Challenge Suspicious Traffic & Bad Bots`
* **Expression:**
  ```text
  (cf.threat_score gt 15 and not cf.client.bot) or 
  (cf.bot_management.score lt 30 and not cf.client.bot)
  ```
* **Action:** `Managed Challenge` (Kullanıcıya görünmez Turnstile doğrulaması sunar, botları sunucuya ulaşmadan eler)

#### Kural 2: OTP ve Giriş Uç Noktalarına Kaba Kuvvet (Brute-Force) Engeli
* **Kural Adı:** `Mitigate Auth & OTP Floods`
* **Expression:**
  ```text
  (http.request.uri.path in {"/api/v1/auth/send-otp" "/api/v1/auth/login" "/api/v1/auth/login-otp"})
  ```
* **Action:** `Rate Limiting` (Eğer Pro plan varsa: 1 dakikada 5 istekten fazlasına Block veya Challenge)

---

### C. cPanel / Plesk Gerçek IP Çözümleme (Real IP Restorasyonu)
Sunucunun Cloudflare arkasında doğru IP tespiti yapabilmesi ve Laravel Rate Limiter'ın Cloudflare proxy IP'lerini engellememesi için web sunucusuna aşağıdaki ayarlar eklenmelidir:

#### Apache (cPanel / Plesk Apache Ayarları)
`httpd.conf` veya VirtualHost içine:
```apache
<IfModule mod_remoteip.c>
    RemoteIPHeader CF-Connecting-IP
    RemoteIPTrustedProxy 173.245.48.0/20
    RemoteIPTrustedProxy 103.21.244.0/22
    RemoteIPTrustedProxy 103.22.200.0/22
    RemoteIPTrustedProxy 103.31.4.0/22
    RemoteIPTrustedProxy 141.101.64.0/18
    RemoteIPTrustedProxy 108.162.192.0/18
    RemoteIPTrustedProxy 190.93.240.0/20
    RemoteIPTrustedProxy 188.114.96.0/20
    RemoteIPTrustedProxy 197.234.240.0/22
    RemoteIPTrustedProxy 198.41.128.0/17
    RemoteIPTrustedProxy 162.158.0.0/15
    RemoteIPTrustedProxy 104.16.0.0/13
    RemoteIPTrustedProxy 104.24.0.0/14
    RemoteIPTrustedProxy 172.64.0.0/13
    RemoteIPTrustedProxy 131.0.72.0/22
</IfModule>
```

#### Nginx (Plesk Reverse Proxy veya Standalone Nginx)
```nginx
set_real_ip_from 173.245.48.0/20;
set_real_ip_from 103.21.244.0/22;
set_real_ip_from 103.22.200.0/22;
set_real_ip_from 103.31.4.0/22;
set_real_ip_from 141.101.64.0/18;
set_real_ip_from 108.162.192.0/18;
set_real_ip_from 190.93.240.0/20;
set_real_ip_from 188.114.96.0/20;
set_real_ip_from 197.234.240.0/22;
set_real_ip_from 198.41.128.0/17;
set_real_ip_from 162.158.0.0/15;
set_real_ip_from 104.16.0.0/13;
set_real_ip_from 104.24.0.0/14;
set_real_ip_from 172.64.0.0/13;
set_real_ip_from 131.0.72.0/22;
real_ip_header CF-Connecting-IP;
```

---

## 2. MySQL Yavaş Sorgu (Slow Query) ve İndeks Optimizasyonu

### A. `/etc/my.cnf` veya `/etc/mysql/my.cnf` Yapılandırması
```ini
[mysqld]
# 1. Yavaş Sorgu Günlüğü (0.5 saniyeden uzun süren veya indekssiz sorguları yakalar)
slow_query_log = 1
slow_query_log_file = /var/log/mysql/mysql-slow.log
long_query_time = 0.5
log_queries_not_using_indexes = 1
min_examined_row_limit = 100

# 2. InnoDB Buffer Pool Optimizasyonu (VPS RAM'ine Göre)
# 2 GB RAM'li VPS için:
# innodb_buffer_pool_size = 1G
# 4 GB RAM'li VPS için:
# innodb_buffer_pool_size = 2.5G
# 8 GB RAM'li VPS için:
innodb_buffer_pool_size = 5G
innodb_log_file_size = 512M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT

# 3. Bağlantı Havuzu
max_connections = 250
wait_timeout = 60
interactive_timeout = 60
```

---

## 3. EXPLAIN Analizi ve İndeks Doğrulama

Konsol komutu ile test etmek için:
```bash
php artisan db:analyze-indexes
```

### Kritik Sorgular ve Beklenen Çıktılar:

#### 1. Yerel Akış Sorgusu (Local Feed):
```sql
EXPLAIN SELECT id, user_id, content, scope, location_id 
FROM posts 
WHERE is_hidden = 0 AND scope = 'local' AND location_id = 1 
ORDER BY id DESC LIMIT 20;
```
* **Kullanılan İndeks:** `idx_posts_local_composite` (`is_hidden, scope, location_id, id desc`)
* **type:** `ref`
* **rows:** 20
* **Extra:** `Using index condition; Backward index scan` (Sıfır `Using filesort`, Sıfır `Using temporary`!)

#### 2. Genel Akış Sorgusu (General Feed):
```sql
EXPLAIN SELECT id, user_id, content, scope, location_id 
FROM posts 
WHERE is_hidden = 0 AND scope = 'general' 
ORDER BY id DESC LIMIT 20;
```
* **Kullanılan İndeks:** `idx_posts_general_composite` (`is_hidden, scope, id desc`)
* **type:** `ref`
* **rows:** 20
* **Extra:** `Using index condition; Backward index scan`

#### 3. Lokasyon Arama (Location Lookup):
```sql
EXPLAIN SELECT id, city, district 
FROM locations 
WHERE city = 'Ankara' AND district = 'Keçiören';
```
* **Kullanılan İndeks:** `uidx_locations_city_district`
* **type:** `const` (Doğrudan tek satır O(1) erişim)
* **rows:** 1

