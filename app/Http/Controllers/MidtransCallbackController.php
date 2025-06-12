<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Midtrans\Notification;

class MidtransCallbackController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('Midtrans callback payload:', $request->all());

        $orderId = explode('-', $request->order_id)[1];
        $status = $request->transaction_status;
        $paymentType = $request->payment_type;

        $order = Orders::find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Order tidak ditemukan'], 404);
        }

        if ($status === 'settlement') {
            $order->payment_status = 'paid';
        } elseif ($status === 'pending') {
            $order->payment_status = 'pending';
        } elseif (in_array($status, ['cancel', 'deny', 'expire'])) {
            $order->payment_status = 'cancelled';
        }

        $order->payment_method = $paymentType;
        $order->save();

        return response()->json(['message' => 'Notifikasi berhasil diproses']);
    }
}
