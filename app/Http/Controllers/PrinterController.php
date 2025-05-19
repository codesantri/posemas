<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;

class PrinterController extends Controller
{
    public function printInvoicePurchase($inv)
    {
        $invoice = Purchase::where('invoice', $inv)->first();
        // dd($invoice);
        return view('prints.invoice-purchase', ['invoice' => $invoice]);
    }
}
