<?php

namespace App\Filament\Clusters\Product\Resources\KaratResource\Pages;

use App\Filament\Clusters\Product\Resources\KaratResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKarat extends EditRecord
{
    protected static string $resource = KaratResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
