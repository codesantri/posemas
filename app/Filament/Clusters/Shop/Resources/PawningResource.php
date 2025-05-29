<?php

namespace App\Filament\Clusters\Shop\Resources;

use Filament\Forms;
use App\Models\Type;
use Filament\Tables;
use App\Models\Karat;
use App\Models\Pawning;
use App\Models\Product;
use App\Models\Category;
use App\Models\Customer;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Filament\Clusters\Shop;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Pages\SubNavigationPosition;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Clusters\Shop\Resources\PawningResource\Pages;
use App\Filament\Clusters\Shop\Resources\PawningResource\RelationManagers;

class PawningResource extends Resource
{
    protected static ?string $model = Pawning::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale';
    protected static ?string $navigationLabel = 'Penggadaian';
    protected static ?string $label = "Penggadaian";
    protected static ?string $breadcrumb = 'Penggadaian';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $cluster = Shop::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Data Produk')
                        ->schema([
                            Repeater::make('products')
                                ->label('Data Produk')
                                ->schema([
                                    Grid::make(2)->schema([
                                        Select::make('name')
                                            ->label('Nama Produk')
                                            ->searchable()
                                            ->options(Product::pluck('name', 'name'))
                                            ->preload()
                                            ->required()
                                            ->createOptionForm([
                                                TextInput::make('name')
                                                    ->label('Nama Produk')
                                                    ->required()
                                                    ->minLength(3)
                                                    ->maxLength(100)
                                                    ->rule('regex:/^[a-zA-Z0-9\s\-\.]+$/')
                                                    ->rule('unique:products,name'), // Validasi unik produk baru
                                            ])
                                            ->createOptionUsing(fn(array $data) => $data['name']),

                                        Select::make('category_id')
                                            ->label('Kategori')
                                            ->options(Category::pluck('name', 'id'))
                                            ->required()
                                            ->searchable()
                                            ->preload(),
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
                                            ->suffix('Gram')
                                            ->rule('gte:0.01'),

                                        TextInput::make('quantity')
                                            ->label('Kuantitas')
                                            ->required()
                                            ->numeric()
                                            ->minValue(1)
                                            ->maxValue(1000)
                                            ->step(1)
                                            ->default(1)
                                            ->rule('gte:1'),
                                    ]),

                                    FileUpload::make('image')
                                        ->label('Gambar Produk')
                                        ->image()
                                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/webp'])
                                        ->directory('pawning')
                                        ->maxSize(2048)
                                        ->imagePreviewHeight('200')
                                        ->columnSpanFull(),
                                ])
                                ->columns(1)
                                ->minItems(1)
                                ->collapsible()
                                ->cloneable()
                                ->itemLabel(fn(array $state): ?string => $state['product_name'] ?? null)
                                ->createItemButtonLabel('Tambah'),
                        ]),

                    Wizard\Step::make('Data Penggadaian')
                        ->schema([
                            Select::make('customer_id')
                                ->label('Nama Pelanggan')
                                ->searchable()
                                ->required()
                                ->preload()
                                ->options(function () {
                                    return Customer::all()->mapWithKeys(fn($customer) => [
                                        $customer->id => "{$customer->name}"
                                    ]);
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
                                        ->telRegex('/^(\+62|62|0)8[1-9][0-9]{6,11}$/')
                                        ->rule('unique:customers,phone')
                                        ->helperText('Gunakan format +62 atau 08xxx.'),

                                    TextInput::make('address')
                                        ->label('Alamat')
                                        ->prefixIcon('heroicon-m-map-pin')
                                        ->required()
                                        ->minLength(5)
                                        ->maxLength(255)
                                        ->rule('regex:/^[a-zA-Z0-9\s,.\-\/]+$/')
                                        ->helperText('Isi alamat lengkap, boleh pakai koma, titik, atau strip.'),
                                ])
                                ->createOptionUsing(function (array $data) {
                                    $customer = Customer::create($data);

                                    Notification::make()
                                        ->title("Pelanggan berhasil ditambahkan")
                                        ->body("{$customer->name}")
                                        ->success()
                                        ->send();

                                    return $customer;
                                }),

                            Grid::make(3)->schema([
                                TextInput::make('rate')
                                    ->label('Bunga (0.00% - 100.00%)')
                                    ->type('number')
                                    ->required()
                                    ->step(0.01)
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->default(0.01)
                                    ->suffix('%')
                                    ->rule('between:0,100'),

                                DatePicker::make('pawn_date')  // Ganti nama field biar gak bentrok
                                    ->label('Tanggal Gadai')
                                    ->prefixIcon('heroicon-m-calendar-days')
                                    ->required()
                                    ->rule('date'),

                                DatePicker::make('due_date')
                                    ->label('Jatuh Tempo')
                                    ->prefixIcon('heroicon-m-clock')
                                    ->required()
                                    ->rule('date')
                                    ->afterOrEqual('pawn_date'),

                                Textarea::make('notes')
                                    ->label('Catatan')
                                    ->placeholder('Masukkan catatan pinjaman')
                                    ->columnSpanFull()
                                    ->rows(10),
                            ]),
                        ]),
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table

            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Pelanggan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pawn_date')
                    ->label('Tanggan Gadai')
                    ->searchable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('estimated_value')
                    ->label('Nilai Gadai')->money('IDR'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'active' => 'success',
                        'paid_off' => 'info',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Menunggu Konfirmasi',
                        'active' => 'Aktif',
                        'paid_off' => 'Lunas',
                        default => ucfirst($state),
                    }),
            ])
            ->filters([
                // Bulan Transaksi
                // SelectFilter::make('transaction_month')
                //     ->label('Bulan Transaksi')
                //     ->options([
                //         '01' => 'Januari',
                //         '02' => 'Februari',
                //         '03' => 'Maret',
                //         '04' => 'April',
                //         '05' => 'Mei',
                //         '06' => 'Juni',
                //         '07' => 'Juli',
                //         '08' => 'Agustus',
                //         '09' => 'September',
                //         '10' => 'Oktober',
                //         '11' => 'November',
                //         '12' => 'Desember',
                //     ])
                //     ->default(now()->format('m'))
                //     ->modifyQueryUsing(function ($query, $state) {
                //         if (! $state) return;
                //         $query->whereHas('transaction', function ($q) use ($state) {
                //             $q->whereMonth('transaction_date', $state);
                //         });
                //     }),

                // Kategori Barang
                // SelectFilter::make('category_id')
                //     ->label('Kategori')
                //     ->options(\App\Models\Category::pluck('name', 'id')->toArray())
                //     ->default(null)
                //     ->modifyQueryUsing(function ($query, $state) {
                //         if (! $state) return;
                //         $query->whereHas('details', function ($q) use ($state) {
                //             $q->where('category_id', $state);
                //         });
                //     }),

                // SelectFilter::make('status')
                //     ->label('Status')
                //     ->options([
                //         'pending' => 'Menunggu Konfirmasi',
                //         'active' => 'Aktif',
                //         'paid_off' => 'Lunas',
                //     ])
                //     ->default(null)
                //     ->modifyQueryUsing(function ($query, $state) {
                //         if (! $state) return;
                //         $query->where('status', $state);
                //     }),
            ])


            ->defaultSort('id', 'desc') // 🔥 Tambahin default sort by ID desc

            ->actions([
                Action::make('payment')
                    ->label('Pembayaran')
                    ->icon('heroicon-m-paper-airplane')
                    ->color('info')
                    ->visible(fn($record) => $record->transaction->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Proses Pembayaran')
                    ->modalDescription('Apakah kamu yakin mau proses pembayaran untuk penggadaian ini?')
                    ->modalButton('Ya, Proses Pembayaran')
                    ->action(function ($record, $data) {
                        return redirect()->route('filament.admin.shop.resources.pawnings.payment', $record->transaction->invoice);
                    }),

                Action::make('confirmation')
                    ->label('Konfirmasi')
                    ->icon('heroicon-m-paper-airplane')
                    ->color('success')
                    ->visible(fn($record) => $record->status !== 'active' && $record->transaction->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Proses Pembayaran')
                    ->modalDescription('Apakah kamu yakin mau proses pembayaran untuk penggadaian ini?')
                    ->modalButton('Ya, Proses Pembayaran')
                    ->action(function ($record, $data) {
                        return redirect()->route('filament.admin.shop.resources.pawnings.confirmation', $record->transaction->invoice);
                    }),
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
                        return redirect()->route('print.pawning', $record->transaction->invoice);
                    })
                    ->link(),
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
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
            'index' => Pages\ListPawnings::route('/'),
            'create' => Pages\CreatePawning::route('/create'),
            'view' => Pages\ViewPawning::route('/{record}'),
            'confirmation' => Pages\ConfirmationPage::route('/confirmation/{inv}'),
            'payment' => Pages\PaymentPage::route('/payment/{inv}'),
        ];
    }
}
