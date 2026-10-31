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
        // 1. Yerel Etkinlikler Tablosu
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->string('title', 120);
            $table->text('description');
            $table->enum('category', [
                'spor',
                'kultur_sanat',
                'yardimlasma',
                'hobi_oyun',
                'belediye_duyuru',
            ])->default('hobi_oyun');
            $table->string('venue_name', 120);
            $table->string('address', 255);
            $table->timestamp('event_date');
            $table->integer('max_participants')->nullable();
            $table->unsignedInteger('participants_count')->default(0);
            $table->enum('status', ['active', 'cancelled', 'completed'])->default('active');

            // Esnaf Sponsorluk Bağı (Yerel kafeler / işletmeler için sponsorlu buluşma noktası)
            $table->foreignId('business_profile_id')->nullable()->constrained('business_profiles')->nullOnDelete();
            $table->boolean('is_sponsored_venue')->default(false);

            // Hatırlatma bildirim bayrağı (Etkinliğe 2 saat kala gönderim)
            $table->timestamp('reminder_sent_at')->nullable();

            $table->timestamps();

            // Performans bileşik indeksi
            $table->index(['location_id', 'status', 'event_date'], 'idx_events_location_status_date');
            $table->index(['event_date', 'reminder_sent_at'], 'idx_events_reminder');
        });

        // 2. Etkinlik Katılımcıları Tablosu
        Schema::create('event_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['attending', 'interested'])->default('attending');
            $table->timestamps();

            // Mükerrer katılım engeli
            $table->unique(['event_id', 'user_id'], 'uniq_event_attendee');
            $table->index(['event_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_attendees');
        Schema::dropIfExists('events');
    }
};

