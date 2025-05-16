<?php

namespace App\Filament\Clusters\MenuTransactions\Resources\BuyResource\Pages;

use App\Filament\Clusters\MenuTransactions\Resources\BuyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBuy extends EditRecord
{
    protected static string $resource = BuyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
