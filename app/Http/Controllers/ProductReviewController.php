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

            $review = ProductReview::create([
                'user_id' => Auth::id(),
                ...$validated
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
}
