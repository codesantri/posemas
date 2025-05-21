<?php

namespace App\Filament\Clusters\Transactions\Pages;

use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Clusters\Transactions;

class Pawning extends Page
{
    protected static ?string $cluster = Transactions::class;
    protected static string $view = 'filament.clusters.transactions.pages.pawning';
    protected static ?string $navigationLabel = "Gadai/Titip";
    protected static ?string $navigationIcon = 'heroicon-o-scale';
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public function getTitle(): string|Htmlable
    {
        return 'Gadai/Titip';
    }
    public function getHeading(): string|Htmlable
    {
        return "Penggadaain Emas";
    }   
}
