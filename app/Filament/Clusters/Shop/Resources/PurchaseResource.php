<?php

namespace App\Filament\Clusters\Shop\Resources;

use Filament\Forms;
use App\Models\Type;
use Filament\Tables;
use App\Models\Karat;
use App\Models\Product;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Purchase;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Filament\Clusters\Shop;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\SubNavigationPosition;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Wizard\Step;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Clusters\Shop\Resources\PurchaseResource\Pages;
use App\Filament\Clusters\Shop\Resources\PurchaseResource\RelationManagers;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = "Pembelian";
    protected static ?string $breadcrumb = 'Pembelian';
    protected static ?string $label = 'Pembelian';
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;


    protected static ?string $cluster = Shop::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make([
                    Split::make([
                        Section::make([
                            Select::make('customer_id')
                                ->label('Nama Pelanggan')
                                ->searchable()
                                ->required()
                                ->preload()
                                ->options(function () {
                                    return Customer::all()->mapWithKeys(function ($customer) {
                                        return [$customer->id => "{$customer->name} - {$customer->nik}"];
                                    });
                                })
                                ->createOptionForm([
                                    TextInput::make('name')
                                        ->label('Nama Lengkap')
                                        ->prefixIcon('heroicon-m-user')
                                        ->required()
                                        ->minLength(3)
                                        ->maxLength(100)
                                        ->rule('regex:/^[a-zA-Z\s\.\']+$/') // hanya huruf, spasi, titik, apostrof
                                        ->helperText('Hanya huruf, spasi, titik, dan apostrof.'),

                                    TextInput::make('nik')
                                        ->label('NIK')
                                        ->prefixIcon('heroicon-m-identification')
                                        ->required()
                                        ->numeric()
                                        ->minLength(16)
                                        ->maxLength(16)
                                        ->unique(table: 'customers', column: 'nik')
                                        ->helperText('16 digit sesuai KTP.'),

                                    TextInput::make('phone')
                                        ->label('Nomor Telepon')
                                        ->prefixIcon('heroicon-m-phone')
                                        ->tel()
                                        ->required()
                                        ->minLength(10)
                                        ->maxLength(15)
                                        ->telRegex('/^(\+62|62|0)8[1-9][0-9]{6,11}$/') // regex khas nomor Indo
                                        ->unique(table: 'customers', column: 'phone')
                                        ->helperText('Gunakan format +62 atau 08xxx.'),

                                    TextInput::make('address')
                                        ->label('Alamat')
                                        ->prefixIcon('heroicon-m-map-pin')
                                        ->required()
                                        ->minLength(5)
                                        ->maxLength(255)
                                        ->rule('regex:/^[a-zA-Z0-9\s,.\-\/]+$/') // Alamat standar
                                        ->helperText('Isi alamat lengkap, boleh pakai koma, titik, atau strip.'),
                                ])
                                ->createOptionUsing(function (array $data) {
                                    $customer = Customer::create($data);

                                    Notification::make()
                                        ->title("Pelanggan berhasil ditambahkan")
                                        ->body("{$customer->name} - {$customer->nik}")
                                        ->success()
                                        ->send();

                                    return $customer;
                                }),
                        ])->grow(false),
                        Section::make([
                            Repeater::make('products')
                                ->label('Data Produk')
                                ->schema([
                                    Grid::make(2)->schema([
                                        Select::make('product_name')
                                            ->label('Nama Produk')
                                            ->searchable()
                                            ->options(Product::pluck('name', 'name')) // key & value: name
                                            ->preload()
                                            ->required()
                                            ->createOptionForm([
                                                TextInput::make('name')
                                                    ->label('Nama Produk')
                                                    ->required()
                                                    ->minLength(3)
                                                    ->maxLength(100)
                                                    ->rule('regex:/^[a-zA-Z0-9\s\-\.]+$/')
                                            ]),

                                        Select::make('category_id')
                                            ->label('Kategori')
                                            ->options(Category::pluck('name', 'id'))
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                    ]),

                                    Grid::make(4)->schema([
                                        Select::make('type_id')
                                            ->label('Jenis')
                                            ->required()
                                            ->options(Type::pluck('name', 'id'))
                                            ->searchable()
                                            ->preload(),

                                        Select::make('karat_id')
                                            ->label('Karat-Kadar')
                                            ->required()
                                            ->options(Karat::all()->mapWithKeys(fn($karat) => [
                                                $karat->id => "{$karat->karat} - {$karat->rate}%",
                                            ]))
                                            ->searchable()
                                            ->preload(),

                                        TextInput::make('weight')
                                            ->label('Berat (gram)')
                                            ->required()
                                            ->numeric()
                                            ->step(0.01)
                                            ->minValue(0.01)
                                            ->maxValue(10000)
                                            ->default(0.01)
                                            ->suffix('Gram'),

                                        TextInput::make('quantity')
                                            ->label('Kuantitas')
                                            ->required()
                                            ->numeric()
                                            ->minValue(1)
                                            ->maxValue(1000)
                                            ->step(1)
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
                    ])->from('md')->columnSpanFull(),
                ]),
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
                Action::make('payment')
                    ->label('Proses Pembayaran')
                    ->icon('heroicon-m-credit-card')
                    ->color('success')
                    ->visible(fn($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Proses Pembayaran')
                    ->modalDescription('Apakah kamu yakin mau proses pembayaran untuk pembelian ini?')
                    ->modalButton('Ya, Proses Pembayaran')
                    ->action(function ($record, $data) {
                        return redirect()->route('filament.admin.shop.resources.purchases.payment', $record->invoice);
                    })
                    ->link(),
                // ->url(fn($record) => Pages\InvoicePurchase::getUrl([$record]))
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
            'payment' => Pages\PaymentPage::route('/payment/{invoice}'),
            // 'view' => Pages\ViewPurchase::route('/{record}'),
            // 'edit' => Pages\EditPurchase::route('/{record}/edit'),
        ];
    }
}
