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
        Schema::create('follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follower_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('following_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->timestamps();

            // Bir kullanıcının aynı kişiyi birden fazla kez takip etmesini engeller
            $table->unique(['follower_id', 'following_id'], 'uidx_follows_follower_following');

            // Takipçi listesi sorguları için ters indeks (kullanıcının takipçilerini çekerken)
            $table->index(['following_id', 'id desc'], 'idx_follows_following_id_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('follows');
    }
};

