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
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->unsignedBigInteger('user_id')->nullable(); // Use unsignedBigInteger for foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('display_text')->nullable();
            $table->string('reference')->nullable();
            $table->string('type');
            $table->integer('action');
            $table->string('location')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
