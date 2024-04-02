<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Document $document)
    {
        $dbTransaction = DB::transaction(function () use ($document, $request) {
            // Do we really need this?
            $document->update([
                'page_range' => $request->page_range_slider,
            ]);

            $printPages = explode('-', $request->page_range);
            $numPages =  abs(($printPages[0] -  $printPages[1])) + 1;

            // new transaction
            $transaction = Transaction::create([
                'status' => Transaction::TS_PENDING,
                'user_id' => auth()->user()->id,
                'document_id' => $request->document_id,
                'total_pages' => $numPages,
                'amount_to_be_paid' => $request->total_amount  * $request->no_copies,
                'is_colored' => $request->color === 'colored' ? 1 : 0,
                'no_copies' => $request->no_copies,
            ]);

            return compact('transaction');
        });

        // TODO: Schedule task to set transaction as EXPIRED after
        // 15 minutes.
        $transaction = $dbTransaction['transaction'];

        // Generate a unique 6-digit PIN.
        $pin = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        while (Cache::has("PIN-$pin")) {
            $pin = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        }
        Cache::put("PIN-$pin", $transaction->id, 15 * 60);

        return back()->with('succes', "PIN is: $pin");
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
