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
Route::get('/pdf-viewer/{id}', [DocumentController::class, 'pdfViewer'])->name('pdf.viewer');

Route::redirect('/', '/dashboard');

Route::middleware(['guest'])->group(function() {
	Route::get('/register', [RegisterController::class, 'create'])->name('register');
	Route::post('/register', [RegisterController::class, 'store'])->name('register.perform');
	Route::get('/login', [LoginController::class, 'show'])->name('login');
	Route::post('/login', [LoginController::class, 'login'])->name('login.perform');
	Route::get('/reset-password', [ResetPassword::class, 'show'])->name('reset-password');
	Route::post('/reset-password', [ResetPassword::class, 'send'])->name('reset.perform');
	Route::get('/change-password', [ChangePassword::class, 'show'])->name('change-password');
	Route::post('/change-password', [ChangePassword::class, 'update'])->name('change.perform');
});

Route::middleware(['auth'])->group(function () {
	Route::get('/virtual-reality', [PageController::class, 'vr'])->name('virtual-reality');
	Route::get('/rtl', [PageController::class, 'rtl'])->name('rtl');
	Route::get('/profile', [UserProfileController::class, 'show'])->name('profile');
	Route::post('/profile', [UserProfileController::class, 'update'])->name('profile.update');
	Route::get('/profile-static', [PageController::class, 'profile'])->name('profile-static');
	Route::get('/sign-in-static', [PageController::class, 'signin'])->name('sign-in-static');
	Route::get('/sign-up-static', [PageController::class, 'signup'])->name('sign-up-static');
	Route::post('logout', [LoginController::class, 'logout'])->name('logout');

	Route::get('/dashboard', [HomeController::class, 'index'])->name('home');

	// TODO: Consolidate kiosk routes and handle all via KioskController
	// See PageController for example
	//Route::get('/kiosk/process',[KioskController::class,'kioskCachedRedirect'])->name('cache.kiosk');
	Route::controller(KioskController::class)->middleware(['admin'])->group(function () {
		Route::redirect('/kiosk', '/kiosk/qr');
		Route::get('/kiosk/qr', 'indexQR')->name('index.kiosk');
		Route::post('/kiosk/cancelled', 'cancelTransaction')->name('kiosk.cancelled');
		Route::get('/kiosk/pin', 'pinInput')->name('content.kiosk');
		Route::post('/kiosk/loadTransaction', 'pinTransaction')->name('kiosk.pinTransaction');
		Route::get('/kiosk/printPreview/{transaction}', 'printPreview')->name('kiosk.printPreview');
	});

	// Non-visible pages
	Route::resource('document', DocumentController::class);

	Route::resource('transaction', TransactionController::class);

	Route::put('price/{id}', [PriceControlller::class, 'update'])->name('price.update');

	// Make sure this is last so that routes do not get overridden.
	Route::get('/{page}', [PageController::class, 'index'])->name('page');
});
