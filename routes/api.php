<?php

use App\Http\Controllers\TwitterController;
use App\Http\Controllers\XController;
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

Route::get('v1/in/token', [TwitterController::class, 'getTokenRough']);

Route::post('v1/twitter/dm', [XController::class, 'handleTwitterDM']);
Route::get('v1/twitter/verify', [XController::class, 'handleTwitterVerification']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
