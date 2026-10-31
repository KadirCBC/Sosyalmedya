#!/usr/bin/env bash
set -e

echo "🚀 Meydan Platformu - Canlıya Dağıtım (Production Deploy) Başlatılıyor..."

# 1. Bakım modunu aç
php artisan down --render="errors::503" || true

# 2. Composer üretim bağımlılıklarını optimize autoloader ile kur
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# 3. Veritabanı migrasyonlarını zorunlu çalıştır
php artisan migrate --force

# 4. Sembolik bağlantı oluştur
php artisan storage:link || true

# 5. Üretim Önbelleklerini Derle
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 6. Queue Worker'ı yeniden başlat (kod güncellemelerini anında alması için)
php artisan queue:restart

# 7. Bakım modunu kapat
php artisan up

echo "✅ Dağıtım başarıyla tamamlandı!"

