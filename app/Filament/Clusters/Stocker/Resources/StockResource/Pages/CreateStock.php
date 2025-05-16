<?php

namespace App\Filament\Clusters\Stocker\Resources\StockResource\Pages;

use App\Models\Stock;
use App\Models\StockTotal;
use Filament\Actions;
use Illuminate\Support\Facades\DB;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Clusters\Stocker\Resources\StockResource;

class CreateStock extends CreateRecord
{
    protected static string $resource = StockResource::class;

    /**
     * Menampung item stok dari Repeater.
     */
    protected array $stocks = [];

    /**
     * Tombol header (opsional).
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Kembali')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray'),
        ];
    }

    /**
     * Override handleRecordCreation supaya insert minimal 1 record utama.
     */
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $firstStock = $this->stocks[0] ?? null;

        if (!$firstStock || !isset($firstStock['product_id'], $firstStock['stock_quantity'], $firstStock['received_at'])) {
            throw new \Exception('Data stok pertama tidak lengkap.');
        }

        // Simpan stok pertama
        $record = Stock::create([
            'product_id' => $firstStock['product_id'],
            'supplier_id' => $firstStock['supplier_id'] ?? null,
            'stock_quantity' => $firstStock['stock_quantity'],
            'received_at' => $firstStock['received_at'],
        ]);

        // Update atau buat total stok
        StockTotal::updateOrCreate(
            ['product_id' => $firstStock['product_id']],
            ['total' => DB::raw("total + " . (int) $firstStock['stock_quantity'])]
        );

        // ✅ HARUS return instance dari Model
        return $record;
    }


    /**
     * Konfigurasi tombol simpan (dengan label dan konfirmasi).
     */
    protected function getCreateFormAction(): Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('Simpan Stok')
            ->modalHeading('Konfirmasi Simpan Stok')
            ->modalSubmitActionLabel('Ya, Simpan')
            ->modalCancelActionLabel('Batal');
    }

    /**
     * Tangkap dan olah data sebelum disimpan.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->stocks = $data['Stocks'] ?? [];
        unset($data['Stocks']);
        return $data;
    }

    /**
     * Simpan data stok satu per satu secara manual, kecuali yang pertama sudah masuk di handleRecordCreation.
     */
    protected function afterCreate(): void
    {
        if (empty($this->stocks)) {
            return;
        }
        // Skip item pertama karena sudah dibuat di handleRecordCreation
        $stocksToSave = array_slice($this->stocks, 1);

        foreach ($stocksToSave as $item) {
            if (!isset($item['product_id'], $item['supplier_id'], $item['stock_quantity'], $item['received_at'])) {
                continue;
            }
            Stock::create([
                'product_id' => $item['product_id'],
                'supplier_id' => $item['supplier_id'] ?? null,
                'stock_quantity' => $item['stock_quantity'],
                'received_at' => $item['received_at'],
            ]);
            StockTotal::updateOrCreate(
                ['product_id' => $item['product_id']],
                ['total' => DB::raw("total + " . (int) $item['stock_quantity'])]
            );
        }
    }

    /**
     * Arahkan kembali ke index setelah simpan.
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
