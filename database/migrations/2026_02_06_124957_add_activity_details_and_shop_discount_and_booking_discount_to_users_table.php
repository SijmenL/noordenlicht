<?php

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
            $table->integer('shop_discount')->default(0)->after('password');
            $table->integer('booking_discount')->default(0)->after('shop_discount');
            $table->longText('activity_details')->nullable()->after('booking_discount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['shop_discount', 'booking_discount', 'activity_details']);
        });
    }
};
