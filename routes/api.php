<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('norrison-directo')->get('/ping', function () {
    return response()->json(["message" => "start working"]);
});
Route::middleware('auth:sanctum')->prefix('norrison-directo')->get('/secureping', function () {
    return response()->json(["message" => "is working"]);
});

Route::middleware('auth:sanctum')->prefix('norrison-directo')->get('/secureping', function () {
    return response()->json(["message" => "is working"]);
});


Route::middleware('auth:sanctum')
    ->prefix('norrison-directo')
    ->get('/stocklevel', [\App\Http\Controllers\WorkerController::class, 'stockLevel']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
