<?php

use App\Http\Controllers\KioskController;
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

/**
 * @todo 
 * 
 * 1. add no of copies in --done
 * ADMIN
 * 1. setup admin account
 * 2. update migration admin
 * 3. dashboard admin
 *    1. view all transaction & document- done
 *    2. view all users - done
 *    3. view total sales - done
 *    4. update print price - done
 * 
 * KIOSK
 * 1. QR Page --> token identification
 * 2. Preview document and Prices
 * 3. Payment page (with print trigger) 
 * 
 * API
 * 1. create api to send print details --> post  to printlab
 * 2. api for receiving the print status and payment status
 * 3. activity logs
 * 4. error handling
 * 
 * 
 */


use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\ResetPassword;
use App\Http\Controllers\ChangePassword;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\PriceControlller;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

Route::get('/welcome', function () {
    return view('welcome');
});
Route::redirect('/kiosk', '/kiosk/qr');
Route::get('/kiosk/qr', [KioskController::class, 'indexQR'])->name('index.kiosk');
Route::get('/kiosk/content',[KioskController::class,'loadContent'])->name('content.kiosk');
Route::get('/pdf-viewer/{id}', [DocumentController::class, 'pdfViewer'])->name('pdf.viewer');
Route::post('/kiosk/cancelled', [KioskController::class, 'cancelTransaction'])->name('kioask.cancelled');

Route::get('/', function () {return redirect('/dashboard');})->middleware('auth');
	Route::get('/register', [RegisterController::class, 'create'])->middleware('guest')->name('register');
	Route::post('/register', [RegisterController::class, 'store'])->middleware('guest')->name('register.perform');
	Route::get('/login', [LoginController::class, 'show'])->middleware('guest')->name('login');
	Route::post('/login', [LoginController::class, 'login'])->middleware('guest')->name('login.perform');
	Route::get('/reset-password', [ResetPassword::class, 'show'])->middleware('guest')->name('reset-password');
	Route::post('/reset-password', [ResetPassword::class, 'send'])->middleware('guest')->name('reset.perform');
	Route::get('/change-password', [ChangePassword::class, 'show'])->middleware('guest')->name('change-password');
	Route::post('/change-password', [ChangePassword::class, 'update'])->middleware('guest')->name('change.perform');
	Route::get('/dashboard', [HomeController::class, 'index'])->name('home')->middleware('auth');
Route::group(['middleware' => 'auth'], function () {
	Route::get('/virtual-reality', [PageController::class, 'vr'])->name('virtual-reality');
	Route::get('/rtl', [PageController::class, 'rtl'])->name('rtl');
	Route::get('/profile', [UserProfileController::class, 'show'])->name('profile');
	Route::post('/profile', [UserProfileController::class, 'update'])->name('profile.update');
	Route::get('/profile-static', [PageController::class, 'profile'])->name('profile-static'); 
	Route::get('/sign-in-static', [PageController::class, 'signin'])->name('sign-in-static');
	Route::get('/sign-up-static', [PageController::class, 'signup'])->name('sign-up-static'); 
	Route::get('/{page}', [PageController::class, 'index'])->name('page');
	Route::post('logout', [LoginController::class, 'logout'])->name('logout');

	//documents
	Route::resource('document',DocumentController::class);
	Route::get('/kiosk/process',[KioskController::class,'kioskCachedRedirect'])->name('cache.kiosk');

	Route::resource('transaction', TransactionController::class);

	//price
	Route::put('price/{id}',[PriceControlller::class, 'update'])->name('price.update');
});