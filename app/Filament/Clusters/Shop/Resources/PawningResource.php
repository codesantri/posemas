<?php

namespace App\Filament\Clusters\Shop\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Pawning;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Filament\Clusters\Shop;
use Filament\Resources\Resource;
use Filament\Pages\SubNavigationPosition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Clusters\Shop\Resources\PawningResource\Pages;
use App\Filament\Clusters\Shop\Resources\PawningResource\RelationManagers;

class PawningResource extends Resource
{
    protected static ?string $model = Pawning::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale';
    protected static ?string $navigationLabel = 'Penggadaian';
    protected static ?string $breadcrumb = 'Penggadaian';
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $cluster = Shop::class;

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
            'index' => Pages\ListPawnings::route('/'),
            'create' => Pages\CreatePawning::route('/create'),
            // 'edit' => Pages\EditPawning::route('/{record}/edit'),
        ];
    }
}
