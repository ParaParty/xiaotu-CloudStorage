<?php

namespace App\Http\Controllers;


use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
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

Route::get('/dd', static function () {
    return dirname('/123/1');
});



Route::get('/', static function () {
    //return view('welcome');
    return redirect()->route('view.index');
});

Route::get('/t', TestController::class);

Route::view('/index', 'index')->name('view.index');

Route::view('/login', 'user.login');
Route::post('/login', [AuthController::class, 'login'])->name('user.login');

Route::view('/register', 'user.register');
Route::post('/register', [AuthController::class, 'register'])->name('user.register');

//Route::view('/t', 'layout.layout');

Route::middleware('auth')->group(static function () {
    Route::view('/home', 'home')->name('view.home');
    Route::get('/logout', [AuthController::class, 'logout'])->name('user.logout');

});


//Route::middleware('auth')->name('sapi.')->group(static function () {
//    Route::prefix('storage')->name('storage.')->group(static function () {
//        Route::any('/getPath', [CloudStorageController::class, 'getPath'])->name('getPath');
//    });
//
//});

