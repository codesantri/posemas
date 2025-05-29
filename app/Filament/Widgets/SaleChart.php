<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SaleChart extends ChartWidget
{
    protected static ?string $heading = 'Penjualan';
    protected static string $color = 'success';

    public ?string $filter = null;

    public function mount(): void
    {
        // Default ke tahun sekarang
        $this->filter = (string) now()->year;
    }

    protected function getFilters(): ?array
    {
        $years = Transaction::selectRaw('YEAR(transaction_date) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();

        // Tambahin fallback
        if (empty($years)) {
            $years[] = now()->year;
        }

        return collect($years)
            ->mapWithKeys(fn($year) => [$year => $year])
            ->toArray();
    }

    protected function getData(): array
    {
        $year = $this->filter ?? now()->year;

        $monthlySales = Transaction::selectRaw('MONTH(transaction_date) as month, SUM(total_amount) as total')
            ->where('transaction_type', 'sale')
            ->where('status', 'success')
            ->whereYear('transaction_date', $year)
            ->groupBy(DB::raw('MONTH(transaction_date)'))
            ->pluck('total', 'month')
            ->toArray();

        $salesData = collect(range(1, 12))
            ->map(fn($month) => (float) ($monthlySales[$month] ?? 0))
            ->toArray();

        $labels = collect(range(1, 12))
            ->map(fn($m) => now()->setMonth($m)->translatedFormat('M'))
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => "Total Penjualan Tahun $year",
                    'data' => $salesData,
                    'backgroundColor' => '#22c55e',
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
