<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Carts;
use App\Models\OrderDetails;
use App\Models\Orders;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Orders::with(['details.product'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10); // Gunakan pagination

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    public function store(Request $request, MidtransService $midtrans)
    {
        $user = Auth::user();

        $cartItems = Carts::where('user_id', $user->id)->with('product')->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Keranjang kosong'], 400);
        }

        $totalPrice = 0;
        foreach ($cartItems as $item) {
            $totalPrice += $item->product->price * $item->quantity;
        }

        DB::beginTransaction();
        try {
            $order = Orders::create([
                'user_id' => $user->id,
                'total_price' => $totalPrice,
                'payment_status' => 'pending',
                'payment_method' => $request->payment_method ?? null,
            ]);

            foreach ($cartItems as $item) {
                OrderDetails::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);
            }

            Carts::where('user_id', $user->id)->delete();

            // Create snap token
            $snap = $midtrans->createTransaction($order);

            DB::commit();

            return response()->json([
                'message' => 'Pesanan berhasil dibuat',
                'order' => $order->load('details'),
                'snap_token' => $snap->token,
                'redirect_url' => $snap->redirect_url,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal membuat pesanan', 'error' => $e->getMessage()], 500);
        }
    }

    public function repay(Orders $order, MidtransService $midtrans)
    {
        if ($order->payment_status !== 'pending') {
            return response()->json(['message' => 'Pembayaran sudah dilakukan.'], 400);
        }

        $redirectUrl = $midtrans->createTransactionWithOrder($order);
        return response()->json(['redirect_url' => $redirectUrl]);
    }

    public function show($id)
    {
        $order = Orders::with('details.product')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json(['order' => $order]);
    }
}
