<?php

namespace App\Http\Controllers;

use App\Models\Price;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
        /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if(auth()->user()->is_admin){
            $todaySales = Transaction::successTransactionToday()
            ->success()
            ->sum('amount_to_be_paid');

            $totalSales = Transaction::success()
            ->sum('amount_to_be_paid');

            $totalUsers = User::users();
            $todayUsers = User::usersToday();

            $price = Price::first();
            return view('pages.dashboard',compact(
                'totalSales',
                'todaySales',
                'totalUsers',
                'todayUsers',
                'price'

            ));
        }

        // Redirect to the 'documents' route
        return redirect()->route('page', ['page' => 'documents']);
    }
}
