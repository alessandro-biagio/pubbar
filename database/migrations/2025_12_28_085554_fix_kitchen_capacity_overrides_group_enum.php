<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1) aggiungi colonna nuova (string)
        Schema::table('kitchen_capacity_overrides', function (Blueprint $table) {
            if (!Schema::hasColumn('kitchen_capacity_overrides', 'category_slug')) {
                $table->string('category_slug', 64)->after('hour_start');
            }
        });

        // 2) copia i dati esistenti: enum `group` -> category_slug
        // NB: `group` è keyword, quindi backticks
        DB::statement("UPDATE kitchen_capacity_overrides SET category_slug = `group`");

        // 3) rimuovi unique vecchio + colonna enum vecchia
        Schema::table('kitchen_capacity_overrides', function (Blueprint $table) {
            // droppa unique di default (Laravel lo chiama kitchen_capacity_overrides_hour_start_group_unique)
            // ma qui usiamo dropUnique con le colonne: è il modo più robusto
            if (Schema::hasColumn('kitchen_capacity_overrides', 'group')) {
                $table->dropUnique(['hour_start', 'group']);
                $table->dropColumn('group');
            }
        });

        // 4) aggiungi unique nuovo
        Schema::table('kitchen_capacity_overrides', function (Blueprint $table) {
            $table->unique(['hour_start', 'category_slug'], 'kco_hour_category_unique');
        });
    }

    public function down(): void
    {
        // Rollback non supportato volutamente:
        // tornare a ENUM richiederebbe ricostruire la lista dinamica di categorie.
    }
};
