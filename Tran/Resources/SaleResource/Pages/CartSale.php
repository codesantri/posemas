<?php

namespace App\Filament\Clusters\Transactions\Resources\SaleResource\Pages;

use App\Models\Cart;
use App\Models\Customer;
use Filament\Forms\Form;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Log;
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

    protected static string $resource = \App\Filament\Clusters\Transactions\Resources\SaleResource::class;
    protected static string $view = 'filament.clusters.transactions.resources.sales-resource.cart-sales';

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

    public function getFormSchema(): array
    {
        return [
            Section::make([
                Select::make('customer_id')
                    ->label('Nama Pelanggan')
                    ->searchable()
                    ->required()
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
                            view('filament.clusters.transactions.resources.sales-resource.items', [
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


    public function checkout(): void
    {
        $data = $this->form->getState();

        if ($this->carts->isEmpty()) {
            Notification::make()->title('Keranjang kosong!')->danger()->send();
            return;
        }

        // Get a fresh database connection
        $connection = DB::connection();
        $connection->disableQueryLog(); // Optional: for better performance

        try {
            $retries = 0;
            $maxRetries = 3;

            while ($retries < $maxRetries) {
                try {
                    $connection->beginTransaction();
                    break;
                } catch (\Exception $e) {
                    $retries++;
                    if ($retries === $maxRetries) {
                        throw $e;
                    }
                    usleep(100000); // 100ms delay
                }
            }

            // Create transaction
            $transaction = Transaction::create([
                'customer_id' => $data['customer_id'] ?? null,
                'user_id' => Auth::id(),
                'total_amount' => $this->total,
                'cash' => 0,
                'change' => 0,
                'discount' => 0,
                'payment_method' => 'cash',
                'transaction_date' => now(),
                'status' => 'pending',
                'invoice' => 'INV-' . date('Ymd') . '-' . str_pad(Transaction::max('id') + 1, 3, '0', STR_PAD_LEFT)
            ]);

            // Create transaction details
            foreach ($this->carts as $cart) {
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $cart->product_id,
                    'quantity' => $cart->quantity,
                    'weight' => $cart->weight,
                    'buy_price' => $cart->buy_price,
                    'subtotal' => $cart->subtotal,
                ]);
            }

            // Clear carts
            Cart::whereIn('id', $this->carts->pluck('id'))->delete();
            $this->refreshCarts();

            // Verify transaction is still active before committing
            if ($connection->transactionLevel() > 0) {
                $connection->commit();
            } else {
                throw new \RuntimeException('Transaction was not active at commit time');
            }

            $this->redirect(route('filament.admin.transactions.resources.sales.payment', $transaction->invoice));
        } catch (\Exception $e) {
            // Verify transaction is active before rollback
            if (isset($connection) && $connection->transactionLevel() > 0) {
                $connection->rollBack();
            }

            Log::error('Checkout failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'transaction_level' => isset($connection) ? $connection->transactionLevel() : 'N/A'
            ]);

            Notification::make()
                ->title('Checkout Gagal')
                ->body('Terjadi kesalahan: ' . $e->getMessage())
                ->danger()
                ->persistent()
                ->send();

            if (app()->environment('local')) {
                report($e);
                throw $e;
            }
        }
    }
}
