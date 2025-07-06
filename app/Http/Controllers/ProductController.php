<?php

namespace App\Http\Controllers;

use App\Models\Products;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function groupedProducts(Request $request)
    {
        // Mapping kategori_id ke section
        $categorySectionMap = [
            1 => 'Minuman',         // kopi
            2 => 'Minuman',         // teh
            3 => 'Makanan Ringan',  // Makanan ringan
            4 => 'Minuman',         // Non-kopi
            5 => 'Roti',            // Roti dan kopi
            6 => 'Makanan Utama',   // Makanan utama
        ];

        $products = Products::with('reviews')->orderBy('created_at', 'desc')->get();

        $now = now();

        $grouped = [
            'Minuman' => [],
            'Makanan Utama' => [],
            'Roti' => [],
            'Makanan Ringan' => [],
        ];

        foreach ($products as $product) {
            $finalPrice = $product->price;
            if ($product->discount_type && $product->discount_start && $product->discount_end) {
                if ($now->between($product->discount_start, $product->discount_end)) {
                    if ($product->discount_type == 1) {
                        $finalPrice = max($product->price - $product->discount_amount, 0);
                    } elseif ($product->discount_type == 2) {
                        $finalPrice = max($product->price - ($product->price * $product->discount_amount / 100), 0);
                    }
                }
            }

            $section = $categorySectionMap[$product->product_category_id] ?? null;
            if ($section) {
                $grouped[$section][] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'final_price' => $finalPrice,
                    'discount_type' => $product->discount_type,
                    'discount_amount' => $product->discount_amount,
                    'discount_start' => $product->discount_start,
                    'discount_end' => $product->discount_end,
                    'stock' => $product->stock,
                    'image_url' => $product->image_url,
                    'is_promoted' => $product->is_promoted,
                    'product_category_id' => $product->product_category_id,
                    'created_at' => $product->created_at,
                    'average_rating' => round($product->reviews->avg('rating'), 1),
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $grouped,
        ]);
    }
}
