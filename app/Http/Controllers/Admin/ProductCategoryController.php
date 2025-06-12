<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductCategoryController extends Controller
{
    public function index()
    {
        try {
            $categories = ProductCategories::whereNull('deleted_at')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data'   => $categories,
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengambil data kategori.',
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $category = ProductCategories::create([
                'name' => $request->name,
            ]);

            return response()->json([
                'status' => 'success',
                'data'   => $category,
            ], 201);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menambahkan kategori.',
            ], 500);
        }
    }

    public function show(int $id)
    {
        try {
            $category = ProductCategories::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data'   => $category,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kategori tidak ditemukan.',
            ], 404);
        }
    }

    public function update(Request $request, int $id)
    {
        try {
            $category = ProductCategories::findOrFail($id);

            $category->update([
                'name' => $request->name ?? $category->name,
            ]);

            return response()->json([
                'status' => 'success',
                'data'   => $category,
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengubah data kategori.',
            ], 500);
        }
    }

    public function delete(int $id)
    {
        try {
            $category = ProductCategories::findOrFail($id);
            $category->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Kategori berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus kategori.',
            ], 500);
        }
    }
}
