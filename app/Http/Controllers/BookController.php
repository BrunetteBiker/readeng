<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\CoverMaterial;
use App\Models\Genre;
use App\Models\PaperMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BookController extends Controller
{


    function demo()
    {

    }


    function create(Request $request)
    {
        $title = $request->get("title");
        $summary = $request->get("summary");
        $price = $request->get("price");
        $discount = $request->get("discount");
        $coverMaterial = $request->get("cover-material");
        $pages = $request->get("pages");
        $paperMaterial = $request->get("paper-material");
        $stock = $request->get("stock");
        $categoryId = $request->get("category-id");
        $genreId = $request->get("genre-id");


        $vData = $request->collect()->filter();

        $vRules = [
            "title" => "required",
            "summary" => "required",
            "category-id" => "required",
            "genre-id" => "required",
            "price" => "required|numeric",
            "pages" => "required|numeric",
            "cover-material" => "required",
            "paper-material" => "required",
            "stock" => "required|numeric",
        ];
        $vMsgs = [
            "title.required" => "Basliq daxil edilmelidir",
            "summary.required" => "Xulase daxil edilmelidir",
            "category-id.required" => "Kateqoriya daxil edilmelidir",
            "genre-id.required" => "Janr daxil edilmelidir",
            "price.required" => "Qiymet daxil edilmelidir",
            "price.numeric" => "Qiymet duzgun deyil",
            "cover-material.required" => "Uz qabigi materiali daxil edilmelidir",
            "paper-material.required" => "Kagiz materiali daxil edilmelidir",
        ];

        if ($vData->has("discount")) {
            $vRules["discount"] = "numeric";
            $vMsgs["discount.numeric"] = "Endirim faizi duzgun deyil";
        }

        $validator = Validator::make($vData->toArray(), $vRules, $vMsgs);

        if ($validator->fails()) {
            return [
                "type" => "warning",
                "msg" => $validator->errors()->first()
            ];
        }

        $book = new Book();
        $book->title = $title;
        $book->summary = $summary;
        $book->category_id = $categoryId;
        $book->genre_id = $genreId;
        $book->price = $price;
        $book->discount = $discount;
        $book->cover_material_id = $coverMaterial;
        $book->paper_material_id = $paperMaterial;
        $book->pages = $pages;
        $book->stock = $stock;
        $book->for_sale = false;
        $book->save();

        $book->pin = "REB-" . Str::of($book->id)->padLeft(6, 0);
        $book->save();

        return [
            "type" => "success"
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

        $books = new Book();

        foreach ($range as $column => $item) {

            $item = collect($item)->filter();

            if ($item->isNotEmpty()) {

                if ($item->has("min")) {
                    $books = $books->where($column, ">=", $item->get("min"));
                }

                if ($item->has("max")) {
                    $books = $books->where($column, "<=", $item->get("max"));
                }


            }

        }

        foreach ($like as $column => $value) {
            $books = $books->where($column, "like", "%$value%");
        }

        foreach ($whereIn as $column => $array) {
            $books = $books->whereIn($column, $array);
        }

        $count = $books->count();

        $records = $books->take($take)->skip($skip)->orderBy($orderBy->first(), $orderBy->last())->get();

        return [
            "count" => $count,
            "records" => $records
        ];

    }

    function modify(Request $request, $id)
    {
        $book = Book::find($id);
        $title = $request->get("title");
        $summary = $request->get("summary");
        $price = $request->get("price");
        $discount = $request->get("discount");
        $coverMaterial = $request->get("cover-material");
        $pages = $request->get("pages");
        $paperMaterial = $request->get("paper-material");
        $stock = $request->get("stock");
        $categoryId = $request->get("category-id");
        $genreId = $request->get("genre-id");


        $vData = $request->collect()->filter();

        $vRules = [
            "title" => "required",
            "summary" => "required",
            "category-id" => "required",
            "genre-id" => "required",
            "price" => "required|numeric",
            "pages" => "required|numeric",
            "cover-material" => "required",
            "paper-material" => "required",
            "stock" => "required|numeric",
        ];
        $vMsgs = [
            "title.required" => "Basliq daxil edilmelidir",
            "summary.required" => "Xulase daxil edilmelidir",
            "category-id.required" => "Kateqoriya daxil edilmelidir",
            "genre-id.required" => "Janr daxil edilmelidir",
            "price.required" => "Qiymet daxil edilmelidir",
            "price.numeric" => "Qiymet duzgun deyil",
            "cover-material.required" => "Uz qabigi materiali daxil edilmelidir",
            "paper-material.required" => "Kagiz materiali daxil edilmelidir",
        ];

        if ($vData->has("discount")) {
            $vRules["discount"] = "numeric";
            $vMsgs["discount.numeric"] = "Endirim faizi duzgun deyil";
        }

        $validator = Validator::make($vData->toArray(), $vRules, $vMsgs);

        if ($validator->fails()) {
            return [
                "type" => "warning",
                "msg" => $validator->errors()->first()
            ];
        }

        $book->title = $title;
        $book->summary = $summary;
        $book->category_id = $categoryId;
        $book->genre_id = $genreId;
        $book->price = $price;
        $book->discount = $discount;
        $book->cover_material_id = $coverMaterial;
        $book->paper_material_id = $paperMaterial;
        $book->pages = $pages;
        $book->stock = $stock;
        $book->for_sale = false;
        $book->save();

    }


}
