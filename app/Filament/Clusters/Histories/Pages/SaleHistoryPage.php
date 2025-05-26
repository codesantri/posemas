<?php

namespace App\Filament\Clusters\Histories\Pages;

use App\Filament\Clusters\Histories;
use Filament\Pages\Page;

class SaleHistoryPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $title = 'Penjualan';

    protected static string $view = 'filament.clusters.histories.pages.sale-history-page';

    protected static ?string $cluster = Histories::class;
}
