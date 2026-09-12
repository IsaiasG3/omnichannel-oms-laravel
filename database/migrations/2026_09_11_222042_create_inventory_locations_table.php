<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
      
        Schema::create('inventory_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();
            $table->string('location_code'); 
            $table->unsignedInteger('quantity_on_hand')->default(0);
            $table->unsignedInteger('quantity_reserved')->default(0);
        
            $table->unsignedInteger('version')->default(0); 
            $table->timestamps();

            $table->unique(['product_id', 'location_code']);
            $table->index('location_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_locations');
    }
};