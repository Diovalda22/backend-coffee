<?php

namespace Database\Seeders;

use App\Models\ProductCategories;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Kopi'],
            ['name' => 'Teh'],
            ['name' => 'Makanan Ringan'],
            ['name' => 'Non-Kopi'],
            ['name' => 'Roti & Kue'],
        ];

        foreach ($categories as $category) {
            ProductCategories::create($category);
        }
    }
}
