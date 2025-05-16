<?php

namespace App\Filament\Clusters\MenuTransactions\Resources\BuyResource\Pages;

use App\Filament\Clusters\MenuTransactions\Resources\BuyResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBuy extends CreateRecord
{
    protected static string $resource = BuyResource::class;

    public static function canCreateAnother(): bool
    {
        return false;
    }
    public static function canCreate(): bool
    {
        return false;
    }
}
