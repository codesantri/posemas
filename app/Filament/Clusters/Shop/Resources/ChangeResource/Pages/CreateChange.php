<?php

namespace App\Filament\Clusters\Shop\Resources\ChangeResource\Pages;

use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Clusters\Shop\Resources\ChangeResource;

class CreateChange extends CreateRecord
{
    protected static string $resource = ChangeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('addnew')
                ->label('Tambah Produk')
                ->icon('heroicon-m-plus')
                ->form([
                    TextInput::make('name')
                        ->label('Nama Produk')
                        ->required(),

                    Select::make('category_id')
                        ->label('Kategori')
                        ->options(\App\Models\Category::all()->pluck('name', 'id'))
                        ->required(),

                    Select::make('type_id')
                        ->label('Jenis')
                        ->options(\App\Models\Type::all()->pluck('name', 'id'))
                        ->required(),

                    Select::make('karat_id')
                        ->label('Karat-Kadar')
                        ->options(\App\Models\Karat::all()->mapWithKeys(fn($k) => [
                            $k->id => "{$k->karat} - {$k->rate}%",
                        ])),

                    TextInput::make('weight')
                        ->label('Berat (gram)')
                        ->numeric()
                        ->required()
                        ->minValue(0.01)
                        ->default(0.01)
                        ->step(0.01),

                    TextInput::make('quantity')
                        ->label('Jumlah')
                        ->numeric()
                        ->default(1)
                        ->required(),

                    FileUpload::make('image')
                        ->label('Gambar Produk')
                        ->image()
                        ->directory('products')
                        ->maxSize(2048),
                ])
                ->action(function (array $data) {
                    // Ambil quantity
                    $quantity = $data['quantity'];
                    unset($data['quantity']);

                    $product = \App\Models\Product::create($data);

                    \App\Models\Stock::create([
                        'product_id' => $product->id,
                        'stock_quantity' => $quantity,
                        'received_at' => now(),
                    ]);

                    \App\Models\StockTotal::updateOrCreate(
                        ['product_id' => $product->id],
                        ['total' => $quantity]
                    );

                    \Filament\Notifications\Notification::make()
                        ->title('Produk berhasil ditambahkan')
                        ->success()
                        ->send();
                }),
        ];
    }

    public static function canCreateAnother(): bool
    {
        return false;
    }


    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('Simpan & Hitung Nilai Tukar')
            ->requiresConfirmation()
            ->modalHeading('Konfirmasi Perhitungan')
            ->modalSubheading('Apakah Anda yakin ingin menghitung nilai tukar? Data yang dimasukkan akan disimpan.')
            ->modalButton('Ya, Lanjutkan');
    }
}
