<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTotal extends Model
{
    /** @use HasFactory<\Database\Factories\StockTotalFactory> */
    use HasFactory;
    protected $guarded = [''];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
