<?php

namespace App\Http\Controllers;

use Midtrans\Notification;
use Illuminate\Http\Request;
use App\Models\Purchase;
use Illuminate\Support\Facades\Log;

class PaymentNotificationController extends Controller
{
    public function purchaseNotification(Request $request, $inv)
    {
        // Cari purchase berdasarkan invoice
        $purchase = Purchase::where('invoice', $inv)->first();

        if (!$purchase) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        try {
            $notif = new Notification();
            $transaction = $notif->transaction_status ?? 'pending';
            $type = $notif->payment_type ?? null;
            $fraud = $notif->fraud_status ?? null;
            switch ($transaction) {
                case 'capture':
                    if ($type === 'credit_card') {
                        if ($fraud === 'challenge') {
                            $purchase->status = 'pending';
                        } else {
                            $purchase->status = 'success';
                        }
                    }
                    break;

                case 'settlement':
                    $purchase->status = 'success';
                    break;

                case 'pending':
                    $purchase->status = 'pending';
                    break;

                case 'deny':
                case 'expire':
                case 'cancel':
                    $purchase->status = 'failed';
                    break;

                default:
                    $purchase->status = 'pending';
                    break;
            }

            $purchase->save();

            return response()->json(['message' => 'Purchase updated', 'status' => $purchase->status]);
        } catch (\Exception $e) {
            Log::error('Midtrans Purchase Notification Error: ' . $e->getMessage());

            return response()->json(['message' => 'Error processing notification'], 500);
        }
    }
}
