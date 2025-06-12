<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    public function createTransaction($order)
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $itemDetails = [];

        foreach ($order->details as $detail) {
            $itemDetails[] = [
                'id' => 'P-' . $detail->product_id,
                'price' => (int) $detail->price,
                'quantity' => $detail->quantity,
                'name' => substr($detail->product->name, 0, 50),
            ];
        }

        $transaction = [
            'transaction_details' => [
                'order_id' => 'ORDER-' . $order->id,
                'gross_amount' => (int) $order->total_price,
            ],
            'customer_details' => [
                'first_name' => $order->user->name,
                'email' => $order->user->email,
            ],
            'item_details' => $itemDetails,
        ];

        return Snap::createTransaction($transaction);
    }
}
