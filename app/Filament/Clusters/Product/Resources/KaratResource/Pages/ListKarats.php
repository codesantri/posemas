<?php

namespace App\Filament\Clusters\Product\Resources\KaratResource\Pages;

use App\Filament\Clusters\Product\Resources\KaratResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKarats extends ListRecords
{
    protected static string $resource = KaratResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
