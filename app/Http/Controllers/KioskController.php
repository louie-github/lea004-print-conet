<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Psy\Readline\Transient;

class KioskController extends Controller
{
    public function indexQR() {
        $transaction = [];
        $reload = false;
        return view('pages.kiosk.index-qr',compact('transaction','reload'));
    }

    public function kioskCachedRedirect() {

        $cachePinKey = 'pin-' . auth()->user()->id;

        if ( !Cache ::has( $cachePinKey)) {
            //TTL == 15mins
            Cache::put($cachePinKey , Str::randomNumber(6), $seconds = 900);

            // Cache::store('file')->put($cacheKey,  Str::random(32), 900);
        }
      
        return redirect()->route('page', ['page' => 'documents']);
    }

    public function loadContent(Request $request) {
    // Perform any necessary operations to fetch data
        $transaction = Transaction::latest()
        ->where('status', Transaction::TS_IN_PROCESS)
        ->first();
        //current cache id
    $currentUuid =  Cache::get('cache-current-key');

        if (Cache::has('cache-current-key') && $transaction?->uuid === $currentUuid ) {
            $transaction->where('uuid', $currentUuid)
                ->first();
                    
            $data = [
                'transactions' => $transaction,
                'response' => 200,
                'url' => $transaction->document->url,
                'page_range' => $transaction->document->page_range
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

        if(Cache::has('cache-current-key')){
            Transaction::where('uuid',  Cache::get('cache-current-key'))->update(['status' =>Transaction::TS_CANCELLED]);
        }

    }
}

