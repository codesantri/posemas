<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class Stocker extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $clusterBreadcrumb = 'Stok Inventoris';
    protected static ?string $navigationGroup = 'Manajemen Produk';

    public static function getNavigationLabel(): string
    {
        return 'Stok Inventoris';
    }
}
