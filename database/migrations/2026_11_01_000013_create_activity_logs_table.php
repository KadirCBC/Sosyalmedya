<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 5651 Sayılı Kanun ve KVKK uyumlu işlem ve trafik kayıtları tablosu.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('action', 50); // örn: post_create, comment_create, auth_login
            $table->string('ip_address', 45); // IPv4 veya IPv6
            $table->unsignedInteger('port')->nullable(); // CGNAT / 5651 kanuni zorunluluk portu
            $table->text('user_agent')->nullable();
            $table->string('target_type', 60)->nullable(); // App\Models\Post vb.
            $table->unsignedBigInteger('target_id')->nullable();
            $table->char('payload_hash', 64)->nullable(); // SHA-256 içerik parmak izi
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'created_at'], 'idx_activity_logs_user_date');
            $table->index(['action', 'created_at'], 'idx_activity_logs_action_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};

