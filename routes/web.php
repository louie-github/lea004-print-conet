<?php

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
use App\Http\Controllers\KioskController;

Route::get('/welcome', function () {
	return view('welcome');
});

Route::get('/pdf-viewer/{id}', [DocumentController::class, 'pdfViewer'])->name('pdf.viewer');

Route::redirect('/', '/dashboard');
Route::redirect('/home', '/dashboard');

Route::middleware(['guest'])->group(function () {
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
	Route::prefix('kiosk')->controller(KioskController::class)
		->middleware(['admin'])->group(function () {
			Route::redirect('/', '/kiosk/qr');
			Route::get('/qr', 'indexQR')->name('index.kiosk');
			Route::post('/cancelled', 'cancelTransaction')->name('kiosk.cancelled');
			Route::get('/pin', 'pinInput')->name('content.kiosk');
			Route::post('/loadTransaction', 'pinTransaction')->name('kiosk.pinTransaction');
			Route::get('/printPreview/{transaction}', 'printPreview')->name('kiosk.printPreview');
			Route::get('/payment/{transaction}', 'payment')->name('kiosk.payment');
			Route::get('/print/{transaction}', 'print')->name('kiosk.print');
		});

	// Non-visible pages
	Route::resource('document', DocumentController::class);

	Route::resource('transaction', TransactionController::class);

	Route::put('price/{id}', [PriceControlller::class, 'update'])->name('price.update');

	// Make sure this is last so that routes do not get overridden.
	Route::get('/{page}', [PageController::class, 'index'])->name('page');
});

// NOTHING FOLLOWS. ALL ROUTES FROM HERE ONWARDS WILL BE OVERRIDEN by
// the 'page' route.