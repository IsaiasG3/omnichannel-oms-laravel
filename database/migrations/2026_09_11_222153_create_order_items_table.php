<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();
            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();
            $table->foreignId('inventory_location_id')
                ->nullable()
                ->constrained('inventory_locations')
                ->nullOnDelete();
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('unit_price_cents');
            $table->unsignedInteger('subtotal_cents');
            $table->timestamps();

            $table->index('order_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};