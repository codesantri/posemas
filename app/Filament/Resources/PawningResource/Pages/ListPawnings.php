<?php

namespace App\Filament\Resources\PawningResource\Pages;

use App\Filament\Resources\PawningResource;
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
