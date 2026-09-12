<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_location_id')
                ->constrained('inventory_locations')
                ->cascadeOnDelete();
            $table->foreignId('order_id')
                ->nullable()
                ->constrained('orders')
                ->nullOnDelete();
            $table->unsignedInteger('quantity');
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['inventory_location_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_reservations');
    }
};