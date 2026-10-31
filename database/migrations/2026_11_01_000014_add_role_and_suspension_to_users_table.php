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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['user', 'local_moderator', 'super_admin'])
                ->default('user')
                ->after('bio');
            $table->foreignId('moderated_location_id')
                ->nullable()
                ->after('role')
                ->constrained('locations')
                ->nullOnDelete();
            $table->timestamp('suspended_until')
                ->nullable()
                ->after('moderated_location_id');

            $table->index('role', 'idx_users_role');
            $table->index('suspended_until', 'idx_users_suspended_until');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_role');
            $table->dropIndex('idx_users_suspended_until');
            $table->dropForeign(['moderated_location_id']);
            $table->dropColumn(['role', 'moderated_location_id', 'suspended_until']);
        });
    }
};

