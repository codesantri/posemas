<?php

namespace App\Filament\Clusters\Shop\Resources\PurchaseResource\Pages;

use App\Filament\Clusters\Shop\Resources\PurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPurchases extends ListRecords
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Pembelian')->icon('heroicon-m-plus'),
        ];
    }

    protected function getTableQuery(): ?Builder
    {
        return parent::getTableQuery()
            ?->whereHas('transaction', function (Builder $query) {
                $query->where('transaction_type', 'purchase');
            });
    }
}
