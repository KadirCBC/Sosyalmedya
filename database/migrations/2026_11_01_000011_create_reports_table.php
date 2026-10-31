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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('reportable_type', 60);
            $table->unsignedBigInteger('reportable_id');
            $table->enum('reason', [
                'spam',
                'hate_speech',
                'harassment',
                'illegal',
                'copyright',
            ]);
            $table->text('description')->nullable();
            $table->enum('status', [
                'pending',
                'resolved',
                'rejected',
            ])->default('pending');
            $table->foreignId('reviewer_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();

            // Bir kullanıcının aynı içeriği yalnızca 1 kez şikayet edebilmesini sağlar
            $table->unique(
                ['reporter_id', 'reportable_type', 'reportable_id'],
                'uidx_reports_reporter_reportable'
            );

            // Eşik değeri (örn: 5 şikayet) ve moderasyon kuyruğu sorgularını hızlandıran indeks
            $table->index(
                ['reportable_type', 'reportable_id', 'status'],
                'idx_reports_target_status'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};

