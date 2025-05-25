<?php

namespace App\Filament\Clusters\Shop\Resources\SaleResource\Pages;

use App\Models\Sale;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Filament\Clusters\Shop\Resources\SaleResource;

class OrdersPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = SaleResource::class;
    protected static string $view = 'filament.pages.shop.sale.orders';

    protected static ?string $title = 'Pesanan';
    protected static ?string $breadcrumb = 'Pesanan';

    public function getHeading(): string|Htmlable
    {
        return 'Pesanan';
    }

    public function table(Table $table): Table
    {
        return $table
            ->emptyStateActions([
                Action::make('add')
                    ->label('Tambah Penjualan')
                    ->url(route('filament.admin.shop.resources.sales.index'))
                    ->icon('heroicon-m-plus')
                    ->button(),
            ])
            ->query(Sale::query()) // 👈 Pastikan query ini sesuai dengan relasi produk
            ->columns([
                TextColumn::make('transaction.invoice')
                    ->label('Invoice')
                    ->searchable(),
                TextColumn::make('cash')
                    ->money('IDR')
                    ->label('Tunai'),
                TextColumn::make('change')
                    ->money('IDR')
                    ->label('Kembalian'),
                TextColumn::make('discount')
                    ->money('IDR')
                    ->label('Diskon'),
                TextColumn::make('total_amount')
                    ->money('IDR')
                    ->label('Jumlah'),
                TextColumn::make('transaction.payment_method')
                    ->label('Pembayaran')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'cash' => 'success', // Hijau
                        'online' => 'info',  // Biru
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'cash' => 'Tunai',
                        'online' => 'Transfer',
                        default => ucfirst($state),
                    }),

                TextColumn::make('transaction.status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'success' => 'success',
                        'expired' => 'gray',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'success' => 'Berhasil',
                        'expired' => 'Kadaluarsa',
                        'failed' => 'Gagal',
                        default => ucfirst($state),
                    }),

            ])
            ->filters([
                // Tambahkan filter jika perlu
            ])
            ->actions([
                Action::make('payment')
                    ->label('Proses Pembayaran')
                    ->icon('heroicon-m-credit-card')
                    ->color('success')
                    ->visible(fn($record) => $record->transaction->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Proses Pembayaran')
                    ->modalDescription('Apakah kamu yakin mau proses pembayaran untuk pesanan ini?')
                    ->modalButton('Ya, Proses Pembayaran')
                    ->action(function ($record, $data) {
                        return redirect()->route('filament.admin.shop.resources.sales.checkout', $record->transaction->invoice);
                    })
                    ->link(),
                // Action::make('payment')
                //     ->label('Proses Pembayaran')
                //     ->icon('heroicon-m-credit-card')
                //     ->color('success')
                //     ->visible(fn($record) => $record->transaction->status === 'pending')
                //     ->requiresConfirmation()
                //     ->modalHeading('Proses Pembayaran')
                //     ->modalDescription('Apakah kamu yakin mau proses pembayaran untuk pesanan ini?')
                //     ->modalButton('Ya, Proses Pembayaran')
                //     ->action(function ($record, $data) {
                //         return redirect()->to($record->transaction->payment_link);
                //     })
                //     ->link(),
                Action::make('print')
                    ->label('Cetak Nota')
                    ->icon('heroicon-m-printer')
                    ->color('danger')
                    ->visible(fn($record) => $record->transaction->status === 'success')
                    ->requiresConfirmation()
                    ->modalHeading('Cetak Nota')
                    ->modalDescription('Apakah kamu yakin mau cetak nota untuk pesanan ini?')
                    ->modalButton('Ya, Cetak')
                    ->action(function ($record, $data) {
                        return redirect()->route('print.sale', $record->transaction->invoice);
                    })
                    ->link(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
