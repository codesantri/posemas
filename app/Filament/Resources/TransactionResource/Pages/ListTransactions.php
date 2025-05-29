<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Widgets\TransactionWidget;
use App\Filament\Resources\TransactionResource;
use Illuminate\Contracts\Support\Htmlable;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;
    protected static ?string $title = 'Riwayat transaksi';

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua Transaksi'),

            'sale' => Tab::make('Penjualan')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->where('transaction_type', 'sale')
                ),

            'purchase' => Tab::make('Pembelian')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->where('transaction_type', 'purchase')
                ),

            'pawning' => Tab::make('Penggadaian')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->where('transaction_type', 'pawning')
                ),

            'tukar_tambah' => Tab::make('Tukar Tambah')
                ->modifyQueryUsing(function (Builder $query) {
                    $query->where('transaction_type', 'change')
                        ->whereHas('sale', function ($q) {
                            $q->whereNotNull('total_amount');
                        })
                        ->whereHas('purchase', function ($q) {
                            $q->whereNotNull('total_amount');
                        })
                        ->whereRaw('
                (SELECT total_amount FROM sales WHERE sales.transaction_id = transactions.id) > 
                (SELECT total_amount FROM purchases WHERE purchases.transaction_id = transactions.id)
            ');
                }),

            'tukar_kurang' => Tab::make('Tukar Kurang')
                ->modifyQueryUsing(function (Builder $query) {
                    $query->where('transaction_type', 'change')
                        ->whereHas('sale', function ($q) {
                            $q->whereNotNull('total_amount');
                        })
                        ->whereHas('purchase', function ($q) {
                            $q->whereNotNull('total_amount');
                        })
                        ->whereRaw('
                (SELECT total_amount FROM sales WHERE sales.transaction_id = transactions.id) < 
                (SELECT total_amount FROM purchases WHERE purchases.transaction_id = transactions.id)
            ');
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TransactionWidget::class,
        ];
    }
}
