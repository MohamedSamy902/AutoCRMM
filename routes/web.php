<?php

use App\Http\Controllers\crmm;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CRMMContoller;
use App\Http\Controllers\AutoCRMMController;

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


// Route::get('/', [CRMMContoller::class, 'makeTable'])->name('index');
// Route::post('/makeTableRequest', [CRMMContoller::class, 'makeTableRequest'])->name('makeTableRequest');
// Route::get('/handel/Folser/Zip', [CRMMContoller::class, 'handelFolserZip'])->name('handelFolserZip');
// Route::get('/download/folders', [CRMMContoller::class, 'downloadFolders'])->name('downloadFolders');
// Route::get('/delete/zip/file', [CRMMContoller::class, 'deleteZipFile'])->name('deleteZipFile');


Route::get('/', [AutoCRMMController::class, 'viewBlade'])->name('index');
Route::post('/makeTableRequest', [AutoCRMMController::class, 'makeCRMM'])->name('makeTableRequest');
Route::get('/handel/Folser/Zip', [AutoCRMMController::class, 'handelFolserZip'])->name('handelFolserZip');
Route::get('/download/folders', [AutoCRMMController::class, 'downloadFolders'])->name('downloadFolders');
Route::get('/delete/zip/file', [AutoCRMMController::class, 'deleteZipFile'])->name('deleteZipFile');

