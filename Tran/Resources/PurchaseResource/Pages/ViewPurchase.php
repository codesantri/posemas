<?php

namespace App\Filament\Clusters\Transactions\Resources\PurchaseResource\Pages;

use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Components\Grid;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use App\Filament\Clusters\Transactions\Resources\PurchaseResource;

class ViewPurchase extends ViewRecord
{
    protected static string $resource = PurchaseResource::class;

    protected static string $view = 'filament.clusters.transactions.resources.purchase-resource.pages.invoice-purchase';
    protected static ?string $title = 'Invoice';
    protected static ?string $breadcrumb = 'Invoice';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

}
