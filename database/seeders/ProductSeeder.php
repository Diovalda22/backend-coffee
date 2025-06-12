<?php

namespace Database\Seeders;

use App\Models\Products;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
            ],
            [
                'name' => 'Cappuccino',
                'product_category_id' => 1,
                'description' => 'Espresso dengan susu dan busa lembut',
                'price' => 20000,
                'image_url' => 'cappuccino.jpg',
            ],
            [
                'name' => 'Teh Tarik',
                'product_category_id' => 2,
                'description' => 'Teh manis dengan susu kental khas Malaysia',
                'price' => 12000,
                'image_url' => 'teh-tarik.jpg',
            ],
            [
                'name' => 'Matcha Latte',
                'product_category_id' => 4,
                'description' => 'Teh hijau Jepang dengan susu',
                'price' => 22000,
                'image_url' => 'matcha-latte.jpg',
            ],
            [
                'name' => 'Croissant Coklat',
                'product_category_id' => 5,
                'description' => 'Roti croissant isi coklat lumer',
                'price' => 18000,
                'image_url' => 'croissant.jpg',
            ],
            [
                'name' => 'Kentang Goreng',
                'product_category_id' => 3,
                'description' => 'Kentang goreng renyah dan gurih',
                'price' => 17000,
                'image_url' => 'kentang-goreng.jpg',
            ],
        ];

        foreach ($products as $product) {
            Products::create($product);
        }
    }
}
