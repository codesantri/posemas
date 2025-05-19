<?php

namespace App\Filament\Clusters\MenuTransactions\Resources\PurchaseResource\Pages;

use App\Filament\Clusters\MenuTransactions\Resources\PurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPurchases extends ListRecords
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Pembelian')
                ->modalHeading('Data Pembelian')
                ->modalSubmitActionLabel('Simpan & Proses Pemabayaran')
                ->closeModalByClickingAway(false)
                ->createAnother(false),
        ];
    }
}
