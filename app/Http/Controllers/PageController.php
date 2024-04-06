<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\PrinterActivity;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Display all the static pages when authenticated
     *
     * @param string $page
     * @return \Illuminate\View\View
     */

    private function isAdmin()
    {
        return auth()->user()->is_admin;
    }

    private function pageQuery($page)
    {
        switch ($page) {
            case 'documents': // this is document page
                $documentsQuery = Document::latest();
                $transactionsQuery = Transaction::latest();

                if ($this->isAdmin()) {
                    $documents = $documentsQuery->simplePaginate(5, ['*'], 'documents');
                    $transactions = $transactionsQuery->simplePaginate(10, ['*'], 'transactions');
                } else {
                    $user_id = auth()->user()->id;
                    $documents = $documentsQuery->where('user_id', $user_id)->simplePaginate(5, ['*'], 'documents');
                    $transactions = $transactionsQuery->where('user_id', $user_id)->simplePaginate(10, ['*'], 'transactions');
                }

                return view("pages.{$page}", compact('documents', 'transactions'));
                break;
            case 'users':
                $users = User::latest()->get();
                return view("pages.{$page}", compact('users'));
                break;
            case 'printer-activities':
                $activities = PrinterActivity::latest()->get();
                return view("pages.printer-activities", compact('activities'));
                break;
            default:
                abort(404);
                // Handle default case if $page doesn't match any of the above cases
                break;
        }
    }

    public function index(string $page)
    {
        if (view()->exists("pages.{$page}")) {
            return $this->pageQuery($page);
        }

        return abort(404);
    }

    public function vr()
    {
        return view("pages.virtual-reality");
    }

    public function rtl()
    {
        return view("pages.rtl");
    }

    public function profile()
    {
        return view("pages.profile-static");
    }

    public function signin()
    {
        return view("pages.sign-in-static");
    }

    public function signup()
    {
        return view("pages.sign-up-static");
    }
}
