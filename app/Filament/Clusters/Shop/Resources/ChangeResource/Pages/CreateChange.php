<?php

namespace App\Filament\Clusters\Shop\Resources\ChangeResource\Pages;

use App\Models\Sale;
use Filament\Actions;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\SaleDetail;
use App\Models\StockTotal;
use App\Models\Transaction;
use App\Models\PurchaseDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;
use App\Filament\Clusters\Shop\Resources\ChangeResource;

class CreateChange extends CreateRecord
{
    protected static string $resource = ChangeResource::class;

    protected static ?string $title = "Pertukaran";

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('addnew')
                ->label('Tambah Produk')
                ->icon('heroicon-m-plus')
                ->form([
                    TextInput::make('name')
                        ->label('Nama Produk')
                        ->required(),

                    Select::make('category_id')
                        ->label('Kategori')
                        ->options(\App\Models\Category::all()->pluck('name', 'id'))
                        ->required(),

                    Select::make('type_id')
                        ->label('Jenis')
                        ->options(\App\Models\Type::all()->pluck('name', 'id'))
                        ->required(),

                    Select::make('karat_id')
                        ->label('Karat-Kadar')
                        ->options(\App\Models\Karat::all()->mapWithKeys(fn($k) => [
                            $k->id => "{$k->karat} - {$k->rate}%",
                        ])),

                    TextInput::make('weight')
                        ->label('Berat (gram)')
                        ->numeric()
                        ->required()
                        ->minValue(0.01)
                        ->default(0.01)
                        ->step(0.01),


                    FileUpload::make('image')
                        ->label('Gambar Produk')
                        ->image()
                        ->directory('products')
                        ->maxSize(2048),
                ])
                ->action(function (array $data) {
                    // Ambil quantity
                    // $quantity = $data['quantity'];
                    // unset($data['quantity']);

                    Product::create($data);

                    // \App\Models\Stock::create([
                    //     'product_id' => $product->id,
                    //     'stock_quantity' => $quantity,
                    //     'received_at' => now(),
                    // ]);

                    // \App\Models\StockTotal::updateOrCreate(
                    //     ['product_id' => $product->id],
                    //     ['total' => $quantity]
                    // );

                    \Filament\Notifications\Notification::make()
                        ->title('Produk berhasil ditambahkan')
                        ->success()
                        ->send();
                }),
        ];
    }

    public static function canCreateAnother(): bool
    {
        return false;
    }

    // protected function mutateFormDataBeforeCreate(array $data): array
    // {
    //     $totalPurchase = 0;
    //     $totalSale = 0;
    //     $olds = [];
    //     $news = [];
    //     $allItems = array_merge(
    //         $data['olds'] ?? [],
    //         $data['news'] ?? []
    //     );

    //     // Cek stok untuk semua produk
    //     foreach ($allItems as $item) {
    //         $product = Product::find($item['product_id']);
    //         if (!$product) {
    //             Notification::make()
    //                 ->title("Produk dengan ID {$item['product_id']} tidak ditemukan.")
    //                 ->danger()
    //                 ->duration(3000)
    //                 ->send();

    //             throw ValidationException::withMessages([
    //                 'product' => "Produk dengan ID {$item['product_id']} tidak ditemukan."
    //             ]);
    //         }

    //         $stock = StockTotal::where('product_id', $product->id)->first();
    //         $available = $stock?->total ?? 0;
    //         $quantity = $item['quantity'] ?? 0;

    //         if ($quantity > $available) {
    //             Notification::make()
    //                 ->title("Stok produk '{$product->name}' hanya tersedia {$available}, tidak cukup untuk jumlah {$quantity}.")
    //                 ->danger()
    //                 ->duration(3000)
    //                 ->send();

    //             throw ValidationException::withMessages([
    //                 'stock' => "Stok produk '{$product->name}' hanya tersedia {$available}."
    //             ]);
    //         }
    //     }

    //     // Kalau lolos cek stok, lanjut hitung total dan siapkan data
    //     foreach ($data['olds'] as $item) {
    //         $product = Product::with('karat')->find($item['product_id']);
    //         if (!$product) continue;

    //         $weight = $product->weight ?? 0;
    //         $quantity = $item['quantity'] ?? 1;
    //         $buy_price = $product->karat->buy_price ?? 0;
    //         $subtotal = $buy_price * $weight * $quantity;

    //         $totalPurchase += $subtotal;

    //         $olds[] = [
    //             'product_id' => $product->id,
    //             'quantity' => $quantity,
    //             'weight' => $weight,
    //             'buy_price' => $buy_price,
    //             'subtotal' => $subtotal,
    //         ];
    //     }

    //     foreach ($data['news'] as $item) {
    //         $product = Product::with('karat')->find($item['product_id']);
    //         if (!$product) continue;

    //         $weight = $product->weight ?? 0;
    //         $quantity = $item['quantity'] ?? 1;
    //         $sell_price = $product->karat->sell_price ?? 0;
    //         $subtotal = $sell_price * $weight * $quantity;

    //         $totalSale += $subtotal;

    //         $news[] = [
    //             'product_id' => $product->id,
    //             'quantity' => $quantity,
    //             'weight' => $weight,
    //             'sell_price' => $sell_price,
    //             'subtotal' => $subtotal,
    //         ];
    //     }

    //     $difference = $totalSale - $totalPurchase;

    //     $data['total_purchase'] = $totalPurchase;
    //     $data['total_sale'] = $totalSale;
    //     $data['olds'] = $olds;
    //     $data['news'] = $news;

    //     return $data;
    // }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $totalPurchase = 0;
        $totalSale = 0;
        $olds = [];
        $news = [];

        // Cek stok hanya untuk produk baru (news)
        foreach ($data['news'] ?? [] as $item) {
            $product = Product::find($item['product_id']);
            if (!$product) {
                Notification::make()
                    ->title("Produk dengan ID {$item['product_id']} tidak ditemukan.")
                    ->danger()
                    ->duration(3000)
                    ->send();

                throw ValidationException::withMessages([
                    'product' => "Produk dengan ID {$item['product_id']} tidak ditemukan."
                ]);
            }

            $stock = StockTotal::where('product_id', $product->id)->first();
            $available = $stock?->total ?? 0;
            $quantity = $item['quantity'] ?? 0;

            if ($quantity > $available) {
                Notification::make()
                    ->title("Stok produk '{$product->name}' hanya tersedia {$available}, tidak cukup untuk jumlah {$quantity}.")
                    ->danger()
                    ->duration(3000)
                    ->send();

                throw ValidationException::withMessages([
                    'stock' => "Stok produk '{$product->name}' hanya tersedia {$available}."
                ]);
            }
        }

        // Hitung total dan siapkan data untuk produk lama (olds)
        foreach ($data['olds'] ?? [] as $item) {
            $product = Product::with('karat')->find($item['product_id']);
            if (!$product) continue;

            $weight = $product->weight ?? 0;
            $quantity = $item['quantity'] ?? 1;
            $buy_price = $product->karat->buy_price ?? 0;
            $subtotal = $buy_price * $weight * $quantity;

            $totalPurchase += $subtotal;

            $olds[] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'weight' => $weight,
                'buy_price' => $buy_price,
                'subtotal' => $subtotal,
            ];
        }

        // Hitung total dan siapkan data untuk produk baru (news)
        foreach ($data['news'] ?? [] as $item) {
            $product = Product::with('karat')->find($item['product_id']);
            if (!$product) continue;

            $weight = $product->weight ?? 0;
            $quantity = $item['quantity'] ?? 1;
            $sell_price = $product->karat->sell_price ?? 0;
            $subtotal = $sell_price * $weight * $quantity;

            $totalSale += $subtotal;

            $news[] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'weight' => $weight,
                'sell_price' => $sell_price,
                'subtotal' => $subtotal,
            ];
        }

        // Finalisasi hasil
        $difference = $totalSale - $totalPurchase;

        $data['total_purchase'] = $totalPurchase;
        $data['total_sale'] = $totalSale;
        $data['olds'] = $olds;
        $data['news'] = $news;

        return $data;
    }



    protected function handleRecordCreation(array $data): Model
    {
        if (empty($data['olds']) || empty($data['news'])) {
            Notification::make()
                ->title("Data tidak lengkap atau gagal proses.")
                ->danger()
                ->duration(3000)
                ->send();

            throw ValidationException::withMessages([
                'data' => "Data tidak lengkap atau gagal proses, transaksi dibatalkan."
            ]);
        }
        DB::beginTransaction();

        try {
            // 1. Simpan transaksi
            $transaction = Transaction::create([
                'transaction_type' => 'change',
                'payment_method' => "cash",
                'status' => 'pending',
                'total_amount' => 0,
            ]);
            $purchase = Purchase::create([
                'customer_id' => $data['customer_id'],
                'transaction_id' => $transaction->id,
                'user_id' => Auth::id(),
                'total_amount' => $data['total_purchase'],
            ]);

            foreach ($data['olds'] as $item) {
                PurchaseDetail::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'] ?? 1,
                    'weight' => $item['weight'],
                    'buy_price' => $item['buy_price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            // 3. Simpan penjualan (produk baru keluar)
            $sale = Sale::create([
                'customer_id' => $data['customer_id'],
                'transaction_id' => $transaction->id,
                'user_id' => Auth::id(),
                'cash' =>  0,
                'change' =>  0,
                'discount' => 0,
                'total_amount' => $data['total_sale'],
            ]);

            foreach ($data['news'] as $item) {
                SaleDetail::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'] ?? 1,
                    'weight' => $item['weight'],
                    'buy_price' => $item['sell_price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }
            DB::commit();
            return $transaction;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', [$this->record->invoice]);
    }




    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateFormAction()
            ->submit(null)
            ->label('Simpan & Hitung Nilai Tukar')
            ->requiresConfirmation()
            ->modalHeading('Konfirmasi Perhitungan?')
            ->modalSubheading('Data yang akan disimpan tidak bisa diubah. Untuk menghindari kesalahan, mohon cek ulang data Anda.')
            ->modalButton('Ya, Lanjutkan')
            ->action(function () {
                $this->closeActionModal();
                $this->create();
            });
    }
}
