<?php

namespace App\Filament\Resources\ProductResource\Pages;

use Filament\Infolists;
use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use Filament\Support\Enums\FontWeight;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Split;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\Section;
use App\Filament\Resources\ProductResource;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Kembali')
                ->url(route('filament.admin.resources.products.index'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray'),
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolists\Infolist $infolist): Infolists\Infolist
    {
        return $infolist
            ->schema([
                Fieldset::make('Informasi Produk')
                    ->schema([
                        ImageEntry::make('image')
                            ->label('')
                            ->extraImgAttributes([
                                'style' => 'max-width: 100%; height: auto; border-radius: 8px;',
                            ])->columnSpan(1),
                        Grid::make(3)->schema([
                            TextEntry::make('category.name')
                                ->label('Kategori'),
                            TextEntry::make('name')
                                ->label('Nama Produk'),
                            TextEntry::make('type.name')
                                ->label('Jenis'),

                            TextEntry::make('karat.karat')
                                ->label('Karat-Kadar')
                                ->formatStateUsing(fn($state, $record) => $record->karat->karat . ' - ' . $record->karat->rate . '%'),

                            TextEntry::make('weight')
                                ->label('Berat (gram)'),
                            TextEntry::make('harga_modal')
                                ->label('Harga Modal')
                                ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                            TextEntry::make('harga_jual')
                                ->label('Harga Jual')
                                ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),
                            TextEntry::make('stockTotals.total')
                                ->label('Stok'),
                        ])->columnSpan(1),
                    ]),
            ]);
    }
}
