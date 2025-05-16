<?php

namespace App\Filament\Clusters\MenuTransactions\Pages;

use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Clusters\MenuTransactions;

class Changes extends Page
{

    protected static ?string $cluster = MenuTransactions::class;
    protected static string $view = 'filament.clusters.menu-transactions.pages.changes';
    protected static ?string $navigationLabel = "Pertukaran";
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;


    public function getTitle(): string|Htmlable
    {
        return 'Jual Emas';
    }
    public function getHeading(): string|Htmlable
    {
        return "Pertukaran Emas";
    }
}
