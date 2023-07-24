<?php

use App\Http\Controllers\crmm;
use App\Http\Controllers\CRMMContoller;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::view('/', 'me.form')->name('index');
Route::post('/makeTableRequest', [CRMMContoller::class, 'makeTableRequest'])->name('makeTableRequest');
