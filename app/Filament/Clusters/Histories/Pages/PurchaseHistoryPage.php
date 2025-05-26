<?php

namespace App\Filament\Clusters\Histories\Pages;

use App\Filament\Clusters\Histories;
use Filament\Pages\Page;

class PurchaseHistoryPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $title = 'Pembelian';

    protected static string $view = 'filament.clusters.histories.pages.purchase-history-page';

    protected static ?string $cluster = Histories::class;
}
