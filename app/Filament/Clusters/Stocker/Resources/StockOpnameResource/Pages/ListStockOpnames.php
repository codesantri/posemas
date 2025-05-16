<?php

namespace App\Filament\Clusters\Stocker\Resources\StockOpnameResource\Pages;

use App\Filament\Clusters\Stocker\Resources\StockOpnameResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStockOpnames extends ListRecords
{
    protected static string $resource = StockOpnameResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
