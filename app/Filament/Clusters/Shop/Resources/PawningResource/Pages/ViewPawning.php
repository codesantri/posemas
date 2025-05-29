<?php

namespace App\Filament\Clusters\Shop\Resources\PawningResource\Pages;

use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Repeater;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use App\Filament\Clusters\Shop\Resources\PawningResource;

class ViewPawning extends ViewRecord
{
    protected static string $resource = PawningResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }



    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Transaksi')
                    ->schema([
                        TextEntry::make('transaction.invoice')->label('No. Invoice'),
                        TextEntry::make('estimated_value')->label('Estimasi Nilai')->prefix('Rp')->money('IDR'),
                    ])
                    ->columns(2),

                Section::make('Informasi Pelanggan')
                    ->schema([
                        TextEntry::make('customer.name')->label('Nama Pelanggan'),
                        TextEntry::make('customer.phone')->label('Nomor Telepon'),
                        TextEntry::make('customer.address')->label('Alamat'),
                    ])
                    ->columns(2),

                Section::make('Produk yang Digadaikan')
                    ->schema([
                        RepeatableEntry::make('details')
                            ->label('Daftar Produk')
                            ->schema([
                                TextEntry::make('name')->label('Nama Produk'),
                                TextEntry::make('weight')->label('Berat (gr)'),
                                TextEntry::make('quantity')->label('Jumlah'),
                                ImageEntry::make('image')->label('Foto')->height(100),
                            ])
                            ->columns(2)

                    ]),
            ]);
    }
}
