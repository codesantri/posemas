<?php

namespace App\Filament\Clusters\Shop\Resources;

use Filament\Forms;
use App\Models\Type;
use Filament\Tables;
use App\Models\Karat;
use App\Models\Change;
use App\Models\Product;
use App\Models\Category;
use App\Models\Customer;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Filament\Clusters\Shop;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use Filament\Pages\SubNavigationPosition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Clusters\Shop\Resources\ChangeResource\Pages;
use App\Filament\Clusters\Shop\Resources\ChangeResource\RelationManagers;

class ChangeResource extends Resource
{
    protected static ?string $model = Change::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationLabel = 'Pertukaran';
    protected static ?string $label = "Pertukaran";
    protected static ?string $breadcrumb = 'Pertukaran';
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $cluster = Shop::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Card::make([
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
                    })->columnSpanFull(),
                Section::make('Produk Lama')
                    ->description('Informasi produk yang sebelumnya digunakan oleh pelanggan.')
                    ->schema([
                        Repeater::make('olds')
                            ->label('')
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
                                            })->columnSpan(10),
                                        TextInput::make('quantity')
                                            ->label('Jumlah')
                                            ->numeric()
                                            ->default(1)
                                            ->required()->columnSpan(2),
                                    ])

                            ])->addActionLabel('Tambah'),
                    ])->collapsed(false),
                Section::make('Produk Baru')
                    ->description('Detail produk pengganti yang akan dicatat dalam sistem.')
                    ->schema([
                        Repeater::make('news')
                            ->label('')
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
                    ])->collapsed(),
                // ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction.invoice')
                    ->label('Invoice')
                    ->searchable(),
                Tables\Columns\TextColumn::make('purchase.total_amount')
                    ->label('Nilai Produk Lama')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('sale.total_amount')
                    ->label('Nilai Produk Baru')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('difference')
                    ->label('Selisih')
                    ->state(function ($record) {
                        $sale = $record->sale->total_amount ?? 0;
                        $purchase = $record->purchase->total_amount ?? 0;
                        return $sale - $purchase;
                    })
                    ->money('IDR')
                    ->color(function ($record) {
                        $sale = $record->sale->total_amount ?? 0;
                        $purchase = $record->purchase->total_amount ?? 0;
                        return ($sale - $purchase) >= 0 ? 'success' : 'danger';
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('confirmation')
                    ->label('Konfirmasi')
                    ->icon('heroicon-m-paper-airplane')
                    ->color('success')
                    ->visible(fn($record) => $record->transaction->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Proses Pembayaran')
                    ->modalDescription('Apakah kamu yakin mau proses pembayaran untuk penggadaian ini?')
                    ->modalButton('Ya, Proses Pembayaran')
                    ->action(function ($record, $data) {
                        return redirect()->route('filament.admin.shop.resources.changes.payment', $record->transaction->invoice);
                    }),
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListChanges::route('/'),
            'create' => Pages\CreateChange::route('/create'),
            'view' => Pages\ViewChange::route('/{record}'),
            'payment' => Pages\PaymentCangePage::route('/payment/{record}'),
        ];
    }

    public static function resolveRecordRouteBinding(string|int $key): ?Model
    {
        // First try to find by Change ID if numeric
        if (is_numeric($key)) {
            return parent::resolveRecordRouteBinding($key);
        }

        // Otherwise look for related transaction invoice
        return static::getModel()::whereHas('transaction', function ($query) use ($key) {
            $query->where('invoice', $key);
        })->first();
    }
}
