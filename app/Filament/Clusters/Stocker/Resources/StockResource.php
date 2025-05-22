<?php

namespace App\Filament\Clusters\Stocker\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Stock;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\Clusters\Stocker;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use App\Filament\Clusters\Stocker\Resources\StockResource\Pages;

class StockResource extends Resource
{
    protected static ?string $model = Stock::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-up-down';
    protected static ?string $navigationLabel = 'Stok Produk';
    protected static ?string $pluralLabel = 'Stok';
    protected static ?string $modelLabel = 'Stok';

    protected static ?string $cluster = Stocker::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()->schema([
                    Repeater::make('Stocks')
                        ->label('Input Beberapa Stok')
                        ->schema([
                            Grid::make(2)->schema([
                                Select::make('product_id')
                                    ->label('Produk')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->rules(['exists:products,id']),

                                Select::make('supplier_id')
                                    ->label('Supplier')
                                    ->relationship('supplier', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->rules(['exists:suppliers,id']),
                            ]),

                            Grid::make(2)->schema([
                                TextInput::make('stock_quantity')
                                    ->label('Jumlah Stok')
                                    ->type('number')
                                    ->minValue(1)
                                    ->required()
                                    ->rules(['numeric', 'min:1']),

                                DatePicker::make('received_at')
                                    ->label('Tanggal Diterima')
                                    ->required()
                                    ->rules(['date']),
                            ]),
                        ])
                        ->minItems(1)
                        ->createItemButtonLabel('Tambah Stok'),
                ])
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable(),

                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable(),

                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Jumlah')
                    ->numeric(),

                Tables\Columns\TextColumn::make('received_at')
                    ->label('Tanggal Diterima')
                    ->date('d/m/Y'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product')
                    ->label('Produk')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('supplier')
                    ->label('Supplier')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->before(function (Stock $record) {
                        \App\Models\StockTotal::where('product_id', $record->product_id)
                            ->decrement('total', $record->stock_quantity);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            $products = $records->groupBy('product_id');

                            foreach ($products as $productId => $productStocks) {
                                $totalReduction = $productStocks->sum('stock_quantity');
                                \App\Models\StockTotal::where('product_id', $productId)
                                    ->decrement('total', $totalReduction);
                            }
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStocks::route('/'),
            'create' => Pages\CreateStock::route('/create'),
            // 'edit' => Pages\EditStock::route('/{record}/edit'),
        ];
    }
}
