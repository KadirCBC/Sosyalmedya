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
        // 1. İlçe Trend Kelimeleri ve Hashtag Frekans Tablosu
        Schema::create('district_trends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')
                ->constrained('locations')
                ->cascadeOnDelete();
            $table->string('term', 80); // #sukesintisi, otobus, park vb.
            $table->boolean('is_hashtag')->default(false);
            $table->unsignedInteger('frequency')->default(1);
            $table->unsignedInteger('positive_hits')->default(0);
            $table->unsignedInteger('negative_hits')->default(0);
            $table->date('date');
            $table->timestamps();

            $table->unique(['location_id', 'term', 'date'], 'uidx_district_term_date');
            $table->index(['location_id', 'date', 'frequency'], 'idx_district_trend_lookup');
        });

        // 2. İlçe Günlük Memnuniyet ve Gündem Duygu Endeksi Tablosu
        Schema::create('district_sentiment_indexes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')
                ->constrained('locations')
                ->cascadeOnDelete();
            $table->date('date');
            $table->decimal('positive_score', 5, 2)->default(50.00); // %65.50
            $table->decimal('negative_score', 5, 2)->default(50.00); // %34.50
            $table->unsignedInteger('total_posts_analyzed')->default(0);
            $table->unsignedInteger('total_complaints_detected')->default(0);
            $table->json('top_complaint_topics')->nullable(); // ['#sukesintisi', 'otobüs', 'çöp']
            $table->timestamps();

            $table->unique(['location_id', 'date'], 'uidx_sentiment_location_date');
            $table->index(['date', 'negative_score'], 'idx_sentiment_date_complaints');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('district_sentiment_indexes');
        Schema::dropIfExists('district_trends');
    }
};

