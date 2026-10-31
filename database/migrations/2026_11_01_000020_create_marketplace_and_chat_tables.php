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
        // 1. Mahalle Pazarı İlanları Tablosu
        Schema::create('marketplace_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('location_id')
                ->constrained('locations')
                ->cascadeOnDelete();
            $table->string('title', 100);
            $table->text('description');
            $table->decimal('price', 10, 2)->default(0.00);
            $table->enum('category', [
                'elektronik',
                'ev_esyasi',
                'giyim',
                'kitap_hobi',
                'diger',
            ])->default('diger');
            $table->json('images')->nullable();
            $table->enum('status', ['active', 'reserved', 'sold'])->default('active');
            $table->boolean('is_free')->default(false); // Askıda Eşya / Ücretsiz Paylaşım
            $table->timestamps();

            // Yüksek performanslı yerel filtreleme indeksi
            $table->index(['location_id', 'status', 'created_at'], 'idx_marketplace_loc_status_date');
            $table->index(['user_id', 'status'], 'idx_marketplace_user_status');
            $table->index(['is_free', 'location_id', 'status'], 'idx_marketplace_free_items');
        });

        // 2. Alıcı ve Satıcı Arasında Platform İçi Güvenli Sohbet Odaları
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')
                ->nullable()
                ->constrained('marketplace_items')
                ->nullOnDelete();
            $table->foreignId('buyer_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('seller_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->unique(['item_id', 'buyer_id', 'seller_id'], 'uidx_conversation_item_buyer_seller');
            $table->index(['buyer_id', 'last_message_at'], 'idx_conversations_buyer');
            $table->index(['seller_id', 'last_message_at'], 'idx_conversations_seller');
        });

        // 3. Sohbet Mesajları
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')
                ->constrained('conversations')
                ->cascadeOnDelete();
            $table->foreignId('sender_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->text('content');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['conversation_id', 'created_at'], 'idx_messages_conversation_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('marketplace_items');
    }
};

