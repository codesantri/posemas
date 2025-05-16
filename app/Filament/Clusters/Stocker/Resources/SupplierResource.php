<?php

namespace App\Filament\Clusters\Stocker\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Supplier;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\Clusters\Stocker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Radio;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Clusters\Stocker\Resources\SupplierResource\Pages;
use App\Filament\Clusters\Stocker\Resources\SupplierResource\RelationManagers;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $cluster = Stocker::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->maxLength(255)
                    ->label('Nama Supplier')
                    ->required()->columnSpanFull(),
                Forms\Components\TextInput::make('address')
                    ->maxLength(255)
                    ->label('Alamat')
                    ->columnSpanFull()
                    ->required(),
                Radio::make('status')
                    ->options([
                        true => 'Aktif',
                        false => 'Non Aktif',
                    ])->label('Status')
                    ->default(true)
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Supplier')
                    ->searchable(),

                TextColumn::make('address')
                    ->label('Alamat')
                    ->wrap()
                    ->searchable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => fn($state): bool => $state === true,
                        'danger' => fn($state): bool => $state === false,
                    ])
                    ->formatStateUsing(fn($state): string => $state ? 'Aktif' : 'Non Aktif')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
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
            'index' => Pages\ListSuppliers::route('/'),
            // 'create' => Pages\CreateSupplier::route('/create'),
            // 'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
