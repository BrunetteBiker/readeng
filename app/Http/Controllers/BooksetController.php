<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Bookset;
use App\Models\BooksetItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BooksetController extends Controller
{

    function create(Request $request)
    {
        $title = $request->get("title");
        $summary = $request->get("summary");
        $price = $request->get("price");
        $discount = $request->get("discount", 0);
        $stock = $request->get("stock");

        $validator = Validator::make($request->all(), [
            "title" => "required",
            "summary" => "required",
            "price" => "required|numeric",
            "stock" => "required|numeric",
        ], [
            "title.required" => "Basliq daxil edilmelidir",
            "summary.required" => "Xulase daxil edilmelidir",
            "price.required" => "Qiymet daxil edilmelidir",
            "price.numeric" => "Qiymet duzgun deyil",
            "stock.required" => "Miqdar daxil edilmelidir",
            "stock.numeric" => "Miqdar duzgun deyil",
        ]);


        if ($validator->fails()) {
            return [
                "type" => "warning",
                "msg" => $validator->errors()->first()
            ];
        }

        $bookset = new Bookset();
        $bookset->title = $title;
        $bookset->summary = $summary;
        $bookset->price = $price;
        $bookset->discount = $discount;
        $bookset->stock = $stock;
        $bookset->for_sale = false;
        $bookset->save();

        return [
            "type" => "success",
        ];


    }

    function search(Request $request)
    {
        $like = $request->get("like", []);
        $orderBy = $request->get("order-by", "id|desc");
        $orderBy = Str::of($orderBy)->explode("|")->collect();
        $take = $request->get("take", 25);
        $whereIn = $request->get("where-in", []);
        $skip = $request->get("skip", 0) * $take;
        $range = $request->get("range");

        $booksets = new Bookset();

        foreach ($range as $column => $item) {

            $item = collect($item)->filter();

            if ($item->isNotEmpty()) {

                if ($item->has("min")) {
                    $booksets = $booksets->where($column, ">=", $item->get("min"));
                }

                if ($item->has("max")) {
                    $booksets = $booksets->where($column, "<=", $item->get("max"));
                }


            }

        }

        foreach ($like as $column => $value) {
            $booksets = $booksets->where($column, "like", "%$value%");
        }

        foreach ($whereIn as $column => $array) {
            $booksets = $booksets->whereIn($column, $array);
        }

        $count = $booksets->count();

        $records = $booksets->take($take)->skip($skip)->orderBy($orderBy->first(), $orderBy->last())->get();

        return [
            "count" => $count,
            "records" => $records
        ];
    }

    function modify(Request $request, $id)
    {
        $freshInputs = $request->collect()->filter();
        $bookset = Bookset::find($id);

        $title = $bookset->title;
        $summary = $bookset->summary;
        $price = $bookset->price;
        $discount = $bookset->discount;
        $stock = $bookset->stock;
        $forSale = $bookset->for_sale;

        $bookset->title = $freshInputs->get("title", $title);
        $bookset->summary = $freshInputs->get("summary", $summary);
        $bookset->price = $freshInputs->get("price", $price);
        $bookset->stock = $freshInputs->get("stock", $stock);
        $bookset->discount = $freshInputs->get("discount", $discount);
        $bookset->for_sale = $freshInputs->get("for-sale", $forSale);

        $validator = Validator::make($bookset->toArray(), [
            "price" => "numeric",
            "stock" => "numeric",
            "discount" => "numeric"
        ], [
            "price.numeric" => "Qiymet duzgun deyil",
            "stock.numeric" => "Miqdar duzgun deyil",
            "discount.numeric" => "Endirim duzgun deyil",
        ]);


        if ($validator->fails()) {
            return [
                "type" => "warning",
                "msg" => $validator->errors()->first()
            ];
        }

        $bookset->save();

        return [
            "type" => "success",
            "msg" => "Duzelisler qeyde alindi"
        ];


    }

    function setCoverImage(Request $request, $id)
    {
        $bookset = Bookset::find($id);

        $dir = "public/bookset-covers";
        if ($request->has("cover-image")) {
            $filename = "cover-image-$id." . $request->file("cover-image")->getClientOriginalExtension();
            if ($request->file("cover-image")->storePubliclyAs($dir, $filename)) {

                if (!is_null($bookset->cover_image)) {
                    Storage::delete("$dir/$bookset->cover_image");
                }
                $bookset->cover_image = $filename;
                $bookset->save();
                return [
                    "type" => "success",
                    "msg" => "Duzelisler qeyde alindi"
                ];


            } else {
                return [
                    "type" => "warning",
                    "msg" => "Xeta bas verdi"
                ];
            }
        }

    }

    function addBook(Request $request, $id)
    {

        $books = $request->get("books");
        BooksetItem::whereIn("book_id", $books)->where("id", $id)->delete();
        foreach ($books as $book) {
            $booksetItem = new BooksetItem();
            $booksetItem->bookset_id = $id;
            $booksetItem->book_id = $book;
            $booksetItem->save();
        }

        return [
            "type" => "success"
        ];
    }

    function removeBook(Request $request, $id)
    {
        $books = $request->get("books");
        BooksetItem::whereIn("book_id", $books)->where("id", $id)->delete();
        return [
            "type" => "success"
        ];
    }

}
