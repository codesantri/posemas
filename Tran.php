<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;
use Filament\Pages\SubNavigationPosition;

class Transactions extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $clusterBreadcrumb = 'Transaksi';
    

    public static function getNavigationLabel(): string
    {
        return 'Transaksi'; // ← typo diperbaiki dari "Transaki" ke "Transaksi"
    }
}
