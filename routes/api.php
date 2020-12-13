<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Controller;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [Controller::class, 'login']);
Route::post('/register', [Controller::class, 'register']);

Route::middleware(['auth:api'])->group(function () {
    Route::get('/get/categories', [Controller::class, 'get_categories']);
    Route::post('/add/category', [Controller::class, 'add_category']);
    Route::get('/delete/category/{id}', [Controller::class, 'delete_category']);

    Route::get('/get/locations', [Controller::class, 'get_locations']);
    Route::post('add/location', [Controller::class, 'add_location']);
    Route::get('/delete/location/{id}', [Controller::class, 'delete_location']);

    Route::post('/add/event', [Controller::class, 'add_event']);
});

Route::get('/get/events', [Controller::class, 'get_events']);
Route::get('/get/event/{id}', [Controller::class, 'get_event']);
Route::middleware(['auth:api'])->group(function (){
    Route::post('/add/comment/{id}', [Controller::class, 'add_comment']);
});
