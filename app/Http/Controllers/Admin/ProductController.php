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
            $query = Products::query()->whereNull('deleted_at');

            if ($request->has('name')) {
                $query->where('name', 'like', '%' . $request->name . '%');
            }

            if ($request->has('category')) {
                $query->where('category', $request->category);
            }

            $products = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'status' => 'success',
                'data' => $products,
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
            $product = Products::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $product,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Produk tidak ditemukan.',
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
                'category_id'   => $request->category_id,
                'name'          => $request->name,
                'description'   => $request->description,
                'price'         => $request->price,
                'stock'         => $request->stock,
                'image_url'     => $imagePath,
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
                'category_id'   => $request->category_id ?? $product->category_id,
                'name'          => $request->name ?? $product->name,
                'description'   => $request->description ?? $product->description,
                'price'         => $request->price ?? $product->price,
                'stock'         => $request->stock ?? $product->stock,
                'image_url'     => $imagePath ?? null,
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
