<?php

namespace App\Filament\Widgets;

use App\Models\Purchase;
use App\Models\Transaction;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class ResultWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $sale = Transaction::all();
        $purchase = Purchase::all();

        return [
            Stat::make(
                'Penjualan',
                number_format($sale->sum('total_amount'), 0, ',', '.')
            )
                ->description('32k increase')
                ->descriptionIcon('heroicon-m-shopping-cart', IconPosition::Before)
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),
            Stat::make('Pembelian', number_format($purchase->sum('total_amount'), 0, ',', '.'))
                ->description('32k increase')
                ->descriptionIcon('heroicon-m-credit-card', IconPosition::Before)
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('warning'),
            Stat::make('Gadai', '192.1k')
                ->description('32k increase')
                ->descriptionIcon('heroicon-o-scale', IconPosition::Before)
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('info'),
            Stat::make('Pertukaran', '192.1k')
                ->description('32k increase')
                ->descriptionIcon('heroicon-o-arrows-right-left', IconPosition::Before)
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('danger'),
        ];
    }
}
