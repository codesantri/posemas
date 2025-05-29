<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PurchaseChart extends ChartWidget
{
    protected static ?string $heading = 'Pembelian';
    protected static string $color = 'warning';

    // Biar bisa binding filter
    public ?string $filter = null;

    public function mount(): void
    {
        $this->filter = (string) now()->year;
    }

    protected function getFilters(): ?array
    {
        $years = Transaction::selectRaw('YEAR(transaction_date) as year')
            ->where('transaction_type', 'purchase')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();

        if (empty($years)) {
            $years[] = now()->year;
        }

        return collect($years)->mapWithKeys(fn($year) => [$year => $year])->toArray();
    }

    protected function getData(): array
    {
        $year = $this->filter ?? now()->year;

        $monthlyPurchases = cache()->remember(
            "monthly_purchases_{$year}", // 🚨 bedain dari SaleChart
            now()->addMinutes(30),
            fn() => Transaction::selectRaw('MONTH(transaction_date) as month, SUM(total_amount) as total')
                ->where('transaction_type', 'purchase')
                ->where('status', 'success')
                ->whereYear('transaction_date', $year)
                ->groupBy(DB::raw('MONTH(transaction_date)'))
                ->orderBy('month')
                ->pluck('total', 'month')
                ->toArray()
        );

        $salesData = collect(range(1, 12))
            ->map(fn($month) => (float) ($monthlyPurchases[$month] ?? 0))
            ->toArray();

        $labels = collect(range(1, 12))
            ->map(fn($m) => now()->setMonth($m)->translatedFormat('M'))
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => "Total Pembelian Tahun $year",
                    'data' => $salesData,
                    'backgroundColor' => '#f59e0b',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
