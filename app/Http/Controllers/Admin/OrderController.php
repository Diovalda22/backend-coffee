<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Orders;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Orders::with(['user', 'details.product'])->latest()->get();

        return response()->json([
            'status' => true,
            'message' => 'Daftar semua pesanan',
            'data' => $orders
        ]);
    }

    public function show($id)
    {
        $order = Orders::with(['user', 'details.product'])->findOrFail($id);

        return response()->json([
            'status' => true,
            'message' => 'Detail pesanan',
            'data' => $order
        ]);
    }
}
