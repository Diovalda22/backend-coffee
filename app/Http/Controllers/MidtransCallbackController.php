<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use App\Models\Products;
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

        // Tentukan nama metode pembayaran lebih jelas
        $paymentMethodName = strtoupper($paymentType); // default

        if ($paymentType === 'bank_transfer' && isset($request->va_numbers[0]['bank'])) {
            $paymentMethodName = strtoupper($request->va_numbers[0]['bank']); // contoh: BNI, BCA
        }

        if ($paymentType === 'echannel') {
            $paymentMethodName = 'MANDIRI';
        }

        if ($paymentType === 'qris') {
            $paymentMethodName = 'QRIS';
        }

        if ($paymentType === 'gopay') {
            $paymentMethodName = 'GoPay';
        }

        if ($paymentType === 'shopeepay') {
            $paymentMethodName = 'ShopeePay';
        }

        if ($status === 'settlement') {
            $order->payment_status = 'paid';

            // Kurangi stok produk
            foreach ($order->details as $detail) {
                $product = Products::find($detail->product_id);
                if ($product) {
                    $product->stock -= $detail->quantity;
                    $product->save();
                }
            }
        } elseif ($status === 'pending') {
            $order->payment_status = 'pending';
        } elseif (in_array($status, ['cancel', 'deny', 'expire'])) {
            $order->payment_status = 'cancelled';
        }

        $order->payment_method = $paymentMethodName;
        $order->save();

        return response()->json(['message' => 'Notifikasi berhasil diproses']);
    }
}
