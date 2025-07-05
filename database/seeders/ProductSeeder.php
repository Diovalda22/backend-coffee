<?php

namespace Database\Seeders;

use App\Models\Products;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Espresso',
                'product_category_id' => 1,
                'description' => 'Kopi hitam pekat tanpa gula',
                'price' => 15000,
                'image_url' => 'espresso.jpg',
                'stock' => 100,
                'discount_type' => 1, // Nominal
                'discount_amount' => 3000,
                'discount_start' => Carbon::now()->subDays(1),
                'discount_end' => Carbon::now()->addDays(7),
            ],
            [
                'name' => 'Cappuccino',
                'product_category_id' => 1,
                'description' => 'Espresso dengan susu dan busa lembut',
                'price' => 20000,
                'image_url' => 'cappuccino.jpg',
                'stock' => 100,
                'discount_type' => 2, // Persentase
                'discount_amount' => 10, // 10%
                'discount_start' => Carbon::now(),
                'discount_end' => Carbon::now()->addDays(5),
            ],
            [
                'name' => 'Teh Tarik',
                'product_category_id' => 2,
                'description' => 'Teh manis dengan susu kental khas Malaysia',
                'price' => 12000,
                'image_url' => 'teh-tarik.jpg',
                'stock' => 100,
                'discount_type' => 0, // Tidak ada diskon
            ],
            [
                'name' => 'Matcha Latte',
                'product_category_id' => 4,
                'description' => 'Teh hijau Jepang dengan susu',
                'price' => 22000,
                'image_url' => 'matcha-latte.jpg',
                'stock' => 100,
                'discount_type' => 2,
                'discount_amount' => 15,
                'discount_start' => Carbon::now()->addDays(2),
                'discount_end' => Carbon::now()->addDays(10),
            ],
            [
                'name' => 'Croissant Coklat',
                'product_category_id' => 5,
                'description' => 'Roti croissant isi coklat lumer',
                'price' => 18000,
                'image_url' => 'croissant.jpg',
                'stock' => 100,
                'discount_type' => 1,
                'discount_amount' => 2000,
                'discount_start' => Carbon::now(),
                'discount_end' => Carbon::now()->addWeek(),
            ],
            [
                'name' => 'Kentang Goreng',
                'product_category_id' => 3,
                'description' => 'Kentang goreng renyah dan gurih',
                'price' => 17000,
                'image_url' => 'kentang-goreng.jpg',
                'stock' => 100,
                'discount_type' => 0,
            ],
        ];

        foreach ($products as $product) {
            Products::create($product);
        }
    }
}
