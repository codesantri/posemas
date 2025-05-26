<?php

namespace App\Filament\Clusters\Histories\Pages;

use App\Filament\Clusters\Histories;
use Filament\Pages\Page;

class PawningHistoryPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $title = 'Penggadaian';

    protected static string $view = 'filament.clusters.histories.pages.pawning-history-page';

    protected static ?string $cluster = Histories::class;
}
