<?php

namespace App\Filament\Clusters\Shop\Resources\SaleResource\Pages;

use App\Models\Cart;
use App\Models\Sale;
use App\Models\Customer;
use Filament\Forms\Form;
use App\Models\SaleDetail;
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
use Illuminate\Contracts\Support\Htmlable;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Filament\Clusters\Shop\Resources\SaleResource;

class CartPage extends Page implements HasForms
{
    use InteractsWithForms;
    protected static string $resource = SaleResource::class;
    protected static string $view = 'filament.pages.shop.sale.cart.cart';

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
                            view('filament.pages.shop.sale.cart.items', [
                                'state' => $this->carts,
                                'total' => $this->total,
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

        $connection = DB::connection();
        $connection->disableQueryLog();

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
                    usleep(100000);
                }
            }

            $newTransaction = Transaction::create([
                'transaction_type' => 'sale',
                'status' => 'pending',
            ]);

            $sale = Sale::create([
                'customer_id' => $data['customer_id'] ?? null,
                'transaction_id' => $newTransaction->id,
                'user_id' => Auth::id(),
                'total_amount' => $this->total,
            ]);

            // Buat SaleDetail (bukan TransactionDetail)
            foreach ($this->carts as $cart) {
                SaleDetail::create([
                    'sale_id' => $sale->id,
                    'product_id' => $cart->product_id,
                    'quantity' => $cart->quantity,
                    'weight' => $cart->weight,
                    'buy_price' => $cart->buy_price,
                    'subtotal' => $cart->subtotal,
                ]);
            }

            Cart::whereIn('id', $this->carts->pluck('id'))->delete();
            $this->refreshCarts();

            if ($connection->transactionLevel() > 0) {
                $connection->commit();
            } else {
                throw new \RuntimeException('Transaction was not active at commit time');
            }

            $this->redirect(route('filament.admin.shop.resources.sales.checkout', $newTransaction->invoice)); // Ganti $transaction ke $sale

        } catch (\Exception $e) {
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
