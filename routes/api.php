<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get("test", [\App\Http\Controllers\BookController::class, "demo"]);


Route::prefix("user")->group(function () {

    Route::post("demo", [\App\Http\Controllers\UserController::class, "demo"]);
    Route::post("search", [\App\Http\Controllers\UserController::class, "search"]);
    Route::post("create", [\App\Http\Controllers\UserController::class, "create"]);
    Route::post("modify/{id}", [\App\Http\Controllers\UserController::class, "modify"]);
    Route::post("reset-password/{id}", [\App\Http\Controllers\UserController::class, "resetPassword"]);
    Route::post("block/{id}", [\App\Http\Controllers\UserController::class, "block"]);
    Route::post("unblock/{id}", [\App\Http\Controllers\UserController::class, "unblock"]);

});

Route::prefix("book")->group(function () {

    Route::post("search", [\App\Http\Controllers\BookController::class, "search"]);
    Route::post("create", [\App\Http\Controllers\BookController::class, "create"]);
    Route::post("modify/{id}", [\App\Http\Controllers\BookController::class, "modify"]);


    Route::prefix("image")->group(function () {

        Route::post("upload/{bookId}", [\App\Http\Controllers\BookImageController::class, "upload"]);
        Route::post("set-cover/{imageId}", [\App\Http\Controllers\BookImageController::class, "setCover"]);
        Route::post("delete/{imageId}", [\App\Http\Controllers\BookImageController::class, "delete"]);

    });


});


Route::prefix("bookset")->group(function () {

    Route::prefix("book")->group(function (){

        Route::post("add",[\App\Http\Controllers\BooksetController::class,"addBook"]);
        Route::post("remove",[\App\Http\Controllers\BooksetController::class,"removeBook"]);

    });

});
