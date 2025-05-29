<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Tables\Table;
use App\Models\Transaction;
use Illuminate\Support\Number;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Tables\Concerns\InteractsWithTable;

class TransactionHistories extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $model = Transaction::class;
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static string $view = 'filament.pages.transaction-histories';
    protected static ?string $title = 'Riwayat transaksi';

    public function table(Table $table): Table
    {
        return $table
            ->query(Transaction::with(['sale', 'purchase']))
            ->columns([
                TextColumn::make('invoice')->label('Invoice')->searchable(),

                TextColumn::make('transaction_date')
                    ->label('Tanggal Transaksi')
                    ->dateTime('d M Y H:i'),

                TextColumn::make('transaction_type')
                    ->label('Jenis Transaksi')
                    ->badge()
                    ->color(fn($record) => $this->getTypeColor($record))
                    ->formatStateUsing(fn($state, $record) => $this->getTypeLabel($record)),

                TextColumn::make('total_amount')
                    ->label('Total Transaksi')
                    ->money('IDR', true),

                TextColumn::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'cash' => 'success',
                        'online' => 'info',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'cash' => 'Tunai',
                        'online' => 'Online',
                        default => ucfirst($state),
                    }),

                TextColumn::make('status')
                    ->label('Status Pembayaran')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'success' => 'success',
                        'expired' => 'gray',
                        'failed' => 'danger',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'success' => 'Berhasil',
                        'expired' => 'Kadaluarsa',
                        'failed' => 'Gagal',
                        default => ucfirst($state),
                    }),
            ])
            ->filters([
                SelectFilter::make('transaction_type')
                    ->label('Jenis Transaksi')
                    ->options([
                        'sale' => 'Penjualan',
                        'purchase' => 'Pembelian',
                        'pawning' => 'Gadai',
                        'tukar_tambah' => 'Tukar Tambah',
                        'tukar_kurang' => 'Tukar Kurang',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            match ($data['value']) {
                                'sale', 'purchase', 'pawning' => $query->where('transaction_type', $data['value']),
                                'tukar_tambah' => $query->where('transaction_type', 'change')
                                    ->whereHas('sale')
                                    ->whereHas('purchase')
                                    ->whereHas('sale', function ($q) {
                                        $q->whereRaw('(SELECT total_amount FROM sales WHERE sales.transaction_id = transactions.id) > 
                                      (SELECT total_amount FROM purchases WHERE purchases.transaction_id = transactions.id)');
                                    }),
                                'tukar_kurang' => $query->where('transaction_type', 'change')
                                    ->whereHas('sale')
                                    ->whereHas('purchase')
                                    ->whereHas('sale', function ($q) {
                                        $q->whereRaw('(SELECT total_amount FROM sales WHERE sales.transaction_id = transactions.id) < 
                                      (SELECT total_amount FROM purchases WHERE purchases.transaction_id = transactions.id)');
                                    }),
                                default => null,
                            };
                        }
                    }),
                SelectFilter::make('transaction_date')
                    ->label('Tanggal Transaksi')
                    ->options(function () {
                        return Transaction::query()
                            ->selectRaw('DATE(transaction_date) as date')
                            ->distinct()
                            ->pluck('date', 'date')
                            ->toArray();
                    })
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereDate('transaction_date', $data['value']);
                        }
                    }),


                SelectFilter::make('month')
                    ->label('Bulan')
                    ->options([
                        '1' => 'Januari',
                        '2' => 'Februari',
                        '3' => 'Maret',
                        '4' => 'April',
                        '5' => 'Mei',
                        '6' => 'Juni',
                        '7' => 'Juli',
                        '8' => 'Agustus',
                        '9' => 'September',
                        '10' => 'Oktober',
                        '11' => 'November',
                        '12' => 'Desember',
                    ])
                    ->default(Carbon::now()->format('n'))
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereMonth('transaction_date', $data['value']);
                        }
                    }),

                SelectFilter::make('year')
                    ->label('Tahun')
                    ->options(function () {
                        $startYear = 2022;
                        $currentYear = Carbon::now()->year;
                        $years = range($startYear, $currentYear);
                        return array_combine($years, $years);
                    })
                    ->default(Carbon::now()->year)
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereYear('transaction_date', $data['value']);
                        }
                    }),
            ]);
    }

    private function getTypeLabel($record): string
    {
        if ($record->transaction_type === 'change') {
            $saleAmount = optional($record->sale)->total_amount ?? 0;
            $purchaseAmount = optional($record->purchase)->total_amount ?? 0;
            return $saleAmount > $purchaseAmount ? 'Tukar Tambah' : 'Tukar Kurang';
        }

        return match ($record->transaction_type) {
            'sale' => 'Penjualan',
            'purchase' => 'Pembelian',
            'pawning' => 'Gadai',
            default => ucfirst($record->transaction_type),
        };
    }

    private function getTypeColor($record): string
    {
        if ($record->transaction_type === 'change') {
            $saleAmount = optional($record->sale)->total_amount ?? 0;
            $purchaseAmount = optional($record->purchase)->total_amount ?? 0;
            return $saleAmount > $purchaseAmount ? 'success' : 'danger';
        }

        return match ($record->transaction_type) {
            'sale' => 'success',
            'purchase' => 'warning',
            'pawning' => 'info',
            default => 'secondary',
        };
    }

    protected function getFilteredQuery(): Builder
    {
        $query = Transaction::query()->with(['sale', 'purchase']);

        // Apply filters from the table
        if (method_exists($this, 'getTable')) {
            $table = $this->getTable();
            $filters = $table->getFilters();

            foreach ($filters as $filter) {
                $filter->apply(
                    $query,
                    $this->tableFilters[$filter->getName()] ?? []
                );
            }
        }

        return $query;
    }

    public function getHeading(): string|Htmlable
    {
        $query = $this->getFilteredQuery();
        $total = $query->sum('total_amount');
        $count = $query->count();
        $formattedTotal = Number::currency($total, 'IDR', 'id');

        // Get active filters from table property
        $activeFilters = collect($this->tableFilters ?? [])
            ->reject(fn($value) => empty($value))
            ->keys()
            ->map(function ($key) {
                // Map filter keys to human-readable labels
                return match ($key) {
                    'transaction_type' => 'Jenis Transaksi',
                    'month' => 'Bulan',
                    'year' => 'Tahun',
                    default => $key,
                };
            })
            ->implode(', ');

        $heading = "Total • {$formattedTotal}";
        return $heading;
    }
}
