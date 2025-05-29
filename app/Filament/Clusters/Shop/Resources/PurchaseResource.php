<?php

namespace App\Filament\Clusters\Shop\Resources;

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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\SubNavigationPosition;
use App\Filament\Clusters\Shop\Resources\PurchaseResource\Pages;

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
                    Select::make('customer_id')
                        ->label('Nama Pelanggan')
                        ->searchable()
                        ->required()
                        ->preload()
                        ->options(function () {
                            return Customer::all()->mapWithKeys(function ($customer) {
                                return [$customer->id => "{$customer->name}"];
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
                    Repeater::make('products')
                        ->label('Data Produk')
                        ->schema([
                            Grid::make(12)
                                ->schema([
                                    Select::make('product_id')
                                        ->label('Nama Produk')

                                        ->searchable()
                                        ->required()
                                        ->preload()
                                        ->options(function () {
                                            return Product::all()->mapWithKeys(function ($product) {
                                                return [$product->id => $product->name . ' / ' . $product->karat->karat . '-' . $product->karat->rate . '%' . ' / ' . $product->category->name . ' / ' . $product->type->name];
                                            });
                                        })
                                        ->columnSpan(10), // Lebar 8 dari 12 kolom

                                    TextInput::make('quantity')
                                        ->label('Jumlah')
                                        ->numeric()
                                        ->default(1)
                                        ->required()
                                        ->columnSpan(2), // Lebar 4 dari 12 kolom
                                ])

                        ])->addActionLabel('Tambah'),
                ]),
            ]);
    }


    // public static function getEloquentQuery(): Builder
    // {
    //     return parent::getEloquentQuery()
    //         ->where('transaction.status', '!=', 'success');
    // }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction.invoice')
                    ->label('Faktur')
                    ->searchable(),
                IconColumn::make('transaction.status')
                    ->label('Status')
                    ->icon(fn(string $state): string => match ($state) {
                        'pending' => 'heroicon-o-clock',
                        'success' => 'heroicon-o-check-circle',
                        'expired' => 'heroicon-o-x-circle',
                        'failed' => 'heroicon-o-exclamation-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'success' => 'success',
                        'expired' => 'gray',
                        'failed' => 'danger',
                        default => 'secondary',
                    })
                    ->tooltip(fn(string $state): string => match ($state) {
                        'pending' => 'Menunggu Pembayaran',
                        'success' => 'Pembayaran Berhasil',
                        'expired' => 'Kedaluwarsa',
                        'failed' => 'Gagal',
                        default => ucfirst($state),
                    }),
                TextColumn::make('transaction.payment_method')
                    ->label('Pembayaran')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'cash' => 'success',
                        'online' => 'info',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'cash' => 'Tunai',
                        'online' => 'Online',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('transaction.total_amount')
                    ->label('Jumlah')->money('IDR'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('payment')
                    ->label('Proses Pembayaran')
                    ->icon('heroicon-m-credit-card')
                    ->color('success')
                    ->visible(fn($record) => $record->transaction->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Proses Pembayaran')
                    ->modalDescription('Apakah kamu yakin mau proses pembayaran untuk pembelian ini?')
                    ->modalButton('Ya, Proses Pembayaran')
                    ->action(function ($record, $data) {
                        return redirect()->route('filament.admin.shop.resources.purchases.payment', $record->transaction->invoice);
                    })
                    ->link(),
                Action::make('print')
                    ->label('Cetak')
                    ->icon('heroicon-m-printer')
                    ->color('danger')
                    ->visible(fn($record) => $record->transaction->status === 'success')
                    ->requiresConfirmation()
                    ->modalHeading('Cetak Nota')
                    ->modalDescription('Apakah kamu yakin mau cetak nota untuk pesanan ini?')
                    ->modalButton('Ya, Cetak')
                    ->action(function ($record, $data) {
                        return redirect()->route('print.purchase', $record->transaction->invoice);
                    })
                    ->link(),
                // ->url(fn($record) => Pages\InvoicePurchase::getUrl([$record]))
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
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
