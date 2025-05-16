<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class Product extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';
    protected static ?string $clusterBreadcrumb = 'Master Data';
    protected static ?string $navigationGroup = 'Manajemen Produk';

    public static function getNavigationLabel(): string
    {
        return 'Master Data';
    }
}
