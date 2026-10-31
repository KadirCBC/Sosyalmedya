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
        Schema::table('ads', function (Blueprint $table) {
            $table->string('merchant_oid', 64)->nullable()->unique()->after('id');
            $table->string('cta_type', 30)->default('whatsapp')->after('cta_url'); // whatsapp, phone, map, website
            $table->unsignedSmallInteger('duration_days')->default(3)->after('budget');
            $table->decimal('daily_budget', 10, 2)->default(100.00)->after('duration_days');
            $table->decimal('paid_amount', 10, 2)->nullable()->after('daily_budget');
            $table->string('payment_method', 30)->default('paytr')->after('paid_amount');
            $table->timestamp('reported_at')->nullable()->after('ends_at'); // Bitiş SMS raporu gönderilme zamanı
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->dropColumn([
                'merchant_oid',
                'cta_type',
                'duration_days',
                'daily_budget',
                'paid_amount',
                'payment_method',
                'reported_at',
            ]);
        });
    }
};

