<?php

namespace App\Filament\Clusters\Shop\Resources\PurchaseResource\Pages;

use App\Filament\Clusters\Shop\Resources\PurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;
}
