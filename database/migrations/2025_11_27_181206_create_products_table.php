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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // Admin/User who created it
            $table->string('name');
            $table->string('type'); // e.g. Physical, Digital, Service
            $table->text('description'); // Changed to text/longText for longer content
            $table->string('image')->nullable(); // Main image filename
            $table->timestamps();

            // Foreign key constraint (assuming standard users table exists)
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
