<?php

namespace App\Models;

use Illuminate\Support\Facades\Blade;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
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

    // protected function image(): Attribute
    // {
    //     return Attribute::make(
    //         get: function ($image) {
    //             if ($image) {
    //                 return asset('/storage/' . $image);
    //             }

    //             // Render Blade Icon as SVG
    //             $svgFallback = Blade::render('<x-heroicon-o-photo class="w-36 h-36" />');

    //             // Encode jadi data URI
    //             $base64 = 'data:image/svg+xml;base64,' . base64_encode($svgFallback);

    //             return $base64;
    //         },
    //         set: fn($image) => $image,
    //     );
    // }
}
