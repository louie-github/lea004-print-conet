<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Display all the static pages when authenticated
     *
     * @param string $page
     * @return \Illuminate\View\View
     */

    private function pageQuery($page){
        switch ($page) {
            case 'my-documents':
                // Handle home page query
               return Document::paginate(10);
                break;
            case 'tables':
                // Handle home page query
               return Document::paginate(10);
                break;
            case 'billing':
                return Document::paginate(10);
                break;
            case 'tables':
                return Document::paginate(10);
                break;
            case 'user-management':
                return Document::paginate(10);
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
            $data = $this->pageQuery($page);
            return view("pages.{$page}", compact('data'));
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
