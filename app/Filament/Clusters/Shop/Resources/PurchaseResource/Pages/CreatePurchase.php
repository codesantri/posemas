<?php

namespace App\Filament\Clusters\Shop\Resources\PurchaseResource\Pages;

use App\Models\Stock;
use Filament\Actions;
use App\Models\Product;
use App\Models\StockTotal;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Clusters\Shop\Resources\PurchaseResource;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;
    protected static ?string $title = 'Data Pembelian';
    protected array $processedProducts = [];

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
                    Product::create($data);

                    \Filament\Notifications\Notification::make()
                        ->title('Produk berhasil ditambahkan')
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $totalAmount = 0;
        $this->processedProducts = [];

        if (empty($data['products'])) {
            throw new \Exception('Tidak ada produk yang dipilih untuk pembelian');
        }

        foreach ($data['products'] as $productInput) {
            $product = Product::with('karat')->findOrFail($productInput['product_id']);

            if (!$product->karat) {
                throw new \Exception("Produk {$product->name} belum memiliki data karat");
            }

            $buyPrice = $product->karat->buy_price;
            $weight = $product->weight;
            $quantity = $productInput['quantity'];
            $subtotal = $buyPrice * $weight * $quantity;
            $totalAmount += $subtotal;

            $this->processedProducts[] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'buy_price' => $buyPrice,
                'weight' => $weight,
                'subtotal' => $subtotal,
            ];

            // Update stock
            // $this->updateProductStock($product->id, $quantity);
        }

        $transaction = Transaction::create([
            'transaction_type' => 'purchase',
            'payment_method' => $data['payment_method'] ?? 'cash',
            'status' => 'pending',
            'transaction_date' => now(),
            'total_amount' => $totalAmount,
        ]);

        $data['user_id'] = Auth::id();
        $data['transaction_id'] = $transaction->id;
        $data['purchase_date'] = now();
        $data['total_amount'] = $totalAmount;

        unset($data['products']);

        return $data;
    }

    // protected function updateProductStock(int $productId, int $quantity): void
    // {
    //     try {
    //         // Update stock total
    //         StockTotal::updateOrCreate(
    //             ['product_id' => $productId],
    //             ['total' => DB::raw("COALESCE(total, 0) + {$quantity}")]
    //         );

    //         // Create stock history
    //         Stock::create([
    //             'product_id' => $productId,
    //             'stock_quantity' => $quantity,
    //             'received_at' => now(),
    //             'user_id' => Auth::id(),
    //         ]);
    //     } catch (\Exception $e) {
    //         Log::error('Failed to update product stock', [
    //             'product_id' => $productId,
    //             'quantity' => $quantity,
    //             'error' => $e->getMessage()
    //         ]);

    //         throw $e;
    //     }
    // }

    protected function afterCreate(): void
    {
        try {
            $totalAmount = 0;

            foreach ($this->processedProducts as $productDetail) {
                $this->record->purchaseDetails()->create($productDetail);
                $totalAmount += $productDetail['subtotal'];
            }

            // Update purchase total if different
            if ((float)$this->record->total_amount !== (float)$totalAmount) {
                $this->record->update(['total_amount' => $totalAmount]);
            }

            // Sync with transaction
            $this->record->transaction()->update(['total_amount' => $totalAmount]);

            Log::info('Purchase created successfully', [
                'purchase_id' => $this->record->id,
                'transaction_id' => $this->record->transaction_id,
                'total_amount' => $totalAmount,
                'user_id' => Auth::id(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to complete purchase creation', [
                'purchase_id' => $this->record->id ?? null,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function getRedirectUrl(): string
    {
        return route('filament.admin.shop.resources.purchases.payment', ['invoice' => $this->record->transaction->invoice]);
    }


    public static function canCreateAnother(): bool
    {
        return false;
    }

    protected function getCreateFormAction(): Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('Simpan & Proses Pembelian')
            ->icon('heroicon-m-credit-card');
    }
}
