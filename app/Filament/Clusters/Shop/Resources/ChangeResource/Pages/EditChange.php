<?php

namespace App\Filament\Clusters\Shop\Resources\ChangeResource\Pages;

use App\Filament\Clusters\Shop\Resources\ChangeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditChange extends EditRecord
{
    protected static string $resource = ChangeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
