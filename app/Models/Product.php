<?php

namespace App\Models;

use App\Traits\HasImageHandler;
use Illuminate\Support\Facades\Blade;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory, HasImageHandler;
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

    public function getImagePath(): ?string
    {
        return $this->image; // ganti sesuai nama kolom di DB, misal `image`, `image_path`, dll
    }

    protected static function booted()
    {
        static::deleting(function ($model) {
            $model->onDelete(true);
        });

        static::updating(function ($model) {
            $model->onUpdate(true);
        });
    }
}
