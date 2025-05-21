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
    public ?int $total = 0;

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
    }

    public function getTitle(): string|Htmlable
    {
        return 'Pembayaran';
    }

    public function getHeading(): string|Htmlable
    {
        return "";
    }

    public function mount($inv): void
    {
        $this->loadTransaction($inv);
        $this->form->fill();
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
        $this->total = $this->transaction->total_amount;
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
                // Section::make([
                //     TextInput::make('customer')
                //         ->label('Nama Pelanggan')
                //         ->prefixIcon('heroicon-m-identification')
                //         ->disabled()
                //         ->default($this->transaction->customer->name . ' - ' . $this->transaction->customer->nik ?? 'Unknown'),
                //     TextInput::make('cash')
                //         ->label('Nominal Pembayaran')
                //         ->prefix('Rp.')
                //         ->required()
                //         ->placeholder('Masukkan nominal')
                //         ->inputMode('decimal')
                //         ->live()
                //         ->afterStateUpdated(function ($state, $set) {
                //             // Auto convert to integer (hapus titik, convert string ke int)
                //             $cash = (int) str_replace('.', '', $state);

                //             // Ambil diskon dari data form
                //             $discount = (int) str_replace('.', '', $this->data['discount'] ?? '0');

                //             // Hitung total bayar setelah diskon
                //             $totalAfterDiscount = $this->total - $discount;

                //             // Hitung kembalian (change)
                //             $change = max(0, $cash - $totalAfterDiscount);

                //             // Set ke field 'change' dengan format rupiah
                //             $set('change', number_format($change, 0, ',', '.'));
                //         })
                //         ->extraAttributes([
                //             'x-data' => '{}',
                //             'x-init' => <<<JS
                //                 \$el.addEventListener('input', function(e) {
                //                     let value = e.target.value.replace(/[^\\d]/g, '');
                //                     if (value === '') return;
                //                     value = new Intl.NumberFormat('id-ID').format(value);
                //                     e.target.value = value;
                //                 });
                //             JS,
                //             'class' => 'py-2 w-full rounded-lg',
                //             'style' => 'font-size: 3rem; font-weight: bold;',
                //         ]),

                //     Radio::make('payment_method')
                //         ->label('Metode Pembayaran')
                //         ->required()
                //         ->options([
                //             'cash' => 'Tunai',
                //             'online' => 'Transfer'
                //         ])
                //         ->default('cash')
                //         ->inline()
                //         ->live(),
                // ]),

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
                                ])->render()
                            );
                        }),
                ]),

                Section::make([
                    Placeholder::make('orders')
                        ->label('')
                        ->content(function () {
                            return new HtmlString(
                                view('filament.pages.shop.sale.checkout.detail', [
                                    'state' => $this->transaction,
                                    'discount' => $this->totalDiscount,
                                    'change' => $this->totalChange,
                                    'total' => $this->total,
                                ])->render()
                            );
                        }),
                ]),
            ])->from('md'),
        ];
    }
}
