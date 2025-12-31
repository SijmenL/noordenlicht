<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accommodatie_id')->constrained('accommodaties')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->dateTime('start');
            $table->dateTime('end');
            $table->string('status')->default('confirmed'); // pending, confirmed, cancelled
            $table->timestamps();
        });

        Schema::table('accommodaties', function (Blueprint $table) {
            // Default constraints
            $table->time('min_check_in')->default('08:00'); // Earliest check-in time
            $table->time('max_check_in')->default('23:00'); // Latest check-in time (or latest booking end time)
            $table->integer('min_duration_minutes')->default(120); // Minimum booking length in minutes
        });
    }

    public function down()
    {
        Schema::dropIfExists('bookings');
        Schema::table('accommodaties', function (Blueprint $table) {
            $table->dropColumn(['min_check_in', 'max_check_in', 'min_duration_minutes']);
        });
    }
};
