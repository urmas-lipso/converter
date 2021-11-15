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

Route::get('/ping', function () {
    return response()->json(["message" => "start working"]);
});

Route::post('/json2csv', [\App\Http\Controllers\WorkerController::class, 'json2csv']);
Route::post('/csv2json', [\App\Http\Controllers\WorkerController::class, 'csv2json']);

