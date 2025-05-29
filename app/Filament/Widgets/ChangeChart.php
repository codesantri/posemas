<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ChangeChart extends ChartWidget
{
    protected static ?string $heading = 'Pertukaran';
    protected static string $color = 'light';

    public ?string $filter = null;

    public function mount(): void
    {
        $this->filter = (string) now()->year;
    }

    protected function getFilters(): ?array
    {
        $years = Transaction::selectRaw('YEAR(transaction_date) as year')
            ->where('transaction_type', 'change')
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

        $transactions = Transaction::with(['sale', 'purchase'])
            ->where('transaction_type', 'change')
            ->where('status', 'success')
            ->whereYear('transaction_date', $year)
            ->get();

        $tukarTambah = array_fill(1, 12, 0);
        $tukarKurang = array_fill(1, 12, 0);

        foreach ($transactions as $trx) {
            $month = (int) date('n', strtotime($trx->transaction_date));

            $saleAmount = optional($trx->sale)->total_amount ?? 0;
            $purchaseAmount = optional($trx->purchase)->total_amount ?? 0;

            $selisih = abs($saleAmount - $purchaseAmount);

            if ($saleAmount > $purchaseAmount) {
                $tukarTambah[$month] += $selisih;
            } elseif ($saleAmount < $purchaseAmount) {
                $tukarKurang[$month] += $selisih;
            }
        }

        $labels = collect(range(1, 12))->map(fn($m) => now()->setMonth($m)->translatedFormat('M'))->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Tukar Tambah',
                    'data' => array_values($tukarTambah),
                    'backgroundColor' => '#22c55e',
                ],
                [
                    'label' => 'Tukar Kurang',
                    'data' => array_values($tukarKurang),
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
