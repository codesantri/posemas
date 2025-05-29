<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Customer;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use App\Filament\Resources\CustomerResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CustomerResource\RelationManagers;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;
    protected static ?string $navigationLabel = 'Pelanggan';
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $breadcrumb = 'Pelanggan';



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
                    ->rule('regex:/^[a-zA-Z\s\.\']+$/'),

                TextInput::make('phone')
                    ->label('Nomor Telepon')
                    ->prefixIcon('heroicon-m-phone')
                    ->tel()
                    ->required()
                    ->minLength(10)
                    ->maxLength(15)
                    ->telRegex('/^(\+62|62|0)8[1-9][0-9]{6,11}$/')
                    ->unique(table: 'customers', column: 'phone'),

                TextInput::make('address')
                    ->label('Alamat')
                    ->prefixIcon('heroicon-m-map-pin')
                    ->required()
                    ->minLength(5)
                    ->maxLength(255)
                    ->rule('regex:/^[a-zA-Z0-9\s,.\-\/]+$/'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Lengkap')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->label('Alamat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telpone')
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
            'index' => Pages\ListCustomers::route('/'),
            // 'create' => Pages\CreateCustomer::route('/create'),
            // 'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
