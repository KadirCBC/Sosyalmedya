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
        // 1. Kullanıcı İtibar ve Rozet Tablosu
        Schema::create('reputations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();
            $table->integer('points')->default(0)->index('idx_reputations_points');
            $table->string('level', 30)->default('Yeni Komşu');
            $table->json('badges')->nullable();
            $table->timestamps();
        });

        // 2. İtibar Hareket ve Puan Değişim Kayıtları (Audit Log)
        Schema::create('reputation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('action', 50); // post_created, comment_upvoted, report_confirmed, helpful_answer, local_like_received, post_removed
            $table->integer('points_delta');
            $table->string('description', 255)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at'], 'idx_reputation_logs_user_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reputation_logs');
        Schema::dropIfExists('reputations');
    }
};

