<?php

namespace App\Filament\Clusters\Shop\Resources\PurchaseResource\Pages;

use App\Models\Stock;
use Filament\Actions\Action;
use App\Models\Product;
use App\Models\StockTotal;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Clusters\Shop\Resources\PurchaseResource;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    protected static ?string $title = 'Data Pembelian';
    protected array $processedProducts = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {

        $data['user_id'] = Auth::id(); // Set the current user
        $data['purchase_date'] = now();

        // Initialize totals
        $totalAmount = 0;

        // Process each product
        foreach ($data['products'] as $productInput) {
            $product = Product::firstOrCreate(
                [
                    'name' => $productInput['product_name'],
                    'karat_id' => $productInput['karat_id'],
                    'category_id' => $productInput['category_id'],
                    'type_id' => $productInput['type_id'],
                    'weight' => $productInput['weight'],
                ]
            );

            $buyPrice = $product->karat->buy_price ?? 0;
            $weight = $product->weight;
            $quantity = $productInput['quantity'];
            $subtotal = $buyPrice * $weight * $quantity; // Fixed calculation to include quantity
            $totalAmount += $subtotal;

            $this->processedProducts[] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'buy_price' => $buyPrice,
                'weight' => $weight, // Removed extra space in 'weight ' key
                'subtotal' => $subtotal,
            ];

            // Update stock
            $stockTotal = StockTotal::firstOrNew(['product_id' => $product->id]);
            $stockTotal->total += $quantity;
            $stockTotal->save();

            Stock::create([
                'product_id' => $product->id,
                'stock_quantity' => $quantity,
                'received_at' => now(),
            ]);
        }

        $data['total_amount'] = $totalAmount;

        // Remove products from main data as we'll handle them in afterCreate
        unset($data['products']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $totalAmount = 0;

        // Save purchase details using the processed products data
        foreach ($this->processedProducts as $productDetail) {
            $product = Product::with('karat')->find($productDetail['product_id']);

            // Create purchase detail record
            $purchaseDetail = $this->record->purchaseDetails()->create([
                'product_id' => $productDetail['product_id'],
                'quantity' => $productDetail['quantity'],
                'buy_price' => $productDetail['buy_price'],
                'weight' => $productDetail['weight'],
                'subtotal' => $productDetail['subtotal'],
            ]);

            // Accumulate total amount
            $totalAmount += $purchaseDetail->subtotal;
        }

        // Update the purchase total_amount if there's any discrepancy
        if ($this->record->total_amount != $totalAmount) {
            $this->record->update([
                'total_amount' => $totalAmount,
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('payment', [$this->record->invoice]);
    }

    public static function canCreateAnother(): bool
    {
        return false;
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Simpan & Proses Pembayaran')
            ->extraAttributes(['style' => 'float: right'])
            ->color('success');
    }
}
