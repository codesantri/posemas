<?php

namespace App\Filament\Clusters\Shop\Resources\ChangeResource\Pages;

use App\Filament\Clusters\Shop\Resources\ChangeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;


class ListChanges extends ListRecords
{
    protected static string $resource = ChangeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Pertukaran')->icon('heroicon-m-plus'),
        ];
    }

    protected function getTableQuery(): ?Builder
    {
        return parent::getTableQuery()?->where('transaction_type', 'change');
    }
}
