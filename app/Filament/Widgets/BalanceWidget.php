<?php

namespace App\Filament\Widgets;

use App\Models\Pawning;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Transaction;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class BalanceWidget extends BaseWidget
{
    protected function getStats(): array
    {
        // Total Sale
        $totalSale = Sale::whereHas('transaction', fn($q) => $q->where('status', 'success')->where('transaction_type', 'sale'))
            ->sum('total_amount');

        // Total Purchase
        $totalPurchase = Purchase::whereHas('transaction', fn($q) => $q->where('status', 'success')->where('transaction_type', 'purchase'))
            ->sum('total_amount');

        // Total Pawning (transaction)
        $pawningTotal = Transaction::where('transaction_type', 'pawning')
            ->where('status', 'success')
            ->sum('total_amount');

        // Total Pawning estimated_value (active/paid_off)
        $pawningEstimated = Pawning::whereIn('status', ['active', 'paid_off'])
            ->sum('estimated_value');

        // Hitung Tukar Tambah dan Tukar Kurang dari change transaction
        $changeTotals = $this->getTukarChangeTotals();

        // Hitung Pendapatan
        $totalIncome = $totalSale + $changeTotals['tambah'] + $pawningTotal;

        // Hitung Pengeluaran
        $totalOutcome = $totalPurchase + $changeTotals['kurang'] + $pawningEstimated;

        // Profit
        $profit = $totalIncome - $totalOutcome;

        $format = fn($value) => 'Rp. ' . number_format($value, 0, ',', '.');

        return [
            Stat::make('Pendapatan', $format($totalIncome))
                ->description('Penjualan, bunga gadai, & Tukar Tambah')
                ->descriptionIcon('heroicon-m-currency-dollar', IconPosition::Before)
                ->color('success'),

            Stat::make('Pengeluaran', $format($totalOutcome))
                ->description('Pembelian, nilai gadai, dan Tukar Kurang')
                ->descriptionIcon('heroicon-m-currency-dollar', IconPosition::Before)
                ->color('warning'),

            Stat::make('Profit', $format($profit))
                ->description('Selisih antara pendapatan dan pengeluaran')
                ->descriptionIcon('heroicon-m-currency-dollar', IconPosition::Before)
                ->color('info'),
        ];
    }

    private function getTukarChangeTotals(): array
    {
        $year = now()->year;

        $transactions = Transaction::with(['sale', 'purchase'])
            ->where('transaction_type', 'change')
            ->where('status', 'success')
            ->whereYear('transaction_date', $year)
            ->get();

        $tambah = 0;
        $kurang = 0;

        foreach ($transactions as $trx) {
            $saleAmount = optional($trx->sale)->total_amount ?? 0;
            $purchaseAmount = optional($trx->purchase)->total_amount ?? 0;

            if ($saleAmount > $purchaseAmount) {
                $tambah += ($saleAmount - $purchaseAmount);
            } elseif ($saleAmount < $purchaseAmount) {
                $kurang += ($purchaseAmount - $saleAmount);
            }
        }

        return [
            'tambah' => $tambah,
            'kurang' => $kurang,
        ];
    }
}
