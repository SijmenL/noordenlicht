<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('unit_base_price', 10, 2)->default(0)->after('unit_price');
            $table->decimal('unit_vat', 10, 2)->default(0)->after('unit_base_price'); // Type 1
            $table->decimal('unit_discount_percentage', 10, 2)->default(0)->after('unit_vat'); // Type 4 (Sum of percentages)
            $table->decimal('unit_discount_amount', 10, 2)->default(0)->after('unit_discount_percentage'); // Type 2
            $table->decimal('unit_extra', 10, 2)->default(0)->after('unit_discount_amount'); // Type 3
            $table->json('price_metadata')->nullable()->after('unit_extra'); // To store names for the view loops
        });
    }

    public function down()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn([
                'unit_base_price',
                'unit_vat',
                'unit_discount_percentage',
                'unit_discount_amount',
                'unit_extra',
                'price_metadata'
            ]);
        });
    }
};
