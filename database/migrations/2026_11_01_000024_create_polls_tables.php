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
        // 1. Anketler Tablosu (Gönderiye bağlı)
        Schema::create('polls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->string('question', 255);
            $table->timestamp('expires_at');
            $table->unsignedInteger('total_votes')->default(0);
            $table->timestamps();

            $table->index(['post_id', 'expires_at']);
        });

        // 2. Anket Seçenekleri Tablosu (2-4 seçenek)
        Schema::create('poll_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')->constrained()->cascadeOnDelete();
            $table->string('option_text', 100);
            $table->unsignedInteger('votes_count')->default(0);
            $table->timestamps();

            $table->index('poll_id');
        });

        // 3. Kullanıcı Oy Kayıtları (Mükerrer oy engeli)
        Schema::create('poll_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')->constrained()->cascadeOnDelete();
            $table->foreignId('poll_option_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            // Bir kullanıcı aynı ankette yalnızca 1 kez oy kullanabilir
            $table->unique(['poll_id', 'user_id'], 'uniq_poll_user_vote');
            $table->index(['poll_id', 'poll_option_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poll_votes');
        Schema::dropIfExists('poll_options');
        Schema::dropIfExists('polls');
    }
};

