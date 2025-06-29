<?php

namespace App\Http\Controllers;

use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class ProductReviewController extends Controller
{
    public function index($productId)
    {
        try {
            $reviews = ProductReview::with('user')
                ->where('product_id', $productId)
                ->latest()
                ->get();

            if ($reviews->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No reviews found for this product',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $reviews
            ]);
        } catch (Exception $e) {
            Log::error('Failed to fetch product reviews: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve product reviews',
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'rating' => 'required|integer|min:1|max:5',
                'review' => 'nullable|string',
            ]);

            $userId = Auth::id();
            $productId = $validated['product_id'];

            // Cek apakah user sudah memberi review untuk produk ini
            $existingReview = ProductReview::where('user_id', $userId)
                ->where('product_id', $productId)
                ->first();

            if ($existingReview) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already reviewed this product. You can only edit or delete your existing review.',
                    'data' => [
                        'existing_review' => $existingReview,
                        'allowed_actions' => ['edit', 'delete']
                    ]
                ], 422);
            }

            // Buat review baru
            $review = ProductReview::create([
                'user_id' => $userId,
                'product_id' => $productId,
                'rating' => $validated['rating'],
                'review' => $validated['review'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Review submitted successfully',
                'data' => $review
            ], 201);
        } catch (Exception $e) {
            Log::error('Failed to submit review: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit review',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'rating' => 'required|integer|min:1|max:5',
                'review' => 'nullable|string',
            ]);

            $userId = Auth::id();

            $review = ProductReview::where('id', $id)
                ->where('user_id', $userId)
                ->firstOrFail();

            $review->update([
                'rating' => $validated['rating'],
                'review' => $validated['review'] ?? $review->review,
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Review updated successfully',
                'data' => $review
            ], 200);
        } catch (Exception $e) {
            Log::error('Failed to update review: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update review',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $userId = Auth::id();

            $review = ProductReview::where('id', $id)
                ->where('user_id', $userId)
                ->firstOrFail();

            $review->delete();

            return response()->json([
                'success' => true,
                'message' => 'Review deleted successfully'
            ], 200);
        } catch (Exception $e) {
            Log::error('Failed to delete review: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete review',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }
}
