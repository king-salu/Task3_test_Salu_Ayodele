<?php

use App\Http\Controllers\TwitterController;
use App\Http\Controllers\XController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('home');
});

Route::get('/home', function () {
    return view('home');
});

Route::post('/v1/auth/twitter/reverse', [TwitterController::class, 'getToken']);
// Route::get('/v1/auth/twitter/callback', [TwitterController::class, 'handleCallback']);
// Route::get('/v1/auth/twitter', [TwitterController::class, 'handleRedirect']);

Route::get('/v1/auth/twitter', [XController::class, 'redirectToTwitter'])->name('twitter.redirect');
Route::get('/v1/auth/twitter/callback', [XController::class, 'handleTwitterCallback'])->name('twitter.callback');

Route::get('/v1/api/twitter/login', [TwitterController::class, 'redirectToTwitter']);
// Route::get('/v1/auth/twitter/callback', [TwitterController::class, 'handleTwitterCallback']);
