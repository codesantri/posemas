<?php

namespace App\Filament\Clusters\Shop\Resources\SaleResource\Pages;

use App\Models\Sale;
use App\Models\Transaction;
use Filament\Resources\Pages\Page;
use Illuminate\Support\HtmlString;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Filament\Clusters\Shop\Resources\SaleResource;

class PayingPage extends Page
{
    use InteractsWithForms;
    protected static string $resource = SaleResource::class;
    protected static string $view = 'filament.pages.shop.sale.checkout.paying';
    protected static ?string $breadcrumb = 'invoice';

    public Sale $sale;
    public Transaction $transaction;
    public $token = '';
    public ?int $total = 0;
    protected $listeners = ['paymentStatus' => 'payed'];


    public function getTitle(): string|Htmlable
    {
        return '';
    }

    public function getHeading(): string|Htmlable
    {
        return '';
    }

    public function mount($inv): void
    {
        if ($inv) {
            // Cari transaksi berdasarkan invoice
            $this->transaction = Transaction::where('invoice', $inv)->first();

            if (!$this->transaction) {
                $this->redirect(route('filament.admin.shop.resources.sales.index'));
                return;
            }

            // Cari sale berdasarkan transaction_id
            $this->sale = Sale::where('transaction_id', $this->transaction->id)->first();

            if (!$this->sale) {
                $this->redirect(route('filament.admin.shop.resources.sales.index'));
                return;
            }

            $this->total = $this->transaction->total_amount;
            $this->generateSnapToken();
        } else {
            $this->redirect(route('filament.admin.shop.resources.sales.index'));
            return;
        }
    }

    public function generateSnapToken()
    {
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = false;
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        // Build parameters safely
        $params = [
            'transaction_details' => [
                'order_id' => $this->transaction->invoice, // Gunakan invoice dari transaction
                'gross_amount' => $this->total,
            ],
        ];

        try {
            $snapToken = \Midtrans\Snap::getSnapToken($params);
            $this->token = $snapToken;

            // Update payment link di transaction
            $this->transaction->update([
                'payment_link' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/' . $snapToken
            ]);
        } catch (\Exception $e) {
            // Handle error
            Notification::make()
                ->title('Gagal membuat pembayaran')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }


    public function payed($data): void
    {
        $status = $data['status'] ?? null;
        $result = $data['result'] ?? null;

        switch ($status) {
            case 'success':
                Notification::make()
                    ->title('Pembayaran Berhasil')
                    ->success()
                    ->send();
                break;

            case 'pending':
                Notification::make()
                    ->title('Pembayaran Tertunda')
                    ->warning()
                    ->send();
                break;

            case 'error':
                Notification::make()
                    ->title('Terjadi Kesalahan Saat Pembayaran')
                    ->danger()
                    ->send();
                break;

            case 'close':
                Notification::make()
                    ->title('Pembayaran Dibatalkan')
                    ->body('Anda menutup popup pembayaran.')
                    ->danger()
                    ->send();
                break;

            default:
                Notification::make()
                    ->title('Status tidak dikenali')
                    ->danger()
                    ->send();
                break;
        }
        $this->redirect(route('filament.admin.shop.resources.sales.orders'));
    }
}
