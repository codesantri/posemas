<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;
use Filament\Pages\SubNavigationPosition;

class MenuTransactions extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $clusterBreadcrumb = 'Menu Transaksi';
    

    public static function getNavigationLabel(): string
    {
        return 'Menu Transaksi'; // ← typo diperbaiki dari "Transaki" ke "Transaksi"
    }
}
