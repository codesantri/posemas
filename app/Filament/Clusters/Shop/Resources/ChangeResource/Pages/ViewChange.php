<?php

namespace App\Filament\Clusters\Shop\Resources\ChangeResource\Pages;

use Filament\Actions;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use App\Filament\Clusters\Shop\Resources\ChangeResource;

class ViewChange extends ViewRecord
{
    protected static string $resource = ChangeResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Pelanggan')
                    ->schema([
                        TextEntry::make('sale.customer.name')
                            ->label('Nama Pelanggan'),
                        TextEntry::make('sale.customer.phone')
                            ->label('Nomor Telepon'),
                        TextEntry::make('sale.customer.address')
                            ->label('Alamat')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)->collapsible(),

                Section::make('Detail Transaksi')
                    ->schema([
                        TextEntry::make('invoice')
                            ->label('Nomor Invoice'),
                        TextEntry::make('created_at')
                            ->label('Tanggal')
                            ->dateTime(),
                        TextEntry::make('additional_payment')
                            ->label('Tambahan Pembayaran')
                            ->money('IDR')
                            ->state(function ($record) {
                                $purchase = $record->purchase->total_amount ?? 0;
                                $sale = $record->sale->total_amount ?? 0;
                                return max($sale - $purchase, 0); // hanya jika ada kekurangan bayar
                            })
                            ->color(function ($record) {
                                $purchase = $record->purchase->total_amount ?? 0;
                                $sale = $record->sale->total_amount ?? 0;
                                return ($sale - $purchase) >= 0 ? 'success' : 'danger';
                            }),

                        TextEntry::make('change_payment')
                            ->label('Kembalian Pembayaran')
                            ->money('IDR')
                            ->state(function ($record) {
                                $purchase = $record->purchase->total_amount ?? 0;
                                $sale = $record->sale->total_amount ?? 0;
                                return max($purchase - $sale, 0); // hanya jika toko perlu kembalikan uang
                            })
                            ->color(function ($record) {
                                $purchase = $record->purchase->total_amount ?? 0;
                                $sale = $record->sale->total_amount ?? 0;
                                return ($purchase - $sale) > 0 ? 'warning' : 'danger';
                            }),

                    ])
                    ->columns(2),

                Section::make('Produk Lama (Ditukarkan)')
                    ->schema([
                        RepeatableEntry::make('purchase.purchaseDetails')
                            ->label('')
                            ->schema([
                                TextEntry::make('product.name')
                                    ->label('Produk'),
                                TextEntry::make('quantity')
                                    ->label('Jml'),
                                TextEntry::make('weight')
                                    ->label('Berat (g)'),
                                TextEntry::make('buy_price')
                                    ->label('Harga/g')
                                    ->money('IDR'),
                                TextEntry::make('subtotal')
                                    ->label('Total')
                                    ->money('IDR'),
                            ])
                            ->columns(5)
                    ]),

                Section::make('Produk Baru (Diterima)')
                    ->schema([
                        RepeatableEntry::make('sale.saleDetails')
                            ->label('')
                            ->schema([
                                TextEntry::make('product.name')
                                    ->label('Produk'),
                                TextEntry::make('quantity')
                                    ->label('Jml'),
                                TextEntry::make('weight')
                                    ->label('Berat (g)'),
                                TextEntry::make('buy_price')
                                    ->label('Harga/g')
                                    ->money('IDR'),
                                TextEntry::make('subtotal')
                                    ->label('Total')
                                    ->money('IDR'),
                            ])
                            ->columns(5)
                    ]),

                Section::make('Ringkasan')
                    ->schema([
                        TextEntry::make('purchase.total_amount')
                            ->label('Total Nilai Tukar')
                            ->money('IDR'),

                        TextEntry::make('sale.total_amount')
                            ->label('Total Nilai Produk Baru')
                            ->money('IDR'),

                        TextEntry::make('difference')
                            ->label('Selisih')
                            ->money('IDR')
                            ->state(function ($record) {
                                $purchase = $record->purchase->total_amount ?? 0;
                                $sale = $record->sale->total_amount ?? 0;
                                return $sale - $purchase;
                            })
                            ->color(function ($record) {
                                $purchase = $record->purchase->total_amount ?? 0;
                                $sale = $record->sale->total_amount ?? 0;
                                return ($sale - $purchase) >= 0 ? 'success' : 'danger';
                            }),
                    ])
                    ->columns(3)

            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Kembali')
                ->color('danger')
                ->icon('heroicon-o-arrow-left')
                ->url(route('filament.admin.shop.resources.changes.index')),
            Actions\Action::make('gopay')
                ->label('Proses Transaksi')
                // ->color('')
                ->icon('heroicon-o-credit-card')
                ->action(function ($record, $data) {
                    return redirect()->route('filament.admin.shop.resources.changes.payment', $record->invoice);
                })
        ];
    }
}
