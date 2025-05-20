<?php

namespace App\Filament\Clusters\MenuTransactions\Resources\SaleResource\Pages;

use App\Filament\Clusters\MenuTransactions\Resources\SaleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSale extends EditRecord
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
