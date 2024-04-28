<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Smalot\PdfParser\Parser;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function countPdfPages($filePath)
    {
        $parser = new Parser();
        $pdf = $parser->parseFile($filePath);
        return count($pdf->getPages());
    }
}
