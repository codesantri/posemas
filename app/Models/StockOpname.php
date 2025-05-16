<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpname extends Model
{
    /** @use HasFactory<\Database\Factories\StockOpnameFactory> */
    use HasFactory;
    protected $guarded = [''];

    public function details()
    {
        return $this->hasMany(StockOpnameDetail::class);
    }
}
