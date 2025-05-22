<?php

namespace App\Filament\Clusters\Shop\Resources\PurchaseResource\Pages;

use App\Filament\Clusters\Shop\Resources\PurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPurchases extends ListRecords
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Pembelian')->icon('heroicon-m-plus'),
        ];
    }
}
