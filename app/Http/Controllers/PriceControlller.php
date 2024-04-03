<?php

namespace App\Http\Controllers;

use App\Models\Price;
use Illuminate\Http\Request;

class PriceControlller extends Controller
{
    public function update(Request $request, $id) {

        $price = Price::findOrFail($id);
        $price->update([
            'black_and_white_price' => $request->black_and_white_price,
            'colored_price' => $request->colored_price,
        ]);

        return back()->with('succes', 'Price updated.');
    }
}
