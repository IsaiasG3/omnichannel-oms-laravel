<?php

namespace Database\Seeders;

use App\Domain\Inventory\Models\InventoryLocation;
use App\Domain\Inventory\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'test@test.com'],
            ['name' => 'Usuario Demo', 'password' => bcrypt('password'), 'role' => 'customer']
        );

        User::firstOrCreate(
            ['email' => 'almacen@test.com'],
            ['name' => 'Personal de Almacén', 'password' => bcrypt('password'), 'role' => 'warehouse_staff']
        );

        $products = [
            ['sku' => 'SKU-001', 'name' => 'Teclado mecánico', 'price_cents' => 89900, 'stock' => 50],
            ['sku' => 'SKU-002', 'name' => 'Mouse inalámbrico', 'price_cents' => 45000, 'stock' => 30],
            ['sku' => 'SKU-003', 'name' => 'Monitor 27" 144Hz', 'price_cents' => 549900, 'stock' => 10],
            ['sku' => 'SKU-004', 'name' => 'Webcam HD', 'price_cents' => 32000, 'stock' => 20],

        
            ['sku' => 'SKU-005', 'name' => '[STOCK ALTO] USB-C Cable', 'price_cents' => 9900, 'stock' => 5000],
            ['sku' => 'SKU-006', 'name' => '[STOCK BAJO] Edición Limitada', 'price_cents' => 199900, 'stock' => 3],
        ];

        foreach ($products as $data) {
            $product = Product::firstOrCreate(
                ['sku' => $data['sku']],
                ['name' => $data['name'], 'price_cents' => $data['price_cents']]
            );

            InventoryLocation::firstOrCreate(
                ['product_id' => $product->id, 'location_code' => 'CDMX-01'],
                ['quantity_on_hand' => $data['stock']]
            );
        }

        $this->call(OrderDemoSeeder::class);
    }
}