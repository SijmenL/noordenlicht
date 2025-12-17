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
        Schema::create('accommodatie_images', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('accommodatie_id')->nullable();
            $table->string('temp_id')->nullable();
            $table->string('image');
            $table->string('alt')->nullable();
            $table->string('url')->nullable();


            $table->foreign('accommodatie_id')->references('id')->on('accommodaties');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accommodatie_images');
    }
};
