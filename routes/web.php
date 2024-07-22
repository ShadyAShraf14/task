<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;

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
    return view('welcome');
});

Route::get('items', [ItemController::class, 'index'])->name('items.index');
Route::post('items', [ItemController::class, 'store'])->name('items.store');
Route::post('items/{item}', [ItemController::class, 'update'])->name('items.update');
Route::delete('items/{item}', [ItemController::class, 'destroy'])->name('items.destroy');
