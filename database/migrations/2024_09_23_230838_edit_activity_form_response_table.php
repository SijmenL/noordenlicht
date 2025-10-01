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
        Schema::table('activity_form_responses', function (Blueprint $table) {
            $table->dropForeign(['activity_form_element_id']); // Drop the existing foreign key
            $table->foreign('activity_form_element_id')->references('id')->on('activity_form_elements')->onDelete('cascade'); // Add a new foreign key with cascade delete
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
