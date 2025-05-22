<?php

namespace App\Livewire\Payment;

use Livewire\Component;

class Purchase extends Component
{

    public function mount($invoice) {}

    public function paymentProcess() {}
    public function render()
    {
        return view('livewire.payment.purchase');
    }



    // public function onlinePayment()
    // {
    //     try {
    //         // Validate record exists and has required properties
    //         if (!$this->record || !$this->record->invoice || !$this->record->total_amount) {
    //             throw new \Exception('Invalid purchase record');
    //         }

    //         // Configure Midtrans
    //         \Midtrans\Config::$serverKey = config('midtrans.server_key');
    //         \Midtrans\Config::$isProduction = !config('midtrans.is_sandbox', true);
    //         \Midtrans\Config::$isSanitized = true;
    //         \Midtrans\Config::$is3ds = true;

    //         // Prepare transaction details
    //         $params = [
    //             'transaction_details' => [
    //                 'order_id' => $this->record->invoice,
    //                 'gross_amount' => (int) $this->record->total_amount,
    //             ],
    //             'customer_details' => [
    //                 'first_name' => $this->record->customer->name ?? 'Customer',
    //                 'email' => $this->record->customer->email ?? 'test@mail.com',
    //             ],
    //             'callbacks' => [
    //                 'finish' => route('filament.admin.shop.resources.purchases.index'),
    //             ],
    //             'expiry' => [
    //                 'unit' => 'hours',
    //                 'duration' => 2 // 2 hours expiry
    //             ]
    //         ];

    //         // Get Snap token
    //         $snapToken = \Midtrans\Snap::getSnapToken($params);

    //         // Dispatch payment event
    //         $this->dispatch('start-payment', snapToken: $snapToken);
    //     } catch (Exception $e) {
    //         Notification::make()
    //             ->title('Payment Processing Failed')
    //             ->body($e->getMessage())
    //             ->danger()
    //             ->send();

    //         Log::error('Midtrans Error: ' . $e->getMessage(), [
    //             'record_id' => $this->record->id ?? null,
    //             'invoice' => $this->record->invoice ?? null
    //         ]);
    //     } catch (\Exception $e) {
    //         Notification::make()
    //             ->title('Payment Error')
    //             ->body('An unexpected error occurred while processing your payment.')
    //             ->danger()
    //             ->send();

    //         Log::error('Payment Error: ' . $e->getMessage());
    //     }
    // }

    // public function testPay()
    // {
    //     \Midtrans\Config::$serverKey = "SB-Mid-server-TgHGZ7ztLScrQxw9mu3bRMbP";
    //     \Midtrans\Config::$isProduction = false;
    //     // \Midtrans\Config::$isSanitized = false;
    //     // \Midtrans\Config::$is3ds = true;

    //     $params = [
    //         'transaction_details' => [
    //             'order_id' => $this->record->invoice ?? 'INV-' . uniqid(),
    //             'gross_amount' => (int) $this->record->total_amount,
    //         ],
    //         'item_details' => $this->record->purchaseDetails->map(function ($detail) {
    //             return [
    //                 'id' => $detail->product_id,
    //                 'price' => (int) $detail->subtotal,  // bisa juga subtotal kalau mau total per item
    //                 'quantity' => (int) $detail->quantity,
    //                 'name' => $detail->product->name ?? 'Produk tanpa nama',
    //             ];
    //         })->toArray(),
    //         'customer_details' => [
    //             'first_name' => $this->record->customer->name ?? 'Customer Umum',
    //             'phone' => $this->record->customer->phone ?? '081234567890',
    //         ],
    //     ];



    //     try {
    //         $snapToken = \Midtrans\Snap::getSnapToken($params);
    //         dd($snapToken);
    //         $this->dispatch('paying',  snapToken: $snapToken);
    //     } catch (\Exception $e) {
    //         $this->dispatch('payment-error', ['message' => $e->getMessage()]);
    //     }
    // }


}
