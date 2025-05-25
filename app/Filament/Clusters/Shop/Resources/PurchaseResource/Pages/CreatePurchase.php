<?php

namespace App\Filament\Clusters\Shop\Resources\PurchaseResource\Pages;

use App\Models\Stock;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\StockTotal;
use Illuminate\Support\Facades\Auth;
use App\Filament\Clusters\Shop\Resources\PurchaseResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    protected static ?string $title = 'Data Pembelian';
    protected array $processedProducts = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $totalAmount = 0;
        $this->processedProducts = [];

        // Proses produk
        foreach ($data['products'] as $productInput) {
            $product = Product::firstOrCreate([
                'name' => $productInput['product_name'],
                'karat_id' => $productInput['karat_id'],
                'category_id' => $productInput['category_id'],
                'type_id' => $productInput['type_id'],
                'weight' => $productInput['weight'],
            ]);

            $buyPrice = $product->karat->buy_price ?? 0;
            $weight = $product->weight ?? 0;
            $quantity = $productInput['quantity'] ?? 0;
            $subtotal = $buyPrice * $weight * $quantity;
            $totalAmount += $subtotal;

            $this->processedProducts[] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'buy_price' => $buyPrice,
                'weight' => $weight,
                'subtotal' => $subtotal,
            ];

            // Tambah stok
            $stockTotal = StockTotal::firstOrNew(['product_id' => $product->id]);
            $stockTotal->total = ($stockTotal->total ?? 0) + $quantity;
            $stockTotal->save();

            Stock::create([
                'product_id' => $product->id,
                'stock_quantity' => $quantity,
                'received_at' => now(),
            ]);
        }

        // Buat transaction terlebih dahulu
        $transaction = Transaction::create([
            'transaction_type' => 'purchase',
            'payment_method' => 'cash', // bisa disesuaikan dari form
            'status' => 'pending',
            'transaction_date' => now(),
            'total_amount' => $totalAmount,
        ]);

        // Lengkapi data purchase
        $data['user_id'] = Auth::id();
        $data['transaction_id'] = $transaction->id;
        $data['purchase_date'] = now();
        $data['total_amount'] = $totalAmount;

        unset($data['products']); // kita proses sendiri di afterCreate

        return $data;
    }

    protected function afterCreate(): void
    {
        $totalAmount = 0;

        foreach ($this->processedProducts as $productDetail) {
            $this->record->purchaseDetails()->create([
                'product_id' => $productDetail['product_id'],
                'quantity' => $productDetail['quantity'],
                'buy_price' => $productDetail['buy_price'],
                'weight' => $productDetail['weight'],
                'subtotal' => $productDetail['subtotal'],
            ]);

            $totalAmount += $productDetail['subtotal'];
        }

        // Update ulang total_amount di purchases jika berbeda
        if ((int)$this->record->total_amount !== $totalAmount) {
            $this->record->update(['total_amount' => $totalAmount]);
        }

        // Sinkronkan juga ke transaksi
        $this->record->transaction->update(['total_amount' => $totalAmount]);

        Log::info('Purchase berhasil dibuat dan disinkronkan', [
            'purchase_id' => $this->record->id,
            'transaction_id' => $this->record->transaction_id,
            'total' => $totalAmount,
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('payment', [$this->record->transaction->invoice]);
    }

    public static function canCreateAnother(): bool
    {
        return false;
    }

    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('Simpan & Proses Pembelian')->icon('heroicon-m-credit-card');
    }
}
