<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class KioskController extends Controller
{
    public function indexQR() {
        $transaction = [];
        $reload = false;
        return view('pages.kiosk.index-qr',compact('transaction','reload'));
    }

    public function kioskCachedRedirect() {

        $cacheKey = 'cache-current-key';

        if (!Cache ::has( $cacheKey)) {
            //TTL == 5hours
            Cache::store('file')->put($cacheKey,  Str::random(32), 18000);
        }
      
        return redirect()->route('page', ['page' => 'documents']);
    }

    public function loadContent(Request $request) {
    // Perform any necessary operations to fetch data
        $transaction = Transaction::latest()
        ->first();
        //current cache id
        $currentUuid =  Cache::get('cache-current-key');

    if (Cache::has('cache-current-key') && $transaction->uuid === $currentUuid ) {
        $transaction->where('uuid', $currentUuid)
            ->first();
                
        $data = [
            'transactions' => $transaction,
            'response' => 200,
            'url' => $transaction->document->url
        ];
        return response()->json($data);
    }
       
    return response()->json(
        [
            'error' => 'No Transaction Found.',
            'response' => 404
        ],
         404);
    }

    public function cancelTransaction(Request $request) {

        dd($request);
    }
}
