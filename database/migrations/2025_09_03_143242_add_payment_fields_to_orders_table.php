<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_provider')->nullable()->after('status');          // 'paypal'
            $table->string('payment_status')->default('unpaid')->after('payment_provider'); // unpaid|paid|refunded
            $table->string('payment_session_id')->nullable()->after('payment_status');      // id ordine PayPal
            $table->string('payment_intent_id')->nullable()->after('payment_session_id');   // id capture PayPal
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_provider','payment_status','payment_session_id','payment_intent_id']);
        });
    }
};
