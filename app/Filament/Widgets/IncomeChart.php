<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class IncomeChart extends ChartWidget
{
    protected static ?string $heading = 'Penjualan';
    protected static string $color = 'success';

    // Default filter value (tahun sekarang)
    protected function getFilters(): ?array
    {
        // Ambil daftar tahun unik dari transaksi
        $years = Transaction::selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();

        // Mapping jadi ['2025' => '2025', dst]
        return collect($years)->mapWithKeys(fn($year) => [$year => $year])->toArray();
    }

    protected function getData(): array
    {
        $selectedYear = $this->filter ?? now()->year;

        $monthlySales = Transaction::selectRaw('MONTH(created_at) as month, SUM(total_amount) as total')
            ->where('transaction_type', 'sale')
            ->where('status', 'success')
            ->whereYear('created_at', $selectedYear)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy('month')
            ->pluck('total', 'month');

        $salesData = collect(range(1, 12))->map(fn($month) => $monthlySales->get($month, 0));

        return [
            'datasets' => [
                [
                    'label' => "Total Penjualan Tahun $selectedYear",
                    'data' => $salesData,
                    'backgroundColor' => '#22c55e',
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
