<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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

        $transaction = Transaction::find($transactionID);
        Cache::put("ACTIVE-TRANSACTION-ID", $transactionID);
        return redirect()->route("kiosk.printPreview", ['transaction' => $transaction]);
    }

    public function printPreview(Request $request, Transaction $transaction) {
        return view('pages.kiosk.print-preview', compact('transaction'));
    }

    public function payment(Request $request, Transaction $transaction) {
        return view('pages.kiosk.payment', compact('transaction'));
    }

    public function pulsePayment(?Transaction $transaction = null) {
        if (is_null($transaction)) {
            $transaction = Transaction::find(Cache::get('ACTIVE-TRANSACTION-ID'));
        }

        DB::transaction(function() use ($transaction) {
            $transaction->increment('amount_collected');
            return $transaction;
        });

        return response()->json(['transaction_id' => $transaction->id], 200);
    }

    public function print(Transaction $transaction) {
        // $response = Http::post('http://127.0.0.1:48250/print', [
        //     "filename" => $transaction->document->url,
        //     "has_color" => $transaction->is_colored,
        //     "page_start" => $transaction->page_start,
        //     "page_end" => $transaction->page_end,
        //     "num_copies" => $transaction->no_copies
        // ]);

        // if ($response->status() == 200) {
        //     return back()->with("succes", "Your print job has been sent.");
        // } else {
        //     $error = $response->json()['message'];
        //     return back()->with("error", "Unknown error: $error");
        // }

        return back()->with("succes", "Your print job has been sent.");
    }

    public function cancelTransaction(Request $request)
    {

        if (Cache::has('cache-current-key')) {
            Transaction::where('uuid',  Cache::get('cache-current-key'))->update(['status' => Transaction::TS_CANCELLED]);
        }
    }
}
