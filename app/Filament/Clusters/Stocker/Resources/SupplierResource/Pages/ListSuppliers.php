<?php

namespace App\Filament\Clusters\Stocker\Resources\SupplierResource\Pages;

use App\Filament\Clusters\Stocker\Resources\SupplierResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSuppliers extends ListRecords
{
    protected static string $resource = SupplierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Supplier')
                ->modalHeading('Supplier Baru')
                ->modalSubmitActionLabel('Simpan Supplier'),
        ];
    }
}
