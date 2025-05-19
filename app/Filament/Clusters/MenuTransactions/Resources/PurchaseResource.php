<?php

namespace App\Filament\Clusters\MenuTransactions\Resources;

use Filament\Forms;
use App\Models\Type;
use Filament\Tables;
use App\Models\Karat;
use App\Models\Stock;
use App\Models\Product;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Purchase;
use Filament\Forms\Form;
use App\Models\StockTotal;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Wizard\Step;
use App\Filament\Clusters\MenuTransactions;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Clusters\MenuTransactions\Resources\PurchaseResource\Pages;
use App\Filament\Clusters\MenuTransactions\Resources\PurchaseResource\RelationManagers;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = "Pembelian";
    protected static ?string $breadcrumb = 'Pembelian';
    protected static ?string $label = 'Pembelian';


    protected static ?string $cluster = MenuTransactions::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Data Pelanggan')
                        ->icon('heroicon-m-user')
                        ->schema([
                            TextInput::make('customer_name')
                                ->label('Nama Lengkap')
                                // ->required()
                                ->maxLength(100)
                                ->prefixIcon('heroicon-m-user'),

                            TextInput::make('nik')
                                ->label('NIK')
                                // ->required()
                                ->maxLength(20)
                                ->prefixIcon('heroicon-m-identification'),

                            TextInput::make('phone')
                                ->label('No. Handphone')
                                // ->required()
                                ->tel()
                                ->maxLength(15)
                                ->prefixIcon('heroicon-m-phone'),

                            TextInput::make('address')
                                ->label('Alamat')
                                // ->required()
                                ->maxLength(255)
                                ->prefixIcon('heroicon-m-map-pin'),
                        ])->columnSpanFull(),

                    Step::make('Data Produk')
                        ->icon('heroicon-m-shopping-bag')
                        ->schema([
                            Repeater::make('products')
                                ->schema([
                                    Grid::make(2)->schema([
                                        Select::make('product_name')
                                            ->label('Nama Produk')
                                            ->searchable()
                                            ->options(Product::pluck('name', 'name')) // key dan value sama: 'name'
                                            ->preload()
                                            ->createOptionForm([ // jika kamu ingin bisa tambah baru dari sini
                                                TextInput::make('name')->label('Nama Produk')->required(),
                                            ])
                                        // ->allowCustomValue()
                                        // ->maxLength(100)
                                        ,

                                        Select::make('category_id')
                                            ->label('Kategori')
                                            // ->required()
                                            ->options(Category::all()->pluck('name', 'id'))
                                            ->searchable()
                                            ->preload(),
                                    ]),

                                    Grid::make(4)->schema([
                                        Select::make('type_id')
                                            ->label('Jenis')
                                            // ->required()
                                            ->options(Type::all()->pluck('name', 'id'))
                                            ->searchable()
                                            ->preload(),

                                        Select::make('karat_id')
                                            ->label('Karat-Kadar')
                                            // ->required()
                                            ->options(Karat::all()->mapWithKeys(fn($karat) => [
                                                $karat->id => "{$karat->karat} - {$karat->rate}%"
                                            ]))
                                            ->searchable()
                                            ->preload(),

                                        TextInput::make('weight')
                                            ->label('Berat (gram)')
                                            // ->required()
                                            ->numeric()
                                            ->step(0.01)
                                            ->minValue(0.01)
                                            ->default(0.01)
                                            ->suffix('Gram'),
                                        TextInput::make('quantity')
                                            ->label('Kuantitas')
                                            // ->required()
                                            ->numeric()
                                            ->step(0.01)
                                            ->minValue(1)
                                            ->default(1),
                                    ]),
                                ])
                                ->columns(1)
                                ->minItems(1)
                                ->collapsible()
                                ->cloneable()
                                ->itemLabel(fn(array $state): ?string => $state['product_name'] ?? null)
                                ->createItemButtonLabel('Tambah'),
                        ]),
                ])->columnSpanFull(),
            ]);
    }


    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('status', '!=', 'success');
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice')
                    ->label('Invoice')
                    ->searchable(),
                Tables\Columns\TextColumn::make('purchase_details_count')
                    ->label('Item')
                    ->counts('purchaseDetails'),
                TextColumn::make('status')
                    ->label('Status')
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'success' => 'success',
                        'expired' => 'gray',
                        'failed' => 'danger',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'success' => 'Berhasil',
                        'expired' => 'Kedaluwarsa',
                        'failed' => 'Gagal',
                        default => ucfirst($state),
                    }),
                TextColumn::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->color(fn(string $state): string => match ($state) {
                        'cash' => 'gray',
                        'online' => 'blue',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'cash' => 'Tunai',
                        'online' => 'Online',
                        default => ucfirst($state),
                    }),
                IconColumn::make('is_featured')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark')
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('proses_payment')
                    ->label('Proses Pembayaran')
                    ->icon('heroicon-m-credit-card')
                    ->color('success')
                    ->button()
                    ->url(fn($record) => Pages\InvoicePurchase::getUrl([$record]))
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
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
            'invoice' => Pages\InvoicePurchase::route('/invoice/{invoice}'),
            // 'view' => Pages\ViewPurchase::route('/{record}'),
            // 'edit' => Pages\EditPurchase::route('/{record}/edit'),
        ];
    }
}
