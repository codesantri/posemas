<?php

namespace App\Filament\Clusters\Product\Resources;

use App\Filament\Clusters\Product;
use App\Filament\Clusters\Product\Resources\KaratResource\Pages;
use App\Filament\Clusters\Product\Resources\KaratResource\RelationManagers;
use App\Models\Karat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class KaratResource extends Resource
{
    protected static ?string $model = Karat::class;
    protected static ?string $cluster = Product::class;


    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    protected static ?string $navigationLabel = 'Karat';

    protected static ?string $modelLabel = 'Karat'; // Singular
    protected static ?string $pluralModelLabel = 'Karat'; // Plural

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('karat')
                    ->label('Karat')
                    ->maxLength(10)
                    ->required(),

                Forms\Components\TextInput::make('rate')
                    ->label('Kadar (0.00% - 100.00%)')
                    ->type('number') // ini kunci utama!
                    ->step(0.01)
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(0.01)
                    ->suffix('%')
                    ->required(),

                Forms\Components\TextInput::make('buy_price')
                    ->label('Harga Beli')
                    ->prefix('Rp')
                    ->mask(fn() => \Filament\Support\RawJs::make(<<<'JS'
                        $money($input, {
                            thousandsSeparator: '.',
                            decimalSeparator: ',',
                            precision: 0
                        })
                    JS))
                    ->stripCharacters(['.', ','])
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('sell_price')
                    ->label('Harga Jual')
                    ->prefix('Rp')
                    ->mask(fn() => \Filament\Support\RawJs::make(<<<'JS'
                        $money($input, {
                            thousandsSeparator: '.',
                            decimalSeparator: ',',
                            precision: 0
                        })
                    JS))
                    ->stripCharacters(['.', ','])
                    ->numeric()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('karat')
                    ->label('Karat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('rate')
                    ->label('Kadar')
                    ->suffix('%'),
                Tables\Columns\TextColumn::make('buy_price')
                    ->prefix('Rp ')
                    ->label('Harga Beli')
                    ->numeric(),
                Tables\Columns\TextColumn::make('sell_price')
                    ->prefix('Rp ')
                    ->label('Harga Jual')
                    ->numeric(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKarats::route('/'),
            // 'create' => Pages\CreateKarat::route('/create'),
            // 'edit' => Pages\EditKarat::route('/{record}/edit'),
        ];
    }
}
