<?php

namespace App\Filament\Clusters\Shop\Resources;

use Filament\Forms;
use App\Models\Sale;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Filament\Clusters\Shop;
use Filament\Resources\Resource;
use Filament\Pages\SubNavigationPosition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Clusters\Shop\Resources\SaleResource\Pages;
use App\Filament\Clusters\Shop\Resources\SaleResource\RelationManagers;

class SaleResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $cluster = Shop::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Penjualan';
    protected static ?string $breadcrumb = 'Penjualan';
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;


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
            'index' => Pages\ListSales::route('/'),
            'cart' => Pages\CartPage::route('/cart'),
            'checkout' => Pages\CheckoutPage::route('/checkout/{inv}'),
            'orders' => Pages\OrdersPage::route('/orders'),
        ];
    }

    public function getHeading(): string|Htmlable
    {
        return '';
    }
}
