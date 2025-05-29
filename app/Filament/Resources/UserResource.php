<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\UserResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UserResource\RelationManagers;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'Pengguna';
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $breadcrumb = 'Pengguna';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->prefixIcon('heroicon-m-user')
                    ->required()
                    ->minLength(3)
                    ->maxLength(100)
                    ->rules(['regex:/^[a-zA-Z\s\.\']+$/']),

                TextInput::make('username')
                    ->label('Username')
                    ->prefixIcon('heroicon-m-at-symbol')
                    ->required()
                    ->minLength(4)
                    ->maxLength(15)
                    ->telRegex('/^(\+62|62|0)8[1-9][0-9]{6,11}$/')
                    ->unique(table: 'users', column: 'username'),

                TextInput::make('password')
                    ->label('Kata Sandi')
                    ->password()
                    ->required()
                    ->revealable()
                    ->minLength(4)
                    ->maxLength(64)
                    ->dehydrated(true),
                // ->visibleOn('create'),

                Select::make('roles')
                    ->label('Peran Pengguna')
                    ->prefixIcon('heroicon-m-shield-check')
                    ->relationship('roles', 'name')
                    ->required()
                    ->native(false),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Lengkap')
                    ->searchable(),
                Tables\Columns\TextColumn::make('username')
                    ->label('Username')
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Peran Pengguna')
                    ->searchable(),
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
            'index' => Pages\ListUsers::route('/'),
            // 'create' => Pages\CreateUser::route('/create'),
            // 'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
