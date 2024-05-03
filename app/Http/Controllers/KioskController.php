<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

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

    public function pinTransaction(Request $request)
    {
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

    public function printPreview(Request $request, Transaction $transaction)
    {
        $document = $transaction->document;
        return view('pages.kiosk.print-preview', compact('transaction', 'document'));
    }

    public function payment(Request $request, Transaction $transaction)
    {
        $document = $transaction->document;

        // Data used when showing print modal
        $status = $request->query('status', null);
        $message = $request->query('message', null);

        return view('pages.kiosk.payment', compact('transaction', 'document', 'status', 'message'));
    }

    public function pulsePayment(Request $request)
    {
        $transaction = Transaction::find($request->transaction);
        $pulseValue = $request->pulseValue;

        if (is_null($request->transaction)) {
            $transaction = Transaction::find(Cache::get('ACTIVE-TRANSACTION-ID'));
        }
        if (is_null($pulseValue)) {
            $pulseValue = 1;
        }
        // TODO: Add idempotency check

        if (is_null($transaction)) {
            return response()->json(['message' => 'Could not find an active transaction.'], 404);
        }

        DB::transaction(function () use ($transaction, $pulseValue) {
            $transaction->increment('amount_collected', $pulseValue);
            return $transaction;
        });

        return response()->json(['transaction_id' => $transaction->id], 200);
    }

    public function configurePrinting(Request $request) {
        $backendUrl = config('app.backend_url');
        $response = Http::post("$backendUrl/configure", [
            'printer_name' => $request->printerSelect
        ]);
        if ($response->status() === 200) {
            return back()->with('succes', 'Successfully set active printer.');
        } else {
            return back()->with('error', 'An error occurred while setting the active printer.');
        }
    }

    public function print(Request $request)
    {
        $transaction = Transaction::find($request->transactionId);
        $backendUrl = config('app.backend_url');
        $response = Http::post("$backendUrl/print", [
            "file_data" => base64_encode(Storage::disk('public')->get($transaction->document->url)),
            "has_color" => $transaction->is_colored,
            "page_start" => $transaction->page_start,
            "page_end" => $transaction->page_end,
            "num_copies" => $transaction->no_copies
        ]);

        if ($response->status() === 200) {
            $status = "succes";
            $message = "Your print job has been sent.";
        } else {
            $status = "error";
            $message = $response->json()['detail']['message'];
        }

        // Use query parameters instead of flashing the session data
        // here to prevent polling from clearing the flashed message
        return redirect()->route('kiosk.payment', [
            'transaction' => $transaction,
            'status' => $status,
            'message' => $message,
        ]);
    }

    public function cancelTransaction(Request $request)
    {
        if (Cache::has('cache-current-key')) {
            Transaction::where('uuid',  Cache::get('cache-current-key'))->update(['status' => Transaction::TS_CANCELLED]);
        }
    }
}
