<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Transaction;
use Illuminate\Http\Request;

class PrinterController extends Controller
{
    public function printInvoicePurchase($inv)
    {
        $invoice = Purchase::where('invoice', $inv)->first();
        // dd($invoice);
        return view('prints.invoice-purchase', ['invoice' => $invoice]);
    }

    public function printInvoiceSale($inv)
    {
        $invoice = Transaction::where('invoice', $inv)->first();
        return view('prints.invoice-sale', ['invoice' => $invoice]);
    }
}
