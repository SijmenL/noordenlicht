<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('price_id');
            $table->timestamps();

            // Foreign keys
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            // Assuming the existing prices table is named 'prices'
            $table->foreign('price_id')->references('id')->on('prices')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_prices');
    }
};
