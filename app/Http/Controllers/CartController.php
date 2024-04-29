<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{

    function index()
    {
        $items = Cart::where("customer_id", Auth::id())->orderBy("id", "desc")->get();

        return $items;

    }

    function add(Request $request)
    {
        $bookId = $request->get("book-id");
        $booksetId = $request->get("bookset-id");
        $count = $request->get("count");

        $cartItem = new Cart();
        $cartItem->book_id = $bookId;
        $cartItem->bookset_id = $booksetId;
        $cartItem->count = $count;
        $cartItem->customer_id = Auth::id();
        $cartItem->save();

        return [
            "type" => "success"
        ];

    }

    function remove($itemId)
    {
        Cart::find($itemId)->delete();

        return [
            "type" => "success"
        ];
    }


    function pay(Request $request)
    {

    }

}
