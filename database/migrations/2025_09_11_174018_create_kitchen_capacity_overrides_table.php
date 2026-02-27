<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('kitchen_capacity_overrides', function (Blueprint $table) {
            $table->id();
            $table->dateTime('hour_start');
            $table->enum('group', ['panini','sfiziosita']);
            $table->unsignedInteger('capacity');
            $table->timestamps();

            $table->unique(['hour_start','group']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('kitchen_capacity_overrides');
    }
};
