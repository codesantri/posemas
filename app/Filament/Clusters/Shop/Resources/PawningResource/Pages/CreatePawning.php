<?php

namespace App\Filament\Clusters\Shop\Resources\PawningResource\Pages;

use Filament\Actions;
use App\Models\Transaction;
use App\Models\PawningDetail;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use App\Filament\Clusters\Shop\Resources\PawningResource;

class CreatePawning extends CreateRecord
{
    /**
     * @var string
     */
    protected static string $resource = PawningResource::class;
    protected static ?string $title = 'Tambah Penggadaian';

    protected array $processedPawning = [];

    public static function canCreateAnother(): bool
    {
        return false;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $transaction = Transaction::create([
            'transaction_type' => 'pawning',
            'status' => 'pending',
            'total_amount' => 0,
        ]);

        $data['user_id'] = Auth::id();
        $data['transaction_id'] = $transaction->id;
        return $data;
    }



    protected function afterCreate(): void
    {
        $record = $this->record; // pawning baru
        $estimatedValue = 0;

        foreach ($this->data['products'] as $item) {
            $imagePath = null;

            if (!empty($item['image'])) {
                if (is_array($item['image']) || (is_string($item['image']) && json_decode($item['image']))) {
                    $imageData = is_string($item['image']) ? json_decode($item['image'], true) : $item['image'];
                    $imagePath = reset($imageData);
                } else {
                    $imagePath = $item['image'];
                }
            }

            $product = PawningDetail::create([
                'name' => $item['name'],
                'category_id' => $item['category_id'],
                'pawning_id' => $record->id,
                'type_id' => $item['type_id'],
                'karat_id' => $item['karat_id'],
                'weight' => $item['weight'],
                'quantity' => $item['quantity'],
                'image' => $imagePath,
            ]);
            $estimatedValue += $product->karat->buy_price * $product->quantity * $product->weight;
        }

        // Update nilai estimasi pada pawning dan transaksi
        $record->update([
            'estimated_value' => $estimatedValue,
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('confirmation', [$this->record->transaction->invoice]);
    }

    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('Simpan & Proses Penggadaain');
    }
}
