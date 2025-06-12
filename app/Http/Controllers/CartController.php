<?php

namespace App\Http\Controllers;

use App\Models\Carts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function index()
    {
        $carts = Carts::with('product')->where('user_id', Auth::id())->get();
        return response()->json(['cart' => $carts]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Carts::where('user_id', Auth::id())
            ->where('product_id', $request->product_id)
            ->first();

        if ($cart) {
            $cart->quantity += $request->quantity;
            $cart->save();
        } else {
            $cart = Carts::create([
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
            ]);
        }

        return response()->json(['message' => 'Produk ditambahkan ke keranjang', 'cart' => $cart]);
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Carts::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $cart->update(['quantity' => $request->quantity]);

        return response()->json(['message' => 'Jumlah diperbarui', 'cart' => $cart]);
    }

    public function destroy($id)
    {
        $cart = Carts::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $cart->delete();

        return response()->json(['message' => 'Produk dihapus dari keranjang']);
    }

    public function clear()
    {
        Carts::where('user_id', Auth::id())->delete();
        return response()->json(['message' => 'Keranjang dikosongkan']);
    }
}
