<?php

namespace App\Filament\Clusters\MenuTransactions\Resources\SaleResource\Pages;

use App\Models\Cart;
use App\Models\Customer;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Grid;
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
use Filament\Infolists\Components\Actions;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Infolists\Components\Actions\Action;

class CartSale extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = \App\Filament\Clusters\MenuTransactions\Resources\SaleResource::class;
    protected static string $view = 'filament.clusters.menu-transactions.resources.sales-resource.cart-sales';

    public Cart $cart;
    public ?array $data = [];
    public ?int $total = 0;
    public $carts;

    public function getTitle(): string|Htmlable
    {
        return 'Daftar Belanja';
    }

    public function getHeading(): string|Htmlable
    {
        return "";
    }

    public function mount(): void
    {
        $this->form->fill();
        $this->refreshCarts();
    }

    public function refreshCarts(): void
    {
        $this->carts = Cart::with(['product', 'product.category'])->latest()->get();
        $this->calculateTotal();
    }

    public function calculateTotal(): void
    {
        $this->total = $this->carts->sum('subtotal');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->statePath('data');
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->state([
                'items' => $this->carts,
                'total' => $this->total
            ])
            ->schema($this->getInfolistSchema());
    }

    public function getFormSchema(): array
    {
        return [
            Split::make([
                // Section::make([
                //     TextInput::make('cash')
                //         ->label('Nominal Pembayaran')
                //         ->prefix('Rp.')
                //         ->required()
                //         ->placeholder('Masukkan nominal')
                //         ->inputMode('decimal')
                //         ->extraAttributes([
                //             'x-data' => '{}',
                //             'x-init' => <<<JS
                //                     \$el.addEventListener('input', function(e) {
                //                         let value = e.target.value.replace(/[^\\d]/g, '');
                //                         value = new Intl.NumberFormat('id-ID').format(value);
                //                         e.target.value = value;
                //                     });
                //                     JS,
                //             'class' => 'py-2 w-full rounded-lg',
                //             'style' => 'font-size: 3rem; font-weight: bold;',
                //         ]),
                //     TextInput::make('change')
                //         ->label('Kembalian')
                //         ->prefix('Rp.')
                //         ->placeholder('Masukkan nominal')
                //         ->inputMode('decimal')
                //         ->default(0)
                //         ->readOnly()
                //         ->extraAttributes([
                //             'x-data' => '{}',
                //             'x-init' => <<<JS
                //                     \$el.addEventListener('input', function(e) {
                //                         let value = e.target.value.replace(/[^\\d]/g, '');
                //                         value = new Intl.NumberFormat('id-ID').format(value);
                //                         e.target.value = value;
                //                     });
                //                     JS,
                //             'class' => 'py-2 w-full rounded-lg',
                //             'style' => 'font-size: 3rem; font-weight: bold;',
                //         ]),
                //     TextInput::make('discount')
                //         ->label('Diskon')
                //         ->prefix('Rp.')
                //         ->placeholder('Masukkan nominal')
                //         ->inputMode('decimal')
                //         ->default(0)
                //         ->extraAttributes([
                //             'x-data' => '{}',
                //             'x-init' => <<<JS
                //                     \$el.addEventListener('input', function(e) {
                //                         let value = e.target.value.replace(/[^\\d]/g, '');
                //                         value = new Intl.NumberFormat('id-ID').format(value);
                //                         e.target.value = value;
                //                     });
                //                     JS,
                //             'class' => 'py-2 w-full rounded-lg',
                //             'style' => 'font-size: 3rem; font-weight: bold;',
                //         ]),
                //     Radio::make('payment_method')
                //         ->label('Metode Pembayaran')
                //         ->required()
                //         ->options([
                //             'cash' => 'Tunai',
                //             'online' => 'Transfer'
                //         ])->default('cash')->inline(),
                // ]),
                Section::make([
                    Select::make('customer_id')
                        ->label('Nama Pelanggan')
                        ->searchable()
                        ->preload()
                        ->options(function () {
                            return Customer::all()->mapWithKeys(function ($customer) {
                                return [$customer->id => "{$customer->name} - {$customer->nik}"];
                            });
                        })
                        ->createOptionForm([
                            TextInput::make('name')
                                ->label('Nama Lengkap')
                                ->prefixIcon('heroicon-m-user')
                                ->required(),

                            TextInput::make('nik')
                                ->label('NIK')
                                ->prefixIcon('heroicon-m-identification')
                                ->required()
                                ->unique(table: 'customers', column: 'nik'),

                            TextInput::make('phone')
                                ->label('Nomor Telepon')
                                ->prefixIcon('heroicon-m-phone')
                                ->tel()
                                ->required()
                                ->unique(table: 'customers', column: 'phone'),

                            TextInput::make('address')
                                ->label('Alamat')
                                ->prefixIcon('heroicon-m-map-pin')
                                ->required(),
                        ])
                        ->createOptionUsing(function (array $data) {
                            $customer = Customer::create($data);

                            Notification::make()
                                ->title("Pelanggan berhasil ditambahkan")
                                ->body("{$customer->name} - {$customer->nik}")
                                ->success()
                                ->send();

                            return $customer;
                        }),
                    Placeholder::make('orders')
                        ->label('Daftar Belanja')
                        ->content(function () {
                            return new HtmlString(
                                view('filament.clusters.menu-transactions.resources.sales-resource.items', [
                                    'state' => $this->carts,
                                    'total' => $this->total,
                                    'actions' => Actions::make([
                                        Action::make('delete')
                                            ->label('Hapus')
                                            ->link()
                                            ->color('danger')
                                            ->requiresConfirmation()
                                            ->action(fn($record) => $this->removeItem($record->id)),
                                    ]),
                                ])->render()
                            );
                        }),
                ]),
            ])->from('md'),
        ];
    }

    public function increment($id)
    {
        $cart = Cart::findOrFail($id);
        $stokMaksimal = $cart->product->stockTotals->total;

        if ($cart->quantity < $stokMaksimal) {
            $cart->quantity += 1;
            $cart->subtotal = $cart->quantity * $cart->buy_price;
            $cart->save();

            $this->refreshCarts();
        } else {
            Notification::make()
                ->title("Stok tidak cukup!")
                ->danger()
                ->duration(3000)
                ->send();
        }
    }

    public function decrement($id)
    {
        $cart = Cart::findOrFail($id);

        if ($cart->quantity > 1) {
            $cart->quantity -= 1;
            $cart->subtotal = $cart->quantity * $cart->buy_price;
            $cart->save();
            $this->refreshCarts();
        } else {
            Notification::make()
                ->title("Minimal Kuantitas adalah 1")
                ->danger()
                ->duration(3000)
                ->send();
        }
    }

    public function removeItem($cartId): void
    {
        Cart::find($cartId)?->delete();
        $this->refreshCarts();
        Notification::make()
            ->title('Item dihapus')
            ->success()
            ->send();
    }


    public function paymentProcess(): void
    {
        $data = $this->form->getState();

        // Cek jika keranjang kosong
        if ($this->carts->isEmpty()) {
            Notification::make()->title('Keranjang kosong!')->danger()->send();
            return;
        }

        // Validasi pembayaran tunai
        if ($data['payment_method'] === 'cash') {
            $cash = (int) str_replace('.', '', $data['cash']);
            $discount = (int) str_replace('.', '', $data['discount']);
            $totalAfterDiscount = $this->total - $discount;

            if ($cash < $totalAfterDiscount) {
                Notification::make()
                    ->title('Pembayaran kurang!')
                    ->body('Uang tidak cukup untuk membayar.')
                    ->danger()
                    ->send();
                return;
            }
        }

        // Create transaction
        try {
            DB::beginTransaction();
            $transaction = \App\Models\Transaction::create([
                'customer_id' => $data['customer_id'],
                'user_id' => Auth::user()->id,
                'total_amount' => $this->total,
                'cash' => $data['payment_method'] === 'cash' ? (int) str_replace('.', '', $data['cash']) : null,
                'change' => $data['payment_method'] === 'cash' ?
                    ((int) str_replace('.', '', $data['cash']) - ($this->total - (int) str_replace('.', '', $data['discount']))) : null,
                'discount' => (int) str_replace('.', '', $data['discount']),
                'payment_method' => $data['payment_method'],
                'transaction_date' => now(),
                'status' => $data['payment_method'] === 'cash' ? 'success' : 'pending',
            ]);

            // Create transaction details
            foreach ($this->carts as $cart) {
                \App\Models\TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $cart->product_id,
                    'quantity' => $cart->quantity,
                    'weight' => $cart->weight,
                    'buy_price' => $cart->buy_price,
                    'subtotal' => $cart->subtotal,
                ]);

                // Update product stock
                $cart->product->stockTotals->decrement('total', $cart->quantity);
            }

            // Clear cart
            Cart::truncate();
            $this->refreshCarts();

            DB::commit();

            Notification::make()
                ->title('Transaksi berhasil!')
                ->success()
                ->send();
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            Log::error('ERROR: ' . $e->getMessage());

            Notification::make()
                ->title('Gagal: ' . $e->getMessage())
                ->danger()
                ->persistent()
                ->send();

            // Jika ingin melihat detail error di development
            if (app()->environment('local')) {
                throw $e;
            }
        }
    }
}
