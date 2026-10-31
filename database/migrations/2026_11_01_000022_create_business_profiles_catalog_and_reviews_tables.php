<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Esnaf İşletme Profilleri Tablosu
        Schema::create('business_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('location_id')
                ->constrained('locations')
                ->cascadeOnDelete();
            $table->string('business_name', 120);
            $table->string('tax_number', 30)->nullable();
            $table->enum('category', [
                'restoran_kafe',
                'hizmet_usta',
                'perakende_butik',
                'diger',
            ])->default('diger');
            $table->text('address');
            $table->text('google_maps_url')->nullable();
            $table->string('phone', 30);
            $table->string('whatsapp_number', 30)->nullable();
            $table->json('opening_hours')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('cover_url')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->decimal('rating_avg', 3, 2)->default(0.00);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->enum('subscription_status', ['inactive', 'active', 'past_due', 'canceled'])->default('inactive');
            $table->timestamp('subscription_expires_at')->nullable();
            $table->unsignedSmallInteger('free_boosts_remaining')->default(0);
            $table->timestamps();

            // İndeksler
            $table->index(['location_id', 'category', 'is_verified'], 'idx_business_loc_cat_verified');
            $table->index(['subscription_status', 'subscription_expires_at'], 'idx_business_subscription');
        });

        // 2. İşletme Menü / Katalog Öğeleri Tablosu
        Schema::create('business_catalog_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_profile_id')
                ->constrained('business_profiles')
                ->cascadeOnDelete();
            $table->string('name', 120);
            $table->decimal('price', 10, 2)->default(0.00);
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->index(['business_profile_id', 'is_available'], 'idx_catalog_profile_avail');
        });

        // 3. Yerel Halkın Puan ve Tarafsız Yorumları Tablosu
        Schema::create('business_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_profile_id')
                ->constrained('business_profiles')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('rating'); // 1-5 arası
            $table->text('comment');
            $table->timestamps();

            // Bir kullanıcı bir işletmeye tek bir değerlendirme yapabilir
            $table->unique(['business_profile_id', 'user_id'], 'uidx_business_review_user');
            $table->index(['business_profile_id', 'created_at'], 'idx_reviews_profile_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_reviews');
        Schema::dropIfExists('business_catalog_items');
        Schema::dropIfExists('business_profiles');
    }
};

