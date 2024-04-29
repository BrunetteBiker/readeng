<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Library;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LibraryController extends Controller
{

    function add(Request $request)
    {
        $token = $request->get("token");

        $book = Book::where("token", $token)->first();

        if (is_null($book)) {
            return [
                "type" => "warning",
                "msg" => "Kitab tapilmadi"
            ];
        }

        $library = Library::where([
            "customer_id" => Auth::id(),
            "book_id" => $book->id
        ])->first();

        if (!is_null($library)) {
            return [
                "type" => "info",
                "msg" => "Kitab elave olunmusdur"
            ];
        }


    }

    function search()
    {

    }


}
