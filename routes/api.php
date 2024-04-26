<?php

use App\Http\Controllers\KioskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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

Route::post('/pulsePayment', [KioskController::class, 'pulsePayment'])
	->name('pulsePayment');

Route::get('/printerStatus/{printerName?}', function (?string $printerName = null) {
    $backendUrl = config('app.backend_url');
    if (!is_null($printerName)) {
        return Http::get(
            "$backendUrl/status?" . http_build_query(["printer_name" => $printerName])
        )->json();
    } else {
        return Http::get("$backendUrl/status")->json();
    }
})->name('printerStatus');


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
