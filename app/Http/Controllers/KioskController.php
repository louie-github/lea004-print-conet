<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Psy\Readline\Transient;

class KioskController extends Controller
{
    public function indexQR()
    {
        $transaction = [];
        $reload = false;
        return view('pages.kiosk.index-qr', compact('transaction', 'reload'));
    }

    public function kioskCachedRedirect()
    {

        $cachePinKey = 'pin-' . auth()->user()->id;

        if (!Cache::has($cachePinKey)) {
            //TTL == 15mins
            Cache::put($cachePinKey, Str::randomNumber(6), $seconds = 900);

            // Cache::store('file')->put($cacheKey,  Str::random(32), 900);
        }

        return redirect()->route('page', ['page' => 'documents']);
    }

    public function pinInput()
    {
        return view('pages.kiosk.pin-input');
    }

    public function pinTransaction(Request $request) {
        $pin = $request->pin;

        // Protect against race conditions; try instead of check
        $transactionID = Cache::get("PIN-$pin");

        if (is_null($transactionID)) {
            return back()->with('error', "Invalid PIN (PIN-$pin)");
        }

        Cache::put("ACTIVE-PIN", $pin);
        $transaction = Transaction::find($transactionID);

        return redirect()->route("kiosk.printPreview", ['transaction' => $transaction]);
    }

    public function print(Request $request, Transaction $transaction) {
        return view('pages.kiosk.print', compact('transaction'));
    }

    public function loadContent()
    {
        $activePin =  Cache::get('ACTIVE-PIN');

        $transactionID = Cache::get("PIN-$activePin");

        if(is_null($transactionID)) {
            return response()->json(
                [
                    'error' => 'No Transaction Found.',
                    'response' => 404
                ],
                404
            );
        }

        $transaction = Transaction::currentTransaction($transactionID)
            ->first();

        return response()->json([
            'transactions' => $transaction,
            'response' => 200,
            'url' => $transaction->document->url,
            'page_range' => $transaction->document->page_range
        ]);

    }

    public function cancelTransaction(Request $request)
    {

        if (Cache::has('cache-current-key')) {
            Transaction::where('uuid',  Cache::get('cache-current-key'))->update(['status' => Transaction::TS_CANCELLED]);
        }
    }
}
