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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique(); // For the barcode

            // Relationships
            $table->foreignId('activity_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable();
            $table->foreignId('order_id')->nullable();

            // Ticket specific details
            $table->dateTime('start_date'); // To match $ticket->start_date

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
