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
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('post_id')
                ->constrained('posts')
                ->cascadeOnDelete();
            $table->timestamps();

            // Bir kullanıcının bir gönderiyi birden fazla kez beğenmesini engeller
            $table->unique(['user_id', 'post_id'], 'uidx_likes_user_post');

            // Bir gönderiyi beğenenlerin listelenmesi için ters indeks
            $table->index(['post_id', 'id desc'], 'idx_likes_post_id_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
};

