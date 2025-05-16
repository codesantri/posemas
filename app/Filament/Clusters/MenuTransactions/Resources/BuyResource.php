<?php

namespace App\Filament\Clusters\MenuTransactions\Resources;

use App\Models\Buy;
use Filament\Forms;
use App\Models\Type;
use Filament\Tables;
use App\Models\Karat;
use App\Models\Product;
use App\Models\Category;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Illuminate\Support\Facades\Blade;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Wizard\Step;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Clusters\MenuTransactions;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Clusters\MenuTransactions\Resources\BuyResource\Pages;
use App\Filament\Clusters\MenuTransactions\Resources\BuyResource\RelationManagers;

class BuyResource extends Resource
{
    protected static ?string $model = Product::class;

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

                            TextInput::make('phone')
                                ->label('No. Handphone')
                                // ->required()
                                ->tel()
                                ->maxLength(15)
                                ->prefixIcon('heroicon-m-phone'),

                            TextInput::make('nik')
                                ->label('NIK')
                                // ->required()
                                ->maxLength(20)
                                ->prefixIcon('heroicon-m-identification'),

                            TextInput::make('address')
                                ->label('Alamat')
                                // ->required()
                                ->maxLength(255)
                                ->columnSpanFull()
                                ->prefixIcon('heroicon-m-map-pin'),
                        ])
                        ->columns(2),

                    Step::make('Data Produk')
                        ->icon('heroicon-m-shopping-cart')
                        ->schema([
                            Repeater::make('products')
                                ->schema([
                                    Grid::make(2)->schema([
                                        TextInput::make('product_name')
                                            ->label('Nama Produk')
                                            // ->required()
                                            ->maxLength(100),

                                        Select::make('category_id')
                                            ->label('Kategori')
                                            // ->required()
                                            ->options(Category::all()->pluck('name', 'id'))
                                            ->searchable()
                                            ->preload(),
                                    ]),

                                    Grid::make(3)->schema([
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
                                    ]),

                                    FileUpload::make('image')
                                        ->label('Gambar Produk')
                                        ->image()
                                        ->directory('products')
                                        ->maxSize(2048)
                                        ->imagePreviewHeight('150')
                                        ->downloadable()
                                        ->openable()
                                        ->columnSpanFull(),
                                ])
                                ->columns(1)
                                ->minItems(1)
                                ->collapsible()
                                ->cloneable()
                                ->itemLabel(fn(array $state): ?string => $state['product_name'] ?? null)
                                ->createItemButtonLabel('Tambah'),
                        ]),

                    Step::make('Pembayaran')
                        ->icon('heroicon-m-credit-card')
                        ->schema([
                            TextInput::make('payment_amount')
                                ->label('Pembayaran')
                                // ->required()
                                ->numeric()
                                ->minValue(0)
                                ->prefix('Rp')
                                ->step(1000),

                            TextInput::make('change_amount')
                                ->label('Kembalian')
                                ->numeric()
                                ->minValue(0)
                                ->prefix('Rp')
                                ->readOnly(),

                            Radio::make('payment_method')
                                ->label('Metode Pembayaran')
                                // ->required()
                                ->options([
                                    'cash' => 'Tunai',
                                    'transfer' => 'Transfer Bank',
                                    'qris' => 'QRIS',
                                ])
                                ->default('cash')
                                ->inline(),
                        ])
                        ->columns(2),
                ])->columnSpanFull(),
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
            'index' => Pages\ListBuys::route('/'),
            // 'create' => Pages\CreateBuy::route('/create'),
            // 'edit' => Pages\EditBuy::route('/{record}/edit'),
        ];
    }
}
