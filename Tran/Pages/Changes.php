<?php

namespace App\Filament\Clusters\Transactions\Pages;

use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Clusters\Transactions;

class Changes extends Page
{

    protected static ?string $cluster = Transactions::class;
    protected static string $view = 'filament.clusters.transactions.pages.changes';
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
