<?php

namespace App\Models;

use App\Traits\HasImageHandler;
use Illuminate\Support\Facades\Blade;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PawningDetail extends Model
{
    /** @use HasFactory<\Database\Factories\PawningDetailFactory> */
    use HasFactory, HasImageHandler;
    protected $guarded = [''];

    public function pawning()
    {
        return $this->belongsTo(Pawning::class);
    }

    public function karat()
    {
        return $this->belongsTo(Karat::class);
    }

    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    protected static function booted()
    {
        static::deleting(function ($model) {
            $model->onDelete(true);
        });

        static::updating(function ($model) {
            $model->onUpdate(true); // 🔄 Auto delete old image saat update
        });
    }
}
