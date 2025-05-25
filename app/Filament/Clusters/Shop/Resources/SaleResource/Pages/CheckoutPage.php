<?php

namespace App\Filament\Clusters\Shop\Resources\SaleResource\Pages;

use App\Models\Sale;
use App\Models\Transaction;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Placeholder;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Filament\Clusters\Shop\Resources\SaleResource;

class CheckoutPage extends Page implements HasForms
{
    use InteractsWithForms;
    protected static string $resource = SaleResource::class;
    protected static string $view = 'filament.pages.shop.sale.checkout.checkout';

    public Sale $sale;
    public Transaction $transaction;
    public ?array $data = [];

    public ?int $cash = 0;
    public ?int $discount = 0;
    public ?int $totalDiscount = 0;
    public ?int $totalChange = 0;
    public ?int $totalPayment = 0;
    public ?int $total = 0;

    public $payment_method;
    protected $queryString = ['payment_method'];

    protected $rules = [
        'payment_method' => 'required|in:cash,online',
        'cash' => 'required_if:payment_method,cash|numeric',
        'discount' => 'nullable|numeric',
    ];

    protected $listeners = ['updatedCash', 'updatedDiscount'];

    public function updatedCash($value): void
    {
        $this->calculateTotal();
    }

    public function updatedDiscount($value): void
    {
        $this->calculateTotal();
    }

    public function calculateTotal(): void
    {
        $this->totalDiscount = $this->discount;
        $totalAfterDiscount = $this->total - $this->totalDiscount;
        $this->totalChange = max(0, $this->cash - $totalAfterDiscount);
        $this->totalPayment = $this->total - $this->totalDiscount;
    }

    public function getTitle(): string|Htmlable
    {
        return 'Pembayaran';
    }

    public function mount($inv): void
    {
        if ($inv) {
            $this->loadSale($inv);
            $this->payment_method = $this->transaction->payment_method ?? 'cash';

            $this->form->fill([
                'payment_method' => $this->payment_method
            ]);
        } else {
            $this->redirect(route('filament.admin.shop.resources.sales.index'));
            return;
        }
    }

    public function loadSale($inv): void
    {
        if (!$inv) {
            $this->redirect(route('filament.admin.shop.resources.sales.index'));
            return;
        }

        $transaction = Transaction::where('invoice', $inv)->first();

        if (!$transaction) {
            Notification::make()
                ->title('Transaksi tidak ditemukan')
                ->danger()
                ->send();
            $this->redirect(route('filament.admin.shop.resources.sales.index'));
            return;
        }

        $sale = Sale::with('saleDetails', 'customer', 'user', 'transaction')
            ->where('transaction_id', $transaction->id)
            ->first();

        if (!$sale) {
            Notification::make()
                ->title('Data penjualan tidak ditemukan')
                ->danger()
                ->send();
            $this->redirect(route('filament.admin.shop.resources.sales.index'));
            return;
        }

        $this->transaction = $transaction;
        $this->sale = $sale;
        $this->total = $this->sale->saleDetails->sum('subtotal');
        $this->totalPayment = $this->total;
        $this->payment_method = $this->transaction->payment_method;
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
                        ->content(function () {
                            return new HtmlString(
                                view('filament.pages.shop.sale.checkout.counter', [
                                    'state' => $this->sale,
                                    'discount' => $this->totalDiscount,
                                    'change' => $this->totalChange,
                                    'total' => $this->total,
                                ])->render()
                            );
                        }),
                    Radio::make('payment_method')
                        ->label('Metode Pembayaran')
                        ->options([
                            'cash' => 'Tunai',
                            'online' => 'Transfer',
                        ])
                        ->default(fn() => $this->payment_method)
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state) {
                            $this->payment_method = $state;
                            $this->paymentMethod($state);
                        })
                ])->heading('Kasir : ' . $this->sale->user->name),

                Section::make([
                    Placeholder::make('orders')
                        ->label('')
                        ->content(function () {
                            return new HtmlString(
                                view('filament.pages.shop.sale.checkout.detail', [
                                    'method' => $this->payment_method,
                                    'state' => $this->sale,
                                    'discount' => $this->totalDiscount,
                                    'change' => $this->totalChange,
                                    'totalPayment' => $this->totalPayment,
                                    'subtotal' => $this->total,
                                ])->render()
                            );
                        }),
                ])->collapsible()->heading($this->transaction->invoice),
            ])->from('md'),
        ];
    }

    public function paymentMethod($method): void
    {
        $this->payment_method = $method;

        // Update payment method in transaction
        $this->transaction->update([
            'payment_method' => $method,
            'total_amount' => $this->totalPayment,
        ]);

        if ($method === 'online') {
            $this->cash = 0;
            $this->totalChange = 0;
        }

        $this->loadSale($this->transaction->invoice);
        $this->calculateTotal();

        Notification::make()
            ->title('Metode Pembayaran Berhasil Diubah')
            ->success()
            ->duration(3000)
            ->send();
    }

    public function paymentProcess()
    {
        if (!$this->transaction->invoice || $this->transaction->status !== 'pending') {
            Notification::make()
                ->title('Transaksi tidak ditemukan')
                ->danger()
                ->duration(3000)
                ->send();
            return;
        }

        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errorMessage = collect($e->validator->errors()->all())->first();

            Notification::make()
                ->title('Gagal Validasi')
                ->body($errorMessage)
                ->danger()
                ->duration(3000)
                ->send();

            return;
        }

        if ($this->cash < $this->totalPayment) {
            Notification::make()
                ->title('Nominal pembayaran kurang dari total yang harus dibayar')
                ->danger()
                ->duration(3000)
                ->send();
            return;
        }

        if ($this->totalDiscount > $this->totalPayment) {
            Notification::make()
                ->title('Diskon Melebihi Total Pembayaran')
                ->danger()
                ->duration(3000)
                ->send();
            return;
        }


        if ($this->payment_method == 'cash') {

            $this->totalChange = $this->cash - ($this->total - $this->discount);
        }

        // Update transaction
        $this->transaction->update([
            'total_amount' => $this->totalPayment,
            'payment_method' => $this->payment_method,
            'status' => 'success',
            'transaction_date' => now(),
        ]);

        // Update sale
        $this->sale->update([
            'cash' => $this->cash,
            'discount' => $this->discount,
            'change' => $this->totalChange,
            'total_amount' => $this->totalPayment,
        ]);

        Notification::make()
            ->title('Pembayaran Berhasil')
            ->success()
            ->duration(3000)
            ->send();
        return redirect()->route('filament.admin.shop.resources.sales.orders');
    }
}
