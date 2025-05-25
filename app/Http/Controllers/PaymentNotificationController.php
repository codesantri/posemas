<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller;

class PaymentNotificationController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            $payload = $request->all();
            Log::info('Midtrans Notification Received', $payload);

            $orderId = $payload['order_id'] ?? null;
            $statusCode = $payload['status_code'] ?? null;
            $grossAmount = $payload['gross_amount'] ?? null;
            $signatureKey = $payload['signature_key'] ?? null;

            if (!$orderId || !$statusCode || !$grossAmount || !$signatureKey) {
                Log::warning('Invalid payload received', $payload);
                return response()->json(['message' => 'Invalid payload'], 400);
            }

            $serverKey = config('midtrans.server_key');
            $validSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

            if ($signatureKey !== $validSignature) {
                Log::error('Signature mismatch', [
                    'expected' => $validSignature,
                    'received' => $signatureKey,
                    'order_id' => $orderId,
                ]);
                return response()->json(['message' => 'Invalid signature'], 403);
            }

            $transaction = Transaction::where('invoice', $orderId)->first();

            if (!$transaction) {
                Log::error('Transaction not found for order_id: ' . $orderId);
                return response()->json(['message' => 'Transaction not found'], 404);
            }

            $status = $payload['transaction_status'] ?? 'unknown';
            $transactionTime = $payload['transaction_time'] ?? now();

            Log::info("Processing transaction status update", [
                'order_id' => $orderId,
                'status' => $status,
                'transaction_time' => $transactionTime,
            ]);

            switch ($status) {
                case 'settlement':
                case 'capture':
                    $transaction->status = 'success';
                    break;

                case 'pending':
                    $transaction->status = 'pending';
                    break;

                case 'expire':
                    $transaction->status = 'expired';
                    break;

                case 'cancel':
                case 'deny':
                    $transaction->status = 'failed';
                    break;

                default:
                    Log::warning('Unknown transaction status received: ' . $status);
                    $transaction->status = 'pending'; // fallback
                    break;
            }

            $transaction->transaction_date = $transactionTime;
            $transaction->save();

            Log::info("Transaction updated successfully", [
                'invoice' => $transaction->invoice,
                'status' => $transaction->status
            ]);

            return response()->json(['message' => 'Transaction updated successfully']);
        } catch (\Exception $e) {
            Log::error('Payment notification error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['message' => 'Internal Server Error'], 500);
        }
    }
}
