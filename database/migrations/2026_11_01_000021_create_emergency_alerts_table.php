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
        Schema::create('emergency_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('location_id')
                ->constrained('locations')
                ->cascadeOnDelete();
            $table->enum('alert_type', [
                'kayip_ilan',       // Kayıp çocuk, yaşlı, evcil hayvan
                'afet_yardim',      // Deprem, yangın, sel, çadır/ısınma/erzak
                'kan_bagisi',       // Acil 0 Rh-, AB Rh+ vb. kan ihtiyacı
                'guvenlik_uyari',   // Gaz kaçağı, fırtına, şüpheli durum
            ]);
            $table->string('title', 150);
            $table->text('description');
            $table->string('contact_phone', 30)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_verified')->default(false);
            $table->enum('status', ['active', 'resolved'])->default('active');
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();

            // Kırmızı Akış (Sticky Alert) ve İlçe Bazlı Anlık Sorgu İndeksi
            $table->index(
                ['location_id', 'status', 'is_verified', 'created_at'],
                'idx_emergency_loc_status_verified'
            );
            $table->index(['alert_type', 'status'], 'idx_emergency_type_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emergency_alerts');
    }
};

