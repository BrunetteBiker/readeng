<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BookImageController extends Controller
{
    function upload(Request $request, $bookId)
    {
        $error = false;
        $dir = "public/books/$bookId/images";
        $images = collect([]);
        foreach ($request->file("images") as $index => $file) {
            if ($request->file("images")[$index]->storePublicly($dir)) {
                $images->add($request->file("images")[$index]->hashName());
            } else {
                $error = true;
                break;
            }
        }

        if ($error == true) {
            if ($images->isNotEmpty()) {
                foreach ($images as $image) {
                    Storage::delete("$dir/$image");
                }
            }
        }


        foreach ($images as $item) {
            $image = new Image();
            $image->book_id = $bookId;
            $image->filename = $item;
            $image->is_cover = false;
            $image->save();

        }

        return [
            "type" => "success",
            "msg" => "Sekiller elave olundu"
        ];


    }

    function setCover($id)
    {
        $image = Image::find($id);
        $image->is_cover = true;
        $image->save();

        $images = Image::where("book_id", $image->book_id)->whereNot("id", $id)->update(["is_cover" => false]);

        return [
            "type" => "success",
            "msg" => "Uz qabigi tetbiq edildi"
        ];

    }

    function delete($id)
    {
        $image = Image::find($id);
        $bookId = $image->book_id;
        $dir = "public/books/$bookId/images";

        Storage::delete("$dir/$image->filename");

        $image->delete();

        return [
            "type" => "success"
        ];

    }
}
