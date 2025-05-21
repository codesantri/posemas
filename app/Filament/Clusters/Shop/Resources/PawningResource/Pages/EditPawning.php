<?php

namespace App\Filament\Clusters\Shop\Resources\PawningResource\Pages;

use App\Filament\Clusters\Shop\Resources\PawningResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPawning extends EditRecord
{
    protected static string $resource = PawningResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
