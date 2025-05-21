<?php

namespace App\Filament\Clusters\Shop\Resources\PawningResource\Pages;

use App\Filament\Clusters\Shop\Resources\PawningResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPawnings extends ListRecords
{
    protected static string $resource = PawningResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
