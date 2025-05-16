<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpnameDetail extends Model
{
    /** @use HasFactory<\Database\Factories\StockOpnameDetailFactory> */
    use HasFactory;
    protected $guarded = [''];

    public function stockOpname()
    {
        return $this->belongsTo(StockOpname::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function stockTotal()
    {
        return $this->belongsTo(StockTotal::class);
    }
}
