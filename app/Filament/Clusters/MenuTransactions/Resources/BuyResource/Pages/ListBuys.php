<?php

namespace App\Filament\Clusters\MenuTransactions\Resources\BuyResource\Pages;

use App\Filament\Clusters\MenuTransactions\Resources\BuyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBuys extends ListRecords
{
    protected static string $resource = BuyResource::class;

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
