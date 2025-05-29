<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TransactionChart extends ChartWidget
{
    protected static ?string $heading = 'Total Transaksi';
    protected static string $color = 'info';

    // Filter tahun yang dipilih
    public ?string $filterYear = null;

    public function mount(): void
    {
        $this->filterYear = (string) now()->year;
    }

    // Ini untuk bikin dropdown filter (Filament versi lama)
    protected function getFilters(): ?array
    {
        $years = Transaction::selectRaw('YEAR(transaction_date) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();

        return collect($years)->mapWithKeys(fn($year) => [$year => $year])->toArray();
    }

    // Update property filterYear dari filter UI
    public function updatedFilterYear($value)
    {
        $this->filterYear = $value;
        $this->resetChart(); // Refresh chart data
    }

    // Ambil data chart berdasarkan filterYear
    protected function getData(): array
    {
        $transactionTypes = ['sale', 'purchase', 'pawning', 'change'];
        $selectedYear = $this->filterYear ?? now()->year;

        $datasets = [];

        foreach ($transactionTypes as $type) {
            $monthlyTotals = Transaction::selectRaw('MONTH(transaction_date) as month, SUM(total_amount) as total')
                ->where('transaction_type', $type)
                ->where('status', 'success')
                ->whereYear('transaction_date', $selectedYear)
                ->groupBy(DB::raw('MONTH(transaction_date)'))
                ->pluck('total', 'month');

            $data = collect(range(1, 12))->map(fn($month) => round($monthlyTotals->get($month, 0), 2));

            $datasets[] = [
                'label' => $this->getLabelForType($type),
                'data' => $data,
                'fill' => true,
                'borderColor' => $this->getColorForType($type),
                'tension' => 0.4,
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => collect(range(1, 12))->map(fn($m) => now()->setMonth($m)->translatedFormat('M'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    private function getColorForType(string $type): string
    {
        return match ($type) {
            'sale' => '#22c55e',
            'pawning' => '#3b82f6',
            'purchase' => '#f59e0b',
            'change' => '#ef4444',
            default => '#6b7280',
        };
    }

    private function getLabelForType(string $type): string
    {
        return match ($type) {
            'sale' => 'Penjualan',
            'purchase' => 'Pembelian',
            'pawning' => 'Gadai',
            'change' => 'Pertukaran',
            default => ucfirst($type),
        };
    }
}
