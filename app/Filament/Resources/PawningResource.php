<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PawningResource\Pages;
use App\Filament\Resources\PawningResource\RelationManagers;
use App\Models\Pawning;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PawningResource extends Resource
{
    protected static ?string $model = Pawning::class;
    protected static ?string $navigationGroup = 'Riwayat Transaksi';
    protected static ?string $navigationLabel = 'Gadai/Titipan';
    protected static ?string $navigationIcon = 'heroicon-o-clock';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('customer_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                Forms\Components\DatePicker::make('pawn_date')
                    ->required(),
                Forms\Components\TextInput::make('total_weight')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('estimated_value')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('loan_value')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('interest_rate')
                    ->required()
                    ->numeric(),
                Forms\Components\DatePicker::make('due_date')
                    ->required(),
                Forms\Components\TextInput::make('status')
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pawn_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_weight')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estimated_value')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('loan_value')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('interest_rate')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'edit' => Pages\EditPawning::route('/{record}/edit'),
        ];
    }
}
