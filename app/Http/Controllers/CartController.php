<?php

namespace App\Http\Controllers;

use App\Models\Carts;
use App\Models\Products;
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

        $product = Products::findOrFail($request->product_id);

        // Cek stok produk
        if ($product->stock < $request->quantity) {
            return response()->json(['message' => 'Stok produk tidak mencukupi'], 400);
        }

        $cart = Carts::where('user_id', Auth::id())
            ->where('product_id', $request->product_id)
            ->first();

        if ($cart) {
            // Cek apakah total quantity melebihi stok
            if ($product->stock < ($cart->quantity + $request->quantity)) {
                return response()->json(['message' => 'Stok produk tidak mencukupi untuk jumlah yang diminta'], 400);
            }

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

    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:carts,id'
        ]);

        // Hapus hanya cart items milik user yang sedang login
        Carts::whereIn('id', $request->ids)
            ->where('user_id', Auth::id())
            ->delete();

        return response()->json([
            'message' => count($request->ids) . ' item berhasil dihapus dari keranjang'
        ]);
    }

    public function clear()
    {
        Carts::where('user_id', Auth::id())->delete();
        return response()->json(['message' => 'Keranjang dikosongkan']);
    }
}
