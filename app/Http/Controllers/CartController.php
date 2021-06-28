<?php

namespace App\Http\Controllers;

use App\Product;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('cart');
    }

    public function store(Request $request)
    {
        $duplicates = Cart::search(function ($cartItem, $rowId) use ($request) {
            return $cartItem->id === $request->id;
        });

        if ($duplicates->isNotEmpty()) {
            return redirect()->route('cart.index')->with('success_message', 'Item is already in your cart');
        }

        Cart::add($request->id, $request->name, 1, $request->price)->associate('App\Product');
        return redirect()->route('cart.index')->with('success_message', 'Item was added successfully!');
    }

    public function destroy($id)
    {
        Cart::remove($id);
        return back()->with('success_message', 'Item has been removed!');
    }

    /**
     * Switch item for shopping cart to Save for Later
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     *
     */

    public function switchToSaveForLater($id)
    {
        $item = Cart::get($id);

        Cart::remove($id);

        $duplicates = Cart::instance('saveForLater')->search(function ($cartItem, $rowId) use ($id) {
            return $rowId === $id;
        });

        if ($duplicates->isNotEmpty()) {
            return redirect()->route('cart.index')->with('success_message', 'Item is already Saved For Later!');
        }

        Cart::instance('saveForLater')->add($item->id, $item->name, 1, $item->price)
            ->associate('App\Product');


        return redirect()->route('cart.index')->with('success_message', 'Item has been Saved For Later!');
    }
}
