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
use Filament\Forms\Components\Split;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
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
                        })->columnSpanFull(),
                    Split::make([
                        Section::make('Produk Lama')
                            ->description('Informasi produk yang sebelumnya digunakan oleh pelanggan.')
                            ->schema([
                                Repeater::make('olds')
                                    ->label('')
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
                                    ])->addActionLabel('Tambah'),
                            ]),
                        Section::make('Produk Baru')
                            ->description('Detail produk pengganti yang akan dicatat dalam sistem.')
                            ->schema([
                                Repeater::make('news')
                                    ->label('')
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
                                    ])->addActionLabel('Tambah'),
                            ])
                    ])->from('md'),
                ]),
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
            'index' => Pages\ListChanges::route('/'),
            'create' => Pages\CreateChange::route('/create'),
            'edit' => Pages\EditChange::route('/{record}/edit'),
        ];
    }
}
