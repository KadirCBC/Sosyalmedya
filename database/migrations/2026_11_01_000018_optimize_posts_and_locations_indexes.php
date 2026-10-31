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
        Schema::table('posts', function (Blueprint $table) {
            // is_hidden = false filtresi NotBlockedScope tarafından her sorguya eklendiği için
            // bileşik indeksin başına is_hidden eklenerek tam indeks kapsaması (Zero Filesort & Zero Temporary Table) sağlanır.
            $table->rawIndex('is_hidden, scope, location_id, id desc', 'idx_posts_local_composite');
            $table->rawIndex('is_hidden, scope, id desc', 'idx_posts_general_composite');
            $table->rawIndex('user_id, is_hidden, id desc', 'idx_posts_user_timeline');
        });

        Schema::table('locations', function (Blueprint $table) {
            // Şehir bazlı filtrelemeler ve istatistik sorguları için
            $table->index('city', 'idx_locations_city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex('idx_posts_local_composite');
            $table->dropIndex('idx_posts_general_composite');
            $table->dropIndex('idx_posts_user_timeline');
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->dropIndex('idx_locations_city');
        });
    }
};

