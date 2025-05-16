<?php

namespace App\Filament\Resources\PawningResource\Pages;

use App\Filament\Resources\PawningResource;
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
