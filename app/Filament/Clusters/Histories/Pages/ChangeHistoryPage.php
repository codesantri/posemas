<?php

namespace App\Filament\Clusters\Histories\Pages;

use App\Models\Change;
use Filament\Pages\Page;
use Filament\Tables\Table;
use App\Filament\Clusters\Histories;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Pages\SubNavigationPosition;
use Filament\Tables\Concerns\InteractsWithTable;


class ChangeHistoryPage extends Page implements HasTable
{
    use InteractsWithTable;
    protected static ?string $model = Change::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $title = 'Pertukaran';

    protected static string $view = 'filament.clusters.histories.pages.change-history-page';
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $cluster = Histories::class;

    public function table(Table $table): Table
    {
        return $table
            ->query(Change::query())
            ->columns([
                TextColumn::make('transaction.invoice')->label('Invoice')->searchable(),
                TextColumn::make('transaction.transaction_date')->label('Tanggal'),
                TextColumn::make('customer.name')->label('Pelanggan')->searchable(),
                TextColumn::make('purchase.total_amount')->label('Produk Lama')->money('IDR'),
                TextColumn::make('sale.total_amount')->label('Produk Baru')->money('IDR'),
                TextColumn::make('difference')
                    ->label('Selisih')
                    ->state(function ($record) {
                        $sale = $record->sale->total_amount ?? 0;
                        $purchase = $record->purchase->total_amount ?? 0;
                        return $sale - $purchase;
                    })
                    ->money('IDR')
                    ->color(function ($record) {
                        $sale = $record->sale->total_amount ?? 0;
                        $purchase = $record->purchase->total_amount ?? 0;
                        return ($sale - $purchase) >= 0 ? 'success' : 'danger';
                    }),
                TextColumn::make('transaction.total_amount')->label('Jumlah Pemabayaran')->money('IDR'),
                TextColumn::make('transaction.payment_method')
                    ->label('Pembayaran')
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
            ]);
    }
}
