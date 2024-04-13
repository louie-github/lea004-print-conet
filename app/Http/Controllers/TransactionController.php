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
            $printPages = explode('-', $request->page_range);
            $pageStart = $printPages[0];
            $pageEnd = $printPages[1];
            $numPages =  abs(($pageEnd - $pageStart)) + 1;

            // new transaction
            $transaction = Transaction::create([
                'status' => Transaction::TS_PENDING,
                'user_id' => auth()->user()->id,
                'document_id' => $request->document_id,
                'total_pages' => $numPages,
                'page_start' => $pageStart,
                'page_end' => $pageEnd,
                'total_pages' => $numPages,
                'amount_to_be_paid' => $request->total_amount  * $request->no_copies,
                'amount_collected' => 0,
                'is_colored' => $request->color === 'colored' ? 1 : 0,
                'no_copies' => $request->no_copies,
            ]);

            return compact('transaction');
        });

        $transaction = $dbTransaction['transaction'];

        // Generate a unique 6-digit PIN.
        $pin = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        while (Cache::has("PIN-$pin")) {
            $pin = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        }
        $pinDigits = str_split($pin);

        Cache::put("PIN-$pin", $transaction->id, 15 * 60);

        return back()->with('pinDigits', $pinDigits);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $transaction = Transaction::find($id);
        if (is_null($transaction)) {
            return response()->json(
                [
                    'error' => 'No Transaction Found.',
                    'response' => 404
                ],
                404
            );
        }
        else {
            return response()->json(
                [
                    'response' => 200,
                    'transaction' => $transaction,
                ]
            );
        }
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
