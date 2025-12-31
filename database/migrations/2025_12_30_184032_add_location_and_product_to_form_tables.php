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
        // 1. Update the Elements table
        Schema::table('activity_form_elements', function (Blueprint $table) {
            // Add the location string
            $table->string('location')->nullable()->after('id');

            // Add product_id (nullable)
            $table->foreignId('product_id')->nullable()->after('activity_id')->constrained()->nullOnDelete();

            // Make activity_id nullable (so it can be purely for a product)
            $table->unsignedBigInteger('activity_id')->nullable()->change();
        });

        // 2. Update the Responses table
        Schema::table('activity_form_responses', function (Blueprint $table) {
            // Add the location string
            $table->string('location')->nullable()->after('id');

            // Add product_id (nullable)
            $table->foreignId('product_id')->nullable()->after('activity_id')->constrained()->nullOnDelete();

            // Make activity_id nullable
            $table->unsignedBigInteger('activity_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_form_elements', function (Blueprint $table) {
            $table->dropColumn(['location', 'product_id']);
            // We cannot easily revert activity_id to non-nullable if nulls exist,
            // but for a strict rollback:
            // $table->unsignedBigInteger('activity_id')->nullable(false)->change();
        });

        Schema::table('activity_form_responses', function (Blueprint $table) {
            $table->dropColumn(['location', 'product_id']);
        });
    }
};
