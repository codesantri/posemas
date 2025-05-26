<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class TransactionWidget extends BaseWidget
{
    protected function getStats(): array
    {
        // Total by type
        $sale = $this->getTotalByType('sale');
        $purchase = $this->getTotalByType('purchase');
        $pawning = $this->getTotalByType('pawning');
        $change = $this->getTotalByType('change');

        // Total all types
        $totalAll = $sale + $purchase + $pawning + $change;

        // Helper function
        $formatCurrency = fn($value) => 'Rp. ' . number_format($value, 0, ',', '.');
        $percentage = fn($value) => $totalAll > 0 ? number_format(($value / $totalAll) * 100, 2) . '%' : '0%';

        return [
            Stat::make('Penjualan', $percentage($sale))
                ->description($formatCurrency($sale))
                ->descriptionIcon('heroicon-m-shopping-cart', IconPosition::Before)
                ->chart($this->getMonthlyChart('sale'))
                ->color('success'),

            Stat::make('Pembelian', $percentage($purchase))
                ->description($formatCurrency($purchase))
                ->descriptionIcon('heroicon-m-credit-card', IconPosition::Before)
                ->chart($this->getMonthlyChart('purchase'))
                ->color('warning'),

            Stat::make('Gadai', $percentage($pawning))
                ->description($formatCurrency($pawning))
                ->descriptionIcon('heroicon-o-scale', IconPosition::Before)
                ->chart($this->getMonthlyChart('pawning'))
                ->color('info'),

            Stat::make('Pertukaran', $percentage($change))
                ->description($formatCurrency($change))
                ->descriptionIcon('heroicon-o-arrows-right-left', IconPosition::Before)
                ->chart($this->getMonthlyChart('change'))
                ->color('danger'),
        ];
    }

    private function getTotalByType(string $type): float
    {
        return Transaction::where('transaction_type', $type)
            ->where('status', 'success')
            ->sum('total_amount');
    }

    private function getMonthlyChart(string $type): array
    {
        return collect(range(1, 12))->map(function ($month) use ($type) {
            return Transaction::where('transaction_type', $type)
                ->where('status', 'success')
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', now()->year)
                ->sum('total_amount');
        })->toArray();
    }
}
