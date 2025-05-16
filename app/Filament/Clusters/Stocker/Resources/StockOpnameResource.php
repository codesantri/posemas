<?php

namespace App\Filament\Clusters\Stocker\Resources;

use App\Filament\Clusters\Stocker;
use App\Filament\Clusters\Stocker\Resources\StockOpnameResource\Pages;
use App\Filament\Clusters\Stocker\Resources\StockOpnameResource\RelationManagers;
use App\Models\StockOpname;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockOpnameResource extends Resource
{
    protected static ?string $model = StockOpname::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Stok Opname';
    protected static ?string $pluralLabel = 'Stok Opname';
    protected static ?string $modelLabel = 'Stok';

    protected static ?string $cluster = Stocker::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListStockOpnames::route('/'),
            // 'create' => Pages\CreateStockOpname::route('/create'),
            // 'edit' => Pages\EditStockOpname::route('/{record}/edit'),
        ];
    }


    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
