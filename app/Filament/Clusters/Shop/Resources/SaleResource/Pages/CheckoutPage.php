<?php

namespace App\Filament\Clusters\Shop\Resources\SaleResource\Pages;

use App\Models\Cart;
use Filament\Actions;
use App\Models\Customer;
use Filament\Forms\Form;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Split;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
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

    public Transaction $transaction;
    public ?array $data = [];

    public ?int $cash = 0;
    public ?int $discount = 0;
    public ?int $totalDiscount = 0;
    public ?int $totalChange = 0;
    public ?int $totalPayment = 0;
    public ?int $total = 0;



    public $payment_method = '';
    protected $queryString = ['payment_method'];


    protected $rules = [
        'payment_method' => 'required|in:cash,online',
        'cash' => 'required_if:payment_method,cash|numeric',
        'discount' => 'nullable|numeric',
    ];

    // Add these lifecycle hooks
    protected $listeners = ['updatedCash', 'updatedDiscount'];

    public function updatedCash($value): void
    {
        // Tidak perlu lagi membersihkan nilai karena sudah dihandle oleh directive
        $this->calculateTotal();
    }

    public function updatedDiscount($value): void
    {
        // Tidak perlu lagi membersihkan nilai karena sudah dihandle oleh directive
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

    // public function getHeading(): string|Htmlable
    // {
    //     return "";
    // }

    public function mount($inv): void
    {
        if ($inv) {
            $this->loadTransaction($inv);
            $this->payment_method = $this->transaction->payment_method ?? 'cash';
            $this->form->fill([
                'payment_method' => $this->payment_method
            ]);
        } else {
            $this->redirect(route('filament.admin.shop.resources.sales.index'));
            return;
        }
    }

    public function loadTransaction($inv): void
    {
        if (!$inv) {
            $this->redirect(route('filament.admin.shop.resources.sales.index'));
            return;
        }

        $transaction = Transaction::with('details', 'customer')->where('invoice', $inv)->first();

        if (!$transaction) {
            Notification::make()
                ->title('Transaksi tidak ditemukan')
                ->danger()
                ->send();
            $this->redirect(route('filament.admin.shop.resources.sales.index'));
            return;
        }
        $this->transaction = $transaction;
        $this->total = $this->transaction->details->sum('subtotal');
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
                                    'state' => $this->transaction,
                                    'discount' => $this->totalDiscount,
                                    'change' => $this->totalChange,
                                    'total' => $this->total,
                                    'payment_method' => $this->payment_method,
                                ])->render()
                            );
                        }),
                    Radio::make('payment_method')
                        ->label('Metode Pembayaran')
                        ->options([
                            'cash' => 'Tunai',
                            'online' => 'Transfer',
                        ])
                        ->default(fn() => $this->payment_method) // Gunakan closure
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state) {
                            $this->payment_method = $state;
                            $this->paymentMethod($state);
                            // $this->dispatch('payment-method-updated', method: $state);
                        })
                ])->heading('Kasir : ' . $this->transaction->user->name),

                Section::make([
                    Placeholder::make('orders')
                        ->label('')
                        ->content(function () {
                            return new HtmlString(
                                view('filament.pages.shop.sale.checkout.detail', [
                                    'state' => $this->transaction,
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
        $this->transaction->update(['payment_method' => $method]);

        if ($method === 'online') {
            $this->cash = 0;
            $this->totalChange = 0;
        }
        $this->loadTransaction($this->transaction->invoice);
        $this->calculateTotal();
        $this->dispatch('refresh');
        Notification::make()
            ->title('Metode Pembayaran Berhasil Diubah')
            ->success()
            ->duration(3000)
            ->send();
    }

    public function paymentProcess()
    {
        // dd($this->totalPayment);
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
            // Ambil error message pertama dari validasi
            $errorMessage = collect($e->validator->errors()->all())->first();

            Notification::make()
                ->title('Gagal Validasi')
                ->body($errorMessage)
                ->danger()
                ->duration(3000)
                ->send();

            return;
        }

        // Lanjut proses jika valid...
        if ($this->payment_method == 'cash') {
            if ($this->cash <  $this->totalPayment) {
                Notification::make()
                    ->title('Nominal pembayaran kurang dari total yang harus dibayar')
                    ->danger()
                    ->duration(3000)
                    ->send();
                return;
            }

            $this->totalChange = $this->cash - ($this->total - $this->discount);
        }

        $this->transaction->update([
            'total_amount' => $this->totalPayment,
            'payment_method' => $this->payment_method,
            'status' => 'success',
            'cash' => $this->cash,
            'discount' => $this->discount,
            'transaction_date' => now(),
        ]);

        Notification::make()
            ->title('Pembayaran Berhasil')
            ->success()
            ->duration(3000)
            ->send();
        return redirect()->route('filament.admin.shop.resources.sales.orders');
    }
}
