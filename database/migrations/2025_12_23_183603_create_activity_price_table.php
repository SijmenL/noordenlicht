<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_id');
            $table->unsignedBigInteger('price_id');
            $table->timestamps();

            // Foreign keys
            $table->foreign('activity_id')->references('id')->on('activities')->cascadeOnDelete();
            // Assuming the existing prices table is named 'prices'
            $table->foreign('price_id')->references('id')->on('prices')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_prices');
    }
};
