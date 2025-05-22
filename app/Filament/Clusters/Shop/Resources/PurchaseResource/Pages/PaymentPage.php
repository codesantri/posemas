<?php

namespace App\Filament\Clusters\Shop\Resources\PurchaseResource\Pages;

use Midtrans\Snap;
use App\Models\Purchase;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Midtrans\Config as MidtransConfig;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Filament\Clusters\Shop\Resources\PurchaseResource;

class PaymentPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = PurchaseResource::class;
    protected static string $view = 'filament.pages.shop.purchase.payment';
    protected static ?string $title = 'Invoice';
    protected static ?string $breadcrumb = 'Invoice';

    public Purchase $record;
    public ?array $data = [];

    protected $listeners = [
        'payment-status-handler' => 'handlePaymentStatus',
        'payment-popup-closed' => 'handlePopupClosed'
    ];


    public function mount(string $invoice): void
    {
        if (!$invoice) {
            $this->redirect(route('filament.admin.shop.resources.purchases.index'));
            return;
        }

        $this->loadPage($invoice);
        $this->form->fill();
    }

    public function loadPage(string $invoice): void
    {
        $this->record = Purchase::where('invoice', $invoice)->firstOrFail();
        $this->redirectBack($this->record);

        $this->record->status = 'success';
        $this->data['payment_method'] = $this->record->payment_method;
    }

    public function redirectBack(Purchase $record): void
    {
        if ($record->status === 'success') {
            redirect()->route('filament.admin.shop.resources.purchases.index');
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->statePath('data');
    }

    public function getFormSchema(): array
    {
        return [
            Split::make([
                Section::make([
                    Placeholder::make('orders')
                        ->label('')
                        ->content(new HtmlString(
                            view('filament.pages.shop.purchase.detail', [
                                'record' => $this->record,
                            ])->render()
                        )),
                ]),

                Section::make([
                    Placeholder::make('orders')
                        ->label('')
                        ->content(new HtmlString(
                            view('filament.pages.shop.purchase.head')->render()
                        )),

                    TextInput::make('cash')
                        ->label('Pembayaran')
                        ->prefix('Rp.')
                        ->placeholder('Masukkan nominal')
                        ->inputMode('decimal')
                        ->visible(fn($get) => $get('payment_method') === 'cash')
                        ->extraAttributes([
                            'x-data' => '{}',
                            'x-init' => <<<JS
                                \$el.addEventListener('input', function(e) {
                                    let value = e.target.value.replace(/[^\\d]/g, '');
                                    value = new Intl.NumberFormat('id-ID').format(value);
                                    e.target.value = value;
                                });
                            JS,
                            'class' => 'py-2 w-full rounded-lg',
                            'style' => 'font-size: 3rem; font-weight: bold;',
                        ]),

                    Radio::make('payment_method')
                        ->label('Metode Pembayaran')
                        ->options([
                            'cash' => 'Tunai',
                            'online' => 'Transfer',
                        ])
                        ->default($this->data['payment_method'])
                        ->reactive()
                        ->afterStateUpdated(function ($state) {
                            $this->paymentMethod($state);
                        })
                        ->inline(),

                    Placeholder::make('orders')
                        ->label('')
                        ->content(new HtmlString(
                            view('filament.pages.shop.purchase.submit', [
                                'method' => $this->data['payment_method'],
                            ])->render()
                        )),
                ]),
            ])->from('md')
        ];
    }

    public function paymentMethod(string $method): void
    {
        $this->data['payment_method'] = $method;
        $this->record->update(['payment_method' => $method]);

        $this->loadPage($this->record->invoice);

        Notification::make()
            ->title('Metode Pembayaran Berhasil Diubah')
            ->success()
            ->duration(3000)
            ->send();
    }

    public function processPayment(): mixed
    {
        if ($this->data['payment_method'] !== 'cash') {
            return null;
        }

        $cash = (int) str_replace('.', '', $this->data['cash'] ?? '');
        $total = $this->record->total_amount;

        if (empty($cash)) {
            Notification::make()
                ->title('Silakan masukkan jumlah pembayaran')
                ->danger()
                ->send();
            return null;
        }

        if ($cash !== $total) {
            Notification::make()
                ->title('Jumlah pembayaran tidak sesuai')
                ->body("Jumlah uang harus pas: Rp" . number_format($total, 0, ',', '.'))
                ->danger()
                ->persistent()
                ->send();
            return null;
        }

        $this->record->update([
            'status' => 'success',
            'payment_method' => 'cash'
        ]);

        Notification::make()
            ->title('Pembayaran berhasil')
            ->body("Pembayaran berhasil untuk invoice #{$this->record->invoice}")
            ->success()
            ->send();

        return redirect()->route('print.purchase', $this->record->invoice);
    }

    public function paymentOnline(): void
    {
        MidtransConfig::$serverKey = config('midtrans.server_key');
        MidtransConfig::$isProduction = config('midtrans.is_production');
        MidtransConfig::$isSanitized = true;
        MidtransConfig::$is3ds = true;

        $orderId = $this->record->invoice;
        $grossAmount = $this->record->total_amount;

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $this->record->customer->name ?? 'Pelanggan umum',
                'phone' => $this->record->customer->phone ?? '08xxxxx',
            ],
        ];


        try {
            $snapToken = Snap::getSnapToken($params);

            $this->record->update([
                'payment_link' => $snapToken,
            ]);

            $this->dispatch('paying', snapToken: $snapToken);
        } catch (\Exception $e) {
            Log::error('Midtrans Snap Error: ' . $e->getMessage());

            Notification::make()
                ->title('Gagal Membuat Pembayaran')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function handlePaymentStatus(array $result): void
    {
        $status = $result['transaction_status'] ?? 'pending';

        $updateData = ['status' => $status];

        // Additional data to store from Midtrans response
        if (isset($result['payment_type'])) {
            $updateData['payment_method'] = $result['payment_type'];
        }
        if (isset($result['transaction_time'])) {
            $updateData['paid_at'] = $result['transaction_time'];
        }

        $this->record->update($updateData);

        $notification = match ($status) {
            'settlement' => Notification::make()
                ->title('Pembayaran Berhasil')
                ->body('Invoice #' . $this->record->invoice . ' telah dibayar')
                ->success(),

            'pending' => Notification::make()
                ->title('Menunggu Pembayaran')
                ->body('Silakan selesaikan pembayaran untuk invoice #' . $this->record->invoice)
                ->warning(),

            'expire' => Notification::make()
                ->title('Pembayaran Kadaluarsa')
                ->body('Invoice #' . $this->record->invoice . ' telah kadaluarsa')
                ->danger(),

            default => Notification::make()
                ->title('Status Pembayaran: ' . ucfirst($status))
                ->body('Invoice #' . $this->record->invoice)
                ->info()
        };

        $notification->send();
    }

    public function handlePopupClosed(): void
    {
        Notification::make()
            ->title('Pembayaran Dibatalkan')
            ->body('Anda menutup popup pembayaran sebelum menyelesaikan')
            ->warning()
            ->send();
    }
}
