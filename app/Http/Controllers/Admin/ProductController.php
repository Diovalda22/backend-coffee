<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Products::query();

            if ($request->has('name')) {
                $query->where('name', 'like', '%' . $request->name . '%');
            }

            if ($request->has('category')) {
                $query->where('product_category_id', $request->category);
            }

            if ($request->has('promoted') && in_array($request->promoted, [0, 1])) {
                $query->where('is_promoted', $request->promoted);
            }

            $products = $query->with('reviews')->orderBy('created_at', 'desc')->get();

            $now = now();

            $data = $products->map(function ($product) use ($now) {
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

                return [
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
            });

            return response()->json([
                'status' => 'success',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data produk.',
            ], 500);
        }
    }

    public function show(int $id)
    {
        try {
            $product = Products::with(['category', 'reviews.user'])->findOrFail($id);

            $now = now();
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

            $averageRating = round($product->reviews->avg('rating'), 1);
            $reviewCount = $product->reviews->count();

            return response()->json([
                'status' => 'success',
                'data' => [
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
                    'category' => $product->category->name ?? null,
                    'average_rating' => $averageRating,
                    'review_count' => $reviewCount,
                    'reviews' => $product->reviews->map(function ($review) {
                        return [
                            'id' => $review->id,
                            'user' => $review->user->name,
                            'user_id' => $review->user->id,
                            'rating' => $review->rating,
                            'review' => $review->review,
                            'created_at' => $review->created_at->toDateTimeString(),
                        ];
                    }),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Produk tidak ditemukan.',
                'error' => $e->getMessage(),
            ], 404);
        }
    }


    public function store(Request $request)
    {
        try {
            $request->validate([
                'category_id'   => 'nullable|exists:product_categories,id',
                'name'          => 'required|string|max:255',
                'description'   => 'required|string',
                'price'         => 'required|numeric',
                'stock'         => 'required|integer|min:0',
                'image_url'     => 'required|image|mimes:jpg,jpeg,png|max:2048',
                'discount_amount' => 'nullable|numeric|min:0',
                'discount_type' => 'nullable|integer|in:0,1,2',
                'discount_start' => 'nullable|date',
                'discount_end'   => 'nullable|date|after_or_equal:discount_start',
            ]);

            if ($request->hasFile('image_url')) {
                $imagePath = $request->file('image_url')->store('products', 'public');
            } else {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'File gambar tidak ditemukan.',
                ], 422);
            }


            $product = Products::create([
                'product_category_id'   => $request->category_id,
                'name'                  => $request->name,
                'description'           => $request->description,
                'price'                 => $request->price,
                'stock'                 => $request->stock,
                'discount_amount'       => $request->discount_amount,
                'discount_type'         => $request->discount_type,
                'discount_start'        => $request->discount_start,
                'discount_end'          => $request->discount_end,
                'image_url'             => $imagePath,
            ]);

            return response()->json([
                'status'    => 'success',
                'data'      => $product,
            ], 201);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menambahkan produk.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, int $id)
    {
        try {
            $product = Products::findOrFail($id);

            if ($request->hasFile('image_url')) {
                // Hapus gambar lama jika ada
                if ($product->image_url && Storage::disk('public')->exists($product->image_url)) {
                    Storage::disk('public')->delete($product->image_url);
                }

                $imagePath = $request->file('image_url')->store('products', 'public');
            } else {
                $imagePath = $product->image_url;
            }

            $product->update([
                'category_id'           => $request->category_id ?? $product->category_id,
                'name'                  => $request->name ?? $product->name,
                'description'           => $request->description ?? $product->description,
                'price'                 => $request->price ?? $product->price,
                'stock'                 => $request->stock ?? $product->stock,
                'discount_amount'       => $request->discount_amount ?? $product->discount_amount,
                'discount_type'         => $request->discount_type ?? $product->discount_amount,
                'discount_start'        => $request->discount_start ?? $product->discount_amount,
                'discount_end'          => $request->discount_end ?? $product->discount_amount,
                'image_url'             => $imagePath ?? null,
            ]);

            return response()->json([
                'status' => 'success',
                'data'   => $product,
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengubah data produk.',
            ], 500);
        }
    }

    public function setPromoted(int $id)
    {
        try {
            $product = Products::findOrFail($id);
            if (!$product) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Produk tidak ditemukan.',
                ], 404);
            } elseif ($product->is_promoted === 0) {
                $product->update(['is_promoted' => 1]);
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Status promosi produk berhasil diubah.',
                    'is_promoted' => $product->is_promoted,
                ]);
            } else {
                $product->update(['is_promoted' => 0]);
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Status promosi produk berhasil diubah.',
                    'is_promoted' => $product->is_promoted,
                ]);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengubah status promosi produk.',
            ], 500);
        }
    }

    public function delete(int $id)
    {
        try {
            $product = Products::findOrFail($id);
            $product->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Produk berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menghapus produk.',
            ], 500);
        }
    }
}
