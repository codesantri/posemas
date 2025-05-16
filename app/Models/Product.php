<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;
    protected $guarded = [''];
    protected $casts = [
        'weight' => 'float',
    ];


    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    public function karat()
    {
        return $this->belongsTo(Karat::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function stockTotals()
    {
        return $this->hasOne(StockTotal::class);
    }

    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class);
    }


    public function getHargaModalAttribute()
    {
        return $this->karat
            ? $this->karat->buy_price * floatval($this->weight)
            : 0;
    }

    public function getHargaJualAttribute()
    {
        return $this->karat
            ? $this->karat->sell_price * floatval($this->weight)
            : 0;
    }
}
