<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PaymentController extends Controller
{
    //process payment from module/arduino
    public function store()
    {
        // Get the active PIN from cache
        $activePin = Cache::get('ACTIVE-PIN');
        $transactionID = Cache::get("PIN-$activePin");
    
        // Get the latest transaction with the active PIN in process
        $transaction = Transaction::currentTransaction($transactionID)
            ->first();
    
        // If no transaction is found, return 404
        if (!$transaction) {
            return abort(404);
        }
    
        // Check if a payment record exists for the transaction
        $payment = Payment::firstOrNew(['transaction_id' => $transaction->id]);
    
        // Increment the amount or create a new payment record
        $payment->increment('amount');
    
        return response()->json(['message' => 'Payment processed successfully']);
    }
}
