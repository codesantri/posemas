<?php

namespace App\Filament\Clusters\Shop\Resources\ChangeResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Clusters\Shop\Resources\ChangeResource;
use App\Models\Transaction;

class PaymentCangePage extends ViewRecord
{
    protected static string $resource = ChangeResource::class;

    protected static ?string $navigationLabel = '';
    protected static ?string $title = 'Transaksi Pertukaran';

    protected function resolveRecord($key): Model
    {
        if (is_numeric($key)) {
            return parent::resolveRecord($key);
        }

        return static::getModel()::whereHas('transaction', fn($q) => $q->where('invoice', $key))
            ->firstOrFail();
        $transaction = Transaction::where('invoice', $key)->first();
    }
}
