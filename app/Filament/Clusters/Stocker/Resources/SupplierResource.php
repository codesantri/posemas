<?php

namespace App\Filament\Clusters\Stocker\Resources;

use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use App\Models\Supplier;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\Clusters\Stocker;
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
        return $form->schema([
            TextInput::make('name')
                ->label('Nama Supplier')
                ->required()
                ->maxLength(255)
                ->rules(['string', 'max:255'])
                ->columnSpanFull(),

            TextInput::make('address')
                ->label('Alamat')
                ->required()
                ->maxLength(255)
                ->rules(['string', 'max:255'])
                ->columnSpanFull(),

            Radio::make('status')
                ->label('Status')
                ->options([
                    true => 'Aktif',
                    false => 'Non Aktif',
                ])
                ->default(true)
                ->required()
                ->rules(['boolean']),
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
