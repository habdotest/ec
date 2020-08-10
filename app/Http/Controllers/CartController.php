<?php

namespace App\Http\Controllers;

use App\Product;
use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $mightAlsoLike = Product::mightAlsoLike()->get();
        
        return view('cart', ['mightAlsoLike' => $mightAlsoLike]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Check if item's already in Cart
        $duplicates = Cart::search(function($cartItem, $rowId) use ($request) {
            return $cartItem->id === $request->id;
        });

        if ($duplicates->isNotEmpty()) {
            return back()->with('success-message', 'Item is already in Cart!');
        }

        // If item is in Wishlist
        if ($request->has('row_id')) {
            // Remove it from Wishlist
            Cart::instance('wishlist')->remove($request->row_id);
        }

        // Add to Cart
        Cart::instance('default')->add($request->id, $request->name, 1, $request->price)
            ->associate('App\Product');

        return redirect()->route('cart.index')->with('success-message', 'Item was added to Cart');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $rowId
     * @return \Illuminate\Http\Response
     */
    public function destroy($rowId)
    {
        Cart::remove($rowId);

        return back()->with('success-message', 'Item was removed from Cart!');
    }
}