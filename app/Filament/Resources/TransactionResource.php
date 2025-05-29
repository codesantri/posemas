<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Transaction;
use Illuminate\Support\Number;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Storage;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $breadcrumb = 'Riwayat transaksi';
    protected static ?string $navigationLabel = "Riwayat transaksi";

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice')->label('Invoice')->searchable(),

                TextColumn::make('transaction_date')
                    ->label('Tanggal Transaksi')
                    ->dateTime('d M Y H:i'),

                TextColumn::make('transaction_type')
                    ->label('Jenis Transaksi')
                    ->badge()
                    ->color(fn($record) => self::getTypeColor($record))
                    ->formatStateUsing(fn($state, $record) => self::getTypeLabel($record)),

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
                SelectFilter::make('transaction_date')
                    ->label('Tanggal')
                    ->options(function () {
                        return Transaction::query()
                            ->selectRaw('DATE(transaction_date) as date')
                            ->whereNotNull('transaction_date') // Hanya untuk dropdown (null gak bisa jadi option)
                            ->distinct()
                            ->orderBy('date', 'desc')
                            ->pluck('date', 'date')
                            ->toArray();
                    })
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereDate('transaction_date', $data['value']);
                        }
                        // kalau kosong, biarkan semua data termasuk null tetap tampil
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
                    ->default(null)
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
                        return array_combine(range($startYear, $currentYear), range($startYear, $currentYear));
                    })
                    ->default(null)
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereYear('transaction_date', $data['value']);
                        }
                    }),



            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->visible(fn($record) => $record->status !== 'success')
                    ->before(function ($record) {
                        match ($record->transaction_type) {
                            'sale' => self::deleteSale($record),
                            'purchase' => self::deletePurchase($record),
                            'change' => self::deleteChange($record),
                            'pawning' => self::deletePawning($record),
                            default => null,
                        };
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->action(function ($records) {
                        foreach ($records as $record) {
                            match ($record->transaction_type) {
                                'sale' => self::deleteSale($record),
                                'purchase' => self::deletePurchase($record),
                                'change' => self::deleteChange($record),
                                'pawning' => self::deletePawning($record),
                                default => null,
                            };

                            $record->delete(); // Tetap hapus record utamanya
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
        ];
    }

    private static function getTypeLabel($record): string
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

    private static function getTypeColor($record): string
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

    private static function deleteSale($record)
    {
        if ($record->sale) {
            // Hapus saleDetails jika ada
            $record->sale->saleDetails()->delete();
            $record->sale->delete();
        }
    }

    private static function deletePurchase($record)
    {
        if ($record->purchase) {
            $record->purchase->purchaseDetails()->delete();
            $record->purchase->delete();
        }
    }

    private static function deleteChange($record)
    {
        self::deleteSale($record);
        self::deletePurchase($record);
    }

    private static function deletePawning($record)
    {
        if ($record->pawning) {
            // Hapus gambar jika ada di details
            foreach ($record->pawning->details as $detail) {
                if ($detail->image) {
                    Storage::disk('public')->delete($detail->image);
                }
            }
            $record->pawning->details()->delete();
            $record->pawning->delete();
        }
    }
}
