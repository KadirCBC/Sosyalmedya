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
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('location_id')
                ->nullable()
                ->constrained('locations')
                ->nullOnDelete();
            $table->string('title', 120);
            $table->string('content', 500);
            $table->string('image_url', 255)->nullable();
            $table->string('cta_text', 50)->default('Hemen İncele');
            $table->string('cta_url', 255);
            $table->enum('status', ['pending', 'active', 'rejected', 'expired'])
                ->default('pending');
            $table->decimal('budget', 10, 2)->default(0.00);
            $table->unsignedInteger('impressions_count')->default(0);
            $table->unsignedInteger('clicks_count')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'location_id'], 'idx_ads_status_location');
            $table->index(['user_id', 'status'], 'idx_ads_user_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};

