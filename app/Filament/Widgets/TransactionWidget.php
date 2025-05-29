<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Transaction;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class TransactionWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totals = [
            'sale'     => $this->getTotalByType('sale'),
            'purchase' => $this->getTotalByType('purchase'),
            'pawning'  => $this->getTotalByType('pawning'),
        ];

        $tukarTotals = $this->getTukarTotals();

        $totalAll = array_sum($totals) + $tukarTotals['tambah'] + $tukarTotals['kurang'];

        $formatCurrency = fn($value) => 'Rp. ' . number_format($value, 0, ',', '.');
        $percentage     = fn($value) => $totalAll > 0 ? number_format(($value / $totalAll) * 100, 2) . '%' : '0%';

        return [
            Stat::make('Penjualan', $percentage($totals['sale']))
                ->description($formatCurrency($totals['sale']))
                ->descriptionIcon('heroicon-m-shopping-cart', IconPosition::Before)
                ->chart($this->getMonthlyChart('sale'))
                ->color('success'),

            Stat::make('Pembelian', $percentage($totals['purchase']))
                ->description($formatCurrency($totals['purchase']))
                ->descriptionIcon('heroicon-m-credit-card', IconPosition::Before)
                ->chart($this->getMonthlyChart('purchase'))
                ->color('warning'),

            Stat::make('Gadai', $percentage($totals['pawning']))
                ->description($formatCurrency($totals['pawning']))
                ->descriptionIcon('heroicon-o-scale', IconPosition::Before)
                ->chart($this->getMonthlyChart('pawning'))
                ->color('info'),

            Stat::make('Tukar Tambah', $percentage($tukarTotals['tambah']))
                ->description($formatCurrency($tukarTotals['tambah']))
                ->descriptionIcon('heroicon-o-arrows-right-left', IconPosition::Before)
                ->chart($this->getMonthlyTukarChart('tambah'))
                ->color('success'),

            Stat::make('Tukar Kurang', $percentage($tukarTotals['kurang']))
                ->description($formatCurrency($tukarTotals['kurang']))
                ->descriptionIcon('heroicon-o-arrows-right-left', IconPosition::Before)
                ->chart($this->getMonthlyTukarChart('kurang'))
                ->color('danger'),
        ];
    }

    private function getTotalByType(string $type): int
    {
        return Transaction::where('transaction_type', $type)
            ->where('status', 'success')
            ->sum('total_amount');
    }

    private function getMonthlyChart(string $type): array
    {
        $year = Carbon::now()->year;

        return collect(range(1, 12))->map(
            fn($month) =>
            Transaction::where('transaction_type', $type)
                ->where('status', 'success')
                ->whereMonth('transaction_date', $month)
                ->whereYear('transaction_date', $year)
                ->sum('total_amount')
        )->toArray();
    }

    private function getTukarTotals(): array
    {
        $transactions = $this->getTukarTransactions();

        $tambah = 0;
        $kurang = 0;

        foreach ($transactions as $trx) {
            $diff = $this->calculateChangeDiff($trx);

            if ($diff > 0) {
                $tambah += $diff;
            } elseif ($diff < 0) {
                $kurang += abs($diff);
            }
        }

        return [
            'tambah' => $tambah,
            'kurang' => $kurang,
        ];
    }

    private function getMonthlyTukarChart(string $mode): array
    {
        $year = Carbon::now()->year;

        return collect(range(1, 12))->map(function ($month) use ($mode, $year) {
            $transactions = $this->getTukarTransactions($year, $month);

            $total = 0;

            foreach ($transactions as $trx) {
                $diff = $this->calculateChangeDiff($trx);

                if ($mode === 'tambah' && $diff > 0) {
                    $total += $diff;
                } elseif ($mode === 'kurang' && $diff < 0) {
                    $total += abs($diff);
                }
            }

            return $total;
        })->toArray();
    }

    private function getTukarTransactions(?int $year = null, ?int $month = null)
    {
        $query = Transaction::with(['sale', 'purchase'])
            ->where('transaction_type', 'change')
            ->where('status', 'success');

        if ($year) {
            $query->whereYear('transaction_date', $year);
        }

        if ($month) {
            $query->whereMonth('transaction_date', $month);
        }

        return $query->get();
    }

    private function calculateChangeDiff(Transaction $trx): int
    {
        $saleAmount     = optional($trx->sale)->total_amount ?? 0;
        $purchaseAmount = optional($trx->purchase)->total_amount ?? 0;

        return $saleAmount - $purchaseAmount;
    }
}
