<?php

namespace App\Filament\Clusters\Shop\Resources\PawningResource\Pages;

use App\Filament\Clusters\Shop\Resources\PawningResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;

class CreatePawning extends CreateRecord
{
    /**
     * @var string
     */
    protected static string $resource = PawningResource::class;
    protected static ?string $title = 'Tambah Penggadaian';

    public static function canCreateAnother(): bool
    {
        return false;
    }
}
