// database/migrations/2025_10_14_000000_add_is_superuser_to_users_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_superuser')) {
                $table->boolean('is_superuser')->default(false)->after('is_staff');
            }
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_superuser')) {
                $table->dropColumn('is_superuser');
            }
        });
    }
};
