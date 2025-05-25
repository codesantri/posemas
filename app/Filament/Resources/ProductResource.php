<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ProductResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ProductResource\RelationManagers;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationGroup = 'Manajemen Produk';
    protected static ?string $navigationLabel = 'Produk';
    protected static ?string $modelLabel = 'Produk'; // Singular
    protected static ?string $pluralModelLabel = 'Produk'; // Plural
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama Produk')
                    ->required()
                    ->maxLength(255),

                Select::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->required()
                    ->rules(['exists:categories,id']),

                Grid::make(3)->schema([
                    Select::make('type_id')
                        ->label('Jenis')
                        ->relationship('type', 'name')
                        ->required()
                        ->rules(['exists:types,id']),

                    Select::make('karat_id')
                        ->label('Karat-Kadar')
                        ->options(function () {
                            return \App\Models\Karat::all()->mapWithKeys(function ($karat) {
                                return [$karat->id => $karat->karat . ' - ' . $karat->rate . '%'];
                            })->toArray();
                        })
                        ->required()
                        ->rules(['exists:karats,id']),

                    TextInput::make('weight')
                        ->label('Berat (gram)')
                        ->type('number')
                        ->step(0.01)
                        ->default(0.01)
                        ->minValue(0.01)
                        ->suffix('Gram')
                        ->required()
                        ->rules(['numeric', 'min:0.01']),
                ]),

                FileUpload::make('image')
                    ->label('Gambar Produk')
                    ->image()
                    ->directory('products')
                    ->maxSize(2048)
                    ->imagePreviewHeight('200')
                    ->columnSpanFull()
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()->with(['karat', 'category', 'type']) // <== ini WAJIB!
            )
            ->columns([
                // TextColumn::make('name')
                //     ->label('Nama Produk')
                //     ->formatStateUsing(function ($state, $record) {
                //         return <<<HTML
                //         <div style="display: flex; align-items: center; gap: 10px;">
                //             <img src="{$record->image}" alt="Image" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                //             <span>{$state}</span>
                //         </div>
                //     HTML;
                //     })
                //     ->html(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Naama Produk')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type.name')
                    ->label('Jenis')
                    ->searchable(),
                Tables\Columns\TextColumn::make('karat.karat')
                    ->label('Karat-Kadar')
                    ->formatStateUsing(function ($state, $record) {
                        return optional($record->karat)->karat . ' - ' . optional($record->karat)->rate . '%';
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('weight')
                    ->numeric()
                    ->label('Berat (gram)'),
                // Tables\Columns\TextColumn::make('harga_modal')
                //     ->label('Harga Modal')
                //     ->formatStateUsing(fn($state, $record) => 'Rp ' . number_format($record->harga_modal, 0, ',', '.')),

                // Tables\Columns\TextColumn::make('harga_jual')
                //     ->label('Harga Jual')
                //     ->formatStateUsing(fn($state, $record) => 'Rp ' . number_format($record->harga_jual, 0, ',', '.')),
                // Tables\Columns\TextColumn::make('stockTotals.total')
                //     ->label('Stok')
                //     ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListProducts::route('/'),
            'view' => Pages\ViewProduct::route('/{record}'),
        ];
    }
}
