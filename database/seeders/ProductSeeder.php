<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 50; $i++) {
            Product::create([
                'item_number' => 'PRD-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'inventory' => rand(0, 100),
                'name' => 'Teszt Termék ' . $i,
                'purchase_price' => rand(100, 10000) / 100,
                'consumer_price' => rand(100, 20000) / 100,
                'service_price' => rand(100, 15000) / 100,
                'retail_price' => rand(100, 18000) / 100,
                'wholesale_price' => rand(100, 16000) / 100,
                'handover_price' => rand(100, 14000) / 100,
                'service_partner_price' => rand(100, 13000) / 100,
                'description' => 'Ez egy teszt termék leírása ' . $i,
                'product_category_id' => null, // vagy véletlenszerű kategória, ha van
                'is_active' => 1,
                'show_in_webshop' => rand(0, 1),
                'show_in_spare_parts_list' => rand(0, 1),
                'is_main_device' => rand(0, 1),
                'attached_device_id' => null,
                'photos' => null,
                'datasheets' => null,
                'notes' => null,
                'low_stock_limit' => rand(0, 10),
                'created_at' => now(),
                'updated_at' => now(),
                'height' => rand(10, 100) / 10,
                'width' => rand(10, 100) / 10,
                'depth' => rand(10, 100) / 10,
                'weight' => rand(10, 100) / 10,
            ]);
        }
    }
}
