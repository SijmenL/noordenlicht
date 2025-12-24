<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. The main order table
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // e.g. ORD-2025-0001
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Optional: if user is logged in

            // Status tracking
            $table->string('status')->default('open'); // open, paid, failed, canceled, shipped
            $table->string('payment_status')->default('pending');
            $table->string('mollie_payment_id')->nullable(); // To link back to Mollie

            // Financials
            $table->decimal('total_amount', 10, 2);

            // Customer Info
            $table->string('email');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('address');
            $table->string('zipcode');
            $table->string('city');
            $table->string('country')->default('NL');

            $table->timestamps();
        });

        // 2. The items inside the order
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');

            // We store the snapshot of the data at the time of purchase
            // (in case product name/price changes later)
            $table->string('product_name');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
